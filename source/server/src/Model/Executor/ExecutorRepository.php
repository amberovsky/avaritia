<?
/**
 * @author amberovsky
 *
 * Репозиторий исполнителя
 */

namespace Avaritia\Model\Executor\ExecutorRepository;

load('Avaritia\Library\Memcached\Memcached');
load('Avaritia\Model\Executor\Executor');
load('Avaritia\Library\Mysql\MysqlFactory');
load('Avaritia\Library\Mysql\Mysql');
load('Avaritia\Library\Password');

use Avaritia\Library\Memcached\Memcached;
use Avaritia\Model\Executor\Executor;
use Avaritia\Library\Mysql\MysqlFactory;
use Avaritia\Library\Mysql\Mysql;
use Avaritia\Library\Password;

// Константы именования таблиц/бд
const
    DATABASE_NAME   = 'Executor', // бд
    TABLE_NAME      = 'Executor', // таблица
    SHARD_CONFIG    = 'executor'; // секция конфига шардов

// Поля класса
const
    FIELD_MEMCACHED     = 'Memcached', /** объект мемкеша, для счётчиков */
    FIELD_MYSQL_FACTORY = 'Mysql_Factory'; /** объект фабрики mysql */

/**
 * @param array &$Memcached объект мемкеша
 * @param array &$MysqlFactory объект фабрики mysql
 *
 * @return &array объект репозитория исполнителя
 */
function &construct(array &$Memcached, array &$MysqlFactory) {
    $ExecutorRepository = [
        FIELD_MEMCACHED     => &$Memcached,
        FIELD_MYSQL_FACTORY => &$MysqlFactory,
    ];

    return $ExecutorRepository;
}

/**
 * @private
 *
 * @param array $ExecutorRepository репозиторий исполнителя
 *
 * @return &array объект мемкеша
 */
function &getMemcached(array $ExecutorRepository) {
    return $ExecutorRepository[FIELD_MEMCACHED];
}

/**
 * @param array $ExecutorRepository объект репозитория исполнителя
 *
 * @return &array объект фабрики mysql
 */
function &getMysqlFactory(array $ExecutorRepository) {
    return $ExecutorRepository[FIELD_MYSQL_FACTORY];
}

/**
 * @param int $id id исполнителя
 *
 * @return int id mysql шарда для данного исполнителя
 */
function getShardId($id) {
    return (int) floor($id / 10000);
}

/**
 * @static
 *
 * @return string имя ключа в мемкеше, который хранит id последнего созданного исполнителя
 */
function getLastExecutorIdKey() {
    return 'executor_last_id';
}

/**
 * @static
 *
 * @param string $login логин исполнителя
 *
 * @return string ключ в кеширующем мемкеше для сохранения хеша пароля исполнителя
 */
function createMemcachedKeyForPasswordHash($login) {
    return 'executor_passwordhash:' . $login;
}

/**
 * Обновляет значение ключа, хранящего id последнего созданного исполнителя
 *
 * @param array $ExecutorRepository объект репозитория исполнителя
 * @param int $lastId значение, с которым нужно синхронизироваться
 *
 * @return bool упех обновления
 */
function syncLastExecutorId(array $ExecutorRepository, $lastId) {
    return Memcached\set(
        getMemcached($ExecutorRepository),
        getLastExecutorIdKey(),
        (int) $lastId
    );
}

/**
 * Сохраняет хешированный пароль исполнителя в мемкеше
 *
 * @param array $ExecutorRepository объект репозитория исполнителя
 * @param array $Executor объект исполнителя
 * @param string $passwordHash хешированный пароль
 *
 * @return bool результат сохранения
 */
function savePasswordHashToMemcached(array $ExecutorRepository, array $Executor, $passwordHash) {
    return Memcached\set(
        getMemcached($ExecutorRepository),
        createMemcachedKeyForPasswordHash(Executor\getLogin($Executor)),
        $passwordHash
    );
}

/**
 * Создаёт новый шард в mysql
 *
 * @param array $ExecutorRepository объект репозитория исполнителя
 * @param int $shardId id шарда
 *
 * @return bool успех создания
 */
function createShard(array $ExecutorRepository, $shardId) {
    $MysqlFactory = &getMysqlFactory($ExecutorRepository);
    $Mysql = &MysqlFactory\createShard($MysqlFactory, SHARD_CONFIG, $shardId);

    Mysql\query($Mysql, 'CREATE DATABASE ' . DATABASE_NAME . ' CHARACTER SET utf8 COLLATE utf8_unicode_ci');
    return Mysql\query($Mysql, '
        CREATE TABLE ' . DATABASE_NAME . '.' . TABLE_NAME . ' (
          id            INT UNSIGNED  NOT NULL,
          login         VARCHAR(16)   NOT NULL,
          salary        INT UNSIGNED  NOT NULL,
          fio           VARCHAR(32)   NOT NULL,
          password_hash VARCHAR(60)   NOT NULL,

          INDEX(id)
        ) ENGINE=InnoDB;
    ');
}

/**
 * Создаёт нового исполнителя с сохранением в mysql и мемкеш
 *
 * @param array $ExecutorRepository объект репозитория исполнителя
 * @param string $login логин
 * @param string $fio ФИО
 * @param int $salary
 * @param string $password пароль исполнителя
 *
 * @return &array объект исполнителя
 */
function &create(array $ExecutorRepository, $login, $fio, $salary, $password) {
    $Executor = &Executor\construct();

    Executor\setId($Executor, Memcached\increment(getMemcached($ExecutorRepository), getLastExecutorIdKey()));
    Executor\setLogin($Executor, $login);
    Executor\setSalary($Executor, $salary);
    Executor\setFio($Executor, $fio);

    $passwordHash = Password\hash($password);

    createInMysql($ExecutorRepository, $Executor, $passwordHash);
    savePasswordHashToMemcached($ExecutorRepository, $Executor, $passwordHash);
    saveLoginToMemcached($ExecutorRepository, $login, Executor\getId($Executor));

    return $Executor;
}

/**
 * @private
 *
 * Создаёт нового исполнителя в mysql
 *
 * @param array $ExecutorRepository объект репозитория исполнителя
 * @param array $Executor объект исполнителя
 * @param string $passwordHash хешированный пароль исполнителя
 *
 * @return bool успех создания
 */
function createInMysql(array $ExecutorRepository, array $Executor, $passwordHash) {
    $Mysql = &MysqlFactory\createShard(
        getMysqlFactory($ExecutorRepository),
        SHARD_CONFIG,
        getShardId(Executor\getId($Executor))
    );

    return Mysql\query($Mysql, '
        INSERT INTO ' . DATABASE_NAME . '.' . TABLE_NAME . '
          (id, login, salary, fio, password_hash)
        VALUES (
          \'' . (int) Executor\getId($Executor) . '\',
          \'' . Mysql\escape($Mysql, Executor\getLogin($Executor)) . '\',
          \'' . Mysql\escape($Mysql, Executor\getSalary($Executor)) . '\',
          \'' . Mysql\escape($Mysql, Executor\getFio($Executor)) . '\',
          \'' . $passwordHash . '\'
        )
    ');
}

/**
 * @param array &$ExecutorRepository объект репозитория исполнителя
 * @param string $login логин исполнителя
 * @param string $password пароль исполнителя
 *
 * @return bool верный ли пароль
 */
function validateAuth(array &$ExecutorRepository, $login, $password) {
    return Password\check(
        $password,
        Memcached\get(getMemcached($ExecutorRepository), createMemcachedKeyForPasswordHash($login))
    );
}

/**
 * @static
 *
 * @param string $login логин исполнителя
 *
 * @return string ключ в кеширующем мемкеше для сохранения логина-id исполнителя
 */
function createMemcachedKeyForLogin($login) {
    return 'executor:' . $login;
}

/**
 * Сохраняет логин-id исполнителя в мемкеше
 *
 * @param array $ExecutorRepository объект репозитория исполнителя
 * @param string $login логин
 * @param int $id id
 *
 * @return bool результат сохранения
 */
function saveLoginToMemcached(array $ExecutorRepository, $login, $id) {
    return Memcached\set(
        getMemcached($ExecutorRepository),
        createMemcachedKeyForLogin($login),
        $id
    );
}

/**
 * @param array $ExecutorRepository объект репозитория исполнителя
 * @param string $login логин исполнителя
 *
 * @return &array|null объект исполнителя или null, если таковой не найден
 */
function &fetch(array $ExecutorRepository, $login) {
    $Memcached = &getMemcached($ExecutorRepository);
    $id = Memcached\get($Memcached, createMemcachedKeyForLogin($login));

    if ($id === false) {
        // нет такого исполнителя
        return null;
    }

    $id = (int) $id;
    $Mysql = &MysqlFactory\createShard(getMysqlFactory($ExecutorRepository), SHARD_CONFIG, getShardId($id));
    $result = Mysql\query(
        $Mysql,
        'SELECT id, fio, login, salary FROM ' . DATABASE_NAME . '.' . TABLE_NAME . ' WHERE id = ' . (int) $id
    );

    if (Mysql\numRows($result) == 0) {
        return null;
    }

    return Executor\unserializeFromMysql(Mysql\fetchAssoc($Mysql, $result));
}

/**
 * Обновление зарплаты исполнителя
 *
 * @param array &$ExecutorRepository объект репозитория исполнителя
 * @param array &$Executor объект исполнителя
 * @param int $money на сколько изменить зарплату
 */
function updateSalary(array &$ExecutorRepository, array &$Executor, $money) {
    $Mysql = &MysqlFactory\createShard(
        getMysqlFactory($ExecutorRepository),
        SHARD_CONFIG,
        getShardId(Executor\getId($Executor))
    );

    Mysql\query($Mysql, '
        UPDATE ' . DATABASE_NAME . '.' . TABLE_NAME . '
        SET SALARY = SALARY + ' . (int) $money . '
        WHERE id = ' . (int) Executor\getId($Executor)
    );

    Executor\setSalary($Executor, Executor\getSalary($Executor) + (int) $money);
}

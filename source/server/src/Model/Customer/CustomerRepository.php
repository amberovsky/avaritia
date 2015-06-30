<?
/**
 * @author amberovsky
 *
 * Репозиторий заказчиков
 */

namespace Avaritia\Model\Customer\CustomerRepository;

load('Avaritia\Library\Memcached\Memcached');
load('Avaritia\Model\Customer\Customer');
load('Avaritia\Library\Mysql\MysqlFactory');
load('Avaritia\Library\Mysql\Mysql');
load('Avaritia\Library\Password');

use Avaritia\Library\Memcached\Memcached;
use Avaritia\Model\Customer\Customer;
use Avaritia\Library\Mysql\MysqlFactory;
use Avaritia\Library\Mysql\Mysql;
use Avaritia\Library\Password;

// Константы именования таблиц/бд
const
    DATABASE_NAME   = 'Customer', // бд
    TABLE_NAME      = 'Customer', // таблица
    SHARD_CONFIG    = 'customer'; // секция конфига шардов

// Поля класса
const
    FIELD_MEMCACHED     = 'Memcached', /** объект мемкеша, кеширующий заказчиков */
    FIELD_MYSQL_FACTORY = 'Mysql_Factory'; /** объект фабрики mysql */

/**
 * @param array &$Memcached объект мемкеша
 * @param array &$MysqlFactory объект фабрики mysql
 *
 * @return &array объект репозитория заказчиков
 */
function &construct(array &$Memcached, array &$MysqlFactory) {
    $CustomerRepository = [
        FIELD_MEMCACHED     => &$Memcached,
        FIELD_MYSQL_FACTORY => &$MysqlFactory,
    ];

    return $CustomerRepository;
}

/**
 * @private
 *
 * @param array $CustomerRepository
 *
 * @return &array объект мемкеша
 */
function &getMemcached(array $CustomerRepository) {
    return $CustomerRepository[FIELD_MEMCACHED];
}

/**
 * @param array $CustomerRepository объект репозитория заказчика
 *
 * @return &array объект фабрики mysql
 */
function &getMysqlFactory(array $CustomerRepository) {
    return $CustomerRepository[FIELD_MYSQL_FACTORY];
}

/**
 * @static
 *
 * @param string $login логин заказчика
 *
 * @return string ключ в кеширующем мемкеше для сохранения данных заказчика
 */
function createMemcachedKeyForData($login) {
    return 'customer:' . $login;
}

/**
 * @static
 *
 * @param string $login логин заказчика
 *
 * @return string ключ в кеширующем мемкеше для сохранения хеша пароля заказчика
 */
function createMemcachedKeyForPasswordHash($login) {
    return 'customer_passwordhash:' . $login;
}

/**
 * @param array &$CustomerRepository объект репозитория заказчика
 * @param string $login логин заказчика
 *
 * @return &array|null объект заказчика или null, если таковой не найден
 */
function &fetch(array $CustomerRepository, $login) {
    $Memcached = &getMemcached($CustomerRepository);

    $data = Memcached\get($Memcached, createMemcachedKeyForData($login));
    $Customer = ($data === false) ? null : Customer\unserializeFromMemcache($data);

    return $Customer;
}

/**
 * @param array $Customer объект заказчика
 *
 * @return int id mysql шарда для данного заказчика
 */
function getShardId(array $Customer) {
    return (int) floor(Customer\getId($Customer) / 10000);
}

/**
 * Сохраняет данные заказчика в мемкеше
 *
 * @param array $CustomerRepository объект репозитория заказчика
 * @param array $Customer объект заказчика
 *
 * @return bool результат сохранения
 */
function saveToMemcached(array $CustomerRepository, array $Customer) {
    return Memcached\set(
        getMemcached($CustomerRepository),
        createMemcachedKeyForData(Customer\getLogin($Customer)),
        Customer\serializeToMemcache($Customer)
    );
}

/**
 * Сохраняет хешированный пароль заказчика в мемкеше
 *
 * @param array $CustomerRepository объект репозитория заказчика
 * @param array $Customer объект заказчика
 * @param string $passwordHash хешированный пароль
 *
 * @return bool результат сохранения
 */
function savePasswordHashToMemcached(array $CustomerRepository, array $Customer, $passwordHash) {
    return Memcached\set(
        getMemcached($CustomerRepository),
        createMemcachedKeyForPasswordHash(Customer\getLogin($Customer)),
        $passwordHash
    );
}

/**
 * @private
 *
 * Создаёт нового заказчика в mysql
 *
 * @param array $CustomerRepository объект репозитория заказчика
 * @param array $Customer объект заказчика
 * @param string $passwordHash хешированный пароль заказчика
 *
 * @return bool успех создания
 */
function createInMysql(array $CustomerRepository, array $Customer, $passwordHash) {
    $Mysql = &MysqlFactory\createShard(getMysqlFactory($CustomerRepository), SHARD_CONFIG, getShardId($Customer));

    return Mysql\query($Mysql, '
        INSERT INTO
          Customer (id, login, fio, password_hash)
        VALUES (
          \'' . mysql_real_escape_string(Customer\getId($Customer)) . '\',
          \'' . mysql_real_escape_string(Customer\getLogin($Customer)) . '\',
          \'' . mysql_real_escape_string(Customer\getFio($Customer)) . '\',
          \'' . mysql_real_escape_string($passwordHash) . '\'
        )
    '
    );
}

/**
 * @static
 *
 * @return string имя ключа в мемкеше, который хранит id последнего созданного заказчика
 */
function getLastCustomerIdKey() {
    return 'customer_last_id';
}

/**
 * Обновляет значение ключа, хранящего id последнего созданного заказчика
 *
 * @param array $CustomerRepository объект репозитория заказчика
 * @param int $lastId значение, с которым нужно синхронизироваться
 *
 * @return bool упех обновления
 */
function syncLastCustomerId(array $CustomerRepository, $lastId) {
    return Memcached\set(
        getMemcached($CustomerRepository),
        getLastCustomerIdKey(),
        (int) $lastId
    );
}

/**
 * Создаёт новый шард в mysql
 *
 * @param array $CustomerRepository объект репозитория заказчика
 * @param int $shardId id шарда
 *
 * @return bool успех создания
 */
function createShard(array $CustomerRepository, $shardId) {
    $MysqlFactory = &getMysqlFactory($CustomerRepository);
    $Mysql = &MysqlFactory\createShard($MysqlFactory, SHARD_CONFIG, $shardId);

    Mysql\query($Mysql, 'CREATE DATABASE ' . DATABASE_NAME . ' CHARACTER SET utf8 COLLATE utf8_unicode_ci');
    return Mysql\query($Mysql, '
        CREATE TABLE ' . DATABASE_NAME . '.' . TABLE_NAME . ' (
          id            INT UNSIGNED  NOT NULL,
          login         VARCHAR(16)   NOT NULL,
          fio           VARCHAR(32)   NOT NULL,
          password_hash VARCHAR(60)   NOT NULL
        ) ENGINE=InnoDB;
    ');
}

/**
 * Создаёт нового заказчика с сохранением в mysql и мемкеш
 *
 * @param array $CustomerRepository объект репозитория заказчика
 * @param string $login логин
 * @param string $fio ФИО
 * @param string $password пароль заказчика
 *
 * @return &array объект пользователя
 */
function &create(array $CustomerRepository, $login, $fio, $password) {
    $Customer = Customer\construct();

    Customer\setId($Customer, Memcached\increment(getMemcached($CustomerRepository), getLastCustomerIdKey()));
    Customer\setLogin($Customer, $login);
    Customer\setFio($Customer, $fio);

    $passwordHash = Password\hash($password);

    saveToMemcached($CustomerRepository, $Customer);
    savePasswordHashToMemcached($CustomerRepository, $Customer, $passwordHash);
    createInMysql($CustomerRepository, $Customer, $passwordHash);

    return $Customer;
}

/**
 * @param array &$CustomerRepository объект репозитория заказчика
 * @param string $login логин заказчика
 * @param string $password пароль заказчика
 *
 * @return bool верный ли пароль
 */
function validateAuth(array &$CustomerRepository, $login, $password) {
    return Password\check(
        $password,
        Memcached\get(getMemcached($CustomerRepository), createMemcachedKeyForPasswordHash($login))
    );
}

<?
/**
 * @author amberovsky
 *
 * Репозиторий заказов
 */

namespace Avaritia\Model\Order\OrderRepository;

load('Avaritia\Library\Mysql\Mysql');
load('Avaritia\Model\Order\Order');
load('Avaritia\Library\Memcached\Memcached');

use Avaritia\Library\Mysql\Mysql;
use Avaritia\Model\Order\Order;
use Avaritia\Library\Memcached\Memcached;

// Константы именования таблиц/бд
const
    DATABASE_NAME   = '`Order`', // БД
    TABLE_NAME      = '`Order`'; // таблица

// Поля класса
const
    FIELD_MEMCACHED = 'Memcached', /** объект мемкеша, для хранения цены */
    FIELD_MYSQL     = 'Mysql'; // объект mysql

/**
 * @param array &$Memcached объект мемкеша
 * @param array &$Mysql объект mysql
 *
 * @return &array объект репозитория заказа
 */
function &construct(array &$Memcached, array &$Mysql) {
    $OrderRepository = [
        FIELD_MEMCACHED => &$Memcached,
        FIELD_MYSQL     => &$Mysql,
    ];

    return $OrderRepository;
}

/**
 * @param array $OrderRepository объект репозитория заказа
 *
 * @return &array объект mysql
 */
function &getMysql(array $OrderRepository) {
    return $OrderRepository[FIELD_MYSQL];
}

/**
 * @private
 *
 * @param array $OrderRepository репозиторий заказов
 *
 * @return &array объект мемкеша
 */
function &getMemcached(array $OrderRepository) {
    return $OrderRepository[FIELD_MEMCACHED];
}

/**
 * Создание БД и таблицы
 *
 * @param array &$OrderRepository объект репозитория заказа
 *
 * @return bool|resource результат выполнения
 */
function createDatabaseAndTable(array &$OrderRepository) {
    $Mysql = &getMysql($OrderRepository);

    Mysql\query($Mysql, 'CREATE DATABASE ' . DATABASE_NAME . ' CHARACTER SET utf8 COLLATE utf8_unicode_ci');
    return Mysql\query($Mysql, '
        CREATE TABLE ' . DATABASE_NAME . '.' . TABLE_NAME . ' (
            id      INT UNSIGNED    NOT NULL    AUTO_INCREMENT PRIMARY KEY,
            price   INT UNSIGNED    NOT NULL,
            text    VARCHAR(1024)   NOT NULL
        ) ENGINE=InnoDB
    ');
}

/**
 * @private
 * @static
 *
 * @param int $id id заказа
 *
 * @return string ключ в кеширующем мемкеше для сохранения цены
 */
function createMemcachedKeyForPrice($id) {
    return 'order:' . $id;
}

/**
 * Сохраняет стоимость заказа в мемкеше
 *
 * @param array $OrderRepository объект репозитория заказа
 * @param int $price стоимость заказа
 */
function savePriceToMemcached(array $OrderRepository, $price) {
    Memcached\set(
        getMemcached($OrderRepository),
        createMemcachedKeyForPrice(Mysql\lastInsertId(getMysql($OrderRepository))),
        $price
    );
}

/**
 * Создаёт новый заказ в БД
 *
 * @param array &$OrderRepository
 * @param int $price стоимость заказа
 * @param string $text текст заказа
 */
function create(array &$OrderRepository, $price, $text) {
    Mysql\query(
        getMysql($OrderRepository), '
        INSERT INTO ' . DATABASE_NAME . '.' . TABLE_NAME . '
            (price, text)
        VALUES
            (' . (int) $price . ', ' . mysql_real_escape_string($text) . ')'
    );

    savePriceToMemcached($OrderRepository, $price);
}

/**
 * @param array &$OrderRepository объект репозитория заказа
 *
 * @return array все объекты заказов
 */
function fetchAll(array &$OrderRepository) {
    $Mysql = &getMysql($OrderRepository);

    $Orders = [];
    $result = Mysql\query($Mysql, 'SELECT id, price, text FROM ' . DATABASE_NAME . '.' . TABLE_NAME);
    while (($data = Mysql\fetchAssoc($Mysql, $result)) !== false) {
        $Orders[] = &Order\unserializeFromMysql($data);
    }

    return $Orders;
}

/**
 * Удаляет заказ из mysql
 *
 * @param array &$OrderRepository репозиторий заказа
 * @param int $id id заказа на удаление
 *
 * @return bool произошло ли удаление
 */
function deleteFromMysql(array &$OrderRepository, $id) {
    $Mysql = &getMysql($OrderRepository);
    Mysql\query($Mysql, 'DELETE FROM ' . DATABASE_NAME . '.' . TABLE_NAME . ' WHERE id = ' . (int) $id);

    return (Mysql\affectedRows($Mysql) == 1);
}

/**
 * Возвращает стоимость заказа из мемкеша и удалят из мемкеша
 *
 * @param array &$OrderRepository репозиторий заказа
 * @param int $id id заказа
 *
 * @return mixed|null
 */
function getPriceFromMemcachedAndDeleteIt(array &$OrderRepository, $id) {
    $Memcached = &getMemcached($OrderRepository);
    $price = (int) Memcached\get($Memcached, createMemcachedKeyForPrice((int) $id));
    Memcached\delete($Memcached, (int) $id);

    return $price;
}

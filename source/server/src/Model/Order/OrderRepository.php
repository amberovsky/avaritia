<?
/**
 * @author amberovsky
 *
 * Репозиторий заказов
 */

namespace Avaritia\Model\Order\OrderRepository;

load('Avaritia\Library\Mysql\Mysql');

use Avaritia\Library\Mysql\Mysql;

// Константы именования таблиц/бд
const
    DATABASE_NAME   = '`Order`', // БД
    TABLE_NAME      = '`Order`'; // таблица

// Поля класса
const
    FIELD_MYSQL = 'Mysql'; // объект mysql

/**
 * @param array &$Mysql объект mysql
 *
 * @return &array объект репозитория заказа
 */
function &construct(array &$Mysql) {
    $OrderRepository = [
        FIELD_MYSQL => &$Mysql,
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
 * Создаёт новый заказ в БД
 *
 * @param array &$OrderRepository
 * @param int $price стоимость заказа
 * @param string $text текст заказа
 *
 * @return bool|resource
 */
function create(array &$OrderRepository, $price, $text) {
    return Mysql\query(
        getMysql($OrderRepository), '
        INSERT INTO ' . TABLE_NAME . '
            (price, text)
        VALUES
            (' . (int) $price . ', ' . mysql_real_escape_string($text) . ')'
    );
}

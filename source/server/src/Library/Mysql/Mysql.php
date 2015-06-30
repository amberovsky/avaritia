<?
/**
 * @author amberovsky
 *
 * Инстанс Mysql
 */

namespace Avaritia\Library\Mysql\Mysql;

load('Avaritia\Library\Mysql\MysqlFactory');

use Avaritia\Library\Mysql\MysqlFactory;

// Поля класса
const
    FIELD_MYSQL_LINK    = 'mysql_link', /** соедиение с mysql. Используется mysql_, потому что ООП нельзя  */
    FIELD_CONFIG        = 'config';

/**
 * @param array $config конфиг инстанса
 *
 * @return &array объект инстанса mysql
 */
function &construct(array $config) {
    $Mysql = [
        FIELD_CONFIG        => $config,
        FIELD_MYSQL_LINK    => null,
    ];

    return $Mysql;
}

/**
 * @param array $Mysql объект инстанса mysql
 *
 * @return array конфиг инстанса
 */
function getConfig(array $Mysql) {
    return $Mysql[FIELD_CONFIG];
}

/**
 * @private
 *
 * @param array $Mysql объект инстанса
 *
 * @return resource mysql link
 */
function getMysqlLink(array &$Mysql) {
    if (is_null($Mysql[FIELD_MYSQL_LINK])) {
        $config = getConfig($Mysql);

        $Mysql[FIELD_MYSQL_LINK] = mysql_connect(
            $config[MysqlFactory\CONFIGURATION_HOST] . ':' . $config[MysqlFactory\CONFIGURATION_PORT],
            $config[MysqlFactory\CONFIGURATION_USER],
            $config[MysqlFactory\CONFIGURATION_PASSWORD],
            true
        );

        if ($Mysql[FIELD_MYSQL_LINK] === false) {
            trigger_error('Ошибка соедения с mysql [' . mysql_error() . '] ' . print_r($config, true));
        }

        mysql_query('SET NAMES utf8', $Mysql[FIELD_MYSQL_LINK]);
        mysql_select_db($config[MysqlFactory\CONFIGURATION_DATABASE], $Mysql[FIELD_MYSQL_LINK]);
    }

    return $Mysql[FIELD_MYSQL_LINK];
}

/**
 * @link http://php.net/manual/ru/function.mysql-query.php
 *
 * @param array &$Mysql объект инстанса mysql
 * @param string $query sql запрос
 *
 * @return resource|bool результат выполниния
 */
function query(array &$Mysql, $query) {
    $mysqlLink = getMysqlLink($Mysql);

    $result = mysql_query($query, $mysqlLink);

    if ($result === false) {
        trigger_error('Ошибка запроса mysql. Код [' . mysql_errno($mysqlLink) . '], текст [' .
            mysql_error($mysqlLink) . ']');
    }

    return $result;
}

/**
 * @link http://php.net/manual/ru/function.mysql-fetch-assoc.php
 *
 * @param array &$Mysql объект инстанса mysql
 * @param resource $result результат mysql_query
 *
 * @return array|bool результат выполнения
 */
function fetchAssoc(array &$Mysql, $result) {
    return mysql_fetch_assoc($result);
}

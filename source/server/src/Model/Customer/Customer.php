<?
/**
 * @author amberovsky
 *
 * Модель заказчика
 */

namespace Avaritia\Model\Customer\Customer;

load('Avaritia\Library\Mysql\Mysql');

use Avaritia\Library\Mysql\Mysql;

// Ключи сериализации
const
    KEY_ID      = 0, // id
    KEY_LOGIN   = 1, // логин
    KEY_FIO     = 2; // фио

// Поля класса
const
    FIELD_ID    = 'id', // id
    FIELD_LOGIN = 'login', // логин
    FIELD_FIO   = 'fio'; // фио

/**
 * @return &array объект заказчика
 */
function &construct() {
    $Customer = [
        FIELD_ID    => -1,
        FIELD_LOGIN => '',
        FIELD_FIO   => '',
    ];

    return $Customer;
}

/**
 * @param array $Customer объект заказчика
 *
 * @return int id
 */
function getId(array $Customer) {
    return $Customer[FIELD_ID];
}

/**
 * @param array &$Customer объект заказчика
 * @param int $id id
 */
function setId(array &$Customer, $id) {
    $Customer[FIELD_ID] = (int) $id;
}

/**
 * @param array $Customer объект заказчика
 *
 * @return string логин
 */
function getLogin(array $Customer) {
    return $Customer[FIELD_LOGIN];
}

/**
 * @param array &$Customer объект заказчика
 * @param string $login логин
 */
function setLogin(array &$Customer, $login) {
    $Customer[FIELD_LOGIN] = $login;
}

/**
 * @param array $Customer объект заказчика
 *
 * @return string фио
 */
function getFio(array $Customer) {
    return $Customer[FIELD_FIO];
}

/**
 * @param array &$Customer объект заказчика
 * @param string $fio фио
 */
function setFio(array &$Customer, $fio) {
    $Customer[FIELD_FIO] = $fio;
}

/**
 * Создаёт заказчика по данным из мемкеша
 *
 * @param string $data сериализованное представление заказчика в мемкеше
 *
 * @return &array объект заказчика
 */
function &unserializeFromMemcache($data) {
    $Customer = construct();

    $data = json_decode($data);

    setId($Customer, $data[KEY_ID]);
    setLogin($Customer, $data[KEY_LOGIN]);
    setFio($Customer, $data[KEY_FIO]);

    return $Customer;
}

/**
 * @param array $Customer объект заказчика
 *
 * @return string сериализованное для мемкеша представление заказчика
 */
function serializeToMemcache(array $Customer) {
    return json_encode([
        KEY_ID      => getId($Customer),
        KEY_LOGIN   => getLogin($Customer),
        KEY_FIO     => getFio($Customer),
    ]);
}

/**
 * Создаёт заказчика по данным из mysql
 *
 * @param array $data данные из mysql
 *
 * @return &array объект заказчика
 */
function &unserializeFromMysql(array $data) {
    $Customer = &construct();

    setId($Customer, $data['id']);
    setLogin($Customer, $data['login']);
    setFio($Customer, $data['fio']);

    return $Customer;
}

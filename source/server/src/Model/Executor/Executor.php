<?
/**
 * @author amberovsky
 *
 * Объект исполнителя
 */

namespace Avaritia\Model\Executor\Executor;

// Поля класса
const
    FIELD_ID        = 'id', // id
    FIELD_LOGIN     = 'login', // логин
    FIELD_FIO       = 'fio', // фио
    FIELD_SALARY    = 'salary'; // зарплата

/**
 * @return &array объект исполнителя
 */
function &construct() {
    $Executor = [
        FIELD_ID        => -1,
        FIELD_LOGIN     => '',
        FIELD_FIO       => '',
        FIELD_SALARY    => 0,
    ];

    return $Executor;
}

/**
 * @param array $Executor объект исполнителя
 *
 * @return int id
 */
function getId(array $Executor) {
    return $Executor[FIELD_ID];
}

/**
 * @param array $Executor
 * @param int $id id
 */
function setId(array &$Executor, $id) {
    $Executor[FIELD_ID] = (int) $id;
}

/**
 * @param array $Executor объект исполнителя
 *
 * @return string фио
 */
function getFio(array $Executor) {
    return $Executor[FIELD_FIO];
}

/**
 * @param array &$Executor объект исполнителя
 * @param string $fio фио
 */
function setFio(array &$Executor, $fio) {
    $Executor[FIELD_FIO] = $fio;
}

/**
 * @param array $Executor объект исполнителя
 *
 * @return int зарплата
 */
function getSalary(array $Executor) {
    return $Executor[FIELD_SALARY];
}

/**
 * @param array &$Executor объект исполнителя
 * @param int $salary зарплата
 */
function setSalary(array &$Executor, $salary) {
    $Executor[FIELD_SALARY] = (int) $salary;
}

/**
 * @param array $Executor объект исполнителя
 *
 * @return string логин
 */
function getLogin(array $Executor) {
    return $Executor[FIELD_LOGIN];
}

/**
 * @param array &$Executor объект исполнителя
 * @param string $login логин
 */
function setLogin(array &$Executor, $login) {
    $Executor[FIELD_LOGIN] = $login;
}

/**
 * Создаёт исполнителя по данным из mysql
 *
 * @param array $data данные из mysql
 *
 * @return &array объект исполнителя
 */
function &unserializeFromMysql(array $data) {
    $Executor = &construct();

    setId($Executor, $data['id']);
    setFio($Executor, $data['fio']);
    setSalary($Executor, $data['salary']);
    setLogin($Executor, $data['login']);

    return $Executor;
}

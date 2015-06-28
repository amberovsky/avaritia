<?
/**
 * @author amberovsky
 *
 * Реализация ServiceLocator
 */

namespace Avaritia\Library\Framework\ServiceManager;

// Поля класса
const
    FIELD_DATA  = 'data'; /** список сервисов */

/**
 * @return array объект ServiceManager
 */
function construct() {
    return [
        FIELD_DATA => [],
    ];
}

/**
 * Установка значения
 *
 * @param array &$ServiceManager объект сервис-менеджера
 * @param string $name что сохраняем
 * @param mixed $value значение
 *
 * @return array объект сервис-менеджера
 */
function set(array &$ServiceManager, $name, $value) {
    $ServiceManager[FIELD_DATA][$name] = $value;

    return $ServiceManager;
}

/**
 * @param array $ServiceManager объект сервис-менеджера
 * @param string $name имя запрашиваемой сущности
 *
 * @return mixed
 */
function get(array $ServiceManager, $name) {
    if (isset($ServiceManager[FIELD_DATA][$name])) {
        return $ServiceManager[FIELD_DATA][$name];
    } else {
        trigger_error('Значение [' . $name . '] не найдено в сервис-менеджере', E_USER_ERROR);
    }
}

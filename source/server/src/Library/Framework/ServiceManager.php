<?
/**
 * @author amberovsky
 *
 * Реализация ServiceLocator
 */

namespace Avaritia\Library\Framework\ServiceManager;

/**
 * @return array объект ServiceManager
 */
function construct() {
    return [];
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
    $ServiceManager[$name] = $value;

    return $ServiceManager;
}

/**
 * @param array $ServiceManager объект сервис-менеджера
 * @param string $name имя запрашиваемой сущности
 *
 * @return mixed
 */
function get(array $ServiceManager, $name) {
    if (isset($ServiceManager[$name])) {
        return $ServiceManager[$name];
    } else {
        trigger_error('Значение [' . $name . '] не найдено в сервис-менеджере', E_USER_ERROR);
    }
}

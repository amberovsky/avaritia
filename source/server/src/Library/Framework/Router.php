<?
/**
 * @author amberovsky
 *
 * Роутинг
 */

namespace Avaritia\Library\Framework\Router;

// Поля класса
const
    FIELD_CONTROLLER_NAME   = 'controller', /** имя контроллера */
    FIELD_ACTION_NAME       = 'action'; /** имя экшена */

/**
 * @param array &$Config объект конфига
 * @param array &$Request объект запроса
 *
 * @return array объект роута
 */
function construct(array &$Config, array &$Request) {
    return [];
}

/**
 * Определяет роутинг по запросу
 *
 * @param array &$Router объект роута
 */
function match(array &$Router) {
    $Router[FIELD_CONTROLLER_NAME] = 'Index';
    $Router[FIELD_ACTION_NAME] = 'index';
}

/**
 * @param array $Router объект роута
 *
 * @return string имя контроллера
 */
function getControllerName(array $Router) {
    return $Router[FIELD_CONTROLLER_NAME];
}

/**
 * @param array $Router объект роута
 *
 * @return string имя экшена
 */
function getActionName(array $Router) {
    return $Router[FIELD_ACTION_NAME];
}

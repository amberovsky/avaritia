<?
/**
 * @author amberovsky
 *
 * Роутинг
 */

namespace Avaritia\Library\Framework\Router;

load('Avaritia\Library\Framework\Config');
load('Avaritia\Library\Framework\Application');
load('Avaritia\Library\Framework\Request');

use Avaritia\Library\Framework\Config;
use Avaritia\Library\Framework\Application;
use Avaritia\Library\Framework\Request;

// Константы конфигурации роута
const
    CONFIGURATION_SECTION           = 'router', /** определение секции конфигурирования роута */
    CONFIGURATION_CLI               = 'cli', /** список роутов для cli режима */
    CONFIGURATION_WEB               = 'web', /** список роутов для web режима */
    CONFIGURATION_PATTERN           = 'pattern', /** паттерн матчинга запроса */
    CONFIGURATION_CONTROLLER        = 'controller', /** в какой контроллер должен уходить роут */
    CONFIGURATION_DEFAULT_ACTION    = 'default_action'; /** экшен по умолчанию */

// Поля класса
const
    FIELD_CONTROLLER_NAME   = 'controller', /** имя контроллера */
    FIELD_ACTION_NAME       = 'action', /** имя экшена */
    FIELD_ROUTE_NAME        = 'route', /** какой роут был сматчен */
    FIELD_REQUEST           = 'Request', /** объект запроса */
    FIELD_CONFIG            = 'config', /** конфигурация роутинга */
    FIELD_APPLICATION       = 'Application'; /** объект приложения */

/**
 * @param array &$Config объект конфига
 * @param array &$Request объект запроса
 * @param array &$Application объект приложения
 *
 * @return &array объект роута
 */
function &construct(array &$Config, array &$Request, array &$Application) {
    $config = Config\get($Config, CONFIGURATION_SECTION);

    if (!isset($config[CONFIGURATION_WEB])) {
        trigger_error('Отсутствует конфигурация для web-режима', E_USER_ERROR);
    }

    if (!isset($config[CONFIGURATION_CLI])) {
        trigger_error('Отсутствует конфигурация для cli-режима', E_USER_ERROR);
    }

    foreach ($config[CONFIGURATION_WEB] as $name => $web) {
        if (!isset($web[CONFIGURATION_PATTERN])) {
            trigger_error(
                'Отсутствует параметр [' . CONFIGURATION_PATTERN . '] для web роута [' . $name . ']',
                E_USER_ERROR
            );
        }

        if (!isset($web[CONFIGURATION_CONTROLLER])) {
            trigger_error(
                'Отсутствует параметр [' . CONFIGURATION_CONTROLLER . '] для web роута [' . $name . ']',
                E_USER_ERROR
            );
        }

        if (!isset($web[CONFIGURATION_DEFAULT_ACTION])) {
            trigger_error(
                'Отсутствует параметр [' . CONFIGURATION_DEFAULT_ACTION . '] для web роута [' . $name . ']',
                E_USER_ERROR
            );
        }
    }

    // TODO валидация cli

    $Router = [
        FIELD_CONFIG            => $config,
        FIELD_REQUEST           => &$Request,
        FIELD_APPLICATION       => &$Application,
        FIELD_ACTION_NAME       => '',
        FIELD_CONTROLLER_NAME   => '',
        FIELD_ROUTE_NAME        => '',
    ];

    return $Router;
}

/**
 * @private
 *
 * @param array $Router объект роута
 *
 * @return &array объект запроса
 */
function &getRequest(array $Router) {
    return $Router[FIELD_REQUEST];
}

/**
 * @private
 *
 * @param array $Router объект роута
 *
 * @return array раздел конфигурации роута
 */
function getConfig(array $Router) {
    return $Router[FIELD_CONFIG];
}

/**
 * @private
 *
 * @param array $Router объект роута
 *
 * @return &array объект приложения
 */
function &getApplication(array $Router) {
    return $Router[FIELD_APPLICATION];
}

/**
 * Определяет роутинг по запросу
 *
 * @param array &$Router объект роута
 */
function match(array &$Router) {
    $Request = &getRequest($Router);

    switch (Application\getMode(getApplication($Router))) {
        case Application\MODE_CLI: // Запрос в cli-режиме
            if (Request\getArgc($Request) < 3) {
                trigger_error('Отсутствует имя скрипта', E_USER_ERROR);
            }

            $Router[FIELD_ROUTE_NAME] = 'script';
            setControllerName($Router, Request\getArgv($Request)[1]);
            setActionName($Router, 'run');
            break;

        case Application\MODE_WEB: // Запрос в web-режиме
            $documentUri = Request\getDocumentUri($Request);
            $matches = explode('/', trim(strtolower($documentUri), '/'));
            $count = count($matches);

            if (($count != 1) && ($count != 2)) {
                // TODO роутинг на страницу с ошибкой
                trigger_error('TODO', E_USER_ERROR);
            }

            $pattern = '/' . $matches[0];

            $found = false;

            // Проходим по списку роутов и матчим подходящий
            foreach (getConfig($Router)[CONFIGURATION_WEB] as $name => $data) {
                if ($data[CONFIGURATION_PATTERN] === $pattern) {
                    setControllerName($Router, $data[CONFIGURATION_CONTROLLER]);
                    setActionName($Router, ($count == 1) ? $data[CONFIGURATION_DEFAULT_ACTION] : $matches[1]);

                    $Router[FIELD_ROUTE_NAME] = $name;
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                header('HTTP/1.0 404 Not Found');
                exit();
            }

            break;
    }
}

/**
 * @param array &$Router объект роута
 * @param string $name имя сматченного контроллера
 */
function setControllerName(array &$Router, $name) {
    $Router[FIELD_CONTROLLER_NAME] = $name;
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
 * @param array &$Router объект роута
 * @param string $name имя сматченного экшена
 */
function setActionName(array &$Router, $name) {
    $Router[FIELD_ACTION_NAME] = $name;
}

/**
 * @param array $Router объект роута
 *
 * @return string имя экшена
 */
function getActionName(array $Router) {
    return $Router[FIELD_ACTION_NAME];
}

/**
 * @param array $Router объект роута
 *
 * @return string имя сматченного роута
 */
function getRouteName(array $Router) {
    return $Router[FIELD_ROUTE_NAME];
}

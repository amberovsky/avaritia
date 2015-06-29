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
        FIELD_REQUEST           => $Request,
        FIELD_APPLICATION       => $Application,
        FIELD_ACTION_NAME       => '',
        FIELD_CONTROLLER_NAME   => '',
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
    switch (Application\getMode(getApplication($Router))) {
        case Application\MODE_CLI:
            // TODO сделать матчинг cli режима
            trigger_error('TODO', E_USER_ERROR);
            break;

        case Application\MODE_WEB:
            $documentUri = Request\getDocumentUri(getRequest($Router));
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
                    $Router[FIELD_CONTROLLER_NAME] = $data[CONFIGURATION_CONTROLLER];
                    $Router[FIELD_ACTION_NAME] = ($count == 1) ? $data[CONFIGURATION_DEFAULT_ACTION] : $matches[1];

                    $found = true;
                    break;
                }
            }

            if (!$found) {
                // TODO роутинг на страниу ошибок
                trigger_error('TODO роутинг не найден [' . $documentUri . ']', E_USER_ERROR);
            }

            break;
    }
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

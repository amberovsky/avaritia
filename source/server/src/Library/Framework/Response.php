<?
/**
 * @author amberovsky
 *
 * HTTP ответ
 */

namespace Avaritia\Library\Framework\Response;

load('Avaritia\Library\Framework\Application');
load('Avaritia\Library\Framework\ServiceManager');
load('Avaritia\Library\Framework\View');

use Avaritia\Library\Framework\Application;
use Avaritia\Library\Framework\ServiceManager;
use Avaritia\Library\Framework\View;

// Поля класса
const
    FIELD_SERVICE_MANAGER   = 'Service_Manager'; // объект сервис-менеджера

/**
 * @param array &$ServiceManager объект сервис-менеджера
 *
 * @return &array объект ответа
 */
function &construct(array &$ServiceManager) {
    $Response = [
        FIELD_SERVICE_MANAGER   => &$ServiceManager,
    ];

    return $Response;
}

/**
 * @private
 *
 * @param array &$Response объект ответа
 *
 * @return &array объект сервис-менеджера
 */
function &getServiceManager(array &$Response) {
    return $Response[FIELD_SERVICE_MANAGER];
}

/**
 * @private
 *
 * @param array &$Response объект ответа
 *
 * @return string контент в web-режиме
 */
function toStringWeb(array &$Response) {
    $View = &ServiceManager\get(getServiceManager($Response), 'View');
    $render = View\render($View);

    if (View\getRenderStrategy($View) == View\RENDER_STRATEGY_JSON) {
        header('Content-Type: application/json; charset=utf-8');

        $render = json_encode(
            [
                'revision'  => REVISION,
                'response'  => $render,
            ],
            JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);

        if (($render === false) || (json_last_error() != JSON_ERROR_NONE)) {
            trigger_error(
                'Ошибка конвертации ответа в json. Код [' . json_last_error() . '], сообщение [' .
                json_last_error_msg() . ']',
                E_USER_ERROR
            );
        }

    } else {
        header('Content-Type: text/html; charset=utf-8');
    }

    return $render;
}

/**
 * @private
 *
 * @param array &$Response объект ответа
 *
 * @return string контент в cli-режиме
 */
function toStringCli(array &$Response) {
    // Весь вывод внутри скрипта происходит
}

/**
 * @param array &$Response объект ответа
 *
 * @return string контент клиенту
 */
function toString(array &$Response) {
    $Application = &ServiceManager\get(getServiceManager($Response), 'Application');

    switch (Application\getMode($Application)) {
        case Application\MODE_CLI:
            return toStringCli($Response);
            break;

        case Application\MODE_WEB:
            return toStringWeb($Response);
            break;

        default:
            trigger_error('Неизвестный режим запуска', E_USER_ERROR);
    }
}

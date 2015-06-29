<?
/**
 * @author amberovsky
 *
 * HTTP ответ
 */

namespace Avaritia\Library\Framework\Response;

load('Avaritia\Library\Framework\Application');
load('Avaritia\Library\Framework\View');

use Avaritia\Library\Framework\Application;
use Avaritia\Library\Framework\View;

/**
 * @return &array объект ответа
 */
function &construct() {
    $Response = [];

    return $Response;
}

/**
 * @private
 *
 * @param array &$Response объект ответа
 * @param array &$View объект отображения
 *
 * @return string контент в web-режиме
 */
function toStringWeb(array &$Response, array &$View) {
    return View\render($View);
}

/**
 * @private
 *
 * @param array &$Response объект ответа
 * @param array &$View объект отображения
 *
 * @return string контент в cli-режиме
 */
function toStringCli(array &$Response, array &$View) {
    // Выдаём как есть
    return View\render($View);
}

/**
 * @param array &$Response объект ответа
 * @param array &$Application объект приложения
 * @param array &$View объект отображения
 *
 * @return string контент клиенту
 */
function toString(array &$Response, array &$Application, array &$View) {
    switch (Application\getMode($Application)) {
        case Application\MODE_CLI:
            return toStringCli($Response, $View);
            break;

        case Application\MODE_WEB:
            return toStringWeb($Response, $View);
            break;

        default:
            trigger_error('Неизвестный режим запуска', E_USER_ERROR);
    }
}

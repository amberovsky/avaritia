<?
/**
 * @author amberovsky
 *
 * Исполнители
 */

namespace Avaritia\Controller\Executor;

load('Avaritia\Library\Framework\View');

use Avaritia\Library\Framework\View;

/**
 * @return &array объект контроллера
 */
function &construct() {
    $Executor = [];

    return $Executor;
}

/**
 * Стартовая страница
 *
 * @param array &$Controller объект контроллера
 *
 * @return &array объект отображения
 */
function &indexAction(array &$Controller) {
    $View = &View\construct();
    View\setTemplateName($View, 'executor\index');

    return $View;
}

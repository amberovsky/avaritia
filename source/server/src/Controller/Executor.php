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
 * @return array объект контроллера
 */
function construct() {
    return [];
}

/**
 * Стартовая страница
 *
 * @param array &$Controller объект контроллера
 *
 * @return array объект отображения
 */
function indexAction(array &$Controller) {
    return View\setTemplateName(View\construct(), 'executor\index');
}

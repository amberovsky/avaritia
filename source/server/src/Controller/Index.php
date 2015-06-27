<?
/**
 * @author amberovsky
 *
 * Стартовый контроллер сервиса
 */

namespace Avaritia\Controller\Index;

load('Avaritia\Library\Framework\View');

use Avaritia\Library\Framework\View;

/**
 * @return array объект контроллера
 */
function construct() {
    return [];
}

/**
 * Стартовая страница сервиса
 *
 * @param array &$Controller объект контроллера
 *
 * @return array объект отображения
 */
function indexAction(array &$Controller) {
    return View\setVariables(
        View\setTemplateName(View\construct(), 'index\index'),
        [
            'name'   => 'dude',
        ]);
}

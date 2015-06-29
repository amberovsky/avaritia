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
 * @return &array объект контроллера
 */
function &construct() {
    $Index = [];

    return $Index;
}

/**
 * Стартовая страница сервиса
 *
 * @param array &$Controller объект контроллера
 *
 * @return &array объект отображения
 */
function &indexAction(array &$Controller) {
    $View = &View\construct();
    View\setTemplateName($View, 'index\index');
    View\setVariables(
        $View,
        [
            'name'  => 'dude',
        ]
    );

    return $View;
}

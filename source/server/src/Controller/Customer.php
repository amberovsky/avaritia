<?
/**
 * @author amberovsky
 *
 * Заказчики
 */

namespace Avaritia\Controller\Customer;

load('Avaritia\Library\Framework\View');

use Avaritia\Library\Framework\View;

/**
 * @return &array объект контроллера
 */
function &construct() {
    $Customer = [];

    return $Customer;
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
    View\setTemplateName($View, 'customer\index');

    return $View;
}

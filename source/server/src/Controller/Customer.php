<?
/**
 * @author amberovsky
 *
 * Заказчики
 */

namespace Avaritia\Controller\Customer;

load('Avaritia\Library\Framework\View');
load('Avaritia\Library\Framework\ServiceManager');

use Avaritia\Library\Framework\View;
use Avaritia\Library\Framework\ServiceManager;

// Поля класса
const
FIELD_SERVICE_MANAGER   = 'Service_Manager', /** объект сервис-менеджер */
FIELD_REQUEST           = 'Request'; /** объект запроса */

/**
 * @param array &$ServiceManager объект сервис-менеджер
 *
 * @return &array объект контроллера
 */
function &construct(array &$ServiceManager) {
    $Request = &ServiceManager\get($ServiceManager, 'Request');
    $Script = [
        FIELD_REQUEST           => &$Request,
        FIELD_SERVICE_MANAGER   => &$ServiceManager,
    ];

    return $Script;
}

/**
 * @private
 *
 * @param array $Script объект контроллера
 *
 * @return &array объект сервис-менеджера
 */
function &getServiceManager(array $Script) {
    return $Script[FIELD_SERVICE_MANAGER];
}

/**
 * @private
 *
 * @param array $Script объект контроллера
 *
 * @return &array объект запроса
 */
function &getRequest(array $Script) {
    return $Script[FIELD_REQUEST];
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
    View\setVariables(
        $View,
        [
            'ActiveUser'    => ServiceManager\get(getServiceManager($Controller), 'ActiveUser')
        ]
    );

    return $View;
}

<?
/**
 * @author amberovsky
 *
 * Исполнители
 */

namespace Avaritia\Controller\Executor;

load('Avaritia\Library\Framework\View');
load('Avaritia\Library\Framework\ServiceManager');
load('Avaritia\Library\Framework\Request');
load('Avaritia\Model\Order\OrderRepository');
load('Avaritia\Model\Executor\Executor');
load('Avaritia\Model\Executor\ExecutorRepository');

use Avaritia\Library\Framework\View;
use Avaritia\Library\Framework\ServiceManager;
use Avaritia\Library\Framework\Request;
use Avaritia\Model\Order\OrderRepository;
use Avaritia\Model\Executor\Executor;
use Avaritia\Model\Executor\ExecutorRepository;

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
    $OrderRepository = &ServiceManager\get(getServiceManager($Controller), 'OrderRepository');

    $View = &View\construct();

    View\setTemplateName($View, 'executor\index');
    View\setVariables(
        $View,
        [
            'ActiveUser'    => ServiceManager\get(getServiceManager($Controller), 'ActiveUser'),
            'Orders'        => OrderRepository\fetchAll($OrderRepository),
        ]
    );

    return $View;
}

/**
 * Выполнение заказа
 *
 * @param array &$Controller объект контроллера
 *
 * @return array
 */
function cmdExecute(array &$Controller) {
    $Request = &getRequest($Controller);
    $ServiceManager = &getServiceManager($Controller);

    $orderId = (int) Request\getPostParam($Request, 'orderId');

    if ($orderId < 1) {
        return [
            'errorMsg'  => 'Неверный id заказа',
        ];
    }

    $OrderRepository = ServiceManager\get($ServiceManager, 'OrderRepository');
    if (!OrderRepository\deleteFromMysql($OrderRepository, $orderId)) {
        return [
            'errorMsg'  => 'Такого заказа (уже) нет',
        ];
    }

    $price = OrderRepository\getPriceFromMemcachedAndDeleteIt($OrderRepository, $orderId);
    $ExecutorRepository = ServiceManager\get($ServiceManager, 'ExecutorRepository');
    $ActiveUser = ServiceManager\get($ServiceManager, 'ActiveUser');
    ExecutorRepository\updateSalary($ExecutorRepository, $ActiveUser, $price);

    return [
        'salary'    => Executor\getSalary($ActiveUser),
    ];
}

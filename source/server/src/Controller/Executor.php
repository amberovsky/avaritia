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
load('Avaritia\Model\Order\Order');
load('Avaritia\Model\Executor\Executor');
load('Avaritia\Model\Executor\ExecutorRepository');
load('Avaritia\Library\Framework\Application');
load('Avaritia\Library\Session');

use Avaritia\Library\Framework\View;
use Avaritia\Library\Framework\ServiceManager;
use Avaritia\Library\Framework\Request;
use Avaritia\Model\Order\OrderRepository;
use Avaritia\Model\Order\Order;
use Avaritia\Model\Executor\Executor;
use Avaritia\Model\Executor\ExecutorRepository;
use Avaritia\Library\Framework\Application;
use Avaritia\Library\Session;

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
 * @param array &$Controller объект контроллера
 *
 * @return &array объект сервис-менеджера
 */
function &getServiceManager(array &$Controller) {
    return $Controller[FIELD_SERVICE_MANAGER];
}

/**
 * @private
 *
 * @param array &$Controller объект контроллера
 *
 * @return &array объект запроса
 */
function &getRequest(array &$Controller) {
    return $Controller[FIELD_REQUEST];
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
    View\setVariables(
        $View,
        [
            'ActiveUser'    => ServiceManager\get(getServiceManager($Controller), 'ActiveUser')
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
    $token = Request\getPostParam($Request, 'token');

    if (($orderId < 1) || is_null($token) || ($token !== Session\getToken())){
        return [
            'errorMsg'  => 'Неверный id заказа',
        ];
    }

    $OrderRepository = ServiceManager\get($ServiceManager, 'OrderRepository');
    if (!OrderRepository\deleteFromMysql($OrderRepository, $orderId)) {
        return [
            'errorMsg'  => 'Такого заказа (уже) нет',
            'deleted'   => 1,
        ];
    }

    $price = OrderRepository\getPriceFromMemcachedAndDeleteIt($OrderRepository, $orderId);
    $ExecutorRepository = ServiceManager\get($ServiceManager, 'ExecutorRepository');
    $ActiveUser = ServiceManager\get($ServiceManager, 'ActiveUser');
    $Application = ServiceManager\get($ServiceManager, 'Application');

    ExecutorRepository\updateSalary($ExecutorRepository, $ActiveUser, $price - Application\getCommission($Application));

    return [
        'salary'    => Executor\getSalary($ActiveUser),
    ];
}

/**
 * Подгрузка всех заказов
 *
 * @param array $Controller
 *
 * @return array сериализованный список заказов
 */
function cmdLoadOrders(array &$Controller) {
    $Request = &getRequest($Controller);
    $token = Request\getPostParam($Request, 'token');

    if (is_null($token) || ($token !== Session\getToken())) {
        return [
            'errorMsg'  => 'Неверный токен',
        ];
    }

    $OrderRepository = &ServiceManager\get(getServiceManager($Controller), 'OrderRepository');

    return array_map(function (array $Order) {
        return [
            'id'    => Order\getId($Order),
            'text'  => htmlspecialchars(Order\getText($Order), ENT_COMPAT | ENT_QUOTES),
            'price' => Order\getPrice($Order),
        ];
    }, OrderRepository\fetchAll($OrderRepository));
}

/**
 * Подгрузка новых заказов, если есть
 *
 * @param array &$Controller объект контроллера
 *
 * @return array сериализованный список заказов
 */
function cmdCheckNew(array &$Controller) {
    $Request = &getRequest($Controller);
    $token = Request\getPostParam($Request, 'token');
    $fromId = (int) Request\getPostParam($Request, 'fromId');

    if (($fromId < 1) || is_null($token) || ($token !== Session\getToken())) {
        return [
            'errorMsg'  => 'Неверные данные',
        ];
    }

    $OrderRepository = &ServiceManager\get(getServiceManager($Controller), 'OrderRepository');

    return array_map(function (array $Order) {
        return [
            'id'    => Order\getId($Order),
            'text'  => htmlspecialchars(Order\getText($Order), ENT_COMPAT | ENT_QUOTES),
            'price' => Order\getPrice($Order),
        ];
    }, OrderRepository\fetchWithIdOffset($OrderRepository, $fromId));
}

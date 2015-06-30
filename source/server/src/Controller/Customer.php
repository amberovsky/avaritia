<?
/**
 * @author amberovsky
 *
 * Заказчики
 */

namespace Avaritia\Controller\Customer;

load('Avaritia\Library\Framework\View');
load('Avaritia\Library\Framework\ServiceManager');
load('Avaritia\Library\Framework\Request');
load('Avaritia\Library\Mysql\MysqlFactory');
load('Avaritia\Model\Order\OrderRepository');

use Avaritia\Library\Framework\View;
use Avaritia\Library\Framework\ServiceManager;
use Avaritia\Library\Framework\Request;
use Avaritia\Library\Mysql\MysqlFactory;
use Avaritia\Model\Order\OrderRepository;

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

/**
 * Запрос на добавление заказа
 *
 * @param array &$Controller объект котнроллера
 *
 * @return array
 */
function cmdAdd(array &$Controller) {
    $Request = &getRequest($Controller);

    $price = (int) Request\getPostParam($Request, 'price');
    $text = Request\getPostParam($Request, 'text');

    if (($price < 1) || (mb_strlen($text) == 0) || (mb_strlen($text) > 1024)) {
        return [
            'error_msg' => 'Неверные данные',
        ];
    }

    $OrderRepository = &OrderRepository\construct(
        MysqlFactory\create(ServiceManager\getFactory(getServiceManager($Controller), 'Mysql'), 'order')
    );

    if (OrderRepository\create($OrderRepository, $price, $text) === false) {
        return [
            'error_msg' => 'Ошибка создания запроса',
        ];
    } else {
        return [];
    }
}

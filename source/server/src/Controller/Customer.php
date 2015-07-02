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
load('Avaritia\Model\Order\OrderRepository');
load('Avaritia\Library\Session');

use Avaritia\Library\Framework\View;
use Avaritia\Library\Framework\ServiceManager;
use Avaritia\Library\Framework\Request;
use Avaritia\Model\Order\OrderRepository;
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
 * Валидация данные в запросе добавления нового поста
 *
 * @param int $price стоимость
 * @param string $text текст
 * @param string $token токен
 *
 * @return bool|string true, если валидно, текст сообщения иначе
 */
function validateParams($price, $text, $token) {
    if ($price < 1) {
        return 'Стоимость должна быть больше 0';
    }

    if (mb_strlen($text) == 0) {
        return 'Текст не должен быть пуст';
    }

    if (mb_strlen($text) > 1024) {
        return 'Текст не может быть более 1024 символов';
    }

    if (is_null($token)) {
        return 'Не токена';
    }

    if ($token !== Session\getToken()) {
        return 'Неверный токен';
    }

    return true;
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
    $token = Request\getPostParam($Request, 'token');

    $isValid = validateParams($price, $text, $token);

    if ($isValid !== true) {
        return [
            'errorMsg' => $isValid,
        ];
    }

    $OrderRepository = &ServiceManager\get(getServiceManager($Controller), 'OrderRepository');

    if (OrderRepository\create($OrderRepository, $price, $text) === false) {
        return [
            'errorMsg' => 'Ошибка создания запроса',
        ];
    } else {
        return [];
    }
}

<?
/**
 * @author amberovsky
 *
 * Стартовый контроллер сервиса
 */

namespace Avaritia\Controller\Index;

load('Avaritia\Library\Framework\View');
load('Avaritia\Library\Framework\ServiceManager');
load('Avaritia\Library\Framework\Request');
load('Avaritia\Model\Customer\CustomerRepository');
load('Avaritia\Model\Executor\ExecutorRepository');
load('Avaritia\Library\Memcached\MemcachedFactory');
load('Avaritia\Library\Session');

use Avaritia\Library\Framework\ServiceManager;
use Avaritia\Library\Framework\Request;
use Avaritia\Library\Framework\View;
use Avaritia\Model\Customer\CustomerRepository;
use Avaritia\Model\Executor\ExecutorRepository;
use Avaritia\Library\Memcached\MemcachedFactory;
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
 * Стартовая страница сервиса
 *
 * @param array &$Controller объект контроллера
 *
 * @return &array объект отображения
 */
function &indexAction(array &$Controller) {
    $Request = &getRequest($Controller);

    $errorMsg = '';
    if (Request\isPost($Request)) {
        // Auth
        $login = Request\getPostParam($Request, 'login');
        $password = Request\getPostParam($Request, 'password');
        $loginAs = Request\getPostParam($Request, 'loginAs');

        if ((($loginAs !== 'customer') && ($loginAs !== 'executor')) || is_null($login) || is_null($password)) {
            $errorMsg = 'Неверные данные';
        } else {
            $ServiceManager = &getServiceManager($Controller);

            if ($loginAs == 'customer') { // Заказчик
                $CustomerRepository = &CustomerRepository\construct(
                    MemcachedFactory\create(ServiceManager\getFactory($ServiceManager, 'Memcached'), 'cache'),
                    ServiceManager\getFactory($ServiceManager, 'Mysql')
                );

                if (!CustomerRepository\validateAuth($CustomerRepository, $login, $password)) {
                    $errorMsg = 'Неверный логин/пароль';
                } else {
                    ServiceManager\set(
                        $ServiceManager,
                        'ActiveUser',
                        CustomerRepository\fetch($CustomerRepository, $login)
                    );

                    Session\setActiveUserData($login, 'customer');

                    header('Location: /customer', true, 307);
                    exit();
                }
            } else { // Исполнитель
                $ExecutorRepository = &ExecutorRepository\construct(
                    MemcachedFactory\create(ServiceManager\getFactory($ServiceManager, 'Memcached'), 'cache'),
                    ServiceManager\getFactory($ServiceManager, 'Mysql')
                );

                if (!ExecutorRepository\validateAuth($ExecutorRepository, $login, $password)) {
                    $errorMsg = 'Неверный логин/пароль';
                } else {
                    ServiceManager\set(
                        $ServiceManager,
                        'ActiveUser',
                        ExecutorRepository\fetch($ExecutorRepository, $login)
                    );

                    Session\setActiveUserData($login, 'executor');

                    header('Location: /executor', true, 307);
                    exit();
                }
            }
        }
    } else {
        $loginAs = 'customer';
    }

    $View = &View\construct();
    View\setTemplateName($View, 'index\index');
    View\setVariables(
        $View,
        [
            'errorMsg'  => $errorMsg,
            'loginAs'   => $loginAs,
        ]
    );

    return $View;
}

/**
 * Разлогинивание
 *
 * @param array &$Controller объект контроллера
 */
function logoutAction(array &$Controller) {
    $Request = &getRequest($Controller);

    if (Request\isPost($Request)) {
        $logout = Request\getPostParam($Request, 'logout');
        $token = Request\getPostParam($Request, 'token');

        if (!is_null($logout) && ($token === Session\getToken())) {
            Session\clearActiveUserData();
        }
    }

    header('Location: /index', true, 303);
    exit();
}

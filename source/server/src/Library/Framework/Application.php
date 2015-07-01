<?
/**
 * @author amberovsky
 *
 * Приложение
 */

namespace Avaritia\Library\Framework\Application;

load('Avaritia\Library\Framework\ServiceManager');
load('Avaritia\Library\Framework\Config');
load('Avaritia\Library\Framework\Request');
load('Avaritia\Library\Framework\Response');
load('Avaritia\Library\Framework\Router');
load('Avaritia\Library\Framework\View');
load('Avaritia\Library\Session');
load('Avaritia\Library\Memcached\MemcachedFactory');
load('Avaritia\Model\Customer\CustomerRepository');
load('Avaritia\Model\Executor\ExecutorRepository');

use Avaritia\Library\Framework\ServiceManager;
use Avaritia\Library\Framework\Config;
use Avaritia\Library\Framework\Request;
use Avaritia\Library\Framework\Response;
use Avaritia\Library\Framework\Router;
use Avaritia\Library\Framework\View;
use Avaritia\Library\Session;
use Avaritia\Library\Memcached\MemcachedFactory;
use Avaritia\Model\Customer\CustomerRepository;
use Avaritia\Model\Executor\ExecutorRepository;

// Режим запуска
const
    MODE_WEB    = 0, /** запрос от веб-сервера */
    MODE_CLI    = 1; /** консольный запрос */

// Поля класса
const
    FIELD_SERVICE_MANAGER   = 'Service_Manager', /** сервис-менеджер */
    FIELD_MODE              = 'mode'; /** режим запуска */

/**
 * @return &array объект приложения
 */
function &construct() {
    $Application = [];
    setMode($Application, (php_sapi_name() == 'cli') ? MODE_CLI : MODE_WEB);

    $ServiceManager = &ServiceManager\construct(Config\construct());
    ServiceManager\set($ServiceManager, 'Application', $Application);
    setServiceManager($Application, $ServiceManager);

    Session\init(MemcachedFactory\create(ServiceManager\getFactory($ServiceManager, 'Memcached'), 'session'));

    // Объект запроса
    $Request = &Request\construct();
    ServiceManager\set($ServiceManager, 'Request', $Request);

    // Объект ответа
    $Response = &Response\construct($ServiceManager);
    ServiceManager\set($ServiceManager, 'Response', $Response);

    return $Application;
}

/**
 * @param array &$Application объект приложения
 * @param int $mode режим запуска
 */
function setMode(array &$Application, $mode) {
    $Application[FIELD_MODE] = (int) $mode;
}

/**
 * @param array $Application объект отображения
 *
 * @return int режим запуска
 */
function getMode(array $Application) {
    return $Application[FIELD_MODE];
}

/**
 * @param array &$Application объект приложения
 * @param array &$ServiceManager объект сервис-менеджера
 */
function setServiceManager(array &$Application, array &$ServiceManager) {
    $Application[FIELD_SERVICE_MANAGER] = &$ServiceManager;
}

/**
 * @param array $Application объект приложения
 *
 * @return &array объект сервис-менеджера
 */
function &getServiceManager(array $Application) {
    return $Application[FIELD_SERVICE_MANAGER];
}

/**
 * Запуск приложения
 *
 * @param array &$Application
 */
function run(array &$Application) {
    $ServiceManager = &getServiceManager($Application);

    /**
     * У нас нет ни dispatch, ни forward, ни event, поэтому далее простой код
     */

    // Определим роутинг
    $Router = &Router\construct(
        ServiceManager\get($ServiceManager, 'Config'),
        ServiceManager\get($ServiceManager, 'Request'),
        $Application
    );
    Router\match($Router);

    $controllerName = Router\getControllerName($Router);
    $actionName = Router\getActionName($Router);
    $routeName = Router\getRouteName($Router);

    if (getMode($Application) === MODE_WEB) {
        if ($routeName === 'root') {
            header('Location: /index', true, 307);
            return;
        }

        $ActiveUser = null;
        $userData = Session\getActiveUserData();
        if (!is_null($userData)) {
            if ($userData[1] == 'customer') {
                // Заказчик
                if (($routeName !== 'customer') && (($routeName !== 'index') || ($actionName !== 'logout'))) {
                    header('Location: /customer', true, 307);
                    return;
                }

                $CustomerRepository = &CustomerRepository\construct(
                    MemcachedFactory\create(ServiceManager\getFactory($ServiceManager, 'Memcached'), 'cache'),
                    ServiceManager\getFactory($ServiceManager, 'Mysql')
                );

                $ActiveUser = &CustomerRepository\fetch($CustomerRepository, $userData[0]);
            } else {
                // Исполнитель
                if (($routeName !== 'executor') && (($routeName !== 'index') || ($actionName !== 'logout'))) {
                    header('Location: /executor', true, 307);
                    return;
                }

                $ExecutorRepository = &ExecutorRepository\construct(
                    MemcachedFactory\create(ServiceManager\getFactory($ServiceManager, 'Memcached'), 'cache'),
                    ServiceManager\getFactory($ServiceManager, 'Mysql')
                );

                $ActiveUser = &ExecutorRepository\fetch($ExecutorRepository, $userData[0]);

            }
        } else {
            if ($routeName !== 'index') {
                header('Location: /index', true, 307);
                return;
            }
        }
        ServiceManager\set($ServiceManager, 'ActiveUser', $ActiveUser);
    }

    // Создадим контроллер, вызовем action
    load('Avaritia\Controller\\' . $controllerName);
    $controllerNamespace = 'Avaritia\\Controller\\' . $controllerName . '\\';

    // У контроллера должен быть конструктор
    if (!function_exists($controllerNamespace . 'construct')) {
        trigger_error('У контроллера [' . $controllerName . '] отсутствует конструктор', E_USER_ERROR);
    }

    $Controller = &call_user_func_array($controllerNamespace . 'construct', [&$ServiceManager]);

    $Request = &ServiceManager\get($ServiceManager, 'Request');

    // Полное имя функции экшена для вызова
    $actionFunction = $controllerNamespace .
        (Request\isXmlHttpRequest($Request) ? ('cmd' . ucfirst($actionName)) : ($actionName . 'Action'));

    if (!function_exists($actionFunction)) {
        header('HTTP/1.0 404 Not Found');
        exit();
    }

    if (Request\isXmlHttpRequest($Request)) { // ajax запросы просто возвращают массив данных
        $View = &View\construct();
        View\setVariables($View, call_user_func_array($actionFunction, [&$Controller]));
    } else {
        $View = &call_user_func_array($actionFunction, [&$Controller]);
    }

    ServiceManager\set($ServiceManager, 'View', $View);

    echo Response\toString(ServiceManager\get($ServiceManager, 'Response'));
}

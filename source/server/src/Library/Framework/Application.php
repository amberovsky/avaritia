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

use Avaritia\Library\Framework\ServiceManager;
use Avaritia\Library\Framework\Config;
use Avaritia\Library\Framework\Request;
use Avaritia\Library\Framework\Response;
use Avaritia\Library\Framework\Router;
use Avaritia\Library\Framework\View;

// Режим запуска
const
    MODE_WEB    = 0, /** запрос от веб-сервера */
    MODE_CLI    = 1; /** консольный запрос */

// Поля класса
const
    FIELD_SERVICE_MANAGER   = 'service_manager', /** сервис-менеджер */
    FIELD_MODE              = 'mode'; /** режим запуска */

/**
 * @return array объект приложения
 */
function construct() {
    $Application = [];
    setMode($Application, (php_sapi_name() == 'cli') ? MODE_CLI : MODE_WEB);
    setServiceManager($Application, ServiceManager\construct());

    return $Application;
}

/**
 * @param array &$Application объект приложения
 * @param int $mode режим запуска
 *
 * @return array объект приложения
 */
function setMode(array &$Application, $mode) {
    $Application[FIELD_MODE] = (int) $mode;

    return $Application;
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
 *
 * @return array объект отображения
 */
function setServiceManager(array &$Application, array &$ServiceManager) {
    $Application[FIELD_SERVICE_MANAGER] = $ServiceManager;

    return $Application;
}

/**
 * @param array $Application объект приложения
 *
 * @return array объект сервис-менеджера
 */
function getServiceManager(array $Application) {
    return $Application[FIELD_SERVICE_MANAGER];
}

/**
 * Запуск приложения
 *
 * @param array &$Application
 */
function run(array &$Application) {
    $Config = Config\construct();
    Config\init($Config);

    $ServiceManager = getServiceManager($Application);

    ServiceManager\set($ServiceManager, 'Config', $Config);
    ServiceManager\set($ServiceManager, 'Application', $Application);

    setServiceManager($Application, $ServiceManager);

    /**
     * У нас нет ни dispatch, ни forward, ни event, поэтому далее простой код
     */

    // Объект запроса
    $Request = Request\construct();
    ServiceManager\set($ServiceManager, 'Request', $Request);

    // Определим роутинг
    $Router = Router\construct($Config, $Request, $Application);
    Router\match($Router);

    $controllerName = Router\getControllerName($Router);
    $actionName = Router\getActionName($Router);

    // Создадим контроллер, вызовем action
    require_once(PROJECT_SOURCE . '/Controller/' . $controllerName . '.php');
    $controllerNamespace = 'Avaritia\\Controller\\' . $controllerName . '\\';

    // У контроллера должен быть конструктор
    if (!function_exists($controllerNamespace . 'construct')) {
        trigger_error('У контроллера [' . $controllerName . '] отсутствует конструктор');
    }

    $Controller = call_user_func($controllerNamespace . 'construct');

    // Полное имя функции экшена для вызова
    $actionFunction = $controllerNamespace . $actionName . 'Action';
    if (!function_exists($actionFunction)) {
        trigger_error('У контроллера [' . $controllerName . '] отсутствует метод [' . $actionName . ']');
    }

    // & у $Controller абсолютно легален
    $View = call_user_func($actionFunction, [&$Controller]);

    // Объект ответа отрендерит и установит хедеры
    $Response = Response\construct();
    echo Response\toString($Response, $Application, $View);
}

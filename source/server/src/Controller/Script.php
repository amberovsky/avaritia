<?
/**
 * @author amberovsky
 *
 * Запуск консольных скриптов
 */

namespace Avaritia\Controller\Script;

load('Avaritia\Library\Framework\ServiceManager');
load('Avaritia\Library\Framework\Request');

use Avaritia\Library\Framework\ServiceManager;
use Avaritia\Library\Framework\Request;

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
 * Выполнение скрипта
 *
 * @param array &$Script объект контроллера
 */
function runAction(array &$Script) {
    $name = Request\getArgv(getRequest($Script))[2];

    load('Avaritia\Script\\' . $name);
    $ServiceManager = &getServiceManager($Script);

    call_user_func_array('Avaritia\\Script\\' . $name . '\\run', [&$ServiceManager]);
}

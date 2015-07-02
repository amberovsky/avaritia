<?
/**
 * @author amberovsky
 *
 * Сессии
 */

namespace Avaritia\Library\Session;

load('Avaritia\Library\Memcached\MemcachedFactory');
load('Avaritia\Library\Memcached\Memcached');

use Avaritia\Library\Memcached\MemcachedFactory;
use Avaritia\Library\Memcached\Memcached;

// Ключи хранения в сесии
const
    SESSION_KEY_DATA    = 'data', // данные в формате login:type
    SESSION_KEY_TOKEN   = 'token'; // токен

/**
 * Инициализация сессии
 *
 * @param array &$Memcached объект мемкеша
 */
function init(array &$Memcached) {
    $config = array_map(function (array $config) {
        return 'tcp://' . $config[MemcachedFactory\CONFIGURATION_HOST] . ':' .
        $config[MemcachedFactory\CONFIGURATION_PORT];
    }, Memcached\getPoolConfig($Memcached));

    ini_set('session.save_handler', 'memcache');
    ini_set('session.save_path', implode(',', $config));

    session_set_cookie_params(3600, '/', '', false, true);
    session_start();
}

/**
 * @static
 *
 * Обновление сессии
 */
function regenerate() {
    session_regenerate_id(true);
}

/**
 * @static
 *
 * @return array|null данные пользователя сессии в формате login:type
 */
function getActiveUserData() {
    if (isset($_SESSION[SESSION_KEY_DATA])) {
        return explode(':', $_SESSION[SESSION_KEY_DATA]);
    } else {
        return null;
    }
}

/**
 * @static
 *
 * Уничтожение сессии пользователя
 */
function clearActiveUserData() {
    session_unset();
    session_destroy();
}

/**
 * @static
 *
 * @return string текущий токен пользователя
 */
function getToken() {
    if (!isset($_SESSION[SESSION_KEY_TOKEN])) {
        $_SESSION[SESSION_KEY_TOKEN] = base64_encode(openssl_random_pseudo_bytes(32));
    }

    return $_SESSION[SESSION_KEY_TOKEN];
}

/**
 * Обновление данных пользователя (логин)
 *
 * @param string $login логин
 * @param string $type тип customer|executor
 */
function setActiveUserData($login, $type) {
    $_SESSION[SESSION_KEY_DATA] = $login . ':' . $type;
}

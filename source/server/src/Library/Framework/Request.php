<?
/**
 * @author amberovsky
 *
 * HTTP запрос
 */

namespace Avaritia\Library\Framework\Request;

// Метод HTTP запроса
const
    METHOD_GET  = 'GET', /** get */
    METHOD_POST = 'POST'; /** post */

// Поля класса
const
    FIELD_QUERY         = 'query', /** данные get запроса */
    FIELD_POST          = 'post', /** данные post запроса */
    FIELD_SERVER        = 'server', /** данные массива $_SERVER */
    FIELD_METHOD        = 'method', /** метод HTTP запроса */
    FIELD_DOCUMENT_URI  = 'request_uri'; /** document uri запроса */

/**
 * @return array объект запроса
 */
function construct() {
    $Request = [];

    setQuery($Request, isset($_GET) ? $_GET : []);
    setPost($Request, isset($_POST) ? $_POST : []);
    setServer($Request, $_SERVER);

    return $Request;
}

/**
 * Установка данных get запроса
 *
 * @param array &$Request объект запроса
 * @param array $query данные get-запроса
 */
function setQuery(array &$Request, array $query) {
    $Request[FIELD_QUERY] = $query;
}

/**
 * @param array &$Request объект запроса
 * @param string $name какую переменную get-запроса ищем
 * @param null  $default значение по умолчанию, если переменная запроса не найдена
 *
 * @return null|mixed значение переменной запроса
 */
function getQueryParam(array $Request, $name, $default = null) {
    return isset($Request[FIELD_QUERY][$name]) ? $Request[FIELD_QUERY][$name] : $default;
}

/**
 * Установка данных post запроса
 *
 * @param array &$Request объект запроса
 * @param array $post данные post-запроса
 */
function setPost(array &$Request, array $post) {
    $Request[FIELD_POST] = $post;
}

/**
 * @param array &$Request объект запроса
 * @param string $name какую переменную post-запроса ищем
 * @param null  $default значение по умолчанию, если переменная запроса не найдена
 *
 * @return null|mixed значение переменной запроса
 */
function getPostParam(array $Request, $name, $default = null) {
    return isset($Request[FIELD_POST][$name]) ? $Request[FIELD_POST][$name] : $default;
}

/**
 * @param array &$Request объект запроса
 * @param array $server данные массива $_SERVER
 */
function setServer(array &$Request, array $server) {
    $Request[FIELD_SERVER] = $server;
    $Request[FIELD_METHOD] = isset($server['REQUEST_METHOD']) ? strtoupper($server['REQUEST_METHOD']) : METHOD_GET;
    $Request[FIELD_DOCUMENT_URI] = isset($server['DOCUMENT_URI']) ? strtolower($server['DOCUMENT_URI']) : '';
}

/**
 * @param array $Request объект запроса
 *
 * @return array данные массива $_SERVER
 */
function getServer(array $Request) {
    return $Request[FIELD_SERVER];
}

/**
 * @param array $Request объект запроса
 *
 * @return bool является ли запрос - ajax
 */
function isXmlHttpRequest(array $Request) {
    $server = getServer($Request);

    return (
        isset($server['HTTP_X_REQUESTED_WITH']) &&
        (strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
    );
}

/**
 * @param array $Request объект запроса
 *
 * @return string метод HTTP запроса
 */
function getMethod(array $Request) {
    return $Request[FIELD_METHOD];
}

/**
 * @param array $Request объект запроса
 *
 * @return bool является ли запрос - POST
 */
function isPost(array $Request) {
    return (getMethod($Request) == 'POST');
}

/**
 * @param array $Request объект запроса
 *
 * @return bool является ли запрос - GET
 */
function isGet(array $Request) {
    return (getMethod($Request) == 'GET');
}

/**
 * @param array $Request объект запроса
 *
 * @return string document uri запроса
 */
function getDocumentUri(array $Request) {
    return $Request[FIELD_DOCUMENT_URI];
}

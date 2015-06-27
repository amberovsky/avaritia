<?
/**
 * @author amberovsky
 *
 * Бутстрапинг
 */

// Исходники серверной части проекта
define('PROJECT_SOURCE', __DIR__ . '/src');

// Конфигурации
define('PROJECT_CONFIGURATION', realpath(__DIR__ . '/../../configuration'));

// Мы живём в UTC
date_default_timezone_set('UTC');

// Все кодировки - в UTF-8
mb_internal_encoding('UTF-8');

// Время запуска
$timeStart = explode(' ', microtime());
$timeStart = [
    (int) $timeStart[1],
    (int) round($timeStart[0] * 10000)
];

// Id потока
define('THREAD_ID', $timeStart[0] . sprintf('%04d', $timeStart[1]) . mt_rand(100000, 999999));

// Проверка типа окружения
if (!isset($_SERVER['AVARITIA_ENVIRONMENT'])) {
    trigger_error('Не задано окружение AVARITIA_ENVIRONMENT', E_USER_ERROR);
}

define('ENVIRONMENT', $_SERVER['AVARITIA_ENVIRONMENT']);

$configurationDir = PROJECT_CONFIGURATION . '/' . ENVIRONMENT;
// Есть ли такой каталог с настройками
if (!is_dir($configurationDir)) {
    trigger_error('Нет такого каталога с настройками [' . $configurationDir . ']', E_USER_ERROR);
}

/**
 * Используется для подгрузки классов в неймспейсе.
 * После вызова разумно написать use чтобы не тащить гирлянду пути везде.
 * При отсутствии файла require_once выпадет в handle_error, так что ничего проверять не надо.
 *
 * @param string $namespace неймспейс с именем файла без расширения для загрузки с префиксом Avaritia
 *
 * @return string загруженный файл
 */
function load($namespace) {
    // Попутно поправим слеши и вырежем префикс Avaritia
    return require_once(PROJECT_SOURCE . '/' . mb_substr(str_replace('\\', '/', $namespace), 9) . '.php');
}

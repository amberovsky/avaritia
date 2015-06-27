<?
/**
 * @author amberovsky
 *
 * Точка входа
 */

/**
 * Обрабатывает фаталы
 */
ob_start(function ($buffer) {
    $error = error_get_last();

    if (
        !is_array($error) ||
        (
            ($error['type'] != E_ERROR) &&
            ($error['type'] != E_PARSE) &&
            ($error['type'] != E_CORE_ERROR) &&
            ($error['type'] != E_USER_ERROR) &&
            ($error['type'] != E_COMPILE_ERROR)
        )
    ) {
        return $buffer;
    }

    if (php_sapi_name() == 'cli') {
        return 'Ошибка' . PHP_EOL . print_r($error, true) . PHP_EOL . print_r(debug_backtrace(), true) . PHP_EOL;
    } else {
        http_response_code(500);

        /**
         * Выводим голый текст, потому что никаких сервисов нет
         */
        return
'<html>
    <head>
        <meta charset="utf-8">
    </head>
    <body>
        Непоправимая ошибка. Код #X1<br>
        Если через некоторое время она не исчезнет - сообщите, пожалуйста, нам об этом contact@avaritia
    </body>
</html>';
    }
});

/**
 * Определим уровень ошибок, на которых срабатываем
 */
define('ERROR_REPORTING', E_ALL ^ E_DEPRECATED ^ E_STRICT);
error_reporting(ERROR_REPORTING);

/**
 * Обработчик ошибок. Поймаем здесь нотисы, варнинги и т. д.
 *
 * @param int $no уровень ошибки
 * @param string $str сообщение ошибки
 * @param string $file файл с ошибкой
 * @param int $line строка в файле с ошибкой
 */
set_error_handler(function ($no, $str, $file, $line) {
    if (error_reporting() === 0) {
        // был использован '@'
        return false;
    }

    $errorMsg = 'Ошибка [' . $str . '] уровня [' . $no . '] файл [' . $file . '] строка [' . $line . ']' . PHP_EOL .
        print_r(debug_backtrace(), true) . PHP_EOL;

    // Логируем куда-нибудь безопасно
    error_log($errorMsg);

    if (php_sapi_name() == 'cli') {
        echo $errorMsg;
    } else {
        http_response_code(500);

        /**
         * Выводим голый текст из соображений стабильности
         */
        echo
'<html>
    <head>
        <meta charset="utf-8">
    </head>
    <body>
        Непоправимая ошибка. Код #X2<br>
        Если через некоторое время она не исчезнет - сообщите, пожалуйста, нам об этом contact@avaritia
    </body>
</html>';
    }

    die();
}, ERROR_REPORTING);

// Нормализация каталога - для cli
chdir(realpath(__DIR__));

// Грузим бутстрап
require_once(__DIR__ . '/../source/server/bootstrap.php');

load('Avaritia\Library\Framework\Application');

use Avaritia\Library\Framework\Application;

$Application = Application\construct();
Application\run($Application);

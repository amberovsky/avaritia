<?
/**
 * @author amberovsky
 *
 * Объект конфига, хранит в себе конфиги приложения
 */

namespace Avaritia\Library\Framework\Config;

// Поля класса
const
    FIELD_DATA  = 'data'; /** смерженные конфиги */

/**
 * @return array объект конфига
 */
function &construct() {
    $Config = [
        FIELD_DATA  => [],
    ];

    // Глобальные конфиги
    foreach (glob(PROJECT_CONFIGURATION . '/*.php') as $file) {
        $Config[FIELD_DATA] += require_once($file);
    }

    // Конфиги в зависимости от текущего окружения
    foreach (glob(PROJECT_CONFIGURATION . '/' . ENVIRONMENT . '/*.php') as $file) {
        $Config[FIELD_DATA] += require_once($file);
    }

    return $Config;
}

/**
 * @param array $Config объект конфига
 * @param string $item какой раздел конфига нужен
 *
 * @return &array раздел конфига
 */
function &get(array $Config, $item) {
    return $Config[FIELD_DATA][$item];
}

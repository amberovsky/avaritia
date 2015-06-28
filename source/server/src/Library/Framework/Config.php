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
function construct() {
    return [];
}

/**
 * @param array &$Config инициализация конфига
 */
function init(array &$Config) {
    $Config[FIELD_DATA] = [];

    foreach (glob(PROJECT_CONFIGURATION . '/' . ENVIRONMENT . '/*.php') as $file) {
        $Config[FIELD_DATA] += require_once($file);
    }
}

/**
 * @param array $Config объект конфига
 * @param string $item какой раздел конфига нужен
 *
 * @return array раздел конфига
 */
function get(array $Config, $item) {
    return $Config[FIELD_DATA][$item];
}

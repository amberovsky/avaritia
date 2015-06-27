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

    foreach (glob(PROJECT_CONFIGURATION . '/*.php') as $file) {
        $Config[FIELD_DATA] += require_once($file);
    }
}

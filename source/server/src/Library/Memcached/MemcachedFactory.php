<?
/**
 * @author amberovsky
 *
 * Фабрика мемкешей
 */

namespace Avaritia\Library\Memcached\MemcachedFactory;

load('Avaritia\Library\Framework\ServiceManager');
load('Avaritia\Library\Framework\Config');
load('Avaritia\Library\Memcached\Memcached');

use Avaritia\Library\Framework\ServiceManager;
use Avaritia\Library\Framework\Config;
use Avaritia\Library\Memcached\Memcached;

// Константы конфигурирования мемкеша
const
    CONFIGURATION_SECTION   = 'memcached', /** определение секции конфигурирования мемкеша */
    CONFIGURATION_HOST      = 'host', /** хост */
    CONFIGURATION_PORT      = 'port'; /** порт */

// Поля класса
const
    FIELD_INSTANCES = 'Instances', /** инстансы мемкеша */
    FIELD_CONFIG    = 'config'; /** конфиг мемкеша */

/**
 * @param array $ServiceManager объект сервис-менеджер
 *
 * @return &array объект фабрики
 */
function &construct(array $ServiceManager) {
    $config = &Config\get(ServiceManager\get($ServiceManager, 'Config'), CONFIGURATION_SECTION);

    foreach ($config as $name => $pool) {
        if (count($pool) == 0) {
            trigger_error('Пустой пул серверов для мемкеша [' . $name . ']', E_USER_ERROR);
        }

        foreach ($pool as $server) {
            if (!isset($server[CONFIGURATION_HOST])) {
                trigger_error(
                    'Отсутствует параметр [' . CONFIGURATION_HOST . '] для мемкеша [' . $name . ']',
                    E_USER_ERROR
                );
            }

            if (!isset($server[CONFIGURATION_PORT])) {
                trigger_error(
                    'Отсутствует параметр [' . CONFIGURATION_PORT . '] для мемкеша [' . $name . ']',
                    E_USER_ERROR
                );
            }
        }
    }

    $MemcachedFactory = [
        FIELD_INSTANCES => [],
        FIELD_CONFIG    => $config,
    ];

    return $MemcachedFactory;
}

/**
 * @private
 *
 * @param array $MemcachedFactory объект фабрики мемкешей
 *
 * @return &array текущие инстансы мемкешей
 */
function &getInstances(array $MemcachedFactory) {
    return $MemcachedFactory[FIELD_INSTANCES];
}

/**
 * @private
 *
 * @param array $MemcachedFactory объект фабрики мемкешей
 *
 * @return &array полный конфиг мемкешей
 */
function &getConfig(array $MemcachedFactory) {
    return $MemcachedFactory[FIELD_CONFIG];
}

/**
 * @param array &$MemcachedFactory объект фабрики мемкешей
 * @param string $name имя пула серверов из конфига
 *
 * @return &array инстанс мемкеша
 */
function &create(array &$MemcachedFactory, $name) {
    $Instances = &getInstances($MemcachedFactory);

    if (!isset($Instances[$name])) {
        $config = getConfig($MemcachedFactory);
        if (!isset($config[$name])) {
            trigger_error('Отсутствует конфигурация для мемкеша [' . $name . ']', E_USER_ERROR);
        }

        $Instances[$name] = &Memcached\construct($config[$name]);
    }

    return $Instances[$name];
}

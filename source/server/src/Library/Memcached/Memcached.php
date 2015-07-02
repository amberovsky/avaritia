<?
/**
 * @author amberovsky
 *
 * Memcache
 */

namespace Avaritia\Library\Memcached\Memcached;

load('Avaritia\Library\Memcached\MemcachedFactory');

use Avaritia\Library\Memcached\MemcachedFactory;

// Поля класса
const
    FIELD_MEMCACHE_OBJECT   = 'memcache_object', /** php объект мемкеша */
    FIELD_POOL              = 'pool'; /** конфигу пула серверов */

/**
 * @param array $pool конфигурация пула
 *
 * @return &array объект мемкеша с установленными серверами
 */
function &construct(array $pool) {
    $init = reset($pool);

    $memcache = memcache_connect(
        $init[MemcachedFactory\CONFIGURATION_HOST],
        $init[MemcachedFactory\CONFIGURATION_PORT]
    );

    if ($memcache === false) {
        trigger_error(
            'Ошибка memcache_connect ' . $init[MemcachedFactory\CONFIGURATION_HOST] . ':' .
                $init[MemcachedFactory\CONFIGURATION_PORT],
            E_USER_ERROR
        );
    }

    foreach (array_slice($pool, 1) as $server) {
        memcache_add_server(
            $memcache,
            $server[MemcachedFactory\CONFIGURATION_HOST],
            $server[MemcachedFactory\CONFIGURATION_PORT]
        );
    }

    $Memcached = [
        FIELD_POOL              => $pool,
        FIELD_MEMCACHE_OBJECT   => $memcache,
    ];

    return $Memcached;
}

/**
 * @private
 *
 * @param array &$Memcached объект мемкеша
 *
 * @return mixed php объект мемкеша
 */
function &getMemcacheObject(array &$Memcached) {
    return $Memcached[FIELD_MEMCACHE_OBJECT];
}

/**
 * @param array &$Memcached объект мемкеша
 *
 * @return array конфигурация пула
 */
function getPoolConfig(array &$Memcached) {
    return $Memcached[FIELD_POOL];
}

/**
 * @link http://php.net/manual/ru/memcache.set.php
 *
 * @param array &$Memcached объект мемкеша
 * @param string $key имя ключа сохранения
 * @param mixed $var значение ключа
 * @param int|null $flag см документацию
 * @param int $expire время жизни ключа
 *
 * @return bool результат операции
 */
function set(array &$Memcached, $key, $var, $flag = null, $expire = 0) {
    return memcache_set(getMemcacheObject($Memcached), $key, $var, $flag, $expire);
}

/**
 * @link http://php.net/manual/ru/memcache.get.php
 *
 * @param array &$Memcached объект мемкеша
 * @param string|array $keys ключ(и) для выборки
 * @param array|null $flags см документацию
 *
 * @return mixed значение ключа
 */
function get(array &$Memcached, $keys, array $flags = null) {
    return memcache_get(getMemcacheObject($Memcached), $keys, $flags);
}

/**
 * @see http://php.net/manual/ru/memcache.increment.php
 *
 * @param array &$Memcached объект мемкеша
 * @param string $key ключ инкремента
 * @param int   $value стартовое значение
 *
 * @return int|bool новое значение ключа или false при ошибке
 */
function increment(array &$Memcached, $key, $value = 1) {
    return memcache_increment(getMemcacheObject($Memcached), $key, $value);
}

/**
 * @see http://php.net/manual/ru/memcache.delete.php
 *
 * @param array &$Memcached объект мемкеша
 * @param string $key ключ на удаление
 *
 * @return bool результат операции
 */
function delete(array &$Memcached, $key) {
    return memcache_delete(getMemcacheObject($Memcached), $key);
}

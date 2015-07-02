<?
/**
 * @author amberovsky
 *
 * Memcached
 */

load('Avaritia\Library\Memcached\MemcachedFactory');

use Avaritia\Library\Memcached\MemcachedFactory;

return [
    MemcachedFactory\CONFIGURATION_SECTION  => [
        'lock'      => [  // Локи
            [
                MemcachedFactory\CONFIGURATION_HOST => '127.0.0.1',
                MemcachedFactory\CONFIGURATION_PORT => 11211,
            ],
        ],
        'cache'     => [  // Кеширование
            [
                MemcachedFactory\CONFIGURATION_HOST => '127.0.0.1',
                MemcachedFactory\CONFIGURATION_PORT => 11211,
            ],
        ],
        'session'   => [  // Сессии
            [
                MemcachedFactory\CONFIGURATION_HOST => '127.0.0.1',
                MemcachedFactory\CONFIGURATION_PORT => 11211,
            ],
        ],
    ],
];

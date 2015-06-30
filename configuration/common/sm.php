<?
/**
 * @author amberovsky
 *
 * Конфигурация сервис-менеджера
 */

load('Avaritia\Library\Framework\ServiceManager');

use Avaritia\Library\Framework\ServiceManager;

return [
    ServiceManager\CONFIGURATION_SECTION    => [
        ServiceManager\CONFIGURATION_FACTORIES  => [
            'Memcached' => 'Avaritia\Library\Memcached\MemcachedFactory',
            'Mysql'     => 'Avaritia\Library\Mysql\MysqlFactory',
        ],
    ],
];

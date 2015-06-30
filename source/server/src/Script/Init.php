<?
/**
 * @author amberovsky
 *
 * Инициализация системы. Запуск строго на выключенном web-сервере!
 */

namespace Avaritia\Script\Init;

load('Avaritia\Library\Framework\ServiceManager');
load('Avaritia\Library\Memcached\MemcachedFactory');
load('Avaritia\Library\Mysql\MysqlFactory');
load('Avaritia\Model\Customer\CustomerRepository');

use Avaritia\Library\Framework\ServiceManager;
use Avaritia\Library\Memcached\MemcachedFactory;
use Avaritia\Library\Mysql\MysqlFactory;
use Avaritia\Model\Customer\CustomerRepository;

/**
 * Запуск скрипта
 *
 * @param array &$ServiceManager объект сервис-менеджера
 */
function run(array &$ServiceManager) {
    $MemcachedFactory = ServiceManager\getFactory($ServiceManager, 'Memcached');
    $Memcached = MemcachedFactory\create($MemcachedFactory, 'cache');

    $MysqlFactory = ServiceManager\getFactory($ServiceManager, 'Mysql');
    $shardsConfig = MysqlFactory\getShardsConfig($MysqlFactory);

    // Заказчики
    $CustomerRepository = &CustomerRepository\construct($Memcached, $MysqlFactory);
    foreach ($shardsConfig[CustomerRepository\SHARD_CONFIG] as $shardId => $_) {
        CustomerRepository\createShard($CustomerRepository, $shardId);
    }

    CustomerRepository\syncLastCustomerId($CustomerRepository, 0);

    // Два тестовых заказчика
    CustomerRepository\create($CustomerRepository, 'customer_1', 'Петров', '8anbAw4BbuoM');
    CustomerRepository\create($CustomerRepository, 'customer_2', 'Васечкин', 'GZx5ixNwYtos');
}

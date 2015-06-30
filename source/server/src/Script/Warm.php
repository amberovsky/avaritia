<?
/**
 * @author amberovsky
 *
 * Прогрев кешей. Запуск строго на выключенном web-сервере!
 */

namespace Avaritia\Script\Warm;

load('Avaritia\Library\Framework\ServiceManager');
load('Avaritia\Library\Memcached\MemcachedFactory');
load('Avaritia\Library\Memcached\Memcached');
load('Avaritia\Library\Mysql\MysqlFactory');
load('Avaritia\Library\Mysql\Mysql');
load('Avaritia\Model\Customer\Customer');
load('Avaritia\Model\Customer\CustomerRepository');

use Avaritia\Library\Framework\ServiceManager;
use Avaritia\Library\Memcached\MemcachedFactory;
use Avaritia\Library\Memcached\Memcached;
use Avaritia\Library\Mysql\MysqlFactory;
use Avaritia\Library\Mysql\Mysql;
use Avaritia\Model\Customer\Customer;
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
    $maxCustomerId = 1;
    foreach ($shardsConfig[CustomerRepository\SHARD_CONFIG] as $shardId => $_) {
        $Mysql = MysqlFactory\createShard($MysqlFactory, CustomerRepository\SHARD_CONFIG, $shardId);

        $data = Mysql\query($Mysql, 'SELECT * FROM ' . CustomerRepository\TABLE_NAME);
        while (($customerData = Mysql\fetchAssoc($Mysql, $data)) !== false) {
            $Customer = Customer\unserializeFromMysql($customerData);
            CustomerRepository\saveToMemcached($CustomerRepository, $Customer);
            CustomerRepository\savePasswordHashToMemcached(
                $CustomerRepository,
                $Customer,
                $customerData['password_hash']
            );
            $maxCustomerId = max($maxCustomerId, Customer\getId($Customer));
        }
    }
    CustomerRepository\syncLastCustomerId($CustomerRepository, $maxCustomerId);
}

<?
/**
 * @author amberovsky
 *
 * Прогрев кешей. Запуск строго на выключенном web-сервере!
 */

namespace Avaritia\Script\Warm;

load('Avaritia\Library\Framework\ServiceManager');
load('Avaritia\Library\Mysql\MysqlFactory');
load('Avaritia\Library\Mysql\Mysql');
load('Avaritia\Model\Customer\Customer');
load('Avaritia\Model\Customer\CustomerRepository');
load('Avaritia\Model\Executor\Executor');
load('Avaritia\Model\Executor\ExecutorRepository');
load('Avaritia\Model\Order\Order');
load('Avaritia\Model\Order\OrderRepository');

use Avaritia\Library\Framework\ServiceManager;
use Avaritia\Library\Mysql\MysqlFactory;
use Avaritia\Library\Mysql\Mysql;
use Avaritia\Model\Customer\Customer;
use Avaritia\Model\Customer\CustomerRepository;
use Avaritia\Model\Executor\Executor;
use Avaritia\Model\Executor\ExecutorRepository;
use Avaritia\Model\Order\Order;
use Avaritia\Model\Order\OrderRepository;

/**
 * Запуск скрипта
 *
 * @param array &$ServiceManager объект сервис-менеджера
 */
function run(array &$ServiceManager) {
    $MysqlFactory = &ServiceManager\getFactory($ServiceManager, 'Mysql');
    $shardsConfig = MysqlFactory\getShardsConfig($MysqlFactory);

    // Заказчики
    $CustomerRepository = &ServiceManager\get($ServiceManager, 'CustomerRepository');
    $maxCustomerId = 1;
    foreach ($shardsConfig[CustomerRepository\SHARD_CONFIG] as $shardId => $_) {
        $Mysql = MysqlFactory\createShard($MysqlFactory, CustomerRepository\SHARD_CONFIG, $shardId);

        $data = Mysql\query(
            $Mysql,
            'SELECT * FROM ' . CustomerRepository\DATABASE_NAME . '.' . CustomerRepository\TABLE_NAME
        );

        while (($customerData = Mysql\fetchAssoc($Mysql, $data)) !== false) {
            $Customer = &Customer\unserializeFromMysql($customerData);
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

    // Исполнители
    $ExecutorRepository = &ServiceManager\get($ServiceManager, 'ExecutorRepository');
    $maxExecutorId = 1;
    foreach ($shardsConfig[ExecutorRepository\SHARD_CONFIG] as $shardId => $_) {
        $Mysql = &MysqlFactory\createShard($MysqlFactory, ExecutorRepository\SHARD_CONFIG, $shardId);

        $data = Mysql\query(
            $Mysql,
            'SELECT * FROM ' . ExecutorRepository\TABLE_NAME . '.' . ExecutorRepository\TABLE_NAME
        );

        while (($executorData = Mysql\fetchAssoc($Mysql, $data)) !== false) {
            $Executor = &Executor\unserializeFromMysql($executorData);
            ExecutorRepository\saveLoginToMemcached(
                $ExecutorRepository,
                Executor\getLogin($Executor),
                Executor\getId($Executor)
            );

            ExecutorRepository\savePasswordHashToMemcached(
                $ExecutorRepository,
                $Executor,
                $executorData['password_hash']
            );
            $maxExecutorId = max($maxExecutorId, Executor\getId($Executor));
        }
    }
    ExecutorRepository\syncLastExecutorId($ExecutorRepository, $maxExecutorId);

    // Заказы
    $OrderRepository = &ServiceManager\get($ServiceManager, 'OrderRepository');
    foreach (OrderRepository\fetchAll($OrderRepository) as $Order) {
        OrderRepository\savePriceToMemcached($OrderRepository, Order\getPrice($Order));
    }
}

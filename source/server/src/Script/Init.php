<?
/**
 * @author amberovsky
 *
 * Инициализация системы. Запуск строго на выключенном web-сервере!
 */

namespace Avaritia\Script\Init;

load('Avaritia\Library\Framework\ServiceManager');
load('Avaritia\Library\Mysql\MysqlFactory');
load('Avaritia\Model\Customer\CustomerRepository');
load('Avaritia\Model\Executor\ExecutorRepository');
load('Avaritia\Model\Order\OrderRepository');

use Avaritia\Library\Framework\ServiceManager;
use Avaritia\Library\Mysql\MysqlFactory;
use Avaritia\Model\Customer\CustomerRepository;
use Avaritia\Model\Executor\ExecutorRepository;
use Avaritia\Model\Order\OrderRepository;

/**
 * Запуск скрипта
 *
 * @param array &$ServiceManager объект сервис-менеджера
 */
function run(array &$ServiceManager) {
    $shardsConfig = MysqlFactory\getShardsConfig(ServiceManager\getFactory($ServiceManager, 'Mysql'));

    // Заказчики
    $CustomerRepository = &ServiceManager\get($ServiceManager, 'CustomerRepository');
    foreach ($shardsConfig[CustomerRepository\SHARD_CONFIG] as $shardId => $_) {
        CustomerRepository\createShard($CustomerRepository, $shardId);
    }

    CustomerRepository\syncLastCustomerId($CustomerRepository, 0);

    // Два тестовых заказчика
    CustomerRepository\create($CustomerRepository, 'customer_1', 'Петров', '8anbAw4BbuoM');
    CustomerRepository\create($CustomerRepository, 'customer_2', 'Васечкин', 'GZx5ixNwYtos');


    // Заказы
    $OrderRepository = &ServiceManager\get($ServiceManager, 'OrderRepository');

    OrderRepository\createDatabaseAndTable($OrderRepository);

    // Два тестовых заказа
    OrderRepository\create($OrderRepository, 'text', 100);
    OrderRepository\create($OrderRepository, 'text2', 200);


    // Исполнители
    $ExecutorRepository = &ServiceManager\get($ServiceManager, 'ExecutorRepository');
    foreach ($shardsConfig[ExecutorRepository\SHARD_CONFIG] as $shardId => $_) {
        ExecutorRepository\createShard($ExecutorRepository, $shardId);
    }

    ExecutorRepository\syncLastExecutorId($ExecutorRepository, 0);

    // Два тестовых исполнителя
    ExecutorRepository\create($ExecutorRepository, 'executor_1', 'Иванов', 100, '3urvrPhNvEpZ');
    ExecutorRepository\create($ExecutorRepository, 'executor_2', 'Церетели', 200, 'ki22YIk1FR29');
}

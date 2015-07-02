<?
/**
 * @author amberovsky
 *
 * Mysql
 *
 * Конфигурировать можно как одтельные инстансы, так и шарды
 */

load('Avaritia\Library\Mysql\MysqlFactory');
load('Avaritia\Model\Customer\CustomerRepository');
load('Avaritia\Model\Executor\ExecutorRepository');
load('Avaritia\Model\Order\OrderRepository');

use Avaritia\Library\Mysql\MysqlFactory;
use Avaritia\Model\Customer\CustomerRepository;
use Avaritia\Model\Executor\ExecutorRepository;
use Avaritia\Model\Order\OrderRepository;

return [
    MysqlFactory\CONFIGURATION_SECTION  => [
        MysqlFactory\CONFIGURATION_INSTANCES        => [
            'order' => [
                MysqlFactory\CONFIGURATION_HOST     => 'localhost',
                MysqlFactory\CONFIGURATION_PORT     => 3306,
                MysqlFactory\CONFIGURATION_USER     => 'avaritia',
                MysqlFactory\CONFIGURATION_PASSWORD => 'pVduQNeMPUKZ',
                MysqlFactory\CONFIGURATION_DATABASE => OrderRepository\DATABASE_NAME,
            ]
        ],

        MysqlFactory\CONFIGURATION_SHARD_INSTANCES  => [
            CustomerRepository\SHARD_CONFIG => [
                0   => [
                    MysqlFactory\CONFIGURATION_HOST     => 'localhost',
                    MysqlFactory\CONFIGURATION_PORT     => 3306,
                    MysqlFactory\CONFIGURATION_USER     => 'avaritia',
                    MysqlFactory\CONFIGURATION_PASSWORD => 'pVduQNeMPUKZ',
                    MysqlFactory\CONFIGURATION_DATABASE => CustomerRepository\DATABASE_NAME,
                ],
            ],

            ExecutorRepository\SHARD_CONFIG => [
                0   => [
                    MysqlFactory\CONFIGURATION_HOST     => 'localhost',
                    MysqlFactory\CONFIGURATION_PORT     => 3306,
                    MysqlFactory\CONFIGURATION_USER     => 'avaritia',
                    MysqlFactory\CONFIGURATION_PASSWORD => 'pVduQNeMPUKZ',
                    MysqlFactory\CONFIGURATION_DATABASE => ExecutorRepository\DATABASE_NAME,
                ],
            ],
        ],
    ],
];

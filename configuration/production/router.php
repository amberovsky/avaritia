<?
/**
 * @author amberovsky
 *
 * Роутинг
 * Шаблон роута может быть либо "/" либо "/controller"
 */

load('Avaritia\Library\Framework\Router');

use Avaritia\Library\Framework\Router;

return [
    Router\CONFIGURATION_SECTION    => [
        Router\CONFIGURATION_CLI    => [

        ],

        Router\CONFIGURATION_WEB    => [
            'index' => [ // Стартовая страница
                Router\CONFIGURATION_PATTERN        => '/',
                Router\CONFIGURATION_CONTROLLER     => 'Index',
                Router\CONFIGURATION_DEFAULT_ACTION => 'index',
            ],

            'customer'  => [ // Заказчики
                Router\CONFIGURATION_PATTERN        => '/customer',
                Router\CONFIGURATION_CONTROLLER     => 'Customer',
                Router\CONFIGURATION_DEFAULT_ACTION => 'index',
            ],

            'executor'  => [ // Исполнители
                Router\CONFIGURATION_PATTERN        => '/executor',
                Router\CONFIGURATION_CONTROLLER     => 'Executor',
                Router\CONFIGURATION_DEFAULT_ACTION => 'index',
            ],
        ],
    ],
];

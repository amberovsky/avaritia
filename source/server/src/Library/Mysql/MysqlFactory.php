<?
/**
 * @author amberovsky
 *
 * Фабрика Mysql
 */

namespace Avaritia\Library\Mysql\MysqlFactory;

load('Avaritia\Library\Framework\ServiceManager');
load('Avaritia\Library\Framework\Config');
load('Avaritia\Library\Mysql\Mysql');

use Avaritia\Library\Framework\ServiceManager;
use Avaritia\Library\Framework\Config;
use Avaritia\Library\Mysql\Mysql;

// Константы конфигурирования mysql
const
    CONFIGURATION_SECTION           = 'mysql', /** определение секции конфигурирования mysql */
    CONFIGURATION_INSTANCES         = 'instances', /** список не-шардовых инстансов */
    CONFIGURATION_SHARD_INSTANCES   = 'shard_instances', /** список шардовых инстансов */
    CONFIGURATION_HOST              = 'host', /** хост */
    CONFIGURATION_PORT              = 'port', /** порт */
    CONFIGURATION_USER              = 'user', /** логин */
    CONFIGURATION_PASSWORD          = 'password', /** пароль */
    CONFIGURATION_DATABASE          = 'database'; /** имя бд */

// Поля класса
const
    FIELD_INSTANCES         = 'Instances', /** инстансы mysql */
    FIELD_SHARD_INSTANCES   = 'Shard_Instances', /** шардовые инстансы mysql */
    FIELD_CONFIG            = 'config'; /** конфиг mysql */

/**
 * @param array &$ServiceManager объект сервис-менеджер
 *
 * @return &array объект фабрики
 */
function &construct(array &$ServiceManager) {
    $config = &Config\get(ServiceManager\get($ServiceManager, 'Config'), CONFIGURATION_SECTION);

    if (isset($config[CONFIGURATION_INSTANCES])) {
        foreach ($config[CONFIGURATION_INSTANCES] as $name => $configuration) {
            validateInstanceConfig($name, $configuration);
        }
    }

    if (isset($config[CONFIGURATION_SHARD_INSTANCES])) {
        foreach ($config[CONFIGURATION_SHARD_INSTANCES] as $name => $configuration) {
            if (!is_array($configuration) || (count($configuration) == 0)) {
                trigger_error('Пустой конфиг для mysql шард-инстанса [' . $name . ']');
            }

            foreach ($configuration as $shard => $instance) {
                validateInstanceConfig($name, $instance, $shard);
            }
        }
    }

    $MysqlFactory = [
        FIELD_INSTANCES         => [],
        FIELD_SHARD_INSTANCES   => [],
        FIELD_CONFIG            => $config,
    ];

    return $MysqlFactory;
}

/**
 * Валидация строки конфига инстанса
 *
 * @param array $config конфиг истанса
 * @param string $field наименование строки инстанса
 * @param string $name имя инстанса
 * @param string $shardSuffix доп информация в логи в случае ошибки
 */
function validateInstanceConfigField(array $config, $field, $name, $shardSuffix) {
    if (!isset($config[$field])) {
        trigger_error('Отсутствует параметр [' . $field . '] для mysql [' . $name . ']' . $shardSuffix);
    }

}

/**
 * Валидация конфига истанса
 *
 * @param string $name имя инстанса
 * @param array|mixed $config конфиг инстанса
 * @param string|null $shard имя шарда, если шардовый конфиг
 */
function validateInstanceConfig($name, $config, $shard = null) {
    $shardSuffix = is_null($shard) ? '' : (' шарда [' . $shard . ']');

    if (!is_array($config) || (count($config) == 0)) {
        trigger_error('Пустой конфиг для mysql инстанса [' . $name . ']' . $shardSuffix);
    }

    validateInstanceConfigField($config, CONFIGURATION_HOST, $name, $shardSuffix);
    validateInstanceConfigField($config, CONFIGURATION_PORT, $name, $shardSuffix);
    validateInstanceConfigField($config, CONFIGURATION_USER, $name, $shardSuffix);
    validateInstanceConfigField($config, CONFIGURATION_PASSWORD, $name, $shardSuffix);
    validateInstanceConfigField($config, CONFIGURATION_DATABASE, $name, $shardSuffix);
}

/**
 * @private
 *
 * @param array &$MysqlFactory объект фабрики mysql
 *
 * @return &array текущие инстансы mysql
 */
function &getInstances(array &$MysqlFactory) {
    return $MysqlFactory[FIELD_INSTANCES];
}

/**
 * @private
 *
 * @param array &$MysqlFactory объект фабрики mysql
 *
 * @return &array текущие шардовые инстансы mysql
 */
function &getShardInstances(array &$MysqlFactory) {
    return $MysqlFactory[FIELD_SHARD_INSTANCES];
}

/**
 * @private
 *
 * @param array &$MysqlFactory объект фабрики mysql
 *
 * @return &array полный конфиг mysql
 */
function &getConfig(array &$MysqlFactory) {
    return $MysqlFactory[FIELD_CONFIG];
}

/**
 * @param array &$MysqlFactory объект фабрики mysql
 * @param string $name имя инстанса из конфига
 *
 * @return &array инстанс mysql
 */
function &create(array &$MysqlFactory, $name) {
    $Instances = &getInstances($MysqlFactory);

    if (!isset($Instances[$name])) {
        $config = getConfig($MysqlFactory);
        if (!isset($config[CONFIGURATION_INSTANCES][$name])) {
            trigger_error('Отсутствет конфигурация для mysql [' . $name . ']', E_USER_ERROR);
        }

        $Instances[$name] = &Mysql\construct($config[CONFIGURATION_INSTANCES][$name]);
    }

    return $Instances[$name];
}

/**
 * @param array &$MysqlFactory объект фабрики mysql
 *
 * @return array конфигурация шардов
 */
function getShardsConfig(array &$MysqlFactory) {
    return getConfig($MysqlFactory)[CONFIGURATION_SHARD_INSTANCES];
}

/**
 * @param array $MysqlFactory
 * @param string $name имя шардового инстанса
 * @param int $shardId id шарда
 *
 * @return &array объект mysql шардового инстанса
 */
function &createShard(array &$MysqlFactory, $name, $shardId) {
    $ShardInstances = &getShardInstances($MysqlFactory);
    $shardsConfig = getShardsConfig($MysqlFactory);

    if (!isset($ShardInstances[$name][$shardId])) {
        if (!isset($shardsConfig[$name][$shardId])) {
            trigger_error(
                'Отсутствет шардовая конфигурация [' . $shardId . '] для mysql [' . $name . ']',
                E_USER_ERROR
            );
        }

        $ShardInstances[$name][$shardId] = &Mysql\construct($shardsConfig[$name][$shardId]);
    }

    return $ShardInstances[$name][$shardId];
}

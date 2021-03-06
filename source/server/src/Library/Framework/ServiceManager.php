<?
/**
 * @author amberovsky
 *
 * Реализация ServiceLocator
 */

namespace Avaritia\Library\Framework\ServiceManager;

load('Avaritia\Library\Framework\Config');

use Avaritia\Library\Framework\Config;

// Константы конфигурирования сервис-менеджера
const
    CONFIGURATION_SECTION   = 'service_manager', /** определение секции конфигурирования сервис-менеджера */
    CONFIGURATION_FACTORIES = 'factories', /** список фабрик */
    CONFIGURATION_INVOKABLE = 'invokable'; /** список callback для создание объектов */

// Поля класса
const
    FIELD_CONFIG    = 'config', /** конфигурация */
    FIELD_DATA      = 'data', /** список сервисов */
    FIELD_FACTORIES = 'factories', /** список фабрик */
    FIELD_INVOKABLE = 'invokable'; /** список callback */

/**
 * @param array &$Config объект конфига
 *
 * @return &array объект ServiceManager
 */
function &construct(array &$Config) {
    $smConfig = &Config\get($Config, CONFIGURATION_SECTION);

    if (!isset($smConfig[CONFIGURATION_FACTORIES])) {
        trigger_error('Нет списка фабрик', E_USER_ERROR);
    }

    $ServiceManager = [
        FIELD_CONFIG    => $smConfig,
        FIELD_DATA      => [
            'Config'    => &$Config,
        ],
        FIELD_FACTORIES => [],
        FIELD_INVOKABLE => [],
    ];

    return $ServiceManager;
}

/**
 * @private
 *
 * @param array &$ServiceManager объект сервис-менеджера
 *
 * @return array конфигурация сервис-менеджера
 */
function getConfig(array &$ServiceManager) {
    return $ServiceManager[FIELD_CONFIG];
}

/**
 * Установка значения
 *
 * @param array &$ServiceManager объект сервис-менеджера
 * @param string $name что сохраняем
 * @param mixed &$value значение
 */
function set(array &$ServiceManager, $name, &$value) {
    $ServiceManager[FIELD_DATA][$name] = &$value;
}

/**
 * Установка invokable значения
 *
 * @param array &$ServiceManager объект сервис-менеджера
 * @param string $name что сохраняем
 * @param callable &$callback функция создания объекта
 */
function setInvokable(array &$ServiceManager, $name, callable $callback) {
    $ServiceManager[FIELD_INVOKABLE][$name] = $callback;
}

/**
 * @param array &$ServiceManager объект сервис-менеджера
 * @param string $name имя запрашиваемой сущности
 *
 * @return &array|null сущность
 */
function &get(array &$ServiceManager, $name) {
    if (isset($ServiceManager[FIELD_DATA][$name])) {
        return $ServiceManager[FIELD_DATA][$name];
    }

    if (isset($ServiceManager[FIELD_INVOKABLE][$name])) {
        $Object = &$ServiceManager[FIELD_INVOKABLE][$name]($ServiceManager);
        set($ServiceManager, $name, $Object);
        return $ServiceManager[FIELD_DATA][$name];
    }

    $Object = null;
    return $Object;
}

/**
 * Ищет фабрику в списке инициализированных. Если таковой нет, то ищется в конфиге и инициализируется
 *
 * @param array &$ServiceManager объект сервис-менеджера
 * @param string $name каккую фабрику ищем
 *
 * @return &array фабрика
 */
function &getFactory(array &$ServiceManager, $name) {
    if (!isset($ServiceManager[FIELD_FACTORIES][$name])) {
        $factories = getConfig($ServiceManager)[CONFIGURATION_FACTORIES];
        if (!isset($factories[$name])) {
            trigger_error('Фабрика [' . $name . '] не найдена в конфиге', E_USER_ERROR);
        }

        // Подгружаем класс
        load($factories[$name]);

        $ServiceManager[FIELD_FACTORIES][$name] = &call_user_func_array(
            $factories[$name] . '\\construct',
            [&$ServiceManager]
        );
    }

    return $ServiceManager[FIELD_FACTORIES][$name];
}

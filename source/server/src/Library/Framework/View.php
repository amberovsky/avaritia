<?
/**
 * @author amberovsky
 *
 * Объект отображения
 */

namespace Avaritia\Library\Framework\View;

// Тип стратегии рендеринга
const
    RENDER_STRATEGY_PLAIN   = 0, // plain рендеринг
    RENDER_STRATEGY_JSON    = 1; // json рендеринг

// Поля класса
const
    FIELD_RENDER_STRATEGY   = 'render_strategy', /** стратегия рендеринга */
    FIELD_TEMPLATE_NAME     = 'template_name', /** шаблон рендеринга */
    FIELD_VARIABLES         = 'variables'; /** список переменных для шаблона */

/**
 * @return &array объект отображения
 */
function &construct() {
    $View = [];
    setRenderStrategy($View, RENDER_STRATEGY_PLAIN);
    setVariables($View, []);
    setTemplateName($View, '');

    return $View;
}

/**
 * @param array &$View объект отображения
 * @param int $renderStrategy стратегия рендеринга
 */
function setRenderStrategy(array &$View, $renderStrategy) {
    $View[FIELD_RENDER_STRATEGY] = (int) $renderStrategy;
}

/**
 * @param array $View объект отображения
 *
 * @return int стратегия рендеринга
 */
function getRenderStrategy(array $View) {
    return $View[FIELD_RENDER_STRATEGY];
}

/**
 * @param array &$View объект отображения
 * @param string $templateName имя шаблона для рендеринга
 */
function setTemplateName(array &$View, $templateName) {
    $View[FIELD_TEMPLATE_NAME] = $templateName;
}

/**
 * @param array $View объект отображения
 *
 * @return string имя шаблона для рендеринга
 */
function getTemplateName(array $View) {
    return $View[FIELD_TEMPLATE_NAME];
}

/**
 * @param array &$View объект отображения
 * @param array $variables список перменных для шаблона
 */
function setVariables(array &$View, array $variables) {
    $View[FIELD_VARIABLES] = $variables;
}

/**
 * @param array $View объект отображения
 *
 * @return array список перменных для шаблона
 */
function getVariables(array $View) {
    return $View[FIELD_VARIABLES];
}

/**
 * @private
 *
 * @param array $View объект отображения
 *
 * @return array данные ответа
 */
function renderJson(array $View) {
    return getVariables($View);
}

/**
 * @private
 *
 * @param array $View объект отображения
 *
 * @return string отрендеренный plain
 */
function renderPlain(array $View) {
    extract(getVariables($View));
    ob_start();
    require(PROJECT_SOURCE . '/../view/' . str_replace('\\', '/', getTemplateName($View)) . '.phtml');

    return ob_get_clean();
}

/**
 * Рендеринг ответа
 *
 * @param array $View объект отображения
 *
 * @return string результат рендеринга
 */
function render(array &$View) {
    switch (getRenderStrategy($View)) {
        case RENDER_STRATEGY_JSON:
            return renderJson($View);
            break;

        case RENDER_STRATEGY_PLAIN:
            return renderPlain($View);
            break;

        default:
            trigger_error('Неизвестная стратегия рендеринга [' . getRenderStrategy($View) . ']', E_USER_ERROR);
    }
}

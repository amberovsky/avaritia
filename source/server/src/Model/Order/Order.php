<?
/**
 * @author amberovsky
 *
 * Объект заказа
 */

namespace Avaritia\Model\Order\Order;

// Поля класса
const
    FIELD_ID    = 'id', // id
    FIELD_PRICE = 'price', // стоимость
    FIELD_TEXT  = 'text'; // текст

/**
 * @return &array объект заказа
 */
function &construct() {
    $Order = [
        FIELD_ID    => -1,
        FIELD_PRICE => 0,
        FIELD_TEXT  => '',
    ];

    return $Order;
}

/**
 * @param array $Order объект заказа
 *
 * @return int id
 */
function getId(array $Order) {
    return $Order[FIELD_ID];
}

/**
 * @param array &$Order объект заказа
 * @param int $id id
 */
function setId(array &$Order, $id) {
    $Order[FIELD_ID] = (int) $id;
}

/**
 * @param array $Order объект заказа
 *
 * @return int стоимость
 */
function getPrice(array $Order) {
    return $Order[FIELD_PRICE];
}

/**
 * @param array &$Order объект заказа
 * @param int $price стоимость
 */
function setPrice(array &$Order, $price) {
    $Order[FIELD_PRICE] = (int) $price;
}

/**
 * @param array $Order объект заказа
 *
 * @return string текст
 */
function getText(array $Order) {
    return $Order[FIELD_TEXT];
}

/**
 * @param array &$Order объект заказа
 * @param string $text текст
 */
function setText(array &$Order, $text) {
    $Order[FIELD_TEXT] = $text;
}

/**
 * Создаёт заказ по данным из mysql
 *
 * @param array $data данные из mysql
 *
 * @return &array объект заказа
 */
function &unserializeFromMysql(array $data) {
    $Order = &construct();

    setId($Order, $data['id']);
    setPrice($Order, $data['price']);
    setText($Order, $data['text']);

    return $Order;
}

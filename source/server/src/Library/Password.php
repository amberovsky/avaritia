<?
/**
 * @author amberovsky
 *
 * Утилиты работы с паролями
 */

namespace Avaritia\Library\Password;

/**
 * @static
 *
 * @param string $password пароль
 *
 * @return string хешированный пароль
 */
function hash($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

/**
 * @param string $password пароль
 * @param string $passwordHash хеш пароля
 *
 * @return bool верный ли пароль
 */
function check($password, $passwordHash) {
    return password_verify($password, $passwordHash);
}

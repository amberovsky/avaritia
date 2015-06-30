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

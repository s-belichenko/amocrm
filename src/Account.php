<?php
/**
 * Account.php
 * Date: 13.01.2017
 * Time: 10:14
 * Author: Maksim Klimenko
 * Email: mavsan@gmail.com
 */

namespace AmoCRM;


class Account
{
    protected static $_accountInfo;
    
    /**
     * Получение информации о учетной записи в AmoCRM
     *
     * @param \AmoCRM\Handler $handler инициализированный ранее экземпляр
     *
     * @return \AmoCRM\Account
     */
    public static function getAccountInfo(Handler $handler)
    {
        if (is_null(self::$_accountInfo)) {
            self::$_accountInfo = $handler->request(new Request(Request::INFO))->result;
        }
        
        return self::$_accountInfo;
    }
}
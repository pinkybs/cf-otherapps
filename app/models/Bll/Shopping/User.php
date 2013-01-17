<?php

require_once 'Dal/Shopping/User.php';

class Bll_Shopping_User
{
    public static function getAppFriendIds($fids)
    {
        $dalShoppingUser = Dal_Shopping_User::getDefaultInstance();

        return $dalShoppingUser->getAppFriendIds($fids);
    }

    public static function updateUser($uid)
    {
        $dalShoppingUser = Dal_Shopping_User::getDefaultInstance();

        return $dalShoppingUser->updateUser($uid);
    }

    public static function isAppUser($uid)
    {
        $dalShoppingUser = Dal_Shopping_User::getDefaultInstance();
        $rowUser = $dalShoppingUser->getUser($uid);
        if (empty($rowUser)) {
            return false;
        }
        return $rowUser;
    }
}
<?php

require_once 'Dal/Slave/User.php';

class Bll_Slave_User
{
    public static function getAppFriendIds($fids)
    {
        $dalSlaveUser = Dal_Slave_User::getDefaultInstance();

        return $dalSlaveUser->getAppFriendIds($fids);
    }

    public static function updateUser($uid)
    {
        $dalSlaveUser = Dal_Slave_User::getDefaultInstance();

        return $dalSlaveUser->updateUser($uid);
    }

    public static function isAppUser($uid)
    {
        $dalSlaveUser = Dal_Slave_User::getDefaultInstance();
        $rowUser = $dalSlaveUser->getUser($uid);
        if (empty($rowUser)) {
            return false;
        }
        return $rowUser;
    }
}
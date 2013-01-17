<?php

require_once 'Dal/Chat/User.php';

class Bll_Chat_User
{
    public static function getAppFriendIds($fids)
    {
        $dalChatUser = Dal_Chat_User::getDefaultInstance();

        return $dalChatUser->getAppFriendIds($fids);
    }

    public static function updateUser($uid)
    {
        $dalChatUser = Dal_Chat_User::getDefaultInstance();

        return $dalChatUser->updateUser($uid);
    }

    public static function isAppUser($uid)
    {
        $dalChatUser = Dal_Chat_User::getDefaultInstance();
        $rowUser = $dalChatUser->getUser($uid);
        return !empty($rowUser) ? true : false;
    }
}
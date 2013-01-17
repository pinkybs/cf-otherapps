<?php

require_once 'Dal/Scripteditor/User.php';

class Bll_Scripteditor_User
{    
    public static function getAppFriendIds($fids)
    {
        $dalScripteditorUser = Dal_Scripteditor_User::getDefaultInstance();
        
        return $dalScripteditorUser->getAppFriendIds($fids);
    }
    
}
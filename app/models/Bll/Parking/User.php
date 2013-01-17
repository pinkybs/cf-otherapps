<?php

require_once 'Dal/Parking/User.php';

class Bll_Parking_User
{    
    public static function getAppFriendIds($fids)
    {
        $dalParkingUser = Dal_Parking_User::getDefaultInstance();
        
        return $dalParkingUser->getAppFriendIds($fids);
    }
}
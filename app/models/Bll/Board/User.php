<?php

require_once 'Dal/Board/User.php';

class Bll_Board_User
{
    public static function getUser($uid)
    {
        $dalBoardUser = Dal_Board_User::getDefaultInstance();
        
        return $dalBoardUser->getUser($uid);
    }
    
    public static function isJoined($uid)
    {
        $user = self::getUser($uid);
        
        if ($user) {
            //check status ?
            //$user['status']
            
            return true;
        }
        else {
            return false;
        }
    }
    
    public static function join($uid)
    {
        self::updateUser($uid, 0);
        
        require_once 'Bll/Board/Board.php';
        Bll_Board_Board::initSetting($uid);
    }
    
    public static function remove($uid)
    {
        self::updateUser($uid, 1);
    }
        
    public static function updateUser($uid, $status = 0)
    {
        $dalBoardUser = Dal_Board_User::getDefaultInstance();
        
        try {
            $dalBoardUser->updateUser($uid, $status);
        }
        catch (Exception $e) {
            err_log($e->getMessage());
        }
    }
    
    public static function getAppFriendIds($fids)
    {
        $dalBoardUser = Dal_Board_User::getDefaultInstance();
        
        return $dalBoardUser->getAppFriendIds($fids);
    }
}
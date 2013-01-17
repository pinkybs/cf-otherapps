<?php

require_once 'Dal/Board/Friend.php';

class Bll_Board_Friend
{
    public static function getFriendIds($uid)
    {
        $dalBoardFriend = Dal_Board_Friend::getDefaultInstance();
        
        return $dalBoardFriend->getFriendIds($uid);
    }
    
    public static function isFriend($uid, $fid)
    {
        $fids = self::getFriendIds($uid);
        
        if (empty($fids)) {
            return false;
        }
        
        $fid_arr = split(',', $fids);
        
        return in_array($fid, $fid_arr);
    }
    
    public static function isFriendFriend($id, $fid)
    {
        $fids1 = self::getFriendIds($id);
        $fids2 = self::getFriendIds($fid);
        
        if (empty($fids1) || empty($fids2)) {
            return false;
        }
        
        $fid_arr1 = split(',', $fids1);
        $fid_arr2 = split(',', $fids2);
        
        foreach($fid_arr1 as $fid1) {
            if (in_array($fid1, $fid_arr2)) {
                return true;
            }
        }
        
        return false;
    }
    
    public static function updateFriendIds($uid, $fids)
    {        
        if(is_array($fids)) {
            $fids = implode(',', $fids);
        }
                
        $dalBoardFriend = Dal_Board_Friend::getDefaultInstance();
        
        try {
            $dalBoardFriend->updateFriendIds($uid, $fids);
        }
        catch (Exception $e) {
            err_log($e->getMessage());
        }
    }
    
}
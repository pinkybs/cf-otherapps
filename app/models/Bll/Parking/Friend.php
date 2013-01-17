<?php

require_once 'Dal/Parking/Friend.php';

class Bll_Parking_Friend
{
    public static function getFriendIds($uid)
    {
        $fids = self::getFriends($uid);
        
        if (empty($fids)) {
            return '';
        }
        
        return implode(',', $fids);
    }
    
    public static function getFriends($uid)
    {
        $dalParkingFriend = Dal_Parking_Friend::getDefaultInstance();
        
        return $dalParkingFriend->getFriends($uid);
    }
    
    public static function isFriend($uid, $fid)
    {
        $fids = self::getFriends($uid);
        
        if (empty($fids)) {
            return false;
        }
                
        return in_array($fid, $fids);
    }
    
    public static function isFriendFriend($uid, $fid)
    {
        $fids1 = self::getFriends($uid);
        $fids2 = self::getFriends($fid);
        
        if (empty($fids1) || empty($fids2)) {
            return false;
        }
        
        foreach($fids1 as $fid1) {
            if (in_array($fid1, $fids2)) {
                return true;
            }
        }
        
        return false;
    }
    
    public static function updateFriends($uid, $fids)
    {                
        $dalParkingFriend = Dal_Parking_Friend::getDefaultInstance();
        $db = $dalParkingFriend->getWriter();
        
        try {
            $db->beginTransaction();
            $dalParkingFriend->deleteFriends($uid);
            $dalParkingFriend->insertFriends($uid, $fids);
            $db->commit();
        }
        catch (Exception $e) {
            $db->rollBack();
            err_log($e->getMessage());
        }
    }
    
}
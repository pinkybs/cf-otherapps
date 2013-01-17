<?php

require_once 'Dal/Chomeboard/Friend.php';

class Bll_Chomeboard_Friend
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
        $dalChomeboardFriend = Dal_Chomeboard_Friend::getDefaultInstance();
        
        return $dalChomeboardFriend->getFriendIds($uid);
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
        $dalChomeboardFriend = Dal_Chomeboard_Friend::getDefaultInstance();
        $db = $dalChomeboardFriend->getWriter();
        
        try {
            $db->beginTransaction();
            $dalChomeboardFriend->deleteFriends($uid);
            $dalChomeboardFriend->insertFriends($uid, $fids);
            $db->commit();
        }
        catch (Exception $e) {
            $db->rollBack();
            err_log($e->getMessage());
        }
    }
    
}
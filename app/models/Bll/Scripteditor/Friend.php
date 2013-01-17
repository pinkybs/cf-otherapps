<?php

require_once 'Dal/Scripteditor/Friend.php';

class Bll_Scripteditor_Friend
{
    public static function getFriendIds($uid)
    {
        $dalScripteditorFriend = Dal_Scripteditor_Friend::getDefaultInstance();
        
        return $dalScripteditorFriend->getFriendIds($uid);
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
                
        $dalScripteditorFriend = Dal_Scripteditor_Friend::getDefaultInstance();
        
        try {
            $dalScripteditorFriend->updateFriendIds($uid, $fids);
        }
        catch (Exception $e) {
            err_log($e->getMessage());
        }
    }
    
    public static function updateFriends($uid, $fids)
    {
        $dalScripteditorFriend = Dal_Scripteditor_Friend::getDefaultInstance();
        $db = $dalScripteditorFriend->getWriter();

        try {
            $db->beginTransaction();
            $dalScripteditorFriend->deleteFriends($uid);
            $dalScripteditorFriend->insertFriends($uid, $fids);
            $db->commit();
        }
        catch (Exception $e) {
            $db->rollBack();
            err_log($e->getMessage());
        }
    }
    
    
}
<?php

require_once 'Dal/Friend.php';
require_once 'Bll/Cache/User.php';

class Bll_Friend
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
        return Bll_Cache_User::getFriends($uid);
    }
    
    public static function getFriendsPage($uid, $page = 1, $step = 10)
    {
        $fids = Bll_Cache_User::getFriends($uid);

        if ($fids) {
            $start = ($page -1) * $step;
            $count = count($fids);
            if ($count > 0 && $start < $count) {
                return array_slice($fids, $start, $step);
            }
        }
        
        return null;
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
    
    /*
    public static function isSame($fids1, $fids2)
    {
        if ($count = count($fids1) != count($fids2)) {
            return false;
        }
        
        sort($fids1);
        reset($fids1);
        sort($fids2);
        reset($fids2);
        for ($i = 0; $i < $count; $i++) {
            if ($fids1[$i] != $fids2[$i]) {
                return false;
            }
        }
        
        return true;
    }
    */
    
    public static function diff($fids1, $fids2)
    {        
        $union_array = array_merge($fids1, $fids2);

        $new = array_values(array_diff($union_array, $fids1));
        $delete = array_values(array_diff($union_array, $fids2));
        
        return array('new' => $new, 'delete' => $delete);
    }   
    
    /*
    public static function updateFriends($uid, $fids)
    {                
        $fids1 = self::getFriends($uid);
        
        if (self::isSame($fids1, $fids)) {
            return;
        }
        
        $dalFriend = Dal_Friend::getDefaultInstance();
        $db = $dalFriend->getWriter();
        
        try {
            $db->beginTransaction();
            $dalFriend->deleteFriends($uid);
            $dalFriend->insertFriends($uid, $fids);
            $db->commit();
        }
        catch (Exception $e) {
            $db->rollBack();
            err_log($e->getMessage());
        }
    }
    */
    
    public static function updateFriends($uid, $fids)
    {
        $fids1 = self::getFriends($uid);
        
        $result = self::diff($fids1, $fids);
        
        $doNew = count($result['new']) > 0;
        $doDel = count($result['delete']) > 0;
        
        if (!$doNew && !$doDel) {
            return;
        }
        
        $dalFriend = Dal_Friend::getDefaultInstance();
        $db = $dalFriend->getWriter();
        
        try {
            $db->beginTransaction();
            if ($doDel) {
                foreach ($result['delete'] as $del) {
                    $dalFriend->deleteFriend($uid, $del);
                }
            }
            if ($doNew) {
                foreach ($result['new'] as $new) {
                    $dalFriend->insertFriend($uid, $new);
                }
            }
            $db->commit();
            
            Bll_Cache_User::cleanMultiFriends(array_merge(array($uid), $result['delete'], $result['new']));
        }
        catch (Exception $e) {
            $db->rollBack();
            err_log($e->getMessage());
        }
    }
    
}
<?php

/** @see Bll_Cache */
require_once 'Bll/Cache.php';

/**
 * Scripteditor Cache
 *
 * @package    Bll/Cache
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/06/02    Hulj
*/
class Bll_Cache_User
{    
    private static $_prefix = 'Bll_Cache_User';
    
    public static function getCacheKey($salt, $params = null)
    {
        return Bll_Cache::getCacheKey(self::$_prefix, $salt, $params);
    }

    public static function getPerson($id)
    {
        $key = self::getCacheKey('getPerson', $id);
        
        if (!$result = Bll_Cache::get($key)) {
            require_once 'Dal/User.php';
            $dalUser = Dal_User::getDefaultInstance();
        
            $result = $dalUser->getPerson($id);
            
            Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_MAX);
        }
        
        return $result;
    }

    public static function getFriends($id)
    {
        $key = self::getCacheKey('getFriends', $id);
        
        if (!$result = Bll_Cache::get($key)) {
            require_once 'Dal/Friend.php';
            
            $dalFriend = Dal_Friend::getDefaultInstance();
        
            $result = $dalFriend->getFriends($id);
            
            Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_MAX);
        }
        
        return $result;
    }
    
    public static function isUpdated($id)
    {
        $key = self::getCacheKey('isUpdated', $id);
        
        if (!Bll_Cache::get($key)) {
            return false;
        }
        
        return true;
    }
    
    public static function setUpdated($id)
    {
        $key = self::getCacheKey('isUpdated', $id);
            
        Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_ONE_MINUTE * 10);
    }   
    
    public static function cleanPerson($id)
    {
        Bll_Cache::delete(self::getCacheKey('getPerson', $id));
    }
    
    public static function cleanPeople($ids)
    {
        if (count($ids) > 0) {
            foreach ($ids as $id) {
                self::cleanPerson($id);
            }
        }
    }
    
    public static function cleanFriends($id)
    {
        Bll_Cache::delete(self::getCacheKey('getFriends', $id));
    }
    
    public static function cleanMultiFriends($ids)
    {
        foreach ($ids as $id) {
            self::cleanFriends($id);
        }
    }
}
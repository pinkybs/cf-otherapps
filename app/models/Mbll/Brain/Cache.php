<?php

require_once 'Bll/Cache.php';

class  Mbll_Brain_Cache
{
    private static $_mdalPlace = null;
    
    private static $_mdalUser = null;
    
    private static $_mdalPay = null;

    private static $_prefix = 'Mbll_Brain_Cache';
    
    public static function getCacheKey($salt, $params = null)
    {
        return Bll_Cache::getCacheKey(self::$_prefix, $salt, $params);
    }


    public static function getMdalBrain()
    {
        if (self::$_mdalUser === null) {
            require_once 'Mdal/Disney/User.php';
            self::$_mdalUser = Mdal_Disney_User::getDefaultInstance();
        }

        return self::$_mdalUser;
    }
    


    public static function getAllRankById()
    {
        $key = self::getCacheKey('getAllRankById');

        if (!$result = Bll_Cache::get($key)) {

            $mdalUser = self::getMdalUser();
            $result = $mdalUser->getAllRankById(0);

            if ($result) {
                Bll_Cache::set($key, $result,  Bll_Cache::LIFE_TIME_ONE_DAY );
            }
        }

        return $result;
    }

    public static function getFriendRankingList($uid)
    {
        $key = self::getCacheKey('getFriendRankingList' . $uid);

        if (!$result = Bll_Cache::get($key)) {

            require_once 'Bll/Friend.php';
            $fids = Bll_Friend::getFriends($uid);
            
            $mdalUser = self::getMdalUser();
            //get game point ranking list
            $result = $mdalUser->getRankingList(1, $uid, $fids);
            
            if ($result) {
                Bll_Cache::set($key, $result,  Bll_Cache::LIFE_TIME_ONE_DAY );
            }
        }

        return $result;
    }    
        
    /**
     * clear place
     */
    public static function clearPlace()
    {
        Bll_Cache::delete(self::getCacheKey('getPlace'));
    }

    /**
     * clear all ranking list
     */
    public static function clearAllRankingList()
    {
        Bll_Cache::delete(self::getCacheKey('getAllRankingList'));
    }

    /**
     * clear frined ranking list
     */
    public static function clearFriendRankingList($uid)
    {
        Bll_Cache::delete(self::getCacheKey('getFriendRankingList' . $uid));
    }
}
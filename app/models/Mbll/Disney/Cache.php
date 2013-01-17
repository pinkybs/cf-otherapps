<?php

require_once 'Bll/Cache.php';

class  Mbll_Disney_Cache
{
    private static $_mdalPlace = null;
    
    private static $_mdalUser = null;
    
    private static $_mdalPay = null;

    private static $_prefix = 'Mbll_Disney_Cache';
    
    public static function getCacheKey($salt, $params = null)
    {
        return Bll_Cache::getCacheKey(self::$_prefix, $salt, $params);
    }
    
    /**
     * get mdal place
     *
     * @return Mdal_Disney_Place
     */
    public static function getMdalPlace()
    {
        if (self::$_mdalPlace === null) {
            require_once 'Mdal/Disney/Place.php';
            self::$_mdalPlace = Mdal_Disney_Place::getDefaultInstance();
        }

        return self::$_mdalPlace;
    }

    /**
     * get mdal user
     *
     * @return Mdal_Disney_User
     */
    public static function getMdalUser()
    {
        if (self::$_mdalUser === null) {
            require_once 'Mdal/Disney/User.php';
            self::$_mdalUser = Mdal_Disney_User::getDefaultInstance();
        }

        return self::$_mdalUser;
    }
    
    /**
     * get mdal pay
     *
     * @return Mdal_Disney_Pay
     */
    public static function getMdalPay()
    {
        if (self::$_mdalPay === null) {
            require_once 'Mdal/Disney/Pay.php';            
            self::$_mdalPay = Mdal_Disney_Pay::getDefaultInstance();
        }

        return self::$_mdalPay;
    }
    
    public static function getPlaceAwardName($pid)
    {
    	$place = self::getPlace();
    	
    	foreach ($place as $item) {
    		if ($item['pid'] == $pid) {
    			return $item['award_name'];
    		}
    	}
    }
    
    public static function getPlace()
    {
        $key = self::getCacheKey('getPlace1217');

        if (!$result = Bll_Cache::get($key)) {

            $mdalPlace = self::getMdalPlace();
            $result = $mdalPlace->getPetImageUrl();

            if ($result) {
               Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_MAX );
            }
        }

        return $result;
    }

    public static function getAllRankingList()
    {
        $key = self::getCacheKey('getRankingList');

        if (!$result = Bll_Cache::get($key)) {

            $mdalUser = self::getMdalUser();
            $result = $mdalUser->getRankingList(0);

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

    public static function getUserRankNmInAll($uid)
    {
        $key = self::getCacheKey('getUserRankNmInAll' . $uid);

        if (!$result = Bll_Cache::get($key)) {

            $mdalUser = self::getMdalUser();
            //get user game point ranking number in friends
            $result = $mdalUser->getUserRankNm($uid, '');
            
            if ($result) {
                Bll_Cache::set($key, $result,  Bll_Cache::LIFE_TIME_ONE_DAY );
            }
        }

        return $result;
    }
    
    public static function getUserRankNmInFriends($uid)
    {
        $key = self::getCacheKey('getUserRankNmInFriends' . $uid);

        //if (!$result = Bll_Cache::get($key)) {

            require_once 'Bll/Friend.php';
            $fids = Bll_Friend::getFriends($uid);

            $mdalUser = self::getMdalUser();
            //get user game point ranking number in friends
            $result = $mdalUser->getUserRankNm($uid, $fids);
            
            if ($result) {
                Bll_Cache::set($key, $result,  Bll_Cache::LIFE_TIME_ONE_DAY );
            }
        //}

        return $result;
    }
    
    public static function getPayment()
    {
        $key = self::getCacheKey('getPayment');

        if (!$result = Bll_Cache::get($key)) {

            $mdalPay = self::getMdalPay();
            $result = $mdalPay->getPayment();

            if ($result) {
                Bll_Cache::set($key, $result,  Bll_Cache::LIFE_TIME_MAX  );
            }
        }

        return $result;
    }
    
    /**
     * clear payment
     *
     */
    public static function clearPayment()
    {
        Bll_Cache::delete(self::getCacheKey('getPayment'));
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
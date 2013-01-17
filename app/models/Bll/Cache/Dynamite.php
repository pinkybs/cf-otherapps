<?php

/** @see Bll_Cache */
require_once 'Bll/Cache.php';

/**
 * Dynamite Cache
 *
 * @package    Bll/Cache
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/07/06    Liz
*/
class Bll_Cache_Dynamite
{
    /**
     * Bll_Dynamite_Index object
     *
     * @static
     * @var Bll_Dynamite_Index
     */
    private static $_bllDynamiteIndex = null;

    private static $_prefix = 'Bll_Cache_Dynamite';

    private static $_dalBomb = null;

    private static $_dalUser = null;

    private static $_dalRank = null;

    private static $_dalItem = null;

    public static function getCacheKey($salt, $params = null)
    {
        return Bll_Cache::getCacheKey(self::$_prefix, $salt, $params);
    }

    /**
     * get Class Bll_Dynamite_Index instance object
     *
     * @static
     * @return Bll_Dynamite_Index object
     */
    public static function getBllDynamiteIndex()
    {
        if (self::$_bllDynamiteIndex === null) {
            require_once 'Bll/Dynamite/Index.php';
            self::$_bllDynamiteIndex = new Bll_Dynamite_Index();
        }

        return self::$_bllDynamiteIndex;
    }

    /**
     * get Class Dal_Dynamite_Bomb instance object
     * @return Dal_Dynamite_Bomb object
     */
    public static function getDalBomb()
    {
        if (self::$_dalBomb=== null) {
            require_once 'Dal/Dynamite/Bomb.php';
            self::$_dalBomb = new Dal_Dynamite_Bomb();
        }

        return self::$_dalBomb;
    }

    /**
     * get Class Dal_Dynamite_User instance object
     * @return Dal_Dynamite_User object
     */
    public static function getDalUser()
    {
        if (self::$_dalUser=== null) {
            require_once 'Dal/Dynamite/User.php';
            self::$_dalUser = new Dal_Dynamite_User();
        }

        return self::$_dalUser;
    }

    /**
     * get Class Dal_Dynamite_Rank instance object
     * @return Dal_Dynamite_Rank object
     */
    public static function getDalRank()
    {
        if (self::$_dalRank=== null) {
            require_once 'Dal/Dynamite/Rank.php';
            self::$_dalRank = new Dal_Dynamite_Rank();
        }

        return self::$_dalRank;
    }

    /**
     * get Class Dal_Dynamite_Item instance object
     * @return Dal_Dynamite_Item object
     */
    public static function getDalItem()
    {
        if (self::$_dalItem === null) {
            require_once 'Dal/Dynamite/Item.php';
            self::$_dalItem = new Dal_Dynamite_Item();
        }

        return self::$_dalItem;
    }

    /**
     * get my mixi app user
     *
     * @return array
     */
    public static function getMyMixiUser($uid)
    {
        $key = self::getCacheKey('getMyMixiUser', $uid);

        if (!$result = Bll_Cache::get($key)) {
            $_bllDynamiteIndex = self::getBllDynamiteIndex();
            $result = $_bllDynamiteIndex->getMyMixiUser($uid);

            if ($result) {
                //5 minute
                Bll_Cache::set($key, $result, 5*60);
            }
        }

        return $result;
    }

    /**
     * get my mixi app user
     *
     * @return array
     */
    public static function getAllAppUser()
    {
        $key = self::getCacheKey('getAllAppUser');

        if (!$result = Bll_Cache::get($key)) {
            $_dalUser = self::getDalUser();
            $result = $_dalUser->getAllAppUser();

            if ($result) {
                //5 minute
                Bll_Cache::set($key, $result, 5*60);
            }
        }

        return $result;
    }

    /**
     * get bomb message
     * @return integer
     */
    public static function getArrayBombMessage()
    {

        $key = self::getCacheKey('getArrayBombMessage');

        if (!$result = Bll_Cache::get($key)) {

            $dalBomb = self::getDalBomb();
            $result = $dalBomb->getBombMsg();

            if ($result) {
                Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_ONE_WEEK);
            }
        }

        return $result;
    }

    /**
     * get max reward rank user
     * @return integer
     */
    public static function getMaxRewardRankUser($uid, $idArray, $start, $size, $type, $orderType)
    {
        if ($type == 1) {
            $key = self::getCacheKey('getMaxRewardRankUserInFriend', $uid);
        }
        else {
        	$key = self::getCacheKey('getMaxRewardRankUserInAll', $uid);
        }

        if (!$result = Bll_Cache::get($key)) {
            $dalRank = self::getDalRank();
            $result = $dalRank->getMaxRewardRankUser($uid, $idArray, $start, $size, $type, $orderType);

            if ($result) {
            	//5 minute
                Bll_Cache::set($key, $result, 5 * 60);
            }
        }

        return $result;
    }

    /**
     * get game over rank user
     * @return integer
     */
    public static function getGameOverRankUser($uid, $idArray, $start, $size, $type, $orderType)
    {
        if ($type == 1) {
            $key = self::getCacheKey('getGameOverRankUserInFriend', $uid);
        }
        else {
        	$key = self::getCacheKey('getGameOverRankUserInAll', $uid);
        }

        if (!$result = Bll_Cache::get($key)) {

            $dalRank = self::getDalRank();
            $result = $dalRank->getGameOverRankUser($uid, $idArray, $start, $size, $type, $orderType);

            if ($result) {
                //5 minute
                Bll_Cache::set($key, $result, 5 * 60);
            }
        }

        return $result;
    }

    public static function getHitmanType()
    {
    	$key = self::getCacheKey('getHitmanType');

        if (!$result = Bll_Cache::get($key)) {
            $_dalUser = self::getDalUser();
            $result = $_dalUser->getHitmanTypeInfo();

            if ($result) {
                Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_MAX);
            }
        }

        return $result;
    }

    public static function getItemInfo()
    {
        $key = self::getCacheKey('getItemInfo');

        if (!$result = Bll_Cache::get($key)) {
            $_dalUser = self::getDalUser();
            $result = $_dalUser->getItemInfo();

            if ($result) {
                Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_MAX);
            }
        }

        return $result;
    }

    public static function getUserBasicInfo($uid)
    {
        $key = self::getCacheKey('getUserBasicInfo', $uid);

        if (!$result = Bll_Cache::get($key)) {
            $_dalUser = self::getDalUser();
            $result = $_dalUser->getUserBasicInfo($uid);

            if ($result) {
                //15 min
                Bll_Cache::set($key, $result, 15 * 60);
            }
        }

        return $result;
    }

    public static function getAllHitmanInfo($number)
    {
        $key = self::getCacheKey('getAllHitmanInfo', $number);

        if (!$result = Bll_Cache::get($key)) {
            $_dalUser = self::getDalUser();
            $result = $_dalUser->getAllHitmanInfo($number);

            if ($result) {
                Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_MAX);
            }
        }

        return $result;
    }

    public static function getRankTempTable()
    {

        $key = self::getCacheKey('getRankTempTable');

        if (!$result = Bll_Cache::get($key)) {
            $result = 1;
            Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_MAX);
        }

        return $result;
    }

    public static function getDeadNumTempTable()
    {
        $key = self::getCacheKey('getDeadNumTempTable');

        if (!$result = Bll_Cache::get($key)) {
            $result = 1;
            Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_MAX);
        }

        return $result;
    }


    public static function getAllUserRankTable()
    {
        $key = self::getCacheKey('getAllUserRankTable');

        if (!$result = Bll_Cache::get($key)) {
            $result = 1;
            Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_MAX);
        }

        return $result;
    }

    /**
     * get item list in item shop
     * @return array
     */
    public static function getItemShopList()
    {
        $key = self::getCacheKey('getItemShopList');

        if (!$result = Bll_Cache::get($key)) {
            $_dalItem = self::getDalItem();
            $result = $_dalItem->getItemShopList();

            if ($result) {
                Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_MAX);
            }
        }

        return $result;
    }

    /**
     * clean my mixi user cache
     *
     * @param int $uid
     * @return void
     */
    public static function cleanMyMixiUser($uid)
    {
        Bll_Cache::delete(self::getCacheKey('getMyMixiUser', $uid));
    }

    /**
     * clean all app user cache
     *
     * @return void
     */
    public static function cleanAllAppUser()
    {
        Bll_Cache::delete(self::getCacheKey('getAllAppUser'));
    }


    /**
     * clear bomb message
     */
    public static function clearArrayBombMessage()
    {
        Bll_Cache::delete(self::getCacheKey('getArrayBombMessage'));
    }

    /**
     * clear max reward rank user
     */
    public static function clearRewardRankUser($uid)
    {
        Bll_Cache::delete(self::getCacheKey('getMaxRewardRankUserInFriend', $uid));
        Bll_Cache::delete(self::getCacheKey('getMaxRewardRankUserInAll', $uid));
    }

    /**
     * clear game over rank user
     */
    public static function clearGameOverRankUser($uid)
    {
        Bll_Cache::delete(self::getCacheKey('getGameOverRankUserInFriend', $uid));
        Bll_Cache::delete(self::getCacheKey('getGameOverRankUserInAll', $uid));
    }

    /**
     * clear hitman type
     */
    public static function clearHitmanType()
    {
        Bll_Cache::delete(self::getCacheKey('getHitmanType'));
    }

    /**
     * clear item info
     */
    public static function clearItemInfo()
    {
        Bll_Cache::delete(self::getCacheKey('getItemInfo'));
    }

    public static function clearUserBasicInfo($uid)
    {
        Bll_Cache::delete(self::getCacheKey('getUserBasicInfo', $uid));
    }

    public static function clearAllHitmanInfo($number)
    {
        Bll_Cache::delete(self::getCacheKey('getAllHitmanInfo', $number));
    }

    public static function clearRankTempTable()
    {
        Bll_Cache::delete(self::getCacheKey('getRankTempTable'));
    }

    public static function clearDeadNumTempTable()
    {
        Bll_Cache::delete(self::getCacheKey('getDeadNumTempTable'));
    }

    public static function clearAllUserRankTempTable()
    {
        Bll_Cache::delete(self::getCacheKey('getAllUserRankTable'));
    }

    public function cleanItemShopList()
    {
        Bll_Cache::delete(self::getCacheKey('getItemShopList'));
    }

    public static function freshRankTempTable($value)
    {

        $key = self::getCacheKey('getRankTempTable');

        Bll_Cache::set($key, $value, Bll_Cache::LIFE_TIME_MAX);

    }

    public static function freshDeadNumTempTable($value)
    {

        $key = self::getCacheKey('getDeadNumTempTable');

        Bll_Cache::set($key, $value, Bll_Cache::LIFE_TIME_MAX);

    }

    public static function freshAllUserRankTempTable($value)
    {

        $key = self::getCacheKey('getAllUserRankTable');

        Bll_Cache::set($key, $value, Bll_Cache::LIFE_TIME_MAX);
    }

}
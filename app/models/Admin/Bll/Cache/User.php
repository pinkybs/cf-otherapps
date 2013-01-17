<?php

/** @see Admin_Bll_Cache */
require_once 'Admin/Bll/Cache.php';

/**
 * Admin User Cache
 *
 * @package    Admin/Bll/Cache
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/03/12    zhangxin
 */
class Admin_Bll_Cache_User
{

    private static $_prefix = 'Admin_Bll_Cache_User';

    /**
     * class Admin_Dal_User
     *
     * @var Admin_Dal_User
     */
    private static $_dalUser = null;

    /**
     * get Dal_User instance object
     *
     * @return Dal_User object
     */
    public static function getDalUser()
    {
        if (self::$_dalUser === null) {
            require_once 'Admin/Dal/User.php';
            self::$_dalUser = Admin_Dal_User::getDefaultInstance();
        }

        return self::$_dalUser;
    }

    public static function getCacheKey($salt, $params = null)
    {
        return Admin_Bll_Cache::getCacheKey(self::$_prefix, $salt, $params);
    }

    /**
     * get user info cache
     *
     * @param integer $uid
     * @return array
     */
    public static function getInfo($uid)
    {
        $key = self::getCacheKey('getInfo', $uid);

        if (!$result = Admin_Bll_Cache::get($key)) {
            $dalUser = self::getDalUser();
            $result = $dalUser->getUser($uid);

            if ($result) {
                Admin_Bll_Cache::set($key, $result, Admin_Bll_Cache::LIFE_TIME_ONE_WEEK);
            }
        }

        return $result;
    }

    /**
     * get user all cache
     *
     * @param null
     * @return array
     */
    public static function getAllAdmin()
    {
        $key = self::getCacheKey('getAllAdmin');

        if (!$result = Admin_Bll_Cache::get($key)) {
            $dalUser = self::getDalUser();
            $result = $dalUser->getUserList(1, 1000);

            if ($result) {
                Admin_Bll_Cache::set($key, $result, Admin_Bll_Cache::LIFE_TIME_ONE_WEEK);
            }
        }

        return $result;
    }

    /**
     * clear user info cache
     *
     * @param integer $uid
     * @return void
     */
    public static function clearInfo($uid)
    {
        Admin_Bll_Cache::delete(self::getCacheKey('getInfo', $uid));
    }

    /**
     * clear user info all cache
     *
     * @param null
     * @return void
     */
    public static function clearAllAdmin()
    {
        Admin_Bll_Cache::delete(self::getCacheKey('getAllAdmin'));
    }
}
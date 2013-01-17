<?php

/** @see Bll_Cache */
require_once 'Bll/Cache.php';

/**
 * Millionminds Cache
 *
 * @package    Bll/Cache
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/07/27    Liz
*/
class Bll_Cache_Millionminds
{    
    /**
     * Bll_Millionminds_Profile object
     *
     * @static
     * @var Bll_Millionminds_Profile
     */
    private static $_bllMillionmindsProfile = null;
    
    private static $_prefix = 'Bll_Cache_Millionminds';
    
    public static function getCacheKey($salt, $params = null)
    {
        return Bll_Cache::getCacheKey(self::$_prefix, $salt, $params);
    }

    /**
     * get Class Bll_Millionminds_Profile instance object
     *
     * @static
     * @return Bll_Millionminds_Profile object
     */
    public static function getBllMillionmindsProfile()
    {
        if (self::$_bllMillionmindsProfile === null) {
            require_once 'Bll/Millionminds/Profile.php';
            self::$_bllMillionmindsProfile = new Bll_Millionminds_Profile();
        }
        
        return self::$_bllMillionmindsProfile;
    }

    /**
     * get complare result
     *
     * @param integer $groupId1
     * @param integer $groupId2
     * @return array
     */
    public static function getComplare($groupId1, $groupId2)
    {      
        $key = self::getCacheKey('getComplare', $groupId1.$groupId2);
                
        if (!$result = Bll_Cache::get($key)) {
            $_bllMillionmindsProfile = self::getBllMillionmindsProfile();
            $result = $_bllMillionmindsProfile->getComplare($groupId1, $groupId2);

            if ($result) {
                Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_ONE_MONTH);
            }
        }
        
        return $result;
    }

    /**
     * clean complare result
     *
     * @return void
     */
    public static function cleanComplare($groupId1, $groupId2)
    { 
        Bll_Cache::delete(self::getCacheKey('getComplare', $groupId1.$groupId2));
    }

}
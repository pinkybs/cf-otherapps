<?php

require_once 'Bll/Cache.php';

class  Bll_Hotel_Cache
{
    private static $_dalConfig = null;

    private static $_prefix = 'Bll_Config_Cache';
    
    public static function getCacheKey($salt, $params = null)
    {
        return Bll_Cache::getCacheKey(self::$_prefix, $salt, $params);
    }
    
    /**
     * get dal config
     *
     * @return Dal_Hotel_Config
     */
    public static function getDalConfig()
    {
        if (self::$_dalConfig=== null) {
            require_once 'Dal/Hotel/Config.php';
            self::$_dalConfig = Dal_Hotel_Config::getDefaultInstance();
        }

        return self::$_dalConfig;
    }
    
    public static function getRoomType()
    {
        $key = self::getCacheKey('getRoomType');

        if (!$result = Bll_Cache::get($key)) {

            $dalConfig = self::getDalConfig();
            $result = $dalConfig->getRoomType();

            if ($result) {
                Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_MAX );
            }
        }

        return $result;
    }
    
    public static function getRestaurantType()
    {
        $key = self::getCacheKey('getRestaurantType');

        if (!$result = Bll_Cache::get($key)) {

            $dalConfig = self::getDalConfig();
            $result = $dalConfig->getRestaurantType();

            if ($result) {
                Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_MAX );
            }
        }

        return $result;
    }
    
    public static function getCleanOccupancy()
    {
        $key = self::getCacheKey('getCleanOccupancy');

        if (!$result = Bll_Cache::get($key)) {

            $dalConfig = self::getDalConfig();
            $result = $dalConfig->getCleanOccupancy();

            if ($result) {
                Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_MAX );
            }
        }

        return $result;
    }
}
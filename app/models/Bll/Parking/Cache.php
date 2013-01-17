<?php

require_once 'Bll/Cache.php';

class  Bll_Parking_Cache
{

    private static $_dalCarShop = null;
    private static $_dalHouse = null;
    private static $_dalItem = null;

    private static $_prefix = 'Bll_Parking_Cache';

    public static function getCacheKey($salt, $params = null)
    {
        return Bll_Cache::getCacheKey(self::$_prefix, $salt, $params);
    }

    /**
     * get Class Dal_Parking_Car instance object
     * @return Dal_Parking_Car object
     */
    public static function getDalCarShop()
    {
        if (self::$_dalCarShop=== null) {
            require_once 'Dal/Parking/Car.php';
            self::$_dalCarShop = new Dal_Parking_Car();
        }

        return self::$_dalCarShop;
    }
    /**
     * get Class Dal_Parking_House instance object
     * @return Dal_Parking_House object
     */
    public static function getDalHouse()
    {
        if (self::$_dalHouse=== null) {
            require_once 'Dal/Parking/House.php';
            self::$_dalHouse = new Dal_Parking_House();
        }

        return self::$_dalHouse;
    }
    /**
     * get Class Dal_Parking_Store instance object
     * @return Dal_Parking_Store object
     */
    public static function getDalItem()
    {
        if (self::$_dalItem=== null) {
            require_once 'Dal/Parking/Store.php';
            self::$_dalItem = new Dal_Parking_Store();
        }

        return self::$_dalItem;
    }
    /**
     * get car shop count
     * @return integer
     */
    public static function getCarShopCount()
    {

        $key = self::getCacheKey('getCarShopCount');

        if (!$result = Bll_Cache::get($key)) {

            $dalCarShop = self::getDalCarShop();
            $result = $dalCarShop->getCarCount();

            if ($result) {
                Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_ONE_WEEK);
            }
        }

        return $result;
    }
    /**
     * get car shop list
     * @return array
     */
    public static function getCarShopList($pageIndex, $pageSize=8)
    {

        $key = self::getCacheKey('getCarShopList', $pageIndex);

        if (!$result = Bll_Cache::get($key)) {

            $dalCarShop = self::getDalCarShop();
            $result = $dalCarShop->getCarList($pageIndex, $pageSize);

            if ($result) {
                Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_ONE_WEEK);
            }
        }

        return $result;
    }
    /**
     * get house count
     * @return integer
     */
    public static function getHouseCount()
    {
        $key = self::getCacheKey('getHouseCount');

        if (!$result = Bll_Cache::get($key)) {
            $dalHouse = self::getDalHouse();
            $result = $dalHouse->getHouseCount();

            if ($result) {
                Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_ONE_WEEK);
            }
        }

        return $result;
    }
    /**
     * get house list
     * @return array
     */
    public static function getHouseList($pageIndex, $pageSize=8)
    {
        $key = self::getCacheKey('getHouseList', $pageIndex);

        if (!$result = Bll_Cache::get($key)) {
            $dalHouse = self::getDalHouse();
            $result = $dalHouse->getHouseList($pageIndex, $pageSize);

            if ($result) {
                Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_ONE_WEEK);
            }
        }

        return $result;
    }
    /**
     * get item count
     * @return integer
     */
    public static function getItemCount()
    {
        $key = self::getCacheKey('getItemCount');

        if (!$result = Bll_Cache::get($key)) {
            $dalItem = self::getDalItem();
            $result = $dalItem->getItemCount();

            if ($result) {
                Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_ONE_WEEK);
            }
        }

        return $result;
    }
    /**
     * get item list
     * @return array
     */
    public static function getItemList($pageIndex, $pageSize=8)
    {
        $param = 'page:' . $pageIndex . ',size:' . $pageSize;
        $key = self::getCacheKey('getItemList0625', $param);

        if (!$result = Bll_Cache::get($key)) {
            $dalItem = self::getDalItem();
            $result = $dalItem->getItemsList($pageIndex, $pageSize);

            if ($result) {
                Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_ONE_WEEK);
            }
        }

        return $result;
    }
    /**
     * clear car shop
     */
    public static function clearCarShop()
    {
        Bll_Cache::delete(self::getCacheKey('getCarShopCount'));

        for( $i=1;$i<=10;$i++){
            Bll_Cache::delete(self::getCacheKey('getCarShopList', $i));
        }
    }
    /**
     * clear house
     */
    public static function clearHouse()
    {
        Bll_Cache::delete(self::getCacheKey('getHouseCount'));

        for( $i=1;$i<=10;$i++){
            Bll_Cache::delete(self::getCacheKey('getHouseList', $i));
        }
    }
    /**
     * clear item
     */
    public static function clearItem()
    {
        Bll_Cache::delete(self::getCacheKey('getItemCount'));

        for( $i=1;$i<=10;$i++){
            Bll_Cache::delete(self::getCacheKey('getItemList', $i));
        }
    }
}
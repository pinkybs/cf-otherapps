<?php

/** @see Bll_Cache */
require_once 'Bll/Cache.php';

/**
 * Board Cache
 *
 * @package    Bll/Cache
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/09/22    lp
*/
class Bll_Cache_Board
{

    private static $_prefix = 'Bll_Cache_Board';

    private static $_dalBoard = null;

    public static function getCacheKey($salt, $params = null)
    {
        return Bll_Cache::getCacheKey(self::$_prefix, $salt, $params);
    }

    /**
     * get Class Dal_Board_Board instance object
     * @return Dal_Board_Board object
     */
    public static function getDalBoard()
    {
        if (self::$_dalBoard=== null) {
            require_once 'Dal/Board/Board.php';
            self::$_dalBoard = new Dal_Board_Board();
        }

        return self::$_dalBoard;
    }

    /**
     * get skin basic infomation
     *
     * @return array
     */
    public static function getSkinBasicInfo()
    {
        $key = self::getCacheKey('getSkinBasicInfo');

        if (!$result = Bll_Cache::get($key)) {

            $dalBoard = self::getDalBoard();
            $result = $dalBoard->getSkinBasicInfo();

            if ($result) {
                Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_ONE_WEEK);
            }
        }

        return $result;
    }

    /**
     * clear game over rank user
     */
    public static function clearSkinBasicInfo()
    {
        Bll_Cache::delete(self::getCacheKey('getSkinBasicInfo'));
    }

}
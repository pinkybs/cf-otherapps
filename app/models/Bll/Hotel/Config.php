<?php

require_once 'Bll/Abstract.php';

/**
 * hotel config logic's Operation
 *
 * @package    Bll
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/10/20    Huch
 */
class Bll_Hotel_Config extends Bll_Abstract
{
    /**
     * get game start time
     *
     * @return datetime
     */
    public static function getGameStartTime()
    {
        return '2009-10-21 00:00:00';
    }
    
    /**
     * get game day
     *
     * @return integer second
     */
    public static function getGameDay()
    {
        return 3600;
    }
    
    public static function getCleanDownPerDay()
    {
        return 2;
    }
    
    public static function getAllowNotLoginDay()
    {
        return 30;
    }
    
    /**
     * get game current date
     *
     */
    public static function getGameCurrentDate()
    {
        //get pass day
        $day = floor((time() - strtotime(self::getGameStartTime()))/self::getGameDay());
        
        return date('Y-m-d',strtotime($date . "+$day day"));
    }
    
    public static function getCurrentRoomCustomer()
    {
        
    }
}
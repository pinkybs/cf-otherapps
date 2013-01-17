<?php

require_once 'Dal/Abstract.php';

/**
 * Dal Hotel Config
 * MixiApp hotel config Data Access Layer
 *
 * @package    Dal/Hotel
 * @copyright  Copyright (c) 2009 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/09/120    huch
 */
class Dal_Hotel_Config extends Dal_Abstract
{
    protected static $_instance;
    
    /**
     * get Dal_Hotel_Huser default
     *
     * @return Dal_Hotel_Config
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        
        return self::$_instance;
    }
    
    public function getCurrentRoomCustomer($uid)
    {
        $sql = "SELECT * FROM hotel_user_log WHERE uid=:uid AND salary=0";
        
        return $this->_rdb->fetchOne($sql, array('uid'=>$uid));
    }
    
    public function getRoomType()
    {
        $sql = "SELECT * FROM hotel_room_type";
        
        return $this->_rdb->fetchAll($sql);
    }
    
    public function getRestaurantType()
    {
        $sql = "SELECT * FROM hotel_restaurant_type";
        
        return $this->_rdb->fetchAll($sql);
    }
    
    public function getCleanOccupancy()
    {
        $sql = "SELECT * FROM hotel_clean";
        
        return $this->_rdb->fetchAll($sql);
    }
}
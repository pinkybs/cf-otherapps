<?php

require_once 'Dal/Abstract.php';

/**
 * Dal Hotel Bacth
 * MixiApp hotel batch Data Access Layer
 *
 * @package    Dal/Hotel
 * @copyright  Copyright (c) 2009 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/09/20    huch
 */
class Dal_Hotel_Batch extends Dal_Abstract
{
    protected static $_instance;
    
    /**
     * get Dal_Hotel_Huser default
     *
     * @return Dal_Hotel_Batch
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        
        return self::$_instance;
    }
    
    /**
     * get user visit date
     *
     * @param integer $uid
     * @return date 
     */
    public function getUserVisitDate($uid)
    {
        $sql = "SELECT last_visit_date FROM hotel_user WHERE uid=:uid";
        
        return $this->_rdb->fetchOne($sql, array('uid'=>$uid));
    }
    
    public function getUserOperateDate($uid)
    {
        $sql = "SELECT last_operate_date FROM hotel_user WHERE uid=:uid";
        
        return $this->_rdb->fetchOne($sql, array('uid'=>$uid));
    }
    
    /**
     * update user visit date
     *
     * @param integer $uid
     * @param string $date
     */
    public function updateUserVisitDate($uid, $date)
    {
        $sql = "UPDATE hotel_user SET last_visit_date=:date WHERE uid=:uid";
        
        $this->_wdb->query($sql, array('date'=>$date, 'uid'=>$uid));
    }
    
    public function getUserHotelIncome($uid, $month)
    {
        $sql = "SELECT SUM(fee) FROM hotel_system_feed WHERE game_date LIKE '$month%' AND uid=:uid";
        
        return $this->_rdb->fetchOne($sql, array('uid'=>$uid));
    }
    
    public function updateUserMoney($uid, $money)
    {
        $sql = "UPDATE hotel_user SET money=money+:money WHERE uid=:uid";
        
        $this->_wdb->query($sql, array('money'=>$money, 'uid'=>$uid));
    }
    
    public function getRoomCustomer($uid, $date)
    {
        $sql = "SELECT * FROM hotel_user_log WHERE uid=:uid AND game_time=:date";
        
        return $this->_rdb->fetchRow($sql, array('uid'=>$uid, 'date'=>$date));
    }
    
    public function insertUserLog($uid, $room1, $room2, $room3, $date, $income)
    {
        $sql = "REPLACE INTO hotel_user_log SET uid=:uid,room1=:room1,room2=:room1,room3=:room1,game_time=:date,income=:income";
        
        $this->_wdb->query($sql, array('uid'=>$uid, 'room1'=>$room1, 'room2'=>$room2, 'room3'=>$room3, 'date'=>$date, 'income'=>$income));
    }
    
    public function updateUserClean($uid, $clean)
    {
        $sql = "UPDATE hotel_user SET clean=:clean WHERE uid=:uid";
        
        $this->_wdb->query($sql, array('clean'=>$clean, 'uid'=>$uid));
    }    
}
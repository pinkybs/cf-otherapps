<?php

require_once 'Dal/Abstract.php';

/**
 * Dal Hotel Cus
 * MixiApp hotel Cus Data Access Layer
 *
 * @package    Dal/Hotel
 * @copyright  Copyright (c) 2009 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/11/02    Zhaoxh
 */
class Dal_Hotel_Cus extends Dal_Abstract
{
    /**
     * tech table name
     *
     * @var string
     */
    protected $table_user = 'hotel_user_cusclick';

    protected static $_instance;

    /**
     * get Dal_Hotel_Cus default
     *
     * @return Dal_Hotel_Cus
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }


    /**
     * insert a cusclick data
     *
     * @param array $info
     */
    public function insertCusClick($info)
    {
        $this->_wdb->insert($this->table_user, $info);
    }

    /**
     * get whether today can cusclick or not
     *
     * @param string $uid
     * @return boolean
     */
	public function canInsertToday($uid)
    {
        $t = time();
    	$sql = "SELECT COUNT(1) FROM hotel_user_cusclick WHERE $t - create_time < 24*60*60 AND uid=:uid";
    	$re = $this->_rdb->fetchOne($sql,array('uid' => $uid));
    	return 5 - $re > 0;
    }
    
    
    
    public function updateCusById($uid,$opresult=1)
    {
    	$t = time();
    	$sqll = "SELECT min(id) FROM hotel_user_cusclick WHERE $t - create_time < 24*60*60 AND uid=:uid AND operated = 0";
    	$id = $this->_rdb->fetchOne($sqll,array('uid' => $uid));
    	
        $sql = "UPDATE hotel_user_cusclick SET operated = $opresult WHERE id = :id";
        return $this->_wdb->query($sql, array('id' => $id));
    }

    /**
     * get count Operated is zero
     *
     * @param integer $uid
     * @param integer $operated
     * @return array
     */
    public function countOperatedById($uid, $operated=0)
    {
    	$t = time();
    	$sql = "SELECT COUNT(1) FROM hotel_user_cusclick WHERE uid = :uid AND operated = :operated AND $t - create_time < 24*60*60";
    	return $this->_rdb->fetchOne($sql, array('uid' => $uid, 'operated' => $operated));
    }
}
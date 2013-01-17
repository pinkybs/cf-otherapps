<?php

require_once 'Mdal/Abstract.php';

class Mdal_Parking_Store extends Mdal_Abstract
{
    protected static $_instance;

    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }
	/**
     * check if user have 有料駐車場カード
     * @author lp
     * @param integer $uid
     */
	public function haveFreeCard($uid)
	{
		$sql = "SELECT count FROM parking_user_card WHERE uid=:uid AND sid=1";
		return $this->_rdb->fetchOne($sql, array('uid' => $uid));
	}

    /**
     * get user all items
     * @author lp
     * @param integer $uid
     * @return array
     */
    public function getUserAllItems($uid)
    {
        $sql="SELECT IFNULL(pc.pc_uid,:uid) AS uid,IFNULL(pc.count,0) AS count,
                     ps.sid,ps.name,ps.introduce,ps.price,
                     pu.last_bribery_time,pu.last_evasion_time,pu.last_check_time,pu.insurance_card
              FROM parking_user AS pu
              INNER JOIN
              (SELECT uid AS pc_uid,sid,count FROM parking_user_card WHERE uid=:uid) AS pc
              ON pu.uid=pc.pc_uid
              RIGHT JOIN
              (SELECT * FROM parking_store) AS ps ON pc.sid=ps.sid";
        return $this->_rdb->fetchAll($sql, array('uid' => $uid));
    }
    /**
     * get user all items
     * @author lp
     * @param integer $uid
     */
    public function getUserTimeLimitCardContinueTime($uid)
    {
    	$sql = "SELECT uid,last_bribery_time,last_evasion_time,last_check_time,insurance_card
    	        FROM parking_user WHERE uid=:uid";
    	return $this->_rdb->fetchRow($sql, array('uid' => $uid));
    }
}
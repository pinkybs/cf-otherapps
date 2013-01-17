<?php

require_once 'Dal/Abstract.php';

class Dal_Parking_Item extends Dal_Abstract
{
	public function hasCard($uid, $sid)
	{
		$sql = "SELECT count FROM parking_user_card WHERE uid=:uid AND sid=:sid";
		$result = $this->_rdb->fetchOne($sql, array('uid' => $uid, 'sid' => $sid));

		return $result > 0;
	}

	public function updateUserCard($uid, $sid)
	{
		$sql = "UPDATE parking_user_card SET count=count-1 WHERE uid=:uid AND sid=:sid";
		$this->_wdb->query($sql, array('uid' => $uid, 'sid' => $sid));
	}


	public function updateUserBomb($uid, $bomb)
	{
		$where = $this->_wdb->quoteinto('uid = ?', $uid);
        $this->_wdb->update('parking_user_bomb', $bomb, $where);
	}

	public function updateUserYanki($uid, $yanki)
	{
		$where = $this->_wdb->quoteinto('uid = ?', $uid);
        $this->_wdb->update('parking_user_yanki', $yanki, $where);
	}
	/**
     *
     * @author lp
     */
	public function updateUserBribery($uid, $time)
	{
		$sql = "UPDATE parking_user SET last_bribery_time=:last_bribery_time WHERE uid=:uid ";
        $this->_wdb->query($sql, array('last_bribery_time' => $time,'uid'=>$uid));
	}
    /**
     *
     * @author lp
     */
    public function updateUserCheck($uid, $time)
    {
        $sql = "UPDATE parking_user SET last_check_time=:last_check_time WHERE uid=:uid ";
        $this->_wdb->query($sql, array('last_check_time' => $time,'uid'=>$uid));
    }
    /**
     *
     * @author lp
     */
    public function getInsuranceState($uid)
    {
    	$sql = "SELECT insurance_card FROM parking_user WHERE uid=:uid";
    	return $this->_rdb->fetchRow($sql,array('uid'=>$uid));
    }

    /**
     * update user insurance card
     *
     * @param string $uid
     * @param integer $chage
     * @return void
     */
    public function updateUserInsuranceCount($uid, $change)
    {
        $sql = " UPDATE parking_user SET insurance_card=insurance_card+:change WHERE uid=:uid";
        $this->_wdb->query($sql,array('uid'=>$uid, 'change'=>$change));
    }

    /**
     *
     * @author lp
     */
    public function updateUserInsurance($uid)
    {
    	$sql = " UPDATE parking_user SET insurance_card=1 WHERE uid=:uid";
    	$this->_wdb->query($sql,array('uid'=>$uid));
    }
    /**
     *
     * @author lp
     */
    public function updateUserGuard($uid)
    {
    	$sql = "UPDATE parking_user_yanki SET location1=0,location2=0,location1=0,location3=0,location4=0,location5=0,location6=0,location7=0,location8=0
    	        WHERE uid=:uid";
    	$this->_wdb->query($sql,array('uid'=>$uid));
    }
    /**
     *
     * @author lp
     */
    public function updateUserEvasion($uid, $time)
    {
        $sql = "UPDATE parking_user SET last_evasion_time=:last_evasion_time WHERE uid=:uid ";
        $this->_wdb->query($sql, array('last_evasion_time' => $time,'uid'=>$uid));

    }


	/**
     * select user location bomb card info
     *
     */
    public function updateBombCount($uid, $location)
	{
        $sql = "UPDATE parking_user_bomb SET " . $location . " =0 WHERE uid=:uid ";

		$this->_wdb->query($sql, array('uid' => $uid));
    }

	/**
     * select user location bomb card info
     *
     */
    public function getBombInfo($uid)
	{
        $sql = "SELECT * FROM parking_user_bomb WHERE uid=:uid ";
		return $this->_rdb->fetchRow($sql,array('uid'=>$uid));

    }

    /**
     * select user location bomb card info
     *
     */
    public function getYankiInfo($uid)
    {
        $sql = "SELECT * FROM parking_user_yanki WHERE uid=:uid ";
        return $this->_rdb->fetchRow($sql,array('uid'=>$uid));

    }

    /**
     * used after user use a card
     * @author lp
     */
    public function getUsedCardInfo($uid, $sid)
    {
    	$sql = "SELECT puc.*,ps.name,pu.last_bribery_time,pu.last_evasion_time,pu.last_check_time,pu.insurance_card
                FROM parking_user_card AS puc,parking_user AS pu,parking_store AS ps
                WHERE puc.uid=:uid AND puc.sid=:sid AND pu.uid=puc.uid AND ps.sid=puc.sid";
        return $this->_wdb->fetchRow($sql, array('uid' => $uid,'sid'=>$sid));
    }
    /**
     * get user's bribery time
     * @author lp
     */
    public function getUserBriberyTime($uid)
    {
    	$sql = "SELECT last_bribery_time FROM parking_user WHERE uid=:uid";
    	return $this->_rdb->fetchOne($sql, array('uid'=>$uid));
    }
    /**
     * get user's check time
     * @author lp
     */
    public function getUserCheckTime($uid)
    {
        $sql = "SELECT last_check_time FROM parking_user WHERE uid=:uid";
        return $this->_rdb->fetchOne($sql, array('uid'=>$uid));
    }
    /**
     * get user's evasion time
     * @author lp
     */
    public function getUserEvasionTime($uid)
    {
        $sql = "SELECT last_evasion_time FROM parking_user WHERE uid=:uid";
        return $this->_rdb->fetchOne($sql, array('uid'=>$uid));
    }
    /**
     * insert minifeed
     * @author lp
     * @param array $info
     * @return integer
     */
    public function insertMinifeed($info)
    {
        $this->_wdb->insert('parking_minifeed', $info);
        return $this->_wdb->lastInsertId();
    }

    /**
     * insert newsfeed
     * @author lp
     * @param array $info
     * @return integer
     */
    public function insertNewsfeed($info)
    {
        $this->_wdb->insert('parking_newsfeed', $info);
        return $this->_wdb->lastInsertId();
    }

}
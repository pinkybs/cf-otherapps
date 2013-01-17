<?php

require_once 'Mdal/Abstract.php';

/**
 * Mdal Disney Cup
 * MixiApp Disney Cup Data Access Layer
 *
 * @package    Mdal/Disney
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/10/12    Liz
 */
class Mdal_Disney_Log extends Mdal_Abstract
{    
    protected static $_instance;

    /**
     * get default instance
     *
     * @return Mdal_Disney_Log
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function insertPay($info)
    {
    	$this->_wdb->insert('disney_log_pay', $info);
    }
    
    public function insertTicket($info)
    {
    	$this->_wdb->insert('disney_log_ticket', $info);
    }
    
    public function insertTrade($info)
    {
    	$this->_wdb->insert('disney_log_trade', $info);
    }
    
    public function updateTradeStatus($nid, $status) 
    {
    	$sql = "UPDATE disney_log_trade SET status=:status WHERE nid=:nid";
    	$this->_wdb->query($sql,array('status'=>$status, 'nid'=>$nid));
    }
    
    public function insertAward($info)
    {
    	$this->_wdb->insert('disney_log_award', $info);
    }
    
    public function insertGet($info)
    {
    	$this->_wdb->insert('disney_log_getcurrent', $info);
    }
    
    public function insertCheck($info)
    {
    	$this->_wdb->insert('disney_log_check', $info);
    }
    
    public function insertShoes($info)
    {
        $this->_wdb->insert('disney_log_shoes', $info);
    }
    
    public function getUserBaseInfo($uid)
    {
    	$sql = "SELECT uid,FROM_UNIXTIME(create_time) AS join_date,game_point FROM disney_user WHERE uid=:uid";
    	return $this->_rdb->fetchRow($sql, array('uid'=>$uid));
    }
    
    public function getFriends($fids)
    {
    	$fids = $this->_rdb->quote($fids);
    	
    	$sql = "SELECT uid FROM disney_user WHERE uid IN ($fids)";
    	return $this->_rdb->fetchAll($sql);
    }
    
    public function getAward($uid)
    {
    	$sql = "SELECT a.pid,b.mixi_name FROM disney_user_award AS a,disney_place AS b WHERE a.pid=b.pid AND uid=:uid";
    	return $this->_rdb->fetchAll($sql, array('uid'=>$uid));
    }
    
    public function getCup($uid)
    {
    	$sql = "SELECT a.cid,b.name FROM disney_user_cup AS a,disney_cup AS b WHERE a.cid=b.cid AND uid=:uid";
    	return $this->_rdb->fetchAll($sql, array('uid'=>$uid));
    }
    
    public function getGetCurrent($uid, $startTime, $endTime)
    {
    	$sql = "SELECT a.pid,b.award_name,a.coordinate,FROM_UNIXTIME(a.create_time) AS create_time FROM disney_log_getcurrent AS a,
				disney_place AS b WHERE a.create_time>$startTime AND a.create_time<$endTime AND a.pid=b.pid AND uid=:uid";
    	
    	return $this->_rdb->fetchAll($sql, array('uid'=>$uid));
    }
    
    public function getCheck($uid, $startTime, $endTime)
    {
    	$sql = "SELECT a.uid,a.coordinate,distance,FROM_UNIXTIME(a.create_time) AS create_time FROM disney_log_check AS a 
    			WHERE a.create_time>$startTime AND a.create_time<$endTime AND uid=:uid";
    	return $this->_rdb->fetchAll($sql, array('uid'=>$uid));
    }
    
    public function getTrade($uid, $startTime, $endTime)
    {
    	$sql = "SELECT a.fid,CONCAT( a.pid_u,'/',a.pid_f) AS content,FROM_UNIXTIME(a.create_time) AS create_time,
				CASE WHEN `status`=1 THEN '承認待ち' WHEN `status`=2 THEN 'キャンセル' ELSE '承認済み' END AS `status`
				FROM disney_log_trade AS a WHERE a.create_time>$startTime AND a.create_time<$endTime AND a.uid=:uid";
    	return $this->_rdb->fetchAll($sql, array('uid'=>$uid));
    }
    
    public function getInvite($uid, $startTime, $endTime)
    {
    	$sql = "SELECT uid,
				CASE WHEN `status`=0 THEN '未登録'
				WHEN `status`>0 AND place=0 THEN '登録不完全' 
				ELSE '登録済み' END AS `status` FROM
				(SELECT a.fid AS uid,IFNULL(b.uid,0) AS `status`,IFNULL(b.current_place,0) AS place 
				FROM disney_invite AS a LEFT JOIN disney_user AS b ON a.fid=b.uid WHERE a.uid=:uid) AS d";
    	
    	return $this->_rdb->fetchAll($sql, array('uid'=>$uid));
    }
    
    public function getPayTicket($uid, $startTime, $endTime)
    {
    	$sql = "SELECT content,FROM_UNIXTIME(create_time) AS create_time FROM disney_log_pay AS a
    			WHERE  a.create_time>$startTime AND a.create_time<$endTime AND `type`=1 AND uid=:uid";

    	return $this->_rdb->fetchAll($sql, array('uid'=>$uid));
    }
    
    public function getUseTicket($uid, $startTime, $endTime)
    {
    	$sql = "SELECT distance,FROM_UNIXTIME(create_time) AS create_time FROM disney_log_ticket AS a 
    			WHERE a.create_time>$startTime AND a.create_time<$endTime AND uid=:uid";
    	return $this->_rdb->fetchAll($sql, array('uid'=>$uid));
    }
    
    public function getPayDownload($uid, $startTime, $endTime)
    {
    	$sql = "SELECT FROM_UNIXTIME(a.create_time) AS create_time,b.award_name FROM disney_log_pay AS a,
    			disney_place AS b WHERE a.create_time>$startTime AND a.create_time<$endTime AND a.content=b.pid AND a.`type`=2 AND uid=:uid";
    	return $this->_rdb->fetchAll($sql, array('uid'=>$uid));
    }
    
    public function getPaySend($uid, $startTime, $endTime)
    {
    	$sql = "SELECT FROM_UNIXTIME(a.create_time) AS create_time,b.award_name FROM disney_log_pay AS a,
    			disney_place AS b WHERE a.create_time>$startTime AND a.create_time<$endTime AND a.content=b.pid AND a.`type`=3 AND uid=:uid";
    	return $this->_rdb->fetchAll($sql, array('uid'=>$uid));
    }
    
    public function getPayDesk($uid, $startTime, $endTime)
    {
    	$sql = "SELECT FROM_UNIXTIME(a.create_time) AS create_time,b.award_name FROM disney_log_pay AS a,
    			disney_place AS b WHERE a.create_time>$startTime AND a.create_time<$endTime AND a.content=b.pid AND a.`type`=4 AND uid=:uid";
    	return $this->_rdb->fetchAll($sql, array('uid'=>$uid));
    }
    
    public function getShoes($uid, $startTime, $endTime)
    {
    	$sql = "SELECT FROM_UNIXTIME(a.create_time) AS create_time,b.name as shoes_name FROM disney_log_pay AS a,
    			disney_payment_type AS b WHERE a.create_time>$startTime AND a.create_time<$endTime AND a.content=b.id AND a.`type`=5 AND uid=:uid";
    	return $this->_rdb->fetchAll($sql, array('uid'=>$uid));
    }
    
    public function getUserShoes($uid, $startTime, $endTime)
    {
        $sql = "SELECT FROM_UNIXTIME(a.create_time) AS create_time,distance,magni,shoes FROM disney_log_shoes AS a 
                WHERE a.create_time>$startTime AND a.create_time<$endTime AND uid=:uid";
        
        return $this->_rdb->fetchAll($sql, array('uid'=>$uid));
    }
}
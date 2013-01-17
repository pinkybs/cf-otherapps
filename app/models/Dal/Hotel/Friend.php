<?php

require_once 'Dal/Abstract.php';

/**
 * Dal Hotel friend
 * MixiApp hotel friend Data Access Layer
 *
 * @package    Dal/Hotel
 * @copyright  Copyright (c) 2009 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/09/16    Zhaoxh
 */
class Dal_Hotel_Friend extends Dal_Abstract
{
    /**
     * Friend table name
     *
     * @var string
     */
    protected $table_user = 'hotel_user_learn';

    protected static $_instance;

    /**
     * get Dal_Hotel_Friend default
     *
     * @return Dal_Hotel_Friend
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * insert a learner data
     *
     * @param array $learnerInfo
     */
    public function insertLearner($learnerInfo)
    {

        $this->_wdb->insert($this->table_user, $learnerInfo);
    }

    /**
     * user has idle learner or not
     *
     * @param unknown_type $uid
     * @return array
     */
    public function hasLearner($uid)
    {
        $sql = "SELECT `index` FROM $this->table_user WHERE uid=:uid AND fid=0";
        $result = $this->_rdb->fetchAll($sql,array('uid'=>$uid));

        return $result;
    }

    /**
     * now there is no learner at fid
     *
     * @param string $uid
     * @param string $fid
     * @return boolean
     */
    public function noLearnerAt($uid,$fid)
    {
        $sql = "SELECT COUNT(1) FROM $this->table_user WHERE uid=:uid AND fid=:fid";
        $result = $this->_rdb->fetchOne($sql,array('uid'=>$uid,'fid' => $fid));

        return $result == 0;
    }

    /**
     * send a learner to friend  ||  call it back
     *
     * @param string $uid
     * @param string $fid
     * @param string $index
     */
    public function setLearner($uid,$fid,$index,$t=0)
    {
        $sql = "UPDATE $this->table_user SET fid=:fid,create_time=$t WHERE uid=:uid AND `index`=:index";
        $this->_wdb->query($sql,array('uid' => $uid,'fid' => $fid,'index' => $index));
    }

    /**
     * is in time limit
     *
     * @param string $uid
     * @param string $index
     * @param number $timeLimit
     * @return boolean
     */
    public function inTimeLimit($uid,$index,$timeLimit=1800)
    {
        $timeNow = time();
        $sql = "SELECT create_time FROM $this->table_user WHERE uid=:uid AND `index`=:index";
        $timeCreate = $this->_rdb->fetchOne($sql,array('uid' => $uid,'index' => $index));

        return $timeNow - $timeCreate <= $timeLimit;
    }

    /**
     * get fid by uid and index
     *
     * @param string $uid
     * @param string $index
     * @return string
     */
    public function getFid($uid,$index)
    {
        $sql = "SELECT fid FROM $this->table_user WHERE uid=:uid AND `index`=:index";
        $fid = $this->_rdb->fetchOne($sql,array('uid' => $uid,'index' => $index));

        return $fid;
    }

    /**
     * compute learner income
     *
     * @param string $uid
     * @param string $index
     * @return number
     */
    public function earnLearner($uid,$index)
    {
        $sqll = "SELECT a.effect FROM hotel_technology_type AS a,hotel_user_technology as b WHERE b.learn=a.level AND a.name='learn' AND b.uid=:uid";
        $effect = $this->_rdb->fetchOne($sqll,array('uid' => $uid));

        $sql = "SELECT create_time FROM $this->table_user WHERE uid=:uid AND `index`=:index";
        $create_time = $this->_rdb->fetchOne($sql,array('uid' => $uid,'index' => $index));

        $earn = ceil(min((time() - $create_time) * $effect / 3600 , 12 * $effect));
        return $earn;
    }

    /**
     * return whether fid has idle place
     *
     * @param string $fid
     * @return boolean
     */
    public function friendHasPlace($fid)
    {
        $sql = "SELECT COUNT(1) FROM hotel_user_learn WHERE fid=:fid";
        $re = $this->_rdb->fetchOne($sql,array('fid' => $fid));
        return $re < 3;
    }

    /**
     * clean friend hotel
     *
     * @param string $fid
     */
    public function clean($fid)
    {
        $sql = "UPDATE hotel_user SET clean = 100 WHERE uid =:fid";
        $this->_wdb->query($sql,array('fid' => $fid));

    }
    
    /**
     * count user idle learner ,not total learner cnt
     *
     * @param string $uid
     * @return string
     */
    public function learnCnt($uid)
    {
    	$sql = "SELECT COUNT(1) FROM hotel_user_learn WHERE uid=:uid AND fid=0";
    	$re = $this->_rdb->fetchOne($sql,array('uid' => $uid));
    	return $re;
    }
    
    /**
     * exchange customer 
     *
     * @param array $info
     * @return array
     */
    public function insertExchange($info)
    {
    	$this->_wdb->insert('hotel_user_exchange', $info);
    	
    }

    /**
     * get count of times that  --   you can exchange today
     *
     * @param string $uid
     * @return int
     */
	public function exchangeCnt($uid)
    {
    	$t = time();
    	$sql = "SELECT COUNT(1) FROM hotel_user_exchange WHERE $t - create_time < 24*60*60 AND (uid=:uid OR fid=:uid)";
    	$re = $this->_rdb->fetchOne($sql,array('uid' => $uid));
    	return 3 - $re;
    	
    }
    
    /**
     * check today has changed or not
     *
     * @param string $uid
     * @param string $fid
     * @return boolean
     */
	public function todayExchanged($uid,$fid)
    {
    	$t = time();
    	$sql = "SELECT count(1) FROM hotel_user_exchange WHERE $t - create_time < 24*60*60 AND
    	 ((uid=:uid AND fid=:fid) OR (fid=:uid AND uid=:fid))";
    	$re = $this->_rdb->fetchOne($sql,array('uid' => $uid,'fid' => $fid));
    	return $re > 0;
    	
    }
    
    /**
     * get user learner info
     *
     * @param string $uid
     * @return array
     */
	public function getLearnInfo($uid)
    {
    	$sql = "SELECT uid,fid,create_time,`index` FROM hotel_user_learn WHERE uid=:uid ORDER BY id";
    	$re = $this->_rdb->fetchAll($sql,array('uid' => $uid));
    	return $re;
    }
//********************************************************************************

    public function getMySpyListById($uid, $page, $pageSize)
    {
    	$start = ($page - 1) * $pageSize;

    	$sql = "SELECT fid, create_time, `index` FROM $this->table_user WHERE uid=:uid LIMIT $start, $pageSize";
        $result = $this->_rdb->fetchAll($sql, array('uid' => $uid));
        return $result;
    }

    public function getLearnCountById($uid)
    {
        $sql = "SELECT COUNT(1) FROM hotel_user_learn WHERE uid=:uid";
        $re = $this->_rdb->fetchOne($sql,array('uid' => $uid));
        return $re;
    }

    public function getEnemyListById($uid)
    {
        $sql = "SELECT uid, create_time, `index` FROM $this->table_user WHERE fid=:uid ";
        $result = $this->_rdb->fetchAll($sql, array('uid' => $uid));
        return $result;
    }
}
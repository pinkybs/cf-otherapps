<?php

require_once 'Dal/Abstract.php';

/**
 * Dal Slave
 * MixiApp Slave Data Access Layer
 *
 * @package    Dal/Slave
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/06/23    zhangxin
 */
class Dal_Slave_Slave extends Dal_Abstract
{

    /**
     * class default instance
     * @var self instance
     */
    protected static $_instance;

    /**
     * return self's default instance
     *
     * @return self instance
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * get slave by id
     *
     * @param string $uid
     * @return array
     */
    public function getSlaveById($uid)
    {
        $sql = 'SELECT * FROM slave_user WHERE uid=:uid ';
        return $this->_rdb->fetchRow($sql, array('uid' => $uid));
    }

    /**
     * get slave by id lock row
     *
     * @param string $uid
     * @return array
     */
    public function getSlaveByIdLock($uid)
    {
        $sql = 'SELECT * FROM slave_user WHERE uid=:uid FOR UPDATE';
        return $this->_rdb->fetchRow($sql, array('uid' => $uid));
    }

    /**
     * insert slave
     *
     * @param array $info
     * @return integer
     */
    public function insertSlave($info)
    {
        return $this->_wdb->insert('slave_user', $info);
    }

    /**
     * update slave
     *
     * @param array $info
     * @param string $uid
     * @return integer
     */
    public function updateSlave($info, $uid)
    {
        $where = $this->_wdb->quoteInto('uid = ?', $uid);
        return $this->_wdb->update('slave_user', $info, $where);
    }

    /**
     * get slave current price rank
     *
     * @param string $uid
     * @return integer
     */
    public function getSlavePriceRank($uid)
    {
        $sql = 'SELECT (COUNT(uid)+1) AS rank_price FROM slave_user
                WHERE price>(SELECT price FROM slave_user WHERE uid=:uid)';

        return $this->_rdb->fetchOne($sql, array('uid' => $uid));
    }

    /**
     * get slave current cash rank
     *
     * @param string $uid
     * @return integer
     */
    public function getSlaveCashRank($uid)
    {
        $sql = 'SELECT (COUNT(uid)+1) AS rank_cash FROM slave_user
                WHERE cash>(SELECT cash FROM slave_user WHERE uid=:uid)';

        return $this->_rdb->fetchOne($sql, array('uid' => $uid));
    }

    /**
     * list popular slave (order by price)
     *
     * @param Integer $pageindex
     * @param Integer $pagesize
     * @return array
     */
    public function listPopularSlave($pageindex = 1, $pagesize = 10)
    {
        $start = ($pageindex - 1) * $pagesize;
        $sql = "SELECT uid,nickname,FORMAT(price,0) AS format_price FROM slave_user
                ORDER BY price DESC LIMIT $start, $pagesize ";

        return $this->_rdb->fetchAll($sql);
    }

    /**
     * get popular slave count(order by price)
     *
     * @return integer
     */
    public function getPopularSlaveCount()
    {
        $sql = 'SELECT COUNT(uid) FROM slave_user';
        return $this->_rdb->fetchOne($sql);
    }

    /**
     * list slave by uid
     *
     * @param string $uid
     * @return array
     */
    public function listSlaveByUid($uid)
    {
        $sql = "SELECT uid,nickname,balloon,price,FORMAT(price,0) AS format_price,last_login_time,status FROM slave_user
        		WHERE master_id=:master_id
                ORDER BY price DESC ";

        return $this->_rdb->fetchAll($sql, array('master_id' => $uid));
    }

    /**
     * is slave by master id
     *
     * @param string $uid
     * @param string $masterId
     * @return boolean
     */
    public function isSlaveByMasterId($uid, $masterId)
    {
        $sql = 'SELECT COUNT(*) FROM slave_user WHERE uid=:uid AND master_id=:master_id ';
        $result = $this->_rdb->fetchOne($sql, array('uid' => $uid, 'master_id' => $masterId));
        return $result > 0 ? true : false;
    }

    /**
     * get all slave price by master
     *
     * @param string $uid
     * @return integer
     */
    public function getSlavePriceByMaster($uid)
    {
        $sql = 'SELECT IFNULL(SUM(price),0) FROM slave_user WHERE master_id=:master_id ';
        return $this->_rdb->fetchOne($sql, array('master_id' => $uid));
    }

    /**
     * get total assets by uid
     *
     * @param string $uid
     * @return integer
     */
    public function getTotalAssetsById($uid)
    {
        $sql = "SELECT IFNULL(u.cash,0) + IFNULL(u.total_slave_price,0) + IFNULL(u.total_gift_price,0) FROM slave_user u WHERE u.uid=:uid ";
        return $this->_rdb->fetchOne($sql, array('uid' => $uid));
    }

	/**
     * list price rank all
     *
     * @param Integer $rankStart
     * @param Integer $fetchSize
     * @return array
     */
    public function listPriceRankAll($rankStart = 1, $fetchSize = 10)
    {
        $start = $rankStart > 0 ? ($rankStart - 1) : 0;
        $sql = "SELECT uid,price FROM slave_user ORDER BY price DESC LIMIT $start, $fetchSize ";

        return $this->_rdb->fetchAll($sql);
    }

    /**
     * get price rank all count
     *
     * @return integer
     */
    public function getPriceRankAllCount()
    {
        $sql = 'SELECT COUNT(uid) FROM slave_user';
        return $this->_rdb->fetchOne($sql);
    }

	/**
     * list need to recovery health slave lock row
     *
     * @param void
     * @return array
     */
    public function listSlaveRecoveryHealthLock()
    {
        $sql = 'SELECT uid,health FROM slave_user WHERE health<10 FOR UPDATE';
        return $this->_rdb->fetchAll($sql);
    }

	/**
     * update recovery health slave (health + 1)
     *
     * @return integer
     */
    public function recoverySlaveHealth()
    {
        $sql = 'UPDATE slave_user SET health=health+1 WHERE health<10 ';
        return $this->_wdb->query($sql);
    }

	/**
     * get master's cheapest slave uid
     *
     * @param $uid
     * @return array
     */
    public function getCheapestSlaveByMaster($uid)
    {
        $sql = 'SELECT uid FROM slave_user WHERE master_id=:uid ORDER BY price';
        return $this->_rdb->fetchRow($sql, array('uid' => $uid));
    }

    /******************************************************/

    /**
     * list slave shop all firends
     *
     * @param : string $uid
     * @param integer $pageindex
     * @param integer $pagesize
     * @param integer $oder_by
     * @return array
     */
    public function listSlaveShopFirends($uid, $pageindex = 1, $pagesize = 10, $sort = 0)
    {
    	//$fids = $this->_rdb->quote($fids);
        $start = ($pageindex - 1) * $pagesize;

        $sql = " SELECT a.* FROM (SELECT m.fid AS uid,u.nickname,u.balloon,IFNULL(u.price,4980) AS price,u.cash,u.slave_count,
                 u.master_id,u.total_gift_price FROM mixi_friend AS m LEFT JOIN slave_user AS u
                 ON u.uid = m.fid WHERE m.uid = :uid) AS a
				 ORDER BY a.price ";

        /*$sql = " SELECT u.uid,u.nickname,u.balloon,IFNULL(u.price,4980) AS price,u.cash,u.slave_count,
                 u.master_id,u.total_gift_price FROM slave_user AS u
                 WHERE u.uid IN ($fids) ORDER BY u.price ";*/

        if (1 == $sort) {
            $sql .= "DESC ";
        }
        $sql .= "LIMIT $start,$pagesize";
        return $this->_rdb->fetchAll($sql, array('uid' => $uid));
    }

    /**
     * get mixi friend count
     * @param : string $uid
     * @return :integer
     */
    public function getMixiFriendById($uid)
    {
    	$sql = "SELECT count(*) FROM mixi_friend AS m WHERE m.uid =:uid";
    	return $this->_rdb->fetchOne($sql, array('uid' => $uid));
    }

    /**
     * list slave shop all Mixi user
     *
     * @param integer $pageindex
     * @param integer $pagesize
     * @param integer $oder_by
     * @return array
     */
    public function listSlaveShopMixiUser($pageindex = 1, $pagesize = 10, $sort = 0)
    {
        $start = ($pageindex - 1) * $pagesize;

        $sql = " SELECT a.* FROM (SELECT m.id AS uid,u.nickname,u.balloon,IFNULL(u.price,4980) AS price,u.cash,u.slave_count,
        		 u.master_id,u.total_gift_price
				 FROM mixi_user AS m LEFT JOIN slave_user AS u
				 ON u.uid = m.id ) AS a
				 ORDER BY a.price ";
        if (1 == $sort) {
            $sql .= " DESC";
        }
        $sql .= " LIMIT $start,$pagesize";
        return $this->_rdb->fetchAll($sql);
    }

    /**
     * get mixi user count
     * @return integer
     */
    public function getMixiuserCount()
    {
        $sql = " SELECT COUNT(id) FROM mixi_user ";
        return $this->_rdb->fetchOne($sql);
    }

    /**
     * get slave total rank
     * @param :string $uid
     * @return:integer
     */
    public function getTotalRankById($uid)
    {
        $sql = " SELECT (COUNT(s.uid)+1) AS total_rank FROM slave_user AS s WHERE
    			 s.cash + s.total_slave_price + s.total_gift_price >
    			 (SELECT u.cash + u.total_slave_price + u.total_gift_price FROM slave_user AS u WHERE u.uid=:uid) ";
        return $this->_rdb->fetchOne($sql, array('uid' => $uid));
    }

    /**
     * get slave cash by id
     * @param :string $uid
     * @return:integer
     */
    public function getCashById($uid)
    {
        $sql = " SELECT cash FROM slave_user WHERE uid = :uid ";
        return $this->_rdb->fetchOne($sql, array('uid' => $uid));
    }

    /**
     * get mixiuser info by id
     * @param :string $uid
     * @return:array
     */
    public function getMixiUserById($id)
    {
        $sql = " SELECT * FROM mixi_user WHERE id = :id ";
        return $this->_rdb->fetchRow($sql, array('id' => $id));
    }

    /**
     * get slave price by id
     * @param :string $uid
     * @return:integer
     */
    public function getPriceById($uid)
    {
        $sql = " SELECT price FROM slave_user WHERE uid = :uid ";
        return $this->_rdb->fetchOne($sql, array('uid' => $uid));
    }

	/**
     * list price rank friend
     *
     * @param : string $uid
     * @param : Integer $rankStart
     * @param : Integer $fetchSize
     * @return: array
     */
    public function listPriceRankFriend($uid , $rankStart = 1, $fetchSize = 10)
    {
        $start = $rankStart > 0 ? ($rankStart - 1) : 0;

		$sql = "SELECT u.uid,u.price FROM slave_user AS u WHERE u.uid IN
				(SELECT fid FROM slave_friend WHERE uid=:uid OR fid=:uid)
				ORDER BY u.price DESC LIMIT $start, $fetchSize";

        return $this->_rdb->fetchAll($sql , array('uid' => $uid));
    }

	/**
     * list total rank friend
     *
     * @param : string $uid
     * @param : Integer $rankStart
     * @param : Integer $fetchSize
     * @return: array
     */
    public function listTotalRankFriend($uid , $rankStart = 1, $fetchSize = 10)
    {
        $start = $rankStart > 0 ? ($rankStart - 1) : 0;
 		$sql = "SELECT u.uid,(u.cash + u.total_slave_price + u.total_gift_price) as rank_total
 				 FROM slave_user AS u WHERE u.uid IN
				 (SELECT fid FROM slave_friend WHERE uid=:uid OR fid=:uid)
				 ORDER BY rank_total DESC LIMIT $start, $fetchSize";
        return $this->_rdb->fetchAll($sql , array('uid' => $uid));
    }

/**
     * list total rank all
     *
     * @param : Integer $rankStart
     * @param : Integer $fetchSize
     * @return: array
     */
    public function listTotalRankAll($rankStart = 1, $fetchSize = 10)
    {
        $start = $rankStart > 0 ? ($rankStart - 1) : 0;
 		$sql = "SELECT u.uid,(u.cash + u.total_slave_price + u.total_gift_price) as rank_total
 				 FROM slave_user AS u ORDER BY rank_total DESC LIMIT $start, $fetchSize";
        return $this->_rdb->fetchAll($sql);
    }

    /**
     * get slave at friend's current rank price
     * @param : string $uid
     * @return: integer
     */
    public function getSlavePriceFriendRank($uid)
    {
    	$sql = " SELECT (COUNT(a.fid)+1) AS rank_price FROM (SELECT f.fid FROM slave_friend AS f WHERE f.uid=:uid
    			 UNION SELECT u.uid FROM slave_user AS u WHERE u.uid=:uid) a,
				 slave_user b WHERE a.fid = b.uid
				 AND b.price > (SELECT price FROM slave_user WHERE uid=:uid)";

    	return $this->_rdb->fetchOne($sql, array('uid' => $uid));
    }


    /**
     * get slave at friend's current rank total
     * @param : string $uid
     * @return: integer
     */
    public function getSlaveTotalFriendRank($uid)
    {
		$sql = " SELECT (COUNT(a.fid)+1) AS total_rank from (SELECT f.fid FROM slave_friend AS f WHERE f.uid=:uid
				 UNION SELECT u.uid FROM slave_user AS u WHERE u.uid=:uid) a,
				 slave_user b WHERE a.fid = b.uid
				 AND (b.cash + b.total_slave_price + b.total_gift_price) >
				 (SELECT (cash + total_slave_price + total_gift_price) FROM slave_user WHERE uid=:uid)";

		return $this->_rdb->fetchOne($sql, array('uid' => $uid));
    }

    /**
     * get price rank friend count
     * @param : string $uid
     * @return integer
     */
    public function getPriceRankFriendCount($uid)
    {
        $sql = " SELECT (COUNT(uid)) FROM slave_friend WHERE uid=:uid";
        return $this->_rdb->fetchOne($sql, array('uid' => $uid));
    }

    /**
     * get slave masterId by Id
     * @param : string $uid
     * @return string
     */
    public function getMasterIdById($uid)
    {
    	$sql = " SELECT master_id FROM slave_user WHERE uid=:uid";
        return $this->_rdb->fetchOne($sql, array('uid' => $uid));
    }
}
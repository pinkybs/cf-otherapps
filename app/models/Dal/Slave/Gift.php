<?php

require_once 'Dal/Abstract.php';

/**
 * Dal Slave Gift
 * MixiApp Slave Gift Data Access Layer
 *
 * @package    Dal/Slave
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/06/25    zhangxin
 */
class Dal_Slave_Gift extends Dal_Abstract
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
     * get slave Gift
     *
     * @param string $uid
     * @return array
     */
    public function getGiftBySlaveId($uid)
    {
        $sql = 'SELECT * FROM slave_Gift WHERE slave_uid=:slave_uid AND status=0 ORDER BY create_time DESC ';
        return $this->_rdb->fetchRow($sql, array('slave_uid' => $uid));
    }

    /**
     * list user's Gift by uid
     *
     * @param string $uid
     * @param Integer $pageindex
     * @param Integer $pagesize
     * @return array
     */
    public function listGiftByUid($uid, $pageindex = 1, $pagesize = 10)
    {
        $start = ($pageindex - 1) * $pagesize;
        $sql = "SELECT n.gid,n.name,n.price,n.url,g.id,g.create_time FROM slave_gift g, slave_nb_gift n
                WHERE g.gid=n.gid AND g.uid=:uid ORDER BY g.create_time DESC LIMIT $start, $pagesize";
        return $this->_rdb->fetchAll($sql, array('uid' => $uid));
    }

    /**
     * get user's Gift count by uid
     *
     * @param string $uid
     * @return integer
     */
    public function getGiftByUidCount($uid)
    {
        $sql = 'SELECT COUNT(id) FROM slave_gift g,slave_nb_gift n WHERE g.gid=n.gid AND uid=:uid ';
        return $this->_rdb->fetchOne($sql, array('uid' => $uid));
    }

    /**
     * insert slave Gift
     *
     * @param array $info
     * @return integer
     */
    public function insertGift($info)
    {
        $this->_wdb->insert('slave_gift', $info);
        return $this->_wdb->lastInsertId();
    }

    /**
     * update slave Gift by key
     *
     * @param array $info
     * @param integer $id
     * @return integer
     */
    public function updateGiftByKey($info, $id)
    {
        $where = $this->_wdb->quoteInto('id = ?', $id);
        return $this->_wdb->update('slave_gift', $info, $where);
    }

    /**
     * get nb Gift
     *
     * @param string $gid
     * @return array
     */
    public function getNbGift($gid)
    {
        $sql = 'SELECT * FROM slave_nb_gift WHERE gid=:gid ';
        return $this->_rdb->fetchRow($sql, array('gid' => $gid));
    }

    /**
     * insert nb Gift
     *
     * @param array $info
     * @return integer
     */
    public function insertNbGift($info)
    {
        return $this->_wdb->insert('slave_nb_gift', $info);
    }

    /**
     * update nb Gift
     *
     * @param array $info
     * @param string $gid
     * @return integer
     */
    public function updateNbGift($info, $gid)
    {
        $where = $this->_wdb->quoteInto('gid = ?', $gid);
        return $this->_wdb->update('slave_nb_gift', $info, $where);
    }

    //--------------------------------------------------------------------


    /** get gift favorites list
     *
     * @param : string $uid
     * @param : integer $pageindex
     * @param : integer $pagesize
     * @return :array
     */
    public function listGiftFavByid($uid, $pageindex = 1, $pagesize = 10, $sort = 1)
    {
        $start = ($pageindex - 1) * $pagesize;
        $sql = " SELECT f.gid,n.name AS gift_name,n.caption,n.price AS gift_price,n.url FROM slave_gift_fav AS f
       			 INNER JOIN slave_nb_gift AS n ON n.gid = f.gid WHERE uid =:uid ORDER BY n.price";
        if (2 == $sort) {
            $sql .= " DESC";
        }
        $sql .= " LIMIT $start, $pagesize";
        return $this->_rdb->fetchAll($sql, array('uid' => $uid));
    }

    /**
     * get gift favorites count
     * @param : string $uid
     * @return:integer
     */
    public function getGiftFavCount($uid)
    {
        $sql = " SELECT COUNT(uid) FROM slave_gift_fav WHERE uid=:uid";
        return $this->_rdb->fetchOne($sql, array('uid' => $uid));
    }

    /**
     * get gid list
     * @param : string $uid
     * @return:array
     */
    public function listGidsById($uid)
    {
        $sql = "SELECT gid FROM slave_gift_fav WHERE uid=:uid";
        return $this->_rdb->fetchAll($sql, array('uid' => $uid));
    }

    /**
     * delete gift
     *
     * @param string $id
     * @return integer
     */
    public function deleteGift($id)
    {
        $sql = "DELETE FROM slave_gift WHERE id=:id ";
        return $this->_wdb->query($sql, array('id' => $id));
    }

    /**
     * insert slave Gift fav
     *
     * @param array $info
     * @return integer
     */
    public function insertGiftFav($info)
    {
        $this->_wdb->insert('slave_gift_fav', $info);
        return $this->_wdb->lastInsertId();
    }

    /**
     * remove gift fav
     * @param string $uid
     * @param string $gid
     */
    public function deleteGiftFav($uid, $gid)
    {
        $sql = "DELETE FROM slave_gift_fav WHERE uid=:uid AND gid=:gid";
        return $this->_wdb->query($sql, array('uid' => $uid, 'gid' => $gid));
    }

    /**
     * get gift fav by id
     * @param : string $gid
     * @return:array
     */
    public function getGiftFavById($gid)
    {
        $sql = " SELECT f.gid,n.name AS gift_name ,n.caption,n.price AS gift_price,n.url FROM slave_gift_fav AS f
         		 INNER JOIN slave_nb_gift AS n ON n.gid = f.gid WHERE f.gid = :gid";
        return $this->_wdb->fetchRow($sql, array('gid' => $gid));
    }

    /**
     * get gift list by id
     * @param : string $uid
     * @return:array
     */
    public function listGiftById($uid)
    {
        $sql = "SELECT g.id,g.gid,n.name AS gift_name ,n.caption,n.price AS gift_price,n.url FROM slave_gift AS g
    			INNER JOIN slave_nb_gift AS n ON n.gid = g.gid WHERE g.uid =:uid
    			 ORDER BY g.create_time DESC";
        return $this->_rdb->fetchAll($sql, array('uid' => $uid));
    }

    /**
     * get gift by id
     * @param :$id integer
     * @return:array
     */
    public function getGidByid($id)
    {
        $sql = "SELECT g.gid,n.name AS gift_name,n.caption,n.price AS gift_price,n.url FROM slave_gift AS g
    			INNER JOIN slave_nb_gift AS n ON n.gid = g.gid WHERE g.id=:id";
        return $this->_wdb->fetchRow($sql, array('id' => $id));
    }

    /**
     * get total
     * @param : string $uid
     * @return: integer
     */
    public function getTotalById($uid)
    {
        $sql = "SELECT s.cash + s.total_slave_price + s.total_gift_price AS total FROM slave_user AS s  WHERE uid=:uid";
        return $this->_wdb->fetchOne($sql, array('uid' => $uid));
    }

    /**
     * get total gift price by id
     * @param : string $uid
     * @return: integer
     */
    public function getTotalGiftPriceById($uid)
    {
        $sql = "SELECT IFNULL(SUM(n.price),0) AS gift_price FROM slave_gift AS g
				INNER JOIN slave_nb_gift AS n ON n.gid = g.gid WHERE g.uid =:uid";
        return $this->_wdb->fetchOne($sql, array('uid' => $uid));
    }

     /**
     * get fav gift count
     * @param : string $uid
     * @param : string $gid
     * @return: boolean
     */
    public function getFavCountById($uid, $gid)
    {
        $sql = "SELECT COUNT(*) FROM slave_gift_fav WHERE uid =:uid AND gid =:gid";
        return $this->_wdb->fetchOne($sql, array('uid' => $uid, 'gid' => $gid));
    }

    /**
     * Popular gift list
     *
     * @param :$sort integer
     * @return:array
     */
    public function lstPopularGift($sort)
    {
    	$sql = "SELECT * FROM ( SELECT f.gid,COUNT(f.id) as count,n.name AS gift_name,n.caption,n.price AS gift_price,n.url
    			 FROM slave_gift AS f
    			 INNER JOIN slave_nb_gift AS n ON n.gid = f.gid GROUP BY f.gid
    			 ORDER BY count DESC,f.create_time DESC LIMIT 0,10) AS c ";

    	 if (2 == $sort) {
            $sql .= "ORDER BY gift_price DESC";
         }else if (1 == $sort) {
			$sql .= "ORDER BY gift_price ASC";
    	 }
    	return $this->_wdb->fetchAll($sql);
    }
}
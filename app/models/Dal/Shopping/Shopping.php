<?php

require_once 'Dal/Abstract.php';

/**
 * Dal Shopping
 * MixiApp Shopping Data Access Layer
 *
 * @package    Dal/Shopping
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/08/10    zhangxin
 */
class Dal_Shopping_Shopping extends Dal_Abstract
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
     * get shopping by id
     *
     * @param string $uid
     * @return array
     */
    public function getShoppingById($uid)
    {
        $sql = 'SELECT * FROM shopping_user WHERE uid=:uid ';
        return $this->_rdb->fetchRow($sql, array('uid' => $uid));
    }

    /**
     * get shopping by id lock row
     *
     * @param string $uid
     * @return array
     */
    public function getShoppingByIdLock($uid)
    {
        $sql = 'SELECT * FROM shopping_user WHERE uid=:uid FOR UPDATE';
        return $this->_rdb->fetchRow($sql, array('uid' => $uid));
    }

    /**
     * insert shopping
     *
     * @param array $info
     * @return integer
     */
    public function insertShopping($info)
    {
        return $this->_wdb->insert('shopping_user', $info);
    }

    /**
     * update shopping
     *
     * @param array $info
     * @param string $uid
     * @return integer
     */
    public function updateShopping($info, $uid)
    {
        $where = $this->_wdb->quoteInto('uid = ?', $uid);
        return $this->_wdb->update('shopping_user', $info, $where);
    }

    /**
     * get shopping current price rank
     *
     * @param string $uid
     * @param string $departCol [price_depart10/ price_depart50/ price_depart100/ price_depart500]
     * @param integer $numTarget [100000/ 500000/ 1000000/ 5000000]
     * @return integer
     */
    public function getShoppingRankByDepart($uid, $departCol, $numTarget)
    {
        $sql = "SELECT (COUNT(uid)+1) AS rank FROM shopping_user
                WHERE (ABS($departCol-$numTarget)<(SELECT ABS($departCol-$numTarget) FROM shopping_user WHERE uid=:uid)
                       OR (ABS($departCol-$numTarget)=(SELECT ABS($departCol-$numTarget) FROM shopping_user WHERE uid=:uid)
                           AND ($departCol-$numTarget)<(SELECT ($departCol-$numTarget) FROM shopping_user WHERE uid=:uid))
                      )
                AND $departCol>0 ";
        return $this->_rdb->fetchOne($sql, array('uid' => $uid));
    }

    /**
     * is in shopping game by id
     *
     * @param string $uid
     * @return boolean
     */
    public function isInShoppingGame($uid)
    {
        $sql = 'SELECT challenge_id FROM shopping_user WHERE uid=:uid ';
        $result = $this->_rdb->fetchOne($sql, array('uid' => $uid));
        return !empty($result) ? true : false;
    }

    /******************************************************/

    /**
     * get shopping friend rank
     *
     * @param string $uid
     * @param string  $departCol [price_depart10/ price_depart50/ price_depart100/ price_depart500]
     * @param integer $numTarget [100000/ 500000/ 1000000/ 5000000]
     * @param integer $start
     * @param integer $fetchSize
     * @return array
     */
    public function lstShoppingFriendRank($uid, $departCol, $numTarget, $rankStart, $fetchSize)
    {
    	$start = $rankStart > 0 ? ($rankStart - 1) : 0;
        $sql = " SELECT m.fid AS uid,$numTarget AS depart,ABS(u.$departCol - $numTarget) AS diff, u.$departCol - $numTarget AS diff2
				 FROM shopping_user AS u,
				 shopping_friend AS m WHERE u.$departCol > 0 AND u.uid = m.fid AND m.uid=:uid
				    UNION
				 SELECT uid,$numTarget AS depart,ABS($departCol - $numTarget) AS diff,$departCol - $numTarget AS diff2
				 FROM shopping_user WHERE $departCol > 0 AND uid=:uid
				 ORDER BY diff,diff2 LIMIT $start, $fetchSize";
    	return $this->_rdb->fetchAll($sql, array('uid' => $uid));
    }

    /**
     * get shopping friend's count by id
     *
     * @param string $uid
     * @param string  $departCol [price_depart10/ price_depart50/ price_depart100/ price_depart500]
     * @return integer
     */
    public function getFriendCountById($uid, $departCol)
    {
        $sql = "SELECT (COUNT(uid)+1) FROM shopping_user WHERE $departCol > 0 AND uid IN
                (SELECT fid FROM shopping_friend WHERE uid=:uid)";
        return $this->_rdb->fetchOne($sql, array('uid' => $uid));
    }

     /**
     * get shopping mixi all rank
     *
     * @param string $departCol [price_depart10/ price_depart50/ price_depart100/ price_depart500]
     * @param integer $numTarget [100000/ 500000/ 1000000/ 5000000]
     * @param integer $start
     * @param integer $fetchSize
     * @return array
     */
    public function lstShoppingAllRank($departCol, $numTarget, $rankStart, $fetchSize)
    {
    	$start = $rankStart > 0 ? ($rankStart - 1) : 0;
        $sql = "SELECT uid,$numTarget AS depart,ABS($departCol - $numTarget) AS diff,$departCol - $numTarget AS diff2 FROM shopping_user
                WHERE $departCol > 0 ORDER BY diff,diff2 LIMIT $start, $fetchSize";
        return $this->_rdb->fetchAll($sql);
    }

    /**
     * get shopping user count
     * @param string  $departCol [price_depart10/ price_depart50/ price_depart100/ price_depart500]
     * @return integer
     */
    public function getShoppingAllCount($departCol)
    {
        $sql = "SELECT COUNT(uid) FROM shopping_user WHERE $departCol > 0 ";
        return $this->_rdb->fetchOne($sql);
    }

    /**
     * get shopping at friend's current rank price
     *
     * @param string $uid
     * @param string $departCol
     * @param integer $numTarget
     * @param string $fids :mixi all's friends
     * @return integer
     */
    public function getShoppingFriendRankByDepart($uid, $departCol, $numTarget, $fids)
    {
    	$fids = $this->_rdb->quote($fids);
    	$sql = " SELECT (COUNT(uid)+1) AS rank FROM shopping_user
                 WHERE (ABS($departCol - $numTarget)<(SELECT ABS($departCol - $numTarget) FROM shopping_user WHERE uid=:uid)
                       OR $departCol - $numTarget <(SELECT ($departCol - $numTarget) FROM shopping_user WHERE uid=:uid))
                AND $departCol > 0 AND uid IN ($fids)";

        return $this->_rdb->fetchOne($sql, array('uid' => $uid));
    }
}
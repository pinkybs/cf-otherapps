<?php

require_once 'Dal/Abstract.php';

/**
 * Dal Shopping Challenge
 * MixiApp Shopping Challenge Data Access Layer
 *
 * @package    Dal/Shopping
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/08/12    zhangxin
 */
class Dal_Shopping_Challenge extends Dal_Abstract
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
     * get last challenge by uid
     *
     * @param string $uid
     * @return array
     */
    public function getLastChallengeByUid($uid)
    {
        $sql = 'SELECT * FROM shopping_challenge WHERE uid=:uid AND is_ended=1 ORDER BY create_time DESC ';
        return $this->_rdb->fetchRow($sql, array('uid' => $uid));
    }

    /**
     * get challenge by Pk
     *
     * @param integer $cid
     * @param string $uid
     * @return array
     */
    public function getChallengeByPk($cid, $uid)
    {
        $sql = 'SELECT * FROM shopping_challenge WHERE cid=:cid AND uid=:uid ';
        return $this->_rdb->fetchRow($sql, array('cid' => $cid, 'uid' => $uid));
    }

    /**
     * insert challenge
     *
     * @param array $info
     * @return integer
     */
    public function insertChallenge($info)
    {
        $this->_wdb->insert('shopping_challenge', $info);
        return $this->_wdb->lastInsertId();
    }

    /**
     * update challenge
     *
     * @param array $info
     * @param integer $cid
     * @param string $uid
     * @return integer
     */
    public function updateChallenge($info, $cid, $uid)
    {
        $where = array($this->_wdb->quoteInto('cid=?', $cid),
                       $this->_wdb->quoteInto('uid=?', $uid));

        return $this->_wdb->update('shopping_challenge', $info, $where);
    }

	/**
     * list challenge cart by cid
     *
     * @param integer $cid
     * @param Integer $pageindex
     * @param Integer $pagesize
     * @return array
     */
    public function listChallengeCart($cid, $pageindex = 1, $pagesize = 10)
    {
        $start = ($pageindex - 1) * $pagesize;
        $sql = "SELECT * FROM shopping_challenge_cart WHERE cid=:cid ORDER BY create_time DESC LIMIT $start, $pagesize";
        return $this->_rdb->fetchAll($sql, array('cid' => $cid));
    }

	/**
     * get challenge cart count by cid
     *
     * @param integer $cid
     * @return integer
     */
    public function getChallengeCartCount($cid)
    {
        $sql = 'SELECT COUNT(*) FROM shopping_challenge_cart WHERE cid=:cid ';
        return $this->_rdb->fetchOne($sql, array('cid' => $cid));
    }

	/**
     * get challenge cart price by cid
     *
     * @param integer $cid
     * @return integer
     */
    public function getChallengeCartPrice($cid)
    {
        $sql = 'SELECT IFNULL(SUM(guess_price),0) FROM shopping_challenge_cart WHERE cid=:cid ';
        return $this->_rdb->fetchOne($sql, array('cid' => $cid));
    }

	/**
     * get challenge cart price real by cid
     *
     * @param integer $cid
     * @return integer
     */
    public function getChallengeCartRealPrice($cid)
    {
        $sql = 'SELECT IFNULL(SUM(real_price),0) FROM shopping_challenge_cart WHERE cid=:cid ';
        return $this->_rdb->fetchOne($sql, array('cid' => $cid));
    }

	/**
     * list popular item
     *
     * @param Integer $pageindex
     * @param Integer $pagesize
     * @return array
     */
    public function listPopularItem($pageindex = 1, $pagesize = 10)
    {
        $start = ($pageindex - 1) * $pagesize;
        $sql = "SELECT iid,COUNT(*) AS pop_count FROM shopping_challenge_cart
                GROUP BY iid ORDER BY pop_count DESC,create_time DESC LIMIT $start, $pagesize";
        return $this->_rdb->fetchAll($sql);
    }

	/**
     * get challenge cart count by cid
     *
     * @return integer
     */
    public function getPopularItemCount()
    {
        $sql = 'SELECT COUNT(DISTINCT(iid)) FROM shopping_challenge_cart ';
        return $this->_rdb->fetchOne($sql);
    }


	/**
     * check item is already in cart by cid and iid
     *
     * @param integer $cid
     * @param integer iid
     * @return integer
     */
    public function isItemInCart($cid,$iid)
    {
        $sql = 'SELECT * FROM shopping_challenge_cart WHERE cid=:cid AND iid=:iid';
        $result = $this->_rdb->fetchRow($sql, array('cid' => $cid, 'iid' => $iid));
        return !empty($result) ? true : false;
    }


	/**
     * update opening challenge to close by uid
     *
     * @param string $uid
     * @return integer
     */
    public function updateOpenChallengeToClose($uid)
    {
        $sql = "UPDATE shopping_challenge SET is_ended=1 WHERE is_ended=0 AND uid=:uid ";
        return $this->_wdb->query($sql, array('uid' => $uid));
    }

    /******************************************************/

    /**
     * delete Challenge cart
     *
     * @param integer $id
     * @return void
     */
    public function deleteChallengeCart($cid, $iid)
    {
        $sql = "DELETE FROM shopping_challenge_cart WHERE cid=:cid AND iid=:iid";
        return $this->_wdb->query($sql, array('cid' => $cid, 'iid' => $iid));
    }

    /**
     * insert challenge cart
     *
     * @param array $info
     * @return integer
     */
    public function insertChallengeCart($info)
    {
        return $this->_wdb->insert('shopping_challenge_cart', $info);
    }

    /**
     * get nb Item by code
     *
     * @param string $code
     * @return array
     */
    public function getCartInfoById($cid, $iid)
    {
        $sql = 'SELECT c.guess_price,n.* FROM shopping_challenge_cart AS c,shopping_nb_item AS n
                 WHERE c.cid=:cid AND c.iid=:iid AND c.iid = n.iid ';
        return $this->_rdb->fetchRow($sql, array('cid' => $cid, 'iid' => $iid));
    }

    /**
     * update challenge cart
     *
     * @param array $info
     * @param integer $cid
     * @param integer $iid
     * @return integer
     */
    public function updateChallengeCart($info, $cid, $iid)
    {
        $where = array($this->_wdb->quoteInto('cid=?', $cid),
                       $this->_wdb->quoteInto('iid=?', $iid));

        return $this->_wdb->update('shopping_challenge_cart', $info, $where);
    }
}
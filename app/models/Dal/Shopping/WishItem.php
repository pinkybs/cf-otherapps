<?php

require_once 'Dal/Abstract.php';

/**
 * Dal Shopping WishItem
 * MixiApp Shopping WishItem Data Access Layer
 *
 * @package    Dal/Shopping
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/08/10    zhangxin
 */
class Dal_Shopping_WishItem extends Dal_Abstract
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
     * list user's WishItem by uid
     *
     * @param string $uid
     * @param Integer $pageindex
     * @param Integer $pagesize
     * @return array
     */
    public function listWishItemByUid($uid, $pageindex = 1, $pagesize = 10)
    {
        $start = ($pageindex - 1) * $pagesize;
        $sql = "SELECT i.* FROM shopping_nb_item i, shopping_wish_item w
                WHERE i.iid=w.iid AND w.uid=:uid
                ORDER BY w.create_time DESC LIMIT $start, $pagesize";
        return $this->_rdb->fetchAll($sql, array('uid' => $uid));
    }

    /**
     * get user's WishItem count by uid
     *
     * @param string $uid
     * @return integer
     */
    public function getWishItemByUidCount($uid)
    {
        $sql = 'SELECT COUNT(w.iid) FROM shopping_nb_item i, shopping_wish_item w WHERE i.iid=w.iid AND w.uid=:uid';
        return $this->_rdb->fetchOne($sql, array('uid' => $uid));
    }

    /**
     * insert shopping WishItem
     *
     * @param array $info
     * @return integer
     */
    public function insertWishItem($info)
    {
        $this->_wdb->insert('shopping_wish_item', $info);
        return $this->_wdb->lastInsertId();
    }

	/**
     * delete WishItem
     *
     * @param integer $uid
     * @param integer $iid
     * @return integer
     */
    public function deleteWishItem($uid, $iid)
    {
        $sql = "DELETE FROM shopping_wish_item WHERE uid=:uid AND iid=:iid ";
        return $this->_wdb->query($sql, array('uid' => $uid, 'iid' => $iid));
    }

    /**
     * get nb Item
     *
     * @param integer $iid
     * @return array
     */
    public function getNbItem($iid)
    {
        $sql = 'SELECT * FROM shopping_nb_item WHERE iid=:iid ';
        return $this->_rdb->fetchRow($sql, array('iid' => $iid));
    }

	/**
     * get nb Item by code
     *
     * @param string $code
     * @return array
     */
    public function getNbItemByCode($code)
    {
        $sql = 'SELECT * FROM shopping_nb_item WHERE item_code=:item_code ';
        return $this->_rdb->fetchRow($sql, array('item_code' => $code));
    }

    /**
     * insert nb Item
     *
     * @param array $info
     * @return integer
     */
    public function insertNbItem($info)
    {
        $this->_wdb->insert('shopping_nb_item', $info);
        return $this->_wdb->lastInsertId();
    }

    /**
     * update nb Item
     *
     * @param array $info
     * @param integer $iid
     * @return integer
     */
    public function updateNbItem($info, $iid)
    {
        $where = $this->_wdb->quoteInto('iid = ?', $iid);
        return $this->_wdb->update('shopping_nb_item', $info, $where);
    }

    //--------------------------------------------------------------------

     /**
     * judge wishitem whether exist
     * @param : string $uid
     * @param : integer $iid
     * @return: boolean
     */
    public function isWishItemExist($uid, $iid)
    {
        $sql = "SELECT * FROM shopping_wish_item WHERE uid =:uid AND iid =:iid";
        $result = $this->_wdb->fetchOne($sql, array('uid' => $uid, 'iid' => $iid));
        return !empty($result) ? true : false;
    }

    /**
     * get iid list
     * @param : string $uid
     * @return: array
     */
    public function listIidsById($uid)
    {
        $sql = "SELECT iid FROM shopping_wish_item WHERE uid=:uid";
        return $this->_rdb->fetchAll($sql, array('uid' => $uid));
    }
}
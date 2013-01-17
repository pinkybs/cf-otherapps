<?php

require_once 'Dal/Abstract.php';

/**
 * Dal Slave Work
 * MixiApp Slave Work Data Access Layer
 *
 * @package    Dal/Slave
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/06/25    zhangxin
 */
class Dal_Slave_Work extends Dal_Abstract
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
     * list basic work
     *
     * @param string $uid
     * @return array
     */
    public function listBasicWork()
    {
        $sql = 'SELECT category_id,wlevel,category FROM slave_nb_work WHERE wlevel=1 AND isspecial=0 ORDER BY category_id ';
        return $this->_rdb->fetchAll($sql);
    }

	/**
     * get nb work by key
     *
     * @param integer $categoryId
     * @param integer $wLevel
     * @return array
     */
    public function getNbWorkByKey($categoryId, $wLevel)
    {
        $sql = 'SELECT * FROM slave_nb_work WHERE category_id=:category_id AND wlevel=:wlevel ';
        return $this->_rdb->fetchRow($sql, array('category_id' => $categoryId, 'wlevel' => $wLevel));
    }

	/**
     * list nb work level up qualify
     *
     * @param integer $categoryId
     * @return array
     */
    public function listNbWorkLevelUpQualify($categoryId)
    {
        $sql = 'SELECT levelup_qualify FROM slave_nb_work WHERE category_id=:category_id ';
        return $this->_rdb->fetchAll($sql, array('category_id' => $categoryId));
    }


    /**
     * list user work
     *
     * @param string $uid
     * @return array
     */
    public function listWorkByUid($uid)
    {
        $sql = 'SELECT n.category,w.* FROM slave_work w, slave_nb_work n
                WHERE w.category_id=n.category_id AND w.wlevel=n.wlevel AND w.uid=:uid
                ORDER BY w.last_working_time DESC';
        return $this->_rdb->fetchAll($sql, array('uid' => $uid));
    }

	/**
     * get slave work by key
     *
     * @param string $uid
     * @param integer $categoryId
     * @return array
     */
    public function getSlaveWorkByKey($uid, $categoryId)
    {
        $sql = 'SELECT * FROM slave_work WHERE uid=:uid AND category_id=:category_id';
        return $this->_rdb->fetchRow($sql, array('uid' => $uid, 'category_id' => $categoryId));
    }

	/**
     * get slave work by key for update
     *
     * @param string $uid
     * @param integer $categoryId
     * @return array
     */
    public function getSlaveWorkByKeyLock($uid, $categoryId)
    {
        $sql = 'SELECT * FROM slave_work WHERE uid=:uid AND category_id=:category_id FOR UPDATE ';
        return $this->_rdb->fetchRow($sql, array('uid' => $uid, 'category_id' => $categoryId));
    }

	/**
     * get current max work level by uid
     *
     * @param string $uid
     * @return integer
     */
    public function getMaxWorkLevelByUid($uid)
    {
        $sql = 'SELECT MAX(wlevel) FROM slave_work WHERE uid=:uid ';
        return $this->_rdb->fetchOne($sql, array('uid' => $uid));
    }

	/**
     * get work level=5 count by uid
     *
     * @param string $uid
     * @return integer
     */
    public function getWorkLevelFiveCountByUid($uid)
    {
        $sql = 'SELECT COUNT(category_id) FROM slave_work WHERE uid=:uid AND wlevel=5';
        return $this->_rdb->fetchOne($sql, array('uid' => $uid));
    }

    /**
     * insert slave work
     *
     * @param array $info
     * @return integer
     */
    public function insertSlaveWork($info)
    {
        return $this->_wdb->insert('slave_work', $info);
    }

    /**
     * update slave work
     *
     * @param array $info
     * @param string $uid
     * @param integer $categoryId
     * @return integer
     */
    public function updateSlaveWork($info, $uid, $categoryId)
    {
        $where = array($this->_wdb->quoteInto('uid=?', $uid),
                       $this->_wdb->quoteInto('category_id=?', $categoryId));
        return $this->_wdb->update('slave_work', $info, $where);
    }

	/**
     * insert slave work detail
     *
     * @param array $info
     * @return integer
     */
    public function insertWorkDetail($info)
    {
        $this->_wdb->insert('slave_work_detail', $info);
        return $this->_wdb->lastInsertId();
    }

}
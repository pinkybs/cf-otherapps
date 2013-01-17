<?php

require_once 'Dal/Abstract.php';

/**
 * Dal Slave Revolution
 * MixiApp Slave Revolution Data Access Layer
 *
 * @package    Dal/Slave
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/06/25    zhangxin
 */
class Dal_Slave_Revolution extends Dal_Abstract
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
     * get slave Revolution
     *
     * @param string $slaveId
     * @param string $masterId
     * @return array
     */
    public function getInRevolutionById($slaveId, $masterId)
    {
        $sql = 'SELECT * FROM slave_revolution
                WHERE slave_uid=:slave_uid AND master_uid=:master_uid AND status=0 ORDER BY create_time DESC ';
        return $this->_rdb->fetchRow($sql, array('slave_uid' => $slaveId, 'master_uid' => $masterId));
    }

	/**
     * get slave Revolution for update
     *
     * @param string $slaveId
     * @param string $masterId
     * @return array
     */
    public function getInRevolutionByIdLock($slaveId, $masterId)
    {
        $sql = 'SELECT * FROM slave_revolution
                WHERE slave_uid=:slave_uid AND master_uid=:master_uid AND status=0
                ORDER BY create_time DESC FOR UPDATE ';
        return $this->_rdb->fetchRow($sql, array('slave_uid' => $slaveId, 'master_uid' => $masterId));
    }

	/**
     * get slave Revolution by master id
     *
     * @param string $uid
     * @return array
     */
    public function getInRevolutionByMasterId($uid)
    {
        $sql = 'SELECT * FROM slave_revolution WHERE master_uid=:master_uid AND status=0 ORDER BY create_time ';
        return $this->_rdb->fetchRow($sql, array('master_uid' => $uid));
    }

	/**
     * get slave Revolution by slave id
     *
     * @param string $uid
     * @return array
     */
    public function getInRevolutionBySlaveId($uid)
    {
        $sql = 'SELECT * FROM slave_revolution WHERE slave_uid=:slave_uid AND status=0 ORDER BY create_time DESC ';
        return $this->_rdb->fetchRow($sql, array('slave_uid' => $uid));
    }

    /**
     * insert slave Revolution
     *
     * @param array $info
     * @return integer
     */
    public function insertRevolution($info)
    {
        $this->_wdb->insert('slave_revolution', $info);
        return $this->_wdb->lastInsertId();
    }

    /**
     * update slave Revolution by key
     *
     * @param array $info
     * @param integer $id
     * @return integer
     */
    public function updateRevolutionByKey($info, $id)
    {
        $where = $this->_wdb->quoteInto('id = ?', $id);
        return $this->_wdb->update('slave_revolution', $info, $where);
    }

    /**
     * update slave Revolution
     *
     * @param array $info
     * @param string $slaveUid
     * @param string $masterUid
     * @return integer
     */
    public function updateRevolution($info, $slaveUid, $masterUid)
    {
        $where = array($this->_wdb->quoteInto('slave_uid=?', $slaveUid),
                       $this->_wdb->quoteInto('master_uid=?', $masterUid),
                       $this->_wdb->quoteInto('status=?', 0));
        return $this->_wdb->update('slave_revolution', $info, $where);
    }

	/**
     * update slave Revolution after first by master id
     *
     * @param array $info
     * @param string $masterUid
     * @param integer time
     * @return integer
     */
    public function updateRevolutionAfterFirst($info, $masterUid, $time)
    {
        $where = array($this->_wdb->quoteInto('master_uid=?', $masterUid),
                       $this->_wdb->quoteInto('create_time>?', $time),
                       $this->_wdb->quoteInto('status=?', 0));
        return $this->_wdb->update('slave_revolution', $info, $where);
    }

	/**
     * delete revolution by slave and master
     *
     * @param string $slaveId
     * @param string $masterId
     * @return integer
     */
    public function deleteRevolutionBegun($slaveId, $masterId)
    {
        $sql = "DELETE FROM slave_revolution WHERE slave_uid=:slave_uid AND master_uid=:master_uid AND status=0";
        return $this->_wdb->query($sql, array('slave_uid' => $slaveId, 'master_uid' => $masterId));
    }
}
<?php

/**
 * Parking
 *
 * @package     Dal
 * @copyright   Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/02/24    shenhw
 */

class Admin_Dal_Parking extends Admin_Dal_Abstract
{
    protected static $_instance;

    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        
        return self::$_instance;
    }

    /**
     * check cav name 4 car
     *
     * @param string $cavName
     * @param integer $cid
     * @return integer
     */
    public function checkCavName4Car($cavName, $cid)
    {
        $where = array();
        
        $sql = "SELECT count(1) FROM parking_car WHERE cav_name = :cavname ";
        if (0 != $cid) {
            $sql .= "AND cid != :cid";
            $where = array('cavname' => $cavName, 'cid' => $cid);
        }
        else {
            $where = array('cavname' => $cavName);
        }
        
        return $this->_rdb->fetchOne($sql, $where);
    }

    /**
     * get car's color list
     *
     * @return array
     */
    public function getCarColors()
    {
        $sql = "SELECT * FROM parking_car_color";
        
        return $this->_rdb->fetchAll($sql);
    }

    /**
     * get car list
     *
     * @param Integer $pageindex
     * @param Integer $pagesize
     * @return array
     */
    public function getCarList($pageindex = 1, $pagesize = 10)
    {
        $start = ($pageindex - 1) * $pagesize;
        $sql = "SELECT cid, name, price
                FROM parking_car
                ORDER BY create_time DESC LIMIT $start, $pagesize ";
        
        return $this->_rdb->fetchAll($sql);
    }

    /**
     * get car count
     *
     * @return Integer
     */
    public function getCarCount()
    {
        $sql = "SELECT COUNT(1) FROM parking_car";
        return $this->_rdb->fetchOne($sql);
    }

    /**
     * insert parking user
     *
     * @param array $info
     * @return integer
     */
    public function insertCar($info)
    {
        $this->_wdb->insert('parking_car', $info);
        return $this->_wdb->lastInsertId();
    }

    /**
     * edit car info
     *
     * @param integer $cid
     * @param array $info
     * @return integer
     */
    public function updateCar($cid, $info)
    {
        $where = $this->_wdb->quoteinto('cid = ?', $cid);
        $this->_wdb->update('parking_car', $info, $where);
    }

    /**
     * get car info
     *
     * @param integer $cid
     * @return array
     */
    public function getCarInfo($cid)
    {
        $sql = "SELECT name, price, cav_name, times
                FROM parking_car
                WHERE cid = :cid ";
        
        return $this->_rdb->fetchRow($sql, array('cid' => $cid));
    }

    /**
     * background list
     *
     * @param Integer $pageindex
     * @param Integer $pagesize
     * @return array
     */
    public function getBackgroundList($pageindex = 1, $pagesize = 10)
    {
        $start = ($pageindex - 1) * $pagesize;
        $sql = "SELECT id, name, type
                FROM parking_background
                ORDER BY create_time DESC LIMIT $start, $pagesize ";
        
        return $this->_rdb->fetchAll($sql);
    }

    /**
     * get background count
     *
     * @return integer
     */
    public function getBackgroundCount()
    {
        $sql = "SELECT COUNT(1) FROM parking_background";
        return $this->_rdb->fetchOne($sql);
    }

    /**
     * check cav name 4 background
     *
     * @param string $cavName
     * @param integer $id
     * @return integer
     */
    public function checkCavName4Background($cavName, $id)
    {
        $where = array();
        
        $sql = "SELECT count(1) FROM parking_background WHERE cav_name = :cavname ";
        if (0 != $id) {
            $sql .= "AND id != :id";
            $where = array('cavname' => $cavName, 'id' => $id);
        }
        else {
            $where = array('cavname' => $cavName);
        }
        
        return $this->_rdb->fetchOne($sql, $where);
    }

    /**
     * insert background
     *
     * @param array $info
     * @return integer
     */
    public function insertBackground($info)
    {
        $this->_wdb->insert('parking_background', $info);
        return $this->_wdb->lastInsertId();
    }

    /**
     * edit background info
     *
     * @param integer $id
     * @param array $info
     * @return integer
     */
    public function updateBackground($id, $info)
    {
        $where = $this->_wdb->quoteinto('id = ?', $id);
        $this->_wdb->update('parking_background', $info, $where);
    }

    /**
     * get background info
     *
     * @param integer $id
     * @return array
     */
    public function getBackgroundInfo($id)
    {
        $sql = "SELECT name, introduce, price, cav_name, type, fee
                FROM parking_background
                WHERE id = :id ";
        
        return $this->_rdb->fetchRow($sql, array('id' => $id));
    }

    /**
     * background list
     *
     * @return array
     */
    public function getRankList()
    {
        $sql = "SELECT * FROM parking_rank";
        
        return $this->_rdb->fetchAll($sql);
    }

    /**
     * background list
     *
     * @param string $rank
     * @return array
     */
    public function getRankInfo($rank)
    {
        $sql = "SELECT *
                FROM parking_rank
                WHERE rank = :rank";
        
        return $this->_rdb->fetchRow($sql, array('rank' => $rank));
    }
    
}
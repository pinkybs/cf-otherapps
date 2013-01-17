<?php

require_once 'Mdal/Abstract.php';

/**
 * Mdal School
 * MixiApp School VisitFoot Data Access Layer
 *
 * @package    Mdal/School
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/11/09    zhangxin
 */
class Mdal_School_VisitFoot extends Mdal_Abstract
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
     * list visit foot by uid (no repeat user)
     *
     * @param integer $visit_uid
     * @param integer $pageindex
     * @param integer $pagesize
     * @return array
     */
    public function listVisitFoot($visit_uid, $pageindex = 1, $pagesize = 10)
    {
        $start = ($pageindex - 1) * $pagesize;
        $sql = "SELECT uid,visit_count FROM school_visit_foot WHERE visit_uid=:visit_uid
                ORDER BY update_time DESC LIMIT $start, $pagesize";

        return $this->_rdb->fetchAll($sql, array('visit_uid' => $visit_uid));
    }

    /**
     * get visit foot count (no repeat user)
     *
     * @param integer $visit_uid
     * @return integer
     */
    public function getVisitFootCount($visit_uid)
    {
        $sql = 'SELECT COUNT(uid) FROM school_visit_foot WHERE visit_uid=:visit_uid ';
        return $this->_rdb->fetchOne($sql, array('visit_uid' => $visit_uid));
    }

    /**
     * get visit foot count all (has repeat user)
     *
     * @param integer $visit_uid
     * @return integer
     */
    public function getVisitFootCountAll($visit_uid)
    {
        $sql = 'SELECT IFNULL(SUM(visit_count),0) AS count_all FROM school_visit_foot WHERE visit_uid=:visit_uid ';
        return $this->_rdb->fetchOne($sql, array('visit_uid' => $visit_uid));
    }

    /**
     * get visit foot by key
     *
     * @param integer $uid
     * @param integer $visit_uid
     * @return integer
     */
    public function getVisitFoot($uid, $visit_uid)
    {
        $sql = "SELECT * FROM school_visit_foot WHERE uid=:uid AND visit_uid=:visit_uid ";
        return $this->_rdb->fetchRow($sql, array('uid' => $uid, 'visit_uid' => $visit_uid));
    }

    /**
     * insert visit foot
     *
     * @param array $info
     * @return integer
     */
    public function insertVisitFoot($info)
    {
        return $this->_wdb->insert('school_visit_foot', $info);
    }

    /**
     * update visit foot
     *
     * @param integer $uid
     * @param integer $visit_uid
     * @param integer $updateTime
     * @return integer
     */
    public function updateVisitFoot($uid, $visit_uid, $updateTime)
    {
        $sql = "UPDATE school_visit_foot SET visit_count=visit_count+1,update_time=:update_time
                WHERE uid=:uid AND visit_uid=:visit_uid ";
        return $this->_wdb->query($sql, array('update_time' =>$updateTime, 'uid' => $uid, 'visit_uid' => $visit_uid));
    }



    /***********************************************************************************/




}
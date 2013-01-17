<?php

require_once 'Dal/Abstract.php';
/**
 * Dal Millionminds Log
 * MixiApp Millionminds Log Data Access Layer
 *
 * @package    Dal/Millionminds
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/07/27    Liz
 */
class Dal_Millionminds_Log extends Dal_Abstract
{
    /**
     * user visit log table name
     *
     * @var string
     */
    protected $table_log = 'millionmind_user_visit';
        
    protected static $_instance;
    
    /**
     * get Dal_Millionminds_Log default
     *
     * @return Dal_Millionminds_Log
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        
        return self::$_instance;
    }

    /**
     * insert millionminds user visit log
     *
     * @param array $info
     * @return void
     */
    public function insertVisitLog($info)
    {
        $this->_wdb->insert($this->table_log, $info);
    }

    /**
     * get user visit log from visit_uid
     *
     * @param integer $uid
     * @param integer $visit_uid
     * @return array
     */
    public function getVisitLog($uid, $visit_uid)
    {
        $sql = "SELECT * FROM $this->table_log WHERE uid=:uid AND visiter_uid=:visit_uid AND (TO_DAYS(now())-TO_DAYS(visit_time)=0)";

        return $this->_rdb->fetchRow($sql, array('uid'=>$uid, 'visit_uid'=>$visit_uid));
    }
    
    /**
     * delete user visit log
     *
     * @param integer $uid
     * @param integer $visit_uid
     * @return void
     */
    public function deleteVisitLog($uid, $visit_uid)
    {
        $sql = "DELETE FROM $this->table_log WHERE uid=:uid AND visiter_uid=:visit_uid ";

        $this->_wdb->query($sql, array('uid'=>$uid, 'visit_uid'=>$visit_uid));
    }
    
    /**
     * get user visit log info
     *
     * @param integer $uid
     * @param integer $pageIndex
     * @param integer $pageSize
     * @return array
     */
    public function getUserVisitInfo($uid, $pageIndex=1, $pageSize=10)
    {
        $start = ($pageIndex-1) * $pageSize;
        
        $sql = "SELECT * FROM $this->table_log WHERE uid=:uid ORDER BY visit_time DESC ";
        
        $sql .= "LIMIT $start, $pageSize";

        return $this->_rdb->fetchAll($sql, array('uid'=>$uid));
    }

    /**
     * get user visit count
     *
     * @param integer $uid
     * @return integer
     */
    public function getUserVisitCount($uid)
    {
        $sql = "SELECT count(1) FROM $this->table_log WHERE uid=:uid ";
        
        return $this->_rdb->fetchOne($sql, array('uid'=>$uid));
    }
}
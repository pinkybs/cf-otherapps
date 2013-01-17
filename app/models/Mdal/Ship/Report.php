<?php

require_once 'Mdal/Abstract.php';

/**
 * Mdal Ship Report
 * MixiApp Ship Report Data Access Layer
 *
 * @package    Mdal/Ship
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/11/30    Liz
 */
class Mdal_Ship_Report extends Mdal_Abstract
{
    /**
     * table name
     *
     * @var string
     */
    protected $table_report = 'ship_report';
        
    protected static $_instance;

    /**
     * getDefaultInstance
     *
     * @return Mdal_Ship_Report
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * insert report
     *
     * @param array $report
     * @return integer
     */
    public function insertReport($report)
    {
        $this->_wdb->insert($this->table_report, $report);
        return $this->_wdb->lastInsertId();
    }
    
    /**
     * check user had reported the parking info
     *
     * @param integer $sid
     * @return boolean
     */
    public function isReport($sid, $uid)
    {
        $sql = "SELECT COUNT(1) FROM $this->table_report WHERE sid=:sid AND uid=:uid";

        $result = $this->_rdb->fetchOne($sql,array('sid' => $sid, 'uid' => $uid));
        return $result>0;
    }
    
    /**
     * get report info
     *
     * @param string $report
     * @return array
     */
    public function getReportBySid($report)
    {
        $sql = "SELECT min(id) FROM ship_report WHERE sid in ($report) GROUP BY sid";
        $result = $this->_rdb->fetchAll($sql);
        
        if (empty($result)) return array();
        $ids = $this->_rdb->quote($result);
        
        $sql = "SELECT r.anonymous,r.uid AS report_uid,r.create_time,s.sid,s.uid,s.parked_time,u.background,b.fee,ss.times,IFNULL(us.ship_name, ss.name) AS shipName,u.last_bribery_time 
                FROM ship_report AS r,ship AS s,ship_user AS u,ship_background AS b,ship_ship AS ss,ship_user_ship AS us 
                WHERE us.id=s.user_ship_id AND ss.sid=s.ship_id AND b.id=u.background AND u.uid=s.uid AND r.sid=s.sid AND r.id IN ($ids)";
        return $this->_rdb->fetchAll($sql);
    }
    
    public function deleteReportBySid($sid)
    {
        $sql = "SELECT id FROM ship_report WHERE sid=:sid";
        $result = $this->_rdb->fetchAll($sql, array('sid'=>$sid));
        $ids = $this->_rdb->quote($result);
        
        $sql = "DELETE FROM ship_report WHERE id IN ($ids)";
        $this->_wdb->query($sql);
    }
    
    public function getReportByUid($uid)
    {
        $sql = "SELECT r.anonymous,r.uid AS report_uid,r.create_time,s.sid,s.uid,s.parked_time,u.background,b.fee,ss.times,IFNULL(us.ship_name, ss.name) AS shipName,u.last_bribery_time  
                FROM ship_report AS r,ship AS s,ship_user AS u,ship_background AS b,ship_ship AS ss,ship_user_ship AS us 
                WHERE us.id=s.user_ship_id AND ss.sid=s.ship_id AND b.id=u.background AND u.uid=s.uid AND r.sid=s.sid AND r.uid=:uid";
        return $this->_rdb->fetchAll($sql, array('uid'=>$uid));
    }
}
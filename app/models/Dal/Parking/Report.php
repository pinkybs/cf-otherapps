<?php

require_once 'Dal/Abstract.php';

class Dal_Parking_Report extends Dal_Abstract
{
    /**
     * user table name
     *
     * @var string
     */
    protected $table_report = 'parking_report';
    
    protected static $_instance;
    
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
     * check user is report the parking info
     *
     * @param integer $pid
     * @return boolean
     */
    public function isReport($pid, $uid)
    {
        $sql = "SELECT COUNT(1) FROM $this->table_report WHERE pid=:pid AND uid=:uid";

        $result = $this->_rdb->fetchOne($sql,array('pid' => $pid, 'uid' => $uid));
        return $result>0;
    }

    /**
     * delete parking  car
     *
     */
    public function deleteParkingCar($pid)
    {
        $where = $this->_wdb->quoteinto('pid = ?', $pid);
        //delete reported parking
        $this->_wdb->delete($this->table_report, $where);
        //delete parking
        $this->_wdb->delete('parking', $where);
    }
}
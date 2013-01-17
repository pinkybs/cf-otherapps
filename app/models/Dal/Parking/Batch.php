<?php

/**
 * Batch
 *
 * @package    Dal
 * @copyright    Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create    2009/03/02    HCH
 */
class Dal_Parking_Batch extends Dal_Abstract
{
	/**
     * get park info
     *
     * @param integer $uid
     * @param integer $car_id
     * @param integer $car_color
     * @return array
     */
    public function getParkInfoByPid($pid)
    {
        $sql = "SELECT * FROM parking WHERE pid=:pid";
        return $this->_rdb->fetchRow($sql,array('pid'=>$pid));
    }
    
    /**
     * get parking user
     *
     */
    public function getParkingUser($uid , $type = 1)
    {
        if($type == 1) {
            $sql = "SELECT p.uid,b.name,b.fee,1 AS type, p.free_park,last_bribery_time FROM parking_user AS p,parking_background AS b
                	WHERE b.id=p.background AND p.uid=:uid  ";
        } 
        else {
            $sql = "SELECT n.id AS uid,n.name AS displayName,n.free_park,n.background,b.name,b.fee,2 AS type
                	FROM parking_neighbor AS n,parking_background AS b WHERE n.background=b.id AND n.id=:uid";
        }
        return $this->_rdb->fetchRow($sql , array('uid' =>$uid));
    }
    
    /**
     * get car name
     *
     * @param integer $car_id
     * @return string
     */
    public function getCarName($car_id)
    {
        $sql = "SELECT name FROM parking_car WHERE cid=:cid";
        return $this->_rdb->fetchOne($sql, array('cid'=>$car_id));
    }
    
    /**
     * delete parking  car
     *
     */
    public function deleteParkingCar($pid)
    {
        $where = $this->_wdb->quoteinto('pid = ?', $pid);
        //delete reported parking
        $this->_wdb->delete('parking_report', $where);
        //delete parking
        $this->_wdb->delete('parking', $where);
    }
    
    /**
     * insert no park
     *
     * @param array $info
     * @return unknown
     */
    public function insertNoPark($info)
    {
        $this->_wdb->insert('parking_nopark', $info);
        return $this->_wdb->lastInsertId();
    }
    
    /**
     * get parking  car
     *
     */
    public function getParkingCar($runTime)
    {
        $sql = "SELECT p.*, IF(r.id,1,0) As is_reported, IF(r.anonymous,1,0) As anonymous, r.uid As reported_uid
            FROM parking as p LEFT JOIN parking_report AS r ON r.pid = p.pid  WHERE  p.parked_time <($runTime - 3600) ORDER BY p.pid";

        return $this->_rdb->fetchAll($sql);
    }
    
    /**
     * get report parking car
     *
     * @return array
     */
    public function getReportedParkingCar($runTime)
    {
        $sql = "SELECT p.*, 1 As is_reported, IF(r.anonymous,1,0) As anonymous, r.uid As reported_uid
            FROM parking as p , parking_report AS r WHERE r.pid = p.pid AND p.parked_time <($runTime - 3600)  AND r.create_time <($runTime - 3600) ORDER BY p.pid";

        return $this->_rdb->fetchAll($sql);
    }
    
    /**
     * delete parking info where pid is remove
     *
     * @return unknown
     */
    public function deleteNotInParking()
    {
        $sql = "DELETE FROM  parking_report WHERE pid not in (select pid FROM parking)";
        return $this->_wdb->query($sql);
    }
    
    /**
     * update parking user's info
     *
     * @param Integer $uid
     * @param array $parking
     * @return void
     */
    public function updateParkingUserAsset($uid, $addMoney)
    {
        $sql = "UPDATE parking_user SET asset=asset + $addMoney where uid=:uid";
         $this->_wdb->query($sql, array('uid' => $uid));
    }
    
    /**
     * insert minifeed
     *
     * @param array $info
     * @return integer
     */
    public function insertMinifeed($info)
    {
        $this->_wdb->insert('parking_minifeed', $info);
        return $this->_wdb->lastInsertId();
    }
    
    /**
     * insert newsfeed
     *
     * @param array $info
     * @return integer
     */
    public function insertNewsfeed($info)
    {
        $this->_wdb->insert('parking_newsfeed', $info);
        return $this->_wdb->lastInsertId();
    }
}
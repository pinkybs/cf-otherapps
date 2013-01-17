<?php

require_once 'Dal/Abstract.php';

class Dal_Parking_Parking extends Dal_Abstract
{
    /**
     * user table name
     *
     * @var string
     */
    protected $table_parking = 'parking';
    
    protected static $_instance;
    
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        
        return self::$_instance;
    }

    /**
     * insert parking info
     *
     * @param array $info
     * @return integer
     */
    public function insertParkingInfo($info)
    {
        $this->_wdb->insert('parking', $info);
        return $this->_wdb->lastInsertId();
    }

    /**
     * delete parking info
     *
     * @param integer $pid
     */
    public function deleteParkingInfo($pid)
    {
        $sql = "DELETE FROM parking WHERE pid=:pid";
        $this->_wdb->query($sql,array('pid'=>$pid));
    }

    /**
     * get friend park
     *
     * @param integer $uid
     * @return array
     */
    public function getFriendPark($uid)
    {
        $sql = "SELECT p.*,c.cav_name,c.times,c.type AS car_type,c.ad_url,UNIX_TIMESTAMP(now())-p.parked_time AS park_time
				FROM parking AS p,parking_car AS c WHERE p.car_id=c.cid AND p.type=1 AND p.parking_uid=:uid";

        return $this->_rdb->fetchAll($sql,array('uid'=>$uid));
    }
    
    /**
     * get neighbor park
     *
     * @param integer $uid
     * @param integer $neighbor
     * @return array
     */
    public function getNeighborPark($uid,$neighbor)
    {
        $sql = "SELECT p.*,c.cav_name,c.times,c.type AS car_type,c.ad_url,UNIX_TIMESTAMP(now())-p.parked_time AS park_time
                FROM parking AS p,parking_car AS c WHERE p.car_id=c.cid AND p.type=2 AND p.parking_uid=:neighbor AND p.uid=:uid";

        return $this->_rdb->fetchAll($sql,array('neighbor'=>$neighbor,'uid'=>$uid));
    }

    /**
     * get park info
     *
     * @param integer $uid
     * @param integer $car_id
     * @param integer $car_color
     * @return array
     */
    public function getParkInfo($uid,$car_id,$car_color)
    {
        $sql = "SELECT type FROM parking WHERE uid=:uid AND car_id=:car_id AND car_color=:car_color";
        $type = $this->_rdb->fetchOne($sql,array('uid'=>$uid,'car_id'=>$car_id,'car_color'=>$car_color));

        if ($type == 1) {
            $sql = "SELECT p.pid,p.parked_time,b.fee,p.parking_uid,p.type,p.location,u.free_park FROM parking AS p,parking_user AS u,parking_background AS b WHERE b.id=u.background
                    AND p.parking_uid=u.uid AND p.uid=:uid AND p.car_id=:car_id AND p.car_color=:car_color AND p.type=1";
        }
        else {
            $sql = "SELECT p.pid,p.parked_time,b.fee,p.parking_uid,p.type,p.location,n.free_park FROM parking AS p,parking_neighbor AS n,parking_background AS b WHERE b.id=n.background
                    AND p.parking_uid=n.id AND p.uid=:uid AND p.car_id=:car_id AND p.car_color=:car_color AND p.type=2";
        }

        return $this->_rdb->fetchAll($sql,array('uid'=>$uid,'car_id'=>$car_id,'car_color'=>$car_color));
    }

    /**
     * get user park info by location
     *
     * @param integer $uid
     * @param integer $location
     * @return array
     */
    public function getUserParkInfoByLocation($uid, $location)
    {
        $sql = "SELECT * FROM parking WHERE parking_uid=:uid AND location=:location AND type=1";

        return $this->_rdb->fetchRow($sql,array('uid'=>$uid,'location'=>$location));
    }

    /**
     * check the user park location is empty
     *
     * @param integer $uid
     * @param integer $parking_uid
     * @param integer $location
     * @param integer $type
     * @return boolean
     */
    public function isEmptyLocation($uid, $parking_uid, $location, $type)
    {
        if ($type == 1) {
             $sql = "SELECT COUNT(1) FROM parking WHERE parking_uid=:parking_uid AND location=:location AND type=1";
             $result = $this->_rdb->fetchOne($sql,array('parking_uid'=>$parking_uid, 'location'=>$location));
        }
        else {
            $sql = "SELECT COUNT(1) FROM parking WHERE uid=:uid AND parking_uid=:parking_uid AND location=:location AND type=2";
            $result = $this->_rdb->fetchOne($sql,array('parking_uid'=>$parking_uid, 'uid'=>$uid, 'location'=>$location));
        }

        return $result==0;
    }

    /**
     * get user parked location
     *
     * @param string $uid
     * @return array
     */
	public function getPakingLocation($uid) 
	{
		$sql = "SELECT location FROM parking WHERE parking_uid=:uid AND type=1";
		
		return $this->_rdb->fetchAll($sql, array('uid' => $uid));
	}
}
<?php

require_once 'Mdal/Abstract.php';

class Mdal_Parking_Car extends Mdal_Abstract
{
    /**
     * user table name
     *
     * @var string
     */
    protected $table_user = 'parking_car';

    protected static $_instance;

    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }
    /**
     * getUserAsset
     * @param integer $uid
     * @return integer
     */
    public function getUserAsset($uid)
    {
        $sql = "SELECT asset FROM parking_user WHERE uid=:uid";
        return $this->_rdb->fetchOne($sql, array('uid' => $uid));
    }
    /**
     * getUserEachCarCount
     * @param integer $uid
     * @return integer
     */
    public function getUserEachCarCount($uid)
    {
        $sql = "SELECT COUNT(car_id) AS count,car_id FROM parking_user_car WHERE uid=:uid GROUP BY car_id";
        return $this->_rdb->fetchAll($sql, array('uid' => $uid));
    }
    /**
     * getUserCarList
     * @param integer $uid
     * @return integer
     */
    public function getUserCarList($uid)
    {
        $sql = "SELECT DISTINCT car_id FROM parking_user_car WHERE uid=:uid";
        return $this->_rdb->fetchAll($sql, array('uid' => $uid));
    }
    /**
     * get user cars
     * @author lp
     * @param integer $uid
     * @return array
     */
    public function getUserCarsWhenChangeCar($uid)
    {
        $sql = "SELECT parking_car.price,parking_car.name,parking_car.cav_name,parking_user_car.* FROM parking_car,parking_user_car
                WHERE parking_car.cid=parking_user_car.car_id AND parking_user_car.uid=:uid AND parking_user_car.status=1 
                AND parking_car.type=1 ORDER BY parking_user_car.car_id";
        return $this->_rdb->fetchAll($sql, array('uid' => $uid));
    }
    /**
     * get user cars
     * @author lp
     * @param integer $uid
     * @param integer $carId
     * @return array
     */
    public function getOneCar($uid, $carId)
    {
        $sql = "SELECT pc.cav_name, pc.name FROM parking_car AS pc, parking_user_car AS puc
                WHERE puc.uid=:uid AND puc.car_id=pc.cid AND puc.car_id=:cid";
        return $this->_rdb->fetchRow($sql, array('uid' => $uid, 'cid' => $carId));
    }
    /**
     * get send car friends infomation
     * @author lp
	 * @param integer $uid
     * @return array
     */
    public function getSendCarFriendsInfo($fid)
    {
        $sql = "SELECT parking_user.id,parking_user.uid,car_count,receive_car_time,car_id,car_color
                FROM parking_user,parking_user_car
                WHERE parking_user.uid IN (:fid) AND parking_user.uid=parking_user_car.uid
                ORDER BY parking_user.uid DESC";
        return $this->_rdb->fetchAll($sql, array('fid' => $fid));
    }
}
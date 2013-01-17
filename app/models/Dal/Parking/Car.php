<?php

require_once 'Dal/Abstract.php';

class Dal_Parking_Car extends Dal_Abstract
{
    /**
     * user table name
     *
     * @var string
     */
    protected $table_user = 'parking_car';

    protected static $_instance;

    /**
     * get Dal_Parking_Car
     *
     * @return Dal_Parking_Car
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * buy new car
     * @author lp
     * @param array $info
     * @return integer
     */
    public function insertUserCars($info)
    {
        $this->_wdb->insert('parking_nopark', $info);
        $this->_wdb->insert('parking_user_car', $info);
        return $this->_wdb->lastInsertId();
    }
    /**
     * change car
     * @author lp
     * @param array $info
     * @return integer
     */
    public function insertUserCarsWhenChangeCar($info)
    {
        $this->_wdb->insert('parking_user_car', $info);
        return $this->_wdb->lastInsertId();
    }

    /**
     * delete user cars
     * @author lp
     * @param integer $uid
     * @param integer $cid
     * @param integer $color
     */
    public function deleteUserCars($uid, $cid, $color)
    {
        $sql = "DELETE FROM parking_user_car WHERE uid=:uid AND car_id=:cid AND car_color=:color";
        $this->_wdb->query($sql,array('uid'=>$uid, 'cid'=>$cid, 'color'=>$color));
    }

    /**
     * update user car
     *
     * @param string $uid
     * @param integer $cid
     * @param string $color
     * @param integer $status
     */
    public function updateUserCar($uid, $cid, $color, $status)
    {
    	$sql = "UPDATE parking_user_car SET status=:status WHERE uid=:uid AND car_id=:cid AND car_color=:color";
    	$this->_wdb->query($sql,array('status'=>$status,'uid'=>$uid,'cid'=>$cid,'color'=>$color));
    }

    /**
     * update user car
     *
     * @param integer $uid
     */
    public function updateUserCarCount($uid)
    {
        $sql = "UPDATE parking_user SET car_count=(SELECT COUNT(1) FROM parking_user_car WHERE uid=:uid) WHERE uid=:uid";
        $this->_wdb->query($sql, array('uid' => $uid));

        $sql = "UPDATE parking_user SET car_price=(SELECT IFNULL(SUM(c.price),0) FROM parking_user_car AS u,parking_car AS c
                WHERE u.car_id=c.cid AND u.uid=:uid AND u.status = 1) WHERE uid=:uid";
        $this->_wdb->query($sql, array('uid' => $uid));
    }

    /**
     * get car price
     *
     * @param integer $cid
     * @return integer
     */
    public function getCarPrice($cid)
    {
        $sql = "SELECT * FROM parking_car WHERE cid=:cid";
        return $this->_rdb->fetchRow($sql,array('cid'=>$cid));
    }

    /**
     * get car info
     *
     * @param integer $cid
     * @return integer
     */
    public function getParkingCarInfo($cid)
    {
        $sql = "SELECT * FROM parking_car WHERE cid=:cid";
        return $this->_rdb->fetchRow($sql,array('cid'=>$cid));
    }
    
    /**
     * get user ad bus info
     *
     * @param integer $uid
     * @return array
     */
    public function getUserAdBus($uid)
    {
        $sql = "SELECT c.cid,u.uid,c.color FROM parking_user_car AS u,parking_car AS c WHERE u.car_id=c.cid AND u.uid=:uid AND c.type=2";
        return $this->_rdb->fetchRow($sql,array('uid'=>$uid));
    }

	/**
     * get user cars
     * @author lp
     * @param integer $uid
     * @return array
     */
    public function getUserCars($uid)
    {
        $sql = "SELECT a.*,p.parking_uid,p.parked_time,p.type,p.location FROM
                (SELECT u.*,c.name,c.price,c.cav_name,c.times,c.type AS car_type,u.status AS carStatus FROM parking_user_car AS u,parking_car AS c WHERE u.car_id=c.cid AND u.uid=:uid) AS a
                LEFT JOIN parking AS p ON a.uid=p.uid AND a.car_id=p.car_id AND a.car_color=p.car_color";
        return $this->_rdb->fetchAll($sql,array('uid'=>$uid));
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
     * get car info
     *
     * @param integer $id id auto id
     * @return array
     */
    public function getCarInfo($cid)
    {
        $sql = "SELECT a.*,p.parking_uid,p.parked_time,p.type,p.location,s.background,s.free_park,b.fee FROM
                (SELECT u.*,c.name,c.price,c.cav_name,c.color,c.times,c.type AS car_type FROM parking_user_car AS u,parking_car AS c WHERE u.car_id=c.cid AND u.id=:id ) AS a
                LEFT JOIN parking AS p ON a.uid=p.uid AND a.car_id=p.car_id AND a.car_color=p.car_color
                LEFT JOIN parking_user AS s ON s.uid=parking_uid
                LEFT JOIN parking_background AS b ON b.id=background";

        return $this->_rdb->fetchRow($sql,array('id'=>$cid));
    }

    /**
     * check the car is user or not
     *
     * @param integer $uid
     * @param integer $car_id
     * @param integer $car_color
     * @return boolean
     */
    public function isUserCar($uid, $car_id, $car_color)
    {
        $sql = "SELECT COUNT(1) FROM parking_user_car WHERE uid=:uid AND car_id=:car_id AND car_color=:car_color";

        $result = $this->_rdb->fetchOne($sql,array('uid'=>$uid,'car_id'=>$car_id,'car_color'=>$car_color));
        return $result>0;
    }

    /**
     * check the car is user or not
     *
     * @param integer $uid
     * @param integer $car_id
     * @param integer $car_color
     * @return boolean
     */
    public function getOneCar($uid, $car_id, $car_color)
    {
        $sql = "SELECT * FROM parking_user_car WHERE uid=:uid AND car_id=:car_id AND car_color=:car_color";

        return $this->_rdb->fetchRow($sql,array('uid'=>$uid,'car_id'=>$car_id,'car_color'=>$car_color));
    }

    /**
     * get user cars
     * @author huch
     * @param integer $uid
     * @return array
     */
    public function getUserbreakCars($uid)
    {
        $sql = "SELECT parking_car.price,parking_car.name,parking_car.cav_name,parking_user_car.* FROM parking_car,parking_user_car
                WHERE parking_car.cid=parking_user_car.car_id AND parking_user_car.uid=:uid AND parking_user_car.status=0";
        return $this->_rdb->fetchAll($sql,array('uid'=>$uid));
    }
    /**
     * get car count
     * @author lp
     * @return array
     */
    public function getCarCount()
    {
        $sql = "SELECT COUNT(cid) FROM parking_car WHERE type=1";
        return $this->_rdb->fetchOne($sql);
    }
    /**
     * get car array
     * @author lp
     * @param integer $pageIndex
     * @param integer $pageSize
     * @return array
     */
    public function getCarList($pageIndex=1,$pageSize=8)
    {
        $start = ($pageIndex - 1) * $pageSize;

        $sql = "SELECT * FROM parking_car WHERE type=1 ORDER BY price LIMIT $start,$pageSize";

        return $this->_rdb->fetchAll($sql);
    }
    
    /**
     * check the car color
     * @author lp
     * @param string $color
     * @return boolean
     */
    public function hasTheColor($color)
    {
        $sql="SELECT COUNT(1) FROM parking_car_color WHERE name='$color'";
        $result = $this->_rdb->fetchOne($sql);
        return $result>0;
    }
    /**
     * check the car id
     * @author lp
     * @param integer $cid
     * @return boolean
     */
    public function hasTheCar($cid)
    {
        $sql = "SELECT COUNT(1) FROM parking_car WHERE cid=:cid";
        $result = $this->_rdb->fetchOne($sql,array('cid'=>$cid));
        return $result>0;
    }
    /**
     * get user park
     * @author lp
     * @param integer $uid
     * @return array
     */
    public function getUserPark($uid)
    {
        $sql = "SELECT p.*,b.name,b.fee,b.type FROM parking_user AS p,parking_background AS b
                WHERE  b.id=p.background AND p.uid=:uid";

        return $this->_rdb->fetchRow($sql,array('uid'=>$uid));
    }

    /**
     * getAllUserCars
     * @author lp
     * @param integer $uid
     * @return boolean
     */
    public function getAllUserCars($uid,$cid,$color)
    {
        $sql="SELECT * FROM parking_user_car WHERE uid=:uid AND car_id=:car_id AND car_color=:car_color";
        return $this->_rdb->fetchRow($sql,array('uid'=>$uid,'car_id'=>$cid,'car_color'=>$color));

    }

    /**
     * get user car count
     *
     * @param string $uid
     * @return integer
     */
    public function getUserCarCount($uid)
    {
        $sql="SELECT COUNT(1) FROM parking_user_car WHERE uid=:uid";
        return $this->_rdb->fetchOne($sql,array('uid'=>$uid));

    }

    /**
     * update user asset
     * @author lp
     * @param integer $price
     * @param integer $uid
     * @return void
     */
    public function updateUserAsset($price, $uid)
    {
        $sql = "UPDATE parking_user SET asset=asset-:price WHERE uid=:uid ";
        $this->_wdb->query($sql, array('price'=>$price, 'uid' => $uid));
    }
    /**
     * update user car
     * @author lp
     * @param integer $uid
     */
    public function updateUserCarWhenBuyAndChange($uid)
    {
        $sql = "UPDATE parking_user SET car_count=(SELECT COUNT(1) FROM parking_user_car WHERE uid=:uid) WHERE uid=:uid";
        $this->_wdb->query($sql, array('uid' => $uid));

        $sql = "UPDATE parking_user SET car_price=(SELECT IFNULL(SUM(c.price),0) FROM parking_user_car AS u,parking_car AS c
                WHERE u.car_id=c.cid AND u.uid=:uid) WHERE uid=:uid";
        $this->_wdb->query($sql, array('uid' => $uid));
    }
    /**
     * updateUserCard especially for when sid=4
     * @author lp
     * @param integer $uid
     * @return integer
     */
    public function updateUserCard($uid){
        $sql="UPDATE parking_user_card SET count=count-1 WHERE uid=:uid AND sid=4";
        $this->_wdb->query($sql, array('uid' => $uid));
    }
    /**
     * insert minifeed
     * @author lp
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
     * @author lp
     * @param array $info
     * @return integer
     */
    public function insertNewsfeed($info)
    {
        $this->_wdb->insert('parking_newsfeed', $info);
        return $this->_wdb->lastInsertId();
    }
    /**
     * get user cars
     * @author lp
     * @param integer $uid
     * @return array
     */
    public function getUserCarsWhenChangeCar($uid)
    {
        $sql = "SELECT c.price,c.name,c.cav_name,u.* FROM parking_car AS c,parking_user_car AS u
                WHERE c.cid=u.car_id AND u.uid=:uid AND u.status=1 AND c.type=1";
        return $this->_rdb->fetchAll($sql,array('uid'=>$uid));
    }
    /**
     * update parking info
     * @abstract lp
     * @param integer $uid
     * @param integer $cidNew
     * @param integer $colorNew
     * @param integer $cidOld
     * @param integer $colorOld
     */
    public function updateParkingInfo($uid, $cidNew, $colorNew, $cidOld, $colorOld)
    {
        $sql = "UPDATE parking SET car_id=:cidNew,car_color=:colorNew WHERE uid=:uid AND car_id=:cidOld AND car_color=:colorOld";
        $this->_wdb->query($sql, array('cidNew'=>$cidNew, 'colorNew'=>$colorNew, 'uid'=>$uid, 'cidOld'=>$cidOld, 'colorOld'=>$colorOld));
    }
    /**
     * update no parking info
     * @abstract lp
     * @param integer $uid
     * @param integer $cidNew
     * @param integer $colorNew
     * @param integer $cidOld
     * @param integer $colorOld
     */
    public function updateNoParkingInfo($uid, $cidNew, $colorNew, $cidOld, $colorOld)
    {
        $sql = "UPDATE parking_nopark SET car_id=:cidNew,car_color=:colorNew WHERE uid=:uid AND car_id=:cidOld AND car_color=:colorOld";
        $this->_wdb->query($sql, array('cidNew'=>$cidNew, 'colorNew'=>$colorNew, 'uid'=>$uid, 'cidOld'=>$cidOld, 'colorOld'=>$colorOld));
    }
    /**
     * check if user has have the car
     * @author lp
     * @param integer $uid
     * @param integer $car_id
     * @return boolean
     */
    public function isUserHaveTheCar($uid,$car_id){
        $sql="SELECT * FROM parking_user_car WHERE uid=:uid AND car_id=:car_id";
        return $this->_rdb->fetchRow($sql,array('uid'=>$uid,'car_id'=>$car_id));

    }
    /**
     * check if user has have the card
     * @author lp
     * @param integer $uid
     * @return boolean
     */
    public function isUserHaveTheCard($uid){
        $sql="SELECT count FROM parking_user_card WHERE uid=:uid AND sid=4";
        return $this->_rdb->fetchRow($sql,array('uid'=>$uid));

    }
    /**
     * check if the old car is a waste car
     * @abstract lp
     * @param integer $uid
     * @param integer $cidOld
     */
    public function isWasteCar($uid, $cidOld){
    	$sql = "SELECT status FROM parking_user_car WHERE uid=:uid AND car_id=:car_id";
    	return $this->_rdb->fetchRow($sql,array('uid'=>$uid, 'car_id'=>$cidOld));
    }
    /**
     * check the break car is user or not
     *
     * @param integer $uid
     * @param integer $car_id
     * @param integer $car_color
     * @return boolean
     */
    public function isUserBreakCar($uid, $car_id, $car_color)
    {
        $sql = "SELECT COUNT(1) FROM parking_user_car WHERE uid=:uid AND car_id=:car_id AND car_color=:car_color AND status=0";

        $result = $this->_rdb->fetchOne($sql,array('uid'=>$uid,'car_id'=>$car_id,'car_color'=>$car_color));
        return $result>0;
    }
    /**
     * getMaxPriceOfUserCars
     * @param integer $uid
     * @return integer
     */
    public function getMaxPriceOfUserCars($uid)
    {
        $sql = "SELECT MAX(parking_car.price) AS maxprice
                FROM parking_car,parking_user_car
                WHERE parking_car.cid=parking_user_car.car_id AND parking_user_car.uid=:uid AND parking_user_car.status=1";
        return $this->_rdb->fetchOne($sql, array('uid'=>$uid));
    }
}
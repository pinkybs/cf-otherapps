<?php

require_once 'Mbll/Abstract.php';

/**
 * parking flash logic's Operation
 *
 * @package    Mbll
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/05/19    Huch
 */
class Mbll_Parking_Parking extends Mbll_Abstract
{
    /**
     * check report
     *
     * @param string $uid
     * @param string $pid
     * @param integer $location
     * @param string $parking_uid
     * @param integer $car_id
     * @param string $car_color
     * @param string $car_name
     * @return integer 0,1
     */
    public function checkReport($uid, $pid, $location, &$parking_uid, &$car_id, &$car_color, &$car_name)
    {
        //get parking info
        require_once 'Mdal/Parking/Puser.php';
        $mdalPuser = new Mdal_Parking_Puser();
        $parking_user = $mdalPuser->getParkingInfoByLocation($pid, $location);
        $parking_uid = $parking_user['uid'];
        $car_id = $parking_user['car_id'];
        $car_color = $parking_user['car_color'];
        $car_name = $parking_user['name'];

        if (empty($parking_user)) {
            return -1;
        }

        require_once 'Bll/Parking/Index.php';
        $bllParkIndex = new Bll_Parking_Index();
        $result = $bllParkIndex->checkReport($uid, $pid, $parking_user['uid'], $parking_user['car_id'], $parking_user['car_color'], $location);

        return $result['status'] == 1 ? 1 : 0;
    }

    /**
     * check can park
     *
     * @param string $uid
     * @param string $pid
     * @param integer $location
     * @return boolean
     */
    public function checkCanPark($uid, $pid, $location)
    {
        $type = ($pid != '-1' && $pid != '-2') ? 1 : 2;

        //check is friend
        if ($type == 1){
            $isFriend = Bll_Parking_Friend::isFriend($uid, $pid);

            if (!$isFriend) {
                return false;
            }
        }
        else{
            //check friend count
            require_once 'Bll/Parking/Friend.php';
            $friendIds = Bll_Parking_Friend::getFriendIds($uid);
            $arrFriendId = explode(',', $friendIds);
            if ( count($arrFriendId) >2 ) {
                return false;
            }
        }

        //check the location is empty
        require_once 'Dal/Parking/Parking.php';
        $dalParkParking = new Dal_Parking_Parking();
        $isEmpty = $dalParkParking->isEmptyLocation($uid, $pid, $location, $type);
        if (!$isEmpty) {
            return false;
        }

        return true;
    }

    /**
     * check parking
     *
     * @param string $uid
     * @param string $park_uid
     * @param integer $car_id
     * @param string $car_color
     * @param integer $location
     * @return array
     */
    public function checkParking($uid, $park_uid, $car_id, $car_color, $location)
    {
        $result = array('status'=>1);
        require_once 'Dal/Parking/User.php';
        $dalParkPuser = new Dal_Parking_User();

        $user = $dalParkPuser->getUserPark($uid);

        //check is friend
        if ($type == 1){
            $isFriend = Bll_Parking_Friend::isFriend($uid, $park_uid);

            if (!$isFriend) {
                return array('status'=>-1);
            }
        }
        else{
            //check is my neighbor
            if ($user['neighbor_left'] != $park_uid && $user['neighbor_right'] != $park_uid) {
                return array('status'=>-1);
            }

            //check friend count
            require_once 'Bll/Parking/Friend.php';
            $friendIds = Bll_Parking_Friend::getFriendIds($uid);
            $arrFriendId = explode(',', $friendIds);
            if ( count($arrFriendId) >2 ) {
                return array('status'=>-1);
            }
        }

        //check the car is user
        require_once 'Dal/Parking/Car.php';
        $dalParkCar = new Dal_Parking_Car();
        $isUser = $dalParkCar->isUserCar($uid, $car_id, $car_color);
        if (!$isUser) {
            return array('status'=>-1);
        }

        require_once 'Dal/Parking/Parking.php';
        $dalParkParking = new Dal_Parking_Parking();
        //check the location is empty
        $isEmpty = $dalParkParking->isEmptyLocation($uid, $park_uid, $location, $type);
        if (!$isEmpty) {
            return array('status'=>-2);
        }

        $lastParkInfo = $dalParkParking->getParkInfo($uid,$car_id,$car_color);
        //check last park user
        if ($lastParkInfo[0]['parking_uid'] == $park_uid && $type == $lastParkInfo[0]['type']) {
            return array('status'=>-3);
        }

        //check last park time
        if (time()-$lastParkInfo[0]['parked_time'] < 3600) {
            return array('status'=>-4);
        }

        //get car info
        $carInfo = $dalParkCar->getParkingCarInfo($car_id);
        //get this car info
        $oneCar = $dalParkCar->getOneCar($uid, $car_id, $car_color);
        //check car status
        if ($oneCar['status'] != 1) {
            return array('status'=>-1);
        }
    }

    /**
     * user first login
     *
     * @param integer $uid
     * @return boolean
     */
    public function firstLogin($uid, &$carColor)
    {
        $result = false;

        try {
            $this->_wdb->beginTransaction();

            require_once 'Dal/Parking/House.php';
            $dalHouse = Dal_Parking_House::getDefaultInstance();
            $background = $dalHouse->getHouseByType('A');

            $park = array('uid' => $uid,
                          'asset' => 400000,
                          'background' => $background[rand(0, count($background)-1)]['id'],
                          'free_park' => rand(1,3),
                          'neighbor_left' => -1,
                          'neighbor_right' => -2,
                          'last_login_time' => time());
            
            require_once 'Dal/Parking/Puser.php';
            $dalParkingPuser = Dal_Parking_Puser::getDefaultInstance();
            $dalParkingPuser->insertParkingUser($park, $uid);

            $color = array(1 => 'black', 2 => 'white', 3 => 'silver', 4 => 'yellow', 5 => red, 6 => 'blue');
            $carColor = $color[rand(1,6)];

            $car = array('uid' => $uid,
                         'car_id' => 1,
                         'car_color' => $carColor,
                         'create_time' => time());

            require_once 'Dal/Parking/Car.php';
            $dalParkingCar = new Dal_Parking_Car();

            $dalParkingCar->insertUserCars($car);
            $dalParkingPuser->updateUserCar($uid);

            $this->_wdb->commit();
            $result = true;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return $result;
        }

        return $result;
    }
}
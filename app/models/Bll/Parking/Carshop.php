<?php

require_once 'Bll/Abstract.php';

class Bll_Parking_Carshop extends Bll_Abstract{

    /**
     * buy car
     * @author lp
     * @param integer $uid
     * @param integer $cid
     * @param integer $color
     * @return integer
     */

    public function buyCar($uid, $cid, $color)
    {
        $result = -1;

        require_once 'Dal/Parking/Car.php';
        $dalCar = new Dal_Parking_Car();

        $carNew = $dalCar->getParkingCarInfo($cid);
        //check is ad bus
        if ( $carNew['type'] == 2 ) {
            return $result;
        }
        
        $price = $carNew['price'];

        //check color
        if (!$dalCar->hasTheColor($color)) {
            return $result;
        }

        //check the car id and color
        if (!$dalCar->hasTheCar($cid)) {
            return $result;
        }

        //check user asset
        $userPark = $dalCar->getUserPark($uid);
        if ($userPark['asset'] < $price) {
            return -2;
        }

        //check user car count
        if ($userPark['car_count'] == 8) {
            return -4;
        }
        //check user buy a new car or buy the same car
        $whichCar=$this->getUserCar($uid,$cid);
        if($whichCar==2){
            return -3;
        }

        //check user car
        $userCars = $dalCar->getAllUserCars($uid,$cid,$color);
        if($userCars!=null){
            return -5;
        }


        //report start
        $this->_wdb->beginTransaction();

        try {
            //update user asset
            $dalCar->updateUserAsset($price,$uid);

            //insert into user car
            $carInfo = array('uid' => $uid,
                             'car_id' => $cid,
                             'car_color' => $color,
                             'create_time' => time());
            $dalCar->insertUserCars($carInfo);

            //update user car count and price
            $dalCar->updateUserCarWhenBuyAndChange($uid);
            //update user card
            if($whichCar==1){
                $dalCar->updateUserCard($uid);
            }

            $create_time = date('Y-m-d H:i:s');
            //insert into minifeed
            $minifeed = array('uid' => $uid,
                              'template_id' => 20,
                              'actor' => $uid,
                              'title' => '{"car_name":"'. $carNew['name'] . '","time":"' . $carNew['times'] . '"}',
                              'icon' => Zend_Registry::get('static') . "/apps/parking/img/icon/car.gif",
                              'create_time' => $create_time);
            $dalCar->insertMinifeed($minifeed);

            //insert into newsfeed
            $newsfeed = array('uid' => $uid,
                              'template_id' => 21,
                              'actor' => $uid,
                              'title' => '{"car_name":"'. $carNew['name'] . '","time":"' . $carNew['times'] . '"}',
                              'icon' => Zend_Registry::get('static') . "/apps/parking/img/icon/car.gif",
                              'create_time' => $create_time);
            $dalCar->insertNewsfeed($newsfeed);

            $this->_wdb->commit();
            $result = 1;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
        }

        return $result;
    }

    /**
     *
     * @author lp
     * @param integer $uid
     * @param integer $car_id
     * @return boolean
     */
    public function getUserCar($uid,$car_id){
        $result=-1;

        require_once 'Dal/Parking/Car.php';
        $dalCar = new Dal_Parking_Car();

        $userCar=$dalCar->isUserHaveTheCar($uid,$car_id);
        // if user have the dame car
        if($userCar!=null){
            $userCard=$dalCar->isUserHaveTheCard($uid);

            if($userCard['count']>0){
            	//hava the same car and have card
                $result=1;
            }else{
            	//hava the same car but not have card
                $result=2;
            }
        }
        else{
        	//not hava the same car
            $result=3;
        }

        return $result;
    }

    /**
     * change car
     * @author lp
     * @param integer $uid
     * @param integer $cidNew
     * @param integer $colorNew
     * @param integer $cidOld
     * @param integer $colorOld
     * @return integer
     */
    public function changeCar($uid, $cidNew, $colorNew, $cidOld, $colorOld)
    {
        $result = -1;

        $colorOld = str_replace("\\", "", $colorOld);
        $colorOld = str_replace("\"", "", $colorOld);

        require_once 'Dal/Parking/Car.php';
        $dalCar = new Dal_Parking_Car();

        $carNew = $dalCar->getParkingCarInfo($cidNew);
        $carOld = $dalCar->getParkingCarInfo($cidOld);

        //check the car is ad bus
        if ( $carNew['type'] == 2 || $carOld['type'] == 2) {
            return $result;
        }
        //check if the old car is a waste car
        $isWasteCar = $dalCar->isWasteCar($uid, $cidOld);

        if( $isWasteCar['status'] == 0){
        	return $result;
        }

        //check color
        if (!$dalCar->hasTheColor($colorNew)) {
            return $result;
        }

        if (!$dalCar->hasTheColor($colorOld)) {
            return $result;
        }

        //check the car id

        if (!$dalCar->hasTheCar($cidNew)) {
            return $result;
        }

        if (!$dalCar->hasTheCar($cidOld)) {
            return $result;
        }

        //check the old car is user
        $isUser = $dalCar->isUserCar($uid, $cidOld, $colorOld);
        if (!$isUser) {
            return $result;
        }

        $oldCarPrice = floor($carOld['price'] * 0.9);

        //check user if user hava enough money
        $userPark = $dalCar->getUserPark($uid);
        if ($userPark['asset'] + $oldCarPrice < $carNew['price']) {
            return -2;
        }

        //check user change a new car or change a same car
        $whichCar=$this->getUserCar($uid,$cidNew);
        if($whichCar==2){
            return -3;
        }

        //check user car
        $userCars = $dalCar->getAllUserCars($uid,$cidNew,$colorNew);

        if($userCars!=null){
            return -3;
        }

        //report start
        $this->_wdb->beginTransaction();

        try {
            //update user asset
            $dalCar->updateUserAsset($carNew['price'] - $oldCarPrice, $uid);

            //delete user old car
            $dalCar->deleteUserCars($uid, $cidOld, $colorOld);

            //insert into user car
            $carInfo = array('uid' => $uid,
                             'car_id' => $cidNew,
                             'car_color' => $colorNew,
                             'create_time' => time());
            $dalCar->insertUserCarsWhenChangeCar($carInfo);

            //update user parking info
            $dalCar->updateParkingInfo($uid, $cidNew, $colorNew, $cidOld, $colorOld);
            //update user no parking info
            $dalCar->updateNoParkingInfo($uid, $cidNew, $colorNew, $cidOld, $colorOld);

            //update user car count and price
            $dalCar->updateUserCarWhenBuyAndChange($uid);
            //update user card
            if($whichCar==1){
                $dalCar->updateUserCard($uid);
            }


            $this->_wdb->commit();
            $result = 1;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
        }
        return $result;
    }

}
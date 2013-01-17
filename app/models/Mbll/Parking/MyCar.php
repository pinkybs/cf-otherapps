<?php

require_once 'Mbll/Abstract.php';

/**
 * parking flash logic's Operation
 *
 * @package    Mbll
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/05/19    lp
 */
class Mbll_Parking_MyCar extends Mbll_Abstract
{
    /**
     * get user cars info
     *
     * @param string $uid
     * @param array $neighbor
     * @return array
     */
    public function getUserCars($uid, $neighbor=array())
    {
        require_once 'Dal/Parking/Car.php';
        $dalParkCar = new Dal_Parking_Car();
        //get user cars
        $userCars = $dalParkCar->getUserCars($uid);

        $cars = array();

        require_once 'Bll/User.php';

        foreach ($userCars as $car) {
            //if car status ==0 廃車
            if ( $car['status'] != 1 ) {
                $car['status'] = '廃車状態のため、<br/>整備カードを使うまで使用できません';
                $car['money'] = '廃車';
                $car['temp'] = '0';
                $cars = array_merge($cars, array($car));
            }
            //if parked time != null
            else if ($car['parked_time'] != null) {
                //park at friend
                if ($car['type'] == 1) {
                    require_once 'Dal/Parking/Puser.php';
                    $dalParkPuser = new Dal_Parking_Puser();
                    $parkingInfo = $dalParkPuser->getUserPark($car['parking_uid']);

                    $parkingUserInfo = Bll_User::getPerson($car['parking_uid']);

                    if ( $parkingInfo['free_park'] == $car['location'] ) {
                        $isfree = 1;
                    }
                    else {
                        $isfree = 0;
                    }

                    $result = $this->getUserCarStatus($car['uid'], $car, $parkingUserInfo->getDisplayName(), $parkingInfo['fee'], $isfree, 1, $uid);
                    $car['money'] = "¥".$result['money'];
                    $car['status'] = $result['status'];
                    $car['temp'] = $result['temp'];

                    $cars = array_merge($cars, array($car));
                }
                //park at neighbor
                else {
                    require_once 'Dal/Parking/Puser.php';
                    $dalParkPuser = new Dal_Parking_Puser();
                    $userPark = $dalParkPuser->getUserPark($uid);

                    require_once 'Dal/Parking/Neighbor.php';
                    $dalParkNeighbor = new Dal_Parking_Neighbor();
                    $neighbor = $dalParkNeighbor->getNeighbor($uid, $userPark['neighbor_left'], $userPark['neighbor_right']);

                    foreach ($neighbor as $n) {
                        if ($car['parking_uid'] == $n['id']) {
                            if ($n['free_park'] == $car['location']) {
                                $result = $this->getUserCarStatus($uid, $car, $n['nickname'], $n['fee'], 1, 2, $uid);
                            }
                            else {
                                $result = $this->getUserCarStatus($uid, $car, $n['nickname'], $n['fee'], 0, 2, $uid);
                            }

                            $car['money'] = "¥".$result['money'];
                            $car['status'] = $result['status'];
                            $car['temp'] = $result['temp'];

                            $cars = array_merge($cars, array($car));
                            break;
                        }
                    }
                }
            }
            //if park time == null
            else {
                require_once 'Dal/Parking/Nopark.php';
                $dalParkNopark = new Dal_Parking_Nopark();
                $nopark = $dalParkNopark->getNoPark($uid, $car['car_id'], $car['car_color']);

                require_once 'Dal/Parking/Puser.php';
                $dalParkPuser = new Dal_Parking_Puser();
                $userAsset = $dalParkPuser->getAsset($uid);

                $time = floor((time() - $nopark['create_time']) / 60);
                //if time> 1 day?
                $time = $time > 1440 ? 1440 : $time;

                $money = $userAsset < $time*2 ? $userAsset : $time*2;
                $money = number_format($money);
                $car['status'] = '移動中（駐車していません）';
                $car['money'] = "¥$money";
                $car['temp'] = '0';
                $cars = array_merge($cars, array($car));
            }
        }

        return $cars;
    }

    /**
     * get user car status
     *
     * @param string $uid
     * @param array $car
     * @param string $nickname
     * @param integer $fee
     * @param integer $isfree
     * @param integer $type 1:user 2:neighbor
     * @param string $userID
     * @return string
     */
    public function getUserCarStatus($uid, $car, $nickname, $fee, $isfree, $type, $userID)
    {
        //check is self or not
        $isSelf = $userID == $uid;

        //get money
        $time = floor((time() - $car['parked_time']) / 900);
        //time>8 hours?
        $time = $time > 32 ? 32 : $time;
        $money = $time * $fee * $car['times'];
        $money = number_format($money);

        $temp = $time/4 + 1;
        $temp = floor($temp);
        //park in friend
        if ($type == 1) {
            if ($isSelf || $car['type'] == 1) {

            	require_once 'Mbll/Parking/Feed.php';
                $mbllFeed = new Mbll_Parking_Feed();
                $parkingUrl = Zend_Registry::get('host') . '/mobile/parking/start?parking_pid=' . $car['parking_uid'];
                $parkingUrl = $mbllFeed->changeCommenUrlToMixiUrl($parkingUrl);

                if ($isfree) {
                    $money = '0';
                    $temp = '0';

                    $status = '<a href="'. $parkingUrl . '" >' . $nickname . "</a>のパーキング（無料）に駐車中<br/>";
                }
                else {
                    $status = '<a href="'. $parkingUrl . '" >' . $nickname . "</a>のパーキングに駐車中<br/>";
                }
            }
        }
        //park in neighbor
        else {
            if ($isfree) {
                $money = '0';
                $temp = '0';
                $status = "<font color='blue'>" . $nickname . "</font>のパーキング（無料）に駐車中<br/>";
            }
            else {
                $status = "<font color='blue'>" . $nickname . "</font>のパーキングに駐車中<br/>";
            }
        }

        $result = array('money' => $money, 'status' => $status, 'temp' => $temp);
        return $result;
    }
}
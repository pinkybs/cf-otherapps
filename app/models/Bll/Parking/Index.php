<?php

/**
 * parking logic's Operation
 *
 * @package    Bll
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2008/02/19    Liz
 */
class Bll_Parking_Index extends Bll_Abstract
{
    /**
     * send car to friend
     *
     * @param string $uid
     * @param integer $car_id
     * @param integer $car_color
     * @param string $fid
     * @return integer
     */
    public function sendFriend($uid, $car_id, $car_color, $fid)
    {
        $result = -1;

        require_once 'Dal/Parking/Puser.php';
        $dalParkPuser = new Dal_Parking_Puser();

        //check is friend
        require_once 'Bll/Parking/Friend.php';
        $isFriend = Bll_Parking_Friend::isFriend($uid, $fid);
        if (!$isFriend) {
            return $result;
        }

        //check friend is join parking
        $isIn = $dalParkPuser->isInParkingUser($fid);
        if (!$isIn) {
            return $result;
        }

        //check friend car count
        $friendPark = $dalParkPuser->getUserPark($fid);
        if($friendPark['car_count'] == 8) {
            return -2;
        }

        //check friend car
        //if friend has this car return false
        require_once 'Dal/Parking/Car.php';
        $dalParkCar = new Dal_Parking_Car();
        $friendCars = $dalParkCar->getUserCars($fid);
        foreach ($friendCars as $car) {
            if ($car['car_id'] == $car_id && $car['car_color'] == $car_color) {
                return -3;
            }
        }

        //check the car is user
        $isUser = $dalParkCar->isUserCar($uid, $car_id, $car_color);
        if (!$isUser) {
            return $result;
        }

        //check local user car count
        $userPark = $dalParkPuser->getUserPark($uid);
        if ( $userPark['car_count'] == 1 ) {
            return -6;
        }

        //check last send friend time
        if (time() - $userPark['send_car_time']< 30*24*3600) {
            return -4;
        }

        //check friend last receive car time
        if (time() - $friendPark['receive_car_time']< 30*24*3600) {
            return -5;
        }
        
        //get this car info
        $oneCar = $dalParkCar->getOneCar($uid, $car_id, $car_color);
        //check car status
        if ($oneCar['status'] != 1) {
            return $result;
        }
        
        require_once 'Dal/Parking/Car.php';
        $dalParkCar = new Dal_Parking_Car();
        $parkCarInfo = $dalParkCar->getParkingCarInfo($car_id);
        //check car is ad bus
        if ( $parkCarInfo['type'] == 2 ) {
            return $result;
        }
        
        //send user start
        $this->_wdb->beginTransaction();

        try {
            require_once 'Dal/Parking/Parking.php';
            $dalParkParking = new Dal_Parking_Parking();
            $lastParkInfo = $dalParkParking->getParkInfo($uid,$car_id,$car_color);

            //clear last park fee
            if (count($lastParkInfo) > 0) {
                if ($lastParkInfo[0]['location'] != $lastParkInfo[0]['free_park']) {
                    //update user asset
                    $time = floor((time()-$lastParkInfo[0]['parked_time'])/900);
                    $time = $time>32 ? 32 : $time;

                    $money = $time*$lastParkInfo[0]['fee']*$parkCarInfo['times'];
                    $dalParkPuser->updateUserAsset($money, $uid, 2);
                }

                //delete last parking info
                $dalParkParking->deleteParkingInfo($lastParkInfo[0]['pid']);
            }
            else {

                //update nopark info
                require_once 'Dal/Parking/Nopark.php';
                $dalParkNopark = new Dal_Parking_Nopark();
                $dalParkNopark->deleteNoPark($uid, $car_id, $car_color);
            }

            //delete user old car
            $dalParkCar->deleteUserCars($uid, $car_id, $car_color);

            //insert into user car
            $carInfo = array('uid' => $fid,
                             'car_id' => $car_id,
                             'car_color' => $car_color,
                             'create_time' => time());
            $dalParkCar->insertUserCars($carInfo);

            //update user car count and price
            $dalParkCar->updateUserCarCount($uid);
            $dalParkCar->updateUserCarCount($fid);

            //update user last send car time
            $dalParkPuser->updateUserSendCarTime($uid);

            //update user last revice car time
            $dalParkPuser->updateUserReciveCarTime($fid);

            require_once 'Dal/Parking/Feed.php';
            $dalParkFeed = new Dal_Parking_Feed();
            $create_time = date('Y-m-d H:i:s');
            //insert into minifeed
            $minifeed1 = array('uid' => $uid,
                              'template_id' => 24,
                              'actor' => $uid,
                              'target' => $fid,
                              'title' => '{"car_name":"'. $parkCarInfo['name'] . '"}',
                              'icon' => Zend_Registry::get('static') . "/apps/parking/img/icon/gift.gif",
                              'create_time' => $create_time);
            $dalParkFeed->insertMinifeed($minifeed1);

            $minifeed2 = array('uid' => $fid,
                              'template_id' => 28,
                              'actor' => $uid,
                              'target' => $fid,
                              'title' => '{"car_name":"'. $parkCarInfo['name'] . '"}',
                              'icon' => Zend_Registry::get('static') . "/apps/parking/img/icon/gift.gif",
                              'create_time' => $create_time);
            $dalParkFeed->insertMinifeed($minifeed2);

            $minifeed1['template_id'] = 29;
            $minifeed2['template_id'] = 29;
            //insert into newsfeed
            $dalParkFeed->insertNewsfeed($minifeed1);
            $dalParkFeed->insertNewsfeed($minifeed2);

            $result = 1;
            $this->_wdb->commit();
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
        }
        return $result;
    }

    /**
     * get rank info
     *
     * @param string $uid
     * @param integer $type1
     * @param integer $type2
     * @return array
     */
    public function getRankInfo($uid, $type1, $type2)
    {
        require_once 'Bll/Parking/Friend.php';
        $friendIds = Bll_Parking_Friend::getFriendIds($uid);
        $friendIds = explode(',', $friendIds);

        require_once 'Dal/Parking/Puser.php';
        $dalParkPuser = new Dal_Parking_Puser();
        $count = $dalParkPuser->getRankingCount($uid, $type1, $friendIds);
        $rankCount = $count;

        if ($count>2) {
            $count = $count-2;
            //get rank info about user
            $userRankNm = $dalParkPuser->getUserRankNm($uid, $friendIds, $type1, $type2);

            //get start number
            $start = $userRankNm>5 ? ($userRankNm-5) : 0;

            //get array count
            $allCount = 16;
            if ( $userRankNm > 5 ) {
                $userRightCount = $count-$userRankNm;

                if ( $userRightCount < 3 ) {
                    $start = $start - (3-$userRightCount);
                    if ( $start > 0 ){
                        $allCount = 8;
                        $count = 8;
                    }
                    else {
                        $start = 0;
                        $allCount = $count;
                    }
                }
                else if ( ($count-$start) <= 16 ) {
                    $allCount = $count-$start;
                }
            }
            else if ( ($count-$start) > 16 ) {
                $allCount = 14;
            }//init count < 8
            else {
                $allCount = $count;
            }

            //get rank info
            $rankInfo = $dalParkPuser->getRankingUser($uid, $friendIds, $type1, $type2, $allCount, 'ASC', $start);

            require_once 'Bll/User.php';
            Bll_User::appendPeople($rankInfo, 'uid');

            $uesrRankNm = ($rankCount-$start);
            $response = array('rankInfo' => $rankInfo, 'userRankNm' => $uesrRankNm, 'rankStatus'=>1);
        }
        else {
            $response = array('rankStatus'=>2);
        }
        return $response;
    }

    /**
     * get more rank info
     *
     * @param string $uid
     * @param integer $type1
     * @param integer $type2
     * @param integer $rankId
     * @param integer $allCount
     * @param integer $isRight
     * @return array
     */
    public function getMoreRank($uid, $type1, $type2, $rankId, $allCount, $isRight)
    {
        require_once 'Dal/Parking/Puser.php';
        $dalParkPuser = new Dal_Parking_Puser();

        //get friend info
        $friendIds = Bll_Parking_Friend::getFriendIds($uid);
        $friendIds = explode(',', $friendIds);

        //$allCount = $dalParkPuser->getRankingCount($uid, $type1, $friendIds);
        //get start number and array count
        $pageSize = 8;

        if ( $isRight == 1 ) {
            $start = $allCount - $rankId + 1;
            $rankCount = $rankId-1;
            if ( $rankCount < 10 ) {
                $pageSize = $rankCount-2;
            }
        }
        //move left
        else {
            $otherCount = $allCount - $rankId;
            if ( $otherCount > 8) {
                $start = $otherCount - 8;
                $rankCount = $rankId + 8;
            }
            else {
                $start = $start > 0 ? $start : 0;
                $rankCount = $rankId + $otherCount;
                $pageSize = $otherCount;
            }
        }
        //get rank info
        $rankInfo = $dalParkPuser->getRankingUser($uid, $friendIds, $type1, $type2, $pageSize, 'ASC', $start);
        $allCount = $allCount;

        if ( $rankInfo ) {
            Bll_User::appendPeople($rankInfo, 'uid');
        }

        $result = array('rankInfo' => $rankInfo, 'count' => $rankCount, 'allCount' => $allCount, 'isRight' => $isRight);

        return $result;

    }

    /**
     * get last rank info
     *
     * @param string $uid
     * @param integer $type1
     * @param integer $type2
     * @param integer $isRight
     * @return array
     */
    public function getLastRank($uid, $type1, $type2, $isRight)
    {
        //get friend info
        require_once 'Bll/Parking/Friend.php';
        $friendIds = Bll_Parking_Friend::getFriendIds($uid);
        $friendIds = explode(',', $friendIds);

        //get rank count
        require_once 'Dal/Parking/Puser.php';
        $dalParkPuser = new Dal_Parking_Puser();
        $count = $dalParkPuser->getRankingCount($uid, $type1, $friendIds);
        $allOfCount = $count;
        $count = $count-2;
        $rankNm = $allOfCount;
        //move right last
        if ($isRight == 1) {
            $i = $count - 8;
            if ($i > 0) {
                $start = $i;
                $allCount = 8;
                $rankNm = 10;
            }
            else {
                $start = 0;
                $allCount = $count;
                $rankNm = $allOfCount;
            }
        }//move left first
        else {
            $i = $count - 16;
            if ( $i > 0 ) {
                $start = 0;
                $allCount = 16;
            }
            else {
                $start = 0;
                $allCount = $count;
            }
        }
        //get rank info
        $rankInfo = $dalParkPuser->getRankingUser($uid, $friendIds, $type1, $type2, $allCount, 'ASC', $start);
        if ( $rankInfo ) {
            Bll_User::appendPeople($rankInfo, 'uid');
        }

        $rankCount = count($rankInfo);
        $rightCount = $rankCount > 8 ? ($rankCount-8) : 0;

        $countArr = array('rankCount' => count($rankInfo),
                          'rightCount' => $rightCount,
                          'allCount' => $allOfCount);

        $result = array('rankInfo' => $rankInfo,'rankNm' => $rankNm, 'countArr' => $countArr);
        return $result;
    }

    /**
     * get user park info
     *
     * @param string $uid
     * @param integer $request_id
     * @param integer $request_type
     * @return array
     */
    public function getUserPark($uid, $request_id, $request_type=1)
    {
        require_once 'Dal/Parking/Parking.php';
        $dalParkingParking = new Dal_Parking_Parking();

        require_once 'Dal/Parking/Puser.php';
        $dalParkingPuser = new Dal_Parking_Puser();

        //check is nieghbor or not
        if ( $request_id < 0 ) {
            $request_type = 2;
        }
        else {
            $request_type = 1;
        }

        if ($request_type == 1) {
            $parkCurrentCar = $dalParkingParking->getFriendPark($request_id);
            $parkCurrent = $dalParkingPuser->getUserPark($request_id);

            if ( $parkCurrentCar ) {
                Bll_User::appendPeople($parkCurrentCar, 'uid');
            }

            //get location count
            switch ( $parkCurrent['bgtype'] ) {
                case 'A':
                    $parkCurrent['locaCount'] = 3;
                    break;
                case 'B':
                    $parkCurrent['locaCount'] = 4;
                    break;
                case 'C':
                    $parkCurrent['locaCount'] = 5;
                    break;
                case 'D':
                    $parkCurrent['locaCount'] = 6;
                    break;
                case 'E':
                    $parkCurrent['locaCount'] = 7;
                    break;
                case 'F':
                    $parkCurrent['locaCount'] = 8;
                    break;
            }

            if ( $parkCurrent ) {
                Bll_User::appendPerson($parkCurrent, 'uid');
            }
        }
        else {
            $parkCurrentCar = $dalParkingParking->getNeighborPark($uid, $request_id);
            $parkCurrent = $dalParkingPuser->getUserNeighborPark($request_id);

            if ( $parkCurrentCar ) {
                Bll_User::appendPeople($parkCurrentCar, 'uid');
            }
        }

        $response = array('current' => array('user'=>$parkCurrent, 'car'=>$parkCurrentCar));

        return $response;
    }

    /**
     * get user cars info
     *
     * @param string $uid
     * @param array $neighbor
     * @return array
     */
    public function getUserCars($uid, $neighbor=array(), $type=1)
    {
        require_once 'Dal/Parking/Car.php';
        $dalParkCar = new Dal_Parking_Car();
        //get user cars
        $userCars = $dalParkCar->getUserCars($uid);
      //$userFriendBg = $dalPark->getUserFriendBg($uid);

        $cars = array();

        require_once 'Bll/User.php';

        foreach ($userCars as $car) {
            //if car status ==0
            if ( $car['status'] != 1 ) {
                $car['status'] = "廃車状態のため、<br/>整備カードを使うまで使用できません";
                $car['money'] = '廃車';
                $car['temp'] = '0';
                $cars = array_merge($cars,array($car));
            }
            //if parked time != null
            else if ($car['parked_time'] != null) {
                //park at friend
                if ($car['type'] == 1) {
                    require_once 'Dal/Parking/Puser.php';
                    $dalParkPuser = new Dal_Parking_Puser();

                    $parkingUserInfo = Bll_User::getPerson($car['parking_uid']);
                    $parkingInfo = $dalParkPuser->getUserPark($car['parking_uid']);

                    if ( $parkingInfo['free_park'] == $car['location'] ) {
                        $isfree = 1;
                    }
                    else {
                        $isfree = 0;
                    }

                    $result = $this->getUserCarStatus($car['uid'], $car, $parkingUserInfo->getDisplayName(), $parkingInfo['fee'], $isfree, $type, $uid);
                    $car['money'] = "¥".$result['money'];
                    $car['status'] = $result['status'];
                    $car['temp'] = $result['temp'];
                    $cars = array_merge($cars,array($car));
                }
                //park at neighbor
                else {
                    require_once 'Dal/Parking/Puser.php';
                    $dalParkPuser = new Dal_Parking_Puser();
                    $userPark = $dalParkPuser->getUserPark($uid);
                    require_once 'Dal/Parking/Neighbor.php';
                    $dalParkNeighbor = new Dal_Parking_Neighbor();
                    $neighbor = $dalParkNeighbor->getNeighbor($uid,$userPark['neighbor_left'],$userPark['neighbor_right']);

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

                            $cars = array_merge($cars,array($car));
                            break;
                        }
                    }
                }
            }
            else {
                require_once 'Dal/Parking/Nopark.php';
                $dalParkNopark = new Dal_Parking_Nopark();
                $nopark = $dalParkNopark->getNoPark($uid, $car['car_id'], $car['car_color']);

                require_once 'Dal/Parking/Puser.php';
                $dalParkPuser = new Dal_Parking_Puser();
                $userAsset = $dalParkPuser->getAsset($uid);

                $time = floor((time()-$nopark['create_time'])/60);
                $time = $time>1440 ? 1440 : $time;
                
                //$temp = floor($time/50);
                //$money = $temp * 100;
                
                $money = $userAsset < $time*2 ? $userAsset : $time*2;
                $money = number_format($money);
                if ($type == 1) {
                	$car['status'] = "移動中（駐車していません）<br/>ガソリン代：¥$money";
                	$car['money'] = "-¥$money";
                }
                else {
                	$car['status'] = "移動中（駐車していません）";
                	$car['money'] = "¥$money";
                }
                $car['temp'] = '0';
                $cars = array_merge($cars,array($car));
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
     * @param integer $type 1:pc 2:mobile
     * @param string $userID
     * @return string
     */
    public function getUserCarStatus($uid, $car, $nickname, $fee, $isfree, $type, $userID)
    {
        //check is self or not
        $isSelf = $userID == $uid;

        //get money
        $time = floor((time()-$car['parked_time'])/900);
        $time = $time>32 ? 32 : $time;
        $money = $time*$fee*$car['times'];
        $money = number_format($money);

        $temp = $time/4 + 1;
        $temp = floor($temp);

        if ($type == 1) {
            if ($isSelf || $car['type'] == 1) {
                if ($isfree) {
                    $money = '0';
                    $temp = '0';
                    $status = '<a href="javascript:userPark.goUserPark(\'' . $car['parking_uid'] . '\',' . $car['type'] . ');" >' . $nickname . "</a>のパーキング（無料）に駐車中<br/>収入：¥0";
                }
                else {
                    $status = '<a href="javascript:userPark.goUserPark(\'' . $car['parking_uid'] . '\',' . $car['type'] . ');" >' . $nickname . "</a>のパーキングに駐車中<br/>収入：¥$money ";
                }
            }
        }
        else {
            if ($isfree) {
                $money = '0';
                $temp = '0';
                $status = $nickname . "のパーキング（無料）に駐車中";
            }
            else {
                $status = $nickname . "のパーキングに駐車中";
            }
        }

        $result = array('money' => $money, 'status' => $status, 'temp' => $temp);
        return $result;
    }

    /**
     * park
     *
     * @param string $uid
     * @param string $park_uid
     * @param integer $car_id
     * @param string $car_color
     * @param integer $location
     * @param integer $type
     * @return array
     */
    public function parking($uid, $park_uid, $car_id, $car_color, $location, $type)
    {
        $result = array('status'=>1);
        require_once 'Dal/Parking/Puser.php';
        $dalParkPuser = new Dal_Parking_Puser();

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

        require_once 'Dal/Parking/Item.php';
        $dalParkItem = new Dal_Parking_Item(); 
        $loca = 'location'.$location;
        $now = time();
        //check yanki card
        $yanki = $dalParkItem->getYankiInfo($park_uid);
        if ( $now - $yanki[$loca] <= 72*3600 ) {
            return array('status'=>-1);
        }

        $this->_wdb->beginTransaction();
        try {
            //check has bomb
            $bomb = $dalParkItem->getBombInfo($park_uid);
            if ( $bomb[$loca] > 0 ) {
                //check is last car
                $userCarCount = $dalParkCar->getUserCarCount($uid);
                //check car is ad bus
                if ( $carInfo['type'] == 2 ) {
                    return array('status'=>-14);
                }
                if ( $userCarCount > 1 ) {
                    //check evasion card and insurance card
                    if ( ($now-$user['last_evasion_time']) > 48*3600 ) {
                        if ( $user['insurance_card'] < 1 ) {
                            //update user car status
                            $dalParkCar->updateUserCar($uid, $car_id, $car_color, 0);
                            //update user car price
                            $dalParkPuser->updateCarPrice($carInfo['price'], $uid);
                            //update parking uid bomb count
                            $dalParkItem->updateBombCount($park_uid, $loca);

                            $result['status'] = -5;
                            $bombFeed = 1;
                        }
                        else {
                            //update user insurance card count
                            $dalParkItem->updateUserInsuranceCount($uid, -1);

                            $result['status'] = -13;
                            $bombFeed = 2;
                        }
                    }
                    else {
                        //evasion card using
                        return array('status'=>-12);
                    }
                }
                else {
                    //car count = 1
                    return array('status'=>-11);
                }
            }

            if ($result['status'] != -13) {
                //clear last park fee
                if (count($lastParkInfo) > 0) {
                    if ($lastParkInfo[0]['location'] != $lastParkInfo[0]['free_park']) {
                        //update user asset
                        $time = floor((time()-$lastParkInfo[0]['parked_time'])/900);
                        $time = $time>32 ? 32 : $time;

                        $now = time();
                        $lastParkUser = $dalParkPuser->getUserPark($lastParkInfo[0]['parking_uid']);
                        Bll_User::appendPerson($lastParkUser, 'uid');

                        //get money
                        $money = $time*$lastParkInfo[0]['fee']*$carInfo['times'];

                        //check last check card time
                        if ( ($now - $lastParkUser['last_check_time']) <= 24*3600 && ($now-$user['last_evasion_time']) > 48*3600 ) {
                            //use check card
                            $dalParkPuser->updateUserAsset($money, $lastParkInfo[0]['parking_uid'], 2);

                            $result['status'] = $result['status']==-5 ? -7 : -6;
                            $result['lastUserName'] = $lastParkUser['displayName'];
                            $result['money'] = $money;
                            $hasFeed = 2;
                        }
                        else {
                        	$result['checkCard'] = 0;
                        	if ( ($now - $lastParkUser['last_check_time']) <= 24*3600) {
                        		$result['checkCard'] = 1;
                        	}
                        	
                            if ( ($now - $lastParkUser['last_check_time']) <= 24*3600 && ($now-$user['last_evasion_time']) <= 48*3600 ) {
                                $hasOtherFeed = 1;
                            }
                            $dalParkPuser->updateUserAsset($money, $uid, 2);
                            $hasFeed = 1;
                        }
                    }
                    else {
                        $hasFeed = 3;
                    }
                    //delete last parking info
                    $dalParkParking->deleteParkingInfo($lastParkInfo[0]['pid']);

                } //if the car is not parked in user park
                else {
                    require_once 'Dal/Parking/Nopark.php';
                    $dalParkNopark = new Dal_Parking_Nopark();
                    $nopark = $dalParkNopark->getNoPark($uid, $car_id, $car_color);

                    if (!empty($nopark)) {
                        $time = floor((time()-$nopark['create_time'])/60);
                        $time = $time>1440 ? 1440 : $time;

                        $money = $user['asset'] < $time*2 ? $user['asset'] : $time*2;
                        $dalParkPuser->updateUserAsset($money, $uid);
                        $dalParkNopark->deleteNoPark($uid, $car_id, $car_color);

                        $carName = $dalParkCar->getCarName($car_id);
                        $create_time = date('Y-m-d H:i:s');
                        //insert into minifeed
                        $minifeedNopark = array('uid' => $uid,
                                                'template_id' => 15,
                                                'actor' => $uid,
                                                'title' => '{"car_name":"'. $carName . '","time":"' . (ceil($time/60)) . '","money":"' . number_format($money) . '"}',
                                                'icon' => Zend_Registry::get('static') . "/apps/parking/img/icon/gas.gif",
                                                'create_time' => $create_time);

                        require_once 'Dal/Parking/Feed.php';
                        $dalParkFeed = new Dal_Parking_Feed();
                        $dalParkFeed->insertMinifeed($minifeedNopark);
                    }
                }

                //if no bomb ,insert parking
                if ( $result['status'] != -5 && $result['status'] != -7 ) {
                    //insert parking
                    $parkInfo = array('uid' => $uid,
                                      'car_id' => $car_id,
                                      'car_color' => $car_color,
                                      'car_count' => $user['car_count'],
                                      'parking_uid' => $park_uid,
                                      'location' => $location,
                                      'parked_time' => time(),
                                      'type' => $type);
                    $dalParkParking->insertParkingInfo($parkInfo);
                }
            }

            //get car name
            $carName = $dalParkCar->getCarName($car_id);
            $create_time = date('Y-m-d H:i:s');
            //get park user name
            if ( $type == 1 ) {
                $park_userInfo = Bll_User::getPerson($park_uid);
                $nickname = $park_userInfo->getDisplayName();
            }
            else {
                require_once 'Dal/Parking/Neighbor.php';
                $dalParkNeighbor = new Dal_Parking_Neighbor();
                $nickname = $dalParkNeighbor->getNeighborName($park_uid);
            }

            //insert feed
            require_once 'Dal/Parking/Feed.php';
            $dalParkFeed = new Dal_Parking_Feed();
            if ($hasFeed == 1) {
                $minifeed = array('uid' => $uid,
                                  'template_id' => 10,
                                  'actor' => $uid,
                                  'target' => $lastParkInfo[0]['parking_uid'],
                                  'title' => '{"car_name":"'. $carName . '","time":"' . (ceil($time/4)) . '","money":"' . number_format($money) . '","name":"' . $nickname . '"}',
                                  'icon' => Zend_Registry::get('static') . "/apps/parking/img/icon/money.gif",
                                  'create_time' => $create_time);
                $dalParkFeed->insertMinifeed($minifeed);

                if ( $lastParkInfo[0]['type'] == 1 ) {
                    $minifeed['uid'] = $lastParkInfo[0]['parking_uid'];
                    $minifeed['template_id'] = 11;
                    $minifeed['icon'] = Zend_Registry::get('static') . "/apps/parking/img/icon/loss.gif";
                    $dalParkFeed->insertMinifeed($minifeed);
                }

                //insert into newsfeed
                $newsfeed = array('uid' => $uid,
                                  'template_id' => 12,
                                  'actor' => $uid,
                                  'target' => $lastParkInfo[0]['parking_uid'],
                                  'title' => '{"car_name":"'. $carName . '","time":"' . (ceil($time/4)) . '","money":"' . number_format($money) . '","name":"' . $nickname . '"}',
                                  'icon' => Zend_Registry::get('static') . "/apps/parking/img/icon/money.gif",
                                  'create_time' => $create_time);
               $dalParkFeed->insertNewsfeed($newsfeed);

               if ( $lastParkInfo[0]['type'] == 1 ) {
                   $newsfeed['uid'] = $lastParkInfo[0]['parking_uid'];
                   $dalParkFeed->insertNewsfeed($newsfeed);
               }
            }
            else if ( $hasFeed == 2) {
                $minifeed = array('uid' => $uid,
                                  'template_id' => 25,
                                  'actor' => $uid,
                                  'target' => $lastParkInfo[0]['parking_uid'],
                                  'title' => '{"car_name":"'. $carName . '","money":"' . number_format($money) . '"}',
                                  'icon' => Zend_Registry::get('static') . "/apps/parking/img/icon/item.gif",
                                  'create_time' => $create_time);
                $dalParkFeed->insertMinifeed($minifeed);

                $minifeed['template_id'] = 31;
                $minifeed['icon'] = Zend_Registry::get('static') . "/apps/parking/img/icon/money.gif";
                $dalParkFeed->insertNewsfeed($minifeed);

                $minifeed['template_id'] = 30;
                $minifeed['uid'] = $lastParkInfo[0]['parking_uid'];
                $dalParkFeed->insertMinifeed($minifeed);

                $minifeed['template_id'] = 31;
                $dalParkFeed->insertNewsfeed($minifeed);
            }
            else if ( $hasFeed == 3) {
                $minifeed = array('uid' => $uid,
                                  'template_id' => 13,
                                  'actor' => $uid,
                                  'target' => $lastParkInfo[0]['parking_uid'],
                                  'title' => '{"car_name":"'. $carName . '","name":"' . $nickname . '"}',
                                  'icon' => Zend_Registry::get('static') . "/apps/parking/img/icon/free.gif",
                                  'create_time' => $create_time);
                $dalParkFeed->insertMinifeed($minifeed);

                if ( $lastParkInfo[0]['type'] == 1 ) {
                    $minifeed['uid'] = $lastParkInfo[0]['parking_uid'];
                    $minifeed['template_id'] = 14;
                    $dalParkFeed->insertMinifeed($minifeed);
                }
            }

            if ( $hasOtherFeed == 1) {
                $minifeed = array('uid' => $uid,
                                  'template_id' => 37,
                                  'actor' => $uid,
                                  'target' => $lastParkInfo[0]['parking_uid'],
                                  'title' => '{"car_name":"'. $carName . '"}',
                                  'icon' => Zend_Registry::get('static') . "/apps/parking/img/icon/item.gif",
                                  'create_time' => $create_time);
                $dalParkFeed->insertMinifeed($minifeed);

                $minifeed['uid'] = $lastParkInfo[0]['parking_uid'];
                $minifeed['template_id'] = 38;
                $minifeed['icon'] = Zend_Registry::get('static') . "/apps/parking/img/icon/loss.gif";
                $dalParkFeed->insertMinifeed($minifeed);
            }

            //insert bomb feed
            if ( $bombFeed == 1 ) {
                $minifeed = array('uid' => $uid,
                                  'template_id' => 26,
                                  'actor' => $uid,
                                  'target' => $park_uid,
                                  'title' => '{"car_name":"'. $carName . '"}',
                                  'icon' => Zend_Registry::get('static') . "/apps/parking/img/icon/loss.gif",
                                  'create_time' => $create_time);
                $dalParkFeed->insertMinifeed($minifeed);

                $minifeed['template_id'] = 33;
                $minifeed['icon'] = Zend_Registry::get('static') . "/apps/parking/img/icon/car.gif";
                $dalParkFeed->insertNewsfeed($minifeed);

                $minifeed['uid'] = $park_uid;
                $minifeed['template_id'] = 32;
                $dalParkFeed->insertMinifeed($minifeed);

                $minifeed['template_id'] = 33;
                $dalParkFeed->insertNewsfeed($minifeed);
            }
            else if ( $bombFeed == 2 ) {
            	$minifeed = array('uid' => $uid,
                                  'template_id' => 27,
                                  'actor' => $uid,
                                  'target' => $park_uid,
                                  'title' => '{"car_name":"'. $carName . '"}',
                                  'icon' => Zend_Registry::get('static') . "/apps/parking/img/icon/car.gif",
                                  'create_time' => $create_time);
                $dalParkFeed->insertMinifeed($minifeed);
                //$dalParkFeed->insertNewsfeed($minifeed);

                $minifeed['uid'] = $park_uid;
                $minifeed['template_id'] = 34;
                $dalParkFeed->insertMinifeed($minifeed);
                //$dalParkFeed->insertNewsfeed($minifeed);
            }

            $this->_wdb->commit();

            $userPark = $dalParkPuser->getUserPark($uid);
            $result['asset'] = $userPark['asset'];
            $result['car_type'] = $carInfo['type'];
            $result['ad_url'] = $carInfo['ad_url'];
            
            if (!empty($nopark)) { 
            	$money = -$money;
            }
            $result['money'] = number_format($money);
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return array('status'=>-1);
        }
        return $result;
    }

    /**
     * stick
     *
     * @param integer $uid
     * @param integer $location
     * @return boolean
     */
    public function stick($uid, $location)
    {
        //$result = -1;
        $result = array('status'=>-1);

        require_once 'Dal/Parking/Parking.php';
        $dalParkParking = new Dal_Parking_Parking();

        //check the location is empty
        $parkInfo = $dalParkParking->getUserParkInfoByLocation($uid, $location);
        if (empty($parkInfo)) {
            return $result;
        }

        //check is friend
        $isFriend = Bll_Parking_Friend::isFriend($uid, $parkInfo['uid']);
        if (!$isFriend) {
            return $result;
        }

        //check time
        if (time() - $parkInfo['parked_time'] < 3600) {
            $result['status'] = -2;
            //$result = -2;
            return $result;
        }

        //stick start
        $this->_wdb->beginTransaction();

        try {
            require_once 'Dal/Parking/Puser.php';
            $dalParkPuser = new Dal_Parking_Puser();
            $userPark = $dalParkPuser->getUserPark($uid);

            //delete parking info
            $dalParkParking->deleteParkingInfo($parkInfo['pid']);

            //insert into no park
            require_once 'Dal/Parking/Nopark.php';
            $dalParkNopark = new Dal_Parking_Nopark();
            $nopark = array('uid' => $parkInfo['uid'],
                            'car_id' => $parkInfo['car_id'],
                            'car_color' => $parkInfo['car_color'],
                            'create_time' => time());
            $dalParkNopark->insertNoPark($nopark);

            //get car info
            require_once 'Dal/Parking/Car.php';
            $dalParkCar = new Dal_Parking_Car();
            $carInfo = $dalParkCar->getParkingCarInfo($parkInfo['car_id']);

            //update user asset
            if ($location != $userPark['free_park']) {
                $time = floor((time()-$parkInfo['parked_time'])/900);
                $time = $time>32 ? 32 : $time;
                $money = $time*$userPark['fee']*$carInfo['times']*1.2;

                $dalParkPuser->updateUserAsset($money, $uid, 2);
            }


            $create_time = date('Y-m-d H:i:s');
            //insert into minifeed

            require_once 'Dal/Parking/Feed.php';
            $dalParkFeed = new Dal_Parking_Feed();
            $minifeed = array('uid' => $uid,
                              'template_id' => 1,
                              'actor' => $uid,
                              'target' => $parkInfo['uid'],
                              'title' => '{"car_name":"'. $carInfo['name'] . '","money":"' . number_format($money) . '"}',
                              'icon' => Zend_Registry::get('static') . "/apps/parking/img/icon/money.gif",
                              'create_time' => $create_time);
            $dalParkFeed->insertMinifeed($minifeed);

            //insert into newsfeed
            $minifeed['uid'] = $uid;
            $minifeed['template_id'] = 3;
            $dalParkFeed->insertNewsfeed($minifeed);

            $minifeed['uid'] = $parkInfo['uid'];
            $dalParkFeed->insertNewsfeed($minifeed);

            //insert into minifeed parking_uid
            $minifeed['uid'] = $parkInfo['uid'];
            $minifeed['template_id'] = 2;
            $minifeed['icon'] = Zend_Registry::get('static') . "/apps/parking/img/icon/loss.gif";
            $dalParkFeed->insertMinifeed($minifeed);

            $this->_wdb->commit();
            
            $result['status'] = 1;
            $result['money'] = $money;            
            
            require_once 'Bll/Parking/Activity.php';
            $result['activity'] = Bll_Parking_Activity::getActivity($uid, $parkInfo['uid'], array('car_name'=>$carInfo['name']), 1);
            //$result = 1;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
        }
        return $result;
    }

    /**
     * report
     *
     * @param string $uid
     * @param string $park_uid  park's host uid
     * @param string $report_uid  be report's uid
     * @param integer $car_id
     * @param string $car_color
     * @param integer $location
     * @return array
     */
    public function report($uid, $park_uid, $report_uid, $car_id, $car_color, $location, $isAnonymous)
    {
        $result = array('status'=>'0');

        require_once 'Dal/Parking/Parking.php';
        $dalParkParking = new Dal_Parking_Parking();

        //check is friend
        $isFriend = Bll_Parking_Friend::isFriend($uid, $report_uid);
        if (!$isFriend) {
            return $result;
        }

        //check location is empty
        $parkInfo = $dalParkParking->getUserParkInfoByLocation($park_uid, $location);
        if (empty($parkInfo)) {
            return $result;
        }

        if (!($parkInfo['uid'] == $report_uid && $parkInfo['car_id'] == $car_id && $parkInfo['car_color'] == $car_color)) {
            return $result;
        }

        //report start
        $this->_wdb->beginTransaction();

        try {
            require_once 'Dal/Parking/Report.php';
            $dalParkReport = new Dal_Parking_Report();
            //check is report
            $isReport = $dalParkReport->isReport($parkInfo['pid'], $uid);

            //insert report
            if (!$isReport) {
                $report = array('uid' => $uid,
                                'pid' => $parkInfo['pid'],
                                'anonymous' => 0,
                                'create_time' => time());

                $report_user = Bll_user::getPerson($report_uid);

                //require_once 'Dal/Parking/Puser.php';
                //$dalParkPuser = new Dal_Parking_Puser();

                //$user = $dalParkPuser->getUserPark($uid);
                $result['message'] = '通報しました。1～2時間以内に警察がやってきます。';

                require_once 'Dal/Parking/Card.php';
                $dalParkCard = new Dal_Parking_Card();
                //get card count
                $anonymousCardCount = $dalParkCard->getUserCardCoutByCid(2, $uid);

                if ( $isAnonymous == 1 && $anonymousCardCount > 0 ) {
                    $report['anonymous'] = 1;

                    //update card count
                    $dalParkCard->updateUserCardCoutByCid(2, $uid, -1);

                    $result['reportCount'] = $anonymousCardCount;
                    $result['message'] = $result['message'] . '<br/>ヒミツ通報カードを使ったので、あなたの通報はバレません。';
                }
                //insert report info
                $dalParkReport->insertReport($report);

            }
            else {
                $result['message'] = 'すでに通報済です。';
            }

            $this->_wdb->commit();
            $result['status'] = "1";
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
        }
        return $result;
    }

    /**
     * check report
     *
     * @param string $uid
     * @param string $park_uid  park's host uid
     * @param string $report_uid  be report's uid
     * @param integer $car_id
     * @param string $car_color
     * @param integer $location
     * @return array
     */
    public function checkReport($uid, $park_uid, $report_uid, $car_id, $car_color, $location)
    {
        $result = array('status'=>'0');

        require_once 'Dal/Parking/Parking.php';
        $dalParkParking = new Dal_Parking_Parking();

        //check is friend
        $isFriend = Bll_Parking_Friend::isFriend($uid, $report_uid);
        if (!$isFriend) {
            return $result;
        }

        //check location is empty
        $parkInfo = $dalParkParking->getUserParkInfoByLocation($park_uid, $location);
        if (empty($parkInfo)) {
            return $result;
        }

        if (!($parkInfo['uid'] == $report_uid && $parkInfo['car_id'] == $car_id && $parkInfo['car_color'] == $car_color)) {
            return $result;
        }

        require_once 'Dal/Parking/Report.php';
        $dalParkReport = new Dal_Parking_Report();
        //check is report
        $isReport = $dalParkReport->isReport($parkInfo['pid'], $uid);

        if ($isReport) {
            $result['message'] = 'すでに通報済です。';
            $result['status'] = "1";
        }
        else {
            $result['status'] = "2";
        }

        return $result;
    }

    /**
     * today frist login,send card
     *
     * @param string $uid
     * @return array
     */
    public function isTodayFirstLogin($uid)
    {
        require_once 'Dal/Parking/Puser.php';
        $dalParkPuser = new Dal_Parking_Puser();
        //get last login time
        $lastLoginTime = $dalParkPuser->getLastLoginTime($uid);
        
        require_once 'Dal/Parking/Card.php';
        $dalParkCard = new Dal_Parking_Card();
        
        //check is today first login
        $todayDate = date("Y-m-d");
        $todayTime = strtotime($todayDate);

        if ($lastLoginTime < $todayTime) {

            $userPark = $dalParkPuser->getUserPark($uid);

            require_once 'Dal/Parking/Car.php';
            $dalParkCar = new Dal_Parking_Car();
            //check has ad bus and car count
            $userAdBus = $dalParkCar->getUserAdBus($uid);
            if ( $userPark['car_count'] < 8 && !$userAdBus ) {
                $cid = 15;
            }
            else {
                $hasFreeCard = $dalParkCard->getUserCardCoutByCid(1, $uid);
    
                $i = rand(1, 100);
    
                //get card id
                switch ($i) {
                    case $i<3 :
                        if ( $userPark['free_park'] == '0' || $hasFreeCard == '1' ) {
                            $cid = 12;
                        }
                        else {
                            $cid = 1;
                        }
                        break;
                    case 2<$i && $i<8 :
                        $cid = 2;
                        break;
                    case 7<$i && $i<10 :
                        $cid = 3;
                        break;
                    case 9<$i && $i<13 :
                        $cid = 4;
                        break;
                    case 12<$i && $i<18 :
                        $cid = 5;
                        break;
                    case 17<$i && $i<23 :
                        $cid = 6;
                        break;
                    case 22<$i && $i<28 :
                        $cid = 7;
                        break;
                    case 27<$i && $i<33 :
                        $cid = 8;
                        break;
                    case 32<$i && $i<38 :
                        $cid = 9;
                        break;
                    case 37<$i && $i<40 :
                        $cid = 10;
                        break;
                    case 39<$i && $i<45 :
                        $cid = 11;
                        break;
                    case 44<$i && $i<95 :
                        $cid = 12;
                        break;
                    case 94<$i && $i<100 :
                        $cid = 13;
                        break;
                    case 99<$i && $i<101 :
                        $cid = 14;
                        break;
                }
            }

            $this->_wdb->beginTransaction();

            try {
                if ( $cid < 12 ) {
                    //add card count
                    $dalParkCard->updateUserCardCoutByCid($cid, $uid, '1');
                }
                else if ( $cid == 15 ) {
                    $adBus = $dalParkCar->getParkingCarInfo(21);
                    //update user asset
                    $dalParkPuser->updateUserAsset($adBus['price'], $uid, 2);

                    //insert into user car
                    $carInfo = array('uid' => $uid,
                                     'car_id' => $adBus['cid'],
                                     'car_color' => $adBus['color'],
                                     'create_time' => time());
                    $dalParkCar->insertUserCars($carInfo);
        
                    //update user car count and price
                    $dalParkCar->updateUserCarWhenBuyAndChange($uid);
                }
                else {
                    switch ( $cid ) {
                        case 12 :
                            $money = "5000";
                            break;
                        case 13 :
                            $money = "50000";
                            break;
                        case 14 :
                            $money = "1000000";
                            break;
                    }
                    //update user asset
                    $dalParkPuser->updateUserAsset($money, $uid, 2);
                }

                $result = array('cid'=>$cid);

                $this->_wdb->commit();
            }
            catch (Exception $e) {
                $this->_wdb->rollBack();
                return false;
            }

        }

        return $result;
    }

    /**
     * append neighbor top rank
     *
     * @param array $rank
     * @param integer $type
     * @return array
     */
    public function appendNeighborRank($rank, $type, &$allRank)
    {
    	if ($type == 1) {
    		$neighbor1 = array('uid'=>-1,'ass'=>'500000','online'=>0,'type'=>2, 'displayName'=>'駐車太郎', 'thumbnailUrl'=> Zend_Registry::get('static') . '/apps/parking/img/neighbor/taro.gif');
    		$neighbor2 = array('uid'=>-2,'ass'=>'700000','online'=>0,'type'=>2, 'displayName'=>'駐車花子', 'thumbnailUrl'=> Zend_Registry::get('static') . '/apps/parking/img/neighbor/hanako.gif');

    		$rank = $this->msort(array_merge(array($neighbor1),array($neighbor2),$rank));

    		$allRank = array();
    		while (count($rank) > 2){
    			$allRank = array_merge($allRank, array($rank[0]));
    			array_shift($rank);
    		}

    		$topRank = array_merge(array($rank[0]), array($rank[1]));
    	}
    	else {
    		$neighbor1 = array('uid'=>-1,'ass'=>'400000','online'=>0,'type'=>2, 'displayName'=>'駐車太郎', 'thumbnailUrl'=> Zend_Registry::get('static') . '/apps/parking/img/neighbor/taro.gif');
    		$neighbor2 = array('uid'=>-2,'ass'=>'500000','online'=>0,'type'=>2, 'displayName'=>'駐車花子', 'thumbnailUrl'=> Zend_Registry::get('static') . '/apps/parking/img/neighbor/hanako.gif');

    		$rank = $this->msort(array_merge(array($neighbor1),array($neighbor2),$rank));

    		$allRank = array();
    		while (count($rank) > 2){
    			$allRank = array_merge($allRank, array($rank[0]));
    			array_shift($rank);
    		}

    		$topRank = array_merge(array($rank[0]),array($rank[1]));
    	}

        return $topRank;
    }

    /**
     * sort the mix array
     *
     * @param array $array
     * @param string $id
     * @return array
     */
    public function msort($array, $id="ass")
    {
    	$temp_array = array();
        while(count($array)>0) {
            $lowest_id = 0;
            $index=0;
            foreach ($array as $item) {
                if (isset($item[$id]) && $array[$lowest_id][$id]) {
                    if ($item[$id]<$array[$lowest_id][$id]) {
                        $lowest_id = $index;
                    }
                }
                $index++;
            }
            $temp_array[] = $array[$lowest_id];
            $array = array_merge(array_slice($array, 0,$lowest_id), array_slice($array, $lowest_id+1));
        }
        return $temp_array;
    }

    /**
     * get minifeed
     *
     * @param integer $uid
     * @return array
     */
    public function getMinifeed($uid)
    {
        require_once 'Dal/Parking/Feed.php';
        $dalParkFeed = new Dal_Parking_Feed();
        $feed = $dalParkFeed->getMinifeed($uid, 1, 8);

        return $this->buildFeed($feed);
    }

    /**
     * get newsfeed
     *
     * @param integer $uid
     * @return array
     */
    public function getNewsfeed($uid)
    {
        require_once 'Bll/Friend.php';
        $friendList  = Bll_Parking_Friend::getFriendIds($uid);
        $aryFriendIds = explode(',', $friendList);

        require_once 'Dal/Parking/Feed.php';
        $dalParkFeed = new Dal_Parking_Feed();
        $feed = $dalParkFeed->getNewsfeed($uid, $aryFriendIds, 1, 8);

        return $this->buildFeed($feed);
    }

    /**
     * build feed
     *
     * @param array $feed
     * @return array
     */
    public function buildFeed($feed)
    {
        $lnml_env = array('is_mobile'=>false);

        require_once 'Zend/Json.php';

        for($i = 0; $i < count($feed); $i++) {
            $feed_title_template = self::getFeedTemplateTitle(0, $feed[$i]['template_id'], $lnml_env['is_mobile']);

            $title_lnml = $this->buildTemplateLnml($feed[$i]['actor'], $feed[$i]['target'], $feed_title_template, Zend_Json::decode($feed[$i]['title']));

            if ($title_lnml) {
                $feed[$i]['title'] = $title_lnml;
            }
            else {
                $feed[$i]['title'] = '';
            }
        }

        return $feed;
    }

    /**
     * build template lnml
     *
     * @param integer $user
     * @param integer $target
     * @param string $template
     * @param array $json_array
     * @return string
     */
    public function buildTemplateLnml($user, $target, $template, $json_array)
    {
        if ($json_array == null) {
            $json_array = array();
        }

        if (! is_array($json_array)) {
            return false;
        }

        require_once 'Bll/User.php';
        $actor = Bll_User::getPerson($user);

        if (empty($actor)) {
        	$actor_name = "____";
        }
        else {
        	$actor_name = $actor->getDisplayName();
        }

        $json_array['actor'] = '<a href="javascript:userPark.goUserPark({*}' . $user . '{*},1);" >' . $actor_name . '</a>';
        if ($target) {
            if ($target < 0) {
                require_once 'Dal/Parking/Neighbor.php';
                $dalPark = new Dal_Parking_Neighbor();
                $json_array['target'] = $dalPark->getNeighborName($target);
            }
            else {
                $targ = Bll_User::getPerson($target);

		        if (empty($targ)) {
		        	$target_name = "____";
		        }
		        else {
		        	$target_name = $targ->getDisplayName();
		        }
                $json_array['target'] = '<a href="javascript:userPark.goUserPark({*}' . $target . '{*},1);" >' . $target_name . '</a>';
            }
        }

        $keys = array();
        $values = array();

        foreach ($json_array as $k => $v) {
            $keys[] = '{*' . $k . '*}';
            $values [] = $v;
        }

        return str_replace($keys, $values, $template);
    }

    /**
     * get some friend info
     *
     * @param string $uid
     * @param array $neighbor
     * @return array
     */
    public function getArrFriend($uid, $neighbor)
    {
        $friendIds = Bll_Parking_Friend::getFriendIds($uid);
        $arrFriendId = explode(',', $friendIds);

        require_once 'Bll/User.php';
        $friendList = Bll_User::getPeople($arrFriendId);
        $friendListCnt = $friendList->count();
        $friendInfo = array();
        for ($i = 0; $i < $friendListCnt; $i++) {
            $friendInfo[$i]['uid'] = $friendList[$i]->getId();
            $friendInfo[$i]['displayName'] = str_replace('&','&amp;',$friendList[$i]->getDisplayName());
            $friendInfo[$i]['type'] = 1;
        }
        $userFriendList = $friendInfo;

        if ( $i < 2 ) {
            $friendInfo[$i]['uid'] = $neighbor['0']['uid'];
            $friendInfo[$i]['displayName'] = $neighbor['0']['nickname'];
            $friendInfo[$i]['type'] = $neighbor['0']['2'];
            $friendInfo[$i+1]['uid'] = $neighbor['1']['uid'];
            $friendInfo[$i+1]['displayName'] = $neighbor['1']['nickname'];
            $friendInfo[$i+1]['type'] = $neighbor['1']['2'];
        }
        $allFriendList = $friendInfo;

        $result = array('friendIds' => $friendIds, 'arrFriendId' => $arrFriendId, 'userFriendList' => $userFriendList, 'allFriendList' => $allFriendList);

        return $result;
    }

    /**
     * get feed title by template
     *
     * @param integer $app_id
     * @param integer $template_id
     * @param boolean $is_mobile
     * @return array
     */
    public function getFeedTemplateTitle($app_id, $template_id, $is_mobile = false)
    {
        $template_info = $this->getFeedTemplateInfo($app_id, $template_id);

        if ($template_info) {
            if (! $is_mobile) {
                return $template_info['title'];
            }
            else {
                return $template_info['m_title'];
            }
        }

        return null;
    }

    /**
     * Get feed template whole information
     *
     * @param int $app_id
     * @param int $template_id
     * @return array
     */
    public function getFeedTemplateInfo($app_id, $template_id)
    {
        $key = $app_id . ',' . $template_id;

        if (Zend_Registry::isRegistered('FEED_TEMPLATE_INFO')) {
            $FEED_TEMPLATE_INFO = Zend_Registry::get('FEED_TEMPLATE_INFO');

            if (isset($FEED_TEMPLATE_INFO[$key])) {
                return $FEED_TEMPLATE_INFO[$key];
            }
        }
        else {
            $FEED_TEMPLATE_INFO = array();
        }

        $template_info = Bll_Cache_FeedTemplate::getInfo($app_id, $template_id);

        if ($template_info) {
            $FEED_TEMPLATE_INFO[$key] = $template_info;

             Zend_Registry::set('FEED_TEMPLATE_INFO', $FEED_TEMPLATE_INFO);

             return $template_info;
        }

        return null;
    }
}
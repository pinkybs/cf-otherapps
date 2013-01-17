<?php

require_once 'Mbll/Abstract.php';

/**
 * parking flash logic's Operation
 *
 * @package    Mbll
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/05/19    Huch
 */
class Mbll_Parking_Flash extends Mbll_Abstract
{
    /**
    * get flash data
    *
    * @param string $uid
    * @param string $pid
    * @return array
    */
    public function getFlashData($uid, $pid, $app_id)
    {
        $flashParam = array();
        
        $flashParam['baseUrl'] = Zend_Registry::get('host') . '/mobile/parking/';
        
        $mixiUrl = 'http://ma.mixi.net/' . $app_id . '/';
        $flashParam['mixiUrl'] = $mixiUrl;
        
        $flashParam['uid'] = $uid;

        require_once 'Dal/Parking/Puser.php';
        $dalParkPuser = new Dal_Parking_Puser();
        $userPark = $dalParkPuser->getUserPark($uid);
        $flashParam['money'] = number_format($userPark['asset']);

        $this->_getLeftAndRightFriend($flashParam,$uid,$pid);

        $flashParam['pid'] = $pid;

        //is not neighbor
        if (!is_numeric($pid) || $pid > 0) {
            require_once 'Mdal/Parking/Puser.php';
            $mdalPark = new Mdal_Parking_Puser();
            $ownerParkInfo = $mdalPark->getUserParkInfo($pid);

            require_once 'Bll/User.php';
            $owner = Bll_User::getPerson($pid);
            $flashParam['owner'] = $owner->getDisplayName();
        }
        else {
            require_once 'Dal/Parking/Puser.php';
            $dalParkingPuser = new Dal_Parking_Puser();
            $ownerParkInfo = $dalParkingPuser->getUserNeighborPark($pid);

            $flashParam['owner'] = $ownerParkInfo['displayName'];
        }

        $flashParam['price'] = number_format($ownerParkInfo['fee']);
        $flashParam['max'] = $ownerParkInfo['locaCount'];

        //get rand number.
        $flashParam['rand'] = rand();

        $flashParam = array_merge($flashParam,$this->getUserPark($uid,$ownerParkInfo));

        return $flashParam;
    }

    /**
     * get user park
     *
     * @param integer $uid
     * @param array $puser
     * @return array
     */
    public function getUserPark($uid, $puser)
    {
        $result = array();

        require_once 'Dal/Parking/Parking.php';
        $dalParkingParking = new Dal_Parking_Parking();

        //not neighbor
        if (!is_numeric($puser['uid']) || $puser['uid'] > 0) {
            $parkingCar = $dalParkingParking->getFriendPark($puser['uid']);

            if (!empty($parkingCar)) {
                Bll_User::appendPeople($parkingCar);
            }

            //if park is self
            if ($uid == $puser['uid']) {
                for ($i = 1; $i <= $puser['locaCount']; $i++) {
                    $result['btn' . $i] = 6;
                    $result['brd' . $i] = 1;
                }

                //check bmob
                for ($i = 1; $i < 9; $i++) {
                    if ($puser['bomb' . $i] == 1) {
                        $result['car' . $i] = 9;
                    }
                }

                //check yankee
                for ($i = 1; $i < 9; $i++) {
                    if ($puser['yankee' . $i] > time() - 72*3600) {
                        $result['car' . $i] = 0;
                    }
                }

                //check parking car
                foreach ($parkingCar as $item) {
                    $result['car' . $item['location']] = $item['location'];

                    $time = floor((time() - $item['parked_time']) / 900);

                    if ($time > 32) {
                        $result['mtr' . $item['location']] = 9;
                        $time = 32;
                    }
                    else  {
                        $result['mtr' . $item['location']] = floor($time/4);
                    }

                    //get money
                    $result['price' . $item['location']] = number_format($time * $puser['fee'] * $item['times']);

                    $result['user' . $item['location']] = $item['displayName'];
                    $result['btn' . $item['location']] = 2;

                    if ($puser['free_park'] == $item['location']) {
                        $result['btn' . $puser['free_park']] = 3;
                    }
                }

                if ($puser['free_park'] != 0) {
                    $result['mtr' . $puser['free_park']] = 0;
                    $result['brd' . $puser['free_park']] = 0;
                }
            }
            //if park is friend
            else if (Bll_Friend::isFriend($uid, $puser['uid'])) {
                for ($i = 1; $i <= $puser['locaCount']; $i++) {
                    $result['btn' . $i] = 0;
                    $result['brd' . $i] = 1;
                }

                //check self have トラップ回避カード
                require_once 'Mdal/Parking/Puser.php';
                $mdalParking = new Mdal_Parking_Puser();
                $lastEvasionTime = $mdalParking->getEvasionTime($uid);

                //check bmob
                if ( (time() - $lastEvasionTime) < 48*3600 ) {
                    for ($i = 1; $i < 9; $i++) {
                        if ($puser['bomb' . $i] == 1) {
                            $result['car' . $i] = 9;
                            $result['btn' . $i] = 1;
                        }
                    }
                }
                
                //check yankee
                for ($i = 1; $i < 9; $i++) {
                    if ($puser['yankee' . $i] > time() - 72*3600) {
                        $result['car' . $i] = 0;
                        $result['btn' . $i] = 1;
                    }
                }

                //check parking car
                foreach ($parkingCar as $item) {
                    $result['car' . $item['location']] = $item['location'];

                    $time = floor((time() - $item['parked_time']) / 900);

                    if ($time > 32) {
                        $result['mtr' . $item['location']] = 9;
                        $time = 32;
                    }
                    else  {
                        $result['mtr' . $item['location']] = floor($time/4);
                    }

                    //get money price btn
                    $result['price' . $item['location']] = number_format($time * $puser['fee'] * $item['times']);

                    $result['user' . $item['location']] = $item['displayName'];
                    $result['btn' . $item['location']] = Bll_Friend::isFriend($uid, $item['uid']) ? 4 : 5;

                    if ($uid == $item['uid']) {
                        $result['btn' . $item['location']] = 8;
                    }

                    if ($puser['free_park'] == $item['location']) {
                        $result['btn' . $item['location']] = 7;
                    }
                }

                if ($puser['free_park'] != 0) {
                    $result['mtr' . $puser['free_park']] = 0;
                    $result['brd' . $puser['free_park']] = 0;
                }
            }
            //other
            else {
                for ($i = 1; $i <= $puser['locaCount']; $i++) {
                    $result['btn' . $i] = 1;
                    $result['brd' . $i] = 1;
                }

                //check yankee
                for ($i = 1; $i < 9; $i++) {
                    if ($puser['yankee' . $i] > time() - 72*3600) {
                        $result['car' . $i] = 0;
                    }
                }

                //check parking car
                foreach ($parkingCar as $item) {
                    $result['car' . $item['location']] = $item['location'];

                    $time = floor((time() - $item['parked_time']) / 900);

                    if ($time > 32) {
                        $result['mtr' . $item['location']] = 9;
                        $time = 32;
                    }
                    else  {
                        $result['mtr' . $item['location']] = floor($time/4);
                    }

                    //get money price btn
                    $result['price' . $item['location']] = number_format($time * $puser['fee'] * $item['times']);
                    $result['user' . $item['location']] = $item['displayName'];
                    $result['btn' . $item['location']] = Bll_Friend::isFriend($uid, $item['uid']) ? 4 : 5;

                    if ($puser['free_park'] == $item['location']) {
                        $result['btn' . $item['location']] = 7;
                    }
                }

                if ($puser['free_park'] != 0) {
                    $result['mtr' . $puser['free_park']] = 0;
                    $result['brd' . $puser['free_park']] = 0;
                }
            }
        }
        //is neighbor
        else {
            $user = Bll_User::getPerson($uid);
            $username = $user->getDisplayName();
            $parkingCar = $dalParkingParking->getNeighborPark($uid, $puser['uid']);

            for ($i = 1; $i <= $puser['locaCount']; $i++) {
                $result['btn' . $i] = 0;
                $result['brd' . $i] = 1;
            }

            foreach ($parkingCar as $item) {
                if ($puser['free_park'] == $item['location']) {
                    $result['car' . $item['location']] = $item['location'];

                    $result['mtr' . $puser['free_park']] = 0;
                    $result['brd' . $puser['free_park']] = 0;
                    $result['price' . $item['location']] = '無料駐車場';
                }
                else {
                    $result['car' . $item['location']] = $item['location'];

                    $time = floor((time() - $item['parked_time']) / 900);

                    if ($time > 32) {
                        $result['mtr' . $item['location']] = 9;
                        $time = 32;
                    }
                    else  {
                        $result['mtr' . $item['location']] = floor($time/4);
                    }

                    //get money price btn
                    $result['price' . $item['location']] = number_format($time * $puser['fee'] * $item['times']);
                }

                $result['user' . $item['location']] = $username;
                $result['btn' . $item['location']] = 8 ;
            }

            if ($puser['free_park'] != 0) {
                $result['mtr' . $puser['free_park']] = 0;
                $result['brd' . $puser['free_park']] = 0;
            }
        }

        return $result;
    }

    /**
    * get user current park left and right user id
    *
    * @param array $flash
    * @param string $uid
    * @param string $fid
    */
    public function _getLeftAndRightFriend(&$flash,$uid,$fid)
    {
        require_once 'Bll/Parking/Friend.php';
        $friends = Bll_Parking_Friend::getFriends($uid);

        if (!empty($friends)) {
            //add self
            $arrFriendId = array_merge(array($uid),$friends);
        }
        else {
            $arrFriendId = array($uid);
        }

        $friendListCnt = count($arrFriendId);

        if ($friendListCnt < 3 ) {
            $arrFriendId[] = -1;
            $arrFriendId[] = -2;
        }

        $key = array_search($fid,$arrFriendId);

        if ($key === false) {
            $flash['r'] = $arrFriendId[0];
            $flash['l'] = $arrFriendId[count($arrFriendId) - 1];
        }
        else {
            if ($key == 0) {
                $flash['l'] = $arrFriendId[count($arrFriendId) - 1];
                $flash['r'] = $arrFriendId[1];
            }
            else if ($key == count($arrFriendId) - 1) {
                $flash['l'] = $arrFriendId[$key - 1];
                $flash['r'] = $arrFriendId[0];
            }
            else {
                $flash['l'] = $arrFriendId[$key - 1];
                $flash['r'] = $arrFriendId[$key + 1];
            }
        }
    }
}
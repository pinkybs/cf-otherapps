<?php

require_once 'Mbll/Abstract.php';

/**
 * parking flash logic's Operation
 *
 * @package    Mbll
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/05/19    Huch
 */
class Mbll_Ship_Flash extends Mbll_Abstract
{
    private $_locationCount = 4;
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
        
        $flashParam['baseUrl'] = urlencode(Zend_Registry::get('host') . '/mobile/ship/');
        $flashParam['neighborUrl'] = Zend_Registry::get('host') . '/mobile/ship/start/opensocial_app_id/'.$app_id.'/opensocial_owner_id/'.$uid.'/rand/'.time().'/CF_parkingUid/';
        
        $mixiUrl = 'http://ma.mixi.net/' . $app_id . '/';
        $flashParam['mixiUrl'] = $mixiUrl;
        
        $flashParam['uid'] = $uid;
        
        require_once 'Mdal/Ship/User.php';
        $mdalUser = Mdal_Ship_User::getDefaultInstance();
        $userPark = $mdalUser->getUserPark($uid);
        
        $flashParam['money'] = number_format($userPark['asset']);

        $this->_getLeftAndRightFriend($flashParam,$uid,$pid);
        
        $flashParam['neighborUrlR'] = $flashParam['neighborUrl'] . $flashParam['r'];
        $flashParam['neighborUrlL'] = $flashParam['neighborUrl'] . $flashParam['l'];
        
        $flashParam['pid'] = $pid;

        //is not neighbor
        if (!is_numeric($pid) || $pid > 0) {
            $ownerParkInfo = $mdalUser->getUserPark($pid);
            
            require_once 'Bll/User.php';
            $owner = Bll_User::getPerson($pid);
            $flashParam['owner'] = htmlspecialchars($owner->getUnescapeDisplayName(), ENT_QUOTES, 'UTF-8');
        }
        else {
            $ownerParkInfo = $mdalUser->getUserNeighborPark($pid);

            $flashParam['owner'] = $ownerParkInfo['displayName'];
        }

        $flashParam['price'] = number_format($ownerParkInfo['fee']);
        
        //now fixed 4
        $flashParam['max'] = $this->_locationCount;

        //get rand number.
        $flashParam['rand'] = time();
        
        $flashParam = array_merge($flashParam,$this->getUserPark($uid,$ownerParkInfo));
        return $flashParam;
    }
    
   /**
    * get top flash data
    *
    * @param string $uid
    * @param string $pid
    * @return array
    */
    public function getTopFlashData($uid, $pid)
    {
        $flashParam = array();
        
        require_once 'Mdal/Ship/User.php';
        $mdalUser = Mdal_Ship_User::getDefaultInstance();
        
        //is not neighbor
        if (!is_numeric($pid) || $pid > 0) {
            $ownerParkInfo = $mdalUser->getUserPark($pid);
        }
        else {
            $ownerParkInfo = $mdalUser->getUserNeighborPark($pid);
        }

        $flashParam['price'] = number_format($ownerParkInfo['fee']);
        
        //now fixed 4
        $flashParam['max'] = $this->_locationCount;
        
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
        
        require_once 'Mdal/Ship/Ship.php';
        $mdalShip = Mdal_Ship_Ship::getDefaultInstance();
        
        //not neighbor
        if (!is_numeric($puser['uid']) || $puser['uid'] > 0) {
            $parkingShip = $mdalShip->getShipDetailInfo($uid, $puser['uid']);

            if (!empty($parkingShip)) {
                Bll_User::appendPeople($parkingShip);
            }
            
            //if park is self
            if ($uid == $puser['uid']) {
                for ($i = 1; $i <= $this->_locationCount; $i++) {
                    $result['btn' . $i] = 3;
                    $result['num' . $i] = 0;
                }

                //check bmob
                for ($i = 1; $i <= $this->_locationCount; $i++) {
                    if ($puser['bomb' . $i] == 1) {
                        $result['trap' . $i] = true;
                    }
                }                
                
                //check parking car
                foreach ($parkingShip as $item) {
                    $time = floor((time() - $item['parked_time']) / 900);

                    if ($time > 32) {
                        $result['mtr' . $item['location']] = 9;
                        $time = 32;
                    }
                    else  {
                        $result['mtr' . $item['location']] = floor($time/4);
                    }

                    if ($time > 3) {
                    	$result['btn' . $item['location']] = 2;
                    }
                    else {
                    	$result['btn' . $item['location']] = 3;
                    }
                    
                    //get money
                    $result['price' . $item['location']] = number_format($time * $puser['fee'] * $item['times']);
                    $result['user' . $item['location']] = htmlspecialchars($item['unescapeDisplayName'], ENT_QUOTES, 'UTF-8');
                    
                    $result['num' . $item['location']] = $item['location'];
                }
            }
            //if park is friend
            else if (Bll_Friend::isFriend($uid, $puser['uid'])) {
                for ($i = 1; $i <= $this->_locationCount; $i++) {
                    $result['btn' . $i] = 0;
                    $result['num' . $i] = 0;
                }                
                
                //check parking ship
                foreach ($parkingShip as $item) {
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

                    $result['user' . $item['location']] = htmlspecialchars($item['unescapeDisplayName'], ENT_QUOTES, 'UTF-8');
                    $result['btn' . $item['location']] = Bll_Friend::isFriend($uid, $item['uid']) ? 4 : 5;

                    if ($uid == $item['uid']) {
                        $result['btn' . $item['location']] = 5;
                    }
                    
                    $result['num' . $item['location']] = $item['location'];
                }
            }
            //other
            else {
                for ($i = 1; $i <= $this->_locationCount; $i++) {
                    $result['btn' . $i] = 2;
                    $result['num' . $i] = 0;
                }
                
                //check parking ship
                foreach ($parkingShip as $item) {
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
                    $result['user' . $item['location']] = htmlspecialchars($item['unescapeDisplayName'], ENT_QUOTES, 'UTF-8');
                    $result['btn' . $item['location']] = Bll_Friend::isFriend($uid, $item['uid']) ? 4 : 5;
                    $result['num' . $item['location']] = $item['location'];
                }
            }
        }
        //is neighbor
        else {
            $user = Bll_User::getPerson($uid);
            $username = htmlspecialchars($user->getUnescapeDisplayName(), ENT_QUOTES, 'UTF-8');
            $parkingShip = $mdalShip->getShipDetailInfo($uid, $puser['uid']);

            for ($i = 1; $i <= $this->_locationCount; $i++) {
                $result['btn' . $i] = 0;
                $result['num' . $i] = 0;
            }

            foreach ($parkingShip as $item) {
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
                $result['user' . $item['location']] = $username;
                $result['btn' . $item['location']] = 5;
                $result['num' . $item['location']] = $item['location'];
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
        require_once 'Bll/Friend.php';
        $mixiFriendIds = Bll_Friend::getFriends($uid);
        
        require_once 'Mdal/Ship/User.php';
        $mdalUser = Mdal_Ship_User::getDefaultInstance();
        //get app friendids
        $friends = $mdalUser->getAppFriends($mixiFriendIds);

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

        $key = array_search($fid, $arrFriendId);

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
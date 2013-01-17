<?php
/** @see Bll_Abstract.php */
require_once 'Bll/Abstract.php';

/**
 * ship index logic's Operation
 *
 * @package    Mbll
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/11/30    Liz
 */
class Mbll_Ship_Index extends Bll_Abstract
{
    /**
     * get user park info
     *
     * @param string $uid
     * @param integer $request_uid
     * @return array
     */
    public function getUserPark($uid, $request_uid)
    {
        require_once 'Mdal/Ship/Ship.php';
        $mdalShip = new Mdal_Ship_Ship();

        require_once 'Mdal/Ship/User.php';
        $mdalUser = new Mdal_Ship_User();

        //check is nieghbor or not
        if ( $request_uid > 0 ) {
            $parkCurrentShip = $mdalShip->getFriendPark($request_uid);
            $parkCurrent = $mdalUser->getUserPark($request_uid);

            if ( $parkCurrentShip ) {
                Bll_User::appendPeople($parkCurrentShip, 'captain_uid');
            }

            $parkCurrent['locaCount'] = 4;

            if ( $parkCurrent ) {
                Bll_User::appendPerson($parkCurrent, 'uid');
            }
        }
        else {
            $parkCurrentShip = $mdalShip->getNeighborPark($uid, $request_uid);
            $parkCurrent = $mdalUser->getUserNeighborPark($request_uid);

            if ( $parkCurrentShip ) {
                Bll_User::appendPeople($parkCurrentShip, 'captain_uid');
            }
        }

        $response = array('user'=>$parkCurrent, 'ship'=>$parkCurrentShip);

        return $response;
    }
    
    /**
    * get user current park left and right user id
    *
    * @param string $uid
    * @param string $fid
    */
    public function getLeftAndRightFriend($uid, $fid)
    {
        require_once 'Bll/Friend.php';
        $mixiFriendIds = Bll_Friend::getFriends($uid);
        
        if ( $mixiFriendIds ) {
            require_once 'Mdal/Ship/User.php';
            $mdalUser = Mdal_Ship_User::getDefaultInstance();
            //get app friendids
            $friends = $mdalUser->getAppFriends($mixiFriendIds);
        }
        else {
            $friends = array();
        }
        
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
            $arrFriendId[] = -3;
        }

        $key = array_search($fid, $arrFriendId);

        if ($key === false) {
            $leftUid = $arrFriendId[count($arrFriendId) - 1];
            $rightUid = $arrFriendId[0];
        }
        else {
            if ($key == 0) {
                $leftUid = $arrFriendId[count($arrFriendId) - 1];
                $rightUid = $arrFriendId[1];
            }
            else if ($key == count($arrFriendId) - 1) {
                $leftUid = $arrFriendId[$key - 1];
                $rightUid = $arrFriendId[0];
            }
            else {
                $leftUid = $arrFriendId[$key - 1];
                $rightUid = $arrFriendId[$key + 1];
            }
        }
        
        return array('rightUid' => $rightUid, 'leftUid' => $leftUid, 'friendIds' => $friends);
    }
    
    /**
     * get user ships list info
     *
     * @param string $uid
     * @param array $neighbor
     * @return array
     */
    public function getUserShips($uid, $neighbor=array(), $type=1)
    {
        require_once 'Mdal/Ship/User.php';
        $mdalUser = Mdal_Ship_User::getDefaultInstance();
                
        require_once 'Mdal/Ship/Ship.php';
        $mdalShip = new Mdal_Ship_Ship();
        //get user ships
        $userShips = $mdalShip->getUserShips($uid);

        $ships = array();

        require_once 'Bll/User.php';

        foreach ($userShips as $ship) {
            //if ship status ==0
            if ( $ship['status'] != 1 ) {
                $ship['status'] = "修理が必要です";
                $ship['money'] = '破壊されました';
                $ship['temp'] = '0';
                $ships = array_merge($ships,array($ship));
            }
            //if parked time != null
            else if ($ship['parked_time'] != null) {
                //park at friend
                if ($ship['type'] == 1) {
                    $parkingUserInfo = Bll_User::getPerson($ship['parking_uid']);
                    $parkingInfo = $mdalUser->getUserPark($ship['parking_uid']);

                    $result = $this->getUserShipStatus($ship['uid'], $ship, $parkingUserInfo->getDisplayName(), $parkingInfo['fee'], $type, $uid);
                    $ship['money'] = "$".$result['money'];
                    $ship['status'] = $result['status'];
                    $ship['temp'] = $result['temp'];
                    $ships = array_merge($ships,array($ship));
                }
                //park at neighbor
                else {
                    $userPark = $mdalUser->getUserPark($uid);
                    
                    require_once 'Mdal/Ship/Neighbor.php';
                    $mdalNeighbor = Mdal_Ship_Neighbor::getDefaultInstance();
                    //get user neighbor info
                    $neighbor = $mdalNeighbor->getNeighbor($uid, $userPark['neighbor_left'], $userPark['neighbor_right'], $userPark['neighbor_center']);

                    foreach ($neighbor as $n) {
                        if ($ship['parking_uid'] == $n['id']) {
                            $result = $this->getUserShipStatus($uid, $ship, $n['nickname'], $n['fee'], 2, $uid);
                            
                            $ship['money'] = "$".$result['money'];
                            $ship['status'] = $result['status'];
                            $ship['temp'] = $result['temp'];

                            $ships = array_merge($ships,array($ship));
                            break;
                        }
                    }
                }
            }
            else {
                $ship['status'] = "大海原を航海中";
                $ship['money'] = "$0";
                $ship['temp'] = '0';
                $ships = array_merge($ships,array($ship));
            }
        }

        return $ships;
    }
    
    /**
     * get ship status by user ship id
     *
     * @param string $uid
     * @param string $userShipId
     * @return array
     */
    public function getShipStatusByUserShipId($uid, $userShipId)
    {
        require_once 'Mdal/Ship/Ship.php';
        $mdalShip = Mdal_Ship_Ship::getDefaultInstance();
        //get user ship info
        $ship = $mdalShip->getShipParkingInfoByUserShipId($userShipId);
        
        //if ship status ==0
        if ( $ship['status'] != 1 ) {
            $ship['status'] = "修理が必要です";
            $ship['money'] = '破壊されました';
            $ship['temp'] = '0';
        }
        else if ( $ship['parked_time'] != null ) {
            
            require_once 'Mdal/Ship/User.php';
            $mdalUser = Mdal_Ship_User::getDefaultInstance();
            //park at friend
            if ($ship['type'] == 1) {
                require_once 'Bll/User.php';
                $parkingUser = Bll_User::getPerson($ship['parking_uid']);
                $parkingUserName = $parkingUser->getDisplayName();
                
                $parkingInfo = $mdalUser->getUserPark($ship['parking_uid']);
                
                //get ship parking status
                $parkingInfo = $this->getUserShipStatus($ship['uid'], $ship, $parkingUserName, $parkingInfo['fee'], $ship['type'], $uid);
                
                $ship['money'] = "$".$parkingInfo['money'];
                $ship['status'] = $parkingInfo['status'];
                $ship['temp'] = $parkingInfo['temp'];
            }//park at neighbor
            else {
                $userPark = $mdalUser->getUserPark($uid);

                require_once 'Mdal/Ship/Neighbor.php';
                $mdalNeighbor = Mdal_Ship_Neighbor::getDefaultInstance();
                //get user neighbor info
                $neighbor = $mdalNeighbor->getNeighbor($uid, $userPark['neighbor_left'], $userPark['neighbor_right'], $userPark['neighbor_center']);

                foreach ($neighbor as $n) {
                    if ($ship['parking_uid'] == $n['id']) {
                        $result = $this->getUserShipStatus($uid, $ship, $n['nickname'], $n['fee'], 2, $uid);
                        
                        $ship['money'] = "$".$result['money'];
                        $ship['status'] = $result['status'];
                        $ship['temp'] = $result['temp'];
                        break;
                    }
                }
            }
        }
        else {
            $ship['status'] = "大海原を航海中";
            $ship['money'] = "$0";
            $ship['temp'] = '0';
        }
        
        return $ship;
    }
    
    
    /**
     * get user ship status
     *
     * @param string $uid
     * @param array $ship
     * @param string $nickname
     * @param integer $fee
     * @param integer $isfree
     * @param integer $type 1:pc 2:mobile
     * @param string $userID
     * @return array
     */
    public function getUserShipStatus($uid, $ship, $nickname, $fee, $type, $userID)
    {
        //check is self or not
        $isSelf = $userID == $uid;

        //get money
        $time = floor((time()-$ship['parked_time'])/900);
        $time = $time > 32 ? 32 : $time;
        $money = $time * $fee * $ship['times'];
        $money = number_format($money);

        $temp = $time/4 + 1;
        $temp = floor($temp);

        //$url = '{% "$baseUrl/mobile/ship/index/CF_uid/' . $uid . '" %}';
        $url = Zend_Registry::get('host') . '/mobile/ship/index/CF_uid/' . $ship['parking_uid'];
        $returnUrl = '?guid=ON&url=' . urlencode($url . '?rand=' .rand());
    
        if ( $type == 1 ) {
            if ( $isSelf || $ship['type'] == 1 ) {
                $status = "<a style='color:#0066ff;' href=".$returnUrl.">" . $nickname . "</a>の海賊島で強奪中";
            }
        }
        else {
            $status = "<a style='color:#0066ff;' href=".$returnUrl.">" . $nickname . "</a>の海賊島で強奪中";
        }

        $result = array('money' => $money, 'status' => $status, 'temp' => $temp);
        return $result;
    }

    /**
     * parking
     *
     * @param string $uid
     * @param string $park_uid
     * @param integer $ship_id
     * @param integer $location
     * @param integer $type
     * @return array
     */
    public function parking($uid, $park_uid, $user_ship_id, $location)
    {
        $result = array('status'=>1);
        
        require_once 'Mdal/Ship/User.php';
        $mdalUser = Mdal_Ship_User::getDefaultInstance();
        
        require_once 'Bll/Friend.php';
        $friendIds = Bll_Friend::getFriends($uid);
        
        require_once 'Bll/User.php';
        
        //get user park info
        $user = $mdalUser->getUserPark($uid);
        //check is neighbor or not
        $type = $park_uid > 0 ? 1 : 2;
        
        //get park user name
        if ( $type == 1 ) {
            $park_userInfo = Bll_User::getPerson($park_uid);
            $nickname = $park_userInfo->getDisplayName();
        }
        else {
            require_once 'Mdal/Ship/Neighbor.php';
            $mdalNeighbor = new Mdal_Ship_Neighbor();                
            $nickname = $mdalNeighbor->getNeighborName($park_uid);
        }
        
        $result['parkingUid'] = $park_uid;
        $result['parkingUsername'] = $nickname;
        
        //check is friend
        if ($type == 1){
            $isFriend = Bll_Friend::isFriend($uid, $park_uid);
            if (!$isFriend) {
                $result['status'] =-1;
                return $result;
            }
        }
        else{
            //check is my neighbor
            if ($user['neighbor_left'] != $park_uid && $user['neighbor_right'] != $park_uid && $user['neighbor_center'] != $park_uid) {
                $result['status'] =-1;
                return $result;
            }

            //check friend count
            $arrFriendId = $mdalUser->getAppFriends($friendIds);
            if ( count($arrFriendId) >2 ) {
                $result['status'] =-1;
                return $result;
            }
        }

        //check is user's ship        
        require_once 'Mdal/Ship/Ship.php';
        $mdalShip = Mdal_Ship_Ship::getDefaultInstance();
        //get ship info by user ship id
        $userShip = $mdalShip->getUserShipById($uid, $user_ship_id);
        if ( !$userShip ) {
            $result['status'] =-1;
            return $result;
        }
        
        //check ship status
        if ( $userShip['status'] != 1 ) {
            $result['status'] =-1;
            return $result;
        }

        //check the location is empty
        $isEmpty = $mdalShip->isEmptyLocation($uid, $park_uid, $location, $type);
        if ( !$isEmpty ) {
            $result['status'] =-2;
            return $result;
        }

        $lastParkInfo = $mdalShip->getParkInfo($uid, $user_ship_id);
        //check last park user
        if ($lastParkInfo['parking_uid'] == $park_uid && $type == $lastParkInfo['type']) {
            $result['status'] =-3;
            return $result;
        }

        //check last park time
        if (time()-$lastParkInfo['parked_time'] < 3600) {
            $result['status'] =-4;
            return $result;
        }

        require_once 'Mdal/Ship/Item.php';
        $mdalItem = Mdal_Ship_Item::getDefaultInstance();
        require_once 'Mdal/Ship/Feed.php';
        $mdalFeed = new Mdal_Ship_Feed();

        $create_time = date('Y-m-d H:i:s');
        $updateAsset = 0;
        
        //get ship info
        $shipInfo = $mdalShip->getShipInfo($userShip['ship_id']);
        
        $this->_wdb->beginTransaction();
        try {
            //check had bomb
            $bomb = $mdalItem->getBombInfo($park_uid);
            $loca = 'location'.$location;
            if ( $bomb[$loca] > 0 ) {
                //check is last ship
                if ( $user['ship_count'] > 1 ) {
                    //check insurance card
                    if ( $user['insurance_card'] < 1 ) {
                        //update user ship status
                        $mdalShip->updateUserShipStatus($uid, $user_ship_id, 0);
                        //update user ship price
                        $mdalUser->updateShipPrice($shipInfo['price'], $uid);
                        //update park user asset
                        $mdalUser->updateUserAsset($shipInfo['price']*0.2, $park_uid);
                        //update parking uid bomb count
                        $mdalItem->updateBombCount($park_uid, $loca);
                        
                        $result['status'] = -5;
                        $bombFeed = 1;
                    }
                    else {
                        //update user insurance card count
                        $mdalItem->updateUserInsuranceCount($uid, -1);

                        $result['status'] = -13;
                        $bombFeed = 2;
                    }
                }
                else {//car count = 1
                    $this->_wdb->rollBack();
                    $result['status'] =-11;
                    return $result;
                }
            }

            if ($result['status'] != -13) {
                //clear last park fee
                if (count($lastParkInfo) > 0) {
                    if ($lastParkInfo['location'] != $lastParkInfo['free_park']) {
                        //update user asset
                        $time = floor((time()-$lastParkInfo['parked_time'])/900);
                        $time = $time>32 ? 32 : $time;

                        $lastParkUser = $mdalUser->getUserPark($lastParkInfo['parking_uid']);
                        Bll_User::appendPerson($lastParkUser, 'uid');

                        //get money
                        $money = $time*$lastParkInfo['fee']*$shipInfo['times'];

                        $mdalUser->updateUserAsset($money, $uid);
                        $updateAsset = 1;
                        $hasFeed = 1;
                    }
                    else {
                        $hasFeed = 3;
                    }
                    //delete last parking info
                    $mdalShip->deleteParkingInfo($lastParkInfo['sid']);

                }

                //if no bomb ,insert parking
                if ( $result['status'] != -5 && $result['status'] != -7 ) {
                    //insert parking
                    $parkInfo = array('uid' => $uid,
                                      'ship_id' => $userShip['ship_id'],
                                      'user_ship_id' => $user_ship_id,
                                      'ship_count' => $user['ship_count'],
                                      'parking_uid' => $park_uid,
                                      'location' => $location,
                                      'parked_time' => time(),
                                      'type' => $type);
                    $mdalShip->insertParkingInfo($parkInfo);
                }
            }

            $this->_wdb->commit();

            $result['updateAsset'] = $updateAsset;
            if ( $updateAsset == 1 ) {
                $result['money'] = $money;
                $result['allAsset'] = $mdalUser->getUserAllAsset($uid);
                
                $result['rankNmFriend'] = $mdalUser->getUserAllAssetRankNmInFriends($uid, $friendIds);
                $result['rankNmAll'] = $mdalUser->getUserAllAssetRankNmInAll($uid);
            }
            
            $result['shipName'] = $userShip['ship_name'] ? $userShip['ship_name'] : $shipInfo['name'];
            $result['shipCavName'] = $shipInfo['cav_name'];
            $result['userShipId'] = $user_ship_id;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            $result['status'] =-1;
            return $result;
        }
    
        //insert feed
        if ($hasFeed == 1) {
            $minifeed = array('uid' => $uid,
                              'template_id' => 67,
                              'actor' => $uid,
                              'target' => $lastParkInfo['parking_uid'],
                              'title' => '{"money":"' . number_format($money) . '"}',
                              'icon' => Zend_Registry::get('static') . "/apps/ship/img/icon/dollar.gif",
                              'create_time' => $create_time);
            $mdalFeed->insertMinifeed($minifeed);

            if ( $lastParkInfo['type'] == 1 ) {
                $minifeed['uid'] = $lastParkInfo['parking_uid'];
                $minifeed['template_id'] = 68;
                $minifeed['title'] = '{"money":"' . number_format($money) . '"}';
                $minifeed['icon'] = Zend_Registry::get('static') . "/apps/ship/img/icon/loss.gif";
                $mdalFeed->insertMinifeed($minifeed);
            }
        }
        else if ( $hasFeed == 3) {
            /*$minifeed = array('uid' => $uid,
                              'template_id' => 13,
                              'actor' => $uid,
                              'target' => $lastParkInfo['parking_uid'],
                              'title' => '{"car_name":"'. $shipInfo['name'] . '","name":"' . $nickname . '"}',
                              'icon' => Zend_Registry::get('static') . "/apps/parking/img/icon/free.gif",
                              'create_time' => $create_time);
            $mdalFeed->insertMinifeed($minifeed);

            if ( $lastParkInfo['type'] == 1 ) {
                $minifeed['uid'] = $lastParkInfo['parking_uid'];
                $minifeed['template_id'] = 14;
                $mdalFeed->insertMinifeed($minifeed);
            }*/
        }

        //insert bomb feed
        if ( $bombFeed == 1 ) {
            /*$minifeed = array('uid' => $uid,
                              'template_id' => 26,
                              'actor' => $uid,
                              'target' => $park_uid,
                              'title' => '{"car_name":"'. $shipInfo['name'] . '"}',
                              'icon' => Zend_Registry::get('static') . "/apps/parking/img/icon/loss.gif",
                              'create_time' => $create_time);
            $mdalFeed->insertMinifeed($minifeed);

            $minifeed['template_id'] = 27;//33
            $minifeed['icon'] = Zend_Registry::get('static') . "/apps/parking/img/icon/car.gif";
            $mdalFeed->insertNewsfeed($minifeed);

            $minifeed['uid'] = $park_uid;
            $minifeed['template_id'] = 27;//32
            $mdalFeed->insertMinifeed($minifeed);

            $minifeed['template_id'] = 27;//33
            $mdalFeed->insertNewsfeed($minifeed);*/
        }
        else if ( $bombFeed == 2 ) {
            /*$minifeed = array('uid' => $uid,
                              'template_id' => 27,
                              'actor' => $uid,
                              'target' => $park_uid,
                              'title' => '{"car_name":"'. $shipInfo['name'] . '"}',
                              'icon' => Zend_Registry::get('static') . "/apps/parking/img/icon/car.gif",
                              'create_time' => $create_time);
            $mdalFeed->insertMinifeed($minifeed);

            $minifeed['uid'] = $park_uid;
            $minifeed['template_id'] = 34;
            $mdalFeed->insertMinifeed($minifeed);*/
        }

        return $result;
    }

    /**
     * stick
     *
     * @param integer $uid
     * @param integer $location
     * @return array
     */
    public function stick($uid, $location)
    {
        $result = array('status'=>-1);
        
        require_once 'Mdal/Ship/Ship.php';
        $mdalShip = Mdal_Ship_Ship::getDefaultInstance();

        //check the location is empty
        $parkInfo = $mdalShip->getUserParkInfoByLocation($uid, $location);

        $parkingUid = $parkInfo['uid'] ? $parkInfo['uid'] : $uid;
        require_once 'Bll/User.php';
        $parkingUser = Bll_User::getPerson($parkingUid);
        $result['parkingName'] = $parkingUser->getDisplayName();
        $result['parkingUid'] = $parkingUid;
        
        if ( empty($parkInfo) ) {
            return $result;
        }
        
        //get user ship info by user ship id
        $userShipInfo = $mdalShip->getShipByUserShipId($parkInfo['user_ship_id']);
        $result['name'] = $userShipInfo['shipName'];
        
        //check is friend
        /*$isFriend = Bll_Friend::isFriend($uid, $parkInfo['uid']);
        if (!$isFriend) {
            return $result;
        }*/

        //check time
        if (time() - $parkInfo['parked_time'] < 3600) {
            $result['status'] = -2;
            return $result;
        }

        //get ship info
        $shipInfo = $mdalShip->getShipInfo($parkInfo['ship_id']);
        
        require_once 'Mdal/Ship/User.php';
        $mdalUser = Mdal_Ship_User::getDefaultInstance();
        
        //stick start
        $this->_wdb->beginTransaction();

        try {
            $userPark = $mdalUser->getUserPark($uid);

            //delete parking info
            $mdalShip->deleteParkingInfo($parkInfo['sid']);

            //update user asset
            if ($location != $userPark['free_park']) {
                $time = floor((time()-$parkInfo['parked_time'])/900);
                $time = $time>32 ? 32 : $time;
                $money = $time*$userPark['fee']*$shipInfo['times']*1.2;

                $mdalUser->updateUserAsset($money, $uid);
            }

            $this->_wdb->commit();
            
            $result['status'] = 1;
            $result['money'] = $money;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return array('status'=>-1);
        }
        
        $create_time = date('Y-m-d H:i:s');
        //insert into minifeed
        require_once 'Mdal/Ship/Feed.php';
        $mdalFeed = new Mdal_Ship_Feed();
        
        $minifeed = array('uid' => $uid,
                          'template_id' => 61,
                          'actor' => $uid,
                          'target' => $parkInfo['uid'],
                          'title' => '{"money":"' . number_format($money) . '"}',
                          'icon' => Zend_Registry::get('static') . "/apps/ship/img/icon/dollar.gif",
                          'create_time' => $create_time);
        $mdalFeed->insertMinifeed($minifeed);

        $minifeed['uid'] = $parkInfo['uid'];
        $minifeed['template_id'] = 62;
        $minifeed['title'] = '{"shipName":"' . $shipInfo['name'] . '","money":"' . number_format($money) . '"}';
        $minifeed['icon'] = Zend_Registry::get('static') . "/apps/ship/img/icon/loss.gif";
        $mdalFeed->insertMinifeed($minifeed);
            
        return $result;
    }

    /**
     * report
     *
     * @param integer $uid
     * @param integer $park_uid  park's host uid
     * @param integer $location
     * @param integer $isAnonymous
     * @return array
     */
    public function report($uid, $park_uid, $location, $isAnonymous)
    {
        $result = array('status'=>-1);

        require_once 'Mdal/Ship/Ship.php';
        $mdalShip = Mdal_Ship_Ship::getDefaultInstance();
        
        $park_userInfo = Bll_User::getPerson($park_uid);
        $result['parkingUsername'] = $park_userInfo->getDisplayName();
        $result['parkingUid'] = $park_uid;

        //check location is empty
        $parkInfo = $mdalShip->getUserParkInfoByLocation($park_uid, $location);
        if ( empty($parkInfo) ) {
            return $result;
        }
    
        //check is friend
        $isFriend = Bll_Friend::isFriend($uid, $parkInfo['uid']);
        if (!$isFriend) {
            return $result;
        }
        
        $report_userInfo = Bll_User::getPerson($parkInfo['uid']);
        //get user ship info by user ship id
        $shipInfo = $mdalShip->getShipByUserShipId($parkInfo['user_ship_id']);
        
        require_once 'Mdal/Ship/Report.php';
        $mdalReport = new Mdal_Ship_Report();
        //check is report
        $isReport = $mdalReport->isReport($parkInfo['sid'], $uid);
        if ( $isReport ) {
            $result['message'] = $report_userInfo->getDisplayName() . '海賊団の' . $shipInfo['shipName'] . '号はすでに通報済みです・・・。';
            $result['status'] = "1";
            return $result;
        }
        
        require_once 'Mdal/Ship/Card.php';
        $mdalCard = Mdal_Ship_Card::getDefaultInstance();
        //get card count
        $anonymousCardCount = $mdalCard->getUserCardCoutByCid(1, $uid);
                
        //report start
        $this->_wdb->beginTransaction();

        try {
            //insert report
            $report = array('uid' => $uid,
                            'sid' => $parkInfo['sid'],
                            'anonymous' => 0,
                            'create_time' => time());
            
            if ( $isAnonymous == 1 && $anonymousCardCount > 0 ) {
                $report['anonymous'] = 1;

                //update card count
                $mdalCard->updateUserCardCoutByCid(1, $uid, -1);

                $result['isAnonymous'] = 1;
                $result['message'] = '匿名通報カード1枚を使いました。<br/>' . $report_userInfo->getDisplayName() . '海賊団の' . $shipInfo['shipName'] . '号はすでに匿名通報済みです・・・。';
            }
            else {
                $result['message'] = '世界政府に' . $report_userInfo->getDisplayName() . '海賊団の' . $shipInfo['shipName'] . '号を通報しました。<br/>1時間後に世界政府の戦艦がやってくるでしょう';
            }
            
            //insert report info
            $mdalReport->insertReport($report);
                
            $this->_wdb->commit();
            
            $result['status'] = "1";
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
        }
        return $result;
    }
    
    /**
     * today frist login,send card
     *
     * @param string $uid
     * @return array
     */
    public function isTodayFirstLogin($uid, $appId)
    {
        $result = array('status' => -1);
        require_once 'Mdal/Ship/User.php';
        $mdalUser = Mdal_Ship_User::getDefaultInstance();
                        
        $i = rand(1, 100);

        //get card id
        switch ($i) {
            case $i<61 :
                $cid = 5;
                break;
            case 60<$i && $i<96 :
                $cid = 6;
                break;
            case 95<$i :
                $cid = 7;
        }
        
        switch ( $cid ) {
            case 5 :
                $money = "200";
                break;
            case 6 :
                $money = "500";
                break;
            case 7 :
                $money = "1000";
                break;
        }
        
        $this->_wdb->beginTransaction();
        try {
            //update user asset
            $mdalUser->updateUserAsset($money, $uid);

            $user = array('send_gift' => $cid, 'last_login_time' => time());
            //update ship user
            $mdalUser->updateShipUser($uid, $user);

            $result['status'] = 1;
            $result['cid'] = $cid;

            $this->_wdb->commit();
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return array('status'=>-1);
        }

        $title = 'ビジターギフトを受け取る：金貨' . $money . 'をもらいました！';
        //ビジターギフトを受け取る：金貨xxxをもらいました！
        
        //send activity
        require_once 'Bll/Restful.php';
        //get restful object
        $restful = Bll_Restful::getInstance($uid, $appId);
        $restful->createActivity(array('title'=>$title));
        
        return $result;
    }

    /**
     * send ship to friend
     *
     * @param integer $uid
     * @param integer $fid
     * @param integer $user_ship_id
     * @return integer
     */
    public function sendShip($uid, $fid, $user_ship_id)
    {
        $result = -1;

        //check is friend
        require_once 'Bll/Friend.php';
        $isFriend = Bll_Friend::isFriend($uid, $fid);
        if (!$isFriend) {
            return $result;
        }

        require_once 'Mdal/Ship/User.php';
        $mdalUser = Mdal_Ship_User::getDefaultInstance();
        
        //check friend is join app
        $isIn = $mdalUser->isInApp($fid);
        if (!$isIn) {
            return $result;
        }

        //check friend ship count
        $friendPark = $mdalUser->getUserPark($fid);
        if($friendPark['ship_count'] == 8) {
            return -2;
        }

        //check friend ship
        //if friend has this ship count return false
        require_once 'Mdal/Ship/Ship.php';
        $mdalShip = Mdal_Ship_Ship::getDefaultInstance();
        
        //check the ship is user
        $userShip = $mdalShip->getUserShipById($uid, $user_ship_id);
        if (!$userShip) {
            return $result;
        }
        
        $friendShipCount = $mdalShip->getUserShipCountBySid($fid, $userShip['ship_id']);
        if ( $friendShipCount >=3 ) {
            return -3;
        }
        
        //check car status
        if ($userShip['status'] != 1) {
            return $result;
        }

        //check local user car count
        $userPark = $mdalUser->getUserPark($uid);
        if ( $userPark['ship_count'] <= 1 ) {
            return -6;
        }

        //check last send friend time
        if (time() - $userPark['send_ship_time']< 30*24*3600) {
            return -4;
        }

        //check friend last receive car time
        if (time() - $friendPark['receive_ship_time']< 30*24*3600) {
            return -5;
        }
        
        //get this ship info
        $shipInfo = $mdalShip->getShipInfo($userShip['ship_id']);
        
        //check car is ad bus
        /*if ( $shipInfo['type'] == 2 ) {
            return $result;
        }*/
        
        //send user start
        $this->_wdb->beginTransaction();

        try {
            $lastParkInfo = $mdalShip->getParkInfo($uid, $user_ship_id);

            //clear last park fee
            if ( $lastParkInfo ) {
                //update user asset
                $time = floor((time()-$lastParkInfo['parked_time'])/900);
                $time = $time>32 ? 32 : $time;
                $money = $time*$lastParkInfo['fee']*$shipInfo['times'];
                
                if ( $money > 0 ) {
                    $mdalUser->updateUserAsset($money, $uid);
                }

                //delete last parking info
                $mdalShip->deleteParkingInfo($lastParkInfo['sid']);
            }
            
            //delete user old car
            $mdalShip->deleteUserShips($uid, $user_ship_id);

            //insert into user ship
            $newShip = array('uid' => $fid,
                             'ship_id' => $shipInfo['sid'],
                             'create_time' => time());
            $mdalShip->insertUserShip($newShip);

            //update user ship count and price
            $mdalShip->updateUserShipCount($uid);
            $mdalShip->updateUserShipCount($fid);

            $time = time();
            $newUser = array('send_ship_time' => $time);
            //update user last send ship time
            $mdalUser->updateShipUser($uid, $newUser);

            $newFriend = array('receive_ship_time' => $time);
            //update friend last revice ship time
            $mdalUser->updateShipUser($fid, $newFriend);

            /*require_once 'Mdal/Ship/Feed.php';
            $mdalFeed = new Mdal_Ship_Feed();
            $create_time = date('Y-m-d H:i:s');
            //insert into minifeed
            $minifeed1 = array('uid' => $uid,
                              'template_id' => 24,
                              'actor' => $uid,
                              'target' => $fid,
                              'title' => '{"car_name":"'. $shipInfo['name'] . '"}',
                              'icon' => Zend_Registry::get('static') . "/apps/parking/img/icon/gift.gif",
                              'create_time' => $create_time);
            $mdalFeed->insertMinifeed($minifeed1);

            $minifeed2 = array('uid' => $fid,
                              'template_id' => 28,
                              'actor' => $uid,
                              'target' => $fid,
                              'title' => '{"car_name":"'. $shipInfo['name'] . '"}',
                              'icon' => Zend_Registry::get('static') . "/apps/parking/img/icon/gift.gif",
                              'create_time' => $create_time);
            $mdalFeed->insertMinifeed($minifeed2);

            $minifeed1['template_id'] = 29;
            $minifeed2['template_id'] = 29;
            //insert into newsfeed
            $mdalFeed->insertNewsfeed($minifeed1);
            $mdalFeed->insertNewsfeed($minifeed2);*/

            $result = 1;
            $this->_wdb->commit();
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
        }
        return $result;
    }
    
    /**
     * get minifeed
     *
     * @param integer $uid
     * @return array
     */
    public function getMinifeed($uid, $appId)
    {
        require_once 'Mdal/Ship/Feed.php';
        $mdalFeed = new Mdal_Ship_Feed();
        $feed = $mdalFeed->getMinifeed($uid, 1, 8);

        return $this->buildFeed($feed, $appId);
    }

    /**
     * get newsfeed
     *
     * @param integer $uid
     * @return array
     */
    public function getNewsfeed($uid, $appId)
    {
        require_once 'Bll/Friend.php';
        $friendList  = Bll_Friend::getFriends($uid);

        require_once 'Mdal/Ship/Feed.php';
        $mdalFeed = new Mdal_Ship_Feed();
        $feed = $mdalFeed->getNewsfeed($uid, $friendList, 1, 8);

        return $this->buildFeed($feed, $appId);
    }

    /**
     * build feed
     *
     * @param array $feed
     * @return array
     */
    public function buildFeed($feed, $appId)
    {
        $lnml_env = array('is_mobile'=>false);

        require_once 'Zend/Json.php';

        for($i = 0; $i < count($feed); $i++) {
            $feed_title_template = self::getFeedTemplateTitle($appId, $feed[$i]['template_id'], $lnml_env['is_mobile']);

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

        $url = Zend_Registry::get('host') . '/mobile/ship/index/CF_uid/' . $user;
        $joinchar = (stripos($url, '?') === false) ? '?' : '&';
        $actorUrl = Zend_Registry::get('MIXI_APP_REQUEST_URL') . urlencode($url . $joinchar . 'rand=' . rand());

        $json_array['actor'] = '<a href="' . $actorUrl . '">' . $actor_name . '</a>';

        if ($target) {
            if ($target < 0) {
                require_once 'Mdal/Ship/Neighbor.php';
                $mdalNeighbor = new Mdal_Ship_Neighbor();
                $json_array['target'] = $mdalNeighbor->getNeighborName($target);
            }
            else {
                $targ = Bll_User::getPerson($target);

                if (empty($targ)) {
                    $target_name = "____";
                }
                else {
                    $target_name = $targ->getDisplayName();
                }
                
                $url = Zend_Registry::get('host') . '/mobile/ship/index/CF_uid/' . $target;
                $joinchar = (stripos($url, '?') === false) ? '?' : '&';
                $targetUrl = Zend_Registry::get('MIXI_APP_REQUEST_URL') . urlencode($url . $joinchar . 'rand=' . rand());
                $json_array['target'] = '<a href="' . $targetUrl . '">' . $target_name . '</a>';
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
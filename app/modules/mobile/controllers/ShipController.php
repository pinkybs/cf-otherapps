<?php

/** @see MyLib_Zend_Controller_Action_Mobile.php */
require_once 'MyLib/Zend/Controller/Action/Mobile.php';

/**
 * Mobile Ship Controller(modules/mobile/controllers/ShipController.php)
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/11/30   Liz
 */
class Mobile_ShipController extends MyLib_Zend_Controller_Action_Mobile
{
    
    protected $_shipUser;

	/**
     * deipatch
     *
     */
    function preDispatch()
    {        
        //report,stick
        require_once 'Mbll/Ship/BatchWork.php';
        $mbllBatch = new Mbll_Ship_BatchWork();
        $mbllBatch->report($this->_USER_ID);
        $mbllBatch->checkIsland($this->_USER_ID);
        
        require_once 'Mdal/Ship/User.php';
        $mdalUser = Mdal_Ship_User::getDefaultInstance();
        //get user info
        $shipUser = $mdalUser->getUserPark($this->_USER_ID);
        if ( !$shipUser ) {
            Mbll_Application_Plugin_Ship::postUpdatePerson($this->_USER_ID);
            $this->_redirect($this->_baseUrl . '/mobile/ship/firstlogin');
            return;
        }
        $this->_shipUser = $shipUser;
        
        $actionName = $this->_request->getActionName();
        //check is today first login
        $todayTime = strtotime(date("Y-m-d"));
        
        if ( !$shipUser['last_login_time'] && $actionName != 'firstlogin' ) {
            $this->_redirect($this->_baseUrl . '/mobile/ship/firstlogin');
            return;
        }
        else if ( $shipUser['last_login_time'] < $todayTime && $actionName != 'todayfirstlogin' && $actionName != 'firstlogin' ) {
            $this->_redirect($this->_baseUrl . '/mobile/ship/todayfirstlogin');
            return;
        }
        
        $this->view->ua = $this->_ua;
        $this->view->rand = time();
    }
    
    /**
     * firstlogin action 
     *
     */
    public function firstloginAction()
    {
        $action = $this->getParam('CF_step', 'start');
        $this->view->action = $action;
        
        if ( $this->_shipUser['last_login_time'] ) {
            $this->_redirect($this->_baseUrl . '/mobile/ship/top');
            return;
        }
        
        if ( $action == 'start' ) {
            
        }
        else if ( $action == 'sendgift' ) {
            require_once 'Mdal/Ship/Ship.php';
            $mdalShip = Mdal_Ship_Ship::getDefaultInstance();
            //get user ship info
            $userShip = $mdalShip->getUserShips($this->_USER_ID);
            $this->view->userShip = $userShip;
        }
        else if ( $action == 'complete' ) {
            $bgId = rand(1, 4);
            $user = array('background' => $bgId, 'last_login_time' => time());
            
            require_once 'Mdal/Ship/User.php';
            $mdalUser = Mdal_Ship_User::getDefaultInstance();
            //update ship user
            $mdalUser->updateShipUser($this->_USER_ID, $user);
            
            $this->_redirect($this->_baseUrl . '/mobile/ship/index');
            return;
        }
        
        $this->render();
    }
    
    /**
     * today firstlogin action 
     *
     */
    public function todayfirstloginAction()
    {
        $action = $this->getParam('CF_step', 'start');
        $this->view->action = $action;
        
        //check is today first login
        $todayTime = strtotime(date("Y-m-d"));
        if ( $this->_shipUser['last_login_time'] >= $todayTime ) {
            $this->_redirect($this->_baseUrl . '/mobile/ship/top');
            return;
        }
        
        require_once 'Mbll/Ship/Index.php';
        $mbllIndex = new Mbll_Ship_Index();
        //today firstlogin send gift
        $result = $mbllIndex->isTodayFirstLogin($this->_USER_ID, $this->_APP_ID);
        
        if ( $result['status'] != 1 ) {
            $this->_redirect($this->_baseUrl . '/mobile/ship/todayfirstlogin');
            return;
        }
        else {
            $this->_redirect($this->_baseUrl . '/mobile/ship/top');
            return;
        }
        
        $this->render();
    }
    
    /**
     * gift action
     *
     */
    public function giftAction()
    {
        $action = $this->getParam('CF_step', 'confirm');
        $this->view->action = $action;
        
        if ( $this->_shipUser['send_gift'] <= 0 ) {
            $this->_redirect($this->_baseUrl . '/mobile/ship/top');
            return;
        }
        
        if ( $action == 'start' ) {
            
        }
        else {
            require_once 'Mdal/Ship/User.php';
            $mdalUser = Mdal_Ship_User::getDefaultInstance();
            //update user asset
            $mdalUser->updateUserSendGift($this->_USER_ID, 0);
            
            $giftInfo = array('cid' => $this->_shipUser['send_gift']);
            if ( $this->_shipUser['send_gift'] < 5 ) {
                require_once 'Mdal/Ship/Item.php';
                $mdalItem = Mdal_Ship_Item::getDefaultInstance();
                //get card info by id
                $cardInfo = $mdalItem->getCardInfo($this->_shipUser['send_gift']);
                $giftInfo['message'] = $cardInfo['name'] . '１個を手に入れました!';
                $giftInfo['name'] = $cardInfo['name'];
                $giftInfo['introduce'] = $cardInfo['introduce'];
                $giftInfo['img'] = 'diamond';
            }
            else {
                switch ( $this->_shipUser['send_gift'] ) {
                    case 5 :
                        $money = 200;
                        $img = 'bonus_s';
                        break;
                    case 6 :
                        $money = 500;
                        $img = 'bonus_m';
                        break;
                    case 7 :
                        $money = 1000;
                        $img = 'bonus_l';
                        break;
                }
                $giftInfo['message'] = '$' . $money . 'を手に入れました!';
                $giftInfo['name'] = '$' . $money;
                $giftInfo['introduce'] = '$' . $money;
                $giftInfo['img'] = $img;
            }
            
            $this->view->giftInfo = $giftInfo;
        }
        
        $this->render();
    }

    /**
     * index action 
     *
     */
    public function indexAction()
    {
        $uid = $this->getParam('CF_uid', $this->_USER_ID);        
        
        require_once 'Mbll/Ship/Index.php';
        $mbllIndex = new Mbll_Ship_Index();
        
        require_once 'Mbll/Ship/Cache.php';
        Mbll_Ship_Cache::batchReport($this->_USER_ID, $uid);
        Mbll_Ship_Cache::batchPolice($uid);
        //get user current ship info
        require_once 'Mbll/Ship/BatchWork.php';
        $mbllBatch = new Mbll_Ship_BatchWork();   
        if ( $uid != $this->_USER_ID ) {
            $mbllBatch->checkIsland($uid);
        }
        require_once 'Mdal/Ship/User.php';
        $mdalUser = Mdal_Ship_User::getDefaultInstance();
        
        //get user ship info
        $shipUser = $mdalUser->getUserPark($uid);
        if ( !$shipUser ) {
            $shipUser = $this->_shipUser;
            
            if ( $uid != $shipUser['neighbor_left'] && $uid != $shipUser['neighbor_right'] && $uid != $shipUser['neighbor_center'] ) {
                $uid = $this->_USER_ID;
            }
        }
        $this->view->userId = $this->_USER_ID;
        $this->view->isMyShip = $uid == $this->_USER_ID ? 1 : 0;
        
        //get user current park left and right user id
        $lrFriendId = $mbllIndex->getLeftAndRightFriend($this->_USER_ID, $uid);
        $this->view->lrFriendId = $lrFriendId;
        
        //get user ship info
        $userPark = $mbllIndex->getUserPark($this->_USER_ID, $uid);
        $this->view->userPark = $userPark;
        $this->view->shipUser = $userPark['user'];
        
        $this->view->currentShip = $mbllBatch->calculateFee($userPark);
        
        $this->view->APP_ID = $this->_APP_ID;
        $this->view->myShip = $this->_shipUser;
        
        //top image
        //get user island
        require_once 'Mdal/Ship/Island.php';
        $mdalIsland = Mdal_Ship_Island::getDefaultInstance();
        $island = $mdalIsland->getIslandByUser($uid);
        
        //get parking ship
        require_once 'Mdal/Ship/Ship.php';
        $mdalShip = Mdal_Ship_Ship::getDefaultInstance();
        $shipInfo = $mdalShip->getShipDetailInfo($this->_USER_ID, $uid);
        
        if (count($shipInfo) > 0) {
            $path = $this->_ua==3 ? '-AU' : '';
            $this->view->imgurl = Zend_Registry::get('static') . '/apps/ship/img/topimage/' . $island['cav_name'] . '-' . $shipInfo[0]['cav_name'] . $path  . '.gif';
        }
        else {
            $this->view->imgurl = Zend_Registry::get('static') . '/apps/ship/img/topimage/' . $island['cav_name'] . '.gif';
        }
        
        //top flash
        //$flashPath = Mbll_Ship_FlashCache::getNewTopFlash($this->_USER_ID, $uid);
        //$this->view->flashPath = Zend_Registry::get('host') . '/static/apps/ship/flash' . $flashPath;
                
        $this->render();
    }
    
    /**
     * park action 
     *
     */
    public function parkAction()
    {
        $action = $this->getParam('CF_step', 'choiceship');
        $this->view->action = $action;
        
        if ( $action == 'choiceship' ) {
            $location = $this->getParam('CF_loca');
            $parkingUid = $this->getParam('CF_parkingUid');
            
            if ( $parkingUid > 0 ) {
                require_once 'Bll/User.php';
                $parkingUserInfo = Bll_User::getPerson($parkingUid);
                $parkingUserName = $parkingUserInfo->getDisplayName();
            }
            else {
                require_once 'Mdal/Ship/User.php';
                $mdalUser = Mdal_Ship_User::getDefaultInstance();
                $parkingUserInfo = $mdalUser->getUserNeighborPark($parkingUid);
                $parkingUserName = $parkingUserInfo['displayName'];
            }
            
            $this->view->loca = $location;
            $this->view->parkingUid = $parkingUid;
            $this->view->parkingUserName = $parkingUserName;
            
            require_once 'Mbll/Ship/Index.php';
            $mbllIndex = new Mbll_Ship_Index();
            //get user ship list info
            $ships = $mbllIndex->getUserShips($this->_USER_ID);
            $this->view->ships = $ships;
        }
        else if ( $action == 'parking' ) {
            $location = $this->getParam('CF_loca');
            $parkingUid = $this->getParam('CF_parkingUid');
            $user_ship_id = $this->getParam('CF_usid');
                        
            require_once 'Mbll/Ship/Index.php';
            $mbllIndex = new Mbll_Ship_Index();
            //parking
            $result = $mbllIndex->parking($this->_USER_ID, $parkingUid, $user_ship_id, $location);
            $this->view->result = $result;
        }
        
        $this->render();
    }
    
    public function parkingAction()
    {
        info_log(Zend_Json::encode($_GET),'ship');
        $location = $this->getParam('CF_loca');
        $parkingUid = $this->getParam('CF_parkingUid');
        $this->_redirect($this->_baseUrl . '/mobile/ship/park/CF_parkingUid/' . $parkingUid . '/CF_loca/' . $location);
    }
    
    /**
     * stick action 
     *
     */
    public function stickAction()
    {
        $location = $this->getParam('CF_loca');

        require_once 'Mbll/Ship/Index.php';
        $mbllIndex = new Mbll_Ship_Index();
        //stick
        $result = $mbllIndex->stick($this->_USER_ID, $location);
        $this->view->result = $result;
        
        $this->render();
    }
    
    /**
     * report action 
     *
     */
    public function reportAction()
    {
        $parkingUid = $this->getParam('CF_parkingUid');
        $location = $this->getParam('CF_loca');
        
        $action = $this->getParam('CF_step', 'start');
        $this->view->action = $action;
        
        if ( $action == 'start' ) {
            $this->view->parkingUid = $parkingUid;
            $this->view->location = $location;
            
            require_once 'Mdal/Ship/Card.php';
            $mdalCard = Mdal_Ship_Card::getDefaultInstance();
            //get user card count
            $anonymousCardCount = $mdalCard->getUserCardCoutByCid(1, $this->_USER_ID);
            if ( $anonymousCardCount <= 0 ) {
                $this->_redirect($this->_baseUrl . '/mobile/ship/report/CF_parkingUid/'.$parkingUid.'/CF_loca/'.$location.'/CF_step/complete');
                return;
            }
        }
        else if ( $action == 'complete' ) {
            $isAnonymous = $this->getParam('CF_isAnonymous', 0);
            
            require_once 'Mbll/Ship/Index.php';
            $mbllIndex = new Mbll_Ship_Index();
            //report
            $result = $mbllIndex->report($this->_USER_ID, $parkingUid, $location, $isAnonymous);
            $this->view->result = $result;
        }
        
        $this->render();
    }
    
    /**
     * feed action 
     *
     */
    public function feedAction()
    {
        $action = $this->getParam('CF_step', 'minifeed');
        $this->view->action = $action;
        
        require_once 'Mbll/Ship/Index.php';
        $mbllIndex = new Mbll_Ship_Index();
        
        if ( $action == 'minifeed' ) {
            $feedList = $mbllIndex->getMinifeed($this->_USER_ID, $this->_APP_ID);
        }
        else if ( $action == 'newsfeed' ) {
            $feedList = $mbllIndex->getNewsfeed($this->_USER_ID, $this->_APP_ID);
        }
        $this->view->feedList = $feedList;
        
        $this->render();
    }
    
    /**
     * my ship action 
     *
     */
    public function myshipAction()
    {
        //get user ships list
        require_once 'Mbll/Ship/Index.php';
        $mbllIndex = new Mbll_Ship_Index();
        $ships = $mbllIndex->getUserShips($this->_USER_ID);

        $this->view->ships = $ships;
        $this->view->shipCount = count($ships);
        $this->view->shipUser = $this->_shipUser;
        
        $this->render();
    }

    /**
     * my ship action 
     *
     */
    public function setshipAction()
    {
        $userShipId = $this->getParam('CF_userShipId');
             
        require_once 'Mbll/Ship/Index.php';
        $mbllIndex = new Mbll_Ship_Index();
        
        //get ship parking status
        $shipInfo = $mbllIndex->getShipStatusByUserShipId($this->_USER_ID, $userShipId);
        $this->view->shipInfo = $shipInfo;
        
        //get friend count 
        require_once 'Bll/Friend.php';
        $friends = Bll_Friend::getFriends($this->_USER_ID);
        $friendsCount = count($friends);
        
        //can send ship? $canSendShip=0->NO $canSendShip=1->YES
        //user only have one ship
        if ( $this->_shipUser['ship_count'] == 1 ) {
            $canSendShip = 0;
        }
        //from last time when send ship to now less than 30 days
        else if (time() - $this->_shipUser['send_ship_time'] < 30*24*3600) {
            $canSendShip = 0;
        }
        else if ($friendsCount == 0) {
            $canSendShip = 0;
        }
        else {
            $canSendShip = 1;
        }

        $this->view->canSendShip = $canSendShip;
        
        $this->render();
    }
    
    /**
     * send ship action 
     *
     */
    public function sendshipAction()
    {
        $action = $this->getParam('CF_step', 'start');
        $this->view->action = $action;
        
        if ( $action == 'start' ) {
            $userShipId = $this->getParam('CF_userShipId');
            
            //get send ship infomation
            require_once 'Mdal/Ship/Ship.php';
            $mdalShip = Mdal_Ship_Ship::getDefaultInstance();
    
            //get ship info by user ship id
            $shipInfo = $mdalShip->getShipByUserShipId($userShipId);
            $this->view->shipInfo = $shipInfo;
            
            //get user friends list
            require_once 'Bll/Friend.php';
            $fids = Bll_Friend::getFriends($this->_USER_ID);
    
            //get can send friend list
            if ( !empty($fids) ) {
                require_once 'Mdal/Ship/User.php';
                $mdalUser = Mdal_Ship_User::getDefaultInstance();
                $appFriends = $mdalUser->getAppFriendsArray($fids);
                //get friend send ship info
                $friendsInfo = $mdalShip->getSendShipFriendsInfo($shipInfo['ship_id'], $fids);
    
                //$canSendFriends = array();
                for ( $i = 0, $iCount = count($friendsInfo); $i < $iCount; $i++ ) {
                    if ( $friendsInfo[$i]['ship_count'] >= 8 && $friendsInfo[$i]['count1'] >= 3 && time() - $friendsInfo[$i]['receive_ship_time'] <= 30*24*3600 ) {
                        //$canSendFriends[] = $friendsInfo[$i];
                        for ( $j = 0, $jCount = count($appFriends); $j < $jCount; $j++ ) {
                            if ( $appFriends[$j]['uid'] == $friendsInfo[$i]['uid'] ) {
                                unset($appFriends[$j]);
                            }
                        }
                    }
                }
                
                if ( !empty($appFriends) ) {
                    require_once 'Bll/User.php';
                    Bll_User::appendPeople($appFriends);
                }
            }
            $this->view->canSendFriends = $appFriends;
        }
        else if ( $action == 'complete' ) {
            $fid = $this->getParam('CF_fid');
            $userShipId = $this->getParam('CF_userShipId');
            
            
            //get send ship infomation
            require_once 'Mdal/Ship/Ship.php';
            $mdalShip = Mdal_Ship_Ship::getDefaultInstance();
            $shipInfo = $mdalShip->getShipByUserShipId($userShipId);
            
            require_once 'Mbll/Ship/Index.php';
            $mbllIndex = new Mbll_Ship_Index();
            //send friend ship
            $result = $mbllIndex->sendShip($this->_USER_ID, $fid, $userShipId);
            
            require_once 'Bll/User.php';
            $friendInfo = Bll_User::getPerson($fid);
            
            switch ($result) {
                case 1:
                    $message = $shipInfo['name'] . '号を' . $friendInfo->getDisplayName() . '海賊団にプレゼントしました。';
                    break;
                case -2:
                    $message = '8台の船を所有している友達にはﾌﾟﾚｾﾞﾝﾄできません。';
                    break;
                case -3:
                    $message = 'マイミクはすでに同じの海賊船を所有しています、援軍を送ることはできませんでした！';
                    break;
                case -4:
                    $message = '前回のﾌﾟﾚｾﾞﾝﾄから1ヵ月以上経っていないため、ﾌﾟﾚｾﾞﾝﾄできませんでした。';
                    break;
                case -5:
                    $message = '今月、誰かがﾌﾟﾚｾﾞﾝﾄしていたため、ﾌﾟﾚｾﾞﾝﾄできませんでした。';
                    break;
                case -6:
                    $message = '1台しか持っていない船を友達にプレゼントすることはできません。';
                    break;
                default:
                    $message = 'システムエラー。';
                    break;
            }
            
            $this->view->shipInfo = $shipInfo;
            $this->view->message = $message;
            $this->view->result = $result;
        }
        
        $this->render();
    }
    
    /**
     * rename ship action 
     *
     */
    public function renameshipAction()
    {
        $action = $this->getParam('CF_step', 'start');
        $this->view->action = $action;
        
        $userShipId = $this->getParam('CF_userShipId');
        $userShipId = $userShipId ? $userShipId : $this->getPost('CF_userShipId');
        
        //get send ship infomation
        require_once 'Mdal/Ship/Ship.php';
        $mdalShip = Mdal_Ship_Ship::getDefaultInstance();
        //get ship info by user ship id
        $shipInfo = $mdalShip->getShipByUserShipId($userShipId);
        $this->view->shipInfo = $shipInfo;
        
        if ( $action == 'start' ) {
            $nameError = $this->getParam('CF_nameError', -1);
            $this->view->nameError = $nameError;
            
            $this->view->userShipId = $userShipId;
        }
        else if ( $action == 'complete' ) {
            $shipName = $this->getParam('CF_name');
            
            if ( $shipInfo['uid'] != $this->_USER_ID ) {
                $this->_redirect($this->_baseUrl . '/mobile/ship/myship');
                return;
            }
            
            //if no name, error
            if (rtrim($shipName) == "") {
                $this->_redirect($this->_baseUrl . '/mobile/ship/renameship/CF_userShipId/' . $userShipId . '/CF_nameError/1');
                return;
            }
            
            //check content length,if length over 10
            $truncateShipName = MyLib_String::truncate($shipName, 10);
            if ($truncateShipName != $shipName) {
                $this->_redirect($this->_baseUrl . '/mobile/ship/renameship/CF_userShipId/' . $userShipId . '/CF_nameError/2');
                return;
            }
            
            //update user ship name
            $ship = array('ship_name' => $shipName);
            $mdalShip->updateUserShipByUserShipId($userShipId, $ship);
            
            $this->view->shipName = $shipName;
        }
            
        $this->render();
    }

    /**
     * activation ship action
     *
     */
    public function activationshipAction()
    {
        $action = $this->getParam('CF_step', 'start');
        $this->view->action = $action;
        
        $userShipId = $this->getParam('CF_userShipId');
        require_once 'Mdal/Ship/Ship.php';
        $mdalShip = Mdal_Ship_Ship::getDefaultInstance();
        //get ship info by user ship id
        $shipInfo = $mdalShip->getShipByUserShipId($userShipId);
        $this->view->shipInfo = $shipInfo;
        
        require_once 'Mbll/Ship/Ship.php';
        $mbllShip = new Mbll_Ship_Ship();
        //send friend ship
        $result = $mbllShip->activationShip($this->_USER_ID, $userShipId);
        $this->view->result = $result;
        
        $this->render();    
    }

    /**
     * activation ship action
     *
     */
    public function prohibitshipAction()
    {
        $action = $this->getParam('CF_step', 'start');
        $this->view->action = $action;
        
        $userShipId = $this->getParam('CF_userShipId');
        require_once 'Mdal/Ship/Ship.php';
        $mdalShip = Mdal_Ship_Ship::getDefaultInstance();
        //get ship info by user ship id
        $shipInfo = $mdalShip->getShipByUserShipId($userShipId);
        $this->view->shipInfo = $shipInfo;
        
        require_once 'Mbll/Ship/Ship.php';
        $mbllShip = new Mbll_Ship_Ship();
        //send friend ship
        $result = $mbllShip->prohibitShip($this->_USER_ID, $userShipId);
        $this->view->result = $result;
        
        $this->render();    
    }
    
    /**
     * appoint captain action 
     *
     */
    /*public function appointcaptainAction()
    {
        $action = $this->getParam('CF_step', 'start');
        $this->view->action = $action;
        
        $userShipId = $this->getParam('CF_userShipId');
        
        //get send ship infomation
        require_once 'Mdal/Ship/Ship.php';
        $mdalShip = Mdal_Ship_Ship::getDefaultInstance();
        //get ship info by user ship id
        $shipInfo = $mdalShip->getShipByUserShipId($userShipId);
        $this->view->shipInfo = $shipInfo;
        
        if ( $action == 'start' ) {
            require_once 'Bll/Friend.php';
            $mixiFriendIds = Bll_Friend::getFriends($this->_USER_ID);
            
            require_once 'Mdal/Ship/User.php';
            $mdalUser = Mdal_Ship_User::getDefaultInstance();
            //get app friendids
            $friends = $mdalUser->getAppFriendsArray($mixiFriendIds);
            $friends ? Bll_User::appendPeople($friends, 'uid') : $friends;
            $this->view->friends = $friends;
            $this->view->userShipId = $userShipId;
        }
        else {
            $fid = $this->getParam('CF_fid');
            
            if ( $shipInfo['uid'] != $this->_USER_ID ) {
                $this->_redirect($this->_baseUrl . '/mobile/ship/myship');
                return;
            }
            
            require_once 'Bll/Friend.php';
            $isFriend = Bll_Friend::isFriend($this->_USER_ID, $fid);
            //if no name, error
            if ( !$isFriend ) {
                $this->_redirect($this->_baseUrl . '/mobile/ship/appointcaptain/CF_userShipId/' . $userShipId);
                return;
            }
            
            //update user ship name
            $ship = array('captain_uid' => $fid);
            $mdalShip->updateUserShip($this->_USER_ID, $ship);
            
            require_once 'Bll/User.php';
            $friendInfo = Bll_User::getPerson($fid);
            $this->view->friendInfo = $friendInfo;
            
            require_once 'Mdal/Ship/Feed.php';
            $mdalFeed = new Mdal_Ship_Feed();
            $create_time = date('Y-m-d H:i:s');

            $minifeed = array('uid' => $this->_USER_ID,
                              'template_id' => 69,
                              'actor' => $this->_USER_ID,
                              'target' => $fid,
                              'title' => '{"shipName":"' . $shipInfo['shipName'] . '","captainName":"' . $friendInfo->getDisplayName() . '"}',
                              'create_time' => $create_time);
            $mdalFeed->insertMinifeed($minifeed);
    
            $minifeed['uid'] = $fid;
            $minifeed['template_id'] = 70;
            $minifeed['title'] = '{"shipName":"' . $shipInfo['shipName'] . '"}';
            $mdalFeed->insertMinifeed($minifeed);
        }
        
        $this->render();
    }*/
    
    /**
     * ranking friend action 
     * 
     */
    public function rankingfriendAction()
    {        
        $pageIndex = $this->getParam('CF_page');
        $pageSize = 10;
        
        require_once 'Mdal/Ship/Rank.php';
        $mdalRank = Mdal_Ship_Rank::getDefaultInstance();
        
        require_once 'Bll/Friend.php';
        $fids = Bll_Friend::getFriends($this->_USER_ID);
        $fids = $fids ? $fids : '';
        $userRankNm = $mdalRank->getUserFriendRankNm($this->_USER_ID, $fids);
        $this->view->userRankNm = $userRankNm;
        
        if ( !$pageIndex ) {
            $pageIndex = ceil($userRankNm/$pageSize);
        }
        
        //get rank user
        $rankUser = $mdalRank->getAssetRankFriendUser($this->_USER_ID, $fids, $pageIndex, $pageSize);
        $rankCount = $mdalRank->getRankFriendCount($this->_USER_ID, $fids);
        
        require_once 'Bll/User.php';
        Bll_User::appendPeople($rankUser, 'uid');
        $this->view->rankUser = $rankUser;
        $this->view->rankCount = $rankCount;
        $maxPager = ceil($rankCount / $pageSize);
        
        if ( $pageIndex > $maxPager ) {
            $this->_redirect($this->_baseUrl . '/mobile/ship/rankingfriend');
            return;
        }
        
        //get pager info
        $this->view->pager = array('count' => $rankCount,
                                   'pageIndex' => $pageIndex,
                                   'requestUrl' => 'mobile/ship/rankingfriend',
                                   'pageSize' => $pageSize,
                                   'maxPager' => $maxPager,
                                   );
                                   
        $this->view->shipUser = $this->_shipUser;
                                   
        $this->render();
    }

    /**
     * ranking all action 
     * 
     */
    public function rankingallAction()
    {
        //get top 10 user
        $rankUser = Mbll_Ship_Cache::getAllRankingList(1);

        require_once 'Bll/User.php';
        Bll_User::appendPeople($rankUser, 'uid');
        $this->view->rankUser = $rankUser;
        $this->view->shipUser = $this->_shipUser;
        
        $this->render();
    }
    
    /**
     * ship shop action 
     *
     */
    public function shipfactoryAction()
    {
        $pageIndex = $this->getParam('CF_page', 1);
        $pageSize = 8;
        
        require_once 'Mdal/Ship/User.php';
        $mdalUser = new Mdal_Ship_User();
        $this->view->userAllShipCount = $mdalUser->getUserUsableShipCount($this->_USER_ID);
        
        require_once 'Mdal/Ship/Ship.php';
        $mdalShip = Mdal_Ship_Ship::getDefaultInstance();
        //get ship list,count
        $shipList = $mdalShip->getShipListByUid($this->_USER_ID, $pageIndex, $pageSize);
        $shipCount = $mdalShip->getShipCount();
        
        //get user ship max price
        $maxPrice = $mdalShip->getUserShipMaxPrice($this->_USER_ID);
        
        $this->view->maxPrice = $maxPrice;
        $this->view->shipList = $shipList;
        $this->view->shipUser = $this->_shipUser;
        $this->view->shipCount = $shipCount;
        
        $startNm = ($pageIndex - 1)*$pageSize + 1;
        $endNm = $startNm + $pageSize > $shipCount ? $shipCount : $startNm + $pageSize - 1;
        $this->view->startNm = $startNm;
        $this->view->endNm = $endNm;
        
        //get pager info
        $this->view->pager = array('count' => $shipCount,
                                   'pageIndex' => $pageIndex,
                                   'requestUrl' => 'mobile/ship/shipfactory',
                                   'pageSize' => $pageSize,
                                   'maxPager' => ceil($shipCount / $pageSize)
                                   );
        $this->render();
    }
    
    /**
     * buy ship action 
     *
     */
    public function buyshipAction()
    {
        $action = $this->getParam('CF_step', 'start');
        $this->view->action = $action;
        
        if ( $action == 'start' ) {
            $shipId = $this->getParam('CF_shipId');
            
            require_once 'Mdal/Ship/Ship.php';
            $mdalShip = Mdal_Ship_Ship::getDefaultInstance();
            //get ship info by ship id
            $shipInfo = $mdalShip->getShipInfo($shipId);
            $this->view->shipInfo = $shipInfo;
            
            //get remain asset
            if ( $shipInfo['diamond'] > 0 ) {
                $remainAssetDiamond = $this->_shipUser['asset_diamond'] - $shipInfo['diamond'];
            }
            if ( $shipInfo['price'] > 0 ) {
                $remainAssetPrice = $this->_shipUser['asset'] - $shipInfo['price'];
            }
            
            $this->view->remainAssetDiamond = $remainAssetDiamond;
            $this->view->remainAssetPrice = $remainAssetPrice;
            $this->view->shipUser = $this->_shipUser;
        }
        else if ( $action == 'complete' ) {
            $shipId = $this->getParam('CF_shipId');
            $payType = $this->getParam('CF_payType');
            
            require_once 'Mbll/Ship/Ship.php';
            $mbllShip = new Mbll_Ship_Ship();
            //buy ship
            $result = $mbllShip->buyShip($this->_USER_ID, $shipId, $payType, $this->_APP_ID);
            $this->view->result = $result;
        }
                
        $this->render();
    }

    /**
     * change ship action 
     *
     */
    public function changeshipAction()
    {
        $action = $this->getParam('CF_step', 'start');
        $this->view->action = $action;
        
        if ( $action == 'start' ) {
            $shipId = $this->getParam('CF_shipId');
            $this->view->shipId = $shipId;
            
            require_once 'Mdal/Ship/Ship.php';
            $mdalShip = Mdal_Ship_Ship::getDefaultInstance();
            //get user ship list
            $shipList = $mdalShip->getUserShipList($this->_USER_ID);
            $this->view->shipList = $shipList;
            
            //get new ship info by ship id
            $shipInfo = $mdalShip->getShipInfo($shipId);
            $this->view->shipInfo = $shipInfo;
            
            $this->view->shipUser = $this->_shipUser;
        }
        else if ( $action == 'confirm' ) {
            $shipId = $this->getParam('CF_shipId');
            $userShipId = $this->getParam('CF_userShipId');
            $this->view->shipId = $shipId;
            $this->view->userShipId = $userShipId;
            
            require_once 'Mdal/Ship/Ship.php';
            $mdalShip = Mdal_Ship_Ship::getDefaultInstance();
            
            //get new ship info by ship id
            $shipInfo = $mdalShip->getShipInfo($shipId);
            $this->view->shipInfo = $shipInfo;
            
            //get user shipinfo by user ship id
            $userShipInfo = $mdalShip->getShipByUserShipId($userShipId);
            $this->view->userShipInfo = $userShipInfo;
            
            //get remain asset
            $remainAsset = $this->_shipUser['asset'] + floor($userShipInfo['price'] * 0.9) - $shipInfo['price'];
            $this->view->remainAsset = $remainAsset;
            $this->view->shipUser = $this->_shipUser;
        }
        else if ( $action == 'complete' ) {
            $shipId = $this->getParam('CF_shipId');
            $userShipId = $this->getParam('CF_userShipId');
            
            require_once 'Mbll/Ship/Ship.php';
            $mbllShip = new Mbll_Ship_Ship();
            //change ship
            $result = $mbllShip->changeShip($this->_USER_ID, $userShipId, $shipId);
            $this->view->result = $result;
        }
        
        $this->render();
    }
    
    /**
     * repair action 
     *
     */
    public function repairAction()
    {
        $action = $this->getParam('CF_step', 'start');
        $this->view->action = $action;
        
        if ( $action == 'start' ) {
            $userShipId = $this->getParam('CF_userShipId');
            
            require_once 'Mdal/Ship/Ship.php';
            $mdalShip = Mdal_Ship_Ship::getDefaultInstance();
            //get user ship info
            $shipInfo = $mdalShip->getShipByUserShipId($userShipId);
            $this->view->shipInfo = $shipInfo;
            $this->view->needAsset = $shipInfo['price'] * 0.2;
            $this->view->shipUser = $this->_shipUser;
            $this->view->remainAsset = $this->_shipUser['asset'] - $shipInfo['price'] * 0.2;
        }
        else if ( $action == 'complete' ) {
            $userShipId = $this->getParam('CF_userShipId');
            
            require_once 'Mbll/Ship/Ship.php';
            $mbllShip = new Mbll_Ship_Ship();
            //repair ship
            $result = $mbllShip->repair($this->_USER_ID, $userShipId);
            $this->view->result = $result;
        }
        
        $this->render();
    }
    
    /**
     * item shop action 
     *
     */
    public function itemshopAction()
    {
        $pageIndex = $this->getParam('CF_page', 1);
        $pageSize = 8;
        
        require_once 'Mdal/Ship/Item.php';
        $mdalItem = Mdal_Ship_Item::getDefaultInstance();
        //get store item list
        $storeList = $mdalItem->getStoreList($pageIndex, $pageSize);
        $this->view->storeList = $storeList;
        $this->view->shipUser = $this->_shipUser;
        
        $this->render();
    }
    
    /**
     * my item action 
     *
     */
    public function myitemAction()
    {
        require_once 'Mdal/Ship/Item.php';
        $mdalItem = Mdal_Ship_Item::getDefaultInstance();
        //get user item list
        $itemList = $mdalItem->getUserItemList($this->_USER_ID);
        $this->view->itemList = $itemList;
        $this->view->shipUser = $this->_shipUser;
        $this->view->nowTime = time();
        
        $this->render();
    }
    
    /**
     * buy item action 
     *
     */
    public function buyitemAction()
    {
        $action = $this->getParam('CF_step', 'start');
        $this->view->action = $action;
        
        if ( $action == 'start' ) {
            $sid = $this->getParam('CF_sid');
            
            require_once 'Mdal/Ship/Item.php';
            $mdalItem = Mdal_Ship_Item::getDefaultInstance();
            //get card info by id
            $cardInfo = $mdalItem->getCardInfo($sid);
            $this->view->cardInfo = $cardInfo;
            
            if ( $cardInfo['price'] > 0 ) {
                $remainAsset = $this->_shipUser['asset'] - $cardInfo['price'];
                $assetType = 1;
            }
            else if ( $cardInfo['diamond'] > 0 ) {
                $remainAsset = $this->_shipUser['asset_diamond'] - $cardInfo['diamond'];
                $assetType = 2;
            }
            
            $this->view->remainAsset = $remainAsset;
            $this->view->assetType = $assetType;
            $this->view->shipUser = $this->_shipUser;
        }
        else if ( $action == 'complete' ) {
            $sid = $this->getParam('CF_sid');
            require_once 'Mbll/Ship/Item.php';
            $mbllItem = new Mbll_Ship_Item();
            //buy item
            $result = $mbllItem->buyItem($this->_USER_ID, $sid);
            $this->view->result = $result;
        }
        
        $this->render();
    }

    /**
     * use item action 
     *
     */
    public function useitemAction()
    {
        $action = $this->getParam('CF_step', 'start');
        $this->view->action = $action;
        
        if ( $action == 'start' ) {
            $sid = $this->getParam('CF_sid');
            
            require_once 'Mdal/Ship/Item.php';
            $mdalItem = Mdal_Ship_Item::getDefaultInstance();
            //get card info by id
            $cardInfo = $mdalItem->getCardInfo($sid);
            $this->view->cardInfo = $cardInfo;
        }
        else if ( $action == 'complete' ) {
            $sid = $this->getParam('CF_sid');
            require_once 'Mbll/Ship/Item.php';
            $mbllItem = new Mbll_Ship_Item();
            //buy item
            $result = $mbllItem->useItem($this->_USER_ID, $sid);
            $this->view->result = $result;
        }
        
        $this->render();
    }

    /**
     * island action 
     *
     */
    public function islandAction()
    {
        $pageIndex = $this->getParam('CF_page', 1);
        $pageSize = 8;
        
        require_once 'Mdal/Ship/Item.php';
        $mdalItem = Mdal_Ship_Item::getDefaultInstance();
        //get island list
        $islandList = $mdalItem->getIslandList($pageIndex, $pageSize);
        //get island count
        $islandCount = $mdalItem->getIslandCount();
        $this->view->islandList = $islandList;
        $this->view->shipUser = $this->_shipUser;
        
        //get pager info
        $this->view->pager = array('count' => $islandCount,
                                   'pageIndex' => $pageIndex,
                                   'requestUrl' => 'mobile/ship/island',
                                   'pageSize' => $pageSize,
                                   'maxPager' => ceil($islandCount / $pageSize)
                                   );
        
        $this->render();
    }
    
    /**
     * myisland action 
     *
     */
    public function myislandAction()
    {
        require_once 'Mdal/Ship/Item.php';
        $mdalItem = Mdal_Ship_Item::getDefaultInstance();
        //get island list
        $islandList = $mdalItem->getUserIslandList($this->_USER_ID);
        
        $this->view->islandList = $islandList;
        $this->view->shipUser = $this->_shipUser;
        
        $this->render();
    }
    
    /**
     * buy island action 
     *
     */
    public function buyislandAction()
    {
        $aciont = $this->getParam('CF_step', 'start');
        $this->view->action = $aciont;
        
        if ( $aciont == 'start' ) {
            $islandId = $this->getParam('CF_id');
            
            require_once 'Mdal/Ship/Item.php';
            $mdalItem = Mdal_Ship_Item::getDefaultInstance();
            //get island info by id
            $islandInfo = $mdalItem->getIslandInfo($islandId);
            $this->view->islandInfo = $islandInfo;
            if ( $islandInfo['price'] > 0 ) {
                $this->view->remainAssetPrice = $this->_shipUser['asset'] - $islandInfo['price'];
            }
            if ( $islandInfo['diamond'] > 0 ) {
                $this->view->remainAssetDiamond = $this->_shipUser['asset_diamond'] - $islandInfo['diamond'];
            }
            
            //get user island info
            $this->view->userIslandInfo = $mdalItem->getIslandInfo($this->_shipUser['background']);
            $this->view->shipUser = $this->_shipUser;
            
        }
        else if ( $aciont == 'complete' ) {
            $islandId = $this->getParam('CF_id');
            $payType = $this->getParam('CF_payType');
            
            require_once 'Mbll/Ship/Item.php';
            $mbllItem = new Mbll_Ship_Item();
            //buy island
            $result = $mbllItem->buyIsland($this->_USER_ID, $islandId, $payType, $this->_APP_ID);
            $this->view->result = $result;
        }
        
        $this->render();
    }

    /**
     * change island action 
     *
     */
    public function changeislandAction()
    {
        $islandId = $this->getParam('CF_id');
        
        require_once 'Mbll/Ship/Item.php';
        $mbllItem = new Mbll_Ship_Item();
        //change user island
        $result = $mbllItem->changeIsland($this->_USER_ID, $islandId);
        $this->view->result = $result;
        
        $this->render();
    }
    
    /**
     * t action 
     *
     */
    public function topAction()
    {
        require_once 'Mbll/Ship/Cache.php';
        Mbll_Ship_Cache::batchReport($this->_USER_ID, $this->_USER_ID);
        Mbll_Ship_Cache::batchPolice($this->_USER_ID);
        
        //get user ships list
        require_once 'Mbll/Ship/Index.php';
        $mbllIndex = new Mbll_Ship_Index();
        $ships = $mbllIndex->getUserShips($this->_USER_ID);

        $this->view->ships = $ships;
        $this->view->shipCount = count($ships);
        $this->view->shipUser = $this->_shipUser;
        
        require_once 'Mdal/Ship/Ship.php';
        $mdalShip = Mdal_Ship_Ship::getDefaultInstance();
        //get parking info
        $parkingInfo = $mdalShip->getPakingLocation($this->_USER_ID);
        if ( $parkingInfo ) {
            $this->view->showParking = 1;
        }
        
        require_once 'Bll/Friend.php';
        $fids = Bll_Friend::getFriends($this->_USER_ID);
        $fids = $fids ? $fids : '';
        require_once 'Mdal/Ship/Rank.php';
        $mdalRank = Mdal_Ship_Rank::getDefaultInstance();
        $userRankNm = $mdalRank->getUserFriendRankNm($this->_USER_ID, $fids);
        $this->view->userRankNm = $userRankNm;
        
        $this->render();
    }
    
    /**
     * help action 
     *
     */
    public function helpAction()
    {
        $parm = $this->getParam('CF_help', 'index');
        $this->view->parm = $parm;

        $this->render();
    }

    /**
     * invite action 
     *
     */
    public function inviteAction()
    {
        
        $this->render();
    }
    
    /**
     * error action 
     *
     */
    public function errorAction()
    {        
        $this->render();
    }
    
    public function startAction()
    {
        $pid = $this->getParam('CF_parkingUid');
        $swf = Mbll_Ship_FlashCache::getNewFlash($this->_USER_ID, $pid, $this->_APP_ID);
        
        ob_end_clean();
        ob_start();
        header("Accept-Ranges: bytes");
        header("Cache-Control: no-cache, must-revalidate");
        header("Content-Type: application/x-shockwave-flash");
        
        echo $swf;
        
        exit;
    }
    
    public function topflashAction()
    {
        $pid = $this->getParam('CF_parkingUid');
        $swf = Mbll_Ship_FlashCache::getNewTopFlash($this->_USER_ID, $pid);
        
        ob_end_clean();
        ob_start();
        header("Accept-Ranges: bytes");
        header("Cache-Control: no-cache, must-revalidate");
        header("Content-Type: application/x-shockwave-flash");
        
        echo $swf;
        
        exit;
    }
    
    /**
     * top image action
     *
     */
    public function topimageAction()
    {
        $pid = $this->getParam('CF_uid', $this->_USER_ID);
        require_once 'Mbll/Ship/Image.php';
        Mbll_Ship_Image::getTopImage($this->_USER_ID, $pid, $this->_ua);
        
        exit;    
    }
    
    /**
     * magic function
     *   if call the function is undefined,then forward to not found
     *
     * @param string $methodName
     * @param array $args
     * @return void
     */
    function __call($methodName, $args)
    {
        return $this->_redirect($this->_baseUrl . '/mobile/ship/error');
    }
}
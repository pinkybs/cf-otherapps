<?php

/** @see MyLib_Zend_Controller_Action_Mobile.php */
require_once 'MyLib/Zend/Controller/Action/Mobile.php';

/**
 * Mobile Game Controller(modules/mobile/controllers/GameController.php)
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/09/08   xial
 */
class Mobile_HotelController extends MyLib_Zend_Controller_Action_Mobile
{
	protected $_pageSize = 10;
	protected $_userInfo = null;
    /**
     * initialize object
     * override
     * @return void
     */
    public function init()
    {
        parent::init();
    }

    public function indexAction()
    {
        $uid = $this->_user->getId();
        require_once 'Dal/Hotel/Huser.php';
        $dalHuser = Dal_Hotel_Huser::getDefaultInstance();
        $isIn = $dalHuser->isInHotel($uid);

        //check is need guid
        if (!$isIn) {
            //add user to Hotel
            require_once 'Bll/Hotel/Huser.php';
            $bllHuser = new Bll_Hotel_Huser();

            $userInfo = array('uid'=>$uid,
                              'tutorial_result'=>0,
                              'money'=>'1000000',
                              'location'=>'0',
                              'join_time'=>time());

            $re = $bllHuser->insertHuser($userInfo);
            $this->_redirect($this->_baseUrl . '/mobile/hotel/guide');
            return;
        }

        //get hotel info
        $isNeedGuid = false;
        $hotelInfo = $dalHuser->getUserInfoById($uid);
        $roomInfo = $dalHuser->getRoomInfoById($uid);
        if (empty($hotelInfo['location'])) {
            $isNeedGuid = true;
        }
        else if (empty($roomInfo['room1']) || empty($roomInfo['restaurant']) || empty($roomInfo['reception'])) {
            $isNeedGuid = true;
        }

        if ($isNeedGuid) {
            $this->_redirect($this->_baseUrl . '/mobile/hotel/guide');
            return;
        }

        $this->_redirect($this->_baseUrl . '/mobile/hotel/profile');
    }


     /**
     * deipatch
     *
     */
    function preDispatch()
    {
    	$uid = $this->_user->getId();
        $profileUid = $this->getParam('CF_uid');

        if (empty($profileUid) || $uid == $profileUid) {
            $profileUid = $uid;
            require_once 'Bll/Hotel/Huser.php';
            $bllHuser = new Bll_Hotel_Huser();
            $bllHuser->handleUpdate($profileUid);
        }

        require_once 'Dal/Hotel/Friend.php';
        $dalFriend = Dal_Hotel_Friend::getDefaultInstance();
        $count = $dalFriend->getLearnCountById($uid);
        $myspylist = $dalFriend->getMySpyListById($profileUid, 1, $count);
        foreach ($myspylist as $value) {
        	if ($value['fid'] != 0) {
	        	if (((time() - $value['create_time']) % 3600) >= 12) {
	                require_once 'Bll/Hotel/Friend.php';
	                $bllFriend = new Bll_Hotel_Friend();
	                $re = $bllFriend->cbkLearner($profileUid, $value['index']);
	            }
        	}
        }

       /* require_once 'Dal/Hotel/Huser.php';
        $dalHuser = Dal_Hotel_Huser::getDefaultInstance();
        $userInfo = $dalHuser->getUserInfoById($uid);
        $roomInfo = $dalHuser->getRoomInfoById($uid);

        $userInfo['stats_level'] = $roomInfo['manager'];
        //user info
        $this->_userInfo = $dalHuser->getUserInfoById($uid);
        $this->_userInfo['format_money'] = number_format($this->_userInfo['money']);
        Bll_User::appendPerson($this->_userInfo, 'uid');*/
    }

    /**
     * guide action
     *
     */
    public function guideAction()
    {
        $uid = $this->_user->getId();
        require_once 'Dal/Hotel/Huser.php';
        $dalHuser = Dal_Hotel_Huser::getDefaultInstance();
        require_once 'Bll/Hotel/Huser.php';
        $bllHuser = new Bll_Hotel_Huser();
        $hotelInfo = $dalHuser->getUserInfoById($uid);
        $roomInfo = $dalHuser->getRoomInfoById($uid);

        $guideNo = '';
        $location = $this->getParam('CF_loc');
        $reception = $this->getParam('CF_rec');
        $restaurant = $this->getParam('CF_res');
        $room1 = $this->getParam('CF_room1');
        //do step 1 : set location
        if (!empty($location) && empty($hotelInfo['location'])) {
            $dalHuser->upHuser($uid, array('location' => $location));
            $hotelInfo['location'] = $location;
            $guideNo = '2';
            $this->view->msg = 'ローカルを選択しました';
        }
        //do step 2 : set reception
        else if (!empty($reception) && empty($roomInfo['reception'])) {
            //$dalHuser->updateHotelRoom($uid, array('reception' => 1));
            $bllHuser->upBuild($uid, 'reception');
            $roomInfo['reception'] = 1;
            $guideNo = '3';
            $this->view->msg = '受付を雇いました';
        }
        //do step 3 : set restaurant
        else if (!empty($restaurant) && empty($roomInfo['restaurant'])) {
            //$dalHuser->updateHotelRoom($uid, array('restaurant' => 1));
            $bllHuser->upBuild($uid, 'restaurant');
            $roomInfo['restaurant'] = 1;
            $guideNo = '4';
            $this->view->msg = 'レストラン担当を雇いました';
        }
        //do step 4 : set room1
        else if (!empty($room1) && empty($roomInfo['room1'])) {
            //$dalHuser->updateHotelRoom($uid, array('room1' => 1));
            $bllHuser->upBuild($uid, 'room1');
            $roomInfo['room1'] = 1;
            $guideNo = '5';
            $this->view->msg = '客室担当を雇いました';
        }
        //show guide
        else {
            if (empty($hotelInfo['location'])) {
                $guideNo = '1';
                $this->view->lstLoc = $dalHuser->listNbLoacation();
            }
            else if (empty($roomInfo['reception'])) {
                $guideNo = '2';
            }
            else if (empty($roomInfo['restaurant'])) {
                $guideNo = '3';
            }
            else if (empty($roomInfo['room1'])) {
                $guideNo = '4';
            }
        }

        if (empty($guideNo)) {
            $this->_redirect($this->_baseUrl . '/mobile/hotel/profile');
            return;
        }

        $this->getProfileInfo($uid);
        $this->view->guideNo = $guideNo;
        $this->render();
    }

    /**
     * profile action
     *
     */
    public function profileAction()
    {
        $uid = $this->_user->getId();
        $profileUid = $this->getParam('CF_uid');
        $isClear = (int)$this->getParam('CF_isClear', 0);
        $isSendLer = (int)$this->getParam('CF_isSendLer', 0);

        $isShow = 0;
        if (empty($profileUid) || $uid == $profileUid) {
        	require_once 'Bll/Hotel/Huser.php';
            $bllHuser = new Bll_Hotel_Huser();
            $bllHuser->handleUpdate($uid);         //update all over_time process

            require_once 'Dal/Hotel/Friend.php';
	        $dalFriend = Dal_Hotel_Friend::getDefaultInstance();
	        $emenylist = $dalFriend->getEnemyListById($uid);

	        $this->view->emenylist = $emenylist;

            $profileUid = $uid;
            $isShow = 1;
        } else {
        	require_once 'Dal/Hotel/Friend.php';
            $dalFriend = Dal_Hotel_Friend::getDefaultInstance();
	        $hasIdleLearner = $dalFriend->hasLearner($uid);
	        $noLearnerAtFriend = $dalFriend->noLearnerAt($uid,  $profileUid);
	        //$friendHasPlace = $dalFriend->friendHasPlace($profileUid);

	        if ($hasIdleLearner && $noLearnerAtFriend) {
	            $this->view->result = 1;
	        }
        }

        if ($isClear) {
            $message = $this->clear($profileUid);
            $this->view->clearMessage = $message;
        }

        if ($isSendLer) {
            $sendMessage = $this->sendlearner($profileUid);
            $this->view->sendMessage = $sendMessage;
        }

        require_once 'Dal/Hotel/Huser.php';
        $dalHuser = Dal_Hotel_Huser::getDefaultInstance();

        $prevId = $dalHuser->getNeighberUid($profileUid, 'prev');
        if (empty($prevId)) {
              $prevId = $dalHuser->getNeighberUid($profileUid, 'last');
        }
        $nextId = $dalHuser->getNeighberUid($profileUid, 'next');
        if (empty($nextId)) {
              $nextId = $dalHuser->getNeighberUid($profileUid, 'first');
        }

        $this->view->pager = array('requestUrl' => 'mobile/hotel/profile',
                                   'prevId' => $prevId,
                                   'nextId' => $nextId,
                                   );

        $this->view->isShow = $isShow;
        $this->getProfileInfo($profileUid);
        $this->render();
    }

    /**
     * boss profile action
     *
     */
    public function bossprofileAction()
    {
        $uid = $this->_user->getId();
        $profileUid = $this->getParam('CF_uid');
        if (empty($profileUid) || $uid == $profileUid) {
            $profileUid = $uid;
        }

        $this->getProfileInfo($profileUid);
        $this->render();
    }

    /**
     * manager profile action
     *
     */
    public function managerprofileAction()
    {
        $uid = $this->_user->getId();
        $profileUid = $this->getParam('CF_uid');
        if (empty($profileUid) || $uid == $profileUid) {
            $profileUid = $uid;
        }

        /*require_once 'Dal/Hotel/Huser.php';
        $dalHuser = Dal_Hotel_Huser::getDefaultInstance();
        $result = $dalHuser->getProcessInfo($uid, 1);*/
        $roomAry = array('room1', 'room2' , 'room3');
        //$this->teching(1, $roomAry);
        foreach ($roomAry as $value) {
            if ($value == $result['name']) {
                $this->view->techName = $result['name'];
                $sendTime = (intval($result['over_time']) - intval(time()));
                //$this->getTechTime($sendTime);
                $strTime = '';
                if ($sendTime > 3600) {
                    $strTime = $sendTime % 3600 . '时' . strftime('%M', $sendTime / 3600). '分' . strftime('%S', $sendTime / 3600) . '秒';
                } else if ($sendTime > 60){
                    $strTime = strftime('%M', $sendTime) . '分' . strftime('%S', $sendTime) . '秒';
                } else {
                   $strTime = strftime('%S', $sendTime) . '秒';
                }
                $this->view->time =  $strTime;
            }
        }
        $this->view->lv = 1;
        $this->getProfileInfo($profileUid);
        $this->render();
    }

    /**
     * front profile action
     *
     */
    public function frontprofileAction()
    {
        $uid = $this->_user->getId();
        $profileUid = $this->getParam('CF_uid');
        if (empty($profileUid) || $uid == $profileUid) {
            $profileUid = $uid;
        }

        //getLivingInByLv
        $roomAry = array('desk');
        $this->teching(2, $roomAry);

        $this->view->lv = 1;
        $this->getProfileInfo($profileUid);
        $this->render();
    }

    /**
     * restaurant profile action
     *
     */
    public function restaurantprofileAction()
    {
        $uid = $this->_user->getId();
        $profileUid = $this->getParam('CF_uid');
        if (empty($profileUid) || $uid == $profileUid) {
            $profileUid = $uid;
        }
        $roomAry = array('restaurant');
        $this->teching(1, $roomAry);

        $this->view->lv = 1;
        $this->getProfileInfo($profileUid);
        $this->render();
    }

    public function rankingAction()
    {
        $uid = $this->_user->getId();
        $colname = $this->getParam('CF_colname', 'money');
        //$pos 1: in friend 2: in mixi user
        $pos = $this->getParam('CF_pos', 1);
        $page = $this->getParam('CF_page', 1);

        require_once 'Dal/Hotel/Huser.php';
        $dalHuser = Dal_Hotel_Huser::getDefaultInstance();

        require_once 'Bll/Friend.php';
        $fids = Bll_Friend::getFriends($uid);

        $rankArray = $dalHuser->rank($colname, $page, $this->_pageSize, $pos, $uid, $fids);

        require_once 'Bll/User.php';
        Bll_User::appendPeople($rankArray, 'uid');

        $count = $dalHuser->getAllAppUserCount();
        if ($rankArray != null && $colname == 'money') {
	        foreach ($rankArray as $key => $value) {
	            $rankArray[$key]['format_money'] = number_format($value['money']);
	        }
        }

        if ($count && !empty($rankArray)) {
			$startCount = ($page - 1) * $this->_pageSize + 1;
			if (count($rankArray) == '10') {
			     $endCount = $page * $this->_pageSize;
			}
			else {
			     $endCount = $startCount + count($rankArray) - 1;
			}
			$listCount = array('startCount' => $startCount, 'endCount' => $endCount );
        }

        $this->view->pager = array('count' => $count,
                                   'pageIndex' => $page,
                                   'requestUrl' => 'mobile/hotel/ranking',
                                   'pageSize' => $this->_pageSize,
                                   'maxPager' => ceil($count / $this->_pageSize),
                                   'pageParam' => '&CF_colname=' . $colname . '&CF_pos=' . $pos
                                   );
        $this->view->listCount = $listCount;
        $this->view->rankInfo = $rankArray;
        $this->view->pos = $pos;
        $this->view->uid = $uid;
        $this->view->count = $count;
        $this->view->colname = $colname;
        $this->render();
    }

    /**
     * hotel shop
     *
     */
    public function hotelshopAction()
    {
    	$uid = $this->_user->getId();
        //stroe type
        $type = (int)$this->getParam('CF_type', 1);
        $page = (int)$this->getParam('CF_page', 1);
        $pageSize = 5;

    	require_once 'Dal/Hotel/Item.php';
        $dalItem = Dal_Hotel_Item::getDefaultInstance();

        $this->view->uid = $uid;
        $this->getProfileInfo($uid);

        //store info
        $shoplist = $dalItem->shopShow($type, $page, $pageSize);
        $count = $dalItem->cntStore();
        if ($shoplist != null) {
            foreach ($shoplist as $key => $value) {
                $shoplist[$key]['format_price'] = number_format($value['price']);
            }
        }

        /*if ($count && !empty($shoplist)) {
            $startCount = ($page - 1) * $pageSize + 1;
            if (count($shoplist) == '5') {
                 $endCount = $page * $pageSize;
            }
            else {
                 $endCount = $startCount + count($shoplist) - 1;
            }
            $listCount = array('startCount' => $startCount, 'endCount' => $endCount );
        }
        $this->view->listCount = $listCount;*/

        $this->view->pager = array('count' => $count,
                                   'pageIndex' => $page,
                                   'requestUrl' => 'mobile/hotel/hotelshop',
                                   'pageSize' => $pageSize,
                                   'maxPager' => ceil($count / $pageSize),
                                   'pageParam' => '&CF_type=' . $type
                                   );

        $this->view->shoplist = $shoplist;
        $this->view->nickname = $this->_user->getDisplayname();
        $this->render();
    }

    public function buystoreconfirmAction()
    {
        $uid = $this->_user->getId();
        $sid = (int)$this->getParam('CF_storeId');
        $model = (int)$this->getParam('CF_model');

        if (empty($sid)) {
            $this->_redirect($this->_baseUrl . '/mobile/hotel/profile');
            return;
        }

        if ($model) {
	        require_once 'Bll/Hotel/Item.php';
	        $bllItem = new Bll_Hotel_Item();
	        $result = $bllItem->buyItem($uid, $sid);
	        if ($result['result'] == 1 ) {
	            $this->view->message = '*******購入OK';
	        }
        }

        require_once 'Dal/Hotel/Item.php';
        $dalItem = Dal_Hotel_Item::getDefaultInstance();
        $store = $dalItem->getStoreById($sid);
        $store['format_price'] = number_format($store['price']);

        $this->getProfileInfo($uid);

        $this->view->store = $store;
        $this->view->uid = $uid;
        $this->view->sid = $sid;
        $this->view->nickname = $this->_user->getDisplayname();
        $this->render();
    }

    public function buystorefinishAction()
    {
        $uid = $this->_user->getId();
        $sid = (int)$this->getParam('CF_sid', 1);
        require_once 'Bll/Hotel/Item.php';
        $bllItem = new Bll_Hotel_Item();
        $result = $bllItem->buyItem($uid,$sid);
        if ($result['result'] != 1 ) {
        	return;
        }

        $this->getProfileInfo($uid);
        $this->view->nickname = $this->_user->getDisplayname();
        $this->render();
    }

    public function roomlistAction()
    {
        $uid = $this->_user->getId();
        /*$profileUid = $this->getParam('CF_uid');
        if (empty($profileUid) || $uid == $profileUid) {
            $profileUid = $uid;
        }*/
        $type = 1;//1 room, 2 manager
        require_once 'Dal/Hotel/Huser.php';
        $dalHuser = Dal_Hotel_Huser::getDefaultInstance();
        $roomlist = $dalHuser->getRoomlist($uid);
        $result = $dalHuser->getProcessInfo($uid, $type);
        if (intval($result['over_time']) > intval(time())) {
            foreach ($roomlist as $key => $value) {
                if ($key == $result['name']) {
                    $this->view->techName = $result['name'];
                    $sendTime = (intval($result['over_time']) - intval(time()));
                    $this->getTechTime($sendTime);
                }
            }
        }

        $this->getProfileInfo($uid);
        $this->view->uid = $uid;
        $this->view->roomlist = $roomlist;
        $this->view->nickname = $this->_user->getDisplayname();
        $this->render();
    }

    /**
     * room tech
     *
     */
    public function roomupconfirmAction()
    {
    	$uid = $this->_user->getId();
    	$type = $this->getParam('CF_room');
    	$model = (int)$this->getParam('CF_model', 0);
    	if (empty($type)) {
    		$this->_redirect($this->_baseUrl . '/mobile/hotel/profile');
            return;
    	}

    	$roominfo = array();
    	$colName = 'room1';
        $typeName = '客室1';

        require_once 'Dal/Hotel/Huser.php';
        $dalHuser = Dal_Hotel_Huser::getDefaultInstance();
        //type table
        $table = 'hotel_room_type';

        //get destination table name
        switch (substr($type, 0, 4)) {
            case 'room' :
                $table = 'hotel_room_type';
                $roominfo['image_url'] = '/apps/hotel/mobile/img/'. $type . '.gif';
                if ($type == 'roomTwo') {
                    $colName = 'room2';
                    $typeName = '客室2';
                } else if ($type == 'roomThree') {
                    $colName = 'room3';
                    $typeName = '客室3';
                }
                break;

            case 'rest' :
                $table = 'hotel_restaurant_type';
                $roominfo['image_url'] = '/apps/hotel/mobile/img/'. $type . '.gif';
                $colName = 'restaurant';
                $typeName = 'レストラン';
                break;

            case 'mana' :
                $table = 'hotel_manager_type';
                $roominfo['image_url'] = '/apps/hotel/mobile/img/system.gif';
                $colName = 'manager';
                break;

            default :
        }

        //get ROOM currentLv
        $currentLv = $dalHuser->getOneData($uid, $colName, 'hotel_user_room');
        // get update_money and update_time
        $priceAndTime = $dalHuser->getTechnology($table, $currentLv);
        $roominfo['update_money'] = $priceAndTime['update_money'];
        $roominfo['update_time'] = $priceAndTime['update_time'];

        $intType = substr($type, 0, 2) == 'ro' ? 1 : 2;
    	$result =  $dalHuser->getProcessInfo($uid, $intType);

    	if (intval($result['over_time']) < intval(time()) && $model ) {
    	   require_once 'Bll/Hotel/Huser.php';
	        $bllHuser = new Bll_Hotel_Huser();
	        $re = $bllHuser->upBuild($uid, $colName);

	        if ($re['result'] == 1) {
	            $over_time = intval(time()) + intval($re['update_time']);
	            $time = $over_time - intval(time());
                $strTime = '';
                if ($time > 3600) {
                    $strTime = $time / 3600 . '時間' . strftime('%M', $time % 3600). '分間' . strftime('%S', ($time % 3600) % 60) . '秒間';
                } else if ($time > 60){
                    $strTime = strftime('%M', $time) . '分' . strftime('%S', $time) . '秒';
                } else {
                   $strTime = strftime('%S', $time) . '秒';
                }
	            $this->view->message = '昇格完了するまで、あと '. $strTime . '。';
	        }
    	} else {
    	    if (intval($result['over_time']) > intval(time())) {
                $time = intval($result['over_time']) - intval(time());
    	        $strTime = '';
                if ($time > 3600) {
                    $strTime = $time / 3600 . '時間' . strftime('%M', $time % 3600). '分間' . strftime('%S', ($time % 3600) % 60) . '秒間';
                } else if ($time > 60){
                    $strTime = strftime('%M', $time) . '分' . strftime('%S', $time) . '秒';
                } else {
                   $strTime = strftime('%S', $time) . '秒';
                }
	            $teched = 0;
	            $this->view->message = '昇格完了するまで、あと ' . $strTime . '。';
            }
    	}

        $this->view->next_lv = $currentLv + 1;
        $this->view->lv = $currentLv;
        $this->view->roominfo = $roominfo;
        $this->view->type = $type;
        $this->view->teched = $teched;

        $this->view->model = $model;
        $this->getProfileInfo($uid);
        $this->view->typeName = $typeName;
        $this->render();
    }

    public function techupconfirmAction()
    {
        $uid = $this->_user->getId();
        $type = $this->getParam('CF_tech');
        $model = (int)$this->getParam('CF_model', 0);
        if (empty($type)) {
            $this->_redirect($this->_baseUrl . '/mobile/hotel/profile');
            return;
        }

        require_once 'Dal/Hotel/Tech.php';
        $dalTech = Dal_Hotel_Tech::getDefaultInstance();
        require_once 'Dal/Hotel/Huser.php';
        $dalHuser = Dal_Hotel_Huser::getDefaultInstance();
        //get destination table name
        switch (substr($type, 0, 4)) {
            case 'desk' :
                $roominfo['image_url'] = '/apps/hotel/mobile/img/roomTech2.gif';
                $colName = 'desk';
                break;

            case 'cook' :
                $roominfo['image_url'] = '/apps/hotel/mobile/img/foodTech2.gif';
                $colName = 'cook';
                break;

            case 'serv' :
                $roominfo['image_url'] = '/apps/hotel/mobile/img/servTech.gif';
                $colName = 'service';
                break;

            default :
        }

                //get ROOM currentLv
        $currentLv = $dalHuser->getOneData($uid, $colName, 'hotel_user_technology');
        // get update_money and update_time
        $priceAndTime = $dalTech->getTechnology($currentLv, $colName);
        $roominfo['update_money'] = $priceAndTime['update_money'];
        $roominfo['update_time'] = $priceAndTime['update_time'];

        $intType = substr($type, 0, 2) == 'ro' ? 1 : 2;
        $result =  $dalHuser->getProcessInfo($uid, $intType);

        if (intval($result['over_time']) < intval(time()) && $model ) {
           require_once 'Bll/Hotel/Tech.php';
            $bllTech = new Bll_Hotel_Tech();
            $re = $bllTech->upTech($uid, $colName);

            if ($re['result'] == 1) {
                $over_time = intval(time()) + intval($re['update_time']);
                $time = $over_time - intval(time());
                $strTime = '';
                if ($time > 3600) {
                    $strTime = $time / 3600 . '時間' . strftime('%M', $time % 3600). '分間' . strftime('%S', ($time % 3600) % 60) . '秒間';
                } else if ($time > 60){
                    $strTime = strftime('%M', $time) . '分' . strftime('%S', $time) . '秒';
                } else {
                   $strTime = strftime('%S', $time) . '秒';
                }
                $this->view->message = $re['techUpName'] . '昇格完了するまで、あと '. $strTime . '。';
            }
        } else {
            if (intval($result['over_time']) > intval(time())) {
                $time = intval($result['over_time']) - intval(time());
                $strTime = '';
                if ($time > 3600) {
                    $strTime = $time / 3600 . '時間' . strftime('%M', $time % 3600). '分間' . strftime('%S', ($time % 3600) % 60) . '秒間';
                } else if ($time > 60){
                    $strTime = strftime('%M', $time) . '分' . strftime('%S', $time) . '秒';
                } else {
                   $strTime = strftime('%S', $time) . '秒';
                }
                $teched = 0;
                $this->view->message = $result['name'] .'昇格完了するまで、あと ' . $strTime . '。';
            }
        }

        $this->view->next_lv = $currentLv + 1;
        $this->view->lv = $currentLv;
        $this->view->roominfo = $roominfo;
        $this->view->type = $type;
        $this->view->teched = $teched;

        $this->view->model = $model;
        $this->getProfileInfo($uid);
        $this->view->typeName = $typeName;
        $this->render();
    }

    public function roomtechfinishAction()
    {
        $uid = $this->_user->getId();
        $colName = $this->getParam('CF_type', 'room1');
        require_once 'Bll/Hotel/Huser.php';
        $bllHuser = new Bll_Hotel_Huser();
        $re = $bllHuser->upBuild($uid, $colName);
        $over_time = intval($time) + intval($re['update_time']);
        $update_time = $re['update_time'];

        $this->render();
    }

    /**
     * my item list
     *
     */
    public function itemlistAction()
    {
        $uid = $this->_user->getId();
        $type = (int)$this->getParam('CF_type', 1);
        $page = (int)$this->getParam('CF_page', 1);
        $pageSize = 5;

        require_once 'Dal/Hotel/Item.php';
        $dalItem = Dal_Hotel_Item::getDefaultInstance();
        $itemlist = $dalItem->getlistStore($uid, $type, $page, $pageSize);
        $count = $dalItem->getItemCount($uid);
         /*if ($count && !empty($shoplist)) {
            $startCount = ($page - 1) * $pageSize + 1;
            if (count($shoplist) == '5') {
                 $endCount = $page * $pageSize;
            }
            else {
                 $endCount = $startCount + count($shoplist) - 1;
            }
            $listCount = array('startCount' => $startCount, 'endCount' => $endCount );
        }
        $this->view->listCount = $listCount;*/

        $this->view->pager = array('count' => $count,
                                   'pageIndex' => $page,
                                   'requestUrl' => 'mobile/hotel/itemlist',
                                   'pageSize' => $pageSize,
                                   'maxPager' => ceil($count / $pageSize),
                                   'pageParam' => '&CF_type=' . $type
                                   );

        $this->view->itemlist = $itemlist;
        $this->view->count = $count;
        $this->view->nickname = $this->_user->getDisplayname();
        $this->view->uid = $uid;
        $this->render();
    }

    /**
     * 使用しますか？
     *
     */
    public function itemconfirmAction()
    {
        $uid = $this->_user->getId();
        $sid = (int)$this->getParam('CF_sid');
        $model = (int)$this->getParam('CF_model', 2);

        if (empty($sid)) {
            $this->_redirect($this->_baseUrl . '/mobile/hotel/profile');
            return;
        }

        require_once 'Dal/Hotel/Item.php';
        $dalItem = Dal_Hotel_Item::getDefaultInstance();
        $number = $dalItem->getItemNum($uid, $sid);

        if ($model == 1 && $number) {
        	require_once 'Bll/Hotel/Item.php';
            $bllItem = new Bll_Hotel_Item();
            $re = $bllItem->useItem($uid, $sid, $fid == 0);
            if ($re['result']) {
            	$this->view->message = '****使用OK';
            	$number = $dalItem->getItemNum($uid, $sid);
            }
        }

        $item = $dalItem->getItemInfo($sid);
        $this->view->item = $item;
        $this->view->number = $number;
        $this->view->nickname = $this->_user->getDisplayname();
        $this->render();
    }

    /**
     * 使用します
     *
     */
    public function itemfinishAction()
    {
        $uid = $this->_user->getId();

        $sid = (int)$this->getParam('CF_sid', 1);
        require_once 'Dal/Hotel/Item.php';
        $dalItem = Dal_Hotel_Item::getDefaultInstance();
        $item = $dalItem->getStore($uid, $sid);

        $this->view->item = $item;
        $this->view->nickname = $this->_user->getDisplayname();
        $this->render();
    }

    public function minifeedAction()
    {
        $uid = $this->_user->getId();
        $type = $this->getParam('CF_type', 1);
        $this->render();
    }

    /**
     * enemy
     *
     */
    public function enemylistAction()
    {
        $uid = $this->_user->getId();
        $friendId = $this->getParam('CF_friendId');
        $index = $this->getParam('CF_index');

        require_once 'Dal/Hotel/Friend.php';
        $dalFriend = Dal_Hotel_Friend::getDefaultInstance();

        if (!empty($friendId) && !empty($index)) {
        	require_once 'Bll/Hotel/Friend.php';
            $bllFriend = new Bll_Hotel_Friend();
            $re = $bllFriend->banishLearner($uid, $friendId, $index, 1);
            $message = '';
            if ($re['result'] == 1) {
            	$message = '店長' . $re['displayName'] . 'さん的使者驱逐完毕';
            } else if ($re['result'] == -1) {
                $message = '派遣使者不够三十分钟';
            }
            //$this->view->message = $message;
        }
        $emenylist = $dalFriend->getEnemyListById($uid);
        require_once 'Bll/User.php';
        Bll_User::appendPeople($emenylist, 'uid');

        $this->view->emenylist = $emenylist;
        $this->view->nickname = $this->_user->getDisplayname();
        $this->render();
    }

    /**
     * my's learner
     *
     */
    public function myspylistAction()
    {
    	$uid = $this->_user->getId();
        $index = $this->getParam('CF_index');
        $page = $this->getParam('CF_page', 1);
        $pageSize = 5;

        if (!empty($index) && $index != 0) {
            require_once 'Bll/Hotel/Friend.php';
            $bllFriend = new Bll_Hotel_Friend();
            $re = $bllFriend->cbkLearner($uid, $index);
            $message = '';
            if ($re['result'] == -2) {
            	$message = '派出使者不够三十分钟';
            } else if ($re['result'] == 1) {
                $message = '派出使者成功';
            }
            $this->view->message = $message;
        }

        require_once 'Dal/Hotel/Friend.php';
        $dalFriend = Dal_Hotel_Friend::getDefaultInstance();
        $spylist = $dalFriend->getMySpyListById($uid, $page, $pageSize);
        $count = $dalFriend->getLearnCountById($uid);
        foreach ($spylist as $key => $value) {
        	if ($value['fid'] != 0 || $value['fid'] != '0') {
        		require_once 'Bll/User.php';
        		$user = Bll_User::getPerson($value['fid']);
        		$spylist[$key]['displayName'] = $user->getDisplayname();
        		//get money
                $earn = $dalFriend->earnLearner($uid, $value['index']);
                $spylist[$key]['money'] = $earn;
        	}
        }

        $this->view->spylist = $spylist;
        $this->view->nickname = $this->_user->getDisplayname();
        $this->render();
    }

    public function sendlearner($friendUid)
    {
    	$uid = $this->_user->getId();
    	/*$friendUid = $this->getParam('CF_friendId');
    	if (empty($friendUid)) {
    		$this->_redirect($this->_baseUrl . '/mobile/hotel/profile');
    	}*/
    	$message = '失败しました';
    	require_once 'Bll/Hotel/Friend.php';
        $bllFriend = new Bll_Hotel_Friend();
        $re = $bllFriend->sendLearner($uid, $friendUid);
        if ($re['result'] != 1) {
            $this->_redirect($this->_baseUrl . '/mobile/hotel/profile');
        }

        require_once 'Bll/User.php';
        $userInfo = Bll_User::getPerson($friendUid);
        $message = '在' . $userInfo->getDisplayName() . 'さん屋成功に送信された通信使.';
        return $message;

        /*$this->view->message = '在' . $re['displayName'] . 'さん屋成功に送信された通信使.';
        $this->view->nickname = $this->_user->getDisplayname();
        $this->render();*/
    }

    public function techlistAction()
    {
    	$uid = $this->_user->getId();
    	$this->view->uid = $uid;

    	require_once 'Dal/Hotel/Tech.php';
        $bllTech = new Bll_Hotel_Tech($uid);

        $techInfo = $bllTech->getTechInfo($uid);
        $this->view->techInfo = $techInfo;
        $this->view->nickname = $this->_user->getDisplayname();
        $this->render();
    }

    public function clear($friendUid)
    {
        /*$uid = $this->_user->getId();
        $friendUid = $this->getParam('CF_friendId');
        if ($uid == $friendUid && empty($friendUid)) {
        	$friendUid = $uid;
        }*/

        $message = 'OK';
        if ($friendUid) {
            require_once 'Bll/Hotel/Friend.php';
            $bllFriend = new Bll_Hotel_Friend();
            $re = $bllFriend->clean($uid, $friendUid);
            if ($re['result'] != 1) {
            	$this->_redirect($this->_baseUrl . '/mobile/hotel/error');
            	$message = '失敗した!';
            }
        } else {
        	return '';
        }

        return $message;
        //$this->view->message = $message;
        //$this->_redirect($this->_baseUrl . '/mobile/hotel/profile?CF_uid=' . $friendUid);
    }

    /**
     * get profile page info
     *
     * @param integer $profileUid
     * @param string $url
     */
    protected function getProfileInfo($profileUid)
    {
    	require_once 'Dal/Hotel/Huser.php';
        $dalHuser = Dal_Hotel_Huser::getDefaultInstance();

        $fullInfo = $dalHuser->getFullData($profileUid, 1);
        $userInfo = $dalHuser->getUserInfoById($profileUid);
        $roomInfo = $dalHuser->getRoomInfoById($profileUid);

        $userInfo['stats_level'] = $roomInfo['manager'];
        $userInfo['format_money'] = number_format($userInfo['money']);
        Bll_User::appendPerson($userInfo, 'uid');

        $this->view->roomInfo = $roomInfo;
        $this->view->fullInfo = $fullInfo;
        $this->view->uid = $profileUid;
        $this->view->info = $userInfo;
    }

    protected function pagerInfo($count, $list, $page, $pageSize, $url)
    {
    	 if ($count && !empty($list)) {
            $startCount = ($page - 1) * $pageSize + 1;
            if (count($list) == $pageSize) {
                 $endCount = $page * $pageSize;
            }
            else {
                 $endCount = $startCount + count($list) - 1;
            }
            $listCount = array('startCount' => $startCount, 'endCount' => $endCount );
        }

        $pager = array('count' => $count,
                                   'pageIndex' => $page,
                                   'requestUrl' => 'mobile/hotel/' . $url,
                                   'pageSize' => $pageSize,
                                   'maxPager' => ceil($count / $pageSize),
                                   );

        $this->view->listCount = $listCount;
        return $pager;
    }

    public function teching($type, $roomAry)
    {
    	$uid = $this->_user->getId();
        require_once 'Dal/Hotel/Huser.php';
        $dalHuser = Dal_Hotel_Huser::getDefaultInstance();
        $result = $dalHuser->getProcessInfo($uid, $type);
        if (intval($result['over_time']) > intval(time())){
	        foreach ($roomAry as $value) {
	            if ($value == $result['name']) {
	                $this->view->techName = $result['name'];
	                $sendTime = (intval($result['over_time']) - intval(time()));
	                $this->getTechTime($sendTime);
	            }
	        }
        }
    }

    protected function getTechTime($sendTime)
    {
        $strTime = '';
        if ($sendTime > 3600) {
            $strTime = $sendTime / 3600 . '時間' . strftime('%M', $sendTime % 3600). '分間' . strftime('%S', ($sendTime % 3600) % 60) . '秒間';
        } else if ($time > 60){
            $strTime = strftime('%M', $sendTime) . '分' . strftime('%S', $sendTime) . '秒';
        } else {
            $strTime = strftime('%S', $sendTime) . '秒';
        }
        $this->view->time =  $strTime;
        return $strTime;
    }

    public function helpAction()
    {
        $this->render();
    }

    public function errorAction()
    {
        $this->render();
    }
}
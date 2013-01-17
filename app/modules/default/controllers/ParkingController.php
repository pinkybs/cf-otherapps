<?php

/**
 * parking controller
 * init each index page
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/02/19    Liz
 */
class ParkingController extends MyLib_Zend_Controller_Action_Default
{
    /**
     * index Action
     *
     */
    public function indexAction()
    {
        $parkUid = $this->_request->getParam('uid');

        require_once 'Dal/Parking/Puser.php';
        $dalParkPuser = new Dal_Parking_Puser();
        $userPark = $dalParkPuser->getUserPark($parkUid);

        if ( !$userPark ) {
            $parkUid = $this->_user->getId();
            $userPark = $dalParkPuser->getUserPark($parkUid);
        }

        require_once 'Bll/User.php';
        Bll_User::appendPerson($userPark, 'uid');
        $this->view->userPark = $userPark;

        $uid = $this->_user->getId();
        $this->view->uid = $uid;

        $userAsset = $dalParkPuser->getAsset($uid);
        $this->view->userAsset = $userAsset;

        require_once 'Dal/Parking/Neighbor.php';
        $dalParkNeighbor = new Dal_Parking_Neighbor();
        $neighbor = $dalParkNeighbor->getNeighbor($uid,$userPark['neighbor_left'],$userPark['neighbor_right']);

        //get user cars info
        require_once 'Bll/Parking/Index.php';
        $bllParkIndex = new Bll_Parking_Index();
        $cars = $bllParkIndex->getUserCars($uid, $neighbor);
        $this->view->cars = $cars;
        $this->view->carCount = count($cars);

        //get friend info
        $arrFriend = $bllParkIndex->getArrFriend($uid, $neighbor);
        $this->view->userFriendList = Zend_Json::encode($arrFriend['userFriendList']);
        $this->view->allFriendList = Zend_Json::encode($arrFriend['allFriendList']);

        //get top rank info
        $type1 = 1;
        $type2 = 1;
        $this->view->type1 = $type1;
        $this->view->type2 = $type2;
        //get ranking count
        $count = $dalParkPuser->getRankingCount($uid, $type1, $arrFriend['arrFriendId']);
        //get start number
        $start = $count>2 ? ($count -2) : 0;
        $topRank = $dalParkPuser->getRankingUser($uid, $arrFriend['arrFriendId'], $type1, $type2, 2, 'ASC', $start);

        require_once 'Bll/User.php';
        Bll_User::appendPeople($topRank, 'uid');

        $friendIds = Bll_Parking_Friend::getFriendIds($uid);
        $friendIds = explode(',', $friendIds);
        if (count($friendIds) < 2) {
        	$allRank = array();
        	$topRank = $bllParkIndex->appendNeighborRank($topRank, $type2, $allRank);
        }

        $topCount = 2;

        $this->view->topRank = $topRank;
        $this->view->topCount = $topCount;
        if ( $topCount == 1 ) {
            $this->view->topArrInvite = 1;
        }

        //get rank info about user
        $response = $bllParkIndex->getRankInfo($uid, $type1, $type2);

        $this->view->userRankNm = $response['userRankNm'];

        if (count($friendIds) < 2) {
        	$response['rankInfo'] = $allRank;
        	$this->view->userRankNm = count($allRank) + 2;
        }

        $this->view->rankInfo = $response['rankInfo'];
        $rankCount = count($response['rankInfo']);
        $this->view->rankCount = $rankCount;

        if ( $rankCount < 8 ) {
            //$this->view->rankingLeft = (8 - $rankCount)*58;
            $arrInvite = array();
            for ( $i=0, $icount = (8 - $rankCount); $i<$icount; $i++ ) {
                $arrInvite[$i] = $i;
            }
            $this->view->arrInvite = $arrInvite;
            $this->view->allInvite = 10;
        }

        //get can move right count
        $rightCount = $rankCount > 8 ? ($rankCount-8) : 0;
        $this->view->rightCount = $rightCount;
        $this->view->allCount = $count;

        //get user park
        $userInfo = Bll_User::getPerson($uid);
        $owner = $this->_request->getParam('uid', $uid);
        $response = $bllParkIndex->getUserPark($uid, $owner);
        $this->view->park = Zend_Json::encode($response);
        $this->view->userInfo = $userInfo;
        $this->view->host = Zend_Json::encode(array('uid'=>$uid,
                                                    'thumbnailUrl'=>$userInfo->getThumbnailUrl(),
                                                    'displayName'=>$userInfo->getDisplayName(),
                                                    'lastEvasionTime'=>$userPark['last_evasion_time']));

        //get user card info
        require_once 'Dal/Parking/Card.php';
        $dalParkCard = new Dal_Parking_Card();
        $anonymousCardCount = $dalParkCard->getUserCardCoutByCid(2, $uid);
        $this->view->reportCardCount = $anonymousCardCount;

        //minifeed
        $this->view->minifeed = Zend_Json::encode($bllParkIndex->getMinifeed($uid));
        $this->view->newsfeed = Zend_Json::encode($bllParkIndex->getNewsfeed($uid));

        //set item info
        $this->_getItem();

        $this->render();
    }

    /**
     * carshop Action
     * @author lp
     */
    public function carshopAction()
    {
    	$pageIndex = $this->_request->getParam('page_index',1);

    	$this->_getItem();
        $uid = $this->_user->getId();

        $pageSize = 8;
        //car_id and car_color from parking page  when change car
        $car_id = $this->_request->getParam('car_id');
        $car_color = $this->_request->getParam('car_color');
        $oldCarPrice = null;
        if($car_id != null && $car_color != null){
            $car_color = str_replace("\\", "", $car_color);
            $car_color = str_replace("\"", "", $car_color);

            require_once 'Dal/Parking/Car.php';

            $dalCar = new Dal_Parking_Car();
            $carOld = $dalCar->getParkingCarInfo($car_id);
            $oldCarPrice = floor($carOld['price']);

        }

        require_once 'Bll/Parking/Cache.php';
        
        $count = Bll_Parking_Cache :: getCarShopCount();
        $carList = Bll_Parking_Cache :: getCarShopList($pageIndex, $pageSize);

        require_once 'Dal/Parking/Car.php';
        $dalCar = new Dal_Parking_Car();
        $maxPrice = $dalCar->getMaxPriceOfUserCars($uid);

        $this->view->carList = $carList;
        $this->view->count = $count;
        $this->view->pageSize = $pageSize;
        $this->view->pageIndex = $pageIndex;
        $this->view->currenCountFrom = $pageSize*($pageIndex-1)+1;
        $this->view->currenCountTo = $pageSize*($pageIndex-1)+count($carList);
        $this->view->car_id = $car_id;
        $this->view->car_color = $car_color;
        $this->view->oldCarPrice = $oldCarPrice;
        $this->view->maxPrice = $maxPrice;

        $this->render();
    }

    /**
     * house Action
     * @author lp
     */
    public function houseAction()
    {
    	$this->_getItem();

    	$pageIndex = 1;
        $pageSize = 8;

    	require_once 'Dal/Parking/House.php';
        $dalHouse = new Dal_Parking_House();

        $oldHouseInfo = $dalHouse->getOldHouseInfo($this->_user->getId());

        $oldHouseType = 0;
        switch ($oldHouseInfo['type']){
        	case 'A' : $oldHouseType = 3; break;
            case 'B' : $oldHouseType = 4; break;
            case 'C' : $oldHouseType = 5; break;
            case 'D' : $oldHouseType = 6; break;
            case 'E' : $oldHouseType = 7; break;
            case 'F' : $oldHouseType = 8; break;
        }

        require_once 'Bll/Parking/Cache.php';

        $count = Bll_Parking_Cache ::getHouseCount();
        $houseList = Bll_Parking_Cache ::getHouseList($pageIndex, $pageSize);

        foreach($houseList as $key => $value){
        	switch ($value['type']) {
                case 'A': $houseList[$key]['type'] = 3; break;
        		case 'B': $houseList[$key]['type'] = 4; break;
        		case 'C': $houseList[$key]['type'] = 5; break;
        		case 'D': $houseList[$key]['type'] = 6; break;
        		case 'E': $houseList[$key]['type'] = 7; break;
        		case 'F': $houseList[$key]['type'] = 8; break;
            }
        }
        $this->view->count = $count;
        $this->view->oldHousePrice = $oldHouseInfo['price'];
        $this->view->oldHouseType = $oldHouseType;
        $this->view->oldHouseId = $oldHouseInfo['id'];

        $this->view->houseList = $houseList;
        $this->view->pageSize = $pageSize;
        $this->view->pageIndex = $pageIndex;
        $this->view->currenCountFrom = $pageSize*($pageIndex-1)+1;
        $this->view->currenCountTo = $pageSize*($pageIndex-1)+count($houseList);

        $this->render();
    }

    /**
     * itemshop Action
     * @author lp
     */
    public function itemshopAction()
    {

        $this->_getItem();
        $pageIndex = 1;
        $pageSize = 8;
        require_once 'Bll/Parking/Cache.php';

        $count = Bll_Parking_Cache ::getItemCount();
        $itemList = Bll_Parking_Cache ::getItemList($pageIndex, $pageSize);

        $this->view->count = $count;
        $this->view->itemList = $itemList;
        $this->view->pageSize = $pageSize;
        $this->view->pageIndex = $pageIndex;
        $this->view->currenCountFrom = $pageSize*($pageIndex-1)+1;
        $this->view->currenCountTo = $pageSize*($pageIndex-1)+count($itemList);

        $this->render();
    }

    /**
     * help Action
     *
     */
    public function errorAction()
    {
        $this->render();
    }

    /**
     * help Action
     *
     */
    public function helpAction()
    {
    	$this->view->csstype = 'parkinghelp';
        $this->render();
    }

    /**
     * deipatch
     *
     */
    function preDispatch()
    {
        $uid = $this->_user->getId();

        require_once 'Dal/Parking/Puser.php';
        $dalParkingPuser = new Dal_Parking_Puser();

        $isIn = $dalParkingPuser->isInParking($uid);
		$actionName = $this->_request->getActionName();

        if ($isIn) {
        	if ("index" == $actionName) {
	            require_once 'Bll/Parking/Index.php';
	            $bllParkIndex = new Bll_Parking_Index();
	            $result = $bllParkIndex->isTodayFirstLogin($uid);

	            if ($result) {
	                $this->view->todayFirstLogin = Zend_Json::encode($result);
	            }

	            $dalParkingPuser->updateLastLoginTime($uid);
        	}
        }
        else {
        	if ("index" != $actionName) {
        		$this->_redirect("/parking/index");
        	}

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
            $dalParkingPuser->insertParkingUser($park, $uid);

            $color = array(1 => 'black', 2 => 'white', 3 => 'silver', 4 => 'yellow', 5 => red, 6 => 'blue');
            $rand = rand(1,6);

            $car = array('uid' => $uid,
                         'car_id' => 1,
                         'car_color' => $color[$rand],
                         'create_time' => time());

            require_once 'Dal/Parking/Car.php';
            $dalParkingCar = new Dal_Parking_Car();

            $dalParkingCar->insertUserCars($car);
            $dalParkingPuser->updateUserCar($uid);

            require_once 'Zend/Json.php';
            $this->view->firstLogin = Zend_Json::encode(array('cid'=>1,'color'=>$color[$rand],'name'=>'SASAKI　原付','cav_name'=>'01_scooter'));
        }
		$this->view->csstype = 'parking';

		//get rand banner
		/*
        $banner = rand(1,2);
        switch ($banner) {
            case 1 :
                $bannerUrl = 'ad_subarea_mich.html';
                break;
            case 2 :
                $bannerUrl = 'ad_subarea_se.html';
                break;
        }*/
        $this->view->bannerUrl = 'ad_subarea_mich.html';
    }

    private function _getItem()
    {
        $uid = $this->_user->getId();

        require_once 'Dal/Parking/Store.php';
        $dalStore = new Dal_Parking_Store();

        $asset = $dalStore->getAsset($uid);
        $assetTwo = $asset['asset'];
        $asset['asset'] = number_format($asset['asset']);

        $havaFreePark = $dalStore->getFreePark($uid);

        $userItemInfo = $dalStore->getUserAllItems($uid);

        require_once 'Bll/Parking/Friend.php';
        $haveFriends = Bll_Parking_Friend::getFriendIds($uid);

        $last_bribery_time = 0;
        $last_evasion_time = 0;
        $last_check_time = 0;


        foreach($userItemInfo as $value){
            if($value['sid'] == 3){
                $last_bribery_time = (time()-$value['last_bribery_time'])/( 24*3600 );
            }
            if($value['sid'] == 8){
                $last_evasion_time = (time()-$value['last_evasion_time'])/( 24*3600 );
            }
            if($value['sid'] == 6){
                $last_check_time = (time()-$value['last_check_time'])/( 24*3600 );
            }
        }

        $this->view->userItemInfo = $userItemInfo;

        $this->view->last_bribery_time = $last_bribery_time;
        $this->view->last_evasion_time = $last_evasion_time;
        $this->view->last_check_time = $last_check_time;

        $this->view->havaFreePark = $havaFreePark['free_park'];

        $this->view->asset = $asset['asset'];
        //a hidden value in page
        $this->view->assetTwo = $assetTwo;

        $this->view->haveFriends = $haveFriends;
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
        return $this->_redirect('/parking/error');
        $this->render();
    }

 }

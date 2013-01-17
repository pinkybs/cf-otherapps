<?php
/** @see Zend_Json */
require_once 'Zend/Json.php';
/** @see MyLib_Zend_Controller_Action_Ajax */
require_once 'MyLib/Zend/Controller/Action/Ajax.php';

/**
 * Parking Ajax Controllers
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/02/19   Liz
 */
class Ajax_ParkingController extends MyLib_Zend_Controller_Action_Ajax
{

    /**
     * get user park action
     *
     */
    public function getuserparkAction()
    {
        $request = $this->_request->getParam('request');
        $request = Zend_Json::decode($request, Zend_Json::TYPE_OBJECT);

        $uid = $this->_user->getId();
        require_once 'Bll/Parking/Index.php';
        $bllParkIndex = new Bll_Parking_Index();
        $response = $bllParkIndex->getUserPark($uid, $request->id, $request->type);
        echo Zend_Json::encode($response);
    }

    /**
     * get user car action
     *
     */
    public function getusercarlistAction()
    {
        $uid = $this->_request->getPost('uid');

        $uid = $uid == null ? $this->_user->getId() : $uid;

        require_once 'Dal/Parking/Puser.php';
        $dalParkPuser = new Dal_Parking_Puser();

        $info = $dalParkPuser->getUserPark($uid);

        require_once 'Bll/Parking/Index.php';
        $bllParkIndex = new Bll_Parking_Index();
        $cars = $bllParkIndex->getUserCars($uid);

        echo Zend_Json::encode(array('info'=>$info,'cars'=>$cars));
    }

    /**
     * parking action
     *
     */
    public function parkingAction()
    {
        $uid = $this->_request->getPost('uid');
        $park_uid = $this->_request->getPost('park_uid');
        $car_id = $this->_request->getPost('car_id');
        $car_color = $this->_request->getPost('car_color');
        $location = $this->_request->getPost('loca');
        $type = $this->_request->getPost('type');

        require_once 'Bll/Parking/Index.php';
        $bllParkIndex = new Bll_Parking_Index();
        $result = $bllParkIndex->parking($uid,$park_uid,$car_id,$car_color,$location,$type);
        
        echo Zend_Json::encode($result);
    }

    /**
     * stick action
     *
     */
    public function stickAction()
    {
        $uid = $this->_request->getPost('uid');
        $location = $this->_request->getPost('loca');

        require_once 'Bll/Parking/Index.php';
        $bllParkIndex = new Bll_Parking_Index();
        $result = $bllParkIndex->stick($uid, $location);

        echo Zend_Json::encode($result);
    }

    /**
     * report action
     *
     */
    public function reportAction()
    {
        $uid = $this->_request->getPost('uid');
        $park_uid = $this->_request->getPost('park_uid');
        $report_uid = $this->_request->getPost('report_uid');
        $car_id = $this->_request->getPost('car_id');
        $car_color = $this->_request->getPost('car_color');
        $location = $this->_request->getPost('loca');
        $isAnonymous = $this->_request->getPost('isAnonymous');

        require_once 'Bll/Parking/Index.php';
        $bllParkIndex = new Bll_Parking_Index();
        $result = $bllParkIndex->report($uid, $park_uid, $report_uid, $car_id, $car_color, $location, $isAnonymous);

        echo Zend_Json::encode($result);
    }

    /**
     * report action
     *
     */
    public function checkreportAction()
    {
        $uid = $this->_request->getPost('uid');
        $park_uid = $this->_request->getPost('park_uid');
        $report_uid = $this->_request->getPost('report_uid');
        $car_id = $this->_request->getPost('car_id');
        $car_color = $this->_request->getPost('car_color');
        $location = $this->_request->getPost('loca');

        require_once 'Bll/Parking/Index.php';
        $bllParkIndex = new Bll_Parking_Index();
        $result = $bllParkIndex->checkReport($uid, $park_uid, $report_uid, $car_id, $car_color, $location);

        echo Zend_Json::encode($result);
    }


    /**
     * get car individual
     *
     */
    public function getcarinfoAction()
    {
        //get search condition
        $request = $this->_request->getParam('request');
        $request = Zend_Json::decode($request, Zend_Json::TYPE_OBJECT);

        $cid = $request->cid;
        require_once 'Dal/Parking/Car.php';
        $dalParkCar = new Dal_Parking_Car();

        $uid = $this->_user->getId();
        $car = $dalParkCar->getCarInfo($cid);

        //if parking neighbor
        if ( $car['type'] == 2 ) {
            require_once 'Dal/Parking/Neighbor.php';
            $dalParkNeighbor = new Dal_Parking_Neighbor();
            $neighborInfo = $dalParkNeighbor->getUserNeighborPark($car['parking_uid']);
            $car['free_park'] = $neighborInfo['free_park'];
            $car['background'] = $neighborInfo['background'];
            $car['free'] = $neighborInfo['free'];
        }

        if ($car['status'] != 1 ) {
            $car['status'] = "廃車状態のため、整備カードを使うまで使用できません";
            $car['money'] = '廃車';
            $car['carStatus'] = 0;
            $car['iconType'] = 4;
        }
        else if ($car['parked_time'] != null) {

            if ( $car['free_park'] == $car['location'] ) {
                //is free park
                $isfree = 1;
                $car['iconType'] = 2;
            }
            else {
                //有料
                $isfree = 0;
                $car['iconType'] = 1;
            }

            if ($car['type'] == 1) {
                $userInfo = Bll_User::getPerson($car['parking_uid']);
                $displayName = $userInfo->getDisplayName();
                $fee = $car['fee'];
            }
            else {
                $displayName = $dalParkNeighbor->getNeighborName($car['parking_uid']);
                $fee = "1500";
            }

            require_once 'Bll/Parking/Index.php';
            $bllParkIndex = new Bll_Parking_Index();

            $result = $bllParkIndex->getUserCarStatus($car['uid'], $car, $displayName, $fee, $isfree, $car['type'], $uid);
            $car['status'] = $result['status'];
            $car['carStatus'] = 1;
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

            $money = $userAsset < $time*2 ? $userAsset : $time*2;
            $money = number_format($money);
            $car['status'] = "移動中（駐車していません）<br/>ガソリン代：¥$money";
            //$car['money'] = $money;
            $car['carStatus'] = 1;
            $car['iconType'] = 3;
        }

        require_once 'Dal/Parking/Puser.php';
        $dalParkPuser = new Dal_Parking_Puser();
        $userPark = $dalParkPuser->getUserPark($uid);

        $friendIds = Bll_Parking_Friend::getFriendIds($uid);

        if ( $userPark['car_count'] == 1 ) {
            $canSendCar = '0';
        }
        else if ( !$friendIds ) {
            $canSendCar = '0';
        }
        else if ( time() - $userPark['send_car_time']< 30*24*3600 ) {
            $canSendCar = '0';
        }
        else {
            $canSendCar = '1';
        }

        if ( $car['car_type'] == 2 ) {
            $car['carStatus'] = 0;
        }
        
        //set output car data
        $response = array('car' => $car, 'canSendCar' => $canSendCar );
        $response = Zend_Json::encode($response);

        echo $response;
    }

    /**
     * send friend action
     *
     */
    public function sendfriendAction()
    {
        $car_id = $this->_request->getPost('car_id');
        $car_color = $this->_request->getPost('car_color');
        $fid = $this->_request->getPost('fid');

        $uid = $this->_user->getId();
        require_once 'Bll/Parking/Index.php';
        $bllParkIndex = new Bll_Parking_Index();
        $result = $bllParkIndex->sendFriend($uid, $car_id, $car_color, $fid);

        echo $result;
    }

    /**
     * get asset action
     *
     */
    public function getassetAction()
    {
        require_once 'Dal/Parking/Puser.php';
        $dalParkPuser = new Dal_Parking_Puser();
        $uid = $this->_user->getId();
        $userPark = $dalParkPuser->getUserPark($uid);

        echo number_format($userPark['asset']);
    }

    /**
     * get my car action
     *
     */
    public function getmycarAction()
    {
        $uid = $this->_user->getId();
        require_once 'Dal/Parking/Puser.php';
        $dalParkPuser = new Dal_Parking_Puser();
        $userPark = $dalParkPuser->getUserPark($uid);

        require_once 'Dal/Parking/Neighbor.php';
        $dalParkNeighbor = new Dal_Parking_Neighbor();
        $neighbor = $dalParkNeighbor->getNeighbor($uid, $userPark['neighbor_left'],$userPark['neighbor_right']);

        //get user cars info
        require_once 'Bll/Parking/Index.php';
        $bllParkIndex = new Bll_Parking_Index();
        $cars = $bllParkIndex->getUserCars($uid, $neighbor);

        $cars = Zend_Json::encode($cars);

        echo $cars;
    }


    /**
     * ranking action
     *
     */
    public function rankingAction()
    {
        //get search condition
        $request = $this->_request->getParam('request');
        $request = Zend_Json::decode($request, Zend_Json::TYPE_OBJECT);

        require_once 'Dal/Parking/Puser.php';
        $dalParkPuser = new Dal_Parking_Puser();

        $uid = $this->_user->getId();
     //   $uid=14;
        $friendIds = Bll_Parking_Friend::getFriendIds($uid);
        $friendIds = explode(',', $friendIds);

        $count = $dalParkPuser->getRankingCount($uid, $request->type1, $friendIds);
        //get top rank info
        $start = $count>2 ? ($count -2) : 0;
        $topRank = $dalParkPuser->getRankingUser($uid, $friendIds, $request->type1, $request->type2, 2, 'ASC', $start);

        require_once 'Bll/User.php';
        Bll_User::appendPeople($topRank, 'uid');

        require_once 'Bll/Parking/Index.php';
        $bllParkIndex = new Bll_Parking_Index();

        if (count($friendIds) < 2 && 1 == $request->type1) {
        	$allRank = array();
        	$topRank = $bllParkIndex->appendNeighborRank($topRank, $request->type2, $allRank);
        }

        $topCount = 2;

        //get rank info about user
        $result = $bllParkIndex->getRankInfo($uid, $request->type1, $request->type2);
        $rankInfo = $result['rankInfo'];
        $userRankNm = $result['userRankNm'];
        $rankStatus = $result['rankStatus'];

        if (!$rankInfo) {
            $rankInfo = $allRank;
            $userRankNm = count($allRank) + 2;
        }
        else {
            Bll_User::appendPeople($rankInfo, 'uid');
        }

        //get some count info
        $rankCount = count($rankInfo);
        $rightCount = $rankCount > 8 ? ($rankCount-8) : 0;
        $countArr = array('rankCount' => $rankCount,
                          'rightCount' => $rightCount,
                          'allCount' => $count);

        //set output rank data
        $response = array('rankInfo' => $rankInfo,
                          'count' => $userRankNm,
                          'topRank' => $topRank,
                          'topCount' => $topCount,
                          'countArr' => $countArr,
                          'rankStatus' => $rankStatus);
        $response = Zend_Json::encode($response);

        echo $response;
    }

    /**
     * get more rank info
     *
     */
    public function getmorerankAction() {
        //get search condition
        $request = $this->_request->getParam('request');
        $request = Zend_Json::decode($request, Zend_Json::TYPE_OBJECT);

        $uid = $this->_user->getId();
        require_once 'Bll/Parking/Index.php';
        $bllParkIndex = new Bll_Parking_Index();
        $result = $bllParkIndex->getMoreRank($uid, $request->type1, $request->type2, $request->rankId, $request->allCount, $request->isRight);

        $response = Zend_Json::encode($result);
        echo $response;
    }

    /**
     * get last rank info
     *
     */
    public function getlastrankAction() {
        //get search condition
        $request = $this->_request->getParam('request');
        $request = Zend_Json::decode($request, Zend_Json::TYPE_OBJECT);

        $uid = $this->_user->getId();

        if ($request->type1 == 1) {
            require_once 'Bll/Parking/Friend.php';
            $friendIds = Bll_Parking_Friend::getFriendIds($uid);
            $friendIds = explode(',', $friendIds);

            if (count($friendIds) < 2) {
            	echo '';
            	return;
            }
        }

        require_once 'Bll/Parking/Index.php';
        $bllParkIndex = new Bll_Parking_Index();
        $result = $bllParkIndex->getLastRank($uid, $request->type1, $request->type2, $request->isRight);

        //set output data
        $response = Zend_Json::encode($result);

        echo $response;
    }

    /**
     * get carshop information
     * @author lp
     */
    public function getcarshopAction(){
        if ($this->_request->isPost()) {
            $pageIndex = (int)$this->_request->getPost('pageIndex', 1);

            require_once 'Bll/Parking/Cache.php';

            $count = Bll_Parking_Cache ::getCarShopCount();
            $array = Bll_Parking_Cache ::getCarShopList($pageIndex, 8);

            $response = array('info' => $array, 'count' => $count);
            $response = Zend_Json::encode($response);

            echo $response;
        }
    }

    /**
     * get house information
     * @author lp
     */
    public function gethouseAction(){
        if ($this->_request->isPost()) {
            $pageIndex = (int)$this->_request->getPost('pageIndex', 1);

            require_once 'Bll/Parking/Cache.php';

            $count = Bll_Parking_Cache ::getHouseCount();
            $array = Bll_Parking_Cache ::getHouseList($pageIndex, 8);

            $response = array('info' => $array, 'count' => $count);
            $response = Zend_Json::encode($response);

            echo $response;
        }
    }

    /**
     * get items information
     * @author lp
     */
    public function getitemAction(){
        if ($this->_request->isPost()) {
            $pageIndex = (int)$this->_request->getPost('pageIndex', 1);

            require_once 'Bll/Parking/Cache.php';

            $count = Bll_Parking_Cache ::getItemCount();
            $array = Bll_Parking_Cache ::getItemList($pageIndex, 8);

            $response = array('info' => $array, 'count' => $count);
            $response = Zend_Json::encode($response);

            echo $response;
        }
    }

    /**
     * buy house action
     * @author lp
     */
    public function buyhouseAction()
    {
    	$uid = $this->_user->getId();

        $hid = $this->_request->getPost('hid');

        require_once 'Bll/Parking/House.php';
        $bllHouse = new Bll_Parking_House();

        $result = $bllHouse->buyHouse($hid,$uid);
        echo $result;
    }

    /**
     * buy item action
     * @author lp
     */
    public function buyitemAction()
    {
        $cid = $this->_request->getPost('cid');
        require_once 'Bll/Parking/Item.php';
        $bllItem = new Bll_Parking_Item();
        $uid = $this->_user->getId();

        if ($cid == 1) {

            $result = $bllItem->buyChangeParkCard($uid);
            echo $result;
        }
        else {

            $result = $bllItem->buyCard($cid,$uid);
            echo $result ? '1' : '0';
        }
    }

    /**
     * buy car action
     * @author lp
     */
    public function buycarAction()
    {
        $uid = $this->_user->getId();

        $car_id = $this->_request->getPost('cid');
        $car_color = $this->_request->getPost('color');

        require_once 'Bll/Parking/Carshop.php';

        $bllCarshop = new Bll_Parking_Carshop();
        $result = $bllCarshop->buyCar($uid,$car_id,$car_color);
        echo $result;
    }

    /**
     * getusercarAction when buy car and change car is used
     * @author lp
     */
    public function getusercarAction(){
        $uid = $this->_user->getId();

        $car_id = $this->_request->getPost('cid');
        require_once 'Bll/Parking/Carshop.php';

        $bllCarshop = new Bll_Parking_Carshop();
        //check if user has had the car which user wants to buy
        $result = $bllCarshop->getUserCar($uid,$car_id);
        echo $result;
    }

    /**
     * getallusercarAction when change car is used
     * @author lp
     */
    public function getallusercarAction(){
        $uid = $this->_user->getId();

        require_once 'Dal/Parking/Car.php';
        $dalCar = new Dal_Parking_Car();

        $info = $dalCar->getUserPark($uid);
        $cars = $dalCar->getUserCarsWhenChangeCar($uid);

        echo Zend_Json::encode(array('info'=>$info,'cars'=>$cars));
    }

    /**
     * change car action
     * @author lp
     */
    public function changecarAction()
    {
        $uid = $this->_user->getId();

        $cidNew = $this->_request->getPost('cidNew');
        $colorNew = $this->_request->getPost('colorNew');
        $cidOld = $this->_request->getPost('cidOld');
        $colorOld = $this->_request->getPost('colorOld');
    //    $havaCard=$this->_request->getPost('havaCard');
        require_once 'Bll/Parking/Carshop.php';

        $bllCarshop = new Bll_Parking_Carshop();

        $result = $bllCarshop->changeCar($uid, $cidNew, $colorNew, $cidOld, $colorOld);

        echo $result;

    }

	/**
	 * get break car action
	 *
	 */
    public function getbreakcarAction()
    {
    	require_once 'Dal/Parking/Car.php';
    	$dalCar = Dal_Parking_Car::getDefaultInstance();
    	$result = $dalCar->getUserbreakCars($this->_user->getId());

    	echo Zend_Json::encode($result);
    }

	/**
	 * get friedn action
	 *
	 */
    public function getfriendAction()
    {
    	require_once 'Bll/Parking/Friend.php';
    	$fids = Bll_Parking_Friend::getFriendIds($this->_user->getId());

    	$friends = array();

    	$temp = split(',', $fids);

    	foreach ($temp as $item) {
    		$friends = array_merge($friends,array(array('uid' => $item)));
    	}

    	require_once 'Bll/User.php';
		Bll_User::appendPeople($friends);

    	echo Zend_Json::encode($friends);
    }

	/**
	 * use item bomo action
	 *
	 */
    public function useitembombAction()
    {
    	require_once 'Bll/Parking/Useitem.php';
    	$bllParkItem = new Bll_Parking_Useitem();
    	$result = $bllParkItem->bomb($this->_user->getId());

        echo $result;
    }

	/**
	 * use item yanki action
	 *
	 */
    public function useitemyankiAction()
    {
    	$fid = $this->_request->getPost('fid');

    	require_once 'Bll/Parking/Useitem.php';
    	$bllParkItem = new Bll_Parking_Useitem();
    	$result = $bllParkItem->yanki($this->_user->getId(),$fid);

        echo $result;
    }

	/**
	 * user item free action
	 *
	 */
    public function useitemfreeAction()
    {
    	require_once 'Bll/Parking/Useitem.php';
    	$bllParkItem = new Bll_Parking_Useitem();
    	$result = $bllParkItem->free($this->_user->getId());

        echo $result;
    }

	/**
     * use item repair action
     *
     */
    public function useitemrepairAction()
    {
    	$car_id = $this->_request->getPost('car_id');
    	$car_color = $this->_request->getPost('car_color');

    	require_once 'Bll/Parking/Useitem.php';
    	$bllParkItem = new Bll_Parking_Useitem();
    	$result = $bllParkItem->repair($this->_user->getId(),$car_id,$car_color);

        echo $result;
    }

    /**
     * use bribery card
     * @author lp
     */
    public function usebriberyAction()
    {
    	$uid = $this->_user->getId();

        require_once 'Bll/Parking/Useitem.php';
        $bllParkItem = new Bll_Parking_Useitem();
        $result = $bllParkItem->bribery($uid);

        echo $result;
    }

    /**
     * use check card
     * @author lp
     */
    public function usecheckAction()
    {
        $uid = $this->_user->getId();

        require_once 'Bll/Parking/Useitem.php';
        $bllParkItem = new Bll_Parking_Useitem();
        $result = $bllParkItem->check($uid);

        echo $result;
    }

    /**
     * use insurance card
     * @author lp
     */
    public function useinsuranceAction()
    {
        $uid = $this->_user->getId();

        require_once 'Bll/Parking/Useitem.php';
        $bllParkItem = new Bll_Parking_Useitem();
        $result = $bllParkItem->insurance($uid);

        echo $result;
    }

    /**
     * use guard card
     * @author lp
     */
    public function useguardAction(){
    	$uid = $this->_user->getId();

        require_once 'Bll/Parking/Useitem.php';
        $bllParkItem = new Bll_Parking_Useitem();
        $result = $bllParkItem->guard($uid);

        echo $result;
    }

    /**
     * use evasion card
     * @author lp
     */
    public function useevasionAction(){
    	$uid = $this->_user->getId();

        require_once 'Bll/Parking/Useitem.php';
        $bllParkItem = new Bll_Parking_Useitem();
        $result = $bllParkItem->evasion($uid);

        echo $result;
    }
    /**
     * after user use a  card
     * @author lp
     */
    public function freshpageAction(){
        $uid = $this->_user->getId();
        $cid = $this->_request->getPost('cid');

        require_once 'Bll/Parking/Useitem.php';
        $bllParkItem = new Bll_Parking_Useitem();
        $returnInfo = $bllParkItem->freshpage($uid,$cid);

        if( $returnInfo['sid'] == 3 ){
                $returnInfo['last_bribery_time']=(time()-$returnInfo['last_bribery_time'])/(24*3600);
        }

        if( $returnInfo['sid'] == 6 ){
                $returnInfo['last_check_time']=(time()-$returnInfo['last_check_time'])/(24*3600);
        }

        if( $returnInfo['sid'] == 8 ){
                $returnInfo['last_evasion_time']=(time()-$returnInfo['last_evasion_time'])/(24*3600);
        }

        require_once 'Dal/Parking/Store.php';
        $dalStore = new Dal_Parking_Store();

        $havaFreePark = $dalStore->getFreePark($uid);
        $returnInfo['havaFreePark'] = $havaFreePark['free_park'];

        require_once 'Bll/Parking/Friend.php';
        $haveFriends = Bll_Parking_Friend::getFriendIds($uid);

        $returnInfo['haveFriends'] = $haveFriends;
        /*
        if( $cid == 11 ){
        	require_once 'Dal/Parking/Store.php';
            $dalStore = new Dal_Parking_Store();

            $last_yanki_time=0;
            $userYanKiItemInfo = $dalStore->getUserYanKiItemInfo($uid);
            for($i=1;$i<=8;$i++){
                $last_yanki_time=(time()-$userYanKiItemInfo['location'.$i])/(24*3600);

                if($last_yanki_time <= 3){
                    break;
                }
            }
            $returnInfo['last_yanki_time'] = $last_yanki_time;
        }
        */
        echo Zend_Json::encode($returnInfo);
    }
    /**
     * get old car price
     * @author lp
     */
    public function getoldcarpriceAction()
    {

        $cid = $this->_request->getPost( 'cid' );

        require_once 'Dal/Parking/Car.php';

        $dalCar = new Dal_Parking_Car();

        $oldCarPrice = $dalCar->getParkingCarInfo($cid);

        echo $oldCarPrice['price'];

    }
    /**
     * fresh user asset
     * @author lp
     */
    public function freshassetAction()
    {
    	$uid = $this->_user->getId();

    	require_once 'Dal/Parking/Store.php';
        $dalStore = new Dal_Parking_Store();

        $asset=$dalStore->getAsset($uid);

        $returnInfo = array('asset'=>$asset['asset']);
        echo Zend_Json::encode($returnInfo);
    }

    /**
     * get gadet flash data
     *
     */
    public function gadetloadAction()
    {
    	$uid = $this->_request->getPost('userID');

    	if (empty($uid)) {
    		echo 'gadgetData=' . Zend_Json::encode(array());
    	}

    	require_once 'Bll/Parking/Flash.php';
    	$bllFlash = new Bll_Parking_Flash();
		$data = $bllFlash->getGadetData($uid);

		echo 'gadgetData=' . Zend_Json::encode($data);
    }
}



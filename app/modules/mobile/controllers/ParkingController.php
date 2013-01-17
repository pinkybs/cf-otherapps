<?php

/** @see MyLib_Zend_Controller_Action_Mobile.php */
require_once 'MyLib/Zend/Controller/Action/Mobile.php';

/**
 * Mobile Parking Controller(modules/mobile/controllers/ParkingController.php)
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/05/14   Huch
 */
class Mobile_ParkingController extends MyLib_Zend_Controller_Action_Mobile
{
    protected $_pageSize = 8;

    /**
     * initialize object
     * override
     * @return void
     */
    public function init()
    {
        //get parking define param
        require_once 'Bll/Parking/Define.php';
        parent::init();
    }

    /**
     * index action
     *
     */
    public function indexAction()
    {
        $this->_redirect($this->_baseUrl . '/mobile/parking/start');
    }

    public function errorAction()
    {
        $this->render();
    }

    /**
     * start the parking flash action
     *
     */
    public function startAction()
    {
        //save visit user's pid
        $pid = $this->_request->getParam('parking_pid', $this->_user->getId());

        // get swf
        require_once 'Mbll/Parking/FlashCache.php';
        $swf = Mbll_Parking_FlashCache::getNewFlash($this->_user->getId(), $pid, $this->_APP_ID);

        ob_end_clean();
        ob_start();
        header("Accept-Ranges: bytes");
        header("Cache-Control: no-cache, must-revalidate");
        header("Content-Type: application/x-shockwave-flash");

        echo $swf;
        exit(0);
    }

    /**
     * parking action
     *
     */
    public function parkingAction()
    {
        $pid = $this->_request->getParam('parking_pid');
        $location = $this->_request->getParam('parking_location');

        //check can park
        require_once 'Mbll/Parking/Parking.php';
        $mbllParking = new Mbll_Parking_Parking();
        $result = $mbllParking->checkCanPark($this->_user->getId(), $pid, $location);

        if (!$result) {
            $this->_redirect($this->_baseUrl . '/mobile/parking/error');
            return;
        }

        //get parking car
        require_once 'Bll/Parking/Index.php';
        $bllParkIndex = new Bll_Parking_Index();
        $this->view->cars = $bllParkIndex->getUserCars($this->_user->getId(), array(), 2);
        $this->view->carColor = array('black'=>'黒の','white'=>'白の','silver'=>'銀の','yellow'=>'黄の','red'=>'赤の','blue'=>'青の');

        $this->view->pid = $pid;
        $this->view->location = $location;
        $this->render();
    }

    /**
     * parking submit action
     *
     */
    public function parkingsubmitAction()
    {
        $pid = $this->_request->getParam('parking_pid');
        $location = $this->_request->getParam('parking_location');
        $car_id = $this->_request->getParam('parking_car_id');
        $car_color = $this->_request->getParam('parking_car_color');
        $type = !is_numeric($pid) ? 1 : ($pid < 0 ? 2 : 1);

        //get before ranking
        require_once 'Bll/Parking/Friend.php';
        $friendIds = Bll_Parking_Friend::getFriends($this->_user->getId());

        require_once 'Mdal/Parking/Puser.php';
        $mdalParkPuser = new Mdal_Parking_Puser();
        $friendRankBefore = $mdalParkPuser->getUserRankNm($this->_user->getId(), $friendIds, 1, 1);
        $mixiRankBefore = $mdalParkPuser->getUserRankNm($this->_user->getId(), $friendIds, 2, 1);

        require_once 'Bll/Parking/Index.php';
        $bllParkIndex = new Bll_Parking_Index();
        $result = $bllParkIndex->parking($this->_user->getId(), $pid, $car_id, $car_color, $location, $type);

        //evasion card using
        if ($result['status'] == -12) {
            $this->_redirect($this->_baseUrl . '/mobile/parking/parkingevasion?parking_pid=' . $pid . '&parking_flag=1');
        }

        //insurance card using
        if ($result['status'] == -13) {
            $this->_redirect($this->_baseUrl . '/mobile/parking/parkingevasion?parking_pid=' . $pid . '&parking_flag=2');
        }

        //bomb car
        if ($result['status'] == -5 || $result['status'] == -7) {
            $this->_redirect($this->_baseUrl . '/mobile/parking/parkingbomb?parking_pid=' . $pid . "&parking_car_id=$car_id&parking_car_color=$car_color");
        }

        //check card
        if ($result['status'] == -6) {
            $this->_redirect($this->_baseUrl . '/mobile/parking/parkingcheck?parking_pid=' . $pid . '&parking_car_id=' . $car_id . '&parking_car_color=' . $car_color . '&parking_money=' . $result['money'] . '&parking_pre_user=' . urlencode($result['lastUserName']));
        }

        if ($result['status'] == -1) {
            $this->_redirect($this->_baseUrl . '/mobile/parking/error');
        }

        //parking fail
        if ($result['status'] == -2 || $result['status'] == -3 || $result['status'] == -4 || $result['status'] == -11 || $result['status'] == -14) {
            $this->_redirect($this->_baseUrl . '/mobile/parking/parkingfail?parking_pid=' . $pid . "&parking_car_id=$car_id&parking_car_color=$car_color" . "&parking_flag=" . $result['status']);
        }

        //parking success
        if ($result['status'] == 1) {
            $flag = $result['money'] <= 0 ? 1 : 0;
            $this->_redirect($this->_baseUrl . '/mobile/parking/parkingover?parking_pid=' . $pid . "&parking_car_id=$car_id&parking_car_color=$car_color&parking_money=" . $result['money'] . "&parking_free=" . $flag . "&parking_check=" . $result['checkCard']  . "&parking_friendRankBefore=" . $friendRankBefore . "&parking_mixiRankBefore=" . $mixiRankBefore);
        }
    }

    /**
     * parking over action
     *
     */
    public function parkingoverAction()
    {
        $pid = $this->_request->getParam('parking_pid');
        $car_id = $this->_request->getParam('parking_car_id');
        $car_color = $this->_request->getParam('parking_car_color');

        $friendRankBefore = $this->_request->getParam('parking_friendRankBefore', 1);
        $mixiRankBefore = $this->_request->getParam('parking_mixiRankBefore', 1);

        $free = $this->_request->getParam('parking_free', 1);
        $this->view->check = $this->_request->getParam('parking_check', 0);

        if ($free == 0) {
            //get end ranking
            require_once 'Bll/Parking/Friend.php';
            $friendIds = Bll_Parking_Friend::getFriends($this->_user->getId());

            require_once 'Mdal/Parking/Puser.php';
            $mdalParkPuser = new Mdal_Parking_Puser();
            $friendRankEnd = $mdalParkPuser->getUserRankNm($this->_user->getId(), $friendIds, 1, 1);
            $mixiRankEnd = $mdalParkPuser->getUserRankNm($this->_user->getId(), $friendIds, 2, 1);

            $this->view->friendRanking = $friendRankEnd;
            $this->view->mixiRanking = $mixiRankEnd;

            if ($friendRankEnd < $friendRankBefore) {
                $this->view->friendRankEmoji = 'F99A';
            }
            else {
                $this->view->friendRankEmoji = 'F99F';
            }

            if ($mixiRankEnd < $mixiRankBefore) {
                $this->view->mixiRankEmoji = 'F99A';
            }
            else {
                $this->view->mixiRankEmoji = 'F99F';
            }
        }

        //get user asset
        require_once 'Dal/Parking/Puser.php';
        $dalParkPuser = new Dal_Parking_Puser();
        $ass = $dalParkPuser->getAllAss($this->_user->getId());
        $this->view->asset = number_format($ass['asset']);

        require_once 'Dal/Parking/Car.php';
        $dalCar = Dal_Parking_Car::getDefaultInstance();
        $this->view->car = $dalCar->getParkingCarInfo($car_id);
        $this->view->car_color = $car_color;

        if ($pid > 0) {
            $user = Bll_User::getPerson($pid);
            $this->view->parking_user = $user->getDisplayName();
        }
        else {
            require_once 'Dal/Parking/Neighbor.php';
            $dalNeighbor = new Dal_Parking_Neighbor();
            $this->view->parking_user = $dalNeighbor->getNeighborName($pid);
        }

        $this->view->free = $free;

        $this->view->money = $this->_request->getParam('parking_money');
        $this->view->pid = $pid;
        $this->render();
    }

    /**
     * parking fail action
     *
     */
    public function parkingfailAction()
    {
        $pid = $this->_request->getParam('parking_pid');
        $car_id = $this->_request->getParam('parking_car_id');
        $car_color = $this->_request->getParam('parking_car_color');
        $flag = $this->_request->getParam('parking_flag', 1);
        if ($flag == -2) {
            $this->view->message = '別の車が駐車中です。';
        }
        else if ($flag == -3) {
            $this->view->message = '同じ駐車場には、連続で駐車できません。';
        }
        else if ($flag == -4) {
            $this->view->message = '1時間未満のため、移動できません。';
        }
        else if ($flag == -11) {
            $this->view->message = 'この区画には廃車ｶｰﾄﾞが設置されているため、駐車できませんでした。';
        }
        else if ($flag == -14) {
            $this->view->message = 'この区画には廃車ｶｰﾄﾞが設置されているため、駐車できませんでした。';
        }

        require_once 'Dal/Parking/Car.php';
        $dalCar = Dal_Parking_Car::getDefaultInstance();
        $car = $dalCar->getParkingCarInfo($car_id);
        $this->view->cav_name = $car['cav_name'];
        $this->view->car_color = $car_color;

        if ($pid > 0) {
            $user = Bll_User::getPerson($pid);
            $this->view->parking_user = $user->getDisplayName();
        }
        else {
            require_once 'Dal/Parking/Neighbor.php';
            $dalNeighbor = new Dal_Parking_Neighbor();
            $this->view->parking_user = $dalNeighbor->getNeighborName($pid);
        }

        $this->view->pid = $pid;
        $this->render();
    }

    /**
     * parking bomb action
     *
     */
    public function parkingbombAction()
    {
        $pid = $this->_request->getParam('parking_pid');
        $car_id = $this->_request->getParam('parking_car_id');
        $car_color = $this->_request->getParam('parking_car_color');

        require_once 'Dal/Parking/Car.php';
        $dalCar = Dal_Parking_Car::getDefaultInstance();
        $this->view->car = $dalCar->getParkingCarInfo($car_id);

        if ($pid > 0) {
            $user = Bll_User::getPerson($pid);
            $this->view->parking_user = $user->getDisplayName();
        }
        else {
            require_once 'Dal/Parking/Neighbor.php';
            $dalNeighbor = new Dal_Parking_Neighbor();
            $this->view->parking_user = $dalNeighbor->getNeighborName($pid);
        }

        $this->view->pid = $pid;
        $this->view->car_color = $car_color;
        $this->render();
    }

    /**
     * parking evasion action
     *
     */
    public function parkingevasionAction()
    {
        $pid = $this->_request->getParam('parking_pid');
        if ($pid > 0) {
            $user = Bll_User::getPerson($pid);
            $this->view->parking_user = $user->getDisplayName();
        }
        else {
            require_once 'Dal/Parking/Neighbor.php';
            $dalNeighbor = new Dal_Parking_Neighbor();
            $this->view->parking_user = $dalNeighbor->getNeighborName($pid);
        }


        $this->view->pid = $pid;
        $this->view->flag = $this->_request->getParam('parking_flag', 1);
        $this->render();
    }

    /**
     * parking check action
     *
     */
    public function parkingcheckAction()
    {
        $uid = $this->_user->getId();

        $this->view->pre_user = urldecode($this->_request->getParam('parking_pre_user'));

        $pid = $this->_request->getParam('parking_pid');

        if ($pid > 0) {
            $user = Bll_User::getPerson($pid);
            $this->view->cur_user = $user->getDisplayName();
        }
        else {
            require_once 'Dal/Parking/Neighbor.php';
            $dalNeighbor = new Dal_Parking_Neighbor();
            $this->view->cur_user = $dalNeighbor->getNeighborName($pid);
        }

        $money = $this->_request->getParam('parking_money');
        $this->view->money = $money;
        $this->view->pid = $pid;

        //get car info
        $car_id = $this->_request->getParam('parking_car_id');
        $this->view->car_color =  $this->_request->getParam('parking_car_color');
        require_once 'Dal/Parking/Car.php';
        $dalCar = Dal_Parking_Car::getDefaultInstance();
        $car = $dalCar->getParkingCarInfo($car_id);
        $this->view->car_name = $car['name'];
        $this->view->cav_name = $car['cav_name'];

        //get user asset
        require_once 'Dal/Parking/Puser.php';
        $dalParkPuser = new Dal_Parking_Puser();
        $ass = $dalParkPuser->getAllAss($this->_user->getId());
        $this->view->asset = number_format($ass['asset']);

        //get ranking
        require_once 'Bll/Parking/Friend.php';
        $friendIds = Bll_Parking_Friend::getFriends($uid);

        require_once 'Mdal/Parking/Puser.php';
        $mdalParkPuser = new Mdal_Parking_Puser();
        $friendRank = $mdalParkPuser->getUserRankNm($uid, $friendIds, 1, 1);
        $mixiRank = $mdalParkPuser->getUserRankNm($uid, $friendIds, 2, 1);

        $this->view->friendRank = $friendRank['rank'];
        $this->view->mixiRank = $mixiRank['rank'];

        $this->render();
    }

    /**
     * report action
     *
     */
    public function reportAction()
    {
        $pid = $this->_request->getParam('parking_pid');
        $location = $this->_request->getParam('parking_location');
        $anonymous = $this->_request->getParam('parking_anonymous', -1);

        if (empty($pid) || empty($location)) {
            $this->_redirect($this->_baseUrl . '/mobile/parking/error');
            return;
        }

        //check report
        require_once 'Mbll/Parking/Parking.php';
        $mbllParking = new Mbll_Parking_Parking();
        $reported = $mbllParking->checkReport($this->_user->getId(), $pid, $location, $car_uid, $car_id, $car_color, $car_name);

        if ($reported == -1) {
            $this->_redirect($this->_baseUrl . '/mobile/parking/error');
            return;
        }
        else if ($reported == 1) {
            $this->_redirect($this->_baseUrl . '/mobile/parking/reportover?parking_flag=1&parking_pid=' . $pid . '&parking_car_uid=' . $car_uid . '&parking_car_name=' . urlencode($car_name));
            return;
        }

        require_once 'Bll/Parking/Index.php';
        $bllParkIndex = new Bll_Parking_Index();

        //report
        if ($anonymous == 0) {
            $result = $bllParkIndex->report($this->_user->getId(), $pid, $car_uid, $car_id, $car_color, $location, 0);

            if ($result['status'] == 0) {
                $this->_redirect($this->_baseUrl . '/mobile/parking/error');
            }
            else {
                $this->_redirect($this->_baseUrl . '/mobile/parking/reportover?parking_flag=3&parking_pid=' . $pid . '&parking_car_uid=' . $car_uid . '&parking_car_name=' . urlencode($car_name));
            }
        }
        //report by anonymous
        else if ($anonymous == 1) {
            $result = $bllParkIndex->report($this->_user->getId(), $pid, $car_uid, $car_id, $car_color, $location, 1);

            if ($result['status'] == 0) {
                $this->_redirect($this->_baseUrl . '/mobile/parking/error');
            }
            else {
                $this->_redirect($this->_baseUrl . '/mobile/parking/reportover?parking_flag=2&parking_pid=' . $pid . '&parking_car_uid=' . $car_uid . '&parking_car_name=' . urlencode($car_name));
            }
        }
        else {
            //check anonymous card
            require_once 'Dal/Parking/Card.php';
            $dalCard = Dal_Parking_Card::getDefaultInstance();
            $cardCount = $dalCard->getUserCardCoutByCid(2, $this->_user->getId());

            //report
            if ($cardCount == 0) {
                $result = $bllParkIndex->report($this->_user->getId(), $pid, $car_uid, $car_id, $car_color, $location, 0);

                if ($result['status'] == 0) {
                    $this->_redirect($this->_baseUrl . '/mobile/parking/error');
                }
                else {
                    $this->_redirect($this->_baseUrl . '/mobile/parking/reportover?parking_flag=3&parking_pid=' . $pid . '&parking_car_uid=' . $car_uid . '&parking_car_name=' . urlencode($car_name));
                }
                return;
            }

            $this->view->pid = $pid;
            $this->view->location = $location;
            $this->render();
        }
    }

    /**
     * report over action
     *
     */
    public function reportoverAction()
    {
        $car_name = urldecode($this->_request->getParam('parking_car_name'));
        $flag = $this->_request->getParam('parking_flag' , 1);

        $pid = $this->_request->getParam('parking_pid' , $this->_user->getId());

        if ($pid > 0) {
            $user = Bll_User::getPerson($pid);
            $this->view->parkingUserName = $user->getDisplayName();
        }
        else {
            require_once 'Dal/Parking/Neighbor.php';
            $dalNeighbor = new Dal_Parking_Neighbor();
            $this->view->parkingUserName = $dalNeighbor->getNeighborName($pid);
        }

        $car_uid = $this->_request->getParam('parking_car_uid' , $this->_user->getId());
        $car_user = Bll_User::getPerson($car_uid);
        $carUserName = $car_user->getDisplayName();

        //have reported
        if ($flag == 1) {
            $this->view->message = $carUserName . 'の'. $car_name . 'は警察に通報済です。';
            $this->view->image = 1;
            $this->view->emoji = 'F9AB';
        }
        //report over  anonymous
        else if ($flag == 2) {
            $this->view->message = $carUserName . 'の'. $car_name . 'を匿名で通報しました';
            $this->view->image = 2;
            $this->view->emoji = 'F9F8';
        }
        //report over
        else {
            $this->view->message = $carUserName . 'の'. $car_name . 'を警察に通報しました';
            $this->view->image = 1;
            $this->view->emoji = 'F9F8';
        }

        $this->view->pid = $pid;
        $this->render();
    }

    /**
     * stick action
     *
     */
    public function stickAction()
    {
        $location = $this->_request->getParam('parking_location');

        if (empty($location)) {
            $this->_redirect($this->_baseUrl . '/mobile/parking/error');
            return;
        }

        //get before ranking
        require_once 'Bll/Parking/Friend.php';
        $friendIds = Bll_Parking_Friend::getFriends($this->_user->getId());

        require_once 'Mdal/Parking/Puser.php';
        $mdalParkPuser = new Mdal_Parking_Puser();
        $friendRankBefore = $mdalParkPuser->getUserRankNm($this->_user->getId(), $friendIds, 1, 1);
        $mixiRankBefore = $mdalParkPuser->getUserRankNm($this->_user->getId(), $friendIds, 2, 1);

        //get location info
        require_once 'Mdal/Parking/Puser.php';
        $mdalPuser = new Mdal_Parking_Puser();
        $parking_user = $mdalPuser->getParkingInfoByLocation($this->_user->getId(), $location);

        //stick car
        require_once 'Bll/Parking/Index.php';
        $bllParkIndex = new Bll_Parking_Index();
        $result = $bllParkIndex->stick($this->_user->getId(), $location);

        if ($result['status'] == -1) {
            $this->_redirect($this->_baseUrl . '/mobile/parking/error');
            return;
        }

        if ($result['status'] == -2) {
            $this->_redirect($this->_baseUrl . '/mobile/parking/stickfail?parking_cav_name=' . $parking_user['cav_name'] . '&parking_car_color=' . $parking_user['car_color']);
            return;
        }

        //get car_user name
        $car_user = Bll_User::getPerson($parking_user['uid']);
        $this->view->car_user = $car_user->getDisplayName();

        //get end ranking
        $friendRankEnd = $mdalParkPuser->getUserRankNm($this->_user->getId(), $friendIds, 1, 1);
        $mixiRankEnd = $mdalParkPuser->getUserRankNm($this->_user->getId(), $friendIds, 2, 1);

        $this->view->friendRanking = $friendRankEnd;
        $this->view->mixiRanking = $mixiRankEnd;

        if ($friendRankEnd < $friendRankBefore) {
            $this->view->friendRankEmoji = 'F99A';
        }
        else {
            $this->view->friendRankEmoji = 'F99F';
        }


        if ($mixiRankEnd < $mixiRankBefore) {
            $this->view->mixiRankEmoji = 'F99A';
        }
        else {
            $this->view->mixiRankEmoji = 'F99F';
        }

        /*
        //post activity
        require_once 'Bll/Parking/Activity.php';
        $activity = Bll_Parking_Activity::getActivity($this->_user->getId(), $parking_user['uid'], array('car_name'=>$parking_user['name']), 1);
        require_once 'Bll/Restful.php';
        //get restful object
        $restful = Bll_Restful::getInstance($this->_user->getId(), APP_ID);
        $restful->createActivity('MOBILE' . $activity);*/

        //get user asset
        require_once 'Dal/Parking/Puser.php';
        $dalParkPuser = new Dal_Parking_Puser();
        $ass = $dalParkPuser->getAllAss($this->_user->getId());
        $this->view->asset = number_format($ass['asset']);

        $this->view->cav_name = $parking_user['cav_name'];
        $this->view->car_name = $parking_user['name'];
        $this->view->car_color = $parking_user['car_color'];
        $this->view->money = number_format($result['money']);
        $this->render();
    }

    /**
     * stick fail action
     *
     */
    public function stickfailAction()
    {
        $this->view->cav_name = $this->_request->getParam('parking_cav_name');
        $this->view->car_color = $this->_request->getParam('parking_car_color');
        $this->render();
    }

    /**
     * carshop action
     *
     */
    public function carshopAction()
    {
        $pageIndex = (int)$this->_request->getParam('parking_page', 1);
        $uid = $this->_user->getId();

        require_once 'Bll/Parking/Cache.php';
        $carCount = Bll_Parking_Cache::getCarShopCount();
        $carList = Bll_Parking_Cache::getCarShopList($pageIndex, $this->_pageSize);

        //add rows 'count' and 'isown',count->how many the same cid car which the user has,isown->this kind of car belongs to the user?1->yes,0->no
        foreach ($carList as $key => $value) {
            $carList[$key]['count'] = 0;
            $carList[$key]['isown'] = 0;
        }

        //get the most expensive car's price
        require_once 'Dal/Parking/Car.php';
        $dalCar = Dal_Parking_Car::getDefaultInstance();
        $maxPrice = $dalCar->getMaxPriceOfUserCars($uid);

        require_once 'Mdal/Parking/Car.php';
        $mdalCar = new Mdal_Parking_Car();
        //get user asset
        $userAsset = $mdalCar->getUserAsset($uid);
        //get user each car's count
        $userEachCarCount = $mdalCar->getUserEachCarCount($uid);

        foreach ($userEachCarCount as $key => $value) {
            if ($carList[$value['car_id']-1]['cid'] == $value['car_id']) {
                $carList[$value['car_id']-1]['count'] = $value['count'];
            }
        }
        //get user car list about car_id
        $userCarList = $mdalCar->getUserCarList($uid);

        foreach ($userCarList as $key => $value) {
            if ($carList[$value['car_id']-1]['cid'] == $value['car_id']) {
                $carList[$value['car_id']-1]['isown'] = 1;
            }
        }

        //if user have allow card  1->yes  0->no
        $userCard = $dalCar->isUserHaveTheCard($uid);
        $haveCard = $userCard['count'] > 0 ? 1 : 0;

        $this->view->pager = array('count' => $carCount,
                                   'pageIndex' => $pageIndex,
                                   'requestUrl' => 'mobile/parking/carshop',
                                   'pageSize' => $this->_pageSize,
                                   'maxPager' => ceil($carCount / $this->_pageSize)
                                   );
        $this->view->carList = $carList;
        $this->view->userAsset = $userAsset;
        $this->view->maxPrice = $maxPrice;
        $this->view->haveCard = $haveCard;

        $this->render();
    }
    /**
     * buy car action
     *
     */
    public function buycarAction()
    {
        $uid = $this->_user->getId();
        $carId = $this->_request->getParam('parking_cid');

         //get user asset
        require_once 'Mdal/Parking/Car.php';
        $mdalCar = new Mdal_Parking_Car();
        $userAsset = $mdalCar->getUserAsset($uid);

        require_once 'Dal/Parking/Car.php';
        $dalCar = Dal_Parking_Car::getDefaultInstance();
        $carInfo = $dalCar->getParkingCarInfo($carId);

        $remainAsset = $userAsset - $carInfo['price'];

        $this->view->carInfo = $carInfo;
        $this->view->remainAsset = $remainAsset;
        $this->render();
    }
    /**
     * buy car submit action
     *
     */
    public function buycarsubmitAction()
    {
        $uid = $this->_user->getId();
        $carId = $this->_request->getParam('carId');
        $carColor = $this->_request->getParam('color');
        $carName = $this->_request->getParam('carName');
        $cavName = $this->_request->getParam('cavName');

        require_once 'Bll/Parking/Carshop.php';
        $bllCar = new Bll_Parking_Carshop();
        $result = $bllCar->buyCar($uid, $carId, $carColor);

        $title = '購入失敗';
        $message = '';

        if ($result == 1) {
        	$title = '購入完了';
            $message = $carName . 'を購入しました｡';
        }
        else if ($result == -2) {
            $message = '現金が不足しています';
        }
        else if ($result == -3) {
            $message = '同じ車を所有しているため、購入できません。';
        }
        else if ($result == -4) {
            $message = '8台の車を所有しているため、購入できませんでした。';
        }
        else if ($result == -5) {
            $message = '同車種・同色の車を2台以上所有できません。';
        }
        else {
            $this->_redirect($this->_baseUrl . '/mobile/parking/error');
        }

        $this->view->cavName = $cavName;
        $this->view->carColor = $carColor;
        $this->view->message = $message;
        $this->view->title = $title;

        $this->render();
    }
    /**
     * buy same car action
     *
     */
    public function buysamecarAction()
    {
        $carId = $this->_request->getParam('parking_cid');

        require_once 'Dal/Parking/Car.php';
        $dalCar = new Dal_Parking_Car();
        $carInfo = $dalCar->getParkingCarInfo($carId);

        $this->view->carInfo = $carInfo;

        $this->render();
    }
    /**
     * change car select action
     *
     */
    public function changecarselectAction()
    {
        $uid = $this->_user->getId();
        //new car id
        $cid = $this->_request->getParam('parking_cid');

        require_once 'Mdal/Parking/Car.php';
        $mdalCar = new Mdal_Parking_Car();
        //get user asset
        $userAsset = $mdalCar->getUserAsset($uid);
        //get user cars
        $cars = $mdalCar->getUserCarsWhenChangeCar($uid);

        require_once 'Dal/Parking/Car.php';
        $dalCar = new Dal_Parking_Car();
        $newCar = $dalCar->getParkingCarInfo($cid);
        $newCarPrice = $newCar['price'];
        //use have the same car?1->yes, 0->no
        $haveSameCar = 0;
        foreach ($cars as $value) {
            if ($value['car_id'] == $cid) {
                $haveSameCar = 1;
                break;
            }
        }

        $this->view->userAsset = $userAsset;
        $this->view->cars = $cars;
        $this->view->newCarPrice = $newCarPrice;
        $this->view->ncid = $cid;
        $this->view->haveSameCar = $haveSameCar;
        $this->view->carColor = array('black' => '黒',
                                      'white' => '白',
                                      'silver' => '銀',
                                      'yellow' => '黄',
                                      'red' => '赤',
                                      'blue' => '青');
        $this->render();
    }
    /**
     * change car confirm action
     *
     */
    public function changecarconfirmAction()
    {
        $uid = $this->_user->getId();
        $oldCarId = $this->_request->getParam('parking_ocid');
        $newCarId = $this->_request->getParam('parking_ncid');
        $oldCarColor = $this->_request->getParam('parking_oldcolor');

        require_once 'Mdal/Parking/Car.php';
        $mdalCar = new Mdal_Parking_Car();
        $userAsset = $mdalCar->getUserAsset($uid);

        require_once 'Dal/Parking/Car.php';
        $dalCar = new Dal_Parking_Car();
        //get about new car infomation
        $newCarInfo = $dalCar->getParkingCarInfo($newCarId);
        //get about old car infomation
        $oldCarInfo = $dalCar->getParkingCarInfo($oldCarId);

        $remainAsset = $userAsset - ($newCarInfo['price'] - $oldCarInfo['price'] * 0.9);

        $this->view->remainAsset = $remainAsset;
        $this->view->carInfo = $newCarInfo;
        $this->view->oldCarId = $oldCarId;
        $this->view->newCarId = $newCarId;
        $this->view->oldCarColor = $oldCarColor;

        $this->render();
    }
    /**
     * change car submit action
     *
     */
    public function changecarsubmitAction()
    {
        $uid = $this->_user->getId();
        $oldCarId = $this->_request->getParam('oldCarId');
        $oldCarColor = $this->_request->getParam('oldCarColor');
        $newCarId = $this->_request->getParam('newCarId');
        $newCarColor = $this->_request->getParam('color');
        $newCarName = $this->_request->getParam('newCarName');
        $newCarCavName = $this->_request->getParam('newCarCavName');

        require_once 'Bll/Parking/Carshop.php';
        $bllCar = new Bll_Parking_Carshop();
        $result = $bllCar->changeCar($uid, $newCarId, $newCarColor, $oldCarId, $oldCarColor);

        $title = '購入失敗';
        $message = '';

        if ($result == 1) {
        	$title = '購入完了';
            $message = $newCarName . 'を購入しました｡';
        }
        else if ($result == -2) {
            $message = '現金が不足しています。';
        }
        else if ($result == -3) {
            $message = '同じ車を所有しているため、購入できません。';
        }
        else {
            $this->_redirect($this->_baseUrl . '/mobile/parking/error');
        }

        $this->view->newCarCavName = $newCarCavName;
        $this->view->newCarColor = $newCarColor;
        $this->view->message = $message;
        $this->view->title = $title;

        $this->render();
    }
    /**
     * change the same car action
     *
     */
    public function changesamecarAction()
    {
        $oldCarId = $this->_request->getParam('parking_ocid');
        $oldCarColor = $this->_request->getParam('parking_oldcolor');
        $newCarId = $this->_request->getParam('parking_ncid');

        require_once 'Dal/Parking/Car.php';
        $dalCar = new Dal_Parking_Car();
        $carInfo = $dalCar->getParkingCarInfo($newCarId);

        $this->view->carInfo = $carInfo;
        $this->view->oldCarId = $oldCarId;
        $this->view->oldCarColor = $oldCarColor;
        $this->view->newCarId = $newCarId;

        $this->render();
    }
    /**
     * show house list action
     *
     */
    public function houseAction()
    {
        $pageIndex = (int)$this->_request->getParam('parking_page', 1);
        $uid = $this->_user->getId();

        require_once 'Mdal/Parking/Car.php';
        $mdalCar = new Mdal_Parking_Car();
        $userAsset = $mdalCar->getUserAsset($uid);

        require_once 'Dal/Parking/House.php';
        $dalHouse = Dal_Parking_House::getDefaultInstance();
        $oldHouseInfo = $dalHouse->getOldHouseInfo($uid);

        $oldHouseType = 0;
        switch ($oldHouseInfo['type']) {
            case 'A':
                $oldHouseType = 3;
                break;
            case 'B':
                $oldHouseType = 4;
                break;
            case 'C':
                $oldHouseType = 5;
                break;
            case 'D':
                $oldHouseType = 6;
                break;
            case 'E':
                $oldHouseType = 7;
                break;
            case 'F':
                $oldHouseType = 8;
                break;
            default:
                break;

        }

        require_once 'Bll/Parking/Cache.php';
        $houseCount = Bll_Parking_Cache::getHouseCount();
        $houseList = Bll_Parking_Cache::getHouseList($pageIndex, $this->_pageSize);

        foreach ($houseList as $key => $value) {
            switch ($value['type']) {
                case 'A':
                    $houseList[$key]['type'] = 3;
                    break;
                case 'B':
                    $houseList[$key]['type'] = 4;
                    break;
                case 'C':
                    $houseList[$key]['type'] = 5;
                    break;
                case 'D':
                    $houseList[$key]['type'] = 6;
                    break;
                case 'E':
                    $houseList[$key]['type'] = 7;
                    break;
                case 'F':
                    $houseList[$key]['type'] = 8;
                    break;
                default:
                    break;
            }
        }

        $this->view->pager = array('count' => $houseCount,
                                   'pageIndex' => $pageIndex,
                                   'requestUrl' => 'mobile/parking/house',
                                   'pageSize' => $this->_pageSize,
                                   'maxPager' => ceil($houseCount / $this->_pageSize)
                                   );
        $this->view->userAsset = $userAsset;
        $this->view->count = $houseCount;
        $this->view->oldHousePrice = $oldHouseInfo['price'];
        $this->view->oldHouseType = $oldHouseType;
        $this->view->oldHouseId = $oldHouseInfo['id'];
        $this->view->houseList = $houseList;

        $this->render();
    }
    /**
     * buy house confirm action
     *
     */
    public function buyhouseAction()
    {
        $uid = $this->_user->getId();
        $newHouseId = $this->_request->getParam('parking_hid');

        require_once 'Mdal/Parking/Car.php';
        $mdalCar = new Mdal_Parking_Car();
        $userAsset = $mdalCar->getUserAsset($uid);

        //get old house infomation
        require_once 'Dal/Parking/House.php';
        $dalHouse = Dal_Parking_House::getDefaultInstance();
        $oldHouseInfo = $dalHouse->getOldHouseInfo($uid);

        //get new  house infomation
        require_once 'Mdal/Parking/House.php';
        $mdalHouse = Mdal_Parking_House::getDefaultInstance();
        $newHouseInfo = $mdalHouse->getHouseInfoById($newHouseId);

        $allowCarCount = 0;
        switch ($newHouseInfo['type']) {
            case 'A':
                $allowCarCount = 3;
                break;
            case 'B':
                $allowCarCount = 4;
                break;
            case 'C':
                $allowCarCount = 5;
                break;
            case 'D':
                $allowCarCount = 6;
                break;
            case 'E':
                $allowCarCount = 7;
                break;
            case 'F':
                $allowCarCount = 8;
                break;
            default:
                break;
        }

        $remainAsset = $userAsset - ($newHouseInfo['price'] - $oldHouseInfo['price'] * 0.9);

        $this->view->remainAsset = $remainAsset;
        $this->view->allowCarCount = $allowCarCount;
        $this->view->newHouseInfo = $newHouseInfo;
        $this->view->oldHouseId = $oldHouseInfo['id'];

        $this->render();
    }
    /**
     * buy house submit action
     *
     */
    public function buyhousesubmitAction()
    {
        $uid = $this->_user->getId();
        $newHouseId = $this->_request->getParam('parking_nid');

        require_once 'Mdal/Parking/House.php';
        $mdalHouse = Mdal_Parking_House::getDefaultInstance();
        $newHouseInfo = $mdalHouse->getHouseInfoById($newHouseId);

        require_once 'Bll/Parking/House.php';
        $bllHouse = new Bll_Parking_House();
        $result = $bllHouse->buyHouse($newHouseId, $uid);

        $title = '購入失敗';
        $message = '';

        switch ($result) {
            case 0:
                $message = '現金が不足しています。';
                break;
            case 1:
            	$title = '購入完了';
                $message = $newHouseInfo['name'] . '購入しました。';
                break;
            case 2:
                $message = 'すでに所有しています。';
                break;
            case 3:
                $message = '今の不動産よりランクが低い不動産は購入できません。';
                break;
            default:
                $this->_redirect($this->_baseUrl . '/mobile/parking/error');

        }

        $this->view->message = $message;
        $this->view->newHouseInfo = $newHouseInfo;
        $this->view->title = $title;

        $this->render();
    }
    /**
     * show item shop action
     *
     */
    public function itemshopAction()
    {
        $pageIndex = (int)$this->_request->getParam('parking_page', 1);
        $uid = $this->_user->getId();

        //get user asset
        require_once 'Mdal/Parking/Car.php';
        $mdalCar = new Mdal_Parking_Car();
        $userAsset = $mdalCar->getUserAsset($uid);

        //get house count and list
        require_once 'Bll/Parking/Cache.php';
        $count = Bll_Parking_Cache::getItemCount();
        $itemList = Bll_Parking_Cache::getItemList($pageIndex, 12);

        //check if user have free park
        require_once 'Dal/Parking/Store.php';
        $dalStore = new Dal_Parking_Store();
        $havaFreePark = $dalStore->getFreePark($uid);

        //check if user have free card
        require_once 'Mdal/Parking/Store.php';
        $mdalStore = new Mdal_Parking_Store();
        $haveFreeCard = $mdalStore->haveFreeCard($uid);

        $this->view->pager = array('count' => $count,
                                   'pageIndex' => $pageIndex,
                                   'requestUrl' => 'mobile/parking/itemshop',
                                   'pageSize' => 12,
                                   'maxPager' => ceil($count / 12)
                                   );
        $this->view->itemList = $itemList;
        $this->view->userAsset = $userAsset;
        $this->view->havaFreePark = $havaFreePark['free_park'];
        $this->view->haveFreeCard = $haveFreeCard;

        $this->render();
    }

    /**
     * buy item action
     *
     */
    public function buyitemAction()
    {
        $uid = $this->_user->getId();
        $cardId = $this->_request->getParam('parking_cid');

        //get user asset
        require_once 'Mdal/Parking/Car.php';
        $mdalCar = new Mdal_Parking_Car();
        $userAsset = $mdalCar->getUserAsset($uid);

        //get card infomation
        require_once 'Dal/Parking/Store.php';
        $dalStore = Dal_Parking_Store::getDefaultInstance();
        $cardInfo = $dalStore->getCardInfo($cardId);

        $remainAsset = $userAsset - $cardInfo['price'];

        $this->view->itemInfo = $cardInfo;
        $this->view->remainAsset = $remainAsset;
        $this->view->cardId = $cardId;

        $this->render();
    }

    /**
     * buy item submit action
     *
     */
    public function buyitemsubmitAction()
    {
        $uid = $this->_user->getId();
        $cardId = (int)$this->_request->getParam('parking_cid');
        $title = '購入失敗';
        $message = '';
        //get card infomation
        require_once 'Dal/Parking/Store.php';
        $dalStore = Dal_Parking_Store::getDefaultInstance();
        $cardInfo = $dalStore->getCardInfo($cardId);

        require_once 'Bll/Parking/Item.php';
        $bllItem = new Bll_Parking_Item();
        //buy changeParkCard which id=1
        if ($cardId == 1) {

            $result = $bllItem->buyChangeParkCard($uid);

            switch ($result) {
                case 0:
                    $message = '現金が不足しています。';
                    break;
                case 1:
                	$title = '購入完了';
                    $message = '有料駐車場カード購入しました。';
                    break;
                case -2:
                    $message = '有料駐車場カードは1枚以上所有できません。';
                    break;
                case -3:
                    $message = '無料駐車場がないため、購入できません。';
                    break;
                default:
                    $this->_redirect($this->_baseUrl . '/mobile/parking/error');

            }
        }
        //buy other card
        else {

            $result = $bllItem->buyCard($cardId,$uid);

            switch ($result) {
                case 0:
                    $message = '現金が不足しています。';
                    break;
                case 1:
                	$title = '購入完了';
                    $message = $cardInfo['name'] . '購入しました。';
                    break;
                default:
                    $this->_redirect($this->_baseUrl . '/mobile/parking/error');

            }
        }

       //the following check the new card which can use now,$canUse=1->yes
        $canUse = 1;
        $last_bribery_time = 0;
        $last_evasion_time = 0;
        $last_check_time = 0;

        if ($cardId == 3 || $cardId == 6 || $cardId == 7 || $cardId == 8) {
            require_once 'Mdal/Parking/Store.php';
            $mdalStore = new Mdal_Parking_Store();
            $userItemInfo = $mdalStore->getUserTimeLimitCardContinueTime($uid);
        }
        //bribery card
        if ($cardId == 3) {
            $last_bribery_time = (time() - $userItemInfo['last_bribery_time']) / ( 24*3600 );
            if ($last_bribery_time <= 3) {
                $canUse = 0;
            }
        }
        //check card
        if ($cardId == 6) {
            $last_check_time = (time() - $userItemInfo['last_check_time']) / ( 24*3600 );
            if ($last_check_time <= 1) {
                $canUse = 0;
            }
        }
        //evasion card
        if ($cardId == 8) {
            $last_evasion_time = (time() - $userItemInfo['last_evasion_time']) / ( 24*3600 );
            if ($last_evasion_time <= 2) {
                $canUse = 0;
            }
        }
        //insurance card
        if ($cardId == 7) {
            if ($userItemInfo['insurance_card'] == 1) {
                $canUse = 0;
            }
        }
        //yankee card
        if ($cardId == 11) {
            require_once 'Bll/Parking/Friend.php';
            $fids = Bll_Parking_Friend::getFriendIds($uid);
            if ( empty($fids) ) {
               $canUse = 0;
            }
        }
        //repair card
        if ($cardId == 10) {
            require_once 'Dal/Parking/Car.php';
            $dalCar = Dal_Parking_Car::getDefaultInstance();
            $breakCars = $dalCar->getUserbreakCars($uid);
            if ( empty($breakCars) ) {
                $canUse = 0;
            }
        }
        //guard card
        if ($cardId == 9) {
            require_once 'Dal/Parking/Store.php';
            $dalStore = new Dal_Parking_Store();
            $userYanKiItemInfo = $dalStore->getUserYanKiItemInfo($uid);

            $last_yanki_time = 0;

            for($i = 1; $i <= 8; $i++){
                $last_yanki_time = (time() - $userYanKiItemInfo['location'.$i]) / (24*3600);
                if ($last_yanki_time <= 3) {
                    break;
                }
            }

            if ($last_yanki_time > 3) {
                $canUse = 0;
            }
        }
        $this->view->canUse = $canUse;
        $this->view->cardId = $cardId;
        $this->view->message = $message;
        $this->view->title = $title;

        $this->render();
    }

    /**
     * item list action,use item page
     *
     */
    public function itemAction()
    {
        $uid = $this->_user->getId();

        //get user asset
        require_once 'Mdal/Parking/Car.php';
        $mdalCar = new Mdal_Parking_Car();
        $userAsset = $mdalCar->getUserAsset($uid);

        require_once 'Dal/Parking/Store.php';
        $dalStore = new Dal_Parking_Store();
        $havaFreePark = $dalStore->getFreePark($uid);

        //get user all items
        require_once 'Mdal/Parking/Store.php';
        $mdalStore = new Mdal_Parking_Store();
        $userItemInfo = $mdalStore->getUserAllItems($uid);

        require_once 'Bll/Parking/Friend.php';
        $haveFriends = Bll_Parking_Friend::getFriendIds($uid);

        $last_bribery_time = 0;
        $last_evasion_time = 0;
        $last_check_time = 0;

        foreach ($userItemInfo as $value) {
            if ($value['sid'] == 3) {
                $last_bribery_time = (time() - $value['last_bribery_time']) / ( 24*3600 );
            }
            if ($value['sid'] == 8) {
                $last_evasion_time = (time() - $value['last_evasion_time']) / ( 24*3600 );
            }
            if ($value['sid'] == 6) {
                $last_check_time = (time() - $value['last_check_time']) / ( 24*3600 );
            }
        }

        $this->view->userItemInfo = $userItemInfo;
        $this->view->last_bribery_time = $last_bribery_time;
        $this->view->last_evasion_time = $last_evasion_time;
        $this->view->last_check_time = $last_check_time;
        $this->view->havaFreePark = $havaFreePark['free_park'];
        $this->view->userAsset = $userAsset;
        $this->view->haveFriends = $haveFriends;

        require_once 'Dal/Parking/Car.php';
        $dalCar = Dal_Parking_Car::getDefaultInstance();
        $breakCars = $dalCar->getUserbreakCars($uid);
        $this->view->hasBreakCar = count($breakCars) > 0 ? 1 : 0;
        
        $this->render();
    }

    /**
     * conform use a item
     *
     */
    public function useitemconformAction()
    {
        $itemId = $this->_request->getParam('parking_id');

        if ($itemId == 11) {
            $this->_redirect($this->_baseUrl . '/mobile/parking/useyankee');
        }

        if ($itemId == 10) {
            $this->_redirect($this->_baseUrl . '/mobile/parking/userepair');
        }

        require_once 'Dal/Parking/Store.php';
        $dalStore = Dal_Parking_Store::getDefaultInstance();
        $itemInfo = $dalStore->getCardInfo($itemId);

        $this->view->itemInfo = $itemInfo;

        $this->render();
    }
    /**
     * use free park card
     *
     */
    public function useitemfreeAction()
    {
        $uid = $this->_user->getId();

        require_once 'Bll/Parking/Useitem.php';
        $bllParkItem = new Bll_Parking_Useitem();
        $result = $bllParkItem->free($uid);

        $this->_redirect($this->_baseUrl . '/mobile/parking/useitemover?parking_id=1&parking_rt=' . $result);
    }
    /**
     * use bribery card
     *
     */
    public function usebriberyAction()
    {
        $uid = $this->_user->getId();

        require_once 'Bll/Parking/Useitem.php';
        $bllParkItem = new Bll_Parking_Useitem();
        $result = $bllParkItem->bribery($uid);

        $this->_redirect($this->_baseUrl . '/mobile/parking/useitemover?parking_id=3&parking_rt=' . $result);
    }
    /**
     * use bomb card
     *
     */
    public function useitembombAction()
    {
        $uid = $this->_user->getId();

        require_once 'Bll/Parking/Useitem.php';
        $bllParkItem = new Bll_Parking_Useitem();
        $result = $bllParkItem->bomb($uid);

        $this->_redirect($this->_baseUrl . '/mobile/parking/useitemover?parking_id=5&parking_rt=' . $result);
    }
    /**
     * use check card
     *
     */
    public function usecheckAction()
    {
        $uid = $this->_user->getId();

        require_once 'Bll/Parking/Useitem.php';
        $bllParkItem = new Bll_Parking_Useitem();
        $result = $bllParkItem->check($uid);

        $this->_redirect($this->_baseUrl . '/mobile/parking/useitemover?parking_id=6&parking_rt=' . $result);
    }
    /**
     * use insurance card
     *
     */
    public function useinsuranceAction()
    {
        $uid = $this->_user->getId();

        require_once 'Bll/Parking/Useitem.php';
        $bllParkItem = new Bll_Parking_Useitem();
        $result = $bllParkItem->insurance($uid);

        $this->_redirect($this->_baseUrl . '/mobile/parking/useitemover?parking_id=7&parking_rt=' . $result);
    }
    /**
     * use evasion card
     *
     */
    public function useevasionAction()
    {
        $uid = $this->_user->getId();

        require_once 'Bll/Parking/Useitem.php';
        $bllParkItem = new Bll_Parking_Useitem();
        $result = $bllParkItem->evasion($uid);

        $this->_redirect($this->_baseUrl . '/mobile/parking/useitemover?parking_id=8&parking_rt=' . $result);
    }
    /**
     * use guard card
     *
     */
    public function useguardAction()
    {
        $uid = $this->_user->getId();

        require_once 'Bll/Parking/Useitem.php';
        $bllParkItem = new Bll_Parking_Useitem();
        $result = $bllParkItem->guard($uid);

        $this->_redirect($this->_baseUrl . '/mobile/parking/useitemover?parking_id=9&parking_rt=' . $result);
    }
    /**
     * use guard card, confirm action
     *
     */
    public function userepairAction()
    {
        $uid = $this->_user->getId();

        require_once 'Dal/Parking/Car.php';
        $dalCar = Dal_Parking_Car::getDefaultInstance();
        $breakCars = $dalCar->getUserbreakCars($uid);

        $this->view->breakCars = $breakCars;
        $this->view->carColor = array('black' => '黒',
                                      'white' => '白',
                                      'silver' => '銀',
                                      'yellow' => '黄',
                                      'red' => '赤',
                                      'blue' => '青');
        $this->render();
    }
    /**
     * use guard card, submit action
     *
     */
    public function userepairsubmitAction()
    {
        $uid = $this->_user->getId();
        $carId = $this->_request->getParam('parking_id');
        $carColor = $this->_request->getParam('parking_cl');

        require_once 'Bll/Parking/Useitem.php';
        $bllParkItem = new Bll_Parking_Useitem();
        $result = $bllParkItem->repair($uid, $carId, $carColor);

        $this->_redirect($this->_baseUrl . '/mobile/parking/useitemover?parking_id=10&parking_rt=' . $result);
    }
    /**
     * use yankee card, select friend
     *
     */
    public function useyankeeAction()
    {
        $uid = $this->_user->getId();

        require_once 'Bll/Parking/Friend.php';
        $fids = Bll_Parking_Friend::getFriendIds($uid);

        if ( !empty($fids) ) {
            $friends = array();
            $temp = split(',', $fids);
            foreach ($temp as $item) {
                $friends = array_merge($friends, array(array('uid' => $item)));
            }

            require_once 'Bll/User.php';
            Bll_User::appendPeople($friends);
        }

        $this->view->friends = $friends;

        $this->render();
    }
    /**
     * use yankee card, submit
     *
     */
    public function useyankeesubmitAction()
    {
        $uid = $this->_user->getId();
        $fid = $this->_request->getParam('friend');

        require_once 'Bll/Parking/Useitem.php';
        $bllParkItem = new Bll_Parking_Useitem();
        $result = $bllParkItem->yanki($uid, $fid);

        $this->_redirect($this->_baseUrl . '/mobile/parking/useitemover?parking_id=11&parking_rt=' . $result . '&parking_fid=' . $fid);
    }
    /**
     * use item over
     *
     */
    public function useitemoverAction()
    {
        $itemId = (int)$this->_request->getParam('parking_id');
        $result = (int)$this->_request->getParam('parking_rt');

        $title = '';
        if ($result == 1) {
            $title = 'ｱｲﾃﾑを使用完了';
        }
        else {
        	$title = 'ｱｲﾃﾑを使用失敗';
        }

        if ($itemId == 11) {
            $this->view->fid = $this->_request->getParam('parking_fid');
        }

        require_once 'Mbll/Parking/Item.php';
        $mbllItem = new Mbll_Parking_Item();
        $message = $mbllItem->useItemOver($itemId, $result);

        if ($message == 'システムエラー') {
        	$this->_redirect($this->_baseUrl . '/mobile/parking/error');
        }

        $this->view->message = $message;
        $this->view->itemId = $itemId;
        $this->view->title = $title;

        $this->render();
    }


    /**
     * my car list
     *
     */
    public function carAction()
    {
        $uid = $this->_user->getId();

        //get friend count 
        require_once 'Bll/Parking/Friend.php';
        $friends = Bll_Parking_Friend::getFriends($this->_user->getId());
        $friendsCount = count($friends);        
        
        //get user cars list
        require_once 'Mbll/Parking/MyCar.php';
        $mbllMyCar = new Mbll_Parking_MyCar();
        $cars = $mbllMyCar->getUserCars($uid);

        //can send car? $canSendCar=0->NO $canSendCar=1->YES
        require_once 'Dal/Parking/Puser.php';
        $dalParkPuser = new Dal_Parking_Puser();
        $userPark = $dalParkPuser->getUserPark($uid);

        //user only have one car
        if ( $userPark['car_count'] == 1 ) {
            $canSendCar = 0;
        }
        //from last time when send car to now less than 30 days
        else if (time() - $userPark['send_car_time'] < 30*24*3600) {
            $canSendCar = 0;
        }
        else if ($friendsCount == 0) {
            $canSendCar = 0;
        }
        else {
            $canSendCar = 1;
        }

        $this->view->cars = $cars;
        $this->view->canSendCar = $canSendCar;
        $this->view->carColor = array('black' => '黒',
                                      'white' => '白',
                                      'silver' => '銀',
                                      'yellow' => '黄',
                                      'red' => '赤',
                                      'blue' => '青');
        $this->render();
    }
    /**
     * send car to friend
     *
     */
    public function sendcartofriendAction()
    {
        $uid = $this->_user->getId();
        $carId = $this->_request->getParam('parking_id');
        $carColor = $this->_request->getParam('parking_cl');

        //get send car infomation
        require_once 'Mdal/Parking/Car.php';
        $mdalCar = Mdal_Parking_Car::getDefaultInstance();
        $carInfo = $mdalCar->getOneCar($uid, $carId);

        //get user friends list
        require_once 'Bll/Parking/Friend.php';
        $fids = Bll_Parking_Friend::getFriendIds($uid);

        $friends = array();
        if ( !empty($fids) ) {
            $temp = split(',', $fids);

            $strFriends = '';

            foreach ($temp as $item) {
                $friends = array_merge($friends, array(array('uid' => $item)));
                $strFriends = "'" . $temp[0] . "'";
            }

            for($i = 1; $i < count($friends); $i++){
                $strFriends = $strFriends . ',' . "'" . $friends[$i]['uid'] . "'";
            }

            require_once 'Mdal/Parking/Car.php';
            $mdalCar = new Mdal_Parking_Car();
            $friendsInfo = $mdalCar->getSendCarFriendsInfo($strFriends);

            foreach ($friendsInfo as $key => $value) {
               $receive_car_time = time() - $value['receive_car_time'];

               if ($value['car_count'] == 8 || $receive_car_time < 30*24*3600 || ($value['car_id'] == $carId && $value['car_color'] == $carColor)) {
                   foreach ($friends as $key1 => $value1) {
                       if ($value1['uid'] == $value['uid']) {
                           unset($friends[$key1]);
                       }
                   }
               }
            }

            if ( !empty($friends) ) {
                require_once 'Bll/User.php';
                Bll_User::appendPeople($friends);
            }
        }

        $this->view->carInfo = $carInfo;
        $this->view->friends = $friends;
        $this->view->carColor = $carColor;
        $this->view->carId = $carId;

        $this->render();
    }
    /**
     * send car to friend submit
     *
     */
    public function sendcartofriendsubmitAction()
    {
        $uid = $this->_user->getId();
        $carId = $this->_request->getParam('carId');
        $carColor = $this->_request->getParam('carColor');
        $carName = $this->_request->getParam('carName');
        $cav_Name = $this->_request->getParam('cavName');
        $fid = $this->_request->getParam('friend');

        require_once 'Bll/User.php';
        $parkingUserInfo = Bll_User::getPerson($fid);
        $displayName = $parkingUserInfo->getDisplayName();

        require_once 'Bll/Parking/Index.php';
        $bllParkIndex = new Bll_Parking_Index();
        $result = $bllParkIndex->sendFriend($uid, $carId, $carColor, $fid);

        $title = '友達にﾌﾟﾚｾﾞﾝﾄ失敗';
        $message = '';

        switch ($result) {
            case 1:
            	$title = '友達にﾌﾟﾚｾﾞﾝﾄ完了';
                $message = $carName . 'を' . $displayName . 'にﾌﾟﾚｾﾞﾝﾄしました';
                break;
            case -2:
                $message = '8台の車を所有している友達にはﾌﾟﾚｾﾞﾝﾄできません。';
                break;
            case -3:
                $message = '同じ車を所有している友達にはﾌﾟﾚｾﾞﾝﾄできません。';
                break;
            case -4:
                $message = '前回のﾌﾟﾚｾﾞﾝﾄから1ヵ月以上経っていないため、ﾌﾟﾚｾﾞﾝﾄできませんでした。';
                break;
            case -5:
                $message = '今月、誰かがﾌﾟﾚｾﾞﾝﾄしていたため、ﾌﾟﾚｾﾞﾝﾄできませんでした。';
                break;
            case -6:
                $message = '1台しか持っていない車を友達にプレゼントすることはできません。';
                break;
            default:
                $this->_redirect($this->_baseUrl . '/mobile/parking/error');

        }

        $this->view->carColor = $carColor;
        $this->view->cavName = $cav_Name;
        $this->view->message = $message;
        $this->view->title = $title;

        $this->render();
    }

    /**
     * ranking action
     *
     */

    public function rankAction()
    {
        $uid = $this->_user->getId();
        $startNum = (int)$this->_request->getParam('parking_st');
        $endNum = (int)$this->_request->getParam('parking_ed');
        //get rank type，type1=1->ranking in friends，type2=1->ranking about all asset,first go into rank page,both type1=1 and type2=1
        $type1 = (int)$this->_request->getParam('parking_tp1', 1);
        $type2 = (int)$this->_request->getParam('parking_tp2', 1);

        //first go into rank page
        if (empty($startNum) && empty($endNum)) {
            require_once 'Dal/Parking/Puser.php';
            $dalParkPuser = new Dal_Parking_Puser();
            $userPark = $dalParkPuser->getUserPark($uid);
            //get neighbor infomation
            require_once 'Dal/Parking/Neighbor.php';
            $dalParkNeighbor = new Dal_Parking_Neighbor();
            $neighbor = $dalParkNeighbor->getNeighbor($uid, $userPark['neighbor_left'], $userPark['neighbor_right']);
            //get friends infomation
            require_once 'Bll/Parking/Index.php';
            $bllParkIndex = new Bll_Parking_Index();
            $arrFriend = $bllParkIndex->getArrFriend($uid, $neighbor);

            //get ranking users count
            $count = $dalParkPuser->getRankingCount($uid, $type1, $arrFriend['arrFriendId']);

            require_once 'Bll/User.php';
            require_once 'Bll/Parking/Friend.php';
            $friendIds = Bll_Parking_Friend::getFriends($uid);

            require_once 'Mbll/Parking/Rank.php';
            $mbllRank = new Mbll_Parking_Rank();
            //rank in friends，if user's friends less than 2,add 2 neighbors
            $rankInfo = array();
            if (count($friendIds) < 2 && $type1 == 1) {
               $start = 0;

               require_once 'Mdal/Parking/Rank.php';
               $mdalRank = new Mdal_Parking_Rank();
               $topRank = $mdalRank->getRankingUser($uid, $arrFriend['arrFriendId'], $type1, $type2, 2, 'ASC', $start);

               Bll_User::appendPeople($topRank, 'uid');

               $tempRankInfo = $mbllRank->appendNeighborRank($topRank, $type2);

               $rankInfo = array();

               foreach ($tempRankInfo as $key => $value) {
                    $rankInfo[$key] = $tempRankInfo[count($tempRankInfo)-1-$key];
               }
               
               $this->view->rankCount = count($tempRankInfo);
               $this->view->start = 1;
               $pagestart = 1;
            }
            //if user's friends above and beyond 2 or rank in all users
            else {
               //get rank info about user
                $response = $mbllRank->getRankInfo($uid, $type1, $type2);
                $rankInfo = $response['rankInfo'];

                $start = $response['start'] + 1;
                //5->pagesize
                $end = $response['start'] + 5 > $count ? $count : $response['start'] + 5;

                $userRankNm = $response['userRankNm'];
                //next page start and end rank number
                if ($end < $count) {
                    $this->view->nextStart = $end + 1;
                    $this->view->nextEnd = $end + 5 > $count ? $count : $end + 5;
                }

                //previous page start rank number and end rank number
                if ($userRankNm < 9) {
                    $this->view->preStart = 1;
                    $this->view->preEnd = 5;
                }
                else {

                    if ($userRankNm + 2 <= $count) {
                        $preStart= $userRankNm - 2 - 5;
                        $this->view->preStart = $preStart;
                    }
                    else {
                        $preStart = $count - 4 - 5;
                        $this->view->preStart = $preStart;
                    }
                    $preEnd = $preStart + 4;
                    $this->view->preEnd = $preEnd;
                }

                $this->view->rankCount = $count;
                $this->view->start = $start;
                $this->view->end = $end;
                $pagestart = $start;
            }

            $this->view->friendCount = count($friendIds);
            $this->view->rankInfo = $rankInfo;
        }
        //page turning
        else {
            require_once 'Mbll/Parking/Rank.php';
            $mbllRank = new Mbll_Parking_Rank();
            $response = $mbllRank->newPage($startNum, $uid, $type1, $type2);

            $this->view->rankInfo = $response['rankInfo'];

            $count = $response['rankCount'];
            $this->view->rankCount = $count;
            $this->view->friendCount = $response['rankCount'];

            $this->view->start = $startNum;
            $pagestart = $startNum;
            $this->view->end = $startNum + 4 > $count ? $count : $startNum + 4;

            $nextStart = $startNum + 5;
            $this->view->nextStart = $nextStart > $count ? $count : $nextStart;

            $nextEnd = $nextStart + 4;
            $this->view->nextEnd = $nextEnd > $count ? $count : $nextEnd;

            if ($startNum - 1 > 5) {
                $preStart = $startNum - 5;
                $preEnd = $preStart + 4;
            }
            else {
                $preEnd = 5;
                $preStart = 1;
            }

            $this->view->preEnd = $preEnd;
            $this->view->preStart = $preStart;
        }

        $this->view->uid = $uid;
        $this->view->type1 = $type1;
        $this->view->type2 = $type2;        
        
        //echo $pagestart;
        $this->view->pageindex = ceil(($pagestart - 1)/5) + 1;
                                   
        $this->render();
    }

    /**
     * show feed action
     *
     */
    public function newsfeedAction()
    {
        $uid = $this->_user->getId();
        $type = $this->_request->getParam('parking_tp');

        require_once 'Mbll/Parking/Feed.php';
        $mbllFeed = new Mbll_Parking_Feed();

        $feed = array();
        //if !empty($type)，get newsfeed
        if (!empty($type)) {
            $feed = $mbllFeed->getNewsfeed($uid);
        }
        else {
            $feed = $mbllFeed->getMinifeed($uid);
        }

        $this->view->feed = $feed;
        $this->view->type = $type;

        $this->render();
    }

    /**
     * deipatch
     *
     */
    function preDispatch()
    {
        $this->view->app_name = '駐車戦争';

        //get parking define param
        require_once 'Bll/Parking/Define.php';

        $this->view->rand = rand();        
        $uid = $this->_user->getId();
        $this->view->uid = $uid;
        
        require_once 'Dal/Parking/Puser.php';
        $dalParkingPuser = Dal_Parking_Puser::getDefaultInstance();
        $isIn = $dalParkingPuser->isInParking($uid);

        //user in table:parking_user,user have join in parking
        if ($isIn) {
            require_once 'Bll/Parking/Index.php';
            $bllParkIndex = new Bll_Parking_Index();
            $result = $bllParkIndex->isTodayFirstLogin($uid);

            if (!empty($result)) {
                $dalParkingPuser->updateLastLoginTime($uid);

                $cardId = $result['cid'];

                $this->_redirect($this->_baseUrl . '/mobile/parking/todayvisit?parking_cardId=' . $cardId);
            }
        }
        //user first join parking,send money,house,car
        else {
            require_once 'Mbll/Parking/Parking.php';
            $mbllParking = new Mbll_Parking_Parking();

            if ($mbllParking->firstLogin($uid, $carColor)) {
                $this->_redirect($this->_baseUrl . '/mobile/parking/welcome?parking_color=' . $carColor);
            }
            else {
                $this->_redirect($this->_baseUrl . '/mobile/parking/error' );
            }
        }
    }
    
    /**
     * everyday first login,get gift
     *
     */
    public function todayvisitAction()
    {
        $cardId = $this->_request->getParam('parking_cardId');
        $money = null;

        switch ($cardId) {
            case 12:
                $money = '5000';
                break;
            case 13:
                $money = '50000';
                break;
            case 14:
                $money = '1000000';
                break;
            default:
                break;
        }

        $this->view->cardId = $cardId;
        $this->view->money = $money;

        $this->render();
    }
    /**
     * everyday first login,show gift
     *
     */
    public function visitgiftAction()
    {
        $cardId = $this->_request->getParam('parking_cardId');
        $money = $this->_request->getParam('parking_money');

        if ($cardId < 12 ) {
            require_once 'Dal/Parking/Store.php';
            $dalStore = Dal_Parking_Store::getDefaultInstance();
            $cardInfo = $dalStore->getCardInfo($cardId);

            $this->view->cardInfo = $cardInfo;
        }
        else if ( $cardId >=12 && $cardId <= 14) {
            $this->view->money = $money;
        }
        else {
            require_once 'Dal/Parking/Car.php';
            $dalCar = Dal_Parking_Car::getDefaultInstance();
            $this->view->adCar = $dalCar->getCarPrice(21);
        }
        
        $this->render();
    }
    /**
     * firstly join in parking war
     *
     */
    public function welcomeAction()
    {
        $color = $this->_request->getParam('parking_color');

        $this->view->color = $color;
        $this->render();
    }
    /**
     * firstly join in parking war,start to play game
     *
     */
    public function startgameAction()
    {
        $cav_name = '01_scooter';
        $color = $this->_request->getParam('parking_cl');

        $this->view->color = $color;
        $this->view->cavName = $cav_name;
        $this->render();
    }
    /**
     * help action
     *
     */
    public function helpAction()
    {
        $parm = $this->_request->getParam('parking_parm', 'index');

        $this->view->parm = $parm;
        $this->render();
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
        return $this->_redirect($this->_baseUrl . '/mobile/parking/error');
    }
}
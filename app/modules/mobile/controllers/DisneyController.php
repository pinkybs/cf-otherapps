<?php

/** @see MyLib_Zend_Controller_Action_Mobile.php */
require_once 'MyLib/Zend/Controller/Action/Mobile.php';

/**
 * Mobile Disney Controller(modules/mobile/controllers/DisneyController.php)
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/10/12   Liz
 */
class Mobile_DisneyController extends MyLib_Zend_Controller_Action_Mobile
{
    
    protected $_disneyUser;

	/**
     * deipatch
     *
     */
    function preDispatch()
    {        
        $uid = $this->_user->getId();
        require_once 'Mdal/Disney/User.php';
        $mdalUser = Mdal_Disney_User::getDefaultInstance();

        require_once 'Mbll/Disney/Index.php';
        $mbllIndex = new Mbll_Disney_Index();
        
        //get user disney info
        $disneyUser = $mdalUser->getUser($uid);
        Bll_User::appendPerson($disneyUser, 'uid', true);
        $this->_disneyUser = $disneyUser;
        $actionName = $this->_request->getActionName();
        
        //check today's first login
        if ($disneyUser['last_login_date'] != date('Y-m-d')) {
            //delete trade apply info
            $mbllIndex->deleteTradeApply($uid);
            
            //check lost send cup
            require_once 'Mbll/Disney/Cup.php';
            $mbllCup = new Mbll_Disney_Cup();
            $mbllCup->updateUserAreaCup($uid);
            
            //update last login date
            $mdalUser->updateUser($uid, array('last_login_date'=>date('Y-m-d'), 'today_trade_times'=>0));
            
            //check award
            require_once 'Mdal/Disney/Cup.php';
            $mdalCup = Mdal_Disney_Cup::getDefaultInstance();
            $cup = $mdalCup->getUserCupRand($uid);
            
            if (!empty($cup) && $actionName != 'getaward') {
                $this->_redirect($this->_baseUrl . '/mobile/disney/getaward');
                return;
            }
        }

        if ( $actionName != 'firsthome' && $actionName != 'help' && !$disneyUser['current_place'] ) {
            $this->_redirect($this->_baseUrl . '/mobile/disney/firsthome');
            return;
        }
        else if ( $actionName != 'firsttarget' && $actionName != 'help' && $actionName != 'firsthome' && !$disneyUser['target_place'] ) {
            $this->_redirect($this->_baseUrl . '/mobile/disney/firsttarget');
            return;
        }
        
        //Mickey's　Birthday
        if ( date("md") == '1028' ) {
            $result = $mbllIndex->addUserCup($uid, 5, $this->_APP_ID);
            
            if ( $result == 1 && $actionName != 'getaward') {
                $this->_redirect($this->_baseUrl . '/mobile/disney/getaward');
            }
        }
        
        //user's birthday        
        if ( date("m-d") == substr($disneyUser['dateOfBirth'], 5, 5) ) {
            $result = $mbllIndex->addUserCup($uid, 10, $this->_APP_ID);
                        
            if ( $result == 1 && $actionName != 'getaward') {
                $this->_redirect($this->_baseUrl . '/mobile/disney/getaward');
            }
        }        
        
        //3->au,2->softbank,1->docomo and other
        if ( $this->_ua == 3 ) {
            $imgPath = 'img_au';
            $imgType = 'png';
        }
        else if ( $this->_ua == 2 ) {
            $imgPath = 'img_softbank';
            $imgType = 'pnz';
        }
        else {
            $imgPath = 'img_docomo';
            $imgType = 'gif';
        }
        $this->view->imgPath = $imgPath;
        $this->view->imgType = $imgType;
        $this->view->ua = $this->_ua;
        $this->view->rand = time();
    }

    /**
     * index action 
     *
     */
    public function indexAction()
    {  
        //check distance
        require_once 'Mbll/Disney/Index.php';
        $mbllIndex = new Mbll_Disney_Index();
            
        if ( $this->_disneyUser['remain_distance'] > 0 && $this->_disneyUser['remain_distance'] <= $this->_disneyUser['flash_distance']) {
            //user arrive target
            $arriveResult = $mbllIndex->arriveTarget($this->_user->getId(), $this->_disneyUser, $this->_APP_ID, $this->_disneyUser['last_lat'], $this->_disneyUser['last_lon']);
        
            if ( $arriveResult == 1 ) {
                $this->_redirect($this->_baseUrl . '/mobile/disney/award?CF_pid=' . $this->_disneyUser['target_place']);
                return;
            }
        }
            
        $nid = $this->getParam('CF_nid');
        
        require_once 'Mdal/Disney/Notice.php';
        $mdalNotice = Mdal_Disney_Notice::getDefaultInstance();
        
        require_once 'Mdal/Disney/User.php';
        $mdalUser = Mdal_Disney_User::getDefaultInstance();
        
        if ( $nid ) {
            //delete notice
            $mdalNotice->deleteNotice($nid);
        }
        
        $uid = $this->_user->getId();
        
        //get user friend id list
        require_once 'Bll/Friend.php';
        $fids = Bll_Friend::getFriends($this->_user->getId());
        
        //get user game point ranking number
        //$this->view->friendRank = Mbll_Disney_Cache::getUserRankNmInFriends($uid);
        //$this->view->allRank = Mbll_Disney_Cache::getUserRankNmInAll($uid);
                
        //get user notice info
        $noticeCount = $mdalNotice->getNoticeCount($uid);
        $this->view->noticeCount = $noticeCount;
        if ( $noticeCount == 1 ) {
            //get user notice list
            $noticeList = $mdalNotice->getNoticeList($uid, 1, 1);
            $this->view->noticeList = $noticeList;
        }
        
        //get user all award count
        $this->view->userAwardCount = $mdalUser->getUserAllAwardCount($uid);
        
        //get friend feed list
        $friendFeedList = array();
        if (!empty($fids)) {
            $friendFeedList = $mdalNotice->getMymixiFeed($fids, 1, 3);
        }
        
        if ( !empty($friendFeedList) ) {
            Bll_User::appendPeople($friendFeedList, 'uid');
        }
        $this->view->friendFeedList = $friendFeedList;
        
        require_once 'Mdal/Disney/Place.php';
        $mdalPlace = Mdal_Disney_Place::getDefaultInstance();
        //get place by pid
        $lastTargetPlace = $mdalPlace->getPlaceById($this->_disneyUser['last_target_place']);
        $this->view->lastTargetAid = $lastTargetPlace['aid'];
        
        $this->view->disneyUser = $this->_disneyUser;
        
        $reaminDistance = $this->_disneyUser['remain_distance'] - $this->_disneyUser['flash_distance'];
        //get distacn img
        $length = strlen($reaminDistance);
        for ( $i = 0; $i < $length; $i++ ) {
            $arrayDistance[$i] = substr($reaminDistance, $i, 1);
        }
        $this->view->arrayDistance = $arrayDistance;
        $this->view->time = time();
        
        $this->view->mymixi = $mdalUser->getMymixi($uid);
        
        $this->render();
    }

    /**
     * first login action 
     *
     */
    public function firsthomeAction()
    {        
        $action = $this->getParam('CF_step', 'firstlogin');
        $this->view->step = $action;
        
        $uid = $this->_user->getId();
        
        if ( $this->_disneyUser['current_place'] ) {
            $this->_redirect($this->_baseUrl . '/mobile/disney/index');
            return;
        }
        
        require_once 'Mdal/Disney/Place.php';
        $mdalPlace = Mdal_Disney_Place::getDefaultInstance();
        if ( $action == "firstlogin" ) {
            
        }
        else if ( $action == "auto" ) {
            require_once 'Bll/User.php';
            $personArray = array('uid' => $uid);
            Bll_User::appendPerson($personArray, 'uid', true);
            //get place info by name
            $placeInfo = $mdalPlace->getPlaceByMixiName($personArray['address']);
            if ( !$placeInfo ) {
                $this->_redirect($this->_baseUrl . '/mobile/disney/firsthome?CF_step=select');
                return;
            }
            
            $this->view->placeInfo = $placeInfo;
        }
        else if ( $action == "select" ) {
            //get place list array
            $placeList = $mdalPlace->getPlaceList();
            $this->view->placeList = $placeList;
        }
        else if ( $action == "confirm" ) {
            if ($this->_request->isPost()) {
                $pid = $this->getPost('CF_pid');
            }
            else {
                $pid = $this->getParam('CF_pid');
            }            
            
            //get place info by id
            $placeInfo = $mdalPlace->getPlaceById($pid);
            $this->view->placeInfo = $placeInfo;
            
            //get distance
            require_once 'Mbll/Disney/User.php';
            $mbllUser = new Mbll_Disney_User();
            $result = $mbllUser->setHome($uid, $pid, 1);
            
            if ( $result['status'] != 1 ) {
                $this->_redirect($this->_baseUrl . '/mobile/disney/firsthome?CF_step=select');
                return;
            }
        }
        else {
            $datum = $this->getParam('datum', 'wgs84');
        
            $lat = $this->getParam('lat');
            $lon = $this->getParam('lon');
            if ( !$lat || !$lon ) {
                $this->_redirect($this->_baseUrl . '/mobile/disney/firsthome/Cf_step/auto');
                return;
            }
    
            require_once 'MyLib/Geomobilejp/Converter.php';
            require_once 'MyLib/Geomobilejp/IArea.php';
    
            $converter = new Geomobilejp_Converter($lat, $lon, $datum);
            $area = Geomobilejp_IArea::seekArea($converter);
            $iAreaCode = $area->getIAreaCode();
            
            require_once 'Mdal/Disney/Place.php';
            $mdalPlace = Mdal_Disney_Place::getDefaultInstance();
            $placeInfo = $mdalPlace->getPlaceByIAreaCode($iAreaCode);
            $this->view->placeInfo = $placeInfo;
                    
            require_once 'Mbll/Disney/Index.php';
            $mbllIndex = new Mbll_Disney_Index();
            $mbllIndex->getCurrentNotice($uid, $placeInfo, $this->_APP_ID);
        }
                
        $this->render();
    }

    /**
     * first target action
     *
     */
    public function firsttargetAction()
    {
        //check if first target
        if ( $this->_disneyUser['target_place'] ) {
            $this->_redirect($this->_baseUrl . '/mobile/disney/index');
            return;
        }
        
        $action = $this->getParam('CF_step', 'start');
        $this->view->step = $action;
        
        $uid = $this->_user->getId();

        require_once 'Mdal/Disney/Place.php';
        $mdalPlace = Mdal_Disney_Place::getDefaultInstance();
            
        if ( $action == "start" ) {
            //get place list array
            $placeList = $mdalPlace->getPlaceList();
            $this->view->placeList = $placeList;
            
            $this->view->disneyUser = $this->_disneyUser;
        }
        else if ( $action == "confirm" ) {
            $pid = $this->getPost('CF_pid');
            
            //get place info by id
            $placeInfo = $mdalPlace->getPlaceById($pid);
            $this->view->placeInfo = $placeInfo;
        }
        else {
            $pid = $this->getParam('CF_pid');
            
            //get place info by id
            $placeInfo = $mdalPlace->getPlaceById($pid);
            if ( !$placeInfo || $pid == $this->_disneyUser['current_place'] ) {
                $this->_redirect($this->_baseUrl . '/mobile/disney/firsttarget');
                return;
            }
            $this->view->placeInfo = $placeInfo;
            
            //get distance
            require_once 'Mbll/Disney/Place.php';
            $mbllPlace = new Mbll_Disney_Place();
            $distance = $mbllPlace->getDistanceByPid($this->_disneyUser['current_place'], $placeInfo['pid']);
            //get distacn img
            $length = strlen($distance);
            for ( $i = 0; $i < $length; $i++ ) {
                $arrayDistance[$i] = substr($distance, $i, 1);
            }
            $this->view->arrayDistance = $arrayDistance;
            $this->view->distance = $distance;
            
            $userInfo = array('target_place'=>$placeInfo['pid'], 'remain_distance'=>$distance);
            
            require_once 'Mdal/Disney/User.php';
            $mdalUser = Mdal_Disney_User::getDefaultInstance();
            //update user info
            $mdalUser->updateUser($uid, $userInfo);
        }
        
        $this->render();
    }
    
    /**
     * set home action
     *
     */
    public function sethomeAction()
    {
    	require_once 'Bll/Restful.php';
        $restful = Bll_Restful::getInstance($this->_user->getId(), $this->_APP_ID);
        $sig = $restful->checkSignature();

        if (!$sig) {
            echo 'CANCEL';
            exit(0);
        }
        
        $action = $this->getParam('CF_step', 'start');
        $this->view->step = $action;
        
        $uid = $this->_user->getId();
        
        require_once 'Mdal/Disney/Place.php';
        $mdalPlace = Mdal_Disney_Place::getDefaultInstance();
        
        if ( $action == "start" ) {
            //check user can set home
            $canSetHome = (time() - $this->_disneyUser['last_home_time']) >= 30*24*60*60 ? 1 : 0;
            $this->view->canSetHome = $canSetHome;
        }
        else if ( $action == "auto" ) {
            $lat = $this->getParam('lat');
            $lon = $this->getParam('lon');
            //$lat = $this->getParam('lat', '35.40.40.750');
            //$lon = $this->getParam('lon', '139.42.22.500');
            $datum = $this->getParam('datum', 'wgs84');
            if ( !$lat || !$lon ) {
                $this->_redirect($this->_baseUrl . '/mobile/disney/index');
                return;
            }
            
            require_once 'MyLib/Geomobilejp/Converter.php';
            require_once 'MyLib/Geomobilejp/IArea.php';
            $converter = new Geomobilejp_Converter($lat, $lon, $datum);
            $area = Geomobilejp_IArea::seekArea($converter);
            $iAreaCode = $area->getIAreaCode();
            
            require_once 'Mdal/Disney/Place.php';
            $mdalPlace = Mdal_Disney_Place::getDefaultInstance();
            //get place info by iAreaCode
            $placeInfo = $mdalPlace->getPlaceByIAreaCode($iAreaCode);
        
            if ( !$placeInfo ) {
                $this->_redirect($this->_baseUrl . '/mobile/disney/sethome?CF_step=select');
                return;
            }
            
            $this->view->placeInfo = $placeInfo;
        }
        else {
            $pid = $this->getParam('CF_pid');
            
            //get distance
            require_once 'Mbll/Disney/User.php';
            $mbllUser = new Mbll_Disney_User();
            $result = $mbllUser->setHome($uid, $pid, 0);
            
            if ( $result['status'] != 1 ) {
                $this->_redirect($this->_baseUrl . '/mobile/disney/sethome?CF_step=start');
                return;
            }
            
            $this->view->placeInfo = $result['placeInfo'];
        }
                
        $this->render();
    }

    /**
     * set target action
     *
     */
    public function settargetAction()
    {
        $action = $this->getParam('CF_step', 'start');
        $this->view->step = $action;
        
        $uid = $this->_user->getId();

        require_once 'Mdal/Disney/Place.php';
        $mdalPlace = Mdal_Disney_Place::getDefaultInstance();
            
        if ( $action == "start" ) {
            //get place list array
            $placeList = $mdalPlace->getPlaceList();
            $this->view->placeList = $placeList;
            $this->view->disneyUser = $this->_disneyUser;
        }
        else if ( $action == "confirm" ) {
            $pid = $this->getPost('CF_pid');
            
            //get place info by id
            $placeInfo = $mdalPlace->getPlaceById($pid);
            $this->view->placeInfo = $placeInfo;
        }
        else {
            $pid = $this->getParam('CF_pid');
            
            //get place info by id
            $placeInfo = $mdalPlace->getPlaceById($pid);
            if ( !$placeInfo || $pid == $this->_disneyUser['current_place'] ) {
                $this->_redirect($this->_baseUrl . '/mobile/disney/settarget');
                return;
            }
            $this->view->placeInfo = $placeInfo;
            
            //get distance
            require_once 'Mbll/Disney/Place.php';
            $mbllPlace = new Mbll_Disney_Place();
            $distance = $mbllPlace->getDistanceByPid($this->_disneyUser['current_place'], $placeInfo['pid']);
            //get distacn img
            $length = strlen($distance);
            for ( $i = 0; $i < $length; $i++ ) {
                $arrayDistance[$i] = substr($distance, $i, 1);
            }
            $this->view->arrayDistance = $arrayDistance;
            $this->view->distance = $distance;
            
            $userInfo = array('target_place' => $placeInfo['pid'], 
                              'remain_distance' => $distance, 
                              'flash_distance' => 0,
                              'game_start' => 0);
            
            require_once 'Mdal/Disney/User.php';
            $mdalUser = Mdal_Disney_User::getDefaultInstance();
            //update user info
            $mdalUser->updateUser($uid, $userInfo);
        }
        
        $this->render();
    }

    /**
     * get current action
     *
     */
    public function getcurrentAction()
    {
        $datum = $this->getParam('datum', 'wgs84');
        
        $lat = $this->getParam('lat');
        $lon = $this->getParam('lon');
        if ( !$lat || !$lon ) {
            $this->_redirect($this->_baseUrl . '/mobile/disney/index');
            return;
        }
        
        require_once 'Bll/Restful.php';
        $restful = Bll_Restful::getInstance($this->_user->getId(), $this->_APP_ID);
        $sig = $restful->checkSignature();

        if (!$sig) {
            echo 'CANCEL';
            exit(0);
        }

        try {
	        require_once 'MyLib/Geomobilejp/Converter.php';
	        require_once 'MyLib/Geomobilejp/IArea.php';
	
	        $converter = new Geomobilejp_Converter($lat, $lon, $datum);
	        $area = Geomobilejp_IArea::seekArea($converter);
	        $iAreaCode = $area->getIAreaCode();
        }
        catch (Exception $e) {
        	$this->_redirect($this->_baseUrl . '/mobile/disney/index');
            return;
        }

        require_once 'Mdal/Disney/Place.php';
        $mdalPlace = Mdal_Disney_Place::getDefaultInstance();
        $placeInfo = $mdalPlace->getPlaceByIAreaCode($iAreaCode);
        $this->view->placeInfo = $placeInfo;
        $uid = $this->_user->getId();
                
        require_once 'Mbll/Disney/Index.php';
        $mbllIndex = new Mbll_Disney_Index();
        $mbllIndex->getCurrentNotice($uid, $placeInfo, $this->_APP_ID); 
        
        //add to log get current
        require_once 'Mdal/Disney/Log.php';
        $mdalLog = Mdal_Disney_Log::getDefaultInstance();
        $mdalLog->insertGet(array('uid'=>$this->_USER_ID, 'pid'=>$placeInfo['pid'], 'coordinate'=>$lat.','.$lon, 'create_time'=>time()));       
        
        $body = "Disneyご当地ｺﾚｸｼｮﾝで『" . $placeInfo['award_name'] . "ｽﾃｨｯﾁ』をGETしたよ♪\n"
               ."47都道府県、ｵﾘｼﾞﾅﾙのご当地ｷｬﾗｸﾀｰがGET出来るんだよ★\n"
               ."みんなも今すぐGETしてね。\n"
               ."http://ma.mixi.net/11122/";
		$title = $placeInfo['award_name'] . "ｽﾃｨｯﾁをGETしたよ!!";  
        $ua = Zend_Registry::get('ua');
		if ( $ua == 3 ){
			$diary_title = urlencode(mb_convert_encoding($title, 'SJIS','UTF-8'));
			$diary_body  = urlencode(mb_convert_encoding($body, 'SJIS','UTF-8'));
			$this->view->diaryUrl = "http://m.mixi.jp/add_diary.pl?diary_title=" . $diary_title . "&diary_body=" . $diary_body . "&guid=ON";
    	}else {
			$this->view->diary_title = $title;
			$this->view->diary_body = $body;
		}
		
        $this->render();
    }

    /**
     * map action
     *
     */
    public function mapAction()
    {
        $aid = $this->getParam('CF_aid');
        $fid = $this->getParam('CF_fid');
        $tradePid = $this->getParam('CF_tradePid');
        $from = $this->getParam('CF_from', 'map');
        
        $from = $from == 'trade' ? 'tradeaward' : $from;
        $this->view->tradePid = $tradePid;
        $this->view->from = $from;
        $this->view->fid = $fid;
        $this->view->uid = $this->_user->getId();
        
        require_once 'Mdal/Disney/Place.php';
        $mdalPlace = Mdal_Disney_Place::getDefaultInstance();
        if ( !$aid ) {
            //get place by pid
            $lastTargetPlace = $mdalPlace->getPlaceById($this->_disneyUser['last_target_place']);
            $aid = $lastTargetPlace['aid'];
        }
        $this->view->aid = $aid;
        
        //get place list by area id
        $placeList = $mdalPlace->getUserPlaceListByAid($this->_user->getId(), $aid);
        
        $placeAry = array();
        $j = 0;
        for ( $i = 0, $iCount = count($placeList); $i < $iCount; $i++ ){
            if ( $i%2 == 0 ) {
                $j++;
            }
            $placeAry[$j][] = $placeList[$i];
        }
        $this->view->placeAry = $placeAry;
                
        //get prev area id and next area id
        $prevId = $mdalPlace->getNeighberArea($aid, 'prev');
        if (empty($prevId)) {
            $prevId = $mdalPlace->getNeighberArea($aid, 'last');
        }
        $nextId = $mdalPlace->getNeighberArea($aid, 'next');
        if (empty($nextId)) {
            $nextId = $mdalPlace->getNeighberArea($aid, 'first');
        }
        
        //get area info by area id
        $this->view->prevArea = $mdalPlace->getAreaByAid($prevId);
        $this->view->nextArea = $mdalPlace->getAreaByAid($nextId);
        $this->view->areaInfo = $mdalPlace->getAreaByAid($aid);
        
        $this->render();
    }

    /**
     * award action
     *
     */
    public function awardAction()
    {
        $pid = $this->getParam('CF_pid');
        $from = $this->getParam('CF_from');
        $fid = $this->getParam('CF_fid');
        $nid = $this->getParam('CF_nid');
        
        if ( $nid ) {
            require_once 'Mdal/Disney/Notice.php';
            $mdalNotice = Mdal_Disney_Notice::getDefaultInstance();
            $notice = $mdalNotice->getNoticeById($nid);
            $fid = $notice['actor_uid'];
            //delete notice
            $mdalNotice->deleteNotice($nid);
        }
        
        require_once 'Mdal/Disney/Place.php';
        $mdalPlace = Mdal_Disney_Place::getDefaultInstance();
        //get place info by pid
        $placeInfo = $mdalPlace->getPlaceById($pid);
        $this->view->placeInfo = $placeInfo;
        $this->view->from = $from;
        $this->view->fid = $fid;

        if ( $fid ) {
            require_once 'Bll/User.php';
            //get friend info
            $friendInfo = Bll_User::getPerson($fid);
            $this->view->friendInfo = $friendInfo;
            
            //check is friend
            require_once 'Bll/Friend.php';
            $this->view->isFriend = Bll_Friend::isFriend($this->_user->getId(), $fid);
        }
        
        if ( $from == 'tradeaward' ) {
            $tradePid = $this->getParam('CF_tradePid');
            //get place info by pid
            $tradePlaceInfo = $mdalPlace->getPlaceById($tradePid);
            $this->view->tradePlaceInfo = $tradePlaceInfo;
        }
        
        $body = "Disneyご当地ｺﾚｸｼｮﾝで『" . $placeInfo['award_name'] . "ｽﾃｨｯﾁ』をGETしたよ♪\n"
               ."47都道府県、ｵﾘｼﾞﾅﾙのご当地ｷｬﾗｸﾀｰがGET出来るんだよ★\n"
               ."みんなも今すぐGETしてね。\n"
               ."http://ma.mixi.net/11122/";
		$title = $placeInfo['award_name'] . "ｽﾃｨｯﾁをGETしたよ!!";  
        $ua = Zend_Registry::get('ua');
		if ( $ua == 3 ){
			$diary_title = urlencode(mb_convert_encoding($title, 'SJIS','UTF-8'));
			$diary_body  = urlencode(mb_convert_encoding($body, 'SJIS','UTF-8'));
			$this->view->diaryUrl = "http://m.mixi.jp/add_diary.pl?diary_title=" . $diary_title . "&diary_body=" . $diary_body . "&guid=ON";
    	}else {
			$this->view->diary_title = $title;
			$this->view->diary_body = $body;
		}
        
        $this->render();
    }

    /**
     * trade award action
     *
     */
    public function tradeawardAction()
    {
        $action = $this->getParam('CF_step', 'apply');
        $this->view->action = $action;
        
        if ( $action == 'apply' ) {
            $pid = $this->getParam('CF_pid');
            $tradePid = $this->getParam('CF_tradePid');
            $fid = $this->getParam('CF_fid');
            
            //get friend info
            require_once 'Bll/User.php';
            $this->view->friendInfo = Bll_User::getPerson($fid);
            
            require_once 'Mbll/Disney/Index.php';
            $mbllIndex = new Mbll_Disney_Index();
            //trade award
            $result = $mbllIndex->applyTradeAward($pid, $tradePid, $this->_user->getId(), $fid, $this->_APP_ID);
            $this->view->result = $result;
        }
        else if ( $action == 'accept' ) {
            $nid = $this->getParam('CF_nid');
            $this->view->nid = $nid;
            
            require_once 'Mdal/Disney/User.php';
            $mdalUser = Mdal_Disney_User::getDefaultInstance();
            //check apply info by nid
            $tradeApplyInfo = $mdalUser->getTradeApplyByNid($nid);
            if ( !$tradeApplyInfo ) {
                $this->_redirect($this->_baseUrl . '/mobile/disney/news');
                return;
            }
            
            require_once 'Mdal/Disney/Place.php';
            $mdalPlace = Mdal_Disney_Place::getDefaultInstance();
            //get place info by pid
            $userPlaceInfo = $mdalPlace->getPlaceById($tradeApplyInfo['friend_pid']);
            $friendPlaceInfo = $mdalPlace->getPlaceById($tradeApplyInfo['user_pid']);
            $this->view->userPlaceInfo = $userPlaceInfo;
            $this->view->friendPlaceInfo = $friendPlaceInfo;
            
            require_once 'Bll/User.php';
            $this->view->friendInfo = Bll_User::getPerson($tradeApplyInfo['uid']);
        }
        else if ( $action == 'acceptconfirm' ) {
            $nid = $this->getParam('CF_nid');
            $this->view->nid = $nid;
            
            require_once 'Mdal/Disney/User.php';
            $mdalUser = Mdal_Disney_User::getDefaultInstance();
            //check apply info by nid
            $tradeApplyInfo = $mdalUser->getTradeApplyByNid($nid);
            if ( !$tradeApplyInfo ) {
                $this->_redirect($this->_baseUrl . '/mobile/disney/news');
                return;
            }
            
            require_once 'Mdal/Disney/Place.php';
            $mdalPlace = Mdal_Disney_Place::getDefaultInstance();
            //get place info by pid
            $this->view->userPlaceInfo = $mdalPlace->getPlaceById($tradeApplyInfo['friend_pid']);
            $this->view->friendPlaceInfo = $mdalPlace->getPlaceById($tradeApplyInfo['user_pid']);
            
            require_once 'Bll/User.php';
            $this->view->friendInfo = Bll_User::getPerson($tradeApplyInfo['uid']);
        }
        else if ( $action == 'acceptcomplete' ) {
            $nid = $this->getParam('CF_nid');
            
            require_once 'Mdal/Disney/User.php';
            $mdalUser = Mdal_Disney_User::getDefaultInstance();
            //check apply info by nid
            $this->view->tradeApplyInfo = $mdalUser->getTradeApplyByNid($nid);
            
            require_once 'Mbll/Disney/Index.php';
            $mbllIndex = new Mbll_Disney_Index();
            //trade award
            $this->view->result = $mbllIndex->acceptTradeAward($nid, $this->_APP_ID);
        }
        
        $this->render();
    }
    
    /**
     * send award action
     *
     */
    public function sendawardAction()
    {
        $action = $this->getParam('CF_step', 'start');
        $this->view->step = $action;
        
        $pid = $this->getParam('CF_pid');
        $fid = $this->getParam('CF_fid');
        
        require_once 'Bll/User.php';
        $friendInfo = Bll_User::getPerson($fid);
        $this->view->friendInfo = $friendInfo;
            
        require_once 'Mdal/Disney/Place.php';
        $mdalPlace = Mdal_Disney_Place::getDefaultInstance();
        //get place info by pid
        $placeInfo = $mdalPlace->getPlaceById($pid);
        $this->view->placeInfo = $placeInfo;

        if ( $action == 'start' ) {
            require_once 'Mbll/Disney/Index.php';
            $mbllIndex = new Mbll_Disney_Index();
            //trade award
            $sendResult = $mbllIndex->sendAward($this->_USER_ID, $fid, $pid);
            if ( $sendResult != 1 ) {
                $this->_redirect($this->_baseUrl . '/mobile/disney/sendaward?CF_fid='.$fid.'&CF_pid='.$pid.'&CF_step=complete&CF_result=-1');
                return;
            }
            
            require_once 'Zend/Json.php';
            $this->view->payParam = Zend_Json::encode(array('pid'=>$pid, 'fid'=>$fid));
            $this->view->fid = $fid;
        }
        else {
            //
            $point_code = $this->getParam('point_code');
            $pay_status = $this->getParam('status', 20);
                        
            //check point_code and status
            if (empty($point_code) || $pay_status == 20) {
                $this->view->result = 0;
            }
            else {
                //check point_code
                require_once 'Mdal/Disney/Pay.php';
                $dalPay = Mdal_Disney_Pay::getDefaultInstance();
                $result = $dalPay->getPaymentByCode($point_code, 1);
                
                $this->view->result = empty($result) ? 0 : 1;
            }
        }
        
        $this->render();
    }
    
    /**
     * down load award action
     *
     */
    public function downloadawardAction()
    {
        $action = $this->getParam('CF_step', 'start');
        $pid = $this->getParam('CF_pid');
        
        //get user award count
        require_once 'Mdal/Disney/User.php';
        $mdalUser = Mdal_Disney_User::getDefaultInstance();
        $userAwardCount = $mdalUser->getUserAwardCount($this->_user->getId(), $pid);
        if ( $userAwardCount < 1 ) {
        	$this->_redirect($this->_baseUrl . '/mobile/disney/index');
            return ;
        }
        
        require_once 'Mdal/Disney/Place.php';
        $mdalPlace = Mdal_Disney_Place::getDefaultInstance();
        //get place info by pid
        $placeInfo = $mdalPlace->getPlaceById($pid);
        $this->view->placeInfo = $placeInfo;
            
        if ( $action == 'start' ) {
            //get user download award info by pid
            $downloadAward = $mdalPlace->getDownloadAwardInfo($this->_user->getId(), $pid);
            if ( $downloadAward ) {
                $this->_redirect($this->_baseUrl . '/mobile/disney/downloadaward?CF_pid=' . $pid . '&CF_step=complete&CF_isdownload=0');
                return;
            }
            
            require_once 'Zend/Json.php';
            $this->view->payParam = Zend_Json::encode(array('pid'=>$pid));
        }
        else {
        	$downloadAward = $mdalPlace->getDownloadAwardInfo($this->_user->getId(), $pid);
        	
        	if ( empty($downloadAward) ) {
        		$action = "start";
        	}
        	
            $isDownload = $this->getParam('CF_isdownload', 1);
            $this->view->isDownload = $isDownload;
            $this->view->uid = $this->_USER_ID;
        }
        
        $this->view->step = $action;
        $this->render();
    }
    
    public function desktopawardAction()
    {    	
    	$action = $this->getParam('CF_step', 'start');
        $pid = $this->getParam('CF_pid');
        
        //get user award count
        require_once 'Mdal/Disney/User.php';
        $mdalUser = Mdal_Disney_User::getDefaultInstance();
        $userAwardCount = $mdalUser->getUserAwardCount($this->_user->getId(), $pid);
        if ( $userAwardCount < 1 ) {
        	$this->_redirect($this->_baseUrl . '/mobile/disney/index');
            return ;
        }
        
        //get place info by pid
        require_once 'Mdal/Disney/Place.php';
        $mdalPlace = Mdal_Disney_Place::getDefaultInstance();
        $placeInfo = $mdalPlace->getPlaceById($pid);
        $this->view->placeInfo = $placeInfo;
            
        if ( $action == 'start' ) {
            //get user download award info by pid
            $desktopAward = $mdalPlace->getDesktopAwardInfo($this->_user->getId(), $pid);
            if ( $desktopAward ) {
                $this->_redirect($this->_baseUrl . '/mobile/disney/desktopaward?CF_pid=' . $pid . '&CF_step=complete&CF_isdownload=0');
                return;
            }
            
            require_once 'Zend/Json.php';
            $this->view->payParam = Zend_Json::encode(array('pid'=>$pid));
        }
        else {
        	$desktopAward = $mdalPlace->getDesktopAwardInfo($this->_user->getId(), $pid);
        	
        	if ( empty($desktopAward) ) {
        		$action = "start";
        	}
        	
            $isDownload = $this->getParam('CF_isdownload', 1);
            $this->view->isDownload = $isDownload;
            $this->view->uid = $this->_USER_ID;
        }
        
        $this->view->step = $action;
        $this->render();
    }
        
    /**
     * down load award img action
     *
     */
    public function awardimgAction()
    {
        $pid = $this->getParam('CF_pid');
        
        require_once 'Mdal/Disney/Place.php';
        $mdalPlace = Mdal_Disney_Place::getDefaultInstance();
        //get user download award info by pid
        $downloadAward = $mdalPlace->getDownloadAwardInfo($this->_USER_ID, $pid);
        
        //get place info by pid
        $placeInfo = $mdalPlace->getPlaceById($pid);
        
        if ( !$downloadAward || !$placeInfo ) {
            $this->_redirect($this->_baseUrl . '/mobile/disney/index');
            return;
        }        
        
        $this->view->pid = $pid;
        $this->view->uid = $this->_USER_ID;
        $this->render();
    }
    
    /**
     * news action
     *
     */
    public function newsAction()
    {
        $action = $this->getParam('CF_step', 'news');
        $this->view->action = $action;
        
        require_once 'Mdal/Disney/Notice.php';
        $mdalNotice = Mdal_Disney_Notice::getDefaultInstance();
        
        if ( $action == 'acceptcancel' ) {
            $nid = $this->getParam('CF_nid');
            
            require_once 'Mbll/Disney/Index.php';
            $mbllIndex = new Mbll_Disney_Index();
            //cancel accept
            $mbllIndex->acceptcancel($nid, $this->_APP_ID);
        }

        $pageIndex = $this->getParam('CF_page', '1');
        $pageSize = 10;
        
        //get user notice list , count
        $noticeList = $mdalNotice->getNoticeList($this->_user->getId(), $pageIndex, $pageSize);
        $noticeCount = $mdalNotice->getNoticeCount($this->_user->getId());
        
        //get pager info
        $this->view->pager = array('count' => $noticeCount,
                                   'pageIndex' => $pageIndex,
                                   'requestUrl' => 'mobile/disney/news',
                                   'pageSize' => $pageSize,
                                   'maxPager' => ceil($noticeCount / $pageSize)
                                   );
        $this->view->noticeList = $noticeList;
        $this->view->noticeCount = count($noticeList);
        
        $this->render();
    }

    /**
     * my mixi list action
     *
     */
    public function mymixilistAction()
    {
        $pageIndex = $this->getParam('CF_page', '1');
        $pageSize = 10;
        
        require_once 'Bll/Friend.php';
        //get user friend list
        $fids = Bll_Friend::getFriends($this->_user->getId());
        if ( $fids ) {
            require_once 'Mdal/Disney/Notice.php';
            $mdalNotice = Mdal_Disney_Notice::getDefaultInstance();
            
            require_once 'Mdal/Disney/User.php';
            $mdalUser = Mdal_Disney_User::getDefaultInstance();
            //update disney_mymixi
            $mdalUser->updateMymixiHasRead($this->_user->getId());
        
            //get user app friend list and count
            $friendList = $mdalNotice->getMymixiFeed($fids, $pageIndex, $pageSize);
            $appFidCount = $mdalUser->getAppFidCount($fids);
            
            if ( $friendList ) {
                Bll_User::appendPeople($friendList, 'uid');
            }
        }
        $this->view->friendList = $friendList;
        
        //get pager info
        $this->view->pager = array('count' => $appFidCount,
                                   'pageIndex' => $pageIndex,
                                   'requestUrl' => 'mobile/disney/mymixilist',
                                   'pageSize' => $pageSize,
                                   'maxPager' => ceil($appFidCount / $pageSize)
                                   );
        
        $this->render();
    }
    
    /**
     * my mixi list action
     *
     */
    public function choicemymixiAction()
    {
        $pid = $this->getParam('CF_pid');
        $pageIndex = $this->getParam('CF_page', '1');
        $pageSize = 10;
        
        require_once 'Bll/Friend.php';
        //get user friend list
        $fids = Bll_Friend::getFriends($this->_user->getId());
        if ( $fids ) {
            require_once 'Mdal/Disney/User.php';
            $mdalUser = Mdal_Disney_User::getDefaultInstance();
            //get user app friend list and count
            $appFids = $mdalUser->getAppFids($fids, $pageIndex, $pageSize);
            $appFidCount = $mdalUser->getAppFidCount($fids);
            
            if (!empty($appFids)) {
	            //get user award count by pid
	            $userAward = $mdalUser->getAwardCountByPid($appFids, $pid);
	            
	            foreach ($userAward as $item1) {
	                $i = 0;
	                foreach ($appFids as $item2) {
	                    $temp = array_search($item1['uid'], $item2);                    
	                    if ($temp) break;
	                    $i++;
	                }
	                
	                if ($i != count($appFids)) {
	                    $appFids[$i]['c'] = $item1['c'];
	                }
	            }
            }  
            
            if ( $appFids ) {
                Bll_User::appendPeople($appFids, 'uid');
            }
        }
        $this->view->appFids = $appFids;
        $this->view->pid = $pid;
        
        //get pager info
        $this->view->pager = array('count' => $appFidCount,
                                   'pageIndex' => $pageIndex,
                                   'requestUrl' => 'mobile/disney/choicemymixi',
                                   'pageSize' => $pageSize,
                                   'maxPager' => ceil($appFidCount / $pageSize)
                                   );
        
        $this->render();
    }

    /**
     * check distance action
     *
     */
    public function checkdistanceAction()
    {
        if ( $this->_disneyUser['remain_distance'] < 1 ) {
            $this->_redirect($this->_baseUrl . '/mobile/disney/index');
            return;
        }
        
        $datum = $this->getParam('datum', 'wgs84');
        $lat = $this->getParam('lat');
        $lon = $this->getParam('lon');
        
        if ( !$lat || !$lon ) {
            $this->_redirect($this->_baseUrl . '/mobile/disney/index');
            return;
        }
        
        try {
	        require_once 'MyLib/Geomobilejp/Converter.php';
	        require_once 'MyLib/Geomobilejp/IArea.php';
	
	        $converter = new Geomobilejp_Converter($lat, $lon, $datum);
	        $area = Geomobilejp_IArea::seekArea($converter);
	        $iAreaCode = $area->getIAreaCode();
        }
        catch (Exception $e) {
        	$this->_redirect($this->_baseUrl . '/mobile/disney/index');
            return;
        }
        
        if ( $this->_disneyUser['game_start'] == 1 ) {
            require_once 'Mbll/Disney/Place.php';
            $mbllPlace = new Mbll_Disney_Place();
            //get move distance
            $moveDistance = $mbllPlace->getDistance($lat, $lon, $this->_disneyUser['last_lat'], $this->_disneyUser['last_lon']);
                        
            //add for log
            $mbllPlace->distanceLog($this->_USER_ID, $lat, $lon, $this->_disneyUser['last_lat'], $this->_disneyUser['last_lon']);
            
            //add to log check
	        require_once 'Mdal/Disney/Log.php';
	        $mdalLog = Mdal_Disney_Log::getDefaultInstance();
	        $mdalLog->insertCheck(array('uid'=>$this->_USER_ID, 'coordinate'=>$lat.','.$lon, 'distance'=>$moveDistance, 'create_time'=>time()));
            
	        require_once 'Mdal/Disney/Shoes.php';
            $mdalShoes = Mdal_Disney_Shoes::getDefaultInstance();
            $hasShoes = $mdalShoes->hasShoes($this->_USER_ID);
            
            if ($hasShoes) {
                $userShoes = $mdalShoes->getUserShoes($this->_USER_ID);
                $magni = $mdalShoes->getShoesMagni($userShoes['shoes']);
    	        $moveDistance = $moveDistance * $magni;
    	        $this->view->magni = $magni;
            }
        
            $length = strlen($moveDistance);
            for ( $i = 0; $i < $length; $i++ ) {
                $arrayMove[$i] = substr($moveDistance, $i, 1);
            }
        }
        else {
            $moveDistance = 0;
            $arrayMove = array('0'=>0);
        }
        
        $this->view->arrayMove = $arrayMove;
        $this->view->moveDistance = $moveDistance;
        
        $remainDistance = $this->_disneyUser['remain_distance'] - $this->_disneyUser['flash_distance'] - $moveDistance;
        $remainDistance = round($remainDistance, 0);
        
        require_once 'Mbll/Disney/Index.php';
        $mbllIndex = new Mbll_Disney_Index();
            
        if ( $remainDistance > 0 ) {
            $length = strlen($remainDistance);
            for ( $j = 0; $j < $length; $j++ ) {
                $arrayRemain[$j] = substr($remainDistance, $j, 1);
            }
            $this->view->arrayRemain = $arrayRemain;
            $this->view->remainDistance = $remainDistance;
            
            $userInfo = array('remain_distance' => $this->_disneyUser['remain_distance'] - $moveDistance,
                              'last_lat' => $lat,
                              'last_lon' => $lon,
                              'game_start' => 1);       
            
            $result = $mbllIndex->checkDistince($this->_user->getId(), $userInfo, $hasShoes, $userShoes);
            
            if (!$result) {
                $this->_redirect($this->_baseUrl . '/mobile/disney/index');
            }            
            
            $isArrive = 0;
        }
        else {
            require_once 'Mbll/Disney/Index.php';
            $mbllIndex = new Mbll_Disney_Index();
            //user arrive target
            $result = $mbllIndex->arriveTarget($this->_user->getId(), $this->_disneyUser, $this->_APP_ID, $lat, $lon, $hasShoes, $userShoes);
            
            if (!$result) {
                $this->_redirect($this->_baseUrl . '/mobile/disney/index');
            }            
            
            require_once 'Mdal/Disney/Place.php';
            $mdalPlace = Mdal_Disney_Place::getDefaultInstance();
            $placeInfo = $mdalPlace->getPlaceById($this->_disneyUser['target_place']);
            
            $body = "Disneyご当地ｺﾚｸｼｮﾝで『" . $placeInfo['award_name'] . "ｽﾃｨｯﾁ』をGETしたよ♪\n"
	               ."47都道府県、ｵﾘｼﾞﾅﾙのご当地ｷｬﾗｸﾀｰがGET出来るんだよ★\n"
	               ."みんなも今すぐGETしてね。\n"
	               ."http://ma.mixi.net/11122/";
			$title = $placeInfo['award_name'] . "ｽﾃｨｯﾁをGETしたよ!!";  
	        $ua = Zend_Registry::get('ua');
			if ( $ua == 3 ){
				$diary_title = urlencode(mb_convert_encoding($title, 'SJIS','UTF-8'));
				$diary_body  = urlencode(mb_convert_encoding($body, 'SJIS','UTF-8'));
				$this->view->diaryUrl = "http://m.mixi.jp/add_diary.pl?diary_title=" . $diary_title . "&diary_body=" . $diary_body . "&guid=ON";
	    	}else {
				$this->view->diary_title = $title;
				$this->view->diary_body = $body;
			}
            
            $isArrive = 1;
        }
        
        $this->view->userShoes = $userShoes;
        $this->view->hasShoes = $hasShoes;
        $this->view->isArrive = $isArrive;
        $this->view->disneyUser = $this->_disneyUser;
        
        $this->render();
    }
    
    /**
     * cup list action
     *
     */
    public function cuplistAction()
    {
        $pageIndex = $this->getParam('CF_page', '1');
        $pageSize = 10;
        
        require_once 'Mdal/Disney/User.php';
        $mdalUser = Mdal_Disney_User::getDefaultInstance();
        //get user cup list and count
        $cupList = $mdalUser->getUserCupList($this->_user->getId(), $pageIndex, $pageSize);
        $cupCount = $mdalUser->getUserCupCount($this->_user->getId()) ;
        
        $this->view->cupList = $cupList;
        //get pager info
        $this->view->pager = array('count' => $cupCount,
                                   'pageIndex' => $pageIndex,
                                   'requestUrl' => 'mobile/disney/cuplist',
                                   'pageSize' => $pageSize,
                                   'maxPager' => ceil($cupCount / $pageSize)
                                   );
        
        $this->render();
    }
    
    /**
     * cup action
     *
     */
    public function cupAction()
    {
        $cid = $this->getParam('CF_cid');
        $fid = $this->getParam('CF_fid');
        
        require_once 'Mdal/Disney/User.php';
        $mdalUser = Mdal_Disney_User::getDefaultInstance();
        //get cup info
        $cup = $mdalUser->getCupByCid($cid);
        $this->view->cup = $cup;
        
        if ( $fid ) {
            require_once 'Bll/User.php';
            $friendInfo = Bll_User::getPerson($fid);
            $this->view->friendInfo = $friendInfo;
            $this->view->fid = $fid;
        }
        
        $this->render();
    }

    /**
     * ranking action
     *
     */
    public function rankingAction()
    {
        $type = $this->getParam('CF_type', '1');
                
        //friend ranking list
        if ( $type == 1 ) {
            //get game point ranking list
            require_once 'Mbll/Disney/Cache.php';
            $rankList = Mbll_Disney_Cache::getFriendRankingList($this->_user->getId());
        }//all app user ranking list
        else {
            //get game point ranking list
            require_once 'Mbll/Disney/Cache.php';
            $rankList = Mbll_Disney_Cache::getAllRankingList();
        }
        
        require_once 'Bll/User.php';
        Bll_User::appendPeople($rankList, 'uid');
        
        $this->view->rankingList = $rankList;
        $this->view->type = $type;
        $this->view->disneyUser = $this->_disneyUser;
        
        $this->render();
    }

    /**
     * profile action
     *
     */
    public function profileAction()
    {
        $fid = $this->getParam('CF_uid');
        if ( !$fid || $fid == $this->_user->getId() ) {
            $this->_redirect($this->_baseUrl . '/mobile/disney/index');
            return;
        }
        
        require_once 'Mdal/Disney/User.php';
        $mdalUser = Mdal_Disney_User::getDefaultInstance();
        require_once 'Mdal/Disney/Place.php';
        $mdalPlace = Mdal_Disney_Place::getDefaultInstance();
        
        //get friend disney info
        $disneyFriend = $mdalUser->getUser($fid);
        require_once 'Bll/User.php';
        Bll_User::appendPerson($disneyFriend, 'uid');
        
        $aid = $this->getParam('CF_aid');
        if ( !$aid ) {
            //get place by pid
            $lastTargetPlace = $mdalPlace->getPlaceById($disneyFriend['last_target_place']);
            $aid = $lastTargetPlace['aid'];
        }

        //get place list by area id
        $placeList = $mdalPlace->getUserPlaceListByAid($fid, $aid);
        
        $placeAry = array();
        $j = 0;
        for ( $i = 0, $iCount = count($placeList); $i < $iCount; $i++ ){
            if ( $i%2 == 0 ) {
                $j++;
            }
            $placeAry[$j][] = $placeList[$i];
        }
        $this->view->placeAry = $placeAry;
        
        //get prev area id and next area id
        $prevId = $mdalPlace->getNeighberArea($aid, 'prev');
        if (empty($prevId)) {
            $prevId = $mdalPlace->getNeighberArea($aid, 'last');
        }
        $nextId = $mdalPlace->getNeighberArea($aid, 'next');
        if (empty($nextId)) {
            $nextId = $mdalPlace->getNeighberArea($aid, 'first');
        }
        
        //get area info by area id
        $prevArea = $mdalPlace->getAreaByAid($prevId);
        $nextArea = $mdalPlace->getAreaByAid($nextId);
        $this->view->prevArea = $prevArea;
        $this->view->nextArea = $nextArea;
        $this->view->areaInfo = $mdalPlace->getAreaByAid($aid);
        $this->view->fid = $fid;
        $this->view->aid = $aid;
        $this->view->disneyFriend = $disneyFriend;
        
        $cupList = $mdalUser->getUserCupList($fid, 1, 100);
        $this->view->cupList = $cupList;
        
        $this->render();
    }

    /**
     * game action
     *
     */
    public function gameAction()
    {        
        $action = $this->getParam('CF_step', 'start');
        $nid = $this->getParam('CF_nid');
        
        if ( $nid ) {
            require_once 'Mdal/Disney/Notice.php';
            $mdalNotice = Mdal_Disney_Notice::getDefaultInstance();
            //delete notice
            $mdalNotice->deleteNotice($nid);
        }
        
        if ($action == 'start') { 
            require_once 'Mdal/Disney/Flash.php';
            $mdalFlash = Mdal_Disney_Flash::getDefaultInstance();        
            //get user flash point info
            $userFlashPoint = $mdalFlash->getUserFlashPoint($this->_USER_ID);
            
            if ($userFlashPoint['type'] > 0) {
                $action = 'end';
            }
        }
        
        if ($this->_disneyUser['remain_distance'] == 0) {
            $action = 'settarget';
        }
            
        $this->view->action = $action;
            
        if ($action == 'start') {
            $this->view->disneyUser = $this->_disneyUser;
            $this->view->time = time();
        }
        else if ($action == 'settarget') {
        	
        }
        else {
            //check mixi point
            if ( $this->_disneyUser['game_ticket'] < 1 ) {
                $this->_redirect($this->_baseUrl . '/mobile/disney/game');
                return;
            }

            require_once 'Mbll/Disney/Index.php';
            $mbllIndex = new Mbll_Disney_Index();
                
            if ( $this->_disneyUser['remain_distance'] - $this->_disneyUser['flash_distance'] <= 0 ) {
                //user arrive target
                $arriveResult = $mbllIndex->arriveTarget($this->_user->getId(), $this->_disneyUser, $this->_APP_ID, $this->_disneyUser['last_lat'], $this->_disneyUser['last_lon']);
            
                if ( $arriveResult == 1 ) {
                    $this->_redirect($this->_baseUrl . '/mobile/disney/award?CF_pid=' . $this->_disneyUser['target_place']);
                    return;
                }
                else {
                    $this->_redirect($this->_baseUrl . '/mobile/disney/index');
                    return;
                }
            }
 
            require_once 'Mbll/Disney/Flash.php';
            $mbllFlash = new Mbll_Disney_Flash();
            //set user flash point
            $result = $mbllFlash->setFlashPoint($this->_user->getId());
            $this->view->result = $result;
            
            if ( $result['status'] != 1 ) {
                $this->_redirect($this->_baseUrl . '/mobile/disney/game');
                return;
            }
            
            $this->view->move = $result['distance'];
            
            require_once 'Mdal/Disney/User.php';
            $mdalUser = Mdal_Disney_User::getDefaultInstance();
            $disneyUser = $mdalUser->getUser($this->_user->getId());
            
            //get remain distance
            $remainDistance = $disneyUser['remain_distance'] - $disneyUser['flash_distance'];
            
            if ( $remainDistance <= 0 ) {
                //user arrive target
                $arriveResult = $mbllIndex->arriveTarget($this->_user->getId(), $this->_disneyUser, $this->_APP_ID, $this->_disneyUser['last_lat'], $this->_disneyUser['last_lon']);
            
                if ( $arriveResult == 1 ) {
                    $this->_redirect($this->_baseUrl . '/mobile/disney/award?CF_pid=' . $this->_disneyUser['target_place']);
                    return;
                }
                else {
                    $this->_redirect($this->_baseUrl . '/mobile/disney/index');
                    return;
                }
            }
            
            $length = strlen($remainDistance);
            for ( $i = 0; $i < $length; $i++ ) {
                $arrayDistance[$i] = substr($remainDistance, $i, 1);
            }
            
            $length = strlen($result['distance']);
            for ( $i = 0; $i < $length; $i++ ) {
                $flashDistance[$i] = substr($result['distance'], $i, 1);
            }
            
            $this->view->flashDistance = $flashDistance;
            $this->view->arrayDistance = $arrayDistance;
            $this->view->disneyUser = $this->_disneyUser;
        }
        
        $this->render();
    }

    /**
     * disney cup action
     *
     */
    public function disneycupAction()
    {
        $action = $this->getParam('CF_step', 'start');
        
        if ($action != 'start') {
            if ( $this->_disneyUser['disney_member'] == 0 ) {
                $this->_redirect($this->_baseUrl . '/mobile/disney/index');
                return;
            }
            else if ( $this->_disneyUser['disney_member'] == 1 ) {
                require_once 'Mbll/Disney/Index.php';
                $mbllIndex = new Mbll_Disney_Index();
                $result = $mbllIndex->addUserCup($this->_USER_ID, 6, $this->_APP_ID);
                
                if ($result) {
                    $this->_redirect($this->_baseUrl . '/mobile/disney/getaward');
                    return;
                }
                else {
                    $this->_redirect($this->_baseUrl . '/mobile/disney/index');
                    return;
                }
            }
        }
        
        $this->view->action = $action;
        $this->render();
    }

    /**
     * get ticket action
     *
     */
    public function getticketAction()
    {        
        $action = $this->getParam('CF_step', 'start');
        $this->view->action = $action;
        
        if ( $action == 'start') {
            $point_code = $this->getParam('point_code');
            $pay_status = $this->getParam('status', 20);
                        
            //check point_code and status
            if (!empty($point_code) && $pay_status == 10) {
                //check point_code
                require_once 'Mdal/Disney/Pay.php';
                $dalPay = Mdal_Disney_Pay::getDefaultInstance();
                $result = $dalPay->getPaymentByCode($point_code, 1);
                
                if (!empty($result)) {
                    $this->_redirect($this->_baseUrl . '/mobile/disney/game');
                }
            }
            
            $ticketList = array('0' => '枚数を選択',
                                '1' => '1枚(30pt)',
                                '2' => '5枚(140pt)',
                                '3' => '10枚(270pt)');
            $this->view->ticketList = $ticketList;
        }
        else {
            $ticketType = $this->getPost('CF_ticket');
            
            //get ticket count and mixi point
            switch ( $ticketType ) {
                case 1 :
                    $ticketCount = 1;
                    $mixiPoint = 30;
                    break;
                case 2 :
                    $ticketCount = 5;
                    $mixiPoint = 140;
                    break;
                case 3 :
                    $ticketCount = 10;
                    $mixiPoint = 270;
                    break;
                default :
                    $this->_redirect($this->_baseUrl . '/mobile/disney/getticket');
                    return;
            }

            require_once 'Mbll/Disney/Index.php';
            $mbllIndex = new Mbll_Disney_Index();
            //buy game ticket
            $result = $mbllIndex->buyTicket($this->_user->getId(), $ticketCount, $mixiPoint);
            $this->view->result = $result;
        }
        
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
     * enjoy action
     *
     */
    public function enjoyAction()
    {
        $this->render();
    }

    /**
     * phone action
     *
     */
    public function phoneAction()
    {
        $this->render();
    }
    
    /**
     * playrule action
     *
     */
    public function playruleAction()
    {
        $this->render();
    }
    
    /**
     * playrule action
     *
     */
    public function ruleAction()
    {
        $this->render();
    }

    /**
     * target tour action
     *
     */
    public function targettourAction()
    {
        $action = $this->getParam('CF_step', 'start');
        $this->view->action = $action;
        
        $this->render();
    }
    
    /**
     * get award action
     *
     */
    public function getawardAction()
    {
        $action = $this->getParam('CF_step', 'start');
        
        require_once 'Mdal/Disney/Cup.php';
        $mdalCup = Mdal_Disney_Cup::getDefaultInstance();
            
        if ('start' == $action) {
            $cupinfo = $mdalCup->getUserCupRand($this->_user->getId());
            $this->view->cup = $cupinfo;
            
            require_once 'Mbll/Disney/Index.php';
            $mbllIndex = new Mbll_Disney_Index();
            $mbllIndex->addUserCupActivity($this->_USER_ID, $this->_APP_ID, $cupinfo);
        }
        else {
            $id = $this->getParam('CF_id');
            $cid = $this->getParam('CF_cid');
            
            if (!empty($id)) {
                //update award status
                require_once 'Mbll/Disney/Index.php';
                $mbllIndex = new Mbll_Disney_Index();
                $mbllIndex->updateUserCup($this->_user->getId(), $cid, $id);
            }
            
            $cup = $mdalCup->getUserCupRand($this->_user->getId());
            
            if (empty($cup)) {
                $this->_redirect($this->_baseUrl . '/mobile/disney/index');
            }
            else {
                $this->_redirect($this->_baseUrl . '/mobile/disney/getaward');
            }
        }
        
        $this->render();
    }
    
    
    
    /**
     * disney pay action
     *
     */
    public function payAction()
    {
        $payid = $this->getParam('CF_yd', 1);
        $param = $this->getParam('CF_param');
        
        if ( $param == 'ticket' ) {
            $ticketType = $this->getParam('CF_ticket');
            switch ($ticketType) {
                case 1 :
                    $payid = 3;
                    break;
                case 2 :
                    $payid = 4;
                    break;
                case 3 :
                    $payid = 5;
                    break;
                default :
                    $this->_redirect($this->_baseUrl . '/mobile/disney/getticket');
                    return;
            }
        }
        
        if (empty($payid)) {
            exit(0);
        }
        
        require_once 'Mbll/Disney/Pay.php';
        $mbllPay = new Mbll_Disney_Pay();
        $payment = $mbllPay->getPayData($payid, $param);
        
        //pay start        
        require_once 'Bll/Restful.php';
        $restful = Bll_Restful::getInstance($this->_user->getId(), $this->_APP_ID);
        $data = $restful->createPoint($payment, $payment['item']);

        //insert into ponit_code
        if(empty($data)) {
            $this->_redirect($payment['finish_url']);
        }
        
        $result = $mbllPay->insertPay($payid, $this->_user->getId(), $data['id'], $data['updated']);
        
        if ($result) {
            $this->_redirect($data['link']);
        }
        else {
            $this->_redirect($payment['finish_url']);
        }
    }
    
    public function kokujiAction()
    {
    	$this->view->apo = $this->checkApologize();
        $this->render();
    }    
    
    public function payticketAction()
    {
        ob_end_clean();
        ob_start();
        ini_set('default_charset', null);
        header('HTTP/1.1 200 OK');
        header('Status: 200');
        header('Content-Type: text/plain');
        
        require_once 'Bll/Restful.php';
        $restful = Bll_Restful::getInstance($this->_user->getId(), $this->_APP_ID);
        $sig = $restful->checkSignature();

        if (!$sig) {
            echo 'CANCEL';
            exit(0);
        }
        
        $point_code = $this->getParam('point_code');
        $pay_status = $this->getParam('status', 20);
        $updated = $this->getParam('updated');
                        
        //check point_code and status
        if (empty($point_code) || $pay_status == 20) {
            //delete payment info by $point_code
            require_once 'Mdal/Disney/Pay.php';
            $dalPay = Mdal_Disney_Pay::getDefaultInstance();
            $dalPay->updatePayStatus(2, time(), $point_code);
            
            echo 'OK';
            exit(0);
        }
        
        require_once 'Mbll/Disney/Pay.php';
        $mbllPay = new Mbll_Disney_Pay();
        $result = $mbllPay->payTicketFinish($point_code);
        
        echo $result ? 'OK' : 'CANCEL';
        exit(0);
    }
    
    public function paysendawardAction()
    {
        ob_end_clean();
        ob_start();
        ini_set('default_charset', null);
        header('HTTP/1.1 200 OK');
        header('Status: 200');
        header('Content-Type: text/plain');
        
        require_once 'Bll/Restful.php';
        $restful = Bll_Restful::getInstance($this->_user->getId(), $this->_APP_ID);
        $sig = $restful->checkSignature();

        if (!$sig) {
            echo 'CANCEL';
            exit(0);
        }
        
        $point_code = $this->getParam('point_code');
        $pay_status = $this->getParam('status', 20);
        $updated = $this->getParam('updated');
        $pid = $this->getParam('CF_pid');
        $fid = $this->getParam('CF_fid');
        
        //check point_code and status
        if (empty($point_code) || $pay_status == 20) {
            //delete payment info by $point_code
            require_once 'Mdal/Disney/Pay.php';
            $dalPay = Mdal_Disney_Pay::getDefaultInstance();
            $dalPay->updatePayStatus(2, time(), $point_code);
            
            echo 'OK';
            exit(0);
        }
        
        //send friend award
        require_once 'Mbll/Disney/Pay.php';
        $mbllPay = new Mbll_Disney_Pay();
        $result = $mbllPay->paySendAwardFinish($this->_user->getId(), $fid, $pid, $this->_APP_ID, $point_code);        
        
        echo $result == 1 ? 'OK' : 'CANCEL';
        exit(0);
    }
    
    /**
     * pay download 
     *
     */
    public function paydownloadAction()
    {
        ob_end_clean();
        ob_start();
        ini_set('default_charset', null);
        header('HTTP/1.1 200 OK');
        header('Status: 200');
        header('Content-Type: text/plain');
        
        require_once 'Bll/Restful.php';
        $restful = Bll_Restful::getInstance($this->_user->getId(), $this->_APP_ID);
        $sig = $restful->checkSignature();

        if (!$sig) {
            echo 'CANCEL';
            exit(0);
        }
        
        $point_code = $this->getParam('point_code');
        $pay_status = $this->getParam('status', 20);
        $updated = $this->getParam('updated');
        $pid = $this->getParam('CF_pid', 1);
        
        //check point_code and status
        if (empty($point_code) || $pay_status == 20) {
            //delete payment info by $point_code
            require_once 'Mdal/Disney/Pay.php';
            $dalPay = Mdal_Disney_Pay::getDefaultInstance();
            $dalPay->updatePayStatus(2, time(), $point_code);
            
            echo 'OK';
            exit(0);
        }
        
        require_once 'Mbll/Disney/Pay.php';
        $mbllPay = new Mbll_Disney_Pay();
        $result = $mbllPay->payDownloadFinish($this->_USER_ID, $pid, $point_code, $this->_APP_ID);
        
        echo $result ? 'OK' : 'CANCEL';
        exit(0);
    }
    
    /**
     * pay desktop 
     *
     */
    public function paydesktopAction()
    {
        ob_end_clean();
        ob_start();
        ini_set('default_charset', null);
        header('HTTP/1.1 200 OK');
        header('Status: 200');
        header('Content-Type: text/plain');
        
        require_once 'Bll/Restful.php';
        $restful = Bll_Restful::getInstance($this->_user->getId(), $this->_APP_ID);
        $sig = $restful->checkSignature();

        if (!$sig) {
            echo 'CANCEL';
            exit(0);
        }
        
        $point_code = $this->getParam('point_code');
        $pay_status = $this->getParam('status', 20);
        $updated = $this->getParam('updated');
        $pid = $this->getParam('CF_pid', 1);
        
        //check point_code and status
        if (empty($point_code) || $pay_status == 20) {
            //delete payment info by $point_code
            require_once 'Mdal/Disney/Pay.php';
            $dalPay = Mdal_Disney_Pay::getDefaultInstance();
            $dalPay->updatePayStatus(2, time(), $point_code);
            
            echo 'OK';
            exit(0);
        }
        
        require_once 'Mbll/Disney/Pay.php';
        $mbllPay = new Mbll_Disney_Pay();
        $result = $mbllPay->payDesktopFinish($this->_USER_ID, $pid, $point_code, $this->_APP_ID);
        
        echo $result ? 'OK' : 'CANCEL';
        exit(0);
    }

    public function countryAction()
    {
    	require_once 'Mdal/Disney/Place.php';
    	$mdalPlace = Mdal_Disney_Place::getDefaultInstance();
    	
    	$area = array('1'=>'九州/沖縄','2'=>'四国','3'=>'中国','4'=>'近畿',
    				  '5'=>'東海','6'=>'関東','7'=>'北陸','8'=>'北海道/東北');
    	$award = array();
    	for ($i = 8; $i > 0; $i--) {
    		$temp = array('aid' => $i,
    					  'name'=>$area[$i], 
    					  'area'=>$mdalPlace->getAllAwardByUser($this->_USER_ID, $i));
    		$award = array_merge($award, array($temp));
    	}
    	
    	$this->view->award = $award;
    	$this->render();
    }
    
    public function chargeAction()
    {
        $this->render();
    }
    
    public function tokusAction()
    {
        $this->render();
    }
    
    public function kokuj002Action()
    {
        $this->render();
    }
    
    public function checkApologize()
    {
        $apo = false;
        
        if (!$apo) {
            $uid = $this->_USER_ID;
            
            $apo_users = array(
            		'8038124', // for test
					'341715',
					'568248',
					'615663',
					'674537',
					'746959',
					'1129970',
					'1138343',
					'1173430',
					'1193329',
					'1282629',
					'1461949',
					'1521919',
					'1607593',
					'1685189',
					'1687557',
					'2124891',
					'2262312',
					'2586261',
					'2843957',
					'3084248',
					'3264060',
					'3347348',
					'3463627',
					'4398915',
					'4403827',
					'4462809',
					'4552788',
					'4700086',
					'4789203',
					'5120662',
					'5670733',
					'5886135',
					'6479617',
					'6777634',
					'6917535',
					'7541122',
					'7827364',
					'7953884',
					'8841518',
					'8930589',
					'9570219',
					'9585047',
					'9644740',
					'9657631',
					'9751755',
					'9771754',
					'10934225',
					'11308121',
					'11845975',
					'12090566',
					'12240786',
					'12282019',
					'12493408',
					'12508326',
					'12915263',
					'13101577',
					'13286786',
					'13716036',
					'13798188',
					'14545865',
					'14563277',
					'14839266',
					'15142284',
					'16128109',
					'16452914',
					'16717672',
					'16785010',
					'16908906',
					'17990590',
					'18717423',
					'19144623',
					'19443409',
					'19516225',
					'19703102',
					'19809580',
					'20670243',
					'21231475',
					'21417281',
					'21497549',
					'22788104',
					'23193897',
					'23540072',
					'23995724',
					'24102654',
					'24232121',
					'24242857',
					'24333169',
					'24362683',
					'24547033',
					'24803426',
					'24909741',
					'24919892',
					'24930338',
					'24967087',
					'25659068',
					'25768688',
					'25991238',
            );
            if (in_array($uid, $apo_users)) {
                $apo = true;
            }
        }

        return $apo;
    }
    
    public function mailfactoryAction()
    {
        $this->render();
    }
    
    public function rankingfriendAction()
    {
    	print_r(Mbll_Disney_Cache::getUserRankNmInFriends($this->_USER_ID));
    	exit;
    }    
    
    public function inviteAction()
    {
        $this->render();
    }
    
    public function diaryAction()
    {
        $body = "ﾃﾞｨｽﾞﾆｰのご当地ｺﾚｸｼｮﾝｱﾌﾟﾘで、\n"
              . "地域限定のｽﾃｨｯﾁ画像をもらったよ。\n\n"                 
              . "47都道府県ｺﾚｸｼｮﾝ制覇を目指してがんばってます!\n"
              . "----------------------------\n"
              . "ﾌﾟﾚｾﾞﾝﾄｷｬﾝﾍﾟｰﾝ実施中!\n"
              . "Disneyご当地ｺﾚｸｼｮﾝ\n"
              . "http://ma.mixi.net/11122/\n"
              . "----------------------------\n";
		$title = "[無料]Disneyご当地ｺﾚｸｼｮﾝでｱｲﾃﾑもらえるよ";  
        $ua = Zend_Registry::get('ua');
		if ( $ua == 3 ){
			$diary_title = urlencode(mb_convert_encoding($title, 'SJIS','UTF-8'));
			$diary_body  = urlencode(mb_convert_encoding($body, 'SJIS','UTF-8'));
			$this->view->diaryUrl = "http://m.mixi.jp/add_diary.pl?diary_title=" . $diary_title . "&diary_body=" . $diary_body . "&guid=ON";
    	}else {
			$this->view->diary_title = $title;
			$this->view->diary_body = $body;
		}
		
        $this->render();
    }
    
    public function willcomAction()
    {
        $this->render();
    }
    
    public function shopAction()
    {
        $this->render();
    }
    
    public function buyitemAction()
    {
        $sid = $this->_getParam('CF_sid' , 7);
        
        //check user shoes
        require_once 'Mdal/Disney/Shoes.php';
        $mdalShoes = Mdal_Disney_Shoes::getDefaultInstance();
        $userShoes = $mdalShoes->getUserShoes($this->_USER_ID);
        
        if (!empty($userShoes)) {
            $this->view->shoesErr = true;
        }
        
        require_once 'Mbll/Disney/Cache.php';
        Mbll_Disney_Cache::clearPayment();
        $paymentArray = Mbll_Disney_Cache::getPayment();    
        
        foreach ($paymentArray as $item) {
            if ($item['id'] == $sid) {
                $shoes = $item;
            }
        }
        
        $this->view->shoes = $shoes;
        $this->render();
    }
    
    public function payshoesAction()
    {
        ob_end_clean();
        ob_start();
        ini_set('default_charset', null);
        header('HTTP/1.1 200 OK');
        header('Status: 200');
        header('Content-Type: text/plain');
                    
        require_once 'Bll/Restful.php';
        $restful = Bll_Restful::getInstance($this->_user->getId(), $this->_APP_ID);
        $sig = $restful->checkSignature();

        if (!$sig) {
            echo 'CANCEL';
            exit(0);
        }
        
        $point_code = $this->getParam('point_code');
        $pay_status = $this->getParam('status', 20);
        $updated = $this->getParam('updated');
        $sid = $this->getParam('CF_sid');
        
        if (empty($sid)) {
            echo 'CANCEL';
            exit(0);
        }
        
        //check point_code and status
        if (empty($point_code) || $pay_status == 20) {
            //delete payment info by $point_code
            require_once 'Mdal/Disney/Pay.php';
            $dalPay = Mdal_Disney_Pay::getDefaultInstance();
            $dalPay->updatePayStatus(2, time(), $point_code);
            
            echo 'OK';
            exit(0);
        }
        
        require_once 'Mbll/Disney/Pay.php';
        $mbllPay = new Mbll_Disney_Pay();
        $result = $mbllPay->payShoesFinish($this->_USER_ID, $sid, $point_code);
        
        echo $result ? 'OK' : 'CANCEL';
        exit(0);
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
        return $this->_redirect($this->_baseUrl . '/mobile/disney/index');
    }
}
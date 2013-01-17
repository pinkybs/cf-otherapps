<?php

/** @see MyLib_Zend_Controller_Action_Mobile.php */
require_once 'MyLib/Zend/Controller/Action/Mobile.php';

/**
 * Brain Controller(modules/mobile/controllers/BrainController.php)
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/09/08   xial
 */
class Mobile_BrainController extends MyLib_Zend_Controller_Action_Mobile
{
	protected $_pageSize = 5;
	protected $_userInfo ;
	protected $_gameInfo ;

    public function preDispatch()
    {
    	
    }
    
    /**
    * game start
    *
    */
    public function startAction()
    {
        $ua = Zend_Registry::get('ua');
        if ($ua == 0) {
            exit;
        }
        
        $version = 13;
        
        //game start
        //save game id
        $CF_uid = $this->_user->getId();
        $gid = $this->getParam('gid',1);        
        
        $mobileType = Zend_Registry::get('ua');        //1=docomo 2=sb 3=au
                
        $typeList = array(0=>'docomo', 1=>'docomo', 2=>'sb', 3=>'au', 4=>'docomo', 5=>'docomo');
        switch ($gid) {
            case 1 :
                $swf = file_get_contents($this->_staticUrl . '/apps/brain/flash/lesson_calc_' . $typeList[$mobileType] . '.swf?' . $version);
                break;
            case 2 :
                $swf = file_get_contents($this->_staticUrl . '/apps/brain/flash/lesson_EngLish_' . $typeList[$mobileType] . '.swf?' . $version);
                break;
            case 3 :
                $swf = file_get_contents($this->_staticUrl . '/apps/brain/flash/lesson_kanji_' . $typeList[$mobileType] . '.swf?' . $version);
                break;
            case 4 :
                $swf = file_get_contents($this->_staticUrl . '/apps/brain/flash/lesson_history_' . $typeList[$mobileType] . '.swf?' . $version);
                break;
            case 5 :
            	//zhaoxh 20091126
            	require_once 'Mdal/Brain/Brain.php';
        		$dalGame = Mdal_Brain_Brain::getDefaultInstance();
		        $k = $dalGame->getBsyouResult($CF_uid);
		        //info_log($k,'brainswf');
		        if ($k == 0) {
		        	info_log('jump5','brainswf');
		        	//$this->_redirect('/mobile/brain/top');
		        	exit(0);
		        }
		        //zhaoxh 20091126
                $swf = file_get_contents($this->_staticUrl . '/apps/brain/flash/lesson_busyou_' . $typeList[$mobileType] . '.swf?10');
                break;
            default :
                $swf = file_get_contents($this->_staticUrl . '/apps/brain/flash/lesson_calc_docomo.swf?10');
                break;
        }
        
        ob_end_clean();
        ob_start();
        header("Accept-Ranges: bytes");
        header("Cache-Control: no-cache, must-revalidate");
        header("Content-Type: application/x-shockwave-flash");
        /*
        //au
        if ($mobileType == 3 ) {
        	info_log('in_au_userAgent','brainLog');
            header("HTTP_USER_AGENT: KDDI-MA33 UP.Browser/6.2.0.13.2 (GUI) MMP/2.0");
        }
        
        //softbank
        if ($mobileType == 2 ) {
            header("HTTP_USER_AGENT: SoftBank/1.0/816SH/SHJ001 Browser/NetFront/3.4 Profile/MIDP-2.0 Configuration/CLDC-1.1");
        }
        */
        echo $swf;
        exit(0);
    }
    
    /**
     * index page
     *
     */
    public function topAction()
    {
    	$CF_uid = $this->_user->getId();
        $specialGameId = 5;

        $userInfo['uid'] = $CF_uid;
        require_once 'Bll/User.php';
        Bll_User::appendPerson($userInfo, 'uid');
        
        require_once 'Mdal/Brain/Brain.php';
        $dalGame = Mdal_Brain_Brain::getDefaultInstance();
        $userInfo['bsyou_on'] = $dalGame->getBsyouResult($CF_uid);
        
        if ($userInfo['bsyou_on'] == 0) {
            require_once 'Mbll/Brain/Brain.php';
            $bllBrain = new Mbll_Brain_Brain();
            $bllBrain->updateUserBsyou($CF_uid);
            
            $userInfo['bsyou_on'] = $dalGame->getBsyouResult($CF_uid);
        }
        
        if ($userInfo['bsyou_on'] == 1) {
        	$dalGame->bsyouOneTwo($CF_uid);
        }
        
        require_once 'Bll/Friend.php';
        $fidsStr = Bll_Friend::getFriendIds($CF_uid);
        $fids = explode(',', $fidsStr);

        $totalScore = $dalGame->getTotalScoreById($CF_uid);
        $this->view->myscore = $totalScore;

        $gameInfo = $dalGame->getlistGameInfoById();
        $specialRank = 0;
        foreach ($gameInfo as $key => $value)
        {
            $CF_gid = $value['gid'];
            $gameInfo[$key]['mineRank'] = $dalGame->getFriendGameRankById($CF_uid, $CF_gid, $fids);
            if ($key == ($specialGameId - 1)) {
            	$specialRank = $dalGame->getFriendGameRankById($CF_uid, $CF_gid, $fids);;
            }
        }

        $rankMine = $dalGame->getTotalScoreFriendRank($fids, $CF_uid);
        
        //totalInfo ranking  AT MOST 3 persons ,
        $totalInfo = array();
        $pageStart = 0;
        $pageIndex = 1;
        //zhaoxh will modify this function when needed              1201 modidied
        $fidsStr .= "," . $CF_uid;
        $fidsUid = explode(',', $fidsStr);
        $totalInfo = $dalGame->getUserInfoTotal(0, 3, $fidsUid);
        
        if ( $totalInfo ) {
            for ($i = 0, $len = count($totalInfo); $i < $len; $i++) {
                $totalInfo[$i]['ranknm'] = $i + 1 + (($pageIndex - 1) * 3);
            }
        }
        $selfRank = $dalGame->getTotalScoreFriendRank($fids, $CF_uid);
        $selfTotalScore = $dalGame->getTotalScoreById($CF_uid);
        if ($selfRank > 3) {
            $totalInfo[2] = array('uid' => $CF_uid,
                                  'totalScore' => $selfTotalScore,
                                  'ranknm' => $selfRank);
        }
        require_once 'Bll/User.php';
        Bll_User::appendPeople($totalInfo, 'uid');
        $this->view->totalInfo = $totalInfo;
        //---totalInfo end
        
        $this->view->userInfo = $userInfo;
        $this->view->gameInfo = $gameInfo;
        $this->view->totalRank = $rankMine;
        $this->view->specialRank = $specialRank;
        
        $rankAll = $dalGame->getTotalScoreRank($CF_uid);
         /*   
        $diary_title = urlencode("私の総合学力は" . $totalScore . "点でした！");
        $diary_body = urlencode("「マイミク頭脳くらべ」で学力テストを受けました！/n/n◯総合成績:".$totalScore."点/n総合順位:".$rankAll."位/n"
                               ."ﾏｲﾐｸ順位:".$rankMine."位/n/n↓みんなもやってみて！/n脳を鍛える!ﾏｲﾐｸ頭脳くらべ/nhttp://mixi.jp/view_appli.pl?id=9461");
        */
        $body = "「マイミク頭脳くらべ」で学力テストを受けました！\n\n◯総合成績:".$totalScore."点\n総合順位:".$rankAll."位\n"
               ."ﾏｲﾐｸ順位:".$rankMine."位\n\n↓みんなもやってみて！\n脳を鍛える!ﾏｲﾐｸ頭脳くらべ\nhttp://mixi.jp/view_appli.pl?id=9461";
		$title = "私の総合学力は" . $totalScore . "点でした！";                       
                               
        //$this->view->diary_title = $title;
	    //$this->view->diary_body = $body;
        //$this->view->diaryUrl = "http://m.mixi.jp/add_diary.pl?diary_title=" . $diary_title . "&diary_body=" . $diary_body;      
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
     * before a game start, show options and menu
     *
     */
    public function gameAction()
    {
    	$CF_uid = $this->_user->getId();
    	$CF_gid = (int)$this->getParam('gid', 1);

    	require_once 'Mdal/Brain/Brain.php';
        $dalGame = Mdal_Brain_Brain::getDefaultInstance();

        $gameInfo = $dalGame->getGameInfoById($CF_gid);
        $gameInfo['description'] = substr($gameInfo['description'], 0, 100);
       
        $this->view->rand = time();
        $this->view->uid = $CF_uid;
    	$this->view->gameInfo = $gameInfo;
        $this->render();
    }
    
    /**
     * one game has one description
     *
     */
    public function descriptionAction()
    {
    	$CF_uid = $this->_user->getId();
        $CF_gid = (int)$this->getParam('gid', 1);

        require_once 'Mdal/Brain/Brain.php';
        $dalGame = Mdal_Brain_Brain::getDefaultInstance();
        
        $this->view->rand = time();
        $gameInfo = $dalGame->getGameInfoById($CF_gid);
        $this->view->gameInfo = $gameInfo;
        $this->view->uid = $CF_uid;
        $this->render();
    }

    /**
     * ranking action ---total and onegame , all show 
     *
     */
    public function rankingAction()
    {
    	//$time1 = $this->microtime_float();
    	$CF_uid = $this->_user->getId();
        $pos = 1;//(int)$this->getParam('pos', 2);

        $pageIndex = (int)$this->getParam('page', 0);
        require_once 'Mdal/Brain/Brain.php';
        $dalGame = Mdal_Brain_Brain::getDefaultInstance();

        /**
         * friend id
         */
        require_once 'Bll/Friend.php';
        $fidsStr = Bll_Friend::getFriendIds($CF_uid);
        $fids = explode(',', $fidsStr);

        $specialGameId = 5;
        $pageSize = 3;

        //$rankMine = 0;
        
        $rankMine = $dalGame->getTotalScoreFriendRank($fids, $CF_uid);
        /*
        $count = 0;

        if ($pos == 1 || $pos == '1') {
            
            //$count = $dalGame->getTotalScoreFriendCountById($fids, $CF_uid);
        }
        else {
        	$rankMine = $dalGame->getTotalScoreRank($CF_uid);
        	$count = $dalGame->getTotalScoreCountById($specialGameId);
        }
        */

        if ( !$pageIndex ) {
            $pageIndex = ceil($rankMine/$pageSize);
        }

        //totalInfo view data ,
        $totalInfo = array();
        $pageStart = 0;
        $pageIndex = 1;
        //zhaoxh will modify this function when needed             1201 modidied
        $fidsStr .= "," . $CF_uid;
        $fidsUid = explode(',', $fidsStr);
        $totalInfo = $dalGame->getUserInfoTotal(0, $pageSize, $fidsUid); 
         
        
        if ( $totalInfo ) {
            for ($i = 0, $len = count($totalInfo); $i < $len; $i++) {
                $totalInfo[$i]['ranknm'] = $i + 1 + (($pageIndex - 1) * 3);
            }
        } 
        $selfRank = $dalGame->getTotalScoreFriendRank($fids, $CF_uid);
        $selfTotalScore = $dalGame->getTotalScoreById($CF_uid);
        if ($selfRank > 3) {
            $totalInfo[2] = array('uid' => $CF_uid,
                                  'totalScore' => $selfTotalScore,
                                  'ranknm' => $selfRank);                               
        }
        require_once 'Bll/User.php';
        Bll_User::appendPeople($totalInfo, 'uid');
        $this->view->totalInfo = $totalInfo;
        //---totalInfo end
        
        
        $gameInfo = array();
        $gameInfo = $dalGame->getlistGameInfoById();

        /**
         * pos == 1 (ﾏｲﾐｸ内) 2 (ﾐｸｼｨ全体)
         */
        foreach ($gameInfo as $key => $value)
        {
        	$CF_gid = $value['gid'];
        	/**
        	 * rank and count info
        	 */
	        if ($pos == 1 || $pos == '1') {
                $gameInfo[$key]['gameCount'] = $dalGame->getFriendGameCount($fids, $CF_gid);
                $gameInfo[$key]['mineRank'] = $dalGame->getFriendGameRankById($CF_uid, $CF_gid, $fids);
	        }
	        else {
        		$gameInfo[$key]['gameCount'] = $dalGame->getAllGameCountByGid($CF_gid);
        		$gameInfo[$key]['mineRank'] = $dalGame->getRankById($CF_uid, $CF_gid);
	        }
        }

        /**
         * page info
         */
        foreach ($gameInfo as $key => $value)
        {
        	$rankMine = $value['mineRank'];
	        if ( !$pageIndex ) {
	            $pageIndex = ceil($rankMine/$pageSize);
	        }
	        $gameInfo[$key]['pageIndex'] = $pageIndex;
        }

        /**
         * pos == 1 (ﾏｲﾐｸ内) 2 (ﾐｸｼｨ全体)
         */
        foreach ($gameInfo as $key => $value)
        {
        	$CF_gid = $value['gid'];
        	/**
        	 * user info
        	 */
        	$pageIndex = $value['pageIndex'];

            if ($pos == 1 || $pos == '1') {
                $gameInfo[$key]['info'] = $dalGame->getUserInfo($CF_gid, $pageStart, $pageSize, $fidsUid);
            }
            else {
                $gameInfo[$key]['info'] = $dalGame->getUserInfo($CF_gid, $pageStart, $pageSize);
            }

            if ($gameInfo[$key]['info']) {
            	$aryInfo = $gameInfo[$key]['info'];
	            for ($i = 0, $len = count($aryInfo); $i < $len; $i++) {
	                $gameInfo[$key]['info'][$i]['ranknm'] = $i + 1 + (($pageIndex - 1) * 3);
	            }
	            
	            
	            //edit --move self info to 3rd 
	            $selfRank = $dalGame->getFriendGameRankById($CF_uid, $CF_gid, $fids);
	            $arrayG = $dalGame->getScore($CF_uid,$CF_gid);
	            
                if ($selfRank > 3){
                    $gameInfo[$key]['info'][2] = array('gid' => $CF_gid,
                                                       'uid' => $CF_uid,
                                                       'ranknm' => $selfRank,
                                                       'score' => $arrayG['score'],
                                                       'id' => $arrayG['id']); 
                }
                //end edit
                
	            require_once 'Bll/User.php';
                Bll_User::appendPeople($gameInfo[$key]['info'], 'uid');
            }
        }

        //zhaoxh 20091126
        $k = $dalGame->getBsyouResult($CF_uid);
        if ($k == 0) {
        	unset($gameInfo[4]);
        }
        //zhaoxh 20091126
        $this->view->pos = $pos;
        $this->view->uid = $CF_uid;
        $this->view->classInfo = $gameInfo;
        
        //$time2 = $this->microtime_float();
        //$timecost = $time2 - $time1;
        //info_log($timecost,'timebrain');
        $this->render();
    }
    
    /**
     * total score ranking
     *
     */
    public function rankingtwoAction()
    {
        $CF_uid = $this->_user->getId();
        $pos = (int)$this->getParam('pos', 1);
        require_once 'Mdal/Brain/Brain.php';
        $dalGame = Mdal_Brain_Brain::getDefaultInstance();

        $left = (int)$this->getParam('left');
        $right = (int)$this->getParam('right');
        
        //get cnt and self position
        if ($pos == 2) {
            $rankSelf = $dalGame->getTotalScoreRank($CF_uid);
            $rankCnt = $dalGame->cntRankByIdTotal();
        }
        else {
            require_once 'Bll/Friend.php';
            $fidsStr = Bll_Friend::getFriendIds($CF_uid);
            $fidsStr .= "," . $CF_uid;
            $fids = explode(',', $fidsStr);
            $rankSelf = $dalGame->getTotalScoreFriendRank($fids, $CF_uid);
            $rankCnt = $dalGame->getTotalScoreFriendCountById($fids, $CF_uid);
        }
       
        //get info array $re ---pagesize <= 5
        $re = array();
        if (!$left && !$right) {
            
            if ($rankSelf <= 3 ){
                $left = 1;
                $right =min($rankCnt,5);
                $size = min($rankCnt,5);
            }
            else if ($rankCnt - $rankSelf < 2) {
                $left = max(1,$rankCnt - 4);
                $right = $rankCnt;
                $size = min($rankCnt,5);
            }
            else {
                $left = $rankSelf - 2;
                $right = $rankSelf + 2;
                $size = 5;
            }
            
            if ($pos == 2){
                $re = $dalGame->getUserInfoTotal($left-1,$size);
            }
            else {
                $re = $dalGame->getUserInfoTotal($left-1,$size,$fids);
            }
        }
        else {
            if ($left < 1) {
                $left = 1;
                $right = min(5,$rankCnt);
            }
            else if ($right > $rankCnt) {
                $right = $rankCnt;
                $left = max($right - 4,1);
            }
            $size = $right - $left + 1;
            if ($pos == 2){
            	$re = $dalGame->getUserInfoTotal($left-1, $size);
            }
            else {
            	$re = $dalGame->getUserInfoTotal($left-1, $size, $fids);
            }
        }
        
        for ($i = 0; $i < count($re); $i++) {
            $re[$i]['rank'] =  $left + $i;
        }
        require_once 'Bll/User.php';
        Bll_User::appendPeople($re, 'uid');
        
        $this->view->maeleft = $left - 5;
        $this->view->maeright = $left - 1;
        $this->view->tugileft = $right + 1;
        $this->view->tugiright = $right + 5;
        $this->view->classInfo = $re;
        
        //$gameInfo = $dalGame->getGameInfoById($CF_gid);
        //$this->view->gname = $gameInfo['gname'];
        //$this->view->gid = $CF_gid;
        $this->view->pos = $pos;
        $this->view->uid = $CF_uid;
        $listCount = array('startCount' => $left, 'endCount' => $right );
        $this->view->listCount = $listCount;
        $this->view->count = $rankCnt;
        $this->render();
    }
    
    /**
     * get onegame ranking by gid
     *
     */
    public function rankingthreeAction()
    {
    	$CF_uid = $this->_user->getId();
        $CF_gid = (int)$this->getParam('gid', 1);
        $pos = (int)$this->getParam('pos', 1);
        require_once 'Mdal/Brain/Brain.php';
        $dalGame = Mdal_Brain_Brain::getDefaultInstance();

        $left = (int)$this->getParam('left');
        $right = (int)$this->getParam('right');
        
        //get cnt and self position
        if ($pos == 2) {
            $rankSelf = $dalGame->getRankById($CF_uid, $CF_gid);
            $rankCnt = $dalGame->cntRankById($CF_gid);
        }
        else {
            require_once 'Bll/Friend.php';
            $fidsStr = Bll_Friend::getFriendIds($CF_uid);
            $fidsStr .= "," . $CF_uid;
            $fids = explode(',', $fidsStr);
            $rankSelf = $dalGame->getFriendGameRankById($CF_uid, $CF_gid, $fids);
            $rankCnt = $dalGame->cntFriendRankById($CF_gid ,$fids);
        }
        
        //get info array $re ---pagesize <= 5
        $re = array();
        if (!$left && !$right) {
            
            if ($rankSelf <= 3 ){
                $left = 1;
                $right =min($rankCnt,5);
                $size = min($rankCnt,5);
            }
            else if ($rankCnt - $rankSelf < 2) {
                $left = max(1,$rankCnt - 4);
                $right = $rankCnt;
                $size = min($rankCnt,5);
            }
            else {
                $left = $rankSelf - 2;
                $right = $rankSelf + 2;
                $size = 5;
            }
            
            if ($pos == 2){
                $re = $dalGame->getUserInfo($CF_gid,$left-1,$size);
            }
            else {
                $re = $dalGame->getUserInfo($CF_gid,$left-1,$size,$fids);
            }
        }
        else {
            if ($left < 1) {
                $left = 1;
                $right = min(5,$rankCnt);
            }
            else if ($right > $rankCnt) {
                $right = $rankCnt;
                $left = max($right - 4,1);
            }
            
        	$size = $right - $left + 1;
            if ($pos == 2){
            	$re = $dalGame->getUserInfo($CF_gid,$left-1,$size);
            }
            else {
            	$re = $dalGame->getUserInfo($CF_gid,$left-1,$size,$fids);
            }
        }
        
        for ($i = 0; $i < count($re); $i++) {
            $re[$i]['rank'] =  $left + $i;
        }
        require_once 'Bll/User.php';
        Bll_User::appendPeople($re, 'uid');
        
        $this->view->maeleft = $left - 5;
        $this->view->maeright = $left - 1;
        $this->view->tugileft = $right + 1;
        $this->view->tugiright = $right + 5;
        $this->view->classInfo = $re;
        
        $gameInfo = $dalGame->getGameInfoById($CF_gid);
        $this->view->gname = $gameInfo['gname'];
        $this->view->gid = $CF_gid;
        $this->view->pos = $pos;
        $this->view->uid = $CF_uid;
        $listCount = array('startCount' => $left, 'endCount' => $right );
        $this->view->listCount = $listCount;
        $this->view->count = $rankCnt;
        $this->render();
    }

    /**
     * result action
     *
     */
    public function resultAction()
    {
    	$CF_uid = $this->_user->getId();
        $CF_gid = (int)$this->getParam('CF_gameName', 1);
        $new_score = (int)$this->getParam('CF_score');
        
        $secret = $this->getParam('CF_secret');
        $secretResult = $this->_decodeCFsecret($new_score);
        
        if ($secret != $secretResult) {
        	info_log($new_score,'brainresult');
        	info_log($secret,'brainresult');
        	info_log($secretResult,'brainresult');
        	info_log($CF_gid,'brainresult');
        	info_log($CF_uid,'brainresult');
        	info_log(date("Ymd.H:i:s"),'brainresult');
        	info_log('--over---','brainresult');
            $this->_redirect('/mobile/brain/top');
        }
        
        
    	$this->view->newScore = $new_score;

    	require_once 'Mbll/Brain/Brain.php';
        $bllGame = new Mbll_Brain_Brain();
        
        require_once 'Mdal/Brain/Brain.php';
        $dalGame = Mdal_Brain_Brain::getDefaultInstance();


        $gameInfo = $dalGame->getGameAndUserInfoById($CF_uid, $CF_gid);
                
        if ($gameInfo['score'] < $new_score) {
            $info = array('uid' => $CF_uid, 'gid' => $CF_gid, 'newScore' => $new_score);
            $bllGame->updateGameScore($info);
            if ($gameInfo) {
                $gameInfo['bestScore'] = $gameInfo['score'];
                
                require_once 'Mbll/Brain/Activity.php';
                $activity = Mbll_Brain_Activity::getActivity(3,$new_score,$gameInfo['gname']);
                require_once 'Bll/Restful.php';
                //get restful object
                $restful = Bll_Restful::getInstance($CF_uid, $this->_APP_ID);
                $restful->createActivity(array('title'=>$activity));
            }
            else {
                $gameInfo = $dalGame->getGameInfo($CF_gid);
                $gameInfo['uid'] = $CF_uid;
                $gameInfo['score'] = 0;
                $gameInfo['bestScore'] = 0;
                
                require_once 'Mbll/Brain/Activity.php';
                $activity = Mbll_Brain_Activity::getActivity(2,$new_score,$gameInfo['gname']);
                require_once 'Bll/Restful.php';
                //get restful object
                $restful = Bll_Restful::getInstance($CF_uid, $this->_APP_ID);
                $restful->createActivity(array('title'=>$activity));
            }
        }
        else {
            if ($gameInfo['score'] == null) {
                $info = array('uid' => $CF_uid, 'gid' => $CF_gid, 'newScore' => 0);
                $bllGame->updateGameScore($info);
            }
            $gameInfo['bestScore'] = $gameInfo['score'];
            //edit
            $gameInfoEdit = $dalGame->getGameInfoById($CF_gid);
            $gameInfo['gname'] = $gameInfoEdit['gname'];
            //edit over
        }        
        
        /**
         * friend id
         */
    	require_once 'Bll/Friend.php';
        $fidsStr = Bll_Friend::getFriendIds($CF_uid);
        $fids = explode(',', $fidsStr);
        
        $gameInfo['rank'] = $dalGame->getFriendGameRankById($CF_uid, $CF_gid, $fids);   //by zhaoxh 20091119 
        
        $classInfo = $dalGame->getGameById($CF_uid);
        foreach ($classInfo as $key => $value)
        {
            $CF_gidone = $value['gid'];
            $classInfo[$key]['friendRank'] = $dalGame->getFriendGameRankById($CF_uid, $CF_gidone, $fids);
            $classInfo[$key]['allRank'] = $dalGame->getRankById($CF_uid, $CF_gidone);
            
        }
        
        //edit classInfo array , make       count($classInfo) = 5 
        $classInfoEdit = array();
        $j=0;
        
        //zhaoxh 20091126
        $k = $dalGame->getBsyouResult($CF_uid);
        if ($k > 0) {
        	$k = 6;
        }
        else {
        	$k = 5;
        }
        //zhaoxh 20091126
        for ($i = 1; $i < $k; $i++) {
        
            if ($classInfo[$j]['gid'] && $classInfo[$j]['gid'] == $i) {
                $classInfoEdit[$i-1] = $classInfo[$j];
                $j++;
            }
            else {
                $arrayG = $dalGame->getGameInfo($i);
                $classInfoEdit[$i-1] = array('gid' => $i,
                                             'score' => null,
                                             'gname' => $arrayG['gname']);
            }
        }
        //end editInfo
        
        $specialGameId = 5;

        /*
         * total score
         */
        $totalFriendRank = $dalGame->getTotalScoreFriendRank($fids, $CF_uid);
        $totalAllRank = $dalGame->getTotalScoreRank($CF_uid);
        $totalScore = $dalGame->getTotalScoreById($CF_uid);

        $avgScore = $dalGame->getAvgScoreById($fids, $CF_uid, $CF_gid);
        $avgScore = number_format($avgScore, 1);
    	$this->view->friendTotalRank = $totalFriendRank;
        $this->view->allTotalRank = $totalAllRank;
        $this->view->totalScore = $totalScore;

        $this->view->avgScore = $avgScore;

        $this->view->gameInfo = $gameInfo;
        
        $this->view->uid = $CF_uid;
        $this->view->classInfo = $classInfoEdit;
        
        $ga = $classInfoEdit[$CF_gid - 1]['gname'];
        $gs = $classInfoEdit[$CF_gid - 1]['score'];
        $grf = $classInfoEdit[$CF_gid - 1]['friendRank'];
        $grt = $classInfoEdit[$CF_gid - 1]['allRank'];
        
        
    	$body = "「マイミク頭脳くらべ」で学力テストを受けました！\n\n◯".$ga.":" .$gs."点\n総合順位:".$grt."位\n"
               ."ﾏｲﾐｸ順位:".$grf."位\n\n↓みんなもやってみて！\n脳を鍛える!ﾏｲﾐｸ頭脳くらべ\nhttp://mixi.jp/view_appli.pl?id=9461";
		$title = "私の".$ga."の学力は" . $gs . "点でした！";                       
                               
        //$this->view->diary_title = $title;
	    //$this->view->diary_body = $body;
        //$this->view->diaryUrl = "http://m.mixi.jp/add_diary.pl?diary_title=" . $diary_title . "&diary_body=" . $diary_body;      
    	$ua = Zend_Registry::get('ua');
		if ( $ua == 3 ){
			$diary_title = urlencode(mb_convert_encoding($title, 'SJIS','UTF-8'));
			$diary_body  = urlencode(mb_convert_encoding($body, 'SJIS','UTF-8'));
			$this->view->diaryUrl = "http://m.mixi.jp/add_diary.pl?diary_title=" . $diary_title . "&diary_body=" . $diary_body . "&guid=ON";
    	}else {
			$this->view->diary_title = $title;
			$this->view->diary_body = $body;
		}
        
        $this->view->rand = time();
        $this->render();
    }

    public function helpAction()
    {        
    	$this->render();
    }

    public function aboutAction()
    {
        $this->render();
    }
    
	public function campaignAction()
    {
        $this->render();
    }
    
	public function invitecompleteAction()
    {
        $this->render();
    }
    /**
     * decode score 
     *
     * @param string $score
     * @return string
     */
    function _decodeCFsecret($score = 0)
    {
        $secretKey = 'cf_mobile_game';
        $str = $secretKey . $score;
        
        for($i=0;$i<strlen($str);$i++)
        {
            switch($i%6)
            {
                case 0:
                    $temp += ord($str{$i})-1;
                    break;
                case 1:
                    $temp += ord($str{$i})-5;
                    break;
                case 2:
                    $temp += ord($str{$i})-7;
                    break;
                case 3:
                    $temp += ord($str{$i})-2;
                    break;
                case 4:
                    $temp += ord($str{$i})-4;
                    break;
                case 5:
                    $temp += ord($str{$i})-9;
                    break;
            }
        }
        return $temp;
    }
    
    function microtime_float()
	{
	    list($usec, $sec) = explode(" ", microtime());
	    return ((float)$usec + (float)$sec);
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
        return $this->_redirect($this->_baseUrl . '/mobile/brain/error');
    }
}
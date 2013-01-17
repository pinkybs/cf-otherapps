<?php

/**
 * Dynamite app controller
 * init each index page
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/07/06	  Liz
 */
class DynamiteController extends MyLib_Zend_Controller_Action_Default
{
    private $_maxBombCount = 26;
    /**
     * index action
     *
     */
    public function indexAction()
    {

        $dynamiteUid = $this->_request->getParam('uid');

        require_once 'Dal/Dynamite/User.php';
        $dalDynamiteUser = Dal_Dynamite_User::getDefaultInstance();

        //get user dynamite info
        $userDynamite = $dalDynamiteUser->getUser($dynamiteUid);

        if ( !$userDynamite ) {
            $dynamiteUid = $this->_user->getId();
            //get user dynamite info
            $userDynamite = $dalDynamiteUser->getUser($dynamiteUid);
        }

        require_once 'Bll/Cache/Dynamite.php';
        $hitmanInfo = Bll_Cache_Dynamite::getHitmanType();
        $userDynamite = array_merge($userDynamite, $hitmanInfo[$userDynamite['hitman_type'] - 1]);

        Bll_User::appendPerson($userDynamite, 'uid');

        $uid = $this->_user->getId();
        $this->view->uid = $uid;
        //get my dynamite info
        $myDynamite = $dalDynamiteUser->getUser($uid);
        $myDynamite = array_merge($myDynamite, $hitmanInfo[$myDynamite['hitman_type'] - 1]);

        $this->view->myDynamite = $myDynamite;
        if ($myDynamite['hitman_type'] == 11 && $myDynamite['bonus'] >= 1000) {
            $this->_redirect($this->_baseUrl . '/dynamite/charaselect');
        }
        //get user hitman pic info
        $userDynamite['pic'] = $myDynamite['pic_id'] < 10 ? '0' . $myDynamite['pic_id'] : $myDynamite['pic_id'];

        $hitmanPicType = 'b';
        for ( $i = 1; $i < 5; $i++ ) {
            $hitmanId = 'hitman_life' . $i;
            if ( $myDynamite[$hitmanId] >= ($myDynamite['max_life']/2) ) {
                $hitmanPicType = 'a';
                break;
            }
        }

        $userDynamite['hitmanPicType'] = $hitmanPicType;

        $this->view->userDynamite = $userDynamite;
        $this->view->txtUserDynamite = Zend_Json::encode($userDynamite);

        require_once 'Bll/Dynamite/Index.php';
        $bllDynamiteIndex = new Bll_Dynamite_Index();
        //get user dynamite, back current and next
        $result = $bllDynamiteIndex->getUserDynamite($this->_user->getId(), $dynamiteUid);
        $this->view->arrDynamite = Zend_Json::encode($result);

        require_once 'Dal/Dynamite/Bomb.php';
        $dalDynamiteBomb = Dal_Dynamite_Bomb::getDefaultInstance();

        //get user's bomb info
        $userBomb = $dalDynamiteBomb->getUserBomb($uid);
        if (!empty($userBomb)) {
            require_once 'Bll/User.php';
            Bll_User::appendPeople($userBomb, 'bomb_uid');
        }
        $this->view->userBomb = $userBomb;

        $userRemoveBomb = $bllDynamiteIndex->getUserRemoveBomb($userDynamite);
        $this->view->userRemoveBomb = $userRemoveBomb;

        for ( $j = 0; $j < ($myDynamite['remainder_bomb_count']-count($userRemoveBomb)); $j++) {
            $remainderBomb[] = $j;
        }
        $this->view->remainderBomb = $remainderBomb;

        if ( $myDynamite['bomb_count'] < $this->_maxBombCount ) {
            $arrEmptyBomb = array();
            for ( $i=0, $icount = ($this->_maxBombCount - $myDynamite['bomb_count']); $i<$icount; $i++ ) {
                $arrEmptyBomb[$i] = $i;
            }

            $this->view->arrEmptyBomb = $arrEmptyBomb;
        }

        //get user item info
        require_once 'Bll/Dynamite/Item.php';
        $bllItem = new Bll_Dynamite_Item();
        $itemList = $bllItem->getItemList($uid);

        $this->view->itemList = $itemList;

        //get rank info
        require_once 'Bll/Dynamite/Rank.php';
        $bllRank = new Bll_Dynamite_Rank();

        $rankInfo = $bllRank->rank($uid);

        $inviteUser = $rankInfo['inviteUser'];

		$this->view->rankCount = $rankInfo['rankCount'];
        $this->view->rankUser = $rankInfo['rankUser'];
        $this->view->topRankUser = $rankInfo['topRankUser'];
        $this->view->inviteUser = $inviteUser;
        $this->view->userRankNum = $rankInfo['userRankNum'];
        $this->view->start = $rankInfo['start'];
        $this->view->lastUserRankNum = $rankInfo['lastUserRankNum'];
        $this->view->rankPrev = $rankInfo['rankPre'];
        $this->view->rankType = 1;
        $this->view->rankRange = 2;
        $this->view->constValue = 6;

        //minifeed
        $this->view->feed = $bllDynamiteIndex->getFeed($uid, $this->_appId);

        $this->render();
    }

    /**
     * start action
     *
     */
    public function startAction()
    {
        $dynamiteUid = $this->_request->getParam('uid');

        /*require_once 'Bll/Application/Plugin/Dynamite.php';
        $bllPlugin = new Bll_Application_Plugin_Dynamite();
        $bllPlugin->postUpdatePerson($this->_user->getId());*/

        require_once 'Dal/Dynamite/User.php';
        $dalDynamiteUser = Dal_Dynamite_User::getDefaultInstance();

        //get user dynamite info
        $userDynamite = $dalDynamiteUser->getUser($dynamiteUid);

        if ( !$userDynamite ) {
            $dynamiteUid = $this->_user->getId();
        }

        if ( $dynamiteUid != $this->_user->getId() ) {
            $this->_redirect($this->_baseUrl . '/dynamite/index/uid/' . $dynamiteUid);
        }

        $this->render();
    }

    /**
     * chara select action
     *
     */
    public function charaselectAction()
    {
    	$uid = $this->_user->getId();

        require_once 'Dal/Dynamite/User.php';
        $dalUser = Dal_Dynamite_User::getDefaultInstance();

        $userInfo = $dalUser->getUserBasicInfo($uid);

        if ( $userInfo['isalive'] && $userInfo['hitman_type'] != 11) {
            $this->_redirect($this->_baseUrl . '/dynamite');
        }
        $this->render();
    }

    /**
     * set select chara
     *
     */
    public function setcharaAction()
    {
        $hitmanType = $this->_request->getParam('chara');

        if ( $hitmanType > 0 && $hitmanType < 4 ) {

        	$uid = $this->_user->getId();

        	require_once 'Bll/Dynamite/Index.php';
            $bllIndex = new Bll_Dynamite_Index();
            $result = $bllIndex->setChara($uid, $hitmanType);

            if ($result) {
                $this->_redirect($this->_baseUrl . '/dynamite/index');
            }
        }

        $this->_redirect($this->_baseUrl . '/dynamite/start');
    }

    /**
     * chara shop action
     *
     */
    public function charashopAction()
    {
        $uid = $this->_user->getId();

        require_once 'Dal/Dynamite/User.php';
        $dalUser = Dal_Dynamite_User::getDefaultInstance();

        $userDynamite = $dalUser->getUser($uid);
        Bll_User::appendPerson($userDynamite, 'uid');

        require_once 'Bll/Cache/Dynamite.php';
        $hitmanInfo = Bll_Cache_Dynamite::getHitmanType();
        $userDynamite['pic_id'] = $hitmanInfo[$userDynamite['hitman_type'] - 1]['pic_id'];
        $userDynamite['max_life'] = $hitmanInfo[$userDynamite['hitman_type'] - 1]['max_life'];

        if ( $userDynamite['isalive'] == 0 ) {
            $this->_redirect($this->_baseUrl . '/dynamite/charaselect');
        }

        //get user hitman pic info
        $userDynamite['pic'] = $userDynamite['pic_id'] < 10 ? '0' . $userDynamite['pic_id'] : $userDynamite['pic_id'];

        $hitmanPicType = 'b';
        for ( $i = 1; $i < 5; $i++ ) {
            $hitmanId = 'hitman_life' . $i;
            if ( $userDynamite[$hitmanId] >= ($userDynamite['max_life']/2) ) {
                $hitmanPicType = 'a';
                break;
            }
        }

        $userDynamite['hitmanPicType'] = $hitmanPicType;
        $this->view->userDynamite = $userDynamite;

        $this->render();
    }

    /**
     * dispatch
     *
     */
    function preDispatch()
    {

        require_once 'Bll/Application/Plugin/Dynamite.php';
        $bllPlugin = new Bll_Application_Plugin_Dynamite();
        $bllPlugin->postUpdatePerson($this->_user->getId());

        $uid = $this->_user->getId();
        require_once 'Dal/Dynamite/User.php';
        $dalUser = Dal_Dynamite_User::getDefaultInstance();
        $isIn = $dalUser->isInDynamite($uid);

        $actionName = $this->_request->getActionName();

        if ($isIn) {
            if ($actionName == 'index') {

            	$userDynamite = $dalUser->getUser($uid);

                require_once 'Bll/Dynamite/Index.php';
                $bllIndex = new Bll_Dynamite_Index();
                //need restart game? 1->yes, -1 -> no
                $needRestartGame = $bllIndex->needRestartGame($uid);

                //no need to restart game
                if ($needRestartGame == -1) {
                    //if user's bomb count=0, send 4 bombs to user
                    if ($userDynamite['bomb_count'] == 0) {
                        $this->view->sendBombCount = 4;
                    }
                }

                //check if user first login today, yes-> send card or bomb
                $lastLoginTime = $userDynamite['last_login_time'];

                //if lastLoginTime==0, user first join game
                if ($lastLoginTime == 0) {
                	$dalUser->updateLastLoginTime($uid);
                    $this->_redirect($this->_baseUrl . '/dynamite/charaselect');
                }
                else {
                    $todayDate = date("Y-m-d");
                    $todayTime = strtotime($todayDate);

                    if ($lastLoginTime < $todayTime) {
                	    $this->view->firstLogin = 1;
                    }
                    else {
                    	require_once 'Dal/Dynamite/Item.php';
                        $dalItem = Dal_Dynamite_Item::getDefaultInstance();
                    	$helpCardCount = $dalItem->haveThisCard($uid, 10);
                    	if ( ($userDynamite['hitman_type'] != 11) && ($userDynamite['bonus'] < 100) && ($userDynamite['dead_number'] >= 3) && ($helpCardCount == 0) ) {
                    		$this->view->sendHelpCard = 1;
                    	}
                    }

                    if ($userDynamite['isgameover'] == 0) {
                        $dalUser->updateLastLoginTime($uid);

                        require_once 'Bll/Cache/Dynamite.php';
                        Bll_Cache_Dynamite::clearUserBasicInfo($uid);
                    }
                    else {
                    	$info = array('isgameover' => 0);

                        $dalUser->updateUserBasicInfo($uid, $info);
                    }
                }

                if ( $needRestartGame != 1 && $userDynamite['isalive'] == 0 ) {
                    $this->_redirect($this->_baseUrl . '/dynamite/charaselect');
                }
            }
        }

        $this->view->userName = $this->_user->getDisplayName();
        $this->view->needRestartGame = $needRestartGame;
        $this->view->mixiHost = MIXI_HOST;
        $this->view->appId = $this->_appId;
        $this->view->csstype = 'dynamite';
    }

    public function specialrankAction()
    {
    	$uid = $this->_user->getId();

    	require_once 'Bll/Dynamite/Rank.php';
    	$bllRank = new Bll_Dynamite_Rank();
    	//get rank user
    	$rankUser = $bllRank->specialRank($uid);

    	$this->view->rewardRankUser = $rankUser['rewardRankUser'];
    	$this->view->gameOverRankUser = $rankUser['gameOverRankUser'];
    	$this->render();
    }

    public function helpAction(){
        $this->render();
    }

    /**
     * magic function
     * if call the function is undefined,then forward to not found
     *
     * @param string $methodName
     * @param array $args
     * @return void
     */
    function __call($methodName, $args)
    {
        return $this->_forward('notfound','error','default');
    }
}
<?php
/** @see Zend_Json */
require_once 'Zend/Json.php';
/** @see MyLib_Zend_Controller_Action_Ajax */
require_once 'MyLib/Zend/Controller/Action/Ajax.php';

/**
 * Dynamite Ajax Controllers
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/07/06    Liz
 */
class Ajax_DynamiteController extends MyLib_Zend_Controller_Action_Ajax
{
    private $_maxBombCount = 26;

    function preDispatch()
    {
        $actionName = $this->_request->getActionName();

        if ($actionName == 'setbomb' || $actionName == 'triggerbomb' || $actionName == 'removebomb'){

            session_start();

            if (!isset($_SESSION['requestCount'])) {

                $_SESSION['requestCount'] = 1;
                $_SESSION['startTime'] = time();
            }
            else {

                $requestCount = $_SESSION['requestCount'];
                $requestCount++;
                $_SESSION['requestCount'] = $requestCount;

                $runTime = time() - $_SESSION['startTime'];

                if ($runTime >= 1) {

                    if ($_SESSION['requestCount'] >= 5) {
                        unset($_SESSION['requestCount']);
                        echo Zend_Json::encode(array('status' => -10));
                        exit();
                    }
                    else {
                        unset($_SESSION['requestCount']);
                    }
                }

            }
        }
    }

    /**
     * get user dynamite info by user id
     *
     */
    public function getuserdynamiteAction()
    {
        if ($this->_request->isPost()) {

        	$uid = $this->_request->getPost('uid');
            $moveDirection = $this->_request->getParam('moveDirection');

            if ( !$uid ) {
                $uid = $this->_user->getId();
            }

            require_once 'Dal/Dynamite/User.php';
            $dalDynamiteUser = Dal_Dynamite_User::getDefaultInstance();

            $myselfUid = $this->_user->getId();
            $myselfModeAndIdInfo = $dalDynamiteUser->getUserGameModeAndId($myselfUid);

            if ($moveDirection != '') {

                $userModeAndIdInfo = $dalDynamiteUser->getUserGameModeAndId($uid);

                $friendIdArray = null;
                //if user's game mode is in friend, get user friend id array
	            if ($myselfModeAndIdInfo['game_mode'] == 1) {
	                require_once 'Bll/Dynamite/Rank.php';
	                $bllRank = new Bll_Dynamite_Rank();
	                $friendIdArray = $bllRank->getIdArray($myselfUid, 1);
	            }
	            //move to next, get next user id
	            if ($moveDirection == 'next') {

                    if ($uid == $this->_user->getId()) {
	            		$nextUid = $dalDynamiteUser->getNextUser($myselfUid, $userModeAndIdInfo['id'], $friendIdArray, $moveDirection, 'DESC');
	            	}
	            	else {
                        $nextUid = $dalDynamiteUser->getNextUser($myselfUid, $userModeAndIdInfo['id'], $friendIdArray, 'back', 'DESC');
	            	}

                    if ($nextUid == null) {
                        $nextUid = $dalDynamiteUser->getNextUser($myselfUid, $userModeAndIdInfo['id'], $friendIdArray, 'back', 'DESC');
                        if ($nextUid == null) {
                        	$nextUid = $dalDynamiteUser->getNextUser($myselfUid, $userModeAndIdInfo['id'], $friendIdArray, $moveDirection, 'DESC');
                        }
                    }
	            }//move to back
	            else if ($moveDirection == 'back') {
	                if ($uid == $this->_user->getId()) {
                        $nextUid = $dalDynamiteUser->getNextUser($myselfUid, $userModeAndIdInfo['id'], $friendIdArray, $moveDirection, 'ASC');
                    }
                    else {
                        $nextUid = $dalDynamiteUser->getNextUser($myselfUid, $userModeAndIdInfo['id'], $friendIdArray, 'next', 'ASC');
                    }

                    if ($nextUid == null) {
                        $nextUid = $dalDynamiteUser->getNextUser($myselfUid, $userModeAndIdInfo['id'], $friendIdArray, 'next', 'ASC');
                        if ($nextUid == null) {
                            $nextUid = $dalDynamiteUser->getNextUser($myselfUid, $userModeAndIdInfo['id'], $friendIdArray, $moveDirection, 'ASC');
                        }
                    }
	            }

	            $uid = $nextUid;
	        }
            else {
            	$nextUid = $uid;
            }
            //get user dynamite info
            $userDynamite = $dalDynamiteUser->getUser($uid);

            require_once 'Bll/Dynamite/Index.php';
            $bllDynamiteIndex = new Bll_Dynamite_Index();
            //get user dynamite, back current and next
            $result = $bllDynamiteIndex->getUserDynamite($this->_user->getId(), $uid);

            $response = array('userDynamite' => $userDynamite,
                              'current' => $result['current'],
                              'nextUid' => $nextUid);

            $response = Zend_Json::encode($response);

            echo $response;
        }
    }

    /**
     * set bomb
     *
     */
    public function setbombAction()
    {
        if ($this->_request->isPost()) {
            $bombUid = $this->_request->getPost('bombUid');
            $bombHitman = $this->_request->getPost('bombHitman');
            $uid = $this->_user->getId();

            require_once 'Dal/Dynamite/Item.php';
            $dalItem = Dal_Dynamite_Item::getDefaultInstance();
            //get hitman count
            $hitmanCount = $dalItem->getHitmanCount($uid);

            if ($hitmanCount == 0) {
                require_once 'Bll/Dynamite/Index.php';
                $bllIndex = new Bll_Dynamite_Index();
                //restart game
                $bllIndex->needRestartGame($uid);

                $response = array('status'=>-2);
                $response = Zend_Json::encode($response);
            }
            else {
	            //get user basic info
	            $userPowerTime = 0;
	            require_once 'Dal/Dynamite/User.php';
	            $dalUser = Dal_Dynamite_User::getDefaultInstance();
	            $userBasicInfo = $dalUser->getUserBasicInfo($uid);

	            require_once 'Bll/Cache/Dynamite.php';
	            $hitmanInfo = Bll_Cache_Dynamite::getHitmanType();

	            for ($i = 0; $i < count($hitmanInfo); $i++) {
	                if ($hitmanInfo[$i]['id'] == $userBasicInfo['hitman_type']) {
	                    $userPowerTime = $hitmanInfo[$i]['power_time'];
	                    $userPicId = $hitmanInfo[$i]['pic_id'];
	                    break;
	                }
	            }

                $now = time();

                $bombInfo = array('uid' => $uid,
                                  'bomb_uid' => $bombUid,
                                  'bomb_hitman' => $bombHitman,
                                  'set_time' => $now,
                                  'power_time' => $userPowerTime);

                require_once 'Bll/Dynamite/Index.php';
                $bllDynamiteIndex = new Bll_Dynamite_Index();
                //set bomb
                $response = $bllDynamiteIndex->setBomb($bombInfo, $userPicId);

                $response = Zend_Json::encode($response);
            }

            echo $response;
        }
    }

    /**
     * trigger bomb
     *
     */
    public function triggerbombAction()
    {
        if ($this->_request->isPost()) {
            $bombUid = $this->_request->getPost('bombUid');
            $bombHitman = $this->_request->getPost('bombHitman');
            //$userHitmanBomb = $this->_request->getPost('userHitmanBomb');
            $uid = $this->_user->getId();

            $bombInfo = array('uid' => $uid,
                              'bomb_uid' => $bombUid,
                              'bomb_hitman' => $bombHitman);

            require_once 'Bll/Dynamite/Index.php';
            $bllDynamiteIndex = new Bll_Dynamite_Index();
            //trigger bomb
            $result = $bllDynamiteIndex->triggerBomb($bombInfo);

            $response = Zend_Json::encode($result);
            echo $response;
        }
    }

    /**
     * remove bomb
     *
     */
    public function removebombAction()
    {
        if ($this->_request->isPost()) {
            $bombUid = $this->_request->getPost('bombUid');
            $bombHitman = $this->_request->getPost('bombHitman');
            //$bombType = $this->_request->getPost('bombType');
            //$removeBombInfo = $this->_request->getPost('removeBombInfo');
            //$userHitmanBomb = $this->_request->getPost('userHitmanBomb');
            $uid = $this->_user->getId();

            $bombInfo = array('uid' => $uid,
                              'bomb_uid' => $bombUid,
                              'bomb_hitman' => $bombHitman,
                              //'bomb_type' => $bombType,
                              //'removeBombInfo' => $removeBombInfo,
                              //'userHitmanBomb' => $userHitmanBomb,
                              'hurt_username' => $this->_user->getdisplayName());

            require_once 'Bll/Dynamite/Index.php';
            $bllDynamiteIndex = new Bll_Dynamite_Index();
            //remove bomb
            $result = $bllDynamiteIndex->removeBomb($bombInfo);

            $response = Zend_Json::encode($result);
            echo $response;
        }
    }

    /**
     * remove bomb
     *
     */
    public function buyhitmanAction()
    {
        if ($this->_request->isPost()) {
            $hitmanType = $this->_request->getPost('hitmanType');
            $uid = $this->_user->getId();

            require_once 'Bll/Dynamite/Shop.php';
            $bllDynamiteShop = new Bll_Dynamite_Shop();
            //buy hitman
            $result = $bllDynamiteShop->buyHitman($uid, $hitmanType);

            $response = Zend_Json::encode($result);
            echo $response;
        }
    }

    /**
     * get user bomb info
     *
     */
    public function getuserbombAction()
    {
        $uid = $this->_user->getId();

        require_once 'Dal/Dynamite/Bomb.php';
        $dalDynamiteBomb = Dal_Dynamite_Bomb::getDefaultInstance();
        //get user's had set bomb info
        $userBomb = $dalDynamiteBomb->getUserBomb($uid);
        if (!empty($userBomb)) {
            require_once 'Bll/User.php';
            Bll_User::appendPeople($userBomb, 'bomb_uid');
        }

        require_once 'Dal/Dynamite/User.php';
        $dalDynamiteUser = Dal_Dynamite_User::getDefaultInstance();
        //get my dynamite info
        $myDynamite = $dalDynamiteUser->getUser($uid);

        //$userRemoveBomb = $dalDynamiteBomb->getUserRomveBomb($uid);
        require_once 'Bll/Dynamite/Index.php';
        $bllIndex = new Bll_Dynamite_Index();
        $userRemoveBomb = $bllIndex->getUserRemoveBomb($myDynamite);

        $emptyBombCount = $this->_maxBombCount - $myDynamite['bomb_count'];

        $response = array('userBomb' => $userBomb,
                          'userRemoveBomb' => $userRemoveBomb,
                          'remainderBombCount' => ($myDynamite['remainder_bomb_count'] - count($userRemoveBomb)),
                          'allRemainderCount' => $myDynamite['remainder_bomb_count'],
                          'emptyBombCount' => $emptyBombCount,
                          'myReward' => $myDynamite['reward'],
                          'bonus' => $myDynamite['bonus']);
        $response = Zend_Json::encode($response);

        echo $response;
    }

    /**
     * use recover blood card
     * @author lp
     * @return
     */
    public function getlessbloodhitmanAction()
    {
        $uid = $this->_user->getId();

        require_once 'Dal/Dynamite/Item.php';
        $dalItem = Dal_Dynamite_Item::getDefaultInstance();
        $allHitManBlood = $dalItem->getAllHitmanBlood($uid);

        $allHitManBloodArray = array('hitman1' => $allHitManBlood['hitman_life1'],
                                     'hitman2' => $allHitManBlood['hitman_life2'],
                                     'hitman3' => $allHitManBlood['hitman_life3'],
                                     'hitman4' => $allHitManBlood['hitman_life4']);

        require_once 'Dal/Dynamite/User.php';
        $dalUser = Dal_Dynamite_User::getDefaultInstance();
        $userInfo = $dalUser->getUserBasicInfo($uid);

        require_once 'Bll/Cache/Dynamite.php';
        $hitmanInfo = Bll_Cache_Dynamite::getHitmanType();

        $userInfo['pic_id'] = $hitmanInfo[$userInfo['hitman_type'] - 1]['pic_id'];
        $maxLife = $hitmanInfo[$userInfo['hitman_type'] - 1]['max_life'];

        foreach ($allHitManBloodArray as $key => $value) {
            if ( $value == 0 || $value == $maxLife) {
                unset($allHitManBloodArray[$key]);
            }
        }

        if (!empty($allHitManBloodArray)) {

            $allHitManBloodArray['bg'] = $userInfo['pic_id'] < 10 ? '0' . $userInfo['pic_id'] : $userInfo['pic_id'];
            $allHitManBloodArray['maxLife'] = $maxLife;

            $response = array('info' => $allHitManBloodArray);
            $response = Zend_Json::encode($response);
        }
        else {
            $response = -1;
        }

        echo $response;
    }

    /**
     * use recover blood card
     * @author lp
     * @return
     */
    public function userecoverbloodcardAction()
    {
        $uid = $this->_user->getId();
        $hitmanId = $this->_request->getParam('hitman');
        $cid = $this->_request->getParam('itemId');
        $hitmanType = $this->_request->getParam('hitmanType');

        require_once 'Bll/Dynamite/Item.php';
        $bllItem = new Bll_Dynamite_Item();
        $result = $bllItem->useRecoverBloodCard($hitmanId, $uid, $cid);

        echo $result;
    }

    /**
     * recover all user's and alliance's hitman blood
     * @author lp
     * @return
     */
    public function userecoveruserandalliancecardAction()
    {
        $uid = $this->_user->getId();

        require_once 'Bll/Dynamite/Item.php';
        $bllItem = new Bll_Dynamite_Item();
        $result = $bllItem->useRecoverUserAndAllianceCard($uid);

        echo $result;
    }

    /**
     * use  confiscate card
     * @author lp
     * @return integer
     */
    public function useconfiscatebombcardAction()
    {
        $uid = $this->_user->getId();

        require_once 'Bll/Dynamite/Item.php';
        $bllItem = new Bll_Dynamite_Item();
        $result = $bllItem->useConfiscateBombCard($uid);

        echo $result;
    }

    /**
     * use  revive card
     * @author lp
     * @return integer
     */
    public function userevivecardAction()
    {
        $uid = $this->_user->getId();

        require_once 'Bll/Dynamite/Item.php';
        $bllItem = new Bll_Dynamite_Item();
        $result = $bllItem->useReviveCard($uid);

        $response = array('responseInfo' => $result);
        $response = Zend_Json::encode($response);

        echo $response;
    }



    /**
     * use  final weapon card
     * @author lp
     * @return integer
     */
    public function usefinalweaponcardAction()
    {
        $uid = $this->_user->getId();

        require_once 'Bll/Dynamite/Item.php';
        $bllItem = new Bll_Dynamite_Item();
        $result = $bllItem->useFinalWeaponCard($uid);

        $response = array('responseInfo' => $result);
        $response = Zend_Json::encode($response);

        echo $response;
    }

    /**
     * use  angry card
     * @author lp
     * @return integer
     */
    public function useangrycardAction()
    {
        $uid = $this->_user->getId();

        require_once 'Bll/Dynamite/Item.php';
        $bllItem = new Bll_Dynamite_Item();
        $result = $bllItem->useAngryCard($uid);

        $response = Zend_Json::encode($result);

        echo $response;
    }

    /**
     * revive user and user's alliance all hitman
     * @author lp
     * @return integer
     */
    public function reviveuserandalliancehitmanAction()
    {
        $uid = $this->_user->getId();

        require_once 'Bll/Dynamite/Item.php';
        $bllItem = new Bll_Dynamite_Item();
        $result = $bllItem->useAllReviveCard($uid);

        $response = array('responseInfo' => $result);
        $response = Zend_Json::encode($response);

        echo $response;
    }

    /**
     * get user remain card count
     * @author lp
     * @return integer
     */
    public function refreshcardAction()
    {
        $uid = $this->_user->getId();
        $cid = $this->_request->getParam('itemId');
        $sendHelpCard = $this->_request->getParam('sendHelpCard');

        require_once 'Dal/Dynamite/Item.php';
        $dalItem = Dal_Dynamite_Item::getDefaultInstance();

        if ($sendHelpCard == 1) {
            $dalItem->updateAngryCard($uid);
        }

        $cardCount = $dalItem->haveThisCard($uid, $cid);

        if ($cid == 5) {
        	$refuseBombTime = $dalItem->getUserRefuseBombTime($uid);
            $useTime = ( time() - $refuseBombTime )/3600;
        }

        if ($cid == 10) {
            $bonus = $dalItem->getUserBonusByUid($uid);

            $bonus < 1000 ? $canUse = 1 : $canUse = 0;
        }

        $response = array('cardCount' => $cardCount, 'useTime' => $useTime, 'canUse' => $canUse);
        $response = Zend_Json::encode($response);
        echo $response;
    }

    /**
     * user first login today, get gift
     * @author lp
     * @return integer
     */
    public function getgiftAction()
    {
        $uid = $this->_user->getId();

        require_once 'Bll/Dynamite/Index.php';
        $bllIndex = new Bll_Dynamite_Index();
        $cid = $bllIndex->isTodayFirstLogin($uid);

        require_once 'Dal/Dynamite/User.php';
        $dalUser = Dal_Dynamite_User::getDefaultInstance();
        $isNeedSelectHitman = $dalUser->isNeedSelectHitman($uid);

        $needSelectHitman = 0;
        if ($isNeedSelectHitman['hitman_type'] == 11 && $isNeedSelectHitman['bonus'] >= 1000) {
        	$needSelectHitman = 1;
        }

        $result = array('cid' => $cid, 'needSelectHitman' => $needSelectHitman);
        $result = Zend_Json::encode($result);

        echo $result;
    }

    /**
     * user bomb count=0, send 4 bombs to user
     * @author lp
     * @return integer
     */
    public function sendbombtouserAction()
    {
        $uid = $this->_user->getId();
        $sendBombCount = $this->_request->getParam('sendBomb');

        require_once 'Dal/Dynamite/User.php';
        $dalUser = Dal_Dynamite_User::getDefaultInstance();

        $result = $dalUser->updateUserMoreInfo($uid, array('bomb_count' => 4, 'remainder_bomb_count' => 4));

        echo $result;
    }

    /**
     * get user live hitman count
     * @return integer
     */
    public function gethitmanconditionAction()
    {
    	$uid = $this->_user->getId();

        require_once 'Dal/Dynamite/Item.php';
        $dalItem = Dal_Dynamite_Item::getDefaultInstance();
        $result = $dalItem->getHitmanCount($uid);

        if ($result == 0) {
            require_once 'Bll/Dynamite/Index.php';
            $bllIndex = new Bll_Dynamite_Index();
            $bllIndex->needRestartGame($uid);
        }

        echo $result;
    }

    /**
     * use in special rank page
     * @return integer
     */
    public function otherspecialtyperankAction()
    {
    	$uid = $this->_user->getId();
    	//rankname=1  reward rank,  rankname=2, gameover rank
    	$rankName = $this->_request->getPost('rankName');
    	$rankType = $this->_request->getPost('rankType');

    	require_once 'Bll/Dynamite/Rank.php';
        $bllRank = new Bll_Dynamite_Rank();
        $result = $bllRank->otherSpecialRank($uid, $rankName, $rankType);

        $response = array('rankInfo' => $result);
        $response = Zend_Json::encode($response);
        echo $response;
    }

    /**
     * if user's bomb count=0, send bomb to user
     * @return integer
     */
    public function getsendbombcountAction()
    {
    	$uid = $this->_user->getId();

    	require_once 'Bll/Dynamite/Index.php';
        $bllIndex = new Bll_Dynamite_Index();
    	$sendBombCount = $bllIndex->sendBombToUser($uid);

        echo $sendBombCount;
    }

    /**
     * show_set_bomb=1, dont't show set bomb message
     * @return integer
     */
    public function updateshowsetbombinfoflagAction()
    {
    	$uid = $this->_user->getId();

    	require_once 'Dal/Dynamite/User.php';
        $dalUser = Dal_Dynamite_User::getDefaultInstance();

        $info = array('show_set_bomb' => 1);
        //update user dynamite info
        $dalUser->updateUserBasicInfo($uid, $info);

    }



    /**
     * use card マイミクシェルター
     * @author lp
     * @return integer
     */
    public function changemodetofriendAction()
    {
    	$uid = $this->_user->getId();

        require_once 'Bll/Dynamite/Item.php';
    	$bllItem = new Bll_Dynamite_Item();

    	$result = $bllItem->changeModeToFriend($uid);

    	$response = Zend_Json::encode($result);

    	echo $response;
    }

    /**
     * use card 宣戦布告
     * @author lp
     * @return integer
     */
    public function changemodetoallAction()
    {
        $uid = $this->_user->getId();

        require_once 'Bll/Dynamite/Item.php';
        $bllItem = new Bll_Dynamite_Item();

        $result = $bllItem->changeModeToAll($uid);

        $response = Zend_Json::encode($result);

        echo $response;
    }

    /**
     * auto refresh page every 1 minite
     * @author lp
     * @return integer
     */
    public function autorefreshuserinfoAction()
    {
    	$uid = $this->_user->getId();
    	$targetUid = $this->_request->getPost('targetUid');

    	require_once 'Bll/Dynamite/Index.php';
    	$bllIndex = new Bll_Dynamite_Index();
    	$response = $bllIndex->autoRefreshUserInfo($uid, $targetUid);

        $response = Zend_Json::encode($response);

        echo $response;
    }


    /**
     * get user remain card count
     * @author lp
     * @return integer
     */
    function othertyperankAction()
    {
        $uid = $this->_user->getId();
        $rankType = $this->_request->getParam('rankType');

        require_once 'Dal/Dynamite/Item.php';
        $dalItem = Dal_Dynamite_Item::getDefaultInstance();
        $gameMode = $dalItem->getUserGameMode($uid);

        require_once 'Bll/Dynamite/Rank.php';
        $bllRank = new Bll_Dynamite_Rank();

        $result = null;
        //if user is in friend mode
        if ($gameMode == 1 && $rankType == 2) {
            $result = $bllRank->friendGameModeRank($uid);
        }
        else {
            $result = $bllRank->rank($uid, $rankType);
        }

        $response = array('rankCount' => $result['rankCount'],
                          'rankUser' => $result['rankUser'],
                          'userRankNum' => $result['userRankNum'],
                          'topRankUser' => $result['topRankUser'],
                          'inviteUser' => $result['inviteUser'],
                          'lastUserRankNum' => $result['lastUserRankNum']);

        $response = Zend_Json::encode($response);

        echo $response;
    }

    /**
     * rank move one
     * @author lp
     * @return integer
     */
    public function getnextuserAction()
    {
        $uid = $this->_user->getId();

        $rankCount = $this->_request->getParam('rankCount');
        $lastUserRankNum = $this->_request->getParam('lastUserRankNum');
        $rankPrev = $this->_request->getParam('rankPrev');
        $rankType = $this->_request->getParam('type');
        $direction = $this->_request->getParam('direction');

        require_once 'Dal/Dynamite/Item.php';
        $dalItem = Dal_Dynamite_Item::getDefaultInstance();
        $gameMode = $dalItem->getUserGameMode($uid);

        require_once 'Bll/Dynamite/Rank.php';
        $bllRank = new Bll_Dynamite_Rank();

        if ($gameMode == 1 && $rankType == 2) {
            $nextRankUser = $bllRank->getFriendModeNextRankUser($uid, $lastUserRankNum, $rankPrev, $direction);
        }
        else {
            $nextRankUser = $bllRank->getNextRankUser($uid, $rankCount, $lastUserRankNum, $rankPrev, $rankType, $direction);
        }

        $response = Zend_Json::encode($nextRankUser);
        echo $response;

    }

    /**
     * get next 10 rank users
     * @author lp
     * @return integer
     */
    public function nexttenuserAction()
    {
        $uid = $this->_user->getId();
        $type = $this->_request->getParam('type');
        $direction = $this->_request->getParam('direction');
        $currentRight = $this->_request->getParam('currentRight');

        require_once 'Bll/Dynamite/Rank.php';
        $bllRank = new Bll_Dynamite_Rank();
        $nextTenRankUser = $bllRank->getNextTenRankUser($uid, $type, $direction, $currentRight);

        $response = array('rankUser' => $nextTenRankUser['rankUser'],
                          'rankCount' => $nextTenRankUser['rankCount'],
                          'currentRight' => $nextTenRankUser['currentRight']);

        $response = Zend_Json::encode($response);
        echo $response;
    }
}


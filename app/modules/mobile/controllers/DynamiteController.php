<?php

/** @see MyLib_Zend_Controller_Action_Mobile.php */
require_once 'MyLib/Zend/Controller/Action/Mobile.php';

/**
 * Mobile Dynamite Controller(modules/mobile/controllers/DynamiteController.php)
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/09/07   zhangxin
 */
class Mobile_DynamiteController extends MyLib_Zend_Controller_Action_Mobile
{
    protected $_pageSize = 8;

    protected $_dynamiteInfo;

    private $_maxReward = 10000;
    private $_quotiety = 0.1;
    /**
     * initialize object
     * override
     * @return void
     */
    public function init()
    {
        parent::init();

        $stop = false;

        if ($stop) {
            $developers = array(
                '22677405', //communityfactory.com
                '23815088', //
                '23815089', //
                '23815090', //
                '23815091', //
                '23815092', //
                '23815093', //
                '23815094', //
                '23815095', //
                '23815096', //
                '23815097', //
                '23815098', //
                '23815099', //
                '23815100', //
                '23815101', //
                '23815102', //
                '23815103', //
                '23815104', //
                '23815105', //
                '23815106', //
                '23815107', //
            );
            $uid = $this->_user->getId();
            if (in_array($uid, $developers)) {
                $stop = false;
            }
        }

        if ($stop) {
            $this->_redirect($this->_baseUrl . '/mobile/error/stop');
            exit;
        }
    }

	/**
     * deipatch
     *
     */
    function preDispatch()
    {
        $uid = $this->_user->getId();
        $this->view->app_name = 'dynamite';
        $this->view->uid = $uid;

        $aryAction = array('index', 'help');
        if (!in_array($this->_request->getActionName(), $aryAction)) {
            require_once 'Bll/Dynamite/Index.php';
            $bllIndex = new Bll_Dynamite_Index();
            //get user dynamite info
            require_once 'Dal/Dynamite/User.php';
            $dalDynamiteUser = Dal_Dynamite_User::getDefaultInstance();
            $rowUserDynamite = $dalDynamiteUser->getUser($uid);
            require_once 'Bll/User.php';
            Bll_User::appendPerson($rowUserDynamite, 'uid');

            $allHitmanBlood = $rowUserDynamite['hitman_life1'] + $rowUserDynamite['hitman_life2'] + $rowUserDynamite['hitman_life3'] + $rowUserDynamite['hitman_life4'];
            //is all hitman dead
            if (0 == $allHitmanBlood) {
                //restart game
                $bllIndex->needRestartGame($uid);

                if ('downfall' != $this->_request->getActionName()
                    && 'agit' != $this->_request->getActionName()
                    && 'charaselect' != $this->_request->getActionName()) {

                    $this->_redirect($this->_baseUrl . '/mobile/dynamite/downfall');
                    return;
                }


            }
            else {

                require_once 'Dal/Dynamite/Item.php';
                $dalItem = Dal_Dynamite_Item::getDefaultInstance();
                $helpCardCount = $dalItem->haveThisCard($uid, 10);
                //has selected chacator
                if (0 == $rowUserDynamite['isalive']
                        && 'downfall' != $this->_request->getActionName()
                        && 'charaselect' != $this->_request->getActionName()
                        && 'itemconfirm' != $this->_request->getActionName()
                        && 'itemfinish' != $this->_request->getActionName()
                        && 'agit' != $this->_request->getActionName()) {
                    $this->_redirect($this->_baseUrl . '/mobile/dynamite/charaselect');
                    return;
                }
                //send bomb
                else if (0 == $rowUserDynamite['bomb_count'] && 'agit' != $this->_request->getActionName()) {
                    $result = $dalItem->sendBombToUser($uid, 4, 4);
                    $this->_redirect($this->_baseUrl . '/mobile/dynamite/providedynamite');
                    return;
                }
                //already send bomb
                else if (isset($_SESSION['GAIN_FOUR_BOMB_FLAG']) && 1== $_SESSION['GAIN_FOUR_BOMB_FLAG']
                        && 'dynamitebomb' != $this->_request->getActionName()
                        && 'bombgift' != $this->_request->getActionName()
                        && 'providedynamite' != $this->_request->getActionName()
                        && 'agit' != $this->_request->getActionName()) {
                    $_SESSION['GAIN_FOUR_BOMB_FLAG'] = null;
                    unset($_SESSION['GAIN_FOUR_BOMB_FLAG']);
                    $this->_redirect($this->_baseUrl . '/mobile/dynamite/providedynamite');
                    return;
                }
                //new charactor cami logic
                else if ($rowUserDynamite['bonus'] >= 1000 && 11 == $rowUserDynamite['hitman_type']
                        && 'charaselect' != $this->_request->getActionName()
                        && 'bombgift' != $this->_request->getActionName()) {
                    $this->_redirect($this->_baseUrl . '/mobile/dynamite/charaselect');
                    return;
                }
                //send help card
                else if ( ($rowUserDynamite['hitman_type'] != 11) && ($rowUserDynamite['bonus'] < 100) && ($rowUserDynamite['dead_number'] >= 3) && ($helpCardCount == 0)
                        && 'bombgift' != $this->_request->getActionName()
                        && 'agit' != $this->_request->getActionName() ) {
            		$dalItem->updateUserCard($uid, 10, 1);
            		$dalDynamiteUser->updateUserBasicInfo($uid, array('last_login_time' => time()));
                    $this->_redirect($this->_baseUrl . '/mobile/dynamite/bombgift?CF_cid=10');
                    return;
            	}
            }
            $rowUserDynamite['format_bonus'] = number_format($rowUserDynamite['bonus']);
            //#476 added
            $limitReward = round($rowUserDynamite['bonus']*$this->_quotiety) > $this->_maxReward ? $this->_maxReward : round($rowUserDynamite['bonus']*$this->_quotiety);
            $rowUserDynamite['format_reward'] = number_format($limitReward);

            //life recovey
            $bllIndex->getUserCurrentBlood($uid);
            $profileUid = $this->getParam('CF_uid');
            if (!empty($profileUid) && $profileUid != $uid) {
                $bllIndex->getUserCurrentBlood($profileUid);
            }

            require_once 'Bll/Friend.php';
            $fids = Bll_Friend::getFriends($uid);
            //friend in app count
            $rowUserDynamite['friend_count'] = $fids ? $dalDynamiteUser->getFriendCountInApp($fids) : 0;

            //item count
            require_once 'Dal/Dynamite/Item.php';
            $dalDynamiteItem = Dal_Dynamite_Item::getDefaultInstance();
            $rowUserDynamite['item_count'] = $dalDynamiteItem->getItemCountAll($uid);
            //enemy count
            require_once 'Dal/Dynamite/Enemy.php';
            $dalEnemy = Dal_Dynamite_Enemy::getDefaultInstance();
            $rowUserDynamite['enemy_count'] = $dalEnemy->getEnemyCount($uid);
            //add hitman infomation
            require_once 'Bll/Cache/Dynamite.php';
            $allHitmanInfo = Bll_Cache_Dynamite::getHitmanType();
            $rowUserDynamite = array_merge($rowUserDynamite, $allHitmanInfo[$rowUserDynamite['hitman_type'] - 1]);

            $this->_dynamiteInfo = $rowUserDynamite;
            $this->view->myDynamiteInfo = $rowUserDynamite;
            $this->view->ua = Zend_Registry::get('ua');
            $this->view->rand = time();
        }
    }

    /**
     * index action -- welcome page
     *
     */
    public function indexAction()
    {
        $uid = $this->_user->getId();
        //is already join game
        require_once 'Bll/Dynamite/User.php';
        $bllDynamiteUser = new Bll_Dynamite_User();
        $isJoined = $bllDynamiteUser->isJoined($uid);
        if ($isJoined) {
        	$this->_redirect($this->_baseUrl . '/mobile/dynamite/home');
            return;
        }

        $bllDynamiteUser->join($uid);

        $this->render();
    }

    /**
     * agit flash action
     *
     */
    public function agitAction()
    {
        $uid = $this->_user->getId();
        $profileUid = $this->getParam('CF_uid');

        // get swf
        $mixiUrl = $this->_mixiMobileUrl . $this->_APP_ID . ((Zend_Registry::get('ua') == 1) ? '/?guid=ON&amp;url=' : '/?url=');
        require_once 'Mbll/Dynamite/FlashCache.php';
        $swf = Mbll_Dynamite_FlashCache::getNewFlash($this->_dynamiteInfo, $profileUid, $mixiUrl, $this->_APP_ID);

        ob_end_clean();
        ob_start();
        header("Accept-Ranges: bytes");
        header("Cache-Control: no-cache, must-revalidate");
        header("Content-Type: application/x-shockwave-flash");

        echo $swf;
        exit(0);
    }

	/**
     * home action
     *
     */
    public function homeAction()
    {
        $uid = $this->_user->getId();
        $rowUserDynamite = $this->_dynamiteInfo;

        require_once 'Dal/Dynamite/User.php';
        $dalUser = Dal_Dynamite_User::getDefaultInstance();
        //user's last login time
        $lastLoginTime = $rowUserDynamite['last_login_time'];
        $todayTime = strtotime(date("Y-m-d"));
        if (!empty($lastLoginTime) && $lastLoginTime < $todayTime) {
            $this->_redirect($this->_baseUrl . '/mobile/dynamite/visitgift');
        	return;
        }
        //update user login time
        $dalUser->updateLastLoginTime($uid);

        require_once 'Bll/User.php';
        require_once 'Dal/Dynamite/Bomb.php';
        $dalBomb = Dal_Dynamite_Bomb::getDefaultInstance();
        $numZero = 0;
        for ($i = 1; $i <= 4; $i++) {
            //get user hitman is under attack
            $rowUserDynamite['hitman_underattack_' . $i] = $dalBomb->isUnderAttack($uid, $i) ? '1' : '0';

            //get user hitman pic info
            if ($rowUserDynamite['hitman_life' . $i] <=0) {
                $hitmanPicType = '_c';
                $numZero ++;
            }
            else if ($rowUserDynamite['hitman_life' . $i] >= ceil($rowUserDynamite['max_life']/2)) {
                $hitmanPicType = '_a';
            }
            else {
                $hitmanPicType = '_b';
            }
            $rowUserDynamite['hitman_pic_' . $i] = ($rowUserDynamite['pic_id'] < 10 ? ('0' . $rowUserDynamite['pic_id']) : $rowUserDynamite['pic_id']) . $hitmanPicType . '.gif';
        }
        $rowUserDynamite['hitman_pic_0'] = ($rowUserDynamite['pic_id'] < 10 ? ('0' . $rowUserDynamite['pic_id']) : $rowUserDynamite['pic_id']) . '_a.gif';

        //get balloon message
        require_once 'Dal/Dynamite/Enemy.php';
        $dalEnemy = Dal_Dynamite_Enemy::getDefaultInstance();
        $rowEnemy = $dalEnemy->getLastEnemy($uid);
        $strBalloon = '';//アイツ、爆破しちゃいましょう//{$nickname}組が調子にのってますよ//このままだと壊滅しちゃいﾏｽ
        if ($rowUserDynamite['hitman_life1'] == $rowUserDynamite['hitman_life2'] && $rowUserDynamite['hitman_life2'] == $rowUserDynamite['hitman_life3']
                && $rowUserDynamite['hitman_life3'] == $rowUserDynamite['hitman_life4'] && $rowUserDynamite['hitman_life4'] == $rowUserDynamite['max_life']) {
            $strBalloon = 'アイツ、爆破しちゃいましょう';
        }
        else if ($numZero >= 2) {
            $strBalloon = 'このままだと壊滅しちゃいﾏｽ';
            $rowUserDynamite['hitman_pic_0'] = ($rowUserDynamite['pic_id'] < 10 ? ('0' . $rowUserDynamite['pic_id']) : $rowUserDynamite['pic_id']) . '_b.gif';
        }
        else if (!empty($rowEnemy)) {
            Bll_User::appendPerson($rowEnemy, 'uid');
            $strBalloon = '組が調子にのってますよ';
            $rowUserDynamite['balloon_last'] = $rowEnemy['displayName'];
            $rowUserDynamite['balloon_last_id'] = $rowEnemy['uid'];
        }
        $rowUserDynamite['balloon'] = $strBalloon;

        //get alert message
        $aryAlertMsg = array();
        $lstBomb = $dalBomb->getUserBomb($uid);
        foreach ($lstBomb as $bomb) {
            if ('0' == $bomb['needWait']) {
                $aryAlertMsg[0]['title'] = '設置ﾀﾞｲﾅﾏｲﾄが爆破できます';
                $aryAlertMsg[0]['link'] = 'dynamitelist';
                break;
            }
        }

        $lstSettedBomb = $dalBomb->getUserHitmanBomb($uid);
        if (!empty($lstSettedBomb) && count($lstSettedBomb) > 0) {
            $idx = count($aryAlertMsg);
            $aryAlertMsg[$idx]['title'] = 'ﾋｯﾄﾏﾝがﾋﾟﾝﾁです!!!!';
            $aryAlertMsg[$idx]['link'] = 'agit';
        }
        if (count($aryAlertMsg) < 2 && empty($lstBomb) && empty($lstSettedBomb)) {
            $idx = count($aryAlertMsg);
            $aryAlertMsg[$idx]['title'] = 'まずはﾀﾞｲﾅﾏｲﾄを設置してみよう!';
            $aryAlertMsg[$idx]['link'] = 'agit';
        }
        $this->view->listMsg = $aryAlertMsg;

        require_once 'Bll/Friend.php';
        //get rank info
        require_once 'Dal/Dynamite/Rank.php';
        $dalRank = Dal_Dynamite_Rank::getDefaultInstance();
        if (0 == $rowUserDynamite['game_mode']) {
            $lstRank = $dalRank->getRankUser($uid, null, 0, 3, 2, 'DESC');
            $myRank = $dalRank->getUserRankNm($uid, $uid, null, 2, 'DESC');
        }
        else {
            $aryFriends = Bll_Friend::getFriends($uid);
            $lstRank = $dalRank->getRankUser($uid, $aryFriends, 0, 3, 1, 'DESC');
            $myRank = $dalRank->getUserRankNm($uid, $uid, $aryFriends, 1, 'DESC');
        }
        $isTop = false;
        foreach ($lstRank as $key=>$rank) {
            if ($rank['uid'] == $uid) {
                $isTop = true;
            }
            $lstRank[$key]['rank'] = $key + 1;
            $lstRank[$key]['format_bonus'] = number_format($lstRank[$key]['bonus']);
        }
        if (!$isTop && count($lstRank) > 2 && !empty($myRank)) {
            $lstRank[2]['uid'] = $uid;
            $lstRank[2]['bonus'] = $myRank['bonus'];
            $lstRank[2]['rank'] = $myRank['rank'];
            $lstRank[2]['format_bonus'] = number_format($myRank['bonus']);
        }
        Bll_User::appendPeople($lstRank, 'uid');
        $this->view->listRank = $lstRank;

        //get neighber uids
        require_once 'Dal/Dynamite/User.php';
        $dalDynamiteUser = Dal_Dynamite_User::getDefaultInstance();
        //all mode
        if (0 == $rowUserDynamite['game_mode']) {
            $prevId = $dalDynamiteUser->getNeighberUid($uid, 'prev');
            if (empty($prevId)) {
                $prevId = $dalDynamiteUser->getNeighberUid($uid, 'last');
            }
            $nextId = $dalDynamiteUser->getNeighberUid($uid, 'next');
            if (empty($nextId)) {
                $nextId = $dalDynamiteUser->getNeighberUid($uid, 'first');
            }
        }
        //friend mode
        else {
            $fids = Bll_Friend::getFriends($uid);
            if (empty($fids)) {
                $prevId = $uid;
                $nextId = $uid;
            }
            else {
                $prevId = $dalDynamiteUser->getNeighberFriendUid($uid, $uid, 'prev', $fids);
                if (empty($prevId)) {
                    $prevId = $dalDynamiteUser->getNeighberFriendUid($uid, $uid, 'last', $fids);
                }
                $nextId = $dalDynamiteUser->getNeighberFriendUid($uid, $uid, 'next', $fids);
                if (empty($nextId)) {
                    $nextId = $dalDynamiteUser->getNeighberFriendUid($uid, $uid, 'first', $fids);
                }
            }
        }
        $rowUserDynamite['prev_uid'] = $prevId;
        $rowUserDynamite['next_uid'] = $nextId;

        //get user set bomb count
        $setBombCount = $dalBomb->getUserBombCount($this->_user->getId());
        $this->view->setBombCount = $setBombCount;

        $this->view->myDynamiteInfo = $rowUserDynamite;
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
        if (empty($profileUid) || $uid==$profileUid) {
            $this->_redirect($this->_baseUrl . '/mobile/dynamite/home');
            return;
        }

        //get profile user dynamite info
        require_once 'Dal/Dynamite/User.php';
        $dalDynamiteUser = Dal_Dynamite_User::getDefaultInstance();
        $rowUserDynamite = $dalDynamiteUser->getUser($profileUid);
        if (empty($rowUserDynamite)) {
            $this->_redirect($this->_baseUrl . '/mobile/dynamite/home');
            return;
        }

        require_once 'Bll/Cache/Dynamite.php';
        $allHitmanInfo = Bll_Cache_Dynamite::getHitmanType();
        $rowUserDynamite = array_merge($rowUserDynamite, $allHitmanInfo[$rowUserDynamite['hitman_type'] - 1]);

        require_once 'Bll/User.php';
        Bll_User::appendPerson($rowUserDynamite, 'uid');
        $rowUserDynamite['format_bonus'] = number_format($rowUserDynamite['bonus']);
        //#476 added
        $limitReward = round($rowUserDynamite['bonus']*$this->_quotiety) > $this->_maxReward ? $this->_maxReward : round($rowUserDynamite['bonus']*$this->_quotiety);
        $rowUserDynamite['format_reward'] = number_format($limitReward);

        require_once 'Dal/Dynamite/Bomb.php';
        $dalBomb = Dal_Dynamite_Bomb::getDefaultInstance();
        for ($i = 1; $i <= 4; $i++) {
            //get user hitman is under attack
            $rowUserDynamite['hitman_underattack_' . $i] = $dalBomb->isUnderAttack($profileUid, $i) ? '1' : '0';

            //get user hitman pic info
            if ($rowUserDynamite['hitman_life' . $i] <=0) {
                $hitmanPicType = '_c';
            }
            else if ($rowUserDynamite['hitman_life' . $i] >= ceil($rowUserDynamite['max_life']/2)) {
                $hitmanPicType = '_a';
            }
            else {
                $hitmanPicType = '_b';
            }
            $rowUserDynamite['hitman_pic_' . $i] = ($rowUserDynamite['pic_id'] < 10 ? ('0' . $rowUserDynamite['pic_id']) : $rowUserDynamite['pic_id']) . $hitmanPicType . '.gif';
        }
        //$rowUserDynamite['hitman_pic_0'] = ($rowUserDynamite['pic_id'] < 10 ? ('0' . $rowUserDynamite['pic_id']) : $rowUserDynamite['pic_id']) . '_a.gif';

        //get hitman's bomb
        $lstSettedBomb = $dalBomb->getUserHitmanBomb($profileUid);
        $rowUserDynamite['setted_bomb_count_hitman_1'] = 0;
        $rowUserDynamite['setted_bomb_count_hitman_2'] = 0;
        $rowUserDynamite['setted_bomb_count_hitman_3'] = 0;
        $rowUserDynamite['setted_bomb_count_hitman_4'] = 0;
        $rowUserDynamite['has_my_bomb_hitman_1'] = '0';
        $rowUserDynamite['has_my_bomb_hitman_2'] = '0';
        $rowUserDynamite['has_my_bomb_hitman_3'] = '0';
        $rowUserDynamite['has_my_bomb_hitman_4'] = '0';
        foreach ($lstSettedBomb as $bombData) {
            //setted bomb count
            if (1 == $bombData['bomb_hitman']) {
                $rowUserDynamite['setted_bomb_count_hitman_1'] += 1;
            }
            if (2 == $bombData['bomb_hitman']) {
                $rowUserDynamite['setted_bomb_count_hitman_2'] += 1;
            }
            if (3 == $bombData['bomb_hitman']) {
                $rowUserDynamite['setted_bomb_count_hitman_3'] += 1;
            }
            if (4 == $bombData['bomb_hitman']) {
                $rowUserDynamite['setted_bomb_count_hitman_4'] += 1;
            }
            //has my setted bomb
            for ($i = 1; $i <= 4; $i++) {
                if ($bombData['uid'] == $uid && $bombData['bomb_hitman'] == $i) {
                    $rowUserDynamite['has_my_bomb_hitman_' . $i] = '1';
                    break;
                }
            }
        }

        $canSetBomb = '1';
        //check game mode
        require_once 'Bll/Friend.php';
        $isFriend = Bll_Friend::isFriend($uid, $profileUid);

        if ( $this->_dynamiteInfo['game_mode'] == 0 && $rowUserDynamite['game_mode'] == 1 && !$isFriend ) {
            $canSetBomb = '0';
        }
        if ( $this->_dynamiteInfo['game_mode'] == 1 && $rowUserDynamite['game_mode'] == 0 && !$isFriend ) {
            $canSetBomb = '0';
        }
        if ( $this->_dynamiteInfo['game_mode'] == 1 && $rowUserDynamite['game_mode'] == 1 && !$isFriend ) {
            $canSetBomb = '0';
        }
        $this->view->canSetBomb = $canSetBomb;

        //get neighber uids
        require_once 'Dal/Dynamite/User.php';
        $dalDynamiteUser = Dal_Dynamite_User::getDefaultInstance();
        //all mode
        if (0 == $this->_dynamiteInfo['game_mode']) {
            $prevId = $dalDynamiteUser->getNeighberUid($profileUid, 'prev');
            if (empty($prevId)) {
                $prevId = $dalDynamiteUser->getNeighberUid($profileUid, 'last');
            }
            $nextId = $dalDynamiteUser->getNeighberUid($profileUid, 'next');
            if (empty($nextId)) {
                $nextId = $dalDynamiteUser->getNeighberUid($profileUid, 'first');
            }
        }
        //friend mode
        else {
            require_once 'Bll/Friend.php';
            $fids = Bll_Friend::getFriends($uid);

            $prevId = $dalDynamiteUser->getNeighberFriendUid($uid, $profileUid, 'prev', $fids);
            if (empty($prevId)) {
                $prevId = $dalDynamiteUser->getNeighberFriendUid($uid, $profileUid, 'last', $fids);
            }
            $nextId = $dalDynamiteUser->getNeighberFriendUid($uid, $profileUid, 'next', $fids);
            if (empty($nextId)) {
                $nextId = $dalDynamiteUser->getNeighberFriendUid($uid, $profileUid, 'first', $fids);
            }
        }
        $rowUserDynamite['prev_uid'] = $prevId;
        $rowUserDynamite['next_uid'] = $nextId;

        $this->view->profileDynamiteInfo = $rowUserDynamite;
        $this->render();
    }

	/**
     * dynamite set action
     *
     */
    public function dynamitesetAction()
    {
        $uid = $this->_user->getId();
        $profileUid = $this->getParam('CF_uid');
        $hitman = $this->getParam('CF_hitmanid');
        $isFlash = $this->getParam('CF_flashlite');
        if (!empty($isFlash) && '1' == $isFlash) {
            $hitman = $this->getParam('area');
            $this->view->isFromFlash = '1';
        }

        if (empty($profileUid) || $uid==$profileUid) {
            $this->_redirect($this->_baseUrl . '/mobile/dynamite/home');
            return;
        }

        //profile info
        require_once 'Dal/Dynamite/User.php';
        $dalDynamiteUser = Dal_Dynamite_User::getDefaultInstance();
        $rowProfileDynamite = $dalDynamiteUser->getUser($profileUid);
        if (empty($rowProfileDynamite)) {
            $this->_redirect($this->_baseUrl . '/mobile/dynamite/home');
            return;
        }

        $actorBasicInfo = $dalDynamiteUser->getUserBasicInfo($uid);
        require_once 'Bll/Cache/Dynamite.php';
        $hitmanInfo = Bll_Cache_Dynamite::getHitmanType();

        $rowProfileDynamite['pic_id'] = $hitmanInfo[$rowProfileDynamite['hitman_type']-1]['pic_id'];
        //actor's bomb info
        for ($i = 0; $i < count($hitmanInfo); $i++) {
            if ($hitmanInfo[$i]['id'] == $actorBasicInfo['hitman_type']) {
                $userPowerTime = $hitmanInfo[$i]['power_time'];
                $userPicId = $hitmanInfo[$i]['pic_id'];
                break;
            }
        }

        $now = time();
        $bombInfo = array('uid' => $uid,
                          'bomb_uid' => $profileUid,
                          'bomb_hitman' => $hitman,
                          'set_time' => $now,
                          'power_time' => $userPowerTime);

        require_once 'Bll/Dynamite/Index.php';
        $bllDynamiteIndex = new Bll_Dynamite_Index();
        //set bomb
        $result = $bllDynamiteIndex->setBomb($bombInfo, $userPicId);

        $this->view->mode = '1';
        //set hoihoi ed
        if (2 == $result['status']) {
            $this->view->mode = '2';
        }
        //set failed clash
        else if ($result['status'] < 0) {
            $this->view->mode = '3';
        }

        //set done
        require_once 'Bll/User.php';
        $rowProfileDynamite['profile_uid'] = $profileUid;
        Bll_User::appendPerson($rowProfileDynamite, 'profile_uid');
        $this->view->profileInfo = $rowProfileDynamite;
        $strStatusPrev = 'normal';
        if ($rowProfileDynamite['hitman_count']<=2) {
            $strStatusPrev = 'damage';
        }
        $this->view->picName = ($rowProfileDynamite['pic_id'] < 10 ? ('0' . $rowProfileDynamite['pic_id']) : $rowProfileDynamite['pic_id']) . '_' . $strStatusPrev . '.gif';
        $this->view->waitTime = $result['power_time'];
        $this->render();
    }

    /**
     * dynamite remove action
     *
     */
    public function dynamiteremoveAction()
    {
        $uid = $this->_user->getId();
        $hitman = $this->getParam('CF_hitmanid');
        $isFlash = $this->getParam('CF_flashlite');
        if (!empty($isFlash) && '1' == $isFlash) {
            $hitman = $this->getParam('area');
            $this->view->isFromFlash = '1';
        }

        $bombInfo = array('uid' => $uid,
                          'bomb_uid' => $uid,
                          'bomb_hitman' => $hitman);

        require_once 'Bll/Dynamite/Index.php';
        $bllDynamiteIndex = new Bll_Dynamite_Index();
        //remove bomb
        $result = $bllDynamiteIndex->removeBomb($bombInfo);

        $this->view->mode = '1';
        //remove failed clash
        if ($result['status'] < 0) {
            $this->view->mode = '3';
        }

        //remove bomb info
        $this->view->lstRemoveBomb = $result['removeBombInfo'];
        $this->view->cntRemoveBomb = $result['removeBombCount'];
        $this->render();
    }

	/**
     * dynamite bomb action
     *
     */
    public function dynamitebombAction()
    {
        $uid = $this->_user->getId();
        $profileUid = $this->getParam('CF_uid');
        $hitman = $this->getParam('CF_hitmanid');
        $isFlash = $this->getParam('CF_flashlite');
        if (!empty($isFlash) && '1' == $isFlash) {
            $hitman = $this->getParam('area');
            $this->view->isFromFlash = '1';
        }

        if (empty($profileUid) || $uid==$profileUid) {
            $this->_redirect($this->_baseUrl . '/mobile/dynamite/home');
            return;
        }

        //profile info
        require_once 'Dal/Dynamite/User.php';
        $dalDynamiteUser = Dal_Dynamite_User::getDefaultInstance();
        $rowProfileDynamite = $dalDynamiteUser->getUser($profileUid);
        if (empty($rowProfileDynamite)) {
            $this->_redirect($this->_baseUrl . '/mobile/dynamite/home');
            return;
        }

        require_once 'Bll/Cache/Dynamite.php';
        $hitmanInfo = Bll_Cache_Dynamite::getHitmanType();
        $rowProfileDynamite['pic_id'] = $hitmanInfo[$rowProfileDynamite['hitman_type']-1]['pic_id'];
        $rowProfileDynamite['max_life'] = $hitmanInfo[$rowProfileDynamite['hitman_type']-1]['max_life'];
        //get hitman pic
        if ($rowProfileDynamite['hitman_life' . $hitman] >= ceil($rowProfileDynamite['max_life']/2)) {
            $hitmanPicType = 'normal';
        }
        else {
            $hitmanPicType = 'damage';
        }
        $rowProfileDynamite['bomb_hitman_pic'] = ($rowProfileDynamite['pic_id'] < 10 ? ('0' . $rowProfileDynamite['pic_id']) : $rowProfileDynamite['pic_id']) . '_' .$hitmanPicType;

        require_once 'Bll/User.php';
        $rowProfileDynamite['profile_uid'] = $profileUid;
        Bll_User::appendPerson($rowProfileDynamite, 'profile_uid');

        //my rank info
        require_once 'Dal/Dynamite/Rank.php';
        $dalRank = Dal_Dynamite_Rank::getDefaultInstance();
        $myRankAll = $dalRank->getUserRankNm($uid, $uid, null, 2, 'DESC');
        require_once 'Bll/Friend.php';
        $fids = Bll_Friend::getFriends($uid);
        $myRankFriend = $dalRank->getUserRankNm($uid, $uid, $fids, 1, 'DESC');
        $rowMyDynamite['prev_rank_all'] = $myRankAll['rank'];
        $rowMyDynamite['prev_rank_friend'] = $myRankFriend['rank'];


        $bombInfo = array('uid' => $uid,
                          'bomb_uid' => $profileUid,
                          'bomb_hitman' => $hitman);

        require_once 'Bll/Dynamite/Index.php';
        $bllDynamiteIndex = new Bll_Dynamite_Index();
        //trigger bomb
        $result = $bllDynamiteIndex->triggerBomb($bombInfo);

        $this->view->mode = '1';
        //remove failed clash
        if ($result['status'] < 0) {
            $this->view->mode = '3';
        }
        //not dead
        else if (1 == $result['status']) {
            $this->view->mode = '1';
            $rowMyDynamite['damage_power'] = $result['bombPower'];
            $rowMyDynamite['remain_health'] = $result['hitmanRemainderSelf'];
            if ($result['hitmanRemainderSelf'] >= ceil($rowProfileDynamite['max_life']/2)) {
                $rowProfileDynamite['bomb_hitman_pic'] .= '_normal.gif';
            }
            else {
                $rowProfileDynamite['bomb_hitman_pic'] .= '_damage.gif';
            }
            //has other can bomb
            require_once 'Dal/Dynamite/Bomb.php';
            $dalBomb = Dal_Dynamite_Bomb::getDefaultInstance();
            $lstBomb = $dalBomb->getUserBomb($uid);
            foreach ($lstBomb as $bomb) {
                if ('0' == $bomb['needWait']) {
                    $rowMyDynamite['has_other_bomb_to_bomb'] = "1";
                    break;
                }
            }

            //bomb sended
            $rowMyDynamite['is_gain_bomb'] = isset($result['presentBomb']) ? $result['presentBomb'] : 0;
        }
        //dead
        else if (2 == $result['status']) {
            $this->view->mode = '2';

            require_once 'Dal/Dynamite/Item.php';
            $dalItem = Dal_Dynamite_Item::getDefaultInstance();
            $myGameMode = $dalItem->getUserGameMode($uid);
            if ($myGameMode == 0) {
                $myRankAll = $dalRank->getUserRankNm($uid, $uid, null, 2, 'DESC');
            }
            else {
                $myRankAll = $dalRank->getUserRankNmAfterTriggerBomb($uid);
            }
            require_once 'Bll/Friend.php';
            $fids = Bll_Friend::getFriends($uid);
            $myRankFriend = $dalRank->getUserRankNm($uid, $uid, $fids, 1, 'DESC');
            $rowMyDynamite['rank_all'] = $myRankAll['rank'];
            $rowMyDynamite['rank_friend'] = $myRankFriend['rank'];
            $rowMyDynamite['gain_bonus'] = $result['getBonus'];
            $rowMyDynamite['format_gain_bonus'] = number_format($result['getBonus']);
            $rowMyDynamite['format_bonus'] = number_format($result['getBonus'] + $this->_dynamiteInfo['bonus']);
            $rowMyDynamite['gain_gift'] = $result['sendCid'];
            //bomb sended
            $rowMyDynamite['is_gain_bomb'] = isset($result['presentBomb']) ? $result['presentBomb'] : 0;
            $rowProfileDynamite['bomb_hitman_pic'] .= '_death.gif';
        }

        //gain bomb session
        if (!empty($rowMyDynamite['is_gain_bomb']) && 1 == $rowMyDynamite['is_gain_bomb']) {
            unset($_SESSION['GAIN_FOUR_BOMB_FLAG']);
            $_SESSION['GAIN_FOUR_BOMB_FLAG'] = 1;
        }

        $this->view->profileInfo = $rowProfileDynamite;
        $this->view->bombInfo = $rowMyDynamite;
        $this->render();
    }

	/**
     * bomb gift action
     *
     */
    public function bombgiftAction()
    {
        $uid = $this->_user->getId();
        $itemId = $this->getParam('CF_cid');
        if (empty($itemId)) {
            $this->_redirect($this->_baseUrl . '/mobile/dynamite/home');
            return;
        }

        $this->view->canUse = '0';
        if ($itemId < 11) {
            require_once 'Dal/Dynamite/Item.php';
            $dalItem = Dal_Dynamite_Item::getDefaultInstance();
            $rowItem = $dalItem->getItemInfo($itemId);
            require_once 'Bll/Dynamite/Item.php';
            $bllItem = new Bll_Dynamite_Item();
            $this->view->canUse = ($bllItem->checkCardCanUse($uid, $itemId) == 1) ? '1' : '0';
            if (empty($rowItem)) {
                $this->_redirect($this->_baseUrl . '/mobile/dynamite/home');
                return;
            }
        }
        else if (50 == $itemId) {
            $rowItem['cid'] = $itemId;
            $rowItem['name'] = 'ボーナス+500$';
            $rowItem['introduce'] = '500ドルの臨時ボーナスをゲットしました。';
            $rowItem['ismoney'] = '500';
        }
        else if (51 == $itemId) {
            $rowItem['cid'] = $itemId;
            $rowItem['name'] = 'ボーナス+1000$';
            $rowItem['introduce'] = '1000ドルの臨時ボーナスをゲットしました。';
            $rowItem['ismoney'] = '1000';
        }
        else if (52 == $itemId) {
            $rowItem['cid'] = $itemId;
            $rowItem['name'] = 'ボーナス+3000$';
            $rowItem['introduce'] = '3000ドルの臨時ボーナスをゲットしました。';
            $rowItem['ismoney'] = '3000';
        }
        else if (60 == $itemId) {
            $rowItem['cid'] = $itemId;
            $rowItem['name'] = 'ダイナマイト詰め合わせ（梅）';
            $rowItem['introduce'] = 'ダイナマイト2個の詰め合わせセットです。';
        }
        else if (61 == $itemId) {
            $rowItem['cid'] = $itemId;
            $rowItem['name'] = 'ダイナマイト詰め合わせ（竹）';
            $rowItem['introduce'] = 'ダイナマイト5個の詰め合わせセットです。';
        }
        else if (62 == $itemId) {
            $rowItem['cid'] = $itemId;
            $rowItem['name'] = 'ダイナマイト詰め合わせ（松）';
            $rowItem['introduce'] = 'ダイナマイト10個の詰め合わせセットです。';
        }

        //has other can bomb
        require_once 'Dal/Dynamite/Bomb.php';
        $dalBomb = Dal_Dynamite_Bomb::getDefaultInstance();
        $lstBomb = $dalBomb->getUserBomb($uid);
        foreach ($lstBomb as $bomb) {
            if ('0' == $bomb['needWait']) {
                $this->view->has_other_bomb_to_bomb = "1";
                break;
            }
        }

        $this->view->itemInfo = $rowItem;
        $this->render();
    }

	/**
     * daily visit gift action
     *
     */
    public function visitgiftAction()
    {
        $uid = $this->_user->getId();
        $randGift = $this->getParam('CF_rand');
        $this->view->mode = '1';

        //get daily gift
        if (!empty($randGift)) {
            $rowUserDynamite = $this->_dynamiteInfo;
            //is today first login
            require_once 'Dal/Dynamite/User.php';
            $dalUser = Dal_Dynamite_User::getDefaultInstance();
            $lastLoginTime = $rowUserDynamite['last_login_time'];
            $todayTime = strtotime(date("Y-m-d"));
            if (empty($lastLoginTime) || $lastLoginTime >= $todayTime) {
                $this->_redirect($this->_baseUrl . '/mobile/dynamite/home');
            	return;
            }
            //send gift
            require_once 'Bll/Dynamite/Index.php';
            $bllGift = new Bll_Dynamite_Index();
            $itemId = $bllGift->isTodayFirstLogin($uid);
            //update user login time
            $dalUser->updateLastLoginTime($uid);

            $this->view->canUse = '0';
            if ($itemId < 11) {
                require_once 'Dal/Dynamite/Item.php';
                $dalItem = Dal_Dynamite_Item::getDefaultInstance();
                $rowItem = $dalItem->getItemInfo($itemId);
                require_once 'Bll/Dynamite/Item.php';
                $bllItem = new Bll_Dynamite_Item();
                $this->view->canUse = ($bllItem->checkCardCanUse($uid, $itemId) == 1) ? '1' : '0';
                if (empty($rowItem)) {
                    $this->_redirect($this->_baseUrl . '/mobile/dynamite/home');
                    return;
                }
            }
            else if (50 == $itemId) {
                $rowItem['cid'] = $itemId;
                $rowItem['name'] = 'ボーナス+500$';
                $rowItem['introduce'] = '500ドルの臨時ボーナスをゲットしました。';
                $rowItem['ismoney'] = '500';
            }
            else if (51 == $itemId) {
                $rowItem['cid'] = $itemId;
                $rowItem['name'] = 'ボーナス+1000$';
                $rowItem['introduce'] = '1000ドルの臨時ボーナスをゲットしました。';
                $rowItem['ismoney'] = '1000';
            }
            else if (52 == $itemId) {
                $rowItem['cid'] = $itemId;
                $rowItem['name'] = 'ボーナス+3000$';
                $rowItem['introduce'] = '3000ドルの臨時ボーナスをゲットしました。';
                $rowItem['ismoney'] = '3000';
            }
            else if (60 == $itemId) {
                $rowItem['cid'] = $itemId;
                $rowItem['name'] = 'ダイナマイト詰め合わせ（梅）';
                $rowItem['introduce'] = 'ダイナマイト2個の詰め合わせセットです。';
            }
            else if (61 == $itemId) {
                $rowItem['cid'] = $itemId;
                $rowItem['name'] = 'ダイナマイト詰め合わせ（竹）';
                $rowItem['introduce'] = 'ダイナマイト5個の詰め合わせセットです。';
            }
            else if (62 == $itemId) {
                $rowItem['cid'] = $itemId;
                $rowItem['name'] = 'ダイナマイト詰め合わせ（松）';
                $rowItem['introduce'] = 'ダイナマイト10個の詰め合わせセットです。';
            }

            $this->view->itemInfo = $rowItem;
            $this->view->mode = '2';
        }

        $this->render();
    }

	/**
     * provide dynamite action
     *
     */
    public function providedynamiteAction()
    {
        $uid = $this->_user->getId();
        $this->render();
    }

	/**
     * game end action
     *
     */
    public function downfallAction()
    {

        $uid = $this->_user->getId();

        $rowUserDynamite = $this->_dynamiteInfo;

        $strStatusPrev = 'normal';
        if ($rowUserDynamite['hitman_count']<=2) {
            $strStatusPrev = 'damage';
        }

        //trac510 add begin
        $this->view->guideCard = 0;
        //check user game mode
        if (0 == $rowUserDynamite['game_mode']) {
            require_once 'Bll/Friend.php';
            $fids = Bll_Friend::getFriends($uid);
            require_once 'Dal/Dynamite/Item.php';
            $dalItem = Dal_Dynamite_Item::getDefaultInstance();
            $rowCard = $dalItem->getUserItemInfo($uid, 8);
            if (count($fids) > 5 && 2 == $rowCard['count']) {
                $this->view->guideCard = 1;
            }
        }
        //trac510 add end
        $b = 143;
        $this->view->picName = ($rowUserDynamite['pic_id'] < 10 ? ('0' . $rowUserDynamite['pic_id']) : $rowUserDynamite['pic_id']) . '_' . $strStatusPrev . '_death.gif';
        $this->render();
    }



    /******************************************************************************************************/

    /**
     * attack list action
     *
     */
    public function attacklistAction()
    {
        $uid = $this->getParam('CF_uid');
        $hitmanId = $this->getParam('CF_hitmanid');

        if ( $hitmanId < 1 || $hitmanId > 4 ) {
            $hitmanId = '1';
        }

        //get dynamite info
        require_once 'Dal/Dynamite/User.php';
        $dalDynamiteUser = Dal_Dynamite_User::getDefaultInstance();
        $rowUserDynamite = $dalDynamiteUser->getUser($uid);

        if ( $rowUserDynamite ) {
            require_once 'Bll/User.php';
            Bll_User::appendPerson($rowUserDynamite, 'uid');
        }
        else {
            $uid = $this->_user->getId();
            $rowUserDynamite = $this->_dynamiteInfo;
        }

        if ( $rowUserDynamite['hitman_life'.$hitmanId] < 1 ) {
            $this->_redirect($this->_baseUrl . "/mobile/dynamite/profile?CF_uid=" . $uid);
        }

        require_once 'Bll/Cache/Dynamite.php';
        $hitmanInfo = Bll_Cache_Dynamite::getHitmanType();
        $rowUserDynamite['pic_id'] = $hitmanInfo[$rowUserDynamite['hitman_type']-1]['pic_id'];
        $rowUserDynamite['max_life'] = $hitmanInfo[$rowUserDynamite['hitman_type']-1]['max_life'];

        //get hitman garment type
        $garmentType = $rowUserDynamite['hitman_life'.$hitmanId] < $rowUserDynamite['max_life']/2 ? 'b' : 'a';
        //get hitman pic type
        $picType = $rowUserDynamite['pic_id'] < 10 ? '0'.$rowUserDynamite['pic_id'] : $rowUserDynamite['pic_id'];
        //get hitman id type
        switch ($hitmanId) {
            case 1 :
                $hitmanType = 'A';
                break;
            case 2 :
                $hitmanType = 'B';
                break;
            case 3 :
                $hitmanType = 'C';
                break;
            case 4 :
                $hitmanType = 'D';
                break;
        }

        $this->view->garmentType = $garmentType;
        $this->view->picType = $picType;
        $this->view->hitmanId = $hitmanId;
        $this->view->hitmanType = $hitmanType;
        $this->view->hitmanLife = $rowUserDynamite['hitman_life'.$hitmanId];

        require_once 'Dal/Dynamite/Bomb.php';
        $dalDynamiteBomb = Dal_Dynamite_Bomb::getDefaultInstance();
        //get hitman bomb info
        $hitmanBomb = $dalDynamiteBomb->getBombUserHitmanBomb($uid, $hitmanId);
        Bll_User::appendPeople($hitmanBomb, 'uid');
        $this->view->hitmanBomb = $hitmanBomb;
        $this->view->userId = $this->_user->getId();

        //check user is me or not
        if ( $uid == $this->_user->getId() ) {
            //check can remove bomb
            for ( $i = 0, $iCount = count($hitmanBomb); $i < $iCount; $i++ ) {
                if ( $hitmanBomb[$i]['bomb_hitman'] == $hitmanId && $hitmanBomb[$i]['bomb_power'] > 0 ) {
                    $canRemoveBomb = '1';
                    break;
                }
            }
            $this->view->canRemoveBomb = $canRemoveBomb;

            $userType = '1';
        }
        else {
            $myUserDynamite = $this->_dynamiteInfo;;
            //check had set bomb, can bomb
            for ( $j = 0, $jCount = count($hitmanBomb); $j < $jCount; $j++ ) {
                if ( $hitmanBomb[$j]['uid'] == $this->_user->getId() ) {
                    $hadSetBomb = '1';
                    if ( $hitmanBomb[$j]['bomb_power'] > 0 && $hitmanBomb[$j]['needWait'] != 1 ) {
                        $canBomb = '1';
                    }
                    break;
                }
            }
            //check can set bomb
            if ( $hadSetBomb != 1 && count($hitmanBomb) < 4 && $myUserDynamite['remainder_bomb_count'] > 0 ) {
                $canSetBomb = '1';
            }

            $this->view->hadSetBomb = $hadSetBomb;
            $this->view->canBomb = $canBomb;
            $this->view->canSetBomb = $canSetBomb;

            $userType = '0';
        }
        $this->view->userType = $userType;

        //get user set bomb count
        $setBombCount = $dalDynamiteBomb->getUserBombCount($this->_user->getId());
        $this->view->setBombCount = $setBombCount;

        $this->view->rowUserDynamite = $rowUserDynamite;

        $this->render();
    }

    /**
     * dynamitelist action
     *
     */
    public function dynamitelistAction()
    {
        $pageIndex = $this->getParam('CF_page', 1);
        $pageSize = 5;

        require_once 'Dal/Dynamite/Bomb.php';
        $dalDynamiteBomb = Dal_Dynamite_Bomb::getDefaultInstance();
        //get user bomb info
        $userBomb = $dalDynamiteBomb->getUserBomb($this->_user->getId(), $pageIndex, $pageSize);
        require_once 'Bll/User.php';
        Bll_User::appendPeople($userBomb, 'bomb_uid');

        $this->view->userBomb = $userBomb;

        //get user set bomb count
        $setBombCount = $dalDynamiteBomb->getUserBombCount($this->_user->getId());
        $this->view->setBombCount = $setBombCount;

        //get pager info
        $this->view->pager = array('count' => $setBombCount,
                                   'pageIndex' => $pageIndex,
                                   'requestUrl' => 'mobile/dynamite/dynamitelist',
                                   'pageSize' => $pageSize,
                                   'maxPager' => ceil($setBombCount / $pageSize)
                                   );

        $this->render();
    }

    /**
     * mymixilist action
     *
     */
    public function mymixilistAction()
    {
        $pageIndex = $this->getParam('CF_page', 1);
        $pageSize = 5;

        $fids = Bll_Friend::getFriendIds($this->_user->getId());
        $fids = explode(',', $fids);

        require_once 'Dal/Dynamite/User.php';
        $dalDynamiteUser = Dal_Dynamite_User::getDefaultInstance();
        //get my mixi user array
        $myMixiUser = $dalDynamiteUser->getUidInFids($fids, 0, $pageIndex, $pageSize);
        $myMixiUser ? Bll_User::appendPeople($myMixiUser, 'uid') : $myMixiUser;

        //get my mixi user count
        $myMixiUserCount = $dalDynamiteUser->getCountInFids($fids);

        $this->view->myMixiUser = $myMixiUser;
        $this->view->myMixiUserCount = $myMixiUserCount;

        //get start number and end number
        $start = ($pageIndex-1)*$pageSize;
        $this->view->startNm = $start + 1;
        $this->view->endNm = ($start + $pageSize) > $myMixiUserCount ? $myMixiUserCount : ($start + $pageSize);

        //get pager info
        $this->view->pager = array('count' => $myMixiUserCount,
                                   'pageIndex' => $pageIndex,
                                   'requestUrl' => 'mobile/dynamite/mymixilist',
                                   'pageSize' => $pageSize,
                                   'maxPager' => ceil($myMixiUserCount / $pageSize)
                                   );
        $this->render();
    }

    /**
     * enemylist action
     *
     */
    public function enemylistAction()
    {
        $pageIndex = $this->getParam('CF_page', 1);
        $pageSize = 5;

        require_once 'Dal/Dynamite/User.php';
        $dalDynamiteUser = Dal_Dynamite_User::getDefaultInstance();
        //get enemy list
        $enemyList = $dalDynamiteUser->getEnemyList($this->_user->getId(), $pageIndex, $pageSize);
        $enemyList ? Bll_User::appendPeople($enemyList, 'uid') : $enemyList;

        //get enemy count
        $enemyCount = $dalDynamiteUser->getEnemyCount($this->_user->getId());

        $this->view->enemyList = $enemyList;
        $this->view->enemyCount = $enemyCount;

        //get start number and end number
        $start = ($pageIndex-1)*$pageSize;
        $this->view->startNm = $start + 1;
        $this->view->endNm = ($start + $pageSize) > $enemyCount ? $enemyCount : ($start + $pageSize);

        //get pager info
        $this->view->pager = array('count' => $enemyCount,
                                   'pageIndex' => $pageIndex,
                                   'requestUrl' => 'mobile/dynamite/enemylist',
                                   'pageSize' => $pageSize,
                                   'maxPager' => ceil($enemyCount / $pageSize)
                                   );

        $this->render();
    }

    /**
     * itemlist action
     *
     */
    public function itemlistAction()
    {
        $uid = $this->_user->getId();

        require_once 'Bll/Dynamite/Item.php';
        $bllItem = new Bll_Dynamite_Item();
        //get user item info
        $itemList = $bllItem->getItemList($uid);
        $this->view->itemList = $itemList;

        require_once 'Dal/Dynamite/Item.php';
        $dalItem = Dal_Dynamite_Item::getDefaultInstance();
        //get user item all count
        $this->view->itemAllCount = $dalItem->getItemCountAll($uid);

        //check card can use
        $canUseCardArray = array();
        for ( $i = 0; $i < 11; $i++ ) {
            $canUseCardArray[$i] = $bllItem->checkCardCanUse($uid, $itemList[$i]['cid']);
        }
        $this->view->canUseCardArray = $canUseCardArray;

        $this->render();
    }

    /**
     * item confirm action
     *
     */
    public function itemconfirmAction()
    {
        $cid = $this->getParam('CF_cid', 1);

        require_once 'Dal/Dynamite/Item.php';
        $dalDynamiteItem = Dal_Dynamite_Item::getDefaultInstance();
        //get card info
        $cardInfo = $dalDynamiteItem->getUserItemInfo($this->_user->getId(), $cid);
        $this->view->cardInfo = $cardInfo;

        if ( !$cardInfo || $cardInfo['count'] < 1 ) {
            $this->_redirect($this->_baseUrl . '/mobile/dynamite/itemlist');
        }

        require_once 'Bll/Dynamite/Item.php';
        $bllItem = new Bll_Dynamite_Item();
        $returl = $bllItem->checkCardCanUse($this->_user->getId(), $cid);
        if ( $returl != 1 ) {
            $this->_redirect($this->_baseUrl . '/mobile/dynamite/itemlist');
        }

        if ( $cid == 1 || $cid == 2 ) {
            $cardType = 1;

            //get user info
            $userInfo = $this->_dynamiteInfo;

            require_once 'Bll/Cache/Dynamite.php';
            $hitmanInfo = Bll_Cache_Dynamite::getHitmanType();
            $userInfo['max_life'] = $hitmanInfo[$userInfo['hitman_type']-1]['max_life'];
            $userInfo['pic_id'] = $hitmanInfo[$userInfo['hitman_type']-1]['pic_id'];
            //get hitman info
            $hitmanInfo = array();
            for ( $i = 0; $i < 4; $i++ ) {
                $hitmanInfo[$i]['hitmanId'] = $i + 1;
                $hitmanInfo[$i]['life'] = $userInfo['hitman_life'.($i+1)];
                $hitmanInfo[$i]['garmentType'] = $userInfo['hitman_life'.($i+1)] < $userInfo['max_life']/2 ? 'b' : 'a';
                $hitmanInfo[$i]['garmentType'] = $userInfo['hitman_life'.($i+1)] > 0 ? $hitmanInfo[$i]['garmentType'] : 'c';
            }

            $this->view->hitmanBg = $userInfo['pic_id'] < 10 ? '0' . $userInfo['pic_id'] : $userInfo['pic_id'];
            $this->view->hitmanInfo = $hitmanInfo;
        }

        $this->view->cardType = $cardType;

        $this->render();
    }

    /**
     * item confirm action
     *
     */
    public function itemfinishAction()
    {
        $cid = $this->getParam('CF_cid', 1);
        $hitmanId = $this->getParam('CF_hitmanid', 1);
        $uid = $this->_user->getId();

        require_once 'Dal/Dynamite/Item.php';
        $dalDynamiteItem = Dal_Dynamite_Item::getDefaultInstance();
        //get card info
        $cardInfo = $dalDynamiteItem->getUserItemInfo($uid, $cid);
        $this->view->cardInfo = $cardInfo;

        if ( !$cardInfo || $cardInfo['count'] < 1 ) {
            $this->_redirect($this->_baseUrl . '/mobile/dynamite/itemlist');
        }

        require_once 'Bll/Dynamite/Item.php';
        $bllItem = new Bll_Dynamite_Item();

        switch ($cid) {
            case 1 :
                $useCardResult = $bllItem->useRecoverBloodCard($hitmanId, $uid, $cid);
                break;
            case 2 :
                $useCardResult = $bllItem->useRecoverBloodCard($hitmanId, $uid, $cid);
                break;
            case 3 :
                $useCardResult = $bllItem->useRecoverUserAndAllianceCard($uid);
                break;
            case 4 :
                $result = $bllItem->useAllReviveCard($uid);
                $useCardResult = $result['result'];
                break;
            case 5 :
                $useCardResult = $bllItem->useConfiscateBombCard($uid);
                break;
            case 6 :
                $result = $bllItem->useReviveCard($uid);
                $useCardResult = $result['result'];
                break;
            case 7 :
                $result = $bllItem->useFinalWeaponCard($uid);
                $useCardResult = $result['result'];
                break;
            case 8 :
                $result = $bllItem->changeModeToFriend($uid);
                $useCardResult = $result['status'];
                break;
            case 9 :
                $result = $bllItem->changeModeToAll($uid);
                $useCardResult = $result['status'];
                break;
            case 10 :
                $result = $bllItem->useAngryCard($uid);
                $useCardResult = $result['status'];
                break;
        }

        if ( $useCardResult == 1 ) {
            if ( $cid == 7 || $cid == 4 ) {
                //get activity title and pic url
                if ( $cid == 7 ) {
                    $titleId = 8;
                    $picUrl = Zend_Registry::get('static') . "/apps/dynamite/img/activity_image/7.gif";
                }
                else {
                    $titleId = 7;
                    $picUrl = Zend_Registry::get('static') . "/apps/dynamite/img/activity_image/4.gif";
                }

                require_once 'Bll/Dynamite/Activity.php';
                $title = Bll_Dynamite_Activity::getActivity($uid, '', $titleId);

                require_once 'Bll/Restful.php';
                //get restful object
                $restful = Bll_Restful::getInstance($uid, $this->_APP_ID);
                $restful->createActivityWithPic(array('title'=>$title), $picUrl, 'image/gif');

            }
        }

        $this->view->useCardResult = $useCardResult;

        $this->render();
    }

    /**
     * charashop action
     *
     */
    public function charashopAction()
    {
        if ( $this->_dynamiteInfo['isalive'] == 0 ) {
            $this->_redirect($this->_baseUrl . '/mobile/dynamite/charaselect');
        }

        require_once 'Dal/Dynamite/User.php';
        $dalUser = Dal_Dynamite_User::getDefaultInstance();
        //get hitman list
        require_once 'Bll/Cache/Dynamite.php';
        $hitmanList = Bll_Cache_Dynamite::getAllHitmanInfo(11);

        $this->view->hitmanList = $hitmanList;

        $this->render();
    }

    /**
     * charashop action
     *
     */
    public function characonfirmAction()
    {
        $action = $this->getParam('CF_step', 'confirm');
        $this->view->step = $action;

        $hid = $this->getParam('CF_hid', 1);

        require_once 'Dal/Dynamite/User.php';
        $dalUser = Dal_Dynamite_User::getDefaultInstance();

        require_once 'Bll/Cache/Dynamite.php';
        $hitmanList = Bll_Cache_Dynamite::getAllHitmanInfo(11);

        for ($i = 0; $i < count($hitmanList); $i++) {
            if ($hitmanList[$i]['id'] == $hid) {
                $hitmanInfo = $hitmanList[$i];
                break;
            }
        }

        $this->view->hitmanInfo = $hitmanInfo;

        if ( $action == "confirm" ) {
            if ( $this->_dynamiteInfo['bonus'] < $hitmanInfo['price'] ) {
                $this->_redirect($this->_baseUrl . '/mobile/dynamite/charashop');
            }
        }
        else {
            require_once 'Bll/Dynamite/Shop.php';
            $bllDynamiteShop = new Bll_Dynamite_Shop();
            //change hitman
            $result = $bllDynamiteShop->buyHitman($this->_user->getId(), $hitmanInfo['pic_id']);
            $this->view->result = $result['status'];
        }

        $this->render();
    }

    /**
     * chara select action
     *
     */
    public function charaselectAction()
    {
        $action = $this->getParam('CF_step', 'start');
        $this->view->step = $action;

        //check is alive
        if ( $this->_dynamiteInfo['isAlive'] ) {
            $this->_redirect($this->_baseUrl . '/mobile/dynamite/home');
        }

        if ( $action == "start" ) {
            require_once 'Dal/Dynamite/User.php';
            $dalUser = Dal_Dynamite_User::getDefaultInstance();
            //get hitman list
            $hitmanList = $dalUser->getAllHitmanInfo(4);
            $this->view->hitmanList = $hitmanList;
        }
        else {
            $hid = $this->getParam('CF_hid');

            if ( $hid > 0 && $hid < 4 ) {
                require_once 'Bll/Dynamite/User.php';
                $bllUser = new Bll_Dynamite_User();

                //rebirth
                $result = $bllUser->setAlive($this->_user->getId(), $hid);

                //reset bomb power
                require_once 'Bll/Cache/Dynamite.php';
                $allHitmanInfo = Bll_Cache_Dynamite::getHitmanType();

                $newHitmanInfo = $allHitmanInfo[$hid - 1];

                require_once 'Dal/Dynamite/Bomb.php';
                $dalDynamiteBomb = Dal_Dynamite_Bomb::getDefaultInstance();

                $nowTime = time();
                $dalDynamiteBomb->updateBombPowerTime($this->_user->getId(), $newHitmanInfo['power_time'], $nowTime);

                if ($result) {
                    $this->_redirect($this->_baseUrl . '/mobile/dynamite/home');
                }
            }

            if ( !$result ) {
                $this->_redirect($this->_baseUrl . '/mobile/dynamite/charaselect');
            }
        }

        $this->render();
    }

	/**
     * rankingmoney action
     *
     */
    public function rankingmoneyAction()
    {
        $pageIndex = $this->getParam('CF_page');
        $type = $this->getParam('CF_type');
        $pageSize = 5;
        $orderType = 1;

        if ( !$type ) {
            $type = $this->_dynamiteInfo['game_mode'] == 1 ? 1 : 2;
        }

        require_once 'Dal/Dynamite/Rank.php';
        $dalRank = Dal_Dynamite_Rank::getDefaultInstance();

        //$type 1->mixifriend, 2->all
        if ( $type == 1 ) {
            require_once 'Bll/Friend.php';
            $fids = Bll_Friend::getFriends($this->_user->getId());
            $fids = $fids ? $fids : '';
            if ( !$pageIndex ) {
                //get user rank number
                $userRankNm = $dalRank->getMobileUserRankNm($this->_user->getId(), $fids, $type, $orderType);
                $pageIndex = ceil($userRankNm/5);
            }
        }
        else {
            if ( !$pageIndex ) {
                if ( $this->_dynamiteInfo['game_mode'] == 1 ) {
                    $pageIndex = 1;
                }
                else {
                    //get user rank number
                    $userRankNm = $dalRank->getMobileUserRankNm($this->_user->getId(), $fids, $type, $orderType);
                    $pageIndex = $userRankNm ? ceil($userRankNm/5) : 1;
                }
            }
        }

        //get rank user
        $rankUser = $dalRank->getMaxRewardRankUser($this->_user->getId(), $fids, $pageIndex, $pageSize, $type, 'DESC');
        $rankCount = $dalRank->getRankCount($this->_user->getId(), $fids, $type, $orderType);

        require_once 'Bll/User.php';
        Bll_User::appendPeople($rankUser, 'uid');

        $this->view->rankUser = $rankUser;
        $this->view->rankCount = $rankCount;
        $this->view->type = $type;

        //get start number and end number
        $start = ($pageIndex-1)*$pageSize;
        $this->view->startNm = $start + 1;
        $this->view->endNm = ($start + $pageSize) > $rankCount ? $rankCount : ($start + $pageSize);

        //get pager info
        $this->view->pager = array('count' => $rankCount,
                                   'pageIndex' => $pageIndex,
                                   'requestUrl' => 'mobile/dynamite/rankingmoney',
                                   'pageSize' => $pageSize,
                                   'maxPager' => ceil($rankCount / $pageSize),
                                   'pageParam' => '&CF_type=' . $type
                                   );

        $this->render();
    }

    /**
     * rankingdamage action
     *
     */
    public function rankingdamageAction()
    {
        $pageIndex = $this->getParam('CF_page');
        $type = $this->getParam('CF_type');
        $pageSize = 5;
        $orderType = 2;

        if ( !$type ) {
            $type = $this->_dynamiteInfo['game_mode'] == 1 ? 1 : 2;
        }

        require_once 'Dal/Dynamite/Rank.php';
        $dalRank = Dal_Dynamite_Rank::getDefaultInstance();

        //$type 1->mixifriend, 2->all
        if ( $type == 1 ) {
            require_once 'Bll/Friend.php';
            $fids = Bll_Friend::getFriends($this->_user->getId());
            $fids = $fids ? $fids : '';
            if ( !$pageIndex ) {
                //get user rank number
                $userRankNm = $dalRank->getMobileUserRankNm($this->_user->getId(), $fids, $type, $orderType);
                $pageIndex = ceil($userRankNm/5);
            }
        }
        else {
            if ( !$pageIndex ) {
                if ( $this->_dynamiteInfo['game_mode'] == 1 ) {
                    $pageIndex = 1;
                }
                else {
                    //get user rank number
                    $userRankNm = $dalRank->getMobileUserRankNm($this->_user->getId(), $fids, $type, $orderType);
                    $pageIndex = $userRankNm ? ceil($userRankNm/5) : 1;
                }
            }
        }

        //get rank user
        $rankUser = $dalRank->getGameOverRankUser($this->_user->getId(), $fids, $pageIndex, $pageSize, $type, 'DESC');
        $rankCount = $dalRank->getRankCount($this->_user->getId(), $fids, $type, $orderType);

        require_once 'Bll/User.php';
        Bll_User::appendPeople($rankUser, 'uid');

        $this->view->rankUser = $rankUser;
        $this->view->rankCount = $rankCount;
        $this->view->type = $type;

        //get start number and end number
        $start = ($pageIndex-1)*$pageSize;
        $this->view->startNm = $start + 1;
        $this->view->endNm = ($start + $pageSize) > $rankCount ? $rankCount : ($start + $pageSize);

        //get pager info
        $this->view->pager = array('count' => $rankCount,
                                   'pageIndex' => $pageIndex,
                                   'requestUrl' => 'mobile/dynamite/rankingdamage',
                                   'pageSize' => $pageSize,
                                   'maxPager' => ceil($rankCount / $pageSize),
                                   'pageParam' => '&CF_type=' . $type
                                   );
        $this->render();
    }

    /**
     * feed action
     *
     */
    public function feedAction()
    {
        require_once 'Bll/Dynamite/Index.php';
        $bllDynamiteIndex = new Bll_Dynamite_Index();

        //minifeed
        $this->view->feed = $bllDynamiteIndex->getFeed($this->_user->getId(), $this->_APP_ID, 1);

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
     * invite finish action
     *
     */
    public function invitefinishAction()
    {
        $this->render();
    }

    /**
     * item shop action
     *
     */
    public function itemshopAction()
    {
        $uid = $this->_user->getId();

        //get user bonus
        require_once 'Dal/Dynamite/User.php';
        $dalUser = Dal_Dynamite_user::getDefaultInstance();
        $userInfo = $dalUser->getUserMoreInfo($uid);

        $bonus = $userInfo['bonus'];

        //get item list in item shop
        require_once 'Bll/Cache/Dynamite.php';
        $itemShopList = Bll_Cache_Dynamite::getItemShopList();

        //get user some item count
        require_once 'Dal/Dynamite/Item.php';
        $dalItem = Dal_Dynamite_Item::getDefaultInstance();
        $userSomeItemCount = $dalItem->getUserSomeItemCount($uid);

        $itemIdArray = array(1, 2, 3, 4, 6, 9);

        foreach ($itemShopList as $key => $value) {
            if (in_array($value['id'], $itemIdArray)) {
                $bonus >= $value['price'] ? $itemShopList[$key]['canBuy'] = 1 : $itemShopList[$key]['canBuy'] = 0;

                if ($value['id'] == 9) {
                    $itemShopList[$key]['canBuyNum'] = floor($bonus / $value['price']) > 2 ? 2 : floor($bonus / $value['price']);
                }
                else {
                    $itemShopList[$key]['canBuyNum'] = floor($bonus / $value['price']) > 9 ? 9 : floor($bonus / $value['price']);
                }
            }

            //get every item count
            if ($userSomeItemCount[$key]['cid'] == $value['id']) {
                $itemShopList[$key]['itemCount'] = $userSomeItemCount[$key]['count'];
            }

            if ($value['id'] == 60 || $value['id'] == 61 || $value['id'] == 62) {
                require_once 'Bll/Dynamite/Item.php';
                $bllItem = new Bll_Dynamite_Item();
                $bombNum = $bllItem->getBombNum($value['id']);

                if ($userInfo['bomb_count'] + $bombNum > 26) {
                    $itemShopList[$key]['passMaxCount'] = 1;
                }
            }

            //get every item price
            $itemShopList[$key]['price'] = number_format($value['price']);
        }

        $this->view->itemShopList = $itemShopList;
        $this->view->bonus = number_format($bonus);
        $this->view->userName = $this->_user->getDisplayName();
        $this->view->userPic = $this->_user->getMiniThumbnailUrl();

        $this->render();
    }

    /**
     * buy item confirm
     *
     */
    public function buyitemconfirmAction()
    {
        $uid = $this->_user->getId();
        $itemId = $this->getParam("CF_cid");
        $selectNum = $this->getPost("itemselect");

        require_once 'Bll/Dynamite/Item.php';
        $bllItem = new Bll_Dynamite_Item();
        $buyItemInfo = $bllItem->getBuyItemInfo($uid, $itemId);

        //get all price
        $allPrice = $selectNum * $buyItemInfo['itemPrice'];

        if ($buyItemInfo['bonus'] < $allPrice) {
            $this->_redirect($this->_baseUrl . '/mobile/dynamite/itemshop');
        }

        $this->view->selectedItem = $buyItemInfo['selectedItem'];
        $this->view->selectNum = $selectNum;
        $this->view->oldSelectedItemCount = $buyItemInfo['itemCount'];
        $this->view->newSelectedItemCount = $buyItemInfo['itemCount'] + $selectNum;
        $this->view->allPrice = number_format($allPrice);
        $this->view->oldBonus = number_format($buyItemInfo['bonus']);
        $this->view->newBonus = number_format($buyItemInfo['bonus'] - $allPrice);

        $this->render();
    }

    /**
     * buy item finish action
     *
     */
    public function buyitemfinishAction()
    {
        $uid = $this->_user->getId();
        $itemId = $this->getParam("CF_cid");
        $selectNum = $this->getPost("CF_selectNum");

        //check selectNum
        if ($selectNum > 9) {
            $this->_redirect($this->_baseUrl . '/mobile/dynamite/itemshop');
        }

        //check if this item can buy by bonus
        $itemIdArray = array(1, 2, 3, 4, 6, 9);

        if (!in_array($itemId, $itemIdArray)) {
            $this->_redirect($this->_baseUrl . '/mobile/dynamite/itemshop');
        }

        //check 宣戦布告 card count, max count=2
        if ($itemId == 9) {
            require_once 'Dal/Dynamite/Item.php';
            $dalItem = Dal_Dynamite_Item::getDefaultInstance();
            $itemCount = $dalItem->getOneItemCount($uid, 9);

            if ($selectNum > 2 || $itemCount >= 2) {
                $this->_redirect($this->_baseUrl . '/mobile/dynamite/itemshop');
            }
        }

        //buy item
        require_once 'Bll/Dynamite/Item.php';
        $bllItem = new Bll_Dynamite_Item();
        $result = $bllItem->buyItemSubmit($uid, $itemId, $selectNum);

        if ($result == -1) {
            $this->_redirect($this->_baseUrl . '/mobile/dynamite/itemshop');
        }

        require_once 'Bll/Cache/Dynamite.php';
        $allItemInfo = Bll_Cache_Dynamite::getItemInfo();

        foreach ($allItemInfo as $key => $value) {
            if ($itemId == $value['cid']) {
                $selectedItem = $allItemInfo[$key];
            }
        }

        $this->view->itemName = $selectedItem['name'];

        $this->render();
    }

    /**
     * click item name to buy item
     *
     */
    public function buyitemAction()
    {
        $uid = $this->_user->getId();
        $itemId = $this->getParam("CF_cid");

        //get item info
        require_once 'Bll/Dynamite/Item.php';
        $bllItem = new Bll_Dynamite_Item();
        $buyItemInfo = $bllItem->getBuyItemInfo($uid, $itemId);

        //get user this item count
        $itemCount = $buyItemInfo['itemCount'];

        //check change gamemode card
        if ($itemId == 8 || $itemId == 9) {
            if ($itemCount >= 2) {
                $this->_redirect($this->_baseUrl . '/mobile/dynamite/buyitemlimit?CF_cid=' . $itemId);
            }
        }

        //check add bomb count card
        require_once 'Dal/Dynamite/User.php';
        $dalUser = Dal_Dynamite_user::getDefaultInstance();

        if ($itemId == 60 || $itemId == 61 || $itemId == 62) {

            $bombNum = $bllItem->getBombNum($itemId);

            $userInfo = $dalUser->getUserMoreInfo($uid);

            if ($userInfo['bomb_count'] + $bombNum > 26) {
                $this->_redirect($this->_baseUrl . '/mobile/dynamite/buyitemlimit?CF_cid=' . $itemId);
            }
        }

        $itemPrice = $buyItemInfo['itemPrice'];
        $bonus = $buyItemInfo['bonus'];

        if ($itemPrice > 0) {

            if ($bonus < $itemPrice) {
                $this->view->flag = 'noEnoughMoney';
            }
            else {
                if ($itemId == 9) {
                    $buyNum = floor($bonus / $itemPrice) > 2 ? 2 : floor($bonus / $itemPrice);
                }
                else {
                    $buyNum = floor($bonus / $itemPrice) > 9 ? 9 : floor($bonus / $itemPrice);
                }
                $this->view->buyNum = $buyNum;
                $this->view->flag = 'twoMethod';
            }
        }

        $this->view->selectedItem = $buyItemInfo['selectedItem'];
        $this->view->itemCount = $buyItemInfo['itemCount'];
        $this->view->bonus = number_format($buyItemInfo['bonus']);
        $this->view->price = number_format($buyItemInfo['itemPrice']);

        $this->render();

    }

    /**
     * buy item by mixi point confirm
     *
     */
    public function buyitembymixipointconfirmAction()
    {
        $uid = $this->_user->getId();
        $itemId = $this->getParam("CF_cid");

        require_once 'Bll/Dynamite/Item.php';
        $bllItem = new Bll_Dynamite_Item();
        $buyItemInfo = $bllItem->getBuyItemInfo($uid, $itemId);

        if ($itemId == 60 || $itemId == 61 || $itemId == 62) {
            $this->view->buyBomb = 1;
        }

        //get selected item's mixi point
        require_once 'Bll/Cache/Dynamite.php';
        $itemShopList = Bll_Cache_Dynamite::getItemShopList();

        foreach ($itemShopList as $key => $value) {
            if ($itemId == $value['id']) {
                $payPoint = $itemShopList[$key]['point'];
                break;
            }
        }

        $this->view->selectedItem = $buyItemInfo['selectedItem'];
        $this->view->selectNum = 1;
        $this->view->oldSelectedItemCount = $buyItemInfo['itemCount'];
        $this->view->newSelectedItemCount = $buyItemInfo['itemCount'] + 1;
        $this->view->payPoint = $payPoint;

        $this->render();
    }

    /**
     * buy item by mixi point
     *
     */
    public function buyitembymixipointAction()
    {
        $uid = $this->_user->getId();
        $itemId = $this->getParam("CF_cid");

        if (empty($itemId)) {
            exit(0);
        }

        //check 宣戦布告 card count and マイミクシェルター count, max count=2
        if ($itemId == 8 || $itemId == 9) {
            require_once 'Dal/Dynamite/Item.php';
            $dalItem = Dal_Dynamite_Item::getDefaultInstance();
            $itemCount = $dalItem->getOneItemCount($uid, $itemId);

            if ($itemCount >= 2) {
                $this->_redirect($this->_baseUrl . '/mobile/dynamite/buyitemlimit?CF_cid=' . $itemId);
            }
        }

        require_once 'Bll/Dynamite/Item.php';
        $bllItem = new Bll_Dynamite_Item();

        //check add bomb count card
        if ($itemId == 60 || $itemId == 61 || $itemId == 62) {
            $bombNum = $bllItem->getBombNum($itemId);

            require_once 'Dal/Dynamite/User.php';
            $dalUser = Dal_Dynamite_user::getDefaultInstance();
            $userInfo = $dalUser->getUserMoreInfo($uid);

            if ($userInfo['bomb_count'] + $bombNum > 26) {
                $this->_redirect($this->_baseUrl . '/mobile/dynamite/buyitemlimit?CF_cid=' . $itemId);
            }
        }

        //get the item info
        require_once 'Bll/Cache/Dynamite.php';
        $itemShopList = Bll_Cache_Dynamite::getItemShopList();

        foreach ($itemShopList as $key => $value) {
            if ($itemId == $value['id']) {
                $selectedItem = $itemShopList[$key];
            }
        }

        $payment = array('callback_url' => Zend_Registry::get('host') . '/mobile/dynamite/buyitembymixipointfinish',
                         //'finish_url'   => Zend_Registry::get('host') . '/mobile/dynamite/buyitem?CF_cid=' . $itemId,
                         'finish_url'   => Zend_Registry::get('host') . '/mobile/dynamite/itemshop',
                         'item'         => array(array('id'    => $selectedItem['id'],
                                                       'name'  => $selectedItem['name'],
                                                       'point' => $selectedItem['point'])));

        //pay start
        require_once 'Bll/Restful.php';
        $restful = Bll_Restful::getInstance($uid, $this->_APP_ID);
        $data = $restful->createPoint($payment, $payment['item']);

        //buy item fail
        if(empty($data)) {
            $this->_redirect($payment['finish_url']);
        }

        $pay = array('point_code' => $data['id'],
                     'uid' => $uid,
                     'item_id' => $itemId,
                     'create_time' => $data['updated']);

        $result = $bllItem->insertPayment($pay);

        if ($result) {
            $this->_redirect($data['link']);
        }
        else {
            $this->_redirect($payment['finish_url']);
        }

    }

    /**
     * buy item by mixi point finish action
     *
     */
    public function buyitembymixipointfinishAction()
    {
        ob_end_clean();
        ob_start();
        ini_set('default_charset', null);
        header('HTTP/1.1 200 OK');
        header('Status: 200');
        header('Content-Type: text/plain');

        //check if this request is sended by mixi
        require_once 'Bll/Restful.php';
        $restful = Bll_Restful::getInstance($this->_user->getId(), $this->_APP_ID);
        $sig = $restful->checkSignature();

        if (!$sig) {
            echo 'CANCEL';
            exit(0);
        }

        $point_code = $this->getParam('point_code');
        //status = 20, cancel, status = 10 buy submit
        $pay_status = $this->getParam('status', 20);
        $updated = $this->getParam('updated');

        if (empty($point_code) || $pay_status == 20) {
            require_once 'Dal/Dynamite/User.php';
            $dalUser = Dal_Dynamite_User::getDefaultInstance();
            $dalUser->updatePaymentStatus($point_code, 2, time());

            echo 'OK';
            exit(0);
        }

        require_once 'Bll/Dynamite/Item.php';
        $bllItem = new Bll_Dynamite_Item();
        $result = $bllItem->buyItemByMixiPointSubmit($point_code);

        echo $result == 1 ? 'OK' : 'CANCEL';
        exit(0);
    }

    /**
     * buy item limit
     *
     */
    public function buyitemlimitAction()
    {
        $uid = $this->_user->getId();
        $itemId = $this->getParam("CF_cid");

        require_once 'Bll/Dynamite/Item.php';
        $bllItem = new Bll_Dynamite_Item();
        $buyItemInfo = $bllItem->getBuyItemInfo($uid, $itemId);

        $this->view->buyItemInfo = $buyItemInfo;

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
        return $this->_redirect($this->_baseUrl . '/mobile/dynamite/error');
    }
}
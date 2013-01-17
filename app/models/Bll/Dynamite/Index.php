<?php
/** @see Zend_Json */
require_once 'Zend/Json.php';
/** @see Bll_Abstract.php */
require_once 'Bll/Abstract.php';

/** @see Dal_Dynamite_User.php */
require_once 'Dal/Dynamite/User.php';

/** @see Dal_Dynamite_Bomb.php */
require_once 'Dal/Dynamite/Bomb.php';

/**
 * dynamite index logic's Operation
 *
 * @package    Bll
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/07/06    Liz
 */
class Bll_Dynamite_Index extends Bll_Abstract
{
    private $_maxBonus = 99999999999;
    private $_maxReward = 10000;
    private $_quotiety = 0.1;
    private $_maxBombCount = 26;

    /**
     * get user dynamite, back current and next
     *
     * @param integer $uid
     * @return array
     */
    public function getUserDynamite($uid, $request_id)
    {
        $this->getUserCurrentBlood($request_id);
        //$time_start = microtime(true);
        $dalDynamiteUser = Dal_Dynamite_User::getDefaultInstance();
        $dalDynamiteBomb = Dal_Dynamite_Bomb::getDefaultInstance();

        $currentDynamite = $dalDynamiteUser->getUser($request_id);

        require_once 'Bll/Cache/Dynamite.php';
        $hitmanInfo = Bll_Cache_Dynamite::getHitmanType();
        //$currentDynamite = array_merge($currentDynamite, $hitmanInfo[$currentDynamite['hitman_type'] - 1]);
        $currentDynamite['pic_id'] = $hitmanInfo[$currentDynamite['hitman_type'] - 1]['pic_id'];
        $currentDynamite['max_life'] = $hitmanInfo[$currentDynamite['hitman_type'] - 1]['max_life'];
        //get target user be set bomb infomation
        $currentHitmanBomb = $dalDynamiteBomb->getUserHitmanBomb($request_id);

        //get actor set to target's bomb
        $currentSetBomb = $dalDynamiteBomb->getUsesSetBombToOne($uid, $request_id);

        $currentDynamite ? Bll_User::appendPerson($currentDynamite, 'uid') : $currentDynamite;
        $currentHitmanBomb ? Bll_User::appendPeople($currentHitmanBomb, 'uid') : $currentHitmanBomb;

        //if user and enemy is friend?
        require_once 'Bll/Friend.php';
        $isFriend = Bll_Friend::isFriend($uid, $request_id);

        $response = array('current' => array('dynamite' => $currentDynamite, 'hitmanBomb' => $currentHitmanBomb, 'setBomb' => $currentSetBomb, 'isFriend' => $isFriend));

        return $response;
    }

    /**
     * get all app user
     *
     * @return array
     */
    public function getMyMixiUser($uid)
    {
        $fids = Bll_Friend::getFriendIds($uid);
        $fids = explode(',', $fids);

        $dalDynamiteUser = Dal_Dynamite_User::getDefaultInstance();
        $myMixiUser = $dalDynamiteUser->getUidInFids($fids, 1);

        return $myMixiUser;
    }

    /**
     * set bomb
     *
     * @return array
     */
    public function setBomb($bombInfo, $userPicId)
    {
        /*
         * status = -1, system error,
         * status = -2, need restart game,
         * status = 1, set bomb success,
         * status = 3, need send bomb to user,
         * status = 2, bomb 没収
         */
        $result = array('status' => -1);

        if ($bombInfo['uid'] == $bombInfo['bomb_uid']) {
            return $result;
        }

        require_once 'Dal/Dynamite/User.php';
        $dalDynamiteUser = Dal_Dynamite_User::getDefaultInstance();
        //check if actor and target are in app
        $isInApp = $dalDynamiteUser->isInApp(array(0 => $bombInfo['uid'], 1 => $bombInfo['bomb_uid']));

        if ($isInApp != 2) {
            return $result;
        }
        //check actor and target game mode
        require_once 'Dal/Dynamite/Item.php';
        $dalItem = Dal_Dynamite_Item::getDefaultInstance();
        $myGameMode = $dalItem->getUserGameMode($bombInfo['uid']);
        $enemyGameMode = $dalItem->getUserGameMode($bombInfo['bomb_uid']);

        require_once 'Bll/Friend.php';
        $isFriend = Bll_Friend::isFriend($bombInfo['uid'], $bombInfo['bomb_uid']);

        if ($myGameMode == 0 && $enemyGameMode == 1 && !$isFriend) {
            return $result;
        }
        if ($myGameMode == 1 && $enemyGameMode == 0 && !$isFriend) {
            return $result;
        }
        if ($myGameMode == 1 && $enemyGameMode == 1 && !$isFriend) {
            return $result;
        }

        $this->_wdb->beginTransaction();
        try {
            if ($bombInfo['uid'] < $bombInfo['bomb_uid']) {
                //lock actor info
                $actorUserInfo = $dalDynamiteUser->getUserForUpdate($bombInfo['uid']);
                //lock target info
                $targetUserInfo = $dalDynamiteUser->getUserForUpdate($bombInfo['bomb_uid']);
            }
            else {
                //lock target info
                $targetUserInfo = $dalDynamiteUser->getUserForUpdate($bombInfo['bomb_uid']);
                //lock actor info
                $actorUserInfo = $dalDynamiteUser->getUserForUpdate($bombInfo['uid']);
            }

            //check target hitman's bomb count
            if ($targetUserInfo['hitman_bomb_count' . $bombInfo['bomb_hitman']] == 4) {
                $this->_wdb->rollBack();
                return $result;
            }
            //check if hitman dead
            if ($targetUserInfo['hitman_life' . $bombInfo['bomb_hitman']] == 0) {
                $this->_wdb->rollBack();
                return $result;
            }

            if ($actorUserInfo['remainder_bomb_count'] == 0) {
                $this->_wdb->rollBack();
                return $result;
            }
            if ($actorUserInfo['bomb_count'] == 0) {
                $this->_wdb->rollBack();
                return $result;
            }
            //check set bomb user dead
            if ($actorUserInfo['hitman_count'] < 1) {
                $this->_wdb->rollBack();
                return array('status' => -2);
            }

            $dalDynamiteBomb = Dal_Dynamite_Bomb::getDefaultInstance();
            //check is had bomb this user's hitman
            $bomb = $dalDynamiteBomb->getBombByUidAndHitman($bombInfo['uid'], $bombInfo['bomb_uid'], $bombInfo['bomb_hitman']);
            if ($bomb) {
                $this->_wdb->rollBack();
                return $result;
            }

            //get actor's remove bomb
            $userRemoveBomb = $this->getUserRemoveBomb($actorUserInfo);

            //begin dispose data, if target use haoyi card
            if (time() - $targetUserInfo['refuse_bomb_time'] < 3 * 3600) {
                //change actor bomb count's infomation
                if ($userRemoveBomb) {
                    //actor reduce a power bomb
                    $removeBomb = $userRemoveBomb[0]['bomb_power'];
                    $dalDynamiteUser->updateUserMoreInfo($bombInfo['uid'], array('bomb_power' . $removeBomb => $actorUserInfo['bomb_power' . $removeBomb] - 1));
                }

                $dalDynamiteUser->updateUserBombCountAndRemainBombCount($bombInfo['uid'], -1);

                //change target bomb count's infomation
                if ($targetUserInfo['bomb_count'] < $this->_maxBombCount) {
                    if ($removeBomb) {
                        //target add a power bomb
                        $dalDynamiteUser->updateUserMoreInfo($bombInfo['bomb_uid'], array('bomb_power' . $removeBomb => $targetUserInfo['bomb_power' . $removeBomb] + 1));
                    }

                    $dalDynamiteUser->updateUserBombCountAndRemainBombCount($bombInfo['bomb_uid'], 1);
                }

                $result['status'] = $actorUserInfo['bomb_count'] == 1 ? 3 : 2;
            }
            else {
                //bomb's init power
                $bombInfo['bomb_power'] = 0;

                if ($userRemoveBomb) {
                    $removeBomb = $userRemoveBomb[0]['bomb_power'];
                    $bombInfo['bomb_power'] = $removeBomb;

                    $updateInfo = array('bomb_power' . $removeBomb => $actorUserInfo['bomb_power' . $removeBomb] - 1, 'remainder_bomb_count' => $actorUserInfo['remainder_bomb_count'] - 1);

                    $dalDynamiteUser->updateUserMoreInfo($bombInfo['uid'], $updateInfo);
                }
                else {
                    //update actor's remainder bomb count
                    $dalDynamiteUser->updateUserRemainderBombCount($bombInfo['uid'], -1);
                }

                //insert to table dynamite_bomb
                $dalDynamiteBomb->insertBomb($bombInfo);

                //update bomb count under the target's hitman
                $dalDynamiteUser->updateUserHitmanBombCount($bombInfo['bomb_uid'], $bombInfo['bomb_hitman'], 1);

                //get user hitman pic info
                $hitmanPicType = 'b';
                for ($i = 1; $i < 5; $i++) {
                    $hitmanId = 'hitman_life' . $i;
                    if ($actorUserInfo[$hitmanId] >= ($actorUserInfo['max_life'] / 2)) {
                        $hitmanPicType = 'a';
                        break;
                    }
                }

                if ($userPicId < 10) {
                    $hitmanPic = '0' . $userPicId . '_' . $hitmanPicType;
                }
                else {
                    $hitmanPic = $userPicId . '_' . $hitmanPicType;
                }

                $result['hitman_pic'] = $hitmanPic;
                $result['power_time'] = $bombInfo['power_time'];
                $result['status'] = 1;
            }
            $this->_wdb->commit();
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return array('status' => -1);
        }

        try {
            //insert into minifeed
            require_once 'Dal/Dynamite/Feed.php';
            $dalDynamiteFeed = Dal_Dynamite_Feed::getDefaultInstance();
            $create_time = date('Y-m-d H:i:s');

            if ($result['status'] == 2 || $result['status'] == 3) {

                $minifeed = array('uid' => $bombInfo['uid'],
                                  'template_id' => 58,
                                  'actor' => $bombInfo['uid'],
                                  'target' => $bombInfo['bomb_uid'],
                                  'feed_type' => 'ダイナマイト没収',
                                  'icon' => Zend_Registry::get('static') . "/apps/dynamite/img/icon/dynamite.gif",
                                  'title' => '',
                                  'create_time' => $create_time);

                $feedTable = $this->getFeedTable($minifeed['uid']);
                $dalDynamiteFeed->insertFeed($minifeed, $feedTable);

                $minifeed['uid'] = $bombInfo['bomb_uid'];
                $feedTable = $this->getFeedTable($minifeed['uid']);
                $dalDynamiteFeed->insertFeed($minifeed, $feedTable);
            }
            else if ($result['status'] == 1) {

                $minifeed = array('uid' => $bombInfo['uid'],
                                  'template_id' => 43,
                                  'actor' => $bombInfo['uid'],
                                  'target' => $bombInfo['bomb_uid'],
                                  'feed_type' => 'ダイナマイト設置',
                                  'icon' => Zend_Registry::get('static') . "/apps/dynamite/img/icon/dynamite.gif",
                                  'title' => '',
                                  'create_time' => $create_time);

                $minifeed['uid'] = $bombInfo['bomb_uid'];
                $feedTable = $this->getFeedTable($minifeed['uid']);

                $dalDynamiteFeed->insertFeed($minifeed, $feedTable);
            }
        }
        catch(Exception $e1) {

        }

        return $result;
    }

    /**
     * trigger bomb
     *
     * @param array $bombInfo
     * @return array
     */
    public function triggerBomb($bombInfo)
    {
        /*
         * status = -1, system error,
         * status = 1, trigger success,enemy's hitman not dead
         * status = 2 ,trigger success,enemy's hitman dead,
         * status = -2, check is had bomb in this user's hitman
         * status = -3, user have game over
         * status = -4, 設置後、5分未満のダイナマイトは爆破できません。
         */
        $result = array('status' => -1);

        if ($bombInfo['uid'] == $bombInfo['bomb_uid']) {
            return $result;
        }

        require_once 'Dal/Dynamite/User.php';
        $dalDynamiteUser = Dal_Dynamite_User::getDefaultInstance();
        //check if actor and target are in app
        $isInApp = $dalDynamiteUser->isInApp(array(0 => $bombInfo['uid'], 1 => $bombInfo['bomb_uid']));

        if ($isInApp != 2) {
            return $result;
        }

        $this->_wdb->beginTransaction();
        try {
            if ($bombInfo['uid'] < $bombInfo['bomb_uid']) {
                //lock actor info
                $actorUserInfo = $dalDynamiteUser->getUserForUpdate($bombInfo['uid']);
                //lock target info
                $targetUserInfo = $dalDynamiteUser->getUserForUpdate($bombInfo['bomb_uid']);
            }
            else {
                //lock target info
                $targetUserInfo = $dalDynamiteUser->getUserForUpdate($bombInfo['bomb_uid']);
                //lock actor info
                $actorUserInfo = $dalDynamiteUser->getUserForUpdate($bombInfo['uid']);
            }

            //check actor is game over
            $userHitmaType = $actorUserInfo['hitman_type'];
            if ($actorUserInfo['hitman_count'] < 1) {
                $this->_wdb->rollBack();
                return array('status' => -3);
            }
            //check target's hitman
            if ($targetUserInfo['hitman_life' . $bombInfo['bomb_hitman']] < 1) {
                $this->_wdb->rollBack();
                return $result;
            }

            if ($targetUserInfo['hitman_count'] < 1) {
                $this->_wdb->rollBack();
                return $result;
            }

            $dalDynamiteBomb = Dal_Dynamite_Bomb::getDefaultInstance();

            //begin dispose data
            //get all of the bomb power with this hitman
            $allBombPower = 0;

            $nowUserHitmanBomb = $dalDynamiteBomb->getTheHitmanBomb($bombInfo['bomb_uid'], $bombInfo['bomb_hitman']);

            if (empty($nowUserHitmanBomb)) {
                $this->_wdb->rollBack();
                return $result;
            }
            else {
                //check if actor had bomb this user's hitman
                $myBomb = null;
                for ($i = 0, $iCount = count($nowUserHitmanBomb); $i < $iCount; $i++) {
                    if ($bombInfo['uid'] == $nowUserHitmanBomb[$i]['uid']) {
                        $myBomb = $nowUserHitmanBomb[$i];
                    }
                    else {
                        //lock user who set bomb in this hitman
                        $dalDynamiteUser->getUserForUpdate($nowUserHitmanBomb[$i]['uid']);
                    }
                }
            }
            if (empty($myBomb)) {
                $this->_wdb->rollBack();
                return array('status' => -2);
            }
            else if ($myBomb['bomb_power'] < 1 || $myBomb['needWait'] == 1) {
                $this->_wdb->rollBack();
                return $result;
            }
            else {
                for ($i = 0, $iCount = count($nowUserHitmanBomb); $i < $iCount; $i++) {
                    //add bomb power,get all bomb power
                    $addPower = $nowUserHitmanBomb[$i]['bomb_power'] ? $nowUserHitmanBomb[$i]['bomb_power'] : 0;
                    $allBombPower += $addPower;
                }
            }

            if ($allBombPower < 1) {
                $this->_wdb->rollBack();
                return array('status' => -4);
            }
            else {
                for ($i = 0, $iCount = count($nowUserHitmanBomb); $i < $iCount; $i++) {
                    if ($nowUserHitmanBomb[$i]['bomb_hitman'] == $bombInfo['bomb_hitman']) {
                        //delete bomb by bomb id
                        $deleteResult = $dalDynamiteBomb->deleteBombById($nowUserHitmanBomb[$i]['bomb_id']);

                        if ($deleteResult) {
                            //update uid bomb count
                            $dalDynamiteUser->updateUserBombCount($nowUserHitmanBomb[$i]['uid'], -1);
                        }
                    }
                }
            }

            //get hitman remainder life
            $hitmanRemainderSelf = $targetUserInfo['hitman_life' . $bombInfo['bomb_hitman']] - $allBombPower;

            $hitmanRemainderSelf = $hitmanRemainderSelf > 0 ? $hitmanRemainderSelf : 0;

            //update bomb_hitman info
            $newHitman = array('hitman_life' . $bombInfo['bomb_hitman'] => $hitmanRemainderSelf, 'hitman_bomb_count' . $bombInfo['bomb_hitman'] => 0);

            //update target hitman bomb count and hitman life
            $dalDynamiteUser->updateUserMoreInfo($bombInfo['bomb_uid'], $newHitman);

            //if actor's bomb count=1, after trigger, auto present 4 bombs
            if ($actorUserInfo['bomb_count'] == 1) {

                $sendBombNum = $this->sendBombToUser($bombInfo['uid']);

                $dalDynamiteUser->updateUserBombCountAndRemainBombCount($bombInfo['uid'], $sendBombNum);

                $result['presentBomb'] = 1;
                $result['autoSendBomb'] = $sendBombNum;
            }

            //check enemy's hitman is dead, enemy's hitman not dead
            if ($hitmanRemainderSelf > 0) {
                $result['status'] = 1;
                $result['hitmanRemainderSelf'] = $hitmanRemainderSelf;
                $result['bombPower'] = $allBombPower;
            }
            //enemy's hitman dead
            else {
                //update target's hitman count and hitman dead time
                $deadHitman = array('hitman_count' => $targetUserInfo['hitman_count'] - 1, 'hitman_dead_time' . $bombInfo['bomb_hitman'] => time());

                $dalDynamiteUser->updateUserMoreInfo($bombInfo['bomb_uid'], $deadHitman);

                //send gift to actor
                if ($result['presentBomb'] == 1) {
                    $sendCid = $this->getGiftWhenBombCountIsZero($bombInfo['uid']);
                }
                else {
                    $sendCid = $this->getGift($bombInfo['uid'], 1);
                }

                $result['sendCid'] = $sendCid;

                //add actor bonus
                $myBonus = $actorUserInfo['bonus'];
                $limitReward = round($targetUserInfo['bonus'] * $this->_quotiety) > $this->_maxReward ? $this->_maxReward : round($targetUserInfo['bonus'] * $this->_quotiety);
                if ($myBonus < $this->_maxBonus) {
                    $addLimitReward = $myBonus + $limitReward > $this->_maxBonus ? ($this->_maxBonus - $myBonus) : $limitReward;

                    $dalDynamiteUser->updateUserBonus($bombInfo['uid'], $addLimitReward);
                }

                $result['getBonus'] = $limitReward;

                require_once 'Dal/Dynamite/Item.php';
                $dalItem = Dal_Dynamite_Item::getDefaultInstance();

                //if enemy game over
                if ($targetUserInfo['hitman_count'] == 1) {
                    //enemy set bomb to other people
                    $userSetBombInfo = $dalItem->getUserSetBombInfoForUpdate($bombInfo['bomb_uid']);

                    foreach ($userSetBombInfo as $value) {
                        $dalItem->updateUserHitmanBomb($value['bomb_uid'], $value['bomb_hitman']);
                    }

                    //delete bomb about enemy
                    $dalItem->deleteBombAboutUser($bombInfo['bomb_uid']);

                    //enemy's bonus reduce 50%
                    $enemyUpdateInfo = array('bonus' => $targetUserInfo['bonus'] * 0.5, 'dead_number' => $targetUserInfo['dead_number'] + 1);

                    $dalDynamiteUser->updateUserMoreInfo($bombInfo['bomb_uid'], $enemyUpdateInfo);

                    $dalDynamiteUser->updateUserBasicInfo($bombInfo['bomb_uid'], array('isgameover' => 1));

                    $result['getBonus'] = $limitReward;

                    $enemyGameOver = true;
                }

                $result['status'] = 2;
                //actor new bonus
                $newUserBonus = $dalItem->getUserBonusByUid($bombInfo['uid']);
                if ($newUserBonus >= 1000 && $userHitmaType == 11) {
                    $result['selectHitman'] = 1;
                }
                else {
                    $result['selectHitman'] = 0;
                }
            }

            $result['bomb_hitman'] = $bombInfo['bomb_hitman'];

            //insert table dynamite_enemy
            require_once 'Dal/Dynamite/Enemy.php';
            $dalDynamiteEnemy = Dal_Dynamite_Enemy::getDefaultInstance();
            $dalDynamiteEnemy->insertEnemy($bombInfo['uid'], $bombInfo['bomb_uid']);

            $this->_wdb->commit();
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return array('status' => -1);
        }

        try {
            //insert into minifeed
            require_once 'Dal/Dynamite/Feed.php';
            $dalDynamiteFeed = Dal_Dynamite_Feed::getDefaultInstance();

            $create_time = date('Y-m-d H:i:s');

            $minifeed = array('uid' => $bombInfo['uid'],
                              'template_id' => 44,
                              'actor' => $bombInfo['uid'],
                              'target' => $bombInfo['bomb_uid'],
                              'feed_type' => 'ダイナマイト爆破',
                              'icon' => Zend_Registry::get('static') . "/apps/dynamite/img/icon/dynamite.gif",
                              'title' => '',
                              'create_time' => $create_time);

            if ($result['status'] == 1) {
                $minifeed['uid'] = $bombInfo['bomb_uid'];
                $feedTable = $this->getFeedTable($minifeed['uid']);
                $dalDynamiteFeed->insertFeed($minifeed, $feedTable);
            }
            else if ($result['status'] == 2) {
                if ($enemyGameOver) {
                    $minifeed['template_id'] = 56;
                    $minifeed['feed_type'] = 'アジト壊滅';

                    $feedTable = $this->getFeedTable($minifeed['uid']);
                    $dalDynamiteFeed->insertFeed($minifeed, $feedTable);

                    $minifeed['uid'] = $bombInfo['bomb_uid'];
                    $feedTable = $this->getFeedTable($minifeed['uid']);
                    $dalDynamiteFeed->insertFeed($minifeed, $feedTable);
                }
                else {
                    $minifeed['template_id'] = 45;
                    $minifeed['feed_type'] = 'ヒットマン殉職';
                    $minifeed['icon'] = Zend_Registry::get('static') . "/apps/dynamite/img/icon/dead.gif";
                    $minifeed['title'] = '{"money":"' . $limitReward . '"}';
                    $minifeed['uid'] = $bombInfo['bomb_uid'];

                    $feedTable = $this->getFeedTable($minifeed['uid']);
                    $dalDynamiteFeed->insertFeed($minifeed, $feedTable);
                }
            }
        }
        catch (Exception $e1) {
        }

        return $result;
    }

    /**
     * remove bomb
     *
     * @param array $bombInfo
     * @return array
     */
    public function removeBomb($bombInfo)
    {
        /*
         * status = -1, system error
         * status = 1,remove success
         */
        $result = array('status' => -1);

        if ($bombInfo['bomb_uid'] != $bombInfo['uid']) {
            return $result;
        }

        require_once 'Dal/Dynamite/User.php';
        $dalDynamiteUser = Dal_Dynamite_User::getDefaultInstance();
        //check if actor and target are in app
        $isInApp = $dalDynamiteUser->isInApp(array(0 => $bombInfo['uid'], 1 => $bombInfo['bomb_uid']));

        if ($isInApp != 1) {
            return $result;
        }

        $this->_wdb->beginTransaction();
        try {
            //lock actor info
            $userInfo = $dalDynamiteUser->getUserForUpdate($bombInfo['uid']);

            //check have this bomb and can remove
            $dalDynamiteBomb = Dal_Dynamite_Bomb::getDefaultInstance();

            $maxPowerBomb = 0;
            //who set bomb in this hitman
            $userHitmanBomb = $dalDynamiteBomb->getTheHitmanBomb($bombInfo['bomb_uid'], $bombInfo['bomb_hitman']);

            for ($i = 0, $iCount = count($userHitmanBomb); $i < $iCount; $i++) {
                if ($userHitmanBomb[$i]['bomb_hitman'] == $bombInfo['bomb_hitman'] && $userHitmanBomb[$i]['bomb_power'] > 0) {
                    //check is had bomb this user's hitman
                    $removeBombInfo[] = $userHitmanBomb[$i];
                    $maxPowerBombInfo = $userHitmanBomb['0'];
                    if ($maxPowerBomb < $userHitmanBomb[$i]['bomb_power']) {
                        $maxPowerBomb = $userHitmanBomb[$i]['bomb_power'];
                        $maxPowerBombInfo = $userHitmanBomb[$i];
                    }
                }
            }

            if (!$removeBombInfo) {
                $this->_wdb->rollBack();
                return $result;
            }

            $lockedUser = array();
            for ($i = 0, $iCount = count($removeBombInfo); $i < $iCount; $i++) {
                $lockedUser[] = $dalDynamiteUser->getUserForUpdate($removeBombInfo[$i]['uid']);
            }

            $updateRemainderBombCount = 0;

            for ($j = 0, $jCount = count($removeBombInfo); $j < $jCount; $j++) {

                Bll_User::appendPerson($removeBombInfo[$j], 'uid');

                //add bomb to actor
                if ($userInfo['bomb_count'] + 1 < 27) {

                    $updateRemainderBombCount += 1;

                    $userUpdateInfo = array('bomb_count' => $userInfo['bomb_count'] + 1,
                                            'remainder_bomb_count' => $userInfo['remainder_bomb_count'] + 1,
                                            'bomb_power' . $removeBombInfo[$j]['bomb_power'] => $userInfo['bomb_power' . $removeBombInfo[$j]['bomb_power']] + 1);

                    $dalDynamiteUser->updateUserMoreInfo($userInfo['uid'], $userUpdateInfo);

                    $userInfo['bomb_count'] = $userInfo['bomb_count'] + 1;
                    $userInfo['remainder_bomb_count'] = $userInfo['remainder_bomb_count'] + 1;
                    $userInfo['bomb_power' . $removeBombInfo[$j]['bomb_power']] = $userInfo['bomb_power' . $removeBombInfo[$j]['bomb_power']] + 1;
                }

                //delete the bomb info
                $deleteResult = $dalDynamiteBomb->deleteBombById($removeBombInfo[$j]['bomb_id']);

                if ($deleteResult) {
                    //update enemy bomb count
                    $dalDynamiteUser->updateUserBombCount($removeBombInfo[$j]['uid'], -1);
                }
            }
            //update my hitman bomb count
            $dalDynamiteUser->updateUserHitmanBombCount($userInfo['uid'], $bombInfo['bomb_hitman'], -count($removeBombInfo));

            $result['remainderBombCount'] = $userInfo['remainder_bomb_count'];

            require_once 'Bll/User.php';
            $activityUserInfo = Bll_User::getPerson($maxPowerBombInfo['uid']);

            $result['removeMaxBombName'] = $activityUserInfo->getdisplayName();
            $result['removeBombCount'] = count($removeBombInfo);
            $result['removeBombInfo'] = $removeBombInfo;

            $this->_wdb->commit();

            $result['status'] = 1;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return array('status' => -1);
        }

        try {
            if ($result['status'] == 1) {
                //insert into minifeed
                require_once 'Dal/Dynamite/Feed.php';
                $dalDynamiteFeed = Dal_Dynamite_Feed::getDefaultInstance();

                $create_time = date('Y-m-d H:i:s');

                $minifeed = array('uid' => $maxPowerBombInfo['uid'],
                                  'template_id' => 48,
                                  'actor' => $bombInfo['uid'],
                                  'target' => $maxPowerBombInfo['uid'],
                                  'feed_type' => 'ダイナマイト撤去成功',
                                  'icon' => Zend_Registry::get('static') . "/apps/dynamite/img/icon/dynamite.gif",
                                  'title' => '',
                                  'create_time' => $create_time);

                $feedTable = $this->getFeedTable($minifeed['uid']);
                $dalDynamiteFeed->insertFeed($minifeed, $feedTable);
            }
        }
        catch (Exception $e1) {

        }

        return $result;
    }

    /**
     * get feed
     *
     * @param integer $uid
     * @param integer $appId
     * @param integer $isMobile
     * @return array
     */
    public function getFeed($uid, $appId, $isMobile = 0)
    {
        $pageSize = $isMobile == 1 ? 10 : 20;

        require_once 'Dal/Dynamite/Feed.php';
        $dalDynamiteFeed = new Dal_Dynamite_Feed();

        $feedTable = $this->getFeedTable($uid);
        $feed = $dalDynamiteFeed->getFeed($uid, 1, $pageSize, $feedTable);

        return $this->buildFeed($feed, $appId, $isMobile);
    }

    /**
     * build feed
     *
     * @param array $feed
     * @return array
     */
    public function buildFeed($feed, $appId, $isMobile)
    {
        $lnml_env = array('is_mobile' => $isMobile == 1 ? true : false);

        require_once 'Zend/Json.php';

        for ($i = 0; $i < count($feed); $i++) {
            $feed_title_template = self::getFeedTemplateTitle($appId, $feed[$i]['template_id'], $lnml_env['is_mobile']);

            $title_lnml = $this->buildTemplateLnml($feed[$i]['actor'], $feed[$i]['target'], $feed_title_template, Zend_Json::decode($feed[$i]['title']), $isMobile);

            if ($title_lnml) {
                $feed[$i]['title'] = $title_lnml;
            }
            else {
                $feed[$i]['title'] = '';
            }
        }

        return $feed;
    }

    /**
     * get feed title by template
     *
     * @param integer $app_id
     * @param integer $template_id
     * @param boolean $is_mobile
     * @return array
     */
    public function getFeedTemplateTitle($app_id, $template_id, $is_mobile = false)
    {
        $template_info = $this->getFeedTemplateInfo($app_id, $template_id);

        if ($template_info) {
            if (!$is_mobile) {
                return $template_info['title'];
            }
            else {
                return $template_info['m_title'];
            }
        }

        return null;
    }

    /**
     * Get feed template whole information
     *
     * @param int $app_id
     * @param int $template_id
     * @return array
     */
    public function getFeedTemplateInfo($app_id, $template_id)
    {
        $key = $app_id . ',' . $template_id;

        if (Zend_Registry::isRegistered('FEED_TEMPLATE_INFO')) {
            $FEED_TEMPLATE_INFO = Zend_Registry::get('FEED_TEMPLATE_INFO');

            if (isset($FEED_TEMPLATE_INFO[$key])) {
                return $FEED_TEMPLATE_INFO[$key];
            }
        }
        else {
            $FEED_TEMPLATE_INFO = array();
        }

        $template_info = Bll_Cache_FeedTemplate::getInfo($app_id, $template_id);

        if ($template_info) {
            $FEED_TEMPLATE_INFO[$key] = $template_info;

            Zend_Registry::set('FEED_TEMPLATE_INFO', $FEED_TEMPLATE_INFO);

            return $template_info;
        }

        return null;
    }

    /**
     * build template lnml
     *
     * @param integer $user
     * @param integer $target
     * @param string $template
     * @param array $json_array
     * @return string
     */
    public function buildTemplateLnml($user, $target, $template, $json_array, $isMobile)
    {
        if ($json_array == null) {
            $json_array = array();
        }

        if (!is_array($json_array)) {
            return false;
        }

        require_once 'Bll/User.php';
        $actor = Bll_User::getPerson($user);

        if (empty($actor)) {
            $actor_name = "____";
        }
        else {
            $actor_name = $actor->getDisplayName();
        }

        if ($isMobile == 1) {
            $url = Zend_Registry::get('host') . '/mobile/dynamite/profile?CF_uid=' . $user;
            $joinchar = (stripos($url, '?') === false) ? '?' : '&';
            $actorUrl = Zend_Registry::get('MIXI_APP_REQUEST_URL') . urlencode($url . $joinchar . 'rand=' . rand());
            //$actorUrl = $url;


            $json_array['actor'] = '<a href="' . $actorUrl . '">' . $actor_name . '</a>';
        }
        else {
            $json_array['actor'] = '<a href="javascript:jQuery.dynamite.goUserDynamite(' . $user . ' ,1);" >' . $actor_name . '</a>';
        }

        if ($target) {
            $targ = Bll_User::getPerson($target);

            if (empty($targ)) {
                $target_name = "____";
            }
            else {
                $target_name = $targ->getDisplayName();
            }

            if ($isMobile == 1) {
                $url = Zend_Registry::get('host') . '/mobile/dynamite/profile?CF_uid=' . $target;
                $joinchar = (stripos($url, '?') === false) ? '?' : '&';
                $targetUrl = Zend_Registry::get('MIXI_APP_REQUEST_URL') . urlencode($url . $joinchar . 'rand=' . rand());
                //$targetUrl = $url;


                $json_array['target'] = '<a href="' . $targetUrl . '">' . $target_name . '</a>';
            }
            else {
                $json_array['target'] = '<a href="javascript:jQuery.dynamite.goUserDynamite(' . $target . ', 1);" >' . $target_name . '</a>';
            }
        }

        $keys = array();
        $values = array();

        foreach ($json_array as $k => $v) {
            $keys[] = '{*' . $k . '*}';
            $values[] = $v;
        }

        return str_replace($keys, $values, $template);
    }

    /**
     * today frist login,send card
     *
     * @param string $uid
     * @return array
     */
    public function isTodayFirstLogin($uid)
    {
        $this->_wdb->beginTransaction();

        try {

            $cid = $this->getGift($uid);

            $this->_wdb->commit();
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return false;
        }

        return $cid;
    }

    /**
     * all hitman dead, restart game
     *
     * @param string $uid
     * @return array
     */
    public function needRestartGame($uid, $batchwork = null)
    {
        $result = -1;

        require_once 'Dal/Dynamite/User.php';
        $dalUser = Dal_Dynamite_User::getDefaultInstance();
        //get user info
        $userInfo = $dalUser->getUser($uid);

        $bloodCount = $userInfo['hitman_life1'] + $userInfo['hitman_life2'] + $userInfo['hitman_life3'] + $userInfo['hitman_life4'];

        //all hitman dead
        if ($bloodCount == 0 && $userInfo['hitman_count'] == 0) {

            $this->_wdb->beginTransaction();

            try {
                require_once 'Dal/Dynamite/Item.php';
                $dalItem = Dal_Dynamite_Item::getDefaultInstance();
                //user set bomb to other
                $userSetBombInfo = $dalItem->getUserSetBombInfoForUpdate($uid);
                //who have set bomb to user
                $whoSetBombToUser = $dalItem->whoInstallBombInUserAndAlliance($uid);

                if ($batchwork) {
                    $typeArray = array('0' => '1', '1' => '2', '2' => '3');
                    $hitmanType = $typeArray[rand(0, 2)];

                    require_once 'Bll/Cache/Dynamite.php';
                    $hitmanInfo = Bll_Cache_Dynamite::getHitmanType();
                    $maxLife = $hitmanInfo[$hitmanType - 1]['max_life'];

                    $cleanInfo = array('hitman_count' => 4,
                                       'bomb_count' => 4,
                                       'remainder_bomb_count' => 4,
                                       'hitman_life1' => $maxLife,
                                       'hitman_life2' => $maxLife,
                                       'hitman_life3' => $maxLife,
                                       'hitman_life4' => $maxLife,
                                       'hitman_bomb_count1' => 0,
                                       'hitman_bomb_count2' => 0,
                                       'hitman_bomb_count3' => 0,
                                       'hitman_bomb_count4' => 0,
                                       'hitman_dead_time1' => 0,
                                       'hitman_dead_time2' => 0,
                                       'hitman_dead_time3' => 0,
                                       'hitman_dead_time4' => 0,
                                       'bomb_power1' => 0,
                                       'bomb_power2' => 0,
                                       'bomb_power3' => 0,
                                       'bomb_power4' => 0,
                                       'bomb_power5' => 0);

                    $dalUser->updateUserMoreInfo($uid, $cleanInfo);
                }
                else {
                    $hitmanType = $userInfo['hitman_type'];
                }

                $dalItem->cleanUserInfo($uid, $batchwork, $hitmanType);

                if (!empty($userSetBombInfo)) {
                    foreach ($userSetBombInfo as $value) {
                        $dalItem->updateUserHitmanBomb($value['bomb_uid'], $value['bomb_hitman']);
                    }
                }

                if (!empty($whoSetBombToUser)) {
                    foreach ($whoSetBombToUser as $value) {
                        $dalItem->updateEnemyBombCount($value['uid'], $value['count']);
                    }
                }

                $this->_wdb->commit();
                $result = 1;
            }
            catch (Exception $e) {
                $this->_wdb->rollBack();
                $result = -2;
            }

            //insert minifeed
            try {
                if ($result == 1) {
                    $create_time = date('Y-m-d H:i:s');

                    require_once 'Bll/User.php';
                    $userInfo = Bll_User::getPerson($uid);
                    $userName = $userInfo->getDisplayName();

                    $minifeed = array('uid' => $uid,
                                      'template_id' => 42,
                                      'actor' => $uid,
                                      'target' => '',
                                      'feed_type' => $userName . '組、結成',
                                      'icon' => Zend_Registry::get('static') . "/apps/dynamite/img/icon/hitman.gif",
                                      'title' => '',
                                      'create_time' => $create_time);

                    require_once 'Dal/Dynamite/Feed.php';
                    $dalDynamiteFeed = Dal_Dynamite_Feed::getDefaultInstance();

                    $feedTable = $this->getFeedTable($minifeed['uid']);
                    $dalDynamiteFeed->insertFeed($minifeed, $feedTable);
                }
            }
            catch (Exception $e1) {

            }
        }
        return $result;
    }

    /**
     * today frist login,send card
     *
     * @param string $uid
     * @return array
     */
    public function getGift($uid, $fromTriggerBomb = 0)
    {
        require_once 'Dal/Dynamite/Item.php';
        $dalItem = Dal_Dynamite_Item::getDefaultInstance();
        $helpCardCount = $dalItem->haveThisCard($uid, 10);

        require_once 'Dal/Dynamite/User.php';
        $dalUser = Dal_Dynamite_User::getDefaultInstance();
        $userInfo = $dalUser->getUser($uid);

        if (($userInfo['hitman_type'] != 11) && ($helpCardCount == 0) && ($userInfo['dead_number'] >= 3) && ($userInfo['bonus'] < 100) && (!$fromTriggerBomb)) {
            $cid = 10;
            $dalItem->updateUserCard($uid, $cid, 1);
        }
        else {
            $i = rand(1, 100);

            //get card id, cid<=10, send card, 11<=cid<=13 send bomb, cid>=14, send money
            switch ($i) {
                //send card
                case $i < 11 :
                    $cid = 1;
                    break;
                case 10 < $i && $i < 21 :
                    $cid = 2;
                    break;
                case 20 < $i && $i < 31 :
                    $cid = 3;
                    break;
                case 30 < $i && $i < 36 :
                    $cid = 4;
                    break;
                case 35 < $i && $i < 37 :
                    $cid = 5;
                    break;
                case 36 < $i && $i < 47 :
                    $cid = 6;
                    break;
                case 46 < $i && $i < 52 :
                    $cid = 7;
                    break;
                //send money
                case 51 < $i && $i < 72 :
                    $cid = 50;
                    break;
                case 71 < $i && $i < 79 :
                    $cid = 51;
                    break;
                case 78 < $i && $i < 84 :
                    $cid = 52;
                    break;
                //send bomb
                case 83 < $i && $i < 91 :
                    $cid = 60;
                    break;
                case 90 < $i && $i < 96 :
                    $cid = 61;
                    break;
                case 95 < $i && $i < 101 :
                    $cid = 62;
                    break;
                default :
                    break;
            }
            if ($cid <= 7) {
                //if user have had 1 haoyi card, can't get another haoyi card
                if ($cid == 5) {
                    $haoyiCardCount = $dalItem->haveThisCard($uid, 5);
                    if ($haoyiCardCount >= 1) {
                        return $this->getGift($uid);
                    }
                    else {
                        $dalItem->updateUserCard($uid, 5, 1);
                    }
                }
                else {
                    //add card count
                    $dalItem->updateUserCard($uid, $cid, 1);
                }
            }
            else {
                switch ($cid) {
                    case 50 :
                        $money = 500;
                        break;
                    case 51 :
                        $money = 1000;
                        break;
                    case 52 :
                        $money = 3000;
                        break;
                    case 60 :
                        $bombNum = 2;
                        break;
                    case 61 :
                        $bombNum = 5;
                        break;
                    case 62 :
                        $bombNum = 10;
                        break;
                    default :
                        break;
                }

                //update user bonus
                if ($cid >= 50 && $cid <= 52) {
                    $myBonus = $dalUser->getUserBonus($uid);
                    if ($myBonus < $this->_maxBonus) {
                        $money = $myBonus + $money > $this->_maxBonus ? ($this->_maxBonus - $myBonus) : $money;
                        $dalUser->updateUserBonus($uid, $money);
                    }
                }
                //update user bomb count
                if ($cid >= 60 && $cid <= 62) {
                    $userBombCount = $dalItem->getUserBombCountForUpdate($uid);

                    if ($userBombCount != $this->_maxBombCount) {
                        $updateCount = ($userBombCount + $bombNum) > $this->_maxBombCount ? $this->_maxBombCount - $userBombCount : $bombNum;
                        $dalUser->updateUserBombCountAndRemainBombCount($uid, $updateCount);
                    }
                }
            }
        }
        return $cid;
    }

    /**
     * if user's bomb count=0, auto send bomb to user
     *
     * @param string $uid
     * @return boolean
     */
    public function sendBombToUser($uid)
    {

        $sendBombCount = 4;
        return $sendBombCount;
    }

    /**
     * get feed table by uid
     * @param string $uid
     * @return String
     */
    public function getFeedTable($uid)
    {
        $tableFlag = substr($uid, strlen($uid) - 1);

        return 'dynamite_feed_' . $tableFlag;
    }

    /**
     * auto refresh page every 1 minite
     * @param string $uid
     * @param string $targetUid
     * @author lp
     * @return integer
     */
    public function autoRefreshUserInfo($uid, $targetUid)
    {

        /**
         * get user bomb infomation
         */
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

        require_once 'Bll/Cache/Dynamite.php';
        $hitmanInfo = Bll_Cache_Dynamite::getHitmanType();
        $myDynamite = array_merge($myDynamite, $hitmanInfo[$myDynamite['hitman_type'] - 1]);

        $userRemoveBomb = $this->getUserRemoveBomb($myDynamite);
        $emptyBombCount = $this->_maxBombCount - $myDynamite['bomb_count'];

        //remainderBombCount-> bomb which power=0
        $bombInfo = array('userBomb' => $userBomb,
                          'userRemoveBomb' => $userRemoveBomb,
                          'remainderBombCount' => ($myDynamite['remainder_bomb_count'] - count($userRemoveBomb)),
                          'allRemainderCount' => $myDynamite['remainder_bomb_count'],
                          'emptyBombCount' => $emptyBombCount,
                          'myReward' => $myDynamite['reward'],
                          'bonus' => $myDynamite['bonus']);
        /**
         * get target user house infomation
         */
        require_once 'Dal/Dynamite/User.php';
        $dalDynamiteUser = Dal_Dynamite_User::getDefaultInstance();
        //get user dynamite info
        $userDynamite = $dalDynamiteUser->getUser($targetUid);
        //$userDynamite = array_merge($userDynamite, $hitmanInfo[$userDynamite['hitman_type'] - 1]);
        $userDynamite['pic_id'] = $hitmanInfo[$userDynamite['hitman_type'] - 1]['pic_id'];
        $userDynamite['max_life'] = $hitmanInfo[$userDynamite['hitman_type'] - 1]['max_life'];

        if (!$userDynamite) {
            $targetUid = $uid;
        }

        //get user dynamite, back current and next
        $result = $this->getUserDynamite($uid, $targetUid);

        $targetUserHouseInfo = array('userDynamite' => $userDynamite, 'current' => $result['current']);

        $response = array('bombInfo' => $bombInfo, 'userInfo' => $targetUserHouseInfo);

        return $response;
    }

    /**
     * get user removed bomb
     * @param array $myDynamite
     * @return array
     */
    public function getUserRemoveBomb($myDynamite)
    {
        $userRemoveBomb = array();

        for ($i = 0; $i < $myDynamite['bomb_power5']; $i++) {
            $userRemoveBomb[]['bomb_power'] = 5;
        }
        for ($i = 0; $i < $myDynamite['bomb_power4']; $i++) {
            $userRemoveBomb[]['bomb_power'] = 4;
        }
        for ($i = 0; $i < $myDynamite['bomb_power3']; $i++) {
            $userRemoveBomb[]['bomb_power'] = 3;
        }
        for ($i = 0; $i < $myDynamite['bomb_power2']; $i++) {
            $userRemoveBomb[]['bomb_power'] = 2;
        }
        for ($i = 0; $i < $myDynamite['bomb_power1']; $i++) {
            $userRemoveBomb[]['bomb_power'] = 1;
        }

        return $userRemoveBomb;
    }

    /**
     * set select chara
     * @param String $uid
     * @param integer $hitmanType
     * @return integer
     */
    public function setChara($uid, $hitmanType)
    {
        $result = -1;

        require_once 'Bll/Cache/Dynamite.php';
        $allHitmanInfo = Bll_Cache_Dynamite::getHitmanType();

        if ($hitmanType == 1 || $hitmanType == 2) {
            $powerTime = $allHitmanInfo[0]['power_time'];
        }
        else if ($hitmanType == 3) {
            $powerTime = $allHitmanInfo[2]['power_time'];
        }

        try {
            require_once 'Bll/Dynamite/User.php';
            $bllUser = new Bll_Dynamite_User();

            $bllUser->setAlive($uid, $hitmanType);

            require_once 'Dal/Dynamite/Bomb.php';
            $dalDynamiteBomb = Dal_Dynamite_Bomb::getDefaultInstance();

            $nowTime = time();
            $dalDynamiteBomb->updateBombPowerTime($uid, $powerTime, $nowTime);

            $result = 1;
        }
        catch (Exception $e) {
            return $result;
        }

        return $result;
    }

    /**
     * auto recover user hitman blood, get user current blood
     * @param String $uid
     * @param integer $hitmanType
     * @return integer
     */
    public function getUserCurrentBlood($uid)
    {
        require_once 'Dal/Dynamite/User.php';
        $dalUser = Dal_Dynamite_User::getDefaultInstance();

        $updateUid = $dalUser->getUpdateLifeUid($uid);

        require_once 'Bll/Cache/Dynamite.php';
        $hitmanInfo = Bll_Cache_Dynamite::getHitmanType();
        $maxLife = $hitmanInfo[$updateUid['hitman_type'] - 1]['max_life'];

        if ($updateUid) {
            $this->_wdb->beginTransaction();
            try {
                //update all hitman self
                for ($j = 1; $j < 5; $j++) {
                    $dalUser->updateHitmanLife($j, $updateUid['uid'], $maxLife);
                }

                //update user last update life time
                $dalUser->updateUserBasicInfo($updateUid['uid'], array('last_update_life_time' => time()));
                $this->_wdb->commit();
            }
            catch (Exception $e) {
                $this->_wdb->rollBack();
            }
        }
    }

    /**
     * get gift, if user's bomb count is zero after user trigger bomb
     *
     * @param string $uid
     * @return array
     */
    public function getGiftWhenBombCountIsZero($uid)
    {
        require_once 'Dal/Dynamite/Item.php';
        $dalItem = Dal_Dynamite_Item::getDefaultInstance();

        $haoyiCardCount = $dalItem->haveThisCard($uid, 5);
        $cid = $this->getCid($haoyiCardCount >= 1);

        require_once 'Dal/Dynamite/User.php';
        $dalUser = Dal_Dynamite_User::getDefaultInstance();

        if ($cid <= 7) {
            //add card count
            $dalItem->updateUserCard($uid, $cid, 1);
        }
        else {
            switch ($cid) {
                case 50 :
                    $money = 500;
                    break;
                case 51 :
                    $money = 1000;
                    break;
                case 52 :
                    $money = 3000;
                    break;
                case 61 :
                    $bombNum = 5;
                    break;
                case 62 :
                    $bombNum = 10;
                    break;
                default :
                    break;
            }
            //update user bonus
            if ($cid >= 50 && $cid <= 52) {
                $myBonus = $dalUser->getUserBonus($uid);
                if ($myBonus < $this->_maxBonus) {
                    $money = $myBonus + $money > $this->_maxBonus ? ($this->_maxBonus - $myBonus) : $money;
                    $dalUser->updateUserBonus($uid, $money);
                }
            }
            //update user bomb count
            if ($cid == 61 || $cid == 62) {
                $userBombCount = $dalItem->getUserBombCountForUpdate($uid);

                if ($userBombCount != $this->_maxBombCount) {
                    $updateCount = ($userBombCount + $bombNum) > $this->_maxBombCount ? $this->_maxBombCount - $userBombCount : $bombNum;
                    $dalUser->updateUserBombCountAndRemainBombCount($uid, $updateCount);
                }
            }
        }

        return $cid;
    }

    public function getCid($haveHaoyiCard)
    {
        $i = rand(1, 100);
        //get card id, cid<=10, send card, 11<=cid<=13 send bomb, cid>=14, send money
        if (!$haoyiCardCount) {
            switch ($i) {
                //send card
                case $i < 11 :
                    $cid = 1;
                    break;
                case 10 < $i && $i < 21 :
                    $cid = 2;
                    break;
                case 20 < $i && $i < 31 :
                    $cid = 3;
                    break;
                case 30 < $i && $i < 36 :
                    $cid = 4;
                    break;
                case 35 < $i && $i < 46 :
                    $cid = 6;
                    break;
                case 45 < $i && $i < 51 :
                    $cid = 7;
                    break;
                //send money
                case 50 < $i && $i < 71 :
                    $cid = 50;
                    break;
                case 70 < $i && $i < 78 :
                    $cid = 51;
                    break;
                case 77 < $i && $i < 83 :
                    $cid = 52;
                    break;
                //send bomb
                case 82 < $i && $i < 93 :
                    $cid = 61;
                    break;
                case 92 < $i && $i < 101 :
                    $cid = 62;
                    break;

                default :
                    break;
            }
        }
        else {
            switch ($i) {
                //send card
                case $i < 11 :
                    $cid = 1;
                    break;
                case 10 < $i && $i < 21 :
                    $cid = 2;
                    break;
                case 20 < $i && $i < 31 :
                    $cid = 3;
                    break;
                case 30 < $i && $i < 36 :
                    $cid = 4;
                    break;
                case 35 < $i && $i < 46 :
                    $cid = 6;
                    break;
                case 45 < $i && $i < 51 :
                    $cid = 7;
                    break;
                //send money
                case 50 < $i && $i < 71 :
                    $cid = 50;
                    break;
                case 70 < $i && $i < 78 :
                    $cid = 51;
                    break;
                case 77 < $i && $i < 83 :
                    $cid = 52;
                    break;
                //send bomb
                case 82 < $i && $i < 92 :
                    $cid = 61;
                    break;
                case 91 < $i && $i < 100 :
                    $cid = 62;
                    break;
                //send haoyi card
                case 99 < $i && $i < 101 :
                    $cid = 5;
                    break;
                default :
                    break;
            }
        }
        return $cid;
    }

}

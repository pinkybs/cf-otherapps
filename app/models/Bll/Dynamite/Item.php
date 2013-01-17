<?php

require_once 'Bll/Abstract.php';

/**
 * dynamite item logic's Operation
 *
 * @package    Bll
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/07/07    lp
 */
class Bll_Dynamite_Item extends Bll_Abstract
{
    private $_maxBombCount = 26;
    /**
     * get item list
     * @author lp
     * @param integer $uid
     * @return array
     */
    public function getItemList($uid)
    {
        require_once 'Dal/Dynamite/Item.php';
        $dalItem = Dal_Dynamite_Item::getDefaultInstance();
        $itemList = $dalItem->getItemList($uid);

        require_once 'Bll/Cache/Dynamite.php';
        $itemInfo = Bll_Cache_Dynamite::getItemInfo();

        for ($i = 1; $i <= count($itemList); $i++) {
            if ($itemList[i]['cid'] == $itemInfo[$i - 1]['cid']) {
                $itemList[i]['name'] = $itemInfo[$i - 1]['cid'];
                $itemList[i]['introduce'] = $itemInfo[$i - 1]['introduce'];
            }
        }

        $refuseBombTime = $dalItem->getUserRefuseBombTime($uid);
        $useTime = (time() - $refuseBombTime) / 3600;
        foreach ($itemList as $key => $value) {
            if ($value['cid'] == 5) {
                $itemList[$key]['useTime'] = $useTime;
            }
            if ($value['cid'] == 10) {
                $bonus = $dalItem->getUserBonusByUid($uid);

                $bonus < 1000 ? $itemList[$key]['canUse'] = 1 : $itemList[$key]['canUse'] = 0;
            }
        }
        return $itemList;
    }

    /**
     * use recover blood card
     * @author lp
     * @param integer $hitman
     * @param integer $uid
     * @param integer $cid
     * @return integer
     */
    public function useRecoverBloodCard($hitmanId, $uid, $cid)
    {
        $result = -1;

        if ($cid < 1 || $cid > 9) {
            return $result;
        }

        if ($hitmanId < 1 || $hitmanId > 4) {
            return $result;
        }

        require_once 'Dal/Dynamite/Item.php';
        $dalItem = Dal_Dynamite_Item::getDefaultInstance();

        $maxLife = $dalItem->getMaxLifeByUid($uid);

        //if hitmanBloodCount==0, the hitman dead
        $hitmanBloodCount = $dalItem->getHitmanBloodCount($hitmanId, $uid);
        if ($hitmanBloodCount == 0 || $hitmanBloodCount == $maxLife) {
            return $result;
        }

        //check if user have relevant card
        $cardCount = $dalItem->haveThisCard($uid, $cid);
        if ($cardCount == 0) {
            return $result;
        }

        try {
            $this->_wdb->beginTransaction();

            if ($cid == 1) {
                $hitmanBloodCount = $hitmanBloodCount + 4 > $maxLife ? $maxLife : $hitmanBloodCount + 4;
            }
            else if ($cid == 2) {
                $hitmanBloodCount = $maxLife;
            }

            $dalItem->updataHitmanBlood($uid, $hitmanId, $hitmanBloodCount);
            $dalItem->updateUserCard($uid, $cid, -1);

            $this->_wdb->commit();
            $result = 1;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return $result;
        }
        return $result;
    }

    /**
     * recover all user's and alliance's hitman blood
     * @author lp
     * @param integer $uid
     * @return integer
     */
    public function useRecoverUserAndAllianceCard($uid)
    {
        $result = -1;

        require_once 'Dal/Dynamite/Item.php';
        $dalItem = Dal_Dynamite_Item::getDefaultInstance();

        //check if user have relevant card
        $cardCount = $dalItem->haveThisCard($uid, 3);
        if ($cardCount == 0) {
            return $result;
        }
        //check if user dead
        $hitmanCount = $dalItem->getHitmanCount($uid);
        if ($hitmanCount == 0) {
            return $result;
        }

        $maxLife = $dalItem->getMaxLifeByUid($uid);
        $allHitManBlood = $dalItem->getAllHitmanBlood($uid);
        $bloodCount = $allHitManBlood['hitman_life1'] + $allHitManBlood['hitman_life2'] + $allHitManBlood['hitman_life3'] + $allHitManBlood['hitman_life4'];
        if ($bloodCount == (4 * $maxLife)) {
            return -2;
        }

        $canNotRecoverNum = 0;
        try {
            $this->_wdb->beginTransaction();

            $hitmanArray = array();
            for ($i = 1; $i <= 4; $i++) {
                if ($allHitManBlood['hitman_life' . $i] != 0 && $allHitManBlood['hitman_life' . $i] != $maxLife) {
                    $canNotRecoverNum++;
                    $hitmanArray['hitman_life' . $i] = $maxLife;
                }
            }
            if ($canNotRecoverNum == 0) {
                $this->_wdb->rollBack();
                return -2;
            }

            require_once 'Dal/Dynamite/User.php';
            $dalUser = Dal_Dynamite_User::getDefaultInstance();
            $dalUser->updateUserMoreInfo($uid, $hitmanArray);

            $dalItem->updateUserCard($uid, 3, -1);

            $this->_wdb->commit();
            $result = 1;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return $result;
        }

        return $result;
    }

    /**
     * use confiscate bomb Card
     * @author lp
     * @param integer $uid
     * @return integer
     */
    public function useConfiscateBombCard($uid)
    {
        $result = -1;

        require_once 'Dal/Dynamite/Item.php';
        $dalItem = Dal_Dynamite_Item::getDefaultInstance();
        $cardCount = $dalItem->haveThisCard($uid, 5);

        if ($cardCount == 0) {
            return $result;
        }
        //check if user have use this card in 3 hours
        $refuseBombTime = $dalItem->getUserRefuseBombTime($uid);
        $useTime = (time() - $refuseBombTime) / 3600;
        if ($useTime <= 3) {
            return -3;
        }

        require_once 'Dal/Dynamite/User.php';
        $dalUser = Dal_Dynamite_User::getDefaultInstance();

        try {
            $this->_wdb->beginTransaction();

            require_once 'Dal/Dynamite/Bomb.php';
            $dalDynamiteBomb = Dal_Dynamite_Bomb::getDefaultInstance();
            $bombInfo = $dalDynamiteBomb->getUserHitmanBombForUpdate($uid);

            $installedBombCount = count($bombInfo);
            //who install bomb in user's house
            $installedBombUser = $dalItem->getInstallBombUserForUpdate($uid);

            //user bomb count
            $bombCount = $dalItem->getUserBombCountForUpdate($uid);

            if (!empty($bombInfo)) {
                //update user bomb
                if ($bombCount < $this->_maxBombCount) {
                    $updateCount = ($bombCount + $installedBombCount) > $this->_maxBombCount ? $this->_maxBombCount - $bombCount : $installedBombCount;

                    $dalUser->updateUserBombCountAndRemainBombCount($uid, $updateCount);
                }
            }

            //delete bomb
            if ($bombInfo) {
                require_once 'Dal/Dynamite/Bomb.php';
                $dalBomb = Dal_Dynamite_Bomb::getDefaultInstance();

                foreach ($bombInfo as $bomb) {
                    if ($bomb['bomb_power'] > 0) {
                        $removeBombPower = $bomb['bomb_power'];
                        //insert remove bomb
                        if ($bombCount < $this->_maxBombCount) {
                            $dalBomb->updatePowerBombCount($uid, $removeBombPower, 1);
                            $bombCount++;
                        }
                    }

                    //update uid bomb count
                    $dalUser->updateUserBombCount($bomb['uid'], -1);
                    //delete bomb
                    $dalDynamiteBomb->deleteBombById($bomb['bomb_id']);
                }
            }

            //clean user's house bomb
            $userUpdateInfo = array('hitman_bomb_count1' => 0,
                                    'hitman_bomb_count2' => 0,
                                    'hitman_bomb_count3' => 0,
                                    'hitman_bomb_count4' => 0
                                    );

            $dalUser->updateUserMoreInfo($uid, $userUpdateInfo);
            $dalUser->updateUserBasicInfo($uid, array('refuse_bomb_time' => time()));

            $dalItem->updateUserCard($uid, 5, -1);

            $this->_wdb->commit();
            $result = 1;

        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return $result;
        }

        try {
            //insert into minifeed
            if ($result == 1) {

                $create_time = date('Y-m-d H:i:s');

                $minifeed = array('uid' => $uid,
                                  'template_id' => 57,
                                  'target' => '',
                                  'actor' => $uid,
                                  'feed_type' => 'ダイナマイトほいほいを使用',
                                  'icon' => Zend_Registry::get('static') . "/apps/dynamite/img/icon/dynamite.gif",
                                  'title' => '',
                                  'create_time' => $create_time);

                require_once 'Bll/Dynamite/Index.php';
                $bllIndex = new Bll_Dynamite_Index();
                $feedTable = $bllIndex->getFeedTable($minifeed['uid']);

                require_once 'Dal/Dynamite/Feed.php';
                $dalDynamiteFeed = Dal_Dynamite_Feed::getDefaultInstance();
                $dalDynamiteFeed->insertFeed($minifeed, $feedTable);

                if (!empty($installedBombUser)) {
                    foreach ($installedBombUser as $value) {
                        $minifeed['uid'] = $value['uid'];
                        $feedTable = $bllIndex->getFeedTable($minifeed['uid']);
                        $dalDynamiteFeed->insertFeed($minifeed, $feedTable);
                    }
                }
            }
        }
        catch (Exception $e1) {

        }

        return $result;
    }

    /**
     * use revive card
     * @author lp
     * @param integer $uid
     * @return integer
     */
    public function useReviveCard($uid)
    {
        $response = array('result' => -1);

        require_once 'Dal/Dynamite/Item.php';
        $dalItem = Dal_Dynamite_Item::getDefaultInstance();
        $cardCount = $dalItem->haveThisCard($uid, 6);

        if ($cardCount == 0) {
            return $response;
        }

        $maxLife = $dalItem->getMaxLifeByUid($uid);

        //check if user have a live hitman
        $allHitManBlood = $dalItem->getAllHitmanBlood($uid);
        //0 -> all hitman have dead
        $sumBlood = 0;
        foreach ($allHitManBlood as $key => $value) {
            $sumBlood = $sumBlood + $value;

            if ($value != 0) {
                unset($allHitManBlood[$key]);
            }
        }
        //all hitman dead
        if ($sumBlood == 0) {
            return $response = array('result' => -4);
        }
        //no hitman need revive
        if (empty($allHitManBlood) && $sumBlood != (4 * $maxLife)) {
            return $response = array('result' => -2);
        }
        //all hitman have max bloods
        if ($sumBlood == (4 * $maxLife)) {
            return $response = array('result' => -3);
        }
        try {

            $this->_wdb->beginTransaction();

            $hitmanArray = array_keys($allHitManBlood);
            if (!empty($hitmanArray)) {
                $hitmanArrayCount = count($hitmanArray);
                /*hitmanArrayCount=1 -> one hitman could be revive,
                  hitmanArrayCount=2 -> two hitman could be revive,
                  hitmanArrayCount=3 -> three hitman could be revive,
                */
                if ($hitmanArrayCount == 1) {
                    $hitman = $hitmanArray[0];
                    $hitmanId = substr($hitmanArray[0], strlen($hitmanArray[0]) - 1, strlen($hitmanArray[0]));
                }
                else if ($hitmanArrayCount == 2) {
                    $rand = rand(0, 1);
                    $randResult = $rand;

                    $hitman = $hitmanArray[$randResult];
                    $hitmanId = substr($hitmanArray[$randResult], strlen($hitmanArray[$randResult]) - 1, strlen($hitmanArray[$rand]));
                }
                else if ($hitmanArrayCount == 3) {
                    $rand = rand(0, 2);
                    $randResult = $rand;

                    $hitman = $hitmanArray[$randResult];
                    $hitmanId = substr($hitmanArray[$randResult], strlen($hitmanArray[$randResult]) - 1, strlen($hitmanArray[$randResult]));
                }

                require_once 'Dal/Dynamite/User.php';
                $dalDynamiteUser = Dal_Dynamite_User::getDefaultInstance();

                $userUpdateInfo = array($hitman => $maxLife,
                                        'hitman_dead_time' . $hitmanId => 0,
                                        'hitman_count' => (4- $hitmanArrayCount) + 1);
                $dalDynamiteUser->updateUserMoreInfo($uid, $userUpdateInfo);
            }
            $dalItem->updateUserCard($uid, 6, -1);

            $this->_wdb->commit();
            $response['result'] = 1;

        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return $response = array('result' => -1);
        }

        try {
            if ($response['result'] == 1) {
                //insert into minifeed
                $create_time = date('Y-m-d H:i:s');

                $minifeed = array('uid' => $uid,
                                  'template_id' => 49,
                                  'actor' => $uid,
                                  'target' => '',
                                  'feed_type' => 'ヒットマン復活',
                                  'icon' => Zend_Registry::get('static') . "/apps/dynamite/img/icon/hitman.gif",
                                  'title' => '',
                                  'create_time' => $create_time);

                require_once 'Bll/Dynamite/Index.php';
                $bllIndex = new Bll_Dynamite_Index();
                $feedTable = $bllIndex->getFeedTable($minifeed['uid']);

                require_once 'Dal/Dynamite/Feed.php';
                $dalDynamiteFeed = Dal_Dynamite_Feed::getDefaultInstance();
                $dalDynamiteFeed->insertFeed($minifeed, $feedTable);
            }
        }
        catch (Exception $e1) {

        }
        return $response;
    }

    /**
     * use  final weapon card
     * @author lp
     * @param integer $uid
     * @return integer
     */
    public function useFinalWeaponCard($uid)
    {
        $response = array('result' => -1);

        require_once 'Dal/Dynamite/Item.php';
        $dalItem = Dal_Dynamite_Item::getDefaultInstance();
        $cardCount = $dalItem->haveThisCard($uid, 7);

        if ($cardCount == 0) {
            return $response;
        }

        try {
            $this->_wdb->beginTransaction();

            require_once 'Dal/Dynamite/User.php';
            $dalUser = Dal_Dynamite_User::getDefaultInstance();

            $userMoreInfo = $dalUser->getUserMoreInfoForUpdate($uid);
            //get user bomb count
            $bombCount = $userMoreInfo['bomb_count'];
            //get user remainder_bomb_count
            $remainBombCount = $userMoreInfo['remainder_bomb_count'];

            //user have 26 bomb
            if ($bombCount == $this->_maxBombCount) {
                $this->_wdb->rollBack();
                return $response = array('result' => -2);
            }

            $dalUser->updateUserMoreInfo($uid, array('bomb_count' => $this->_maxBombCount, 'remainder_bomb_count' => ($this->_maxBombCount - $bombCount + $remainBombCount)));
            $dalItem->updateUserCard($uid, 7, -1);

            $this->_wdb->commit();
            $response['result'] = 1;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return $response;
        }

        try {
            if ($response['result'] == 1) {
                //insert into minifeed
                $create_time = date('Y-m-d H:i:s');

                require_once 'Bll/User.php';
                $userInfo = Bll_User::getPerson($uid);

                $minifeed = array('uid' => $uid,
                                  'template_id' => 51,
                                  'actor' => $uid,
                                  'target' => '',
                                  'feed_type' => $userInfo->getDisplayName() . '組、最終兵器を獲得',
                                  'icon' => Zend_Registry::get('static') . "/apps/dynamite/img/icon/dynamite.gif",
                                  'title' => '',
                                  'create_time' => $create_time);

                require_once 'Bll/Dynamite/Index.php';
                $bllIndex = new Bll_Dynamite_Index();
                $feedTable = $bllIndex->getFeedTable($minifeed['uid']);

                require_once 'Dal/Dynamite/Feed.php';
                $dalDynamiteFeed = Dal_Dynamite_Feed::getDefaultInstance();
                $dalDynamiteFeed->insertFeed($minifeed, $feedTable);

                require_once 'Bll/Dynamite/Activity.php';
                $response['activity'] = Bll_Dynamite_Activity::getActivity($uid, '', 8);
                $response['activity_pic'] = Zend_Registry::get('static') . "/apps/dynamite/img/activity_image/7.gif";
            }
        }
        catch (Exception $e1) {

        }
        return $response;
    }

    /**
     * use  angry card
     * @author lp
     * @param integer $uid
     * @return integer
     */
    public function useAngryCard($uid)
    {
        $result = array('status' => -1);

        require_once 'Dal/Dynamite/Item.php';
        $dalItem = Dal_Dynamite_Item::getDefaultInstance();
        $cardCount = $dalItem->haveThisCard($uid, 10);

        if ($cardCount == 0) {
            return $result;
        }

        require_once 'Dal/Dynamite/User.php';
        $dalUser = Dal_Dynamite_User::getDefaultInstance();
        //get user  info
        $userDynamite = $dalUser->getUser($uid);

        if ($userDynamite['bonus'] >= 1000) {
            return $result;
        }

        if ($userDynamite['hitman_type'] == 11) {
            return $result;
        }

        require_once 'Bll/Cache/Dynamite.php';
        $hitmanInfo = Bll_Cache_Dynamite::getHitmanType();
        $newPowerTime = $hitmanInfo[count($hitmanInfo)-1]['power_time'];
        $userDynamite['max_life'] = $hitmanInfo[count($hitmanInfo)-1]['max_life'];

        try {
            $this->_wdb->beginTransaction();

            //update user hitmanType and show set bomb message flag
            $info = array('hitman_type' => 11, 'show_set_bomb' => 0);
            $dalUser->updateUserBasicInfo($uid, $info);

            //update user bomb power time
            $nowTime = time();
            require_once 'Dal/Dynamite/Bomb.php';
            $dalBomb = Dal_Dynamite_Bomb::getDefaultInstance();
            $dalBomb->updateBombPowerTime($uid, $newPowerTime, $nowTime);

            //update user card count
            $dalItem->updateUserCard($uid, 10, -1);

            $this->_wdb->commit();
            $result['status'] = 1;

        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return array('status' => -1);
        }

        try {
            if ($result['status'] == 1) {
                //get user hitman pic type
                $hitmanPicType = 'b';
                for ($i = 1; $i < 5; $i++) {
                    $hitmanId = 'hitman_life' . $i;
                    if ($userDynamite[$hitmanId] >= ($userDynamite['max_life'] / 2)) {
                        $hitmanPicType = 'a';
                        break;
                    }
                }
                $result['hitmanPicType'] = $hitmanPicType;

                //insert feed
                require_once 'Bll/User.php';
                $userInfo = Bll_User::getPerson($uid);

                $create_time = date('Y-m-d H:i:s');

                $minifeed = array('uid' => $uid,
                                  'template_id' => 60,
                                  'actor' => $uid,
                                  'target' => '',
                                  'feed_type' => $userInfo->getDisplayName() . '組、神々の怒りを味方に',
                                  'icon' => Zend_Registry::get('static') . "/apps/dynamite/img/icon/hitman.gif",
                                  'title' => '',
                                  'create_time' => $create_time);

                require_once 'Bll/Dynamite/Index.php';
                $bllIndex = new Bll_Dynamite_Index();
                $feedTable = $bllIndex->getFeedTable($minifeed['uid']);

                require_once 'Dal/Dynamite/Feed.php';
                $dalDynamiteFeed = Dal_Dynamite_Feed::getDefaultInstance();
                $dalDynamiteFeed->insertFeed($minifeed, $feedTable);

                //send  activity
                require_once 'Bll/Dynamite/Activity.php';
                $result['activity'] = Bll_Dynamite_Activity::getActivity($uid, '', 14);
                $result['activity_pic'] = Zend_Registry::get('static') . "/apps/dynamite/img/activity_image/10.gif";
            }
        }
        catch (Exception $e1) {

        }

        return $result;
    }

    /**
     * revive user and user's alliance all hitman
     * @author lp
     * @param integer $uid
     * @return integer
     */
    public function useAllReviveCard($uid)
    {
        $response = array('result' => -1);

        require_once 'Dal/Dynamite/Item.php';
        $dalItem = Dal_Dynamite_Item::getDefaultInstance();
        $cardCount = $dalItem->haveThisCard($uid, 4);
        //check have this card
        if ($cardCount == 0) {
            return $response;
        }

        //check if user have a live hitman
        $allHitManBlood = $dalItem->getAllHitmanBlood($uid);

        //0 -> all hitman have dead
        $sumBlood = 0;
        foreach ($allHitManBlood as $key => $value) {
            $sumBlood = $sumBlood + $value;

            if ($value != 0) {
                unset($allHitManBlood[$key]);
            }
        }
        //all hitman dead
        if ($sumBlood == 0) {
            return $response;
        }
        //no hitman need revive
        if (empty($allHitManBlood)) {
            return $response = array('result' => -2);
        }

        try {
            $this->_wdb->beginTransaction();

            $maxLife = $dalItem->getMaxLifeByUid($uid);
            $updateInfo = array('hitman_life1' => $maxLife,
                                'hitman_life2' => $maxLife,
                                'hitman_life3' => $maxLife,
                                'hitman_life4' => $maxLife,
                                'hitman_dead_time1' => 0,
                                'hitman_dead_time2' => 0,
                                'hitman_dead_time3' => 0,
                                'hitman_dead_time4' => 0,
                                'hitman_count' => 4);

            require_once 'Dal/Dynamite/User.php';
            $dalUser = Dal_Dynamite_User::getDefaultInstance();
            $dalUser->updateUserMoreInfo($uid, $updateInfo);

            $dalItem->updateUserCard($uid, 4, -1);

            $this->_wdb->commit();
            $response['result'] = 1;

            require_once 'Bll/Dynamite/Activity.php';
            $response['activity'] = Bll_Dynamite_Activity::getActivity($uid, '', 9);
            $response['activity_pic'] = Zend_Registry::get('static') . "/apps/dynamite/img/item/s/4.gif";
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return $response;
        }

        return $response;

    }

    /**
     * use change game mode card
     * @author lp
     * @param integer $uid
     * @return integer
     */
    public function changeModeToFriend($uid)
    {
        $result = array('status' => -1);

        require_once 'Dal/Dynamite/Item.php';
        $dalItem = Dal_Dynamite_Item::getDefaultInstance();
        $cardCount = $dalItem->haveThisCard($uid, 8);
        //check have this card
        if ($cardCount == 0) {
            return $result;
        }

        //check user game mode
        $gameMode = $dalItem->getUserGameMode($uid);
        if ($gameMode == 1) {
            return array('status' => -3);
        }

        //check if friends count > 5
        require_once 'Bll/Friend.php';
        $fids = Bll_Friend::getFriends($uid);

        $appFriendCount = 0;
        if (!empty($fids)) {
            $appFriendCount = $dalItem->getFriendCountInApp($fids);
        }

        if ($appFriendCount < 5) {
            return array('status' => -2);
        }

        try {
            $this->_wdb->beginTransaction();

            //user set bomb to other
            $userSetBombInfo = $dalItem->getUserSetBombInfoForUpdate($uid);
            //who have set bomb to user
            $whoSetBombToUser = $dalItem->whoInstallBombInUserAndAlliance($uid);

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
            //delete bomb -> user set and other people set to user
            $dalItem->deleteBombAboutUser($uid);
            //update user's hitman bomb
            require_once 'Dal/Dynamite/User.php';
            $dalUser = Dal_Dynamite_User::getDefaultInstance();

            $info = array('hitman_bomb_count1' => 0,
                          'hitman_bomb_count2' => 0,
                          'hitman_bomb_count3' => 0,
                          'hitman_bomb_count4' => 0);
            $dalUser->updateUserMoreInfo($uid, $info);
            //update user bomb count
            $dalUser->updateUserBombCount($uid, -count($userSetBombInfo));
            //update user game mode
            $modeInfo = array('game_mode' => 1);
            $dalUser->updateUserBasicInfo($uid, $modeInfo);

            $dalItem->updateUserCard($uid, 8, -1);

            //get use new bomb count
            $newBombCount = $dalItem->getUserBombCountForUpdate($uid);
            if ($newBombCount == 0) {
                $result['sendBomb'] = 1;
                //if user's bomb count = 0 , auto send 4 bombs to user
                $dalUser->updateUserBombCountAndRemainBombCount($uid, 4);
            }

            $this->_wdb->commit();

            $result['status'] = 1;

        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return array('status' => -1);
        }
        return $result;
    }

    /**
     * use change game mode card
     * @author lp
     * @param integer $uid
     * @return integer
     */
    public function changeModeToAll($uid)
    {
        $result = array('status' => -1);

        require_once 'Dal/Dynamite/Item.php';
        $dalItem = Dal_Dynamite_Item::getDefaultInstance();
        $cardCount = $dalItem->haveThisCard($uid, 9);
        //check have this card
        if ($cardCount == 0) {
            return $result;
        }
        //check user game mode
        $gameMode = $dalItem->getUserGameMode($uid);
        if ($gameMode != 1) {
            return array('status' => -2);
        }
        try {
            $this->_wdb->beginTransaction();
            //update user game mode
            $modeInfo = array('game_mode' => 0);

            require_once 'Dal/Dynamite/User.php';
            $dalUser = Dal_Dynamite_User::getDefaultInstance();
            $dalUser->updateUserBasicInfo($uid, $modeInfo);

            $dalItem->updateUserCard($uid, 9, -1);

            $this->_wdb->commit();

            $result['status'] = 1;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return array('status' => -1);
        }
        return $result;
    }

    /**
     * check card can use
     *
     * @param integer $uid
     * @param integer $cid
     * @return integer
     */
    public function checkCardCanUse($uid, $cid)
    {
        $result = -1;

        require_once 'Dal/Dynamite/Item.php';
        $dalItem = Dal_Dynamite_Item::getDefaultInstance();
        //get crad count
        $cardCount = $dalItem->haveThisCard($uid, $cid);
        if (!$cardCount) {
            return $result;
        }

        require_once 'Dal/Dynamite/User.php';
        $dalUser = Dal_Dynamite_User::getDefaultInstance();
        $userInfo = $dalUser->getUser($uid);

        require_once 'Bll/Cache/Dynamite.php';
        $hitmanInfo = Bll_Cache_Dynamite::getHitmanType();
        $userInfo['max_life'] = $hitmanInfo[$userInfo['hitman_type']-1]['max_life'];
        //check card:元気ドリンク、元気ドリンクDX、ミラクルドリンク can use
        if ($cid == 1 || $cid == 2 || $cid == 3) {
            $maxLife = $userInfo['max_life'];
            //get all hitman blood info
            $allHitManBlood = $dalItem->getAllHitmanBlood($uid);

            $allHitManBloodArray = array('0' => $allHitManBlood['hitman_life1'], '1' => $allHitManBlood['hitman_life2'], '2' => $allHitManBlood['hitman_life3'], '3' => $allHitManBlood['hitman_life4']);

            for ($i = 0; $i < 4; $i++) {
                if ($allHitManBloodArray[$i] > 0 && $allHitManBloodArray[$i] < $maxLife) {
                    $result = 1;
                    break;
                }
            }
        }

        //check card:復活の儀式、復活のシャワー can use
        if ($cid == 6 || $cid == 4) {
            //check some hitman are dead
            if ($userInfo['hitman_count'] < 4 && $userInfo['hitman_count'] > 0) {
                $result = 1;
            }
        }

        //check card:ダイナマイトほいほい can use
        if ($cid == 5) {
            if ((time() - $userInfo['refuse_bomb_time']) > 3 * 3600) {
                $result = 1;
            }
        }

        //check card:最終兵器 can use
        if ($cid == 7) {
            if ($userInfo['bomb_count'] < $this->_maxBombCount) {
                $result = 1;
            }
        }

        //check card:マイミクシェルター can use
        if ($cid == 8) {
            //check user game mode
            $gameMode = $dalItem->getUserGameMode($uid);
            if ($gameMode == 1) {
                return -1;
            }

            require_once 'Bll/Friend.php';
            $fids = Bll_Friend::getFriends($uid);

            $appFriendCount = 0;
            if (!empty($fids)) {
                $appFriendCount = $dalItem->getFriendCountInApp($fids);
            }

            if ($appFriendCount >= 5) {
                $result = 1;
            }
        }

        //check card:宣戦布告 can use
        if ($cid == 9) {
            //check user game mode
            $gameMode = $dalItem->getUserGameMode($uid);
            if ($gameMode != 1) {
                return -1;
            }

            $result = 1;
        }

        //check card:神々の怒り can use
        if ($cid == 10) {
            if ($userInfo['bonus'] < 1000 && $userInfo['hitman_type'] != 11) {
                $result = 1;
            }
        }

        return $result;
    }

    /**
     * buy item submit
     *
     * @param integer $uid
     * @param integer $itemId
     * @param integer $selectNum
     */
    public function buyItemSubmit($uid, $itemId, $selectNum)
    {
        $result = -1;

        //get item all price
        require_once 'Bll/Cache/Dynamite.php';
        $itemShopList = Bll_Cache_Dynamite::getItemShopList();
        $allPrice = $selectNum * $itemShopList[$itemId - 1]['price'];

        //get selected item infomation
        $selectedItemInfo = array();
        foreach ($itemShopList as $key => $value) {
            if ($itemId == $value['id']) {
                $selectedItemInfo = $itemShopList[$key];
            }
        }

        try {
            $this->_wdb->beginTransaction();

            require_once 'Dal/Dynamite/User.php';
            $dalUser = Dal_Dynamite_User::getDefaultInstance();
            $userMoreInfo = $dalUser->getUserMoreInfoForUpdate($uid);

            $bonus = $userMoreInfo['bonus'];

            //check if user have enough money
            if ($bonus < $allPrice) {
                $this->_wdb->rollBack();
                return $result;
            }

            //update user bonus
            $updataInfo = array('bonus' => ($bonus - $allPrice));
            $dalUser->updateUserMoreInfo($uid, $updataInfo);

            //update user item count
            require_once 'Dal/Dynamite/Item.php';
            $dalItem = Dal_Dynamite_Item::getDefaultInstance();
            $dalItem->updateUserCard($uid, $itemId, $selectNum);

            //get item count after buy item
            $afterBuyItemCount = $dalItem->getOneItemCount($uid, $itemId);

            //insert buy item log
            $buyItemLog = array('uid' => $uid,
                                'item_id' => $itemId,
                                'item_name' => $selectedItemInfo['name'],
                                'buy_num' => $selectNum,
                                'item_after_buy_count' => $afterBuyItemCount,
                                'buy_method' => '所持金',
                                'before_buy_bonus' => $bonus,
                                'pay_bonus' => $allPrice,
                                'after_buy_bonus' => $bonus - $allPrice,
                                'buy_time' => date('Y-m-d H:i:s'));

            $dalItem->insertLog($buyItemLog);

            $this->_wdb->commit();

            $result = 1;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return $result;
        }

        return $result;
    }

    public function getBombNum($itemId)
    {
        $bombNum = 0 ;

        switch ($itemId) {
            case 60:
                $bombNum = 2;
                break;
            case 61:
                $bombNum = 5;
                break;
            case 62:
                $bombNum = 10;
                break;
            default:
                break;
        }

        return $bombNum;
    }

    /**
     * insert dynamite_payment
     *
     * @param array $pay
     * @return boolean
     */
    public function insertPayment($pay)
    {
        $result = false;

        $this->_wdb->beginTransaction();

        try {
            //insert into dynamite_payment
            require_once 'Dal/Dynamite/User.php';
            $dalUser = Dal_Dynamite_User::getDefaultInstance();
            $dalUser->insertPayment($pay);

            $this->_wdb->commit();

            $result = true;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return $result;
        }

        return $result;
    }

    /**
     * buy item by mixipoint
     * @param string $pointCode
     * @return boolean
     */
    public function buyItemByMixiPointSubmit($pointCode)
    {
        $result = -1;

        require_once 'Dal/Dynamite/User.php';
        $dalUser = Dal_Dynamite_User::getDefaultInstance();
        $payment = $dalUser->getPaymentByCode($pointCode);

        if (empty($payment)) {
            return $result;
        }

        $uid = $payment['uid'];
        $itemId = $payment['item_id'];

        //get selected item infomation
        require_once 'Bll/Cache/Dynamite.php';
        $itemShopList = Bll_Cache_Dynamite::getItemShopList();

        $selectedItemInfo = array();
        foreach ($itemShopList as $key => $value) {
            if ($itemId == $value['id']) {
                $selectedItemInfo = $itemShopList[$key];
            }
        }

        $this->_wdb->beginTransaction();

        try {

            $userMoreInfo = $dalUser->getUserMoreInfoForUpdate($uid);

            $afterBuyItemCount = 0;

            require_once 'Dal/Dynamite/Item.php';
            $dalItem = Dal_Dynamite_Item::getDefaultInstance();

            $bombNum = 0;

            if ($itemId == 60 || $itemId == 61 || $itemId == 62) {
                $bombNum = $this->getBombNum($itemId);
                $dalUser->updateUserBombCountAndRemainBombCount($uid, $bombNum);
            }
            else {
                //update user item count
                $dalItem->updateUserCard($uid, $itemId, 1);

                $afterBuyItemCount = $dalItem->getOneItemCount($uid, $itemId);
            }

            //insert buy item log
            $buyItemLog = array('uid' => $uid,
                                'item_id' => $itemId,
                                'item_name' => $selectedItemInfo['name'],
                                'buy_num' => 1,
                                'item_after_buy_count' => $afterBuyItemCount,
                                'buy_method' => 'mixiポイント',
                                'before_buy_bonus' => $userMoreInfo['bonus'],
                                'pay_bonus' => 0,
                                'after_buy_bonus' => $userMoreInfo['bonus'],
                                'pay_mixi_point' => $selectedItemInfo['point'],
                                //'old_bomb_count' => $userMoreInfo['bomb_count'],
                                //'new_bomb_count' => $userMoreInfo['bomb_count'] + $bombNum,
                                //'old_remain_bomb_count' => $userMoreInfo['remainder_bomb_count'],
                               // 'new_remain_bomb_count' => $userMoreInfo['remainder_bomb_count'] + $bombNum,
                                'buy_time' => date('Y-m-d H:i:s'));

            if ($itemId == 60 || $itemId == 61 || $itemId == 62) {
                $buyItemLog['old_bomb_count'] = $userMoreInfo['bomb_count'];
                $buyItemLog['new_bomb_count'] = $userMoreInfo['bomb_count'] + $bombNum;
                $buyItemLog['old_remain_bomb_count'] = $userMoreInfo['remainder_bomb_count'];
                $buyItemLog['new_remain_bomb_count'] = $userMoreInfo['remainder_bomb_count'] + $bombNum;
            }

            $dalItem->insertLog($buyItemLog);

            //update payment status
            $dalUser->updatePaymentStatus($pointCode, 1, time());

            $this->_wdb->commit();

            $result = 1;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return $result;
        }

        return $result;
    }

    /**
     * get infomation about item
     * @param string $pointCode
     * @return boolean
     */
    public function getBuyItemInfo($uid, $itemId)
    {
        require_once 'Dal/Dynamite/User.php';
        $dalUser = Dal_Dynamite_user::getDefaultInstance();

        //get user bonus
        $bonus = $dalUser->getUserBonus($uid);

        //get selected item info
        require_once 'Bll/Cache/Dynamite.php';
        $allItemInfo = Bll_Cache_Dynamite::getItemInfo();

        $allItemInfo[60]['cid'] = 60;
        $allItemInfo[60]['name'] = 'ダイナマイト詰め合わせ（梅）';
        $allItemInfo[60]['introduce'] = '２個ダイナマイトを増やす';

        $allItemInfo[61]['cid'] = 61;
        $allItemInfo[61]['name'] = 'ダイナマイト詰め合わせ（竹）';
        $allItemInfo[61]['introduce'] = '5個ダイナマイトを増やす';

        $allItemInfo[62]['cid'] = 62;
        $allItemInfo[62]['name'] = 'ダイナマイト詰め合わせ（松）';
        $allItemInfo[62]['introduce'] = '10個ダイナマイトを増やす';

        foreach ($allItemInfo as $key => $value) {
            if ($itemId == $value['cid']) {
                $selectedItem = $allItemInfo[$key];
            }
        }

        //get item price
        $itemShopList = Bll_Cache_Dynamite::getItemShopList();

        if ($itemId == 60 || $itemId == 61 || $itemId == 62) {
            $itemPrice = 0;
        }
        else {
            $itemPrice = $itemShopList[$itemId - 1]['price'];
        }

        //get item count
        require_once 'Dal/Dynamite/Item.php';
        $dalItem = Dal_Dynamite_Item::getDefaultInstance();
        $itemCount = $dalItem->getOneItemCount($uid, $itemId);

        if ($itemId == 60 || $itemId == 61 || $itemId == 62) {
            $itemCount = '--';
        }

        $result = array('bonus' => $bonus, 'selectedItem' => $selectedItem, 'itemCount' => $itemCount, 'itemPrice' => $itemPrice);

        return $result;
    }
}
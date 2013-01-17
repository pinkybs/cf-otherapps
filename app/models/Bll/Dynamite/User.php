<?php

require_once 'Dal/Dynamite/User.php';

class Bll_Dynamite_User
{
    public function __construct($config = null)
    {
        if (is_null($config)) {
            $config = getDBConfig();
        }

        $this->_config = $config;
        $this->_rdb = $config['readDB'];
        $this->_wdb = $config['writeDB'];
    }

    public function isJoined($uid)
    {
        $dalDynamiteUser = Dal_Dynamite_User::getDefaultInstance();
        $user = $dalDynamiteUser->isInDynamite($uid);

        if ($user) {
            return true;
        }
        else {
            return false;
        }
    }

    public function join($uid)
    {

        $result = false;

        try {
            $this->_wdb->beginTransaction();

            $dalDynamiteUser = Dal_Dynamite_User::getDefaultInstance();
            $dalDynamiteItem = Dal_Dynamite_Item::getDefaultInstance();

            $time = time();

            $userBasicInfo = array(
                'uid' => $uid,
                'last_update_life_time' => $time,
                'create_time' => $time
                );

            $dalDynamiteUser->insertUserBasic($userBasicInfo);

            $dalDynamiteUser->insertUserMore(array('uid' => $uid));

            //init item data
            for ($i = 1; $i <= 11; $i++) {
                $item = array('uid' => $uid, 'cid' =>$i);
                if ($i == 8 || $i == 9) {
                	$item['count'] = 2;
                }
                $dalDynamiteItem->insertItem($item);
            }

            $this->_wdb->commit();

            $result = true;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return false;
        }

        try {
            //insert minifeed
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

            require_once 'Bll/Dynamite/Index.php';
            $bllIndex = new Bll_Dynamite_Index();
            $feedTable = $bllIndex->getFeedTable($minifeed['uid']);

            require_once 'Dal/Dynamite/Feed.php';
            $dalDynamiteFeed = Dal_Dynamite_Feed::getDefaultInstance();
            $dalDynamiteFeed->insertFeed($minifeed, $feedTable);

            //if user is invited, auto send card to inviter
            require_once 'Bll/Invite.php';
            $inviterArray = Bll_Invite::get(6230, $uid);

            if ($inviterArray) {
                foreach ($inviterArray as $value) {
                    $inviterId = $value;

                    $giftArray = array(0 => 4, 1 => 6, 2 => 5, 3 => 7);
                    $gifyId = $giftArray[array_rand($giftArray)];

                    require_once 'Dal/Dynamite/Item.php';
                    $dalItem = Dal_Dynamite_Item::getDefaultInstance();

                    $dalItem->updateUserCard($inviterId, $gifyId, 1);

                    /****debug  start***/
                    $inviteTime = date('Y-m-d H:i:s');
                    $giftName = array('4' => '復活のシャワー', '6' => '復活の儀式', '5' => 'ダイナマイトほいほい', '7' => '最終兵器');
                    info_log("time " . $inviteTime . ' uid===' . $uid . 'join game,' . $inviterId .
                             'get gift,gift id===' . $gifyId . '  and gift name==' . $giftName[$gifyId], "dynamite_invite");
                    /****debug end ****/
                }
            }
        }
        catch (Exception $e1) {

        }

        return $result;
    }

    public function updateUser($uid)
    {
        $result = false;

        try {
            $dalDynamiteUser = Dal_Dynamite_User::getDefaultInstance();

            $time = time();

            $dalDynamiteUser->updateUser($uid, $time);

            $result = true;
        }
        catch (Exception $e) {
            return false;
        }

        return $result;
    }

    /**
     * set alive to 1
     *
     * @param string $uid
     * @return boolean
     */
    public function setAlive($uid, $hitmanType)
    {
        $result = false;

        try {
            $dalDynamiteUser = Dal_Dynamite_User::getDefaultInstance();

            $userInfomation = $dalDynamiteUser->getUser($uid);

            $this->_wdb->beginTransaction();

            $info = array('isalive' => 1,
                          'hitman_type' => $hitmanType,
                          'isgameover' => 0);

            $dalDynamiteUser->updateUserBasicInfo($uid, $info);

            //get hitman info
            require_once 'Bll/Cache/Dynamite.php';
            $allHitmanInfo = Bll_Cache_Dynamite::getHitmanType();

            $newHitmanInfo = $allHitmanInfo[$hitmanType - 1];
            //first join game, isalive = 0; game over, isalive = 0
            if ($userInfomation['isalive'] == 0) {
                $updateHitmanInfo = array('hitman_count' => 4,
                                          'bomb_count' => 4,
                                          'remainder_bomb_count' => 4,
                                          'hitman_life1' => $newHitmanInfo['max_life'],
                                          'hitman_life2' => $newHitmanInfo['max_life'],
                                          'hitman_life3' => $newHitmanInfo['max_life'],
                                          'hitman_life4' => $newHitmanInfo['max_life'],
                                          'hitman_dead_time1' => 0,
                                          'hitman_dead_time2' => 0,
                                          'hitman_dead_time3' => 0,
                                          'hitman_dead_time4' => 0,
                                          'bomb_power1' => 0,
                                          'bomb_power2' => 0,
                                          'bomb_power3' => 0,
                                          'bomb_power4' => 0,
                                          'bomb_power5' => 0,
                                    );

            }
            //change super hitman to nomal hitman
            else if ($userInfomation['hitman_count'] > 0) {
                $updateHitmanInfo = array('hitman_life1' => $newHitmanInfo['max_life'],
                                          'hitman_life2' => $newHitmanInfo['max_life'],
                                          'hitman_life3' => $newHitmanInfo['max_life'],
                                          'hitman_life4' => $newHitmanInfo['max_life']);
            }

            //update user hitman info
            $dalDynamiteUser->updateUserMoreInfo($uid, $updateHitmanInfo);

            $this->_wdb->commit();
            $result = true;
        }
        catch (Exception $e) {
            return false;
        }

        return $result;
    }

}
<?php
/** @see Bll_Abstract.php */
require_once 'Bll/Abstract.php';

/**
 * dynamite shop logic's Operation
 *
 * @package    Bll
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/07/07    Liz
 */
class Bll_Dynamite_Shop extends Bll_Abstract
{
    /**
     * buy hitman
     *
     * @param integer $uid
     * @param integer $newHitmanPicId-hitman pic_id
     * @return array
     */
    public function buyHitman($uid, $newHitmanPicId)
    {
        $result = array('status' => -1);

        require_once 'Dal/Dynamite/User.php';
        $dalDynamiteUser = Dal_Dynamite_User::getDefaultInstance();

        //get user dynamite info
        $userDynamite = $dalDynamiteUser->getUser($uid);

        require_once 'Bll/Cache/Dynamite.php';
        $allHitmanInfo = Bll_Cache_Dynamite::getHitmanType();
        $userDynamite['pic_id'] = $allHitmanInfo[$userDynamite['hitman_type'] - 1]['pic_id'];
        $userDynamite['max_life'] = $allHitmanInfo[$userDynamite['hitman_type'] - 1]['max_life'];

        //get new hitman info
        $newHitmanId = 0;
        for ($i = 0; $i < count($allHitmanInfo); $i++) {
            if ($allHitmanInfo[$i]['pic_id'] == (int)$newHitmanPicId) {
                $newHitmanId = $allHitmanInfo[$i]['id'];
                break;
            }
        }
        $newHitmanInfo = $allHitmanInfo[$newHitmanId - 1];
        if ( !$newHitmanInfo ) {
            return $result;
        }

        switch ( $newHitmanInfo['pic_id'] ) {
            case $userDynamite['pic_id'] :
                    return $result;
                    break;
            case 2 :
                if ( $userDynamite['bonus'] < 1000 ) {
                    return $result;
                }
                break;
            case 4 :
                if ( $userDynamite['bonus'] < 15000 ) {
                    return $result;
                }
                break;
            case 6 :
                if ( $userDynamite['bonus'] < 1000 ) {
                    return $result;
                }
                break;
            case 7 :
                if ( $userDynamite['bonus'] < 4000 ) {
                    return $result;
                }
                break;
            case 8 :
                if ( $userDynamite['bonus'] < 4000 ) {
                    return $result;
                }
                break;
            case 9 :
                if ( $userDynamite['bonus'] < 50000 ) {
                    return $result;
                }
                break;
            case 10 :
                if ( $userDynamite['bonus'] < 100000 ) {
                    return $result;
                }
                break;
            default :
                break;
        }

        $this->_wdb->beginTransaction();
        try {
            $info = array('hitman_type' => $newHitmanInfo['id'], 'show_set_bomb' => 0);
            //update user dynamite info
            $dalDynamiteUser->updateUserBasicInfo($uid, $info);

            if ($userDynamite['max_life'] > $newHitmanInfo['max_life']) {
                $hitmanInfo = array('hitman_life1' => $userDynamite['hitman_life1'] > $newHitmanInfo['max_life'] ? $newHitmanInfo['max_life'] : $userDynamite['hitman_life1'],
                                    'hitman_life2' => $userDynamite['hitman_life2'] > $newHitmanInfo['max_life'] ? $newHitmanInfo['max_life'] : $userDynamite['hitman_life2'],
                                    'hitman_life3' => $userDynamite['hitman_life3'] > $newHitmanInfo['max_life'] ? $newHitmanInfo['max_life'] : $userDynamite['hitman_life3'],
                                    'hitman_life4' => $userDynamite['hitman_life4'] > $newHitmanInfo['max_life'] ? $newHitmanInfo['max_life'] : $userDynamite['hitman_life4']
                                    );
                //update user hitman info
                $dalDynamiteUser->updateUserMoreInfo($uid, $hitmanInfo);
            }

            require_once 'Dal/Dynamite/Bomb.php';
            $dalDynamiteBomb = Dal_Dynamite_Bomb::getDefaultInstance();

            $nowTime = time();
            $dalDynamiteBomb->updateBombPowerTime($uid, $newHitmanInfo['power_time'], $nowTime);

            $result['status'] = 1;
            $this->_wdb->commit();

        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return array('status' => -1);
        }

        try {
            //send feed to all friends
            if ($result['status'] == 1) {

                $friends = array();

                require_once 'Bll/Friend.php';
                $friends = Bll_Friend::getFriends($uid);
                $friends[] = $uid;

                require_once 'Bll/User.php';
                $userInfo = Bll_User::getPerson($uid);
                $userName = $userInfo->getDisplayName();

                require_once 'Dal/Dynamite/Feed.php';
                $dalDynamiteFeed = Dal_Dynamite_Feed::getDefaultInstance();

                $minifeed = array('template_id' => 55,
                                  'actor' => $uid,
                                  'target' => '',
                                  'feed_type' => $userName . '組、繁栄',
                                  'icon' => Zend_Registry::get('static') . "/apps/dynamite/img/icon/hitman.gif",
                                  'title' => '',
                                  'create_time' => date('Y-m-d H:i:s'));

                require_once 'Bll/Dynamite/Index.php';
                $bllIndex = new Bll_Dynamite_Index();

                $friendsCount = count($friends);
                for ($i = 0; $i < $friendsCount; $i++) {
                    $minifeed['uid'] = $friends[$i];
                    $feedTable = $bllIndex->getFeedTable($minifeed['uid']);
                    $dalDynamiteFeed->insertFeed($minifeed, $feedTable);
                }

                //send avtivity
                $hitmanPicType = $newHitmanInfo['pic_id'] < 10 ? '0' . $newHitmanInfo['pic_id'] : $newHitmanInfo['pic_id'];

                require_once 'Bll/Dynamite/Activity.php';
                $result['activity'] = Bll_Dynamite_Activity::getActivity($uid, $uid, 12);
                $result['activity_pic'] = Zend_Registry::get('static') . "/apps/dynamite/img/activity_image/" . $hitmanPicType . "_a.gif";
            }
        }
        catch (Exception $e1) {

        }

        return $result;
    }

}
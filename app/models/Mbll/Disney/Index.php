<?php
/** @see Mbll_Abstract.php */
require_once 'Mbll/Abstract.php';

/**
 * disney index logic's Operation
 *
 * @package    Mbll
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/10/12    Liz
 */
class Mbll_Disney_Index extends Mbll_Abstract
{

    /**
     * get current, notice
     *
     * @param integer $uid
     * @param array $placeInfo
     * @return boolean
     */
    public function getCurrentNotice($uid, $placeInfo, $appId)
    {
        $result = -1;
        
        require_once 'Mdal/Disney/User.php';
        $mdalUser = Mdal_Disney_User::getDefaultInstance();
        
        //get user award count
        $userAwardCount = $mdalUser->getUserAwardCount($uid, $placeInfo['pid']);
        if ( $userAwardCount >= 3 ) {
            return;
        }
        
        try {
            $this->_wdb->beginTransaction();
            
            //add user award
            $mdalUser->addUserAward($uid, $placeInfo['pid']);
            
            //update user last target place
            $userInfo = array('last_target_place' => $placeInfo['pid']);
            $mdalUser->updateUser($uid, $userInfo);
            
            //update user game point
            $mdalUser->updateUserPoint($uid, 250);
                        
            $this->sendAreaCup($uid, $placeInfo['pid']);
                        
            $this->_wdb->commit();
            
            $result = 1;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return -1;
        }
        
        if ($result != 1) {
            return;
        }
        
        
        require_once 'Bll/User.php';
        $user = Bll_User::getPerson($uid);
        $title = $user->getDisplayName() . 'さんが' . $placeInfo['award_name'] . 'ｽﾃｨｯﾁをGET!';
        
        //send activity
        require_once 'Bll/Restful.php';
        //get restful object
        $restful = Bll_Restful::getInstance($uid, $appId);
        $picUrl = Zend_Registry::get('static') . "/apps/disney/img/chara_activity/" . $placeInfo['award_icon'] . ".gif";
        $restful->createActivityWithPic(array('title'=>$title), $picUrl, 'image/gif');
                
        //add for mymixi
        require_once 'Bll/Friend.php';
        //get user friend id list
        $fids = Bll_Friend::getFriends($uid);
        if ( !empty($fids) ) {
            $mdalUser->updateMymixi($fids);
        }
        
        /*
        require_once 'Bll/User.php';
        $user = Bll_User::getPerson($uid);
        //notice title
        $title = $user->getDisplayName() . 'さんが' . $placeInfo['award_name'] . 'をGET!';
        
        //send activity
        require_once 'Bll/Restful.php';
        //get restful object
        $restful = Bll_Restful::getInstance($uid, $appId);
        $picUrl = Zend_Registry::get('static') . "/apps/disney/img/chara_activity/" . $placeInfo['award_icon'] . ".gif";
        $restful->createActivityWithPic(array('title'=>$title), $picUrl, 'image/gif');
        
        require_once 'Bll/Friend.php';
        //get user friend id list
        $fids = Bll_Friend::getFriends($uid);
        if ( $fids ) {
            //get user app friend id list
            $appFids = $mdalUser->getAppFids($fids);

            if ( $appFids ) {
                require_once 'Mdal/Disney/Notice.php';
                $mdalNotice = Mdal_Disney_Notice::getDefaultInstance();
                
                $notice = array('title' => $title,
                                'actor_uid' => $uid,
                                'pid' => $placeInfo['pid'],
                                'type' => 2,
                                'create_time' => time());
                
                for ( $i = 0, $iCount = count($appFids); $i < $iCount; $i++ ) {
                    $notice['uid'] = $appFids[$i]['uid'];
                    //insert notice
                    $mdalNotice->insertNotice($notice);
                }
            }
        }*/
        
        return;
    }
    
    /**
     * send friend award
     *
     * @param integer $uid
     * @param integer $fid
     * @param integer $pid
     * @return string
     */
    public function sendAward($uid, $fid, $pid)
    {                
        //check is friend
        require_once 'Bll/Friend.php';
        $isFriend = Bll_Friend::isFriend($uid, $fid);
        if ( !$isFriend ) {
            return -3;
        }
        
        require_once 'Mdal/Disney/Place.php';
        $mdalPlace = Mdal_Disney_Place::getDefaultInstance();
        
        require_once 'Mdal/Disney/User.php';
        $mdalUser = Mdal_Disney_User::getDefaultInstance();
        
        //check friend is in app
        $friendInApp = $mdalUser->isInApp($fid);
        if ( !$friendInApp ) {
            return -4;
        }
        
        //get place info by pid
        $placeInfo = $mdalPlace->getPlaceById($pid);
        if ( !$placeInfo ) {
            return -5;
        }
        
        //get user award count
        $userAwardCount = $mdalUser->getUserAwardCount($uid, $pid);
        if ( $userAwardCount < 1 ) {
            return -6;
        }

        return 1;
    }
    
    /**
     * down load award
     *
     * @param integer $uid
     * @param integer $pid
     * @return string
     */
    public function downloadAward($uid, $pid)
    {
        $result = array('status' => -1);
        
        require_once 'Mdal/Disney/Place.php';
        $mdalPlace = Mdal_Disney_Place::getDefaultInstance();
    
        //get place info by pid
        $placeInfo = $mdalPlace->getPlaceById($pid);
        if ( !$placeInfo ) {
            return array('status' => -3);
        }
        
        require_once 'Mdal/Disney/User.php';
        $mdalUser = Mdal_Disney_User::getDefaultInstance();
        //update user game point
        $mdalUser->updateUserPoint($uid, 10);
        
        $result['status'] = 1;
        
        return $result;
    }
    
    /**
     * arrive target
     *
     * @param integer $uid
     * @return string
     */
    public function arriveTarget($uid, $disneyUser, $appId, $lat, $lon, $hasShoes, &$userShoes)
    {
        $result = -1;

        require_once 'Mdal/Disney/User.php';
        $mdalUser = Mdal_Disney_User::getDefaultInstance();
        
        require_once 'Mdal/Disney/Shoes.php';
        $mdalShoes = Mdal_Disney_Shoes::getDefaultInstance();
        $userShoes = $mdalShoes->getUserShoes($uid);
        
        try {
            $this->_wdb->beginTransaction();
            
            //add user award
            $mdalUser->addUserAward($uid, $disneyUser['target_place']);
                        
            //send area cup
            $this->sendAreaCup($uid, $disneyUser['target_place']);
            
            $userInfo = array('last_target_place' => $disneyUser['target_place'],
                              'remain_distance' => 0,
                              'flash_distance' => 0,
                              'last_lat' => $lat,
                              'last_lon' => $lon);
            //update user info
            $mdalUser->updateUser($uid, $userInfo);
    
            require_once 'Mdal/Disney/Flash.php';
            $mdalFlash = Mdal_Disney_Flash::getDefaultInstance();
            //delete user flash point
            $mdalFlash->deleteUserFlashPoint($uid);
            
            //update user game point
            $mdalUser->updateUserPoint($uid, 100);
            
            if ($hasShoes) {
                $mdalShoes->updateShoesCount($uid);
            }
            
            $this->_wdb->commit();

            $result = 1;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return -1;
        }

        
        require_once 'Bll/User.php';
        $user = Bll_User::getPerson($uid);
        //notice title
        $title = $user->getDisplayName() . 'さんが' . $disneyUser['t_award_name'] . 'をGET!';
        //send activity
        require_once 'Bll/Restful.php';
        //get restful object
        $restful = Bll_Restful::getInstance($uid, $appId);
        $picUrl = Zend_Registry::get('static') . "/apps/disney/img/chara_activity/" . $disneyUser['t_award_icon'] . ".gif";
        $restful->createActivityWithPic(array('title'=>$title), $picUrl, 'image/gif');
        
        //add for mymixi
        require_once 'Bll/Friend.php';
        //get user friend id list
        $fids = Bll_Friend::getFriends($uid);
        if ( $fids ) {
            $mdalUser->updateMymixi($fids);
        }
        
        $userShoes['times'] = $userShoes['times'] - 1;
        
        /*
        if ( $result == 1 ) {
            require_once 'Bll/User.php';
            $user = Bll_User::getPerson($uid);

            //notice title
            $title = $user->getDisplayName() . 'さんが' . $disneyUser['t_award_name'] . 'をGET!';
            
            require_once 'Bll/Friend.php';
            //get user friend id list
            $fids = Bll_Friend::getFriends($uid);
            if ( $fids ) {
                //get user app friend id list
                $appFids = $mdalUser->getAppFids($fids);

                if ( $appFids ) {
                    require_once 'Mdal/Disney/Notice.php';
                    $mdalNotice = Mdal_Disney_Notice::getDefaultInstance();
                    
                    $notice = array('title' => $title,
                                    'actor_uid' => $uid,
                                    'pid' => $disneyUser['target_place'],
                                    'type' => 2,
                                    'create_time' => time());
                    
                    for ( $i = 0, $iCount = count($appFids); $i < $iCount; $i++ ) {
                        $notice['uid'] = $appFids[$i]['uid'];
                        //insert notice
                        $mdalNotice->insertNotice($notice);
                    }
                }
            }
            
            //send activity
            require_once 'Bll/Restful.php';
            //get restful object
            $restful = Bll_Restful::getInstance($uid, $appId);
            $picUrl = Zend_Registry::get('static') . "/apps/disney/img/chara_activity/" . $disneyUser['t_award_icon'] . ".gif";
            $restful->createActivityWithPic(array('title'=>$title), $picUrl, 'image/gif');
        
        }*/
        
        return $result;
    }
    
    /**
     * apply trade award
     *
     * @param integer $pid
     * @param integer $tradePid
     * @param integer $uid
     * @param integer $fid
     * @return string
     */
    public function applyTradeAward($pid, $tradePid, $uid, $fid, $appId)
    {
        $result = array('status' => -1);

        if ( $pid == $tradePid ) {
            return array('status' => -2);
        }
        
        //check is friend
        require_once 'Bll/Friend.php';
        $isFriend = Bll_Friend::isFriend($uid, $fid);
        if ( !$isFriend ) {
            return array('status' => -3);
        }
        
        require_once 'Mdal/Disney/Place.php';
        $mdalPlace = Mdal_Disney_Place::getDefaultInstance();
        
        require_once 'Bll/User.php';
        $user = Bll_User::getPerson($uid);
        
        require_once 'Mdal/Disney/User.php';
        $mdalUser = Mdal_Disney_User::getDefaultInstance();
        
        require_once 'Mdal/Disney/Notice.php';
        $mdalNotice = Mdal_Disney_Notice::getDefaultInstance();
            
        //check had this apply info
        $tradeApplyInfo = $mdalUser->getTradeApply($uid, $fid, $pid, $tradePid);
        if ( $tradeApplyInfo ) {
            return array('status' => -4);
        }
        
        //check friend is in app
        $friendInApp = $mdalUser->isInApp($fid);
        if ( !$friendInApp ) {
            return array('status' => -5);
        }
        
        //get place info by pid
        $placeInfo = $mdalPlace->getPlaceById($pid);
        if ( !$placeInfo ) {
            return array('status' => -6);
        }
    
        //get place info by pid
        $tradePlaceInfo = $mdalPlace->getPlaceById($tradePid);
        if ( !$tradePlaceInfo ) {
            return array('status' => -7);
        }
        
        //get user award count
        $userPidAwardCount = $mdalUser->getUserAwardCount($uid, $pid);
        if ( $userPidAwardCount < 1 ) {
            return array('status' => -8);
        }
        else if ( $userPidAwardCount == 1 ) {
            $userCanApply = 1;
            
            //get some body trade user's pid
            $tradeUserPidList = $mdalUser->getTradeApplyByFidAndFriendPid($uid, $pid);
            if ( $tradeUserPidList ) {
                for ( $i = 0,$iCount = count($tradeUserPidList); $i < $iCount; $i++ ) {
                    if ( time() - $tradeUserPidList[$i]['create_time'] > 7*24*60*60 ) {
                        //delete trade apply info and notice
                        $mdalUser->deleteTradeApply($tradeUserPidList[$i]['nid']);
                        $mdalNotice->deleteNotice($tradeUserPidList[$i]['nid']);
                    }
                    else {
                        $userCanApply = -1;
                    }
                }
            }
            
            //get user trade some body
            $userTradePidList = $mdalUser->getTradeApplyByUidAndUserPid($uid, $pid);
            if ( $userTradePidList ) {
                for ( $k = 0,$kCount = count($userTradePidList); $k < $kCount; $k++ ) {
                    if ( time() - $userTradePidList[$k]['create_time'] > 7*24*60*60 ) {
                        //delete trade apply info and notice
                        $mdalUser->deleteTradeApply($userTradePidList[$k]['nid']);
                        $mdalNotice->deleteNotice($userTradePidList[$k]['nid']);
                    }
                    else {
                        $userCanApply = -1;
                    }
                }
            }
            
            if ( $userCanApply == -1 ) {
                return array('status' => -10);
            }
        }
    
        //get friend award count
        $friendTradePidAwardCount = $mdalUser->getUserAwardCount($fid, $tradePid);
        if ( $friendTradePidAwardCount < 1 ) {
            return array('status' => -9);
        }
        else if ( $friendTradePidAwardCount == 1 ) {
            //get some body trade friend's pid
            $tradeFriendPidList = $mdalUser->getTradeApplyByFidAndFriendPid($fid, $tradePid);
            if ( $tradeFriendPidList ) {
                $friendCanApply = 1;
                for ( $j = 0,$jCount = count($tradeFriendPidList); $j < $jCount; $j++ ) {
                    if ( time() - $tradeFriendPidList[$j]['create_time'] > 7*24*60*60 ) {
                        //delete trade apply info and notice
                        $mdalUser->deleteTradeApply($tradeFriendPidList[$j]['nid']);
                        $mdalNotice->deleteNotice($tradeFriendPidList[$j]['nid']);
                    }
                    else {
                        $friendCanApply = -1;
                    }
                }
                
                if ( $friendCanApply == -1 ) {
                    return array('status' => -11);
                }
            }
        }
        
        //check today trade times
        $userTradeCount = $mdalUser->getUserTradeCount($uid);
        
        if ($userTradeCount >= 3) {
            return array('status' => -12);
        }
        
        try {
            $this->_wdb->beginTransaction();

            //update trade times
            $mdalUser->updateUser($uid, array('today_trade_times'=>$userTradeCount+1));
            
            //notice title
            $title = $user->getDisplayName() . 'さんからﾄﾚｰﾄﾞﾘｸｴｽﾄがあります';

            $notice = array('uid' => $fid,
                            'actor_uid' => $uid,
                            'title' => $title,
                            'type' => 4,
                            'create_time' => time());
            //insert notice
            $nid = $mdalNotice->insertNotice($notice);
            
            $applyInfo = array('nid' => $nid,
                               'uid' => $uid,
                               'fid' => $fid,
                               'user_pid' => $pid,
                               'friend_pid' => $tradePid,
                               'create_time' => time());
            $mdalUser->insertTradeApply($applyInfo);
        
            $this->_wdb->commit();

            $result = array('status' => 1);
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return -1;
        }
        
        if ($result['status'] != 1) {
            return;
        }
        
        //add to log trade
        require_once 'Mdal/Disney/Log.php';
        $mdalLog = Mdal_Disney_Log::getDefaultInstance();
        $logInfo = array('nid' => $nid,
        				 'uid' => $uid,
        				 'pid_u' => Mbll_Disney_Cache::getPlaceAwardName($pid),
        				 'fid' => $fid,
        				 'pid_f' => Mbll_Disney_Cache::getPlaceAwardName($tradePid),
        				 'status' => 1,
        				 'create_time' => time());
        $mdalLog->insertTrade($logInfo);
        
        /*$title = $user->getDisplayName() . 'さんからﾄﾚｰﾄﾞﾘｸｴｽﾄがあります';
        $recipients = array($fid);
        
        //activity
        require_once 'Bll/Restful.php';
        //get restful object
        $restful = Bll_Restful::getInstance($uid, $appId);        
        $picUrl = Zend_Registry::get('static') . "/apps/disney/img/chara_activity/" . $placeInfo['award_icon'] . ".gif";
        $restful->createActivityWithPic(array('title'=>$title, 'recipients'=>$recipients), $picUrl, 'image/gif');*/
        
        return $result;
    }

    /**
     * accept trade award
     *
     * @param integer $nid
     * @param integer $appId
     * @return string
     */
    public function acceptTradeAward($nid, $appId)
    {
        $result = array('status' => -1);

        require_once 'Mdal/Disney/User.php';
        $mdalUser = Mdal_Disney_User::getDefaultInstance();
        //check apply info by nid
        $tradeApplyInfo = $mdalUser->getTradeApplyByNid($nid);
        if ( !$tradeApplyInfo ) {
            return array('status' => -2);
        }
                
        //get user award count
        $userPidAwardCount = $mdalUser->getUserAwardCount($tradeApplyInfo['uid'], $tradeApplyInfo['user_pid']);
        if ( $userPidAwardCount < 1 ) {
            return array('status' => -3);
        }
    
        //get friend award count
        $friendTradePidAwardCount = $mdalUser->getUserAwardCount($tradeApplyInfo['fid'], $tradeApplyInfo['friend_pid']);
        if ( $friendTradePidAwardCount < 1 ) {
            return array('status' => -4);
        }
        
        //check user and friend award
        if ($tradeApplyInfo['user_pid'] == $tradeApplyInfo['friend_pid']) {
            return array('status' => -20);
        }
        
        require_once 'Mdal/Disney/Notice.php';
        $mdalNotice = Mdal_Disney_Notice::getDefaultInstance();
        
        require_once 'Mdal/Disney/Place.php';
        $mdalPlace = Mdal_Disney_Place::getDefaultInstance();
        //get place info by id
        $userPlaceInfo = $mdalPlace->getPlaceById($tradeApplyInfo['user_pid']);
        $friendPlaceInfo = $mdalPlace->getPlaceById($tradeApplyInfo['friend_pid']);
        
        try {
            $this->_wdb->beginTransaction();
        
            //add award
            $mdalUser->addUserAward($tradeApplyInfo['fid'], $tradeApplyInfo['user_pid']);
            
            //update friend last target place
            $friendInfo = array('last_target_place' => $tradeApplyInfo['user_pid']);
            $mdalUser->updateUser($tradeApplyInfo['fid'], $friendInfo);
            
            $mdalUser->addUserAward($tradeApplyInfo['uid'], $tradeApplyInfo['friend_pid']);
            
            //update user last target place
            $userInfo = array('last_target_place' => $tradeApplyInfo['friend_pid']);
            $mdalUser->updateUser($tradeApplyInfo['uid'], $userInfo);
            
            //delete award
            $mdalUser->deleteUserAward($tradeApplyInfo['uid'], $tradeApplyInfo['user_pid']);
            $mdalUser->deleteUserAward($tradeApplyInfo['fid'], $tradeApplyInfo['friend_pid']);
            
            //send area cup
            $this->sendAreaCup($tradeApplyInfo['fid'], $tradeApplyInfo['user_pid']);
            $this->sendAreaCup($tradeApplyInfo['uid'], $tradeApplyInfo['friend_pid']);
            
            //delete trade apply info
            $mdalUser->deleteTradeApply($tradeApplyInfo['nid']);
            
            //delete notice
            $mdalNotice->deleteNotice($tradeApplyInfo['nid']);
            
            //update user game point
            $mdalUser->updateUserPoint($tradeApplyInfo['uid'], 10);
            //update friend game point
            $mdalUser->updateUserPoint($tradeApplyInfo['fid'], 10);
            
            $this->_wdb->commit();

            $result = array('status' => 1);
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return array('status' => -1);
        }
    
        if ($result['status'] != 1) {
            return $result;
        }
        
        //insert notice
        //notice title
        $title = $userPlaceInfo['award_name'] . 'ｽﾃｨｯﾁと' . $friendPlaceInfo['award_name'] . 'ｽﾃｨｯﾁのﾄﾚｰﾄﾞが成立しました。';

        $notice = array('uid' => $tradeApplyInfo['uid'],
                        'actor_uid' => $tradeApplyInfo['fid'],
                        'pid' => $tradeApplyInfo['friend_pid'],
                        'title' => $title,
                        'type' => 5,
                        'create_time' => time());
        //insert notice
        $mdalNotice->insertNotice($notice);        
        
        //add for mymixi
        require_once 'Bll/Friend.php';
        //get user friend id list
        $fids = Bll_Friend::getFriends($tradeApplyInfo['uid']);
        if ( $fids ) {
            $mdalUser->updateMymixi($fids);
        }
        
        //add for mymixi
        require_once 'Bll/Friend.php';
        //get user friend id list
        $fids = Bll_Friend::getFriends($tradeApplyInfo['uid']);
        if ( $fids ) {
            $mdalUser->updateMymixi($fids);
        }
        
        
        //add to log trade
        require_once 'Mdal/Disney/Log.php';
        $mdalLog = Mdal_Disney_Log::getDefaultInstance();
        $mdalLog->updateTradeStatus($nid, 3);
        
        /*
        //user get award notice
        require_once 'Bll/Friend.php';
        //get user friend id list
        $userFids = Bll_Friend::getFriends($tradeApplyInfo['uid']);
        if ( $userFids ) {
            //get user app friend id list
            $appUserFids = $mdalUser->getAppFids($userFids);
            
            if ( $appUserFids ) {                
                require_once 'Bll/User.php';
                $user = Bll_User::getPerson($tradeApplyInfo['uid']);
                //notice title
                $title = $user->getDisplayName() . 'さんが' . $friendPlaceInfo['award_name'] . 'をGET!';
        
                $notice = array('title' => $title,
                                'actor_uid' => $tradeApplyInfo['uid'],
                                'pid' => $friendPlaceInfo['pid'],
                                'type' => 2,
                                'create_time' => time());
                
                for ( $i = 0, $iCount = count($appUserFids); $i < $iCount; $i++ ) {
                    $notice['uid'] = $appUserFids[$i]['uid'];
                    //insert notice
                    $mdalNotice->insertNotice($notice);
                }
            }
        }        
        
        //friend get award notice
        require_once 'Bll/Friend.php';
        //get friend friend id list
        $friendFids = Bll_Friend::getFriends($tradeApplyInfo['fid']);
        if ( $friendFids ) {
            //get friend app friend id list
            $appFriendFids = $mdalUser->getAppFids($friendFids);

            if ( $appFriendFids ) {                
                require_once 'Bll/User.php';
                $friend = Bll_User::getPerson($tradeApplyInfo['fid']);
                //notice title
                $title = $friend->getDisplayName() . 'さんが' . $userPlaceInfo['award_name'] . 'をGET!';
        
                $notice = array('title' => $title,
                                'actor_uid' => $tradeApplyInfo['fid'],
                                'pid' => $userPlaceInfo['pid'],
                                'type' => 2,
                                'create_time' => time());
                
                for ( $j = 0, $jCount = count($appFriendFids); $j < $jCount; $j++ ) {
                    $notice['uid'] = $appFriendFids[$j]['uid'];
                    //insert notice
                    $mdalNotice->insertNotice($notice);
                }
            }
        }
        */
        
        /*$title = $userPlaceInfo['award_name'] . 'と' . $friendPlaceInfo['award_name'] . 'のﾄﾚｰﾄﾞが成立!';
        $recipients = array($tradeApplyInfo['uid']);
        
        //activity
        require_once 'Bll/Restful.php';
        //get restful object
        $restful = Bll_Restful::getInstance($tradeApplyInfo['fid'], $appId);
        $picUrl = Zend_Registry::get('static') . "/apps/disney/img/chara_activity/" . $userPlaceInfo['award_icon'] . ".gif";
        $restful->createActivityWithPic(array('title'=>$title, 'recipients'=>$recipients), $picUrl, 'image/gif');*/
        
        return $result;
    }
    
    /**
     * cancael accept trade award
     *
     * @param integer $nid
     * @param integer $appId
     * @return string
     */
    public function acceptcancel($nid, $appId)
    {
        $result = -1;
        
        require_once 'Mdal/Disney/User.php';
        $mdalUser = Mdal_Disney_User::getDefaultInstance();
        
        require_once 'Mdal/Disney/Notice.php';
        $mdalNotice = Mdal_Disney_Notice::getDefaultInstance();
        //get notice info by nid
        $noticeInfo = $mdalNotice->getNoticeById($nid);
        if ( $noticeInfo['type'] != 4 ) {
            return $result;
        }
        
        //check apply info by nid
        $tradeApplyInfo = $mdalUser->getTradeApplyByNid($nid);
        
        require_once 'Mdal/Disney/Place.php';
        $mdalPlace = Mdal_Disney_Place::getDefaultInstance();
        //get place info by id
        $userPlaceInfo = $mdalPlace->getPlaceById($tradeApplyInfo['user_pid']);
        $friendPlaceInfo = $mdalPlace->getPlaceById($tradeApplyInfo['friend_pid']);
        if ( !$userPlaceInfo || !$friendPlaceInfo ) {
            return $result;
        }
        
        try {
            $this->_wdb->beginTransaction();
            
            //delete trade apply info
            $mdalUser->deleteTradeApply($nid);
            //delete notice
            $mdalNotice->deleteNotice($nid);
            
            $this->_wdb->commit();

            $result = 1;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return -1;
        }
        
        if ($result != 1) {
            return;
        }
        
        //add to log trade
        require_once 'Mdal/Disney/Log.php';
        $mdalLog = Mdal_Disney_Log::getDefaultInstance();
        $mdalLog->updateTradeStatus($nid, 2);
        
        $title = $userPlaceInfo['award_name'] . 'と' . $friendPlaceInfo['award_name'] . 'のﾄﾚｰﾄﾞが成立しませんでした。';
        $recipients = array($tradeApplyInfo['uid']);
        
        //activity
        require_once 'Bll/Restful.php';
        //get restful object
        $restful = Bll_Restful::getInstance($tradeApplyInfo['fid'], $appId);        
        $picUrl = Zend_Registry::get('static') . "/apps/disney/img/chara_activity/" . $userPlaceInfo['award_icon'] . ".gif";
        $restful->createActivityWithPic(array('title'=>$title, 'recipients'=>$recipients), $picUrl, 'image/gif');
        
        return $result;
    }
    
    /**
     * invite user
     *
     * @param integer $uid
     * @param string $recipientIds
     * @return string
     */
    public function invite($uid, $recipientIds, $appId)
    {        
        $inviteArray = explode(',', $recipientIds);
        
        if ( !$inviteArray ) {
            return;
        }
        
        require_once 'Mdal/Disney/Invite.php';
        $mdalInvite = Mdal_Disney_Invite::getDefaultInstance();
                
        //insert invite info
        for ( $i = 0, $iCount = count($inviteArray); $i < $iCount; $i++ ) {
            $mdalInvite->insertInvite($uid, $inviteArray[$i]);
        }

        //insert invite cup
        $inviteCount = $mdalInvite->getInviteCount($uid);
        if ( $inviteCount >= 50 ) {
            $cid = 4;
        }
        else if ( $inviteCount >= 25 ) {
            $cid = 3;
        }
        else if ( $inviteCount >= 10 ) {
            $cid = 2;
        }
        else if ( $inviteCount >= 5 ) {
            $cid = 1;
        }

        if ( $cid ) {
            $this->addUserCup($uid, $cid, $appId);
        }
        
        if ($cid == 2) {
            $this->addUserCup($uid, 1, $appId);
        }
        
        require_once 'Bll/User.php';
        $user = Bll_User::getPerson($uid);
        //notice title
        $title = $user->getDisplayName() . 'さんからﾃﾞｨｽﾞﾆｰご当地ｺﾚｸｼｮﾝに招待されています。';
        $recipients = $inviteArray;
        
        //send activity
        require_once 'Bll/Restful.php';
        //get restful object
        $restful = Bll_Restful::getInstance($uid, $appId);
        $restful->createActivity(array('title'=>$title, 'recipients'=>$recipients));        
    }
    
    /**
     * add user cup
     *
     * @param integer $uid
     * @param integer $cid
     * @param integer $appId
     * @return string
     */
    public function addUserCup($uid, $cid, $appId)
    {        
        $result = -1;
    
        require_once 'Mdal/Disney/Cup.php';
        $mdalCup = Mdal_Disney_Cup::getDefaultInstance();
        
        require_once 'Mdal/Disney/User.php';
        $mdalUser = Mdal_Disney_User::getDefaultInstance();
        $cupInfo = $mdalUser->getCupByCid($cid);
        if ( !$cupInfo ) {
            return -1;
        }
        
        $userCup = $mdalCup->getUserCup($uid, $cid);
        if ( $userCup ) {
            return -1;
        }
        
        try {
            $this->_wdb->beginTransaction();
    
            //insert cup
            $isInsert = $mdalCup->insertCup($uid, $cid);
    
            //mail-factory
            if ($cid == 6) {
               $mdalUser->updateDisneyMember($uid);
            }            
    
            $this->_wdb->commit();
            
            $result = $isInsert == -1 ? -1 : 1;
        
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
        }
        
        if ($result != 1) {
            return $result;
        }
        
        return $result;
    }
    
    public function addUserCupActivity($uid, $appId, $cupInfo)
    {
        require_once 'Bll/User.php';
        $user = Bll_User::getPerson($uid);
        //notice title
        $title = $user->getDisplayName() . 'さんが' . $cupInfo['name'] . 'を受賞!';
        
        //send activity
        require_once 'Bll/Restful.php';
        //get restful object
        $restful = Bll_Restful::getInstance($uid, $appId);
        $picUrl = Zend_Registry::get('static') . "/apps/disney/img/award_activity/".$cupInfo['icon'].".gif";
        $restful->createActivityWithPic(array('title'=>$title), $picUrl, 'image/gif');        
        
        //add for mymixi
        require_once 'Bll/Friend.php';
        //get user friend id list
        $fids = Bll_Friend::getFriends($uid);
        if ( $fids ) {
            require_once 'Mdal/Disney/User.php';
            $mdalUser = Mdal_Disney_User::getDefaultInstance();
            $mdalUser->updateMymixi($fids);
        }
        
        /*
        require_once 'Bll/User.php';
        $user = Bll_User::getPerson($uid);
        //notice title
        $title = $user->getDisplayName() . 'さんが' . $cupInfo['name'] . 'を受賞!';

        require_once 'Bll/Friend.php';
        //get user friend id list
        $fids = Bll_Friend::getFriends($uid);
        if ( $fids ) {
            require_once 'Mdal/Disney/User.php';
            $mdalUser = Mdal_Disney_User::getDefaultInstance();
            //get user app friend id list
            $appFids = $mdalUser->getAppFids($fids);

            if ( $appFids ) {
                require_once 'Mdal/Disney/Notice.php';
                $mdalNotice = Mdal_Disney_Notice::getDefaultInstance();

                $notice = array('title' => $title,
                                'actor_uid' => $uid,
                                'pid' => $cupInfo['cid'],
                                'type' => 3,
                                'create_time' => time());
                
                for ( $j = 0, $jCount = count($appFids); $j < $jCount; $j++ ) {
                    $notice['uid'] = $appFids[$j]['uid'];
                    //insert notice
                    $mdalNotice->insertNotice($notice);
                }
            }
        }
                
        //send activity
        require_once 'Bll/Restful.php';
        //get restful object
        $restful = Bll_Restful::getInstance($uid, $appId);
        $picUrl = Zend_Registry::get('static') . "/apps/disney/img/award_activity/".$cupInfo['icon'].".gif";
        $restful->createActivityWithPic(array('title'=>$title), $picUrl, 'image/gif');*/
    }
    
    public function updateUserCup($uid, $cid, $id)
    {
        $result = -1;
        
        try {
            $this->_wdb->beginTransaction();
            
            $point = 0;
            switch ($cid) {
                case 1 :
                    $point = 50;
                    break;
                case 2 :
                    $point = 100;
                    break;
                case 3 :
                    $point = 150;
                    break;
                case 4 :
                    $point = 200;
                    break;
                case 5 :
                    $point = 50;
                    break;
                case 6 :
                    $point = 300;
                    break;
                case 7 :
                    $point = 300;
                    break;
                case 8 :
                    $point = 300;
                    break;
                case 9 :
                    $point = 300;
                    break;
                case 10 :
                    $point = 50;
                    break;
                case 11<=$cid && $cid<=18 :
                    $point = 30;
                    break;
                case 19 :
                    $point = 50;
                    break;
            }
            
            //update cup status
            require_once 'Mdal/Disney/Cup.php';
            $mdalCup = Mdal_Disney_Cup::getDefaultInstance();
            $mdalCup->updateCupStatus($id);
        
            //add user point
            require_once 'Mdal/Disney/User.php';
            $mdalUser = Mdal_Disney_User::getDefaultInstance();
            $mdalUser->updateUserPoint($uid, $point);
        
            $this->_wdb->commit();
            
            $result = 1;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
        }
        
        return $result;
    }

    /**
     * send area cup
     *
     * @param integer $uid
     * @param integer $pid
     * @param integer $appId
     * @return string
     */
    public function sendAreaCup($uid, $pid)
    {
        require_once 'Mdal/Disney/Cup.php';
        $mdalCup = Mdal_Disney_Cup::getDefaultInstance();
                 
        require_once 'Mdal/Disney/User.php';
        $mdalUser = Mdal_Disney_User::getDefaultInstance();
                
        require_once 'Mdal/Disney/Place.php';
        $mdalPlace = Mdal_Disney_Place::getDefaultInstance();
        
        //get place info by pid
        $placeInfo = $mdalPlace->getPlaceById($pid);
        
        //get cup id by area id
        switch ($placeInfo['aid']) {
            case 1 :
                $cid = 18;
                break;
            case 2 :
                $cid = 17;
                break;
            case 3 :
                $cid = 16;
                break;
            case 4 :
                $cid = 15;
                break;
            case 5 :
                $cid = 14;
                break;
            case 6 :
                $cid = 13;
                break;
            case 7 :
                $cid = 12;
                break;
            case 8 :
                $cid = 11;
                break;
        }
        
        //get user cup info
        $userCup = $mdalCup->getUserCup($uid, $cid);
        
        if ( !$userCup ) {   
            //get pid list by area id
            $pidArray = $mdalPlace->getPlaceListByAid($placeInfo['aid']);
            
            //get user award count by 
            $userAwardCount = $mdalUser->getUserAwardCountByAid($uid, $placeInfo['aid']);
            
            //get cup  
            if ( count($pidArray) <= $userAwardCount ) {
                //insert cup
                $mdalCup->insertCup($uid, $cid);
                
                //get all award count
                $allAwardCount = $mdalPlace->getAllPlaceCount();
                $userAllCount = $mdalUser->getUserAwardCountAllArea($uid);
                if ($allAwardCount == $userAllCount) {
                    $mdalCup->insertCup($uid, 19);
                }
            }
        }
    }
    
    /**
     * buy game ticket
     *
     * @param integer $uid
     * @param integer $ticketCount
     * @param integer $mixiPoint
     * @return string
     */
    public function buyTicket($uid, $ticketCount, $mixiPoint)
    {
        $result = -1;
        
        require_once 'Mdal/Disney/User.php';
        $mdalUser = Mdal_Disney_User::getDefaultInstance();
        
        //check mixi point 
        if ( !$mixiPoint ) {
            return -2;
        }
        
        try {
            $this->_wdb->beginTransaction();
            
            //add user game ticket
            $mdalUser->updateUserGameTicket($uid, $ticketCount);
            
            $this->_wdb->commit();

            $result = 1;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return -1;
        }
        
        return $result;
    }

    /**
     * delete trade apply
     *
     * @param integer $uid
     * @return string
     */
    public function deleteTradeApply($uid)
    {
        $result = -1;
        
        require_once 'Mdal/Disney/User.php';
        $mdalUser = Mdal_Disney_User::getDefaultInstance();
        
        require_once 'Mdal/Disney/Notice.php';
        $mdalNotice = Mdal_Disney_Notice::getDefaultInstance();
        
        try {
            $this->_wdb->beginTransaction();
            
            //get trade user list
            $tradeUserList = $mdalUser->getTradeApplyByFid($uid);
                    
            for ( $i = 0,$iCount = count($tradeUserList); $i < $iCount; $i++ ) {
                if ( time() - $tradeUserList[$i]['create_time']  > 7*24*60*60 ) {
                    //delete trade apply info and notice
                    $mdalUser->deleteTradeApply($tradeUserList[$i]['nid']);
                    $mdalNotice->deleteNotice($tradeUserList[$i]['nid']);
                }
            }
            
            $this->_wdb->commit();

            $result = 1;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return -1;
        }
        
        return $result;
    }   
    
    public function checkDistince($uid, $userInfo, $hasShoes, &$userShoes)
    {
        $result = false;        
            
        require_once 'Mdal/Disney/User.php';
        $mdalUser = Mdal_Disney_User::getDefaultInstance();
        
        require_once 'Mdal/Disney/Shoes.php';
        $mdalShoes = Mdal_Disney_Shoes::getDefaultInstance();
        $userShoes = $mdalShoes->getUserShoes($uid);
        
        try {
            $this->_wdb->beginTransaction();
            
            //update user 
            $mdalUser->updateUser($uid, $userInfo);
            
            //update user shoes
            if ($hasShoes) {
                $mdalShoes->updateShoesCount($uid);
            }
            
            $this->_wdb->commit();

            $result = true;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return false;
        }
        
        $userShoes['times'] = $userShoes['times'] - 1;
        return $result;
    }
}
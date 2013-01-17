<?php
/** @see Bll_Abstract.php */
require_once 'Bll/Abstract.php';

/**
 * disney user logic's Operation
 *
 * @package    Mbll
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/10/12    Liz
 */
class Mbll_Disney_User extends Bll_Abstract
{
    /**
     * check user in joined
     *
     * @param integer $uid
     * @return boolean
     */
    public function isJoined($uid)
    {
        require_once 'Mdal/Disney/User.php';
        $mdalDisneyUser = Mdal_Disney_User::getDefaultInstance();
        //get user info
        return $mdalDisneyUser->isInApp($uid);
    }

    /**
     * join app
     *
     * @param integer $uid
     * @param integer $appId
     * @param integer $inviterId
     * @return boolean
     */
    public function join($uid, $appId)
    {        
        $result = false;

        require_once 'Mdal/Disney/User.php';
        $mdalDisneyUser = Mdal_Disney_User::getDefaultInstance();
        
        require_once 'Mdal/Disney/Invite.php';
        $mdalInvite = Mdal_Disney_Invite::getDefaultInstance();
        //get invite user info
        $inviteInfo = $mdalInvite->getInviteUserInfo($uid);
        
        try {
            $this->_wdb->beginTransaction();
            
            $time = time();
            $userInfo = array(
                'uid' => $uid,
                'mixi_point' => 0,
                'game_point' => 0,
                'game_ticket' => 2,
                'create_time' => $time
            );

            $mdalDisneyUser->insertUser($userInfo);
            
            //insert into disney_mymixi
            $mdalDisneyUser->insertMymixi(array('uid'=>$uid, 'mymixi'=>1));
            
            //notice title
            /*$title = 'ﾙｰﾚｯﾄﾁｹｯﾄをGETしました!';
    
            $notice = array('uid' => $uid,
                            'actor_uid' => $uid,
                            'title' => $title,
                            'type' => 1,
                            'create_time' => time());
            //insert notice
            $mdalNotice->insertNotice($notice);*/
            
            //now use invite api for send ticket            
            if ( $inviteInfo ) {
                require_once 'Mdal/Disney/Notice.php';
                $mdalNotice = Mdal_Disney_Notice::getDefaultInstance();
                
                $title = 'ゲームチケットをGETしました!';
                $notice = array('uid' => $uid,
                                'title' => $title,
                                'actor_uid' => $uid,
                                'type' => 9,
                                'create_time' => time());
                $mdalNotice->insertNotice($notice);
                
                for ( $i = 0, $iCount = count($inviteInfo); $i < $iCount; $i++ ) {
                    $notice['uid'] = $inviteInfo[$i]['uid'];
                    //insert notice
                    $mdalNotice->insertNotice($notice);
                    
                    //update user game ticket.                    
                    $mdalDisneyUser->updateUserGameTicket($inviteInfo[$i]['uid'], 2);
                }                
            }
            
            $this->_wdb->commit();

            $result = true;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return false;
        }

        require_once 'Bll/User.php';
        $user = Bll_User::getPerson($uid);
        $title = $user->getDisplayName() . 'さんが「ﾃﾞｨｽﾞﾆｰご当地ｺﾚｸｼｮﾝ」を追加しました';
        
        require_once 'Bll/Restful.php';
        //get restful object
        $restful = Bll_Restful::getInstance($uid, $appId);
        $restful->createActivity(array('title'=>$title));
        
        /*This can't be see.
        if ( $inviteInfo ) {
            $inviteActivityTitle = 'ルーレットのゲームチケットをGETしました！';
            $restful->createActivity(array('title'=>$inviteActivityTitle));
        }*/
        
        return $result;
    }

    /**
     * set user home
     *
     * @param integer $uid
     * @param integer $pid
     * @return boolean
     */
    public function setHome($uid, $pid, $isFirst = null )
    {
        $result = array('status'=>-1);
        
        require_once 'Mdal/Disney/Place.php';
        $mdalPlace = Mdal_Disney_Place::getDefaultInstance();
        //get place info by id
        $placeInfo = $mdalPlace->getPlaceById($pid);
        if ( !$placeInfo ) {
            return $result;
        }

        require_once 'Mdal/Disney/User.php';
        $mdalUser = Mdal_Disney_User::getDefaultInstance();
        //get user disney info
        $disneyUser = $mdalUser->getUser($uid);
        if ( time() - $disneyUser['last_home_time'] < 30*24*60*60 ) {
            return array('status'=>-2);
        }
        
        try {
            $this->_wdb->beginTransaction();
            
            if ($isFirst) {
                $userInfo = array('current_place' => $pid,
                                  'last_lon' => $placeInfo['longitude'],
                                  'last_lat' => $placeInfo['latitude'],
                                  'game_start' => 0,
                                  'last_home_time' => time());
            }
            else {
                $userInfo = array('current_place' => $pid,
                                  'last_lon' => $placeInfo['longitude'],
                                  'last_lat' => $placeInfo['latitude'],
                                  'game_start' => 0,
                                  'last_target_place' => $pid,
                                  'last_home_time' => time());
                              
                //add user award
                $mdalUser->addUserAward($uid, $pid);
                
                require_once 'Mbll/Disney/Index.php';
                $mbllIndex = new Mbll_Disney_Index();
                $mbllIndex->sendAreaCup($uid, $pid);
            }

            //update user info
            $mdalUser->updateUser($uid, $userInfo);
                    
            $this->_wdb->commit();
            
            $result['placeInfo'] = $placeInfo;
            $result['status'] = 1;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return array('status'=>-1);
        }

        return $result;
    }
    
    /**
     * invite disney user
     *
     * @param integer $uid
     * @param integer $invite_uid
     * @return boolean
     */
    public function inviteDisneyUser($uid, $invite_uid=0, $app_id)
    {
        return true;
        /*
        $result = false;
        
        require_once 'Mdal/Disney/User.php';
        $mdalUser = Mdal_Disney_User::getDefaultInstance();
        
        try {
            $this->_wdb->beginTransaction();
            
            //update disney user                      
            $mdalUser->updateUser($uid, array('invite_uid'=>$invite_uid));
            
            $hasInvite = $mdalUser->checkInviteSuccess($invite_uid, $uid);
            
            if ($hasInvite) {
                require_once 'Mdal/Disney/Notice.php';
                $mdalNotice = Mdal_Disney_Notice::getDefaultInstance();
                
                $title = 'ｹﾞｰﾑﾁｹｯﾄをGETしました!';
                $notice = array('uid' => $invite_uid,
                                'title' => $title,
                                'actor_uid' => $uid,
                                'type' => 9,
                                'create_time' => time());
                $mdalNotice->insertNotice($notice);
                    
                //send gift invite uid
                $mdalUser->updateUserGameTicket($invite_uid, 2);
                
                //send gift uid
                $mdalUser->updateUserGameTicket($uid, 2);
            }
            
            $this->_wdb->commit();
            $result = true;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
        }
        
        if ($hasInvite) {
            require_once 'Bll/Restful.php';
            //get restful object
            $restful = Bll_Restful::getInstance($invite_uid, $app_id);
            $inviteActivityTitle = 'ルーレットのゲームチケットをGETしました！';
            $restful->createActivity(array('title'=>$inviteActivityTitle));
        }
        
        return $result;*/
    }
    
    /**
     * remove disney user
     *
     * @param integer $uid
     * @return boolean
     */
    public function removeDisneyUser($uid)
    {
        return true;
        /*
        $result = false;
        
        require_once 'Mdal/Disney/User.php';
        $mdalUser = Mdal_Disney_User::getDefaultInstance();
        
        try {
            $this->_wdb->beginTransaction();
            
            //remove disney_desktop_award
            $mdalUser->deleteUser('disney_desktop_award', 'uid', $uid);
            
            //remove disney_download_award
            $mdalUser->deleteUser('disney_download_award', 'uid', $uid);
            
            //remove disney_flash_point
            $mdalUser->deleteUser('disney_flash_point', 'uid', $uid);
            
            //remove disney_invite
            $mdalUser->deleteUser('disney_invite', 'uid', $uid);
            
            //remove disney_log_check
            $mdalUser->deleteUser('disney_log_check', 'uid', $uid);
            
            //remove disney_log_getcurrent
            $mdalUser->deleteUser('disney_log_getcurrent', 'uid', $uid);
            
            //remove disney_log_pay
            $mdalUser->deleteUser('disney_log_pay', 'uid', $uid);
            
            //remove disney_log_ticket
            $mdalUser->deleteUser('disney_log_ticket', 'uid', $uid);
            
            //remove disney_log_trade
            $mdalUser->deleteUser('disney_log_trade', 'uid', $uid);
            
            //remove disney_mymixi
            $mdalUser->deleteUser('disney_mymixi', 'uid', $uid);
            
            //remove disney_notice
            $mdalUser->deleteUser('disney_notice', 'uid', $uid);
            $mdalUser->deleteUser('disney_notice', 'actor_uid', $uid);
            
            //remove disney_send_award
            $mdalUser->deleteUser('disney_send_award', 'uid', $uid);
            $mdalUser->deleteUser('disney_send_award', 'fid', $uid);            
            
            //remove disney_trade
            $mdalUser->deleteUser('disney_trade', 'uid', $uid);
            $mdalUser->deleteUser('disney_trade', 'fid', $uid);
            
            //remove disney_user
            $mdalUser->deleteUser('disney_user', 'uid', $uid);
            
            //remove disney_user_award
            $mdalUser->deleteUser('disney_user_award', 'uid', $uid);
            
            //remove disney_user_cup            
            $mdalUser->deleteUser('disney_user_cup', 'uid', $uid);
            
            $this->_wdb->commit();
            $result = true;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
        }
        
        return $result;*/
    }
}
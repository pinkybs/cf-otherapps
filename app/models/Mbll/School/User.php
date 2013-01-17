<?php

require_once 'Mbll/Abstract.php';

/**
 * Mixi App School User logic Operation
 *
 * @package    Mbll/School
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/11/09
 */
final class Mbll_School_User extends Mbll_Abstract
{

    /**
     * is school's app user
     *
     * @param string $uid
     * @return boolean
     */
    public function isJoined($uid)
    {
        require_once 'Mdal/School/User.php';
        $mdalUser = Mdal_School_User::getDefaultInstance();
        $rowUser = $mdalUser->getUser($uid);
        if (empty($rowUser) || 1 == $rowUser['status']) {
            return false;
        }
        return true;
    }

    /**
     * new school user
     *
     * @param integer $uid
     * @param integer $appid
     * @return boolean
     */
    public function newSchoolUser($uid, $appid)
    {
        try {
            require_once 'Mdal/School/User.php';
            $mdalUser = Mdal_School_User::getDefaultInstance();
            require_once 'Mdal/School/Timepart.php';
            $mdalTimepart = Mdal_School_Timepart::getDefaultInstance();
			require_once 'Mdal/School/Design.php';
            $mdalDesign = Mdal_School_Design::getDefaultInstance();

            $this->_wdb->beginTransaction();

            $rowSchoolUser = $mdalUser->getUserLock($uid);
            //new user
            if (empty($rowSchoolUser)) {
                //insert user
                $aryInfo = array();
                $aryInfo['uid'] = $uid;
                $aryInfo['create_time'] = time();
                $mdalUser->insertUser($aryInfo);

                //insert user timepart
                $mdalTimepart->deleteUserTimepart($uid);
                $aryTimepart = array();
                $aryTimepart['uid'] = $uid;
                $aryTimepart['part_minutes'] = 90;
                $aryTimepart['part'] = 1;
                $aryTimepart['start_h'] = '09';
                $aryTimepart['start_m'] = '00';
                $mdalTimepart->insertTimepart($aryTimepart);
                $aryTimepart['part'] = 2;
                $aryTimepart['start_h'] = '10';
                $aryTimepart['start_m'] = '45';
                $mdalTimepart->insertTimepart($aryTimepart);
                $aryTimepart['part'] = 3;
                $aryTimepart['start_h'] = '13';
                $aryTimepart['start_m'] = '30';
                $mdalTimepart->insertTimepart($aryTimepart);
                $aryTimepart['part'] = 4;
                $aryTimepart['start_h'] = '15';
                $aryTimepart['start_m'] = '15';
                $mdalTimepart->insertTimepart($aryTimepart);
                $aryTimepart['part'] = 5;
                $aryTimepart['start_h'] = '17';
                $aryTimepart['start_m'] = '00';
                $mdalTimepart->insertTimepart($aryTimepart);
                $aryTimepart['part'] = 6;
                $aryTimepart['start_h'] = '18';
                $aryTimepart['start_m'] = '45';
                $mdalTimepart->insertTimepart($aryTimepart);
                $aryTimepart['start_h'] = '';
                $aryTimepart['start_m'] = '';
                for ($i = 7; $i <= 14; $i++) {
                    $aryTimepart['part'] = $i;
                    $aryTimepart['is_hide'] = 1;
                    $mdalTimepart->insertTimepart($aryTimepart);
                }

                //insert user design
                $mdalDesign->deleteDesign($uid);
                $aryDesign = array();
    			$aryDesign['uid'] = $uid;
    			$aryDesign['did'] = 1;
    			$aryDesign['create_time'] = time();
    			$mdalDesign->insertDesign($aryDesign);
            }
            //joined user
            else {
                $mdalUser->updateUser(array('status' => 0), $uid);
            }

            $this->_wdb->commit();
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            debug_log('Mbll/School/User/newSchoolUser:' . $e->getMessage());
            return false;
        }

        //invite complete logic
        try {
            //if user is invited, auto add lottery_chance to inviter
            require_once 'Mdal/School/Message.php';
            $mdalMessage = Mdal_School_Message::getDefaultInstance();

            require_once 'Bll/Invite.php';
            $inviterArray = Bll_Invite::get($appid, $uid);//test
            //$inviterArray = Bll_Invite::get(12235, $uid);//real
            if ($inviterArray) {
                foreach ($inviterArray as $inviterId) {
                    $mdalUser->addUserLotteryChance($inviterId);
                    $aryMsg = array();
                    $aryMsg['uid'] = $uid;
                    $aryMsg['target_uid'] = $inviterId;
                    $aryMsg['type'] = 5;
                    $aryMsg['create_time'] = time();
                    $mdalMessage->insertMessage($aryMsg);
            	}
            }
        }
        catch (Exception $e1) {
            debug_log('Mbll/School/User/newSchoolUser-inviter-logic:' . $e1->getMessage());
        }
        return true;
    }

	/**
     * remove school user
     *
     * @param integer $uid
     * @return boolean
     */
    public function removeSchoolUser($uid)
    {
        try {
            require_once 'Mdal/School/User.php';
            $mdalUser = Mdal_School_User::getDefaultInstance();
            require_once 'Mdal/School/Timepart.php';
            $mdalTimepart = Mdal_School_Timepart::getDefaultInstance();
			require_once 'Mdal/School/Design.php';
            $mdalDesign = Mdal_School_Design::getDefaultInstance();
            require_once 'Mdal/School/Class.php';
			$mdalClass = Mdal_School_Class::getDefaultInstance();

			$rowUser = $mdalUser->getUser($uid);
			if (empty($rowUser) || 1 == $rowUser['status']) {
			    return true;
			}

            $this->_wdb->beginTransaction();

            //delete class member and vote info
            $cids = $mdalClass->getCidsById($uid);
			foreach ($cids as $value) {
				//check class isexists
				$rowClass = $mdalClass->getClassInfo($value['cid']);
				if (empty($rowClass)) {
				    continue;
				}
			    //update class member count
				$mdalClass->updateClassMemberCount($value['cid'], 0);
				if ($mdalClass->getVotedInfo($value['cid'], $uid)) {
					//update class vote count
					$mdalClass->updateClassVoteCount($value['cid'], 0);
				}
			}
            $mdalClass->delClassMember(0, $uid);
            $mdalClass->deleteVoteByUid($uid);
            //delete user timepart
            $mdalTimepart->delAllScheduleById($uid);
            //$mdalTimepart->deleteUserTimepart($uid);
            //delete user design
            //$mdalDesign->deleteDesign($uid);
            //delete user
            $delInfo = array();
            $delInfo['status'] = 1;//deleted
            $delInfo['school_code'] = '';
            $delInfo['school_type'] = '';
            $delInfo['login_day_count'] = 0;
            //$delInfo['lottery_chance'] = 0;
            //$delInfo['star_count'] = 0;
            //$delInfo['mode'] = 0;
            //$delInfo['design_type'] = 1;
            $delInfo['is_privacy_showed'] = 0;
            $delInfo['is_ashiato_showed'] = 0;
            $mdalUser->updateUser($delInfo, $uid);

            $this->_wdb->commit();
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            debug_log('Mbll/School/User/removeSchoolUser:' . $e->getMessage());
            return false;
        }

        return true;
    }

	/**
     * update user Login Count
     *
     * @param integer $uid
     * @return boolean
     */
    public function updateLoginCount($uid)
    {
        try {
            require_once 'Mdal/School/User.php';
            $mdalUser = Mdal_School_User::getDefaultInstance();
            $blnChanceAdd = false;

            $this->_wdb->beginTransaction();
            $rowSchoolUser = $mdalUser->getUserLock($uid);
            if (empty($rowSchoolUser)) {
                $this->_wdb->rollBack();
                return false;
            }
            $loginCnt = (int)$rowSchoolUser['login_day_count'] + 1;
            $aryInfo = array();
            $aryInfo['login_day_count'] = $loginCnt;
            $aryInfo['last_login_time'] = time();
            /*if (0 == $loginCnt%5) {
                $aryInfo['lottery_chance'] = (int)$rowSchoolUser['lottery_chance'] + 1;
                $blnChanceAdd = true;
            }*/
            $mdalUser->updateUser($aryInfo, $uid);
            $this->_wdb->commit();

        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            debug_log('Mbll/School/User/updateLoginCount:' . $e->getMessage());
            return false;
        }

        if ($blnChanceAdd) {
            try {
                require_once 'Mdal/School/Message.php';
                $mdalMessage = Mdal_School_Message::getDefaultInstance();
                $aryMsg = array();
                $aryMsg['uid'] = $uid;
                $aryMsg['target_uid'] = $uid;
                $aryMsg['type'] = 5;
                $aryMsg['create_time'] = time();
                $mdalMessage->insertMessage($aryMsg);
            }
            catch (Exception $e1) {
                debug_log('Mbll/School/User/updateLoginCount-:' . $e1->getMessage());
            }
        }
        return true;
    }

	/**
     * update user visit foot
     *
     * @param integer $uid
     * @param integer $visit_uid
     * @return boolean
     */
    public function updateVisitFoot($uid, $visit_uid)
    {
        try {
            require_once 'Mdal/School/VisitFoot.php';
            $mdalVisitFoot = Mdal_School_VisitFoot::getDefaultInstance();

            $this->_wdb->beginTransaction();

            $rowVisit = $mdalVisitFoot->getVisitFoot($uid, $visit_uid);
            if (empty($rowVisit)) {
                $mdalVisitFoot->insertVisitFoot(array('uid'=>$uid, 'visit_uid'=>$visit_uid, 'update_time'=>time()));
            }
            else {
                $mdalVisitFoot->updateVisitFoot($uid, $visit_uid, time());
            }

            $this->_wdb->commit();
            return true;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            debug_log('Mbll/School/User/updateVisitFoot:' . $e->getMessage());
            return false;
        }
    }

/******************************************************/
/**
 * xial **********************************************************
 */

    /**
     * update design type
     *
     * @param integer $uid
     * @param integer $did
     *
     * @return boolean
     */
    public function updateUserDesign($uid, $did)
    {
    	try {
            require_once 'Mdal/School/User.php';
            $mdalUser = Mdal_School_User::getDefaultInstance();
            $this->_wdb->beginTransaction();

			$rowUser = $mdalUser->getUserLock($uid);
			if ($rowUser) {
				$mdalUser->updateUser(array('design_type' => $did), $uid);
			}

            $this->_wdb->commit();
            return true;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            debug_log('Mbll/School/User/updateUserDesign:' . $e->getMessage());
            return false;
        }
        return true;
    }

    public function updateUserLotteryChance($uid)
    {
		try {
            require_once 'Mdal/School/User.php';
            $mdalUser = Mdal_School_User::getDefaultInstance();
            $this->_wdb->beginTransaction();

			$rowUser = $mdalUser->getUserLock($uid);
			if ($rowUser) {
				$mdalUser->updateUser(array('lottery_chance' => $rowUser['lottery_chance'] -1), $uid);
			}
            $this->_wdb->commit();
            return true;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            debug_log('Mbll/School/User/updateUserLotteryChance:' . $e->getMessage());
            return false;
        }
        return true;
    }
}
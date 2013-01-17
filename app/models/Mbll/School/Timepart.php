<?php

require_once 'Mbll/Abstract.php';

/**
 * Mixi App School Timepart logic Operation
 *
 * @package    Mbll/School
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/11/12
 */
final class Mbll_School_Timepart extends Mbll_Abstract
{

	/**
     * update user's time part
     *
     * @param integer $uid
     * @param array $aryInfo
     * @return boolean
     */
    public function setTimepart($uid, $aryInfo)
    {
        try {
            require_once 'Mdal/School/Timepart.php';
            $mdalTimepart = Mdal_School_Timepart::getDefaultInstance();

            $this->_wdb->beginTransaction();
            foreach ($aryInfo as $key=>$tdata) {
                $mdalTimepart->updateTimepart($tdata, $uid, $tdata['part']);
            }
            $this->_wdb->commit();
            return true;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            debug_log('Mbll/School/Timepart/setTimepart:' . $e->getMessage());
            return false;
        }
    }

/**
 * xial****************************************************
 */

	/**
	 * insert timepart
	 *
	 * @param array $info
	 * @return commit : integer, rollBack : false
	 */
	public function insertTimepartSchedule($info)
	{
		try {
			require_once 'Mdal/School/Timepart.php';
			$mdalTimepart = Mdal_School_Timepart::getDefaultInstance();

			require_once 'Mdal/School/Class.php';
			$mdalClass = Mdal_School_Class::getDefaultInstance();

			require_once 'Mdal/School/Message.php';
			$mdalMessage = Mdal_School_Message::getDefaultInstance();

			if ($info['cid'] == null && $info['cname'] == null && $info['tname'] == null) {
				return false;
			} else {
				$this->_wdb->beginTransaction();
				//check cid is null
				if ($info['cid'] == null) {
					$classAry = array();
					$classAry['name'] = $info['cname'];
					$classAry['teacher'] = $info['tname'];
					$classAry['school_code'] = $info['school_code'];
					$classAry['uid'] = $info['uid'];
					$classAry['create_time'] = time();
					$classAry['last_new_update_time'] = time();
					//insert class
					$info['cid'] = $mdalClass->insertClass($classAry);
				}

				//check class isexists
				$rowClass = $mdalClass->getClassInfoLock($info['cid']);
				if (empty($rowClass)) {
				    $this->_wdb->rollBack();
				    return false;
				} else {
					//is class member
					$isClassMember = $mdalClass->isClassMember($info['cid'], $info['uid']);
					//insert member
					if (empty($isClassMember)) {
						$memberInfo = array();
						$memberInfo['cid'] = $info['cid'];
						$memberInfo['uid'] = $info['uid'];
						$memberInfo['create_time'] = time();
						$mdalClass->insertMember($memberInfo);
						$mdalClass->updateClass(array('last_new_update_time' => time()), $info['cid']);
						//update class member count
						$mdalClass->updateClassMemberCount($info['cid']);
					}

					$timepartAry = array();
					$timepartAry['uid'] = $info['uid'];
					$timepartAry['wday'] = $info['wday'];
					$timepartAry['cid'] = $info['cid'];
					$timepartAry['part'] = $info['part'];
					$timepartAry['school_code'] = $info['school_code'];
					$timepartAry['create_time'] = time();

					$rowTimepart = $mdalTimepart->getUserTimepart($info['uid'], $info['part']);
					if ($rowTimepart['is_hide'] && empty($rowTimepart['start_h']) && empty($rowTimepart['start_m'])) {
						for ($i = 1; $i < $info['part'] ;) {
							$timePartMax = $mdalTimepart->getMaxPartIshideById($info['uid']);
							$i = $timePartMax['part'];
							$is_hide = 1;
							$part = $i + 1 ;

							$strSpaceTime = strftime('%H:%M', strtotime($timePartMax['start_h'] . ':' . $timePartMax['start_m']) + $timePartMax['part_minutes'] * 60);
		                    $arySpaceTime = explode(':', $strSpaceTime);
		                    $start_h = $arySpaceTime[0];
                    		$start_m = $arySpaceTime[1];

							if ($part == $info['part']) {
								$is_hide = 0;
							}

							$arySchedule = array('start_h' => $start_h, 'start_m' => $start_m, 'is_hide' => $is_hide);
							$mdalTimepart->updateTimePart($arySchedule, $info['uid'], $part);
							$i++ ;
						}
					}

					//select message invite
					$isId = $mdalMessage->isSameTimeScheduleInvite($info['uid'], $info['wday'], $info['part']);
					if ($isId) {
						//delete all same's time
						$mdalMessage->deleteInvite($info['uid'], $info['wday'], $info['part']);
					}

					$rowTimepartClass = $mdalTimepart->getTimepartScheduleByPk($info['uid'], $info['wday'], $info['part']);
					//schedule is exists
					if ($rowTimepartClass) {
						$this->_wdb->rollBack();
						return false;
					}
					//insert timepart schedule
					$mdalTimepart->insertTimepartSchedule($timepartAry);
					$mdalTimepart->updateTimepart(array('is_hide' => 0), $info['uid'], $info['part']);
				}
			}
			$this->_wdb->commit();
			return $info['cid'];
		}
		catch (Exception $e) {
			$this->_wdb->rollBack();
			debug_log('Mbll/School/timepart/insertTimepartSchedule:' . $e->getMessage());
			return false;
		}
		return $info['cid'];
	}

	/**
	 * update timepart scheaule
	 *
	 * @param array $info
	 * @return integer
	 */
	public function updateTimePartScheaule($info)
	{
		try {
			require_once 'Mdal/School/Timepart.php';
			$mdalTimepart = Mdal_School_Timepart::getDefaultInstance();

			require_once 'Mdal/School/Class.php';
			$mdalClass = Mdal_School_Class::getDefaultInstance();

			require_once 'Mdal/School/Message.php';
			$mdalMessage = Mdal_School_Message::getDefaultInstance();

			if ($info['cid'] == null) {
				return false;
			} else {
				$this->_wdb->beginTransaction();
				$rowTimepartClass = $mdalTimepart->getTimepartScheduleByPk($info['uid'], $info['wday'], $info['part']);
				if ($rowTimepartClass) {
					if ($info['cname'] != null && $info['tname'] != null) {
						$classAry = array();
						$classAry['name'] = $info['cname'];
						$classAry['teacher'] = $info['tname'];
						$classAry['school_code'] = $info['school_code'];
						$classAry['uid'] = $info['uid'];
						$classAry['create_time'] = time();
						$classAry['last_new_update_time'] = time();
						//insert class
						$info['cid'] = $mdalClass->insertClass($classAry);
					}

					//check class isexists
					$rowClass = $mdalClass->getClassInfoLock($info['cid']);
					if (empty($rowClass)) {
					    $this->_wdb->rollBack();
					    return false;
					} else {
						//delete
						$oldClassMember = $mdalClass->isClassMember($rowTimepartClass['cid'], $info['uid']);
						if ($oldClassMember) {
							$cnt = $mdalTimepart->getCntCidById($rowTimepartClass['cid'], $info['uid']);
							if ($cnt == 1) {
								//delete classmember data
								$mdalClass->delClassMember($rowTimepartClass['cid'], $info['uid']);
								//update class member count - 1
								$mdalClass->updateClassMemberCount($rowTimepartClass['cid'], 0);
								if ($mdalClass->getVotedInfo($rowTimepartClass['cid'], $info['uid'])) {
										//update class vote count
										$mdalClass->updateClassVoteCount($rowTimepartClass['cid'], 0);
								}
							}
						}

						$isClassMember = $mdalClass->isClassMember($info['cid'], $info['uid']);
						//insert member
						if (empty($isClassMember)) {
							$memberInfo = array();
							$memberInfo['cid'] = $info['cid'];
							$memberInfo['uid'] = $info['uid'];
							$memberInfo['create_time'] = time();
							$mdalClass->insertMember($memberInfo);
							//update class member count + 1
							$mdalClass->updateClassMemberCount($info['cid']);
						}

						$timepartAry = array();
						$timepartAry['cid'] = $info['cid'];
						$timepartAry['create_time'] = time();

						$mdalTimepart->updateTimepartSchedule($timepartAry, $info['uid'], $info['part'], $info['wday']);

						//select message invite
						$isId = $mdalMessage->isSameTimeScheduleInvite($info['uid'], $info['wday'], $info['part']);
						if ($isId) {
							//delete all same's time invite
							$mdalMessage->deleteInvite($info['uid'], $info['wday'], $info['part']);
						}
					}
				}
			}
			$this->_wdb->commit();
			return $info['cid'];
		}
		catch (Exception $e) {
			$this->_wdb->rollBack();
			debug_log('Mbll/School/timepart/updateTimePartScheaule:' . $e->getMessage());
			return false;
		}
	}

	/**
	 * reset schedule
	 *
	 * @param integer $uid
	 * @param string $schoolToken
	 * @param string $schoolDivision
	 * @return boolean
	 */
	public function scheduleReset($uid, $schoolToken='', $schoolDivision='')
	{
		try {
			require_once 'Mdal/School/Timepart.php';
			$mdalTimepart = Mdal_School_Timepart::getDefaultInstance();

			require_once 'Mdal/School/Class.php';
			$mdalClass = Mdal_School_Class::getDefaultInstance();

			$this->_wdb->beginTransaction();
/*
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
			$mdalClass->deleteVoteByUid($uid);*/
			$mdalTimepart->delAllScheduleById($uid);

			//update school user token if token is not empty
			if (!empty($schoolToken) && !empty($schoolDivision)) {
			    require_once 'Mdal/School/User.php';
                $mdalUser = Mdal_School_User::getDefaultInstance();
                $mdalUser->updateUser(array('school_code' => $schoolToken, 'school_type' => $schoolDivision), $uid);
			}

			$this->_wdb->commit();

		} catch (Exception $e){
			$this->_wdb->rollBack();
			debug_log('Mbll/School/timepart/scheduleReset:' . $e->getMessage());
			return false;
		}

		return true;
	}

	/**
	 * school clear
	 *
	 * @param integer $uid
	 * @return boolean
	 */
	public function schoolClear($uid)
	{
		try {
			require_once 'Mdal/School/Timepart.php';
			$mdalTimepart = Mdal_School_Timepart::getDefaultInstance();
			require_once 'Mdal/School/User.php';
            $mdalUser = Mdal_School_User::getDefaultInstance();

			$this->_wdb->beginTransaction();
			$mdalTimepart->delAllScheduleById($uid);
			//clear user school info
            $mdalUser->updateUser(array('school_code' => '', 'school_type' => ''), $uid);
            //init timepart set
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

			$this->_wdb->commit();

		} catch (Exception $e){
			$this->_wdb->rollBack();
			debug_log('Mbll/School/timepart/schoolClear:' . $e->getMessage());
			return false;
		}

		return true;
	}

	/**
	 * delete schedule
	 *
	 * @param integer $uid
	 * @param integer $wday
	 * @param integer $part
	 * @param integer $cid
	 * @return boolean
	 */
	public function delSchedule($uid, $wday, $part, $cid)
	{
		try{
			require_once 'Mdal/School/Timepart.php';
			$mdalTimepart = Mdal_School_Timepart::getDefaultInstance();

			require_once 'Mdal/School/Class.php';
			$mdalClass = Mdal_School_Class::getDefaultInstance();

			$this->_wdb->beginTransaction();

			//check class isexists
			$rowClass = $mdalClass->getClassInfoLock($cid);
			if (empty($rowClass)) {
			    $this->_wdb->rollBack();
			    return false;
			} else {
				$cnt = $mdalTimepart->getCntCidById($cid, $uid);
				if ($cnt == 1) {
					//delete classmember data
					$mdalClass->delClassMember($cid, $uid);
					//update class member count - 1
					$mdalClass->updateClassMemberCount($cid, 0);
				}
			}
			$mdalTimepart->delSchedule($uid, $wday, $part);
			$this->_wdb->commit();

		}catch (Exception $e){
			$this->_wdb->rollBack();
			debug_log('Mbll/School/timepart/delSchedule:' . $e->getMessage());
			return false;
		}
		return true;
	}

	public function updateTimePart($info, $uid, $wday, $part)
	{
		try{
			require_once 'Mdal/School/Timepart.php';
			$mdalTimepart = Mdal_School_Timepart::getDefaultInstance();

			$this->_wdb->beginTransaction();

			$rowTimepartClass = $mdalTimepart->getTimepartScheduleByPk($uid, $wday, $part);
			if (empty($rowTimepartClass)) {
				$mdalTimepart->updateTimepart($info, $uid, $part);
			}
			$this->_wdb->commit();
			return true;
		}catch (Exception $e){
			$this->_wdb->rollBack();
			debug_log('Mbll/School/timepart/updateTimePart:' . $e->getMessage());
			return false;
		}
		return true;
	}
}
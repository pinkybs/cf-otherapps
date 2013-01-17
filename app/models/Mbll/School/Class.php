<?php

require_once 'Mbll/Abstract.php';

/**
 * Mixi App School Class logic Operation
 *
 * @package    Mbll/School
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/11/10
 */
final class Mbll_School_Class extends Mbll_Abstract
{

	/**
    * forecast class is [0-晴れ/1-曇り/2-雨]
    *
    * @param integer $isBad [0/1]
    * @param integer $cid
    * @param integer $uid
    * @return boolean
    */
	public function forecastClass($isBad, $cid, $uid)
	{
		try {
			require_once 'Mdal/School/Class.php';
			$mdalClass = Mdal_School_Class::getDefaultInstance();
		    if (!$mdalClass->isClassMember($cid, $uid)) {
			    return false;
            }

			$this->_wdb->beginTransaction();
			$rowClass = $mdalClass->getClassInfo($cid);
			if (empty($rowClass)) {
			    $this->_wdb->rollBack();
			    return false;
			}

			//update forecast
		    $mdalClass->updateClassMember(array('is_forecast_bad' => $isBad, 'forecast_time' => time()), $cid, $uid);
		    //$mdalClass->updateClass(array('last_new_update_time' => time()), $cid);
			$this->_wdb->commit();
		}
		catch (Exception $e) {
			$this->_wdb->rollBack();
			debug_log('Mbll/School/Class/forecastClass:' . $e->getMessage());
			return false;
		}
		return true;
	}

   /**
    * add class vote
    *
    * @param array $info
    * @param integer $cid
    * @param integer $uid
    * @return boolean
    */
	public function addVote($info, $cid, $uid)
	{
		try {
			require_once 'Mdal/School/Class.php';
			$mdalClass = Mdal_School_Class::getDefaultInstance();
		    if (!$mdalClass->isClassMember($cid, $uid)) {
			    return false;
            }

			$this->_wdb->beginTransaction();
			$rowClass = $mdalClass->getClassInfoLock($cid);
			if (empty($rowClass)) {
			    $this->_wdb->rollBack();
			    return false;
			}
			//insert /update vote
			$rowVote = $mdalClass->getVotedInfo($cid, $uid);
			if (empty($rowVote)) {
			    $info['cid'] = $cid;
			    $info['uid'] = $uid;
			    $info['create_time'] = time();
			    $result = $mdalClass->insertVote($info);
			    if ($result) {
			        $aryUpdClass = array('vote_count' => ((int)$rowClass['vote_count'] + 1), 'last_new_update_time' => time());
			        $mdalClass->updateClass($aryUpdClass, $cid);
			    }
			}
			else {
			    $mdalClass->updateVote($info, $cid, $uid);
			    $mdalClass->updateClass(array('last_new_update_time' => time()), $cid);
			}

			$this->_wdb->commit();
		}
		catch (Exception $e) {
			$this->_wdb->rollBack();
			debug_log('Mbll/School/Class/addVote:' . $e->getMessage());
			return false;
		}
		return true;
	}



/******************************************************/
/**
 * xial****************************************************
 */

	/**
	 * list class member contain is friend
	 *
	 * @param integer $cid
	 * @param integer $uid
	 * @param integer $pageIndex
	 * @param integer $pageSize
	 * @return array
	 */
   public function lstClassMember($cid, $uid, $pageIndex, $pageSize)
   {
		require_once 'Mdal/School/Class.php';
        $mdalClass = Mdal_School_Class::getDefaultInstance();
		$result = $mdalClass->getlstClassMember($cid, $pageIndex, $pageSize);

		require_once 'Bll/Friend.php';
		require_once 'Bll/User.php';
        foreach ($result as $key => $value) {
        	$result[$key]['is_friend'] = Bll_Friend::isFriend($uid, $value['uid']) ? '1' : '0';
        }
		Bll_User::appendPeople($result, 'uid');
        return $result;
   }

   /**
    * invited class
    *
    * @param array $info
    * @return boolean
    */
	public function inviteClass($info)
	{
		try {
			require_once 'Mdal/School/Message.php';
			$mdalMessage = Mdal_School_Message::getDefaultInstance();
			//check is exists
			$result = $mdalMessage->isInviteClassExites($info);
			if ($result) {
				$aryInfo = array('create_time' => time());
				$mdalMessage->updateMessage($aryInfo, $result['id']);
			}
			$mdalMessage->insertMessage($info);
			return true;
		}
		catch (Exception $e) {
			debug_log('Mbll/School/Class/inviteClass:' . $e->getMessage());
			return false;
		}
	}

	public function insertMember($info)
	{
		try {
			require_once 'Mdal/School/Class.php';
			$mdalClass = Mdal_School_Class::getDefaultInstance();
			$this->_wdb->beginTransaction();
			$mdalClass->insertMember($info);
			$this->_wdb->commit();
			return true;
		}
		catch (Exception $e) {
			$this->_wdb->rollBack();
			debug_log('Mbll/School/Class/insertMember:' . $e->getMessage());
			return false;
		}
	}

	/**
	 * insert class
	 *
	 * @param array $info
	 * @return integer
	 */
	public function insertClass($info)
	{
		try {
			require_once 'Mdal/School/Class.php';
			$mdalClass = Mdal_School_Class::getDefaultInstance();

			$this->_wdb->beginTransaction();
			$cid = $mdalClass->insertClass($info);
			$this->_wdb->commit();

			return $cid;
		}
		catch (Exception $e) {
			$this->_wdb->rollBack();
			debug_log('Mbll/School/Class/insertMember:' . $e->getMessage());
			return false;
		}
	}
}
<?php

require_once 'Mdal/Abstract.php';

/**
 * Mdal School
 * MixiApp school timepart Data Access Layer
 *
 * @package    Mdal/School
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/11/09  zhangxin
 */
class Mdal_School_Timepart extends Mdal_Abstract
{

    /**
     * class default instance
     * @var self instance
     */
    protected static $_instance;

    /**
     * return self's default instance
     *
     * @return self instance
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * list timepart
     *
     * @param integer $uid
     * @return array
     */
    public function listUserTimepart($uid)
    {
        $sql = 'SELECT * FROM school_timepart WHERE uid=:uid ORDER BY part';
        return $this->_rdb->fetchAll($sql, array('uid' => $uid));
    }

    /**
     * list user timepart used
     *
     * @param integer $uid
     * @return array
     */
    public function listUserTimepartUsed($uid)
    {
        $sql = 'SELECT * FROM school_timepart WHERE uid=:uid AND is_hide=0 ORDER BY part';
        return $this->_rdb->fetchAll($sql, array('uid' => $uid));
    }

    /**
     * insert Timepart
     *
     * @param array $info
     * @return integer
     */
    public function insertTimepart($info)
    {
        return $this->_wdb->insert('school_timepart', $info);
    }

    /**
     * update Timepart
     *
     * @param array $info
     * @param integer $uid
     * @param integer $part
     * @return integer
     */
    public function updateTimepart($info, $uid, $part)
    {
        $where = array($this->_wdb->quoteInto('uid=?', $uid),
                       $this->_wdb->quoteInto('part=?', $part));

        return $this->_wdb->update('school_timepart', $info, $where);
    }

	/**
     * delete Timepart
     *
     * @param integer $uid
     * @return integer
     */
    public function deleteUserTimepart($uid)
    {
        $sql = "DELETE FROM school_timepart WHERE uid=:uid ";
        return $this->_wdb->query($sql, array('uid' => $uid));
    }

    /**
     * user timepart
     *
     * @param integer $uid
     * @param integer $pid
     * @return array
     */
    public function getUserTimepart($uid, $pid)
    {
        $sql = "SELECT * FROM school_timepart WHERE uid=:uid AND part=:pid";
        return $this->_rdb->fetchRow($sql, array('uid' => $uid, 'pid' => $pid));
    }

	/**
     * is user has time part schedule
     *
     * @param integer $uid
     * @return boolean
     */
    public function hasTimepartSchedule($uid)
    {
        $sql = 'SELECT COUNT(uid) FROM school_timepart_schedule WHERE uid=:uid ';
        $result = $this->_rdb->fetchOne($sql, array('uid' => $uid));
        return $result > 0;
    }

	/**
     * get time part schedule by Pk
     *
     * @param integer $uid
     * @param integer $wday
     * @param integer $part
     * @return array
     */
    public function getTimepartScheduleByPk($uid, $wday, $part)
    {
        $sql = 'SELECT * FROM school_timepart_schedule WHERE uid=:uid AND wday=:wday AND part=:part ';
        return $this->_rdb->fetchRow($sql, array('uid' => $uid, 'wday' => $wday, 'part' => $part));
    }

    /**
     * check time part schedule is exists
     *
     * @param integer $uid
     * @param integer $wday
     * @param integer $part
     * @param integer $cid
     * @return array
     */
	public function isTimepartScheduleExists($uid, $wday, $part, $cid)
    {
        $sql = 'SELECT * FROM school_timepart_schedule WHERE uid=:uid AND wday=:wday AND part=:part AND cid=:cid';
        $result = $this->_rdb->fetchRow($sql, array('uid' => $uid, 'wday' => $wday, 'part' => $part, 'cid' => $cid));
        return $result == null ? 0 : 1;
    }

	/**
     * get user schedule by part
     *
     * @param integer $uid
     * @param integer $part
     * @return array
     */
    public function lstUserScheduleByPart($uid, $part)
    {
        $sql = "SELECT * FROM school_timepart_schedule WHERE uid=:uid AND part=:part ORDER BY wday ASC";
        return $this->_rdb->fetchAll($sql, array('uid' => $uid, 'part' => $part));
    }

	/**
     * get user schedule by class id
     *
     * @param integer $uid
     * @param integer $cid
     * @return array
     */
    public function lstUserScheduleByCid($uid, $cid)
    {
        $sql = "SELECT * FROM school_timepart_schedule WHERE uid=:uid AND cid=:cid ORDER BY wday ASC, part ASC";
        return $this->_rdb->fetchAll($sql, array('cid' => $cid, 'uid' => $uid));
    }

    /** xial
 * ********************************************************************
 */

    /**
     * get cid
     *
     * @param integer $school_code
     * @param integer $uid
     * @param integer $wday
     * @param integer $part
     * @param integer $pageindex
     * @param integer $pagesize
     * @return array
     */
    public function getLstCidsById($s_code, $uid, $wday, $part, $pageindex, $pagesize)
    {
    	$start = ($pageindex - 1) * $pagesize;

		$sql = "SELECT cid FROM school_timepart_schedule WHERE wday=:wday AND part=:part
				AND uid <> :uid AND school_code = :s_code GROUP BY cid LIMIT $start, $pagesize ";

		return $this->_rdb->fetchAll($sql, array('s_code' => $s_code, 'uid' => $uid, 'wday' => $wday, 'part' => $part));
    }
    /**
     * get time part
     *
     * @param integer $uid
     * @param integer $wday
     * @param integer $part
     * @return integer
     */
    public function getCntScheduleClassById($s_code, $uid, $wday, $part)
    {
		$sql = 'SELECT COUNT(1) FROM school_timepart_schedule WHERE wday=:wday AND part=:part
				AND uid<>:uid AND school_code=:s_code GROUP BY cid';

        return $this->_rdb->fetchOne($sql, array('s_code' => $s_code, 'uid' => $uid, 'wday' => $wday, 'part' => $part));
    }

    /**
     * get same time all cids
     *
     * @param integer $s_code
     * @param integer $uid
     * @param integer $wday
     * @param integer $part
     * @return array
     */
    public function getLstAllCidsById($s_code, $uid, $wday, $part)
    {
		$sql = "SELECT cid FROM school_timepart_schedule WHERE wday=:wday AND part=:part
				AND uid <> :uid AND school_code = :s_code ";
		return $this->_rdb->fetchAll($sql, array('s_code' => $s_code, 'uid' => $uid, 'wday' => $wday, 'part' => $part));
    }

    /**
     * delete user's all schedule
     *
     * @param integer $uid
     */
    public function delAllScheduleById($uid)
    {
    	$sql = "DELETE FROM school_timepart_schedule WHERE uid = :uid";
    	$this->_wdb->query($sql, array('uid' => $uid));
    }

	public function insertTimepartSchedule($info)
    {
        $this->_wdb->insert('school_timepart_schedule', $info);
    }

    /**
     * delete user's one schedule
     *
     * @param integer $uid
     * @param integer $wday
     * @param integer $part
     */
    public function delSchedule($uid, $wday, $part)
    {
		$sql = "DELETE FROM school_timepart_schedule WHERE uid = :uid AND wday = :wday
				AND part = :part";
    	$this->_wdb->query($sql, array('uid' => $uid, 'wday' => $wday, 'part' => $part));
    }

    /**
     * update time part schedule
     *
     * @param integer $info
     * @param integer $uid
     * @param integer $part
     * @param integer $wday
     * @return integer
     */
    public function updateTimepartSchedule($info, $uid, $part, $wday)
    {
        $where = array($this->_wdb->quoteInto('uid=?', $uid),
                       $this->_wdb->quoteInto('part=?', $part),
                       $this->_wdb->quoteInto('wday=?', $wday));

        return $this->_wdb->update('school_timepart_schedule', $info, $where);
    }

    public function getMaxPartIshideById($uid)
    {
    	$sql = " SELECT * FROM school_timepart WHERE part =
    			(SELECT MAX(part) FROM school_timepart WHERE uid=:uid AND start_h <> '' AND start_m <> '')AND uid=:uid";

    	return $this->_rdb->fetchRow($sql, array('uid' => $uid));
    }


    public function getCntCidById($cid, $uid)
    {
    	$sql = "SELECT COUNT(1) FROM school_timepart_schedule WHERE cid = :cid AND uid = :uid";
		return $this->_rdb->fetchOne($sql, array('cid' => $cid, 'uid' => $uid));
    }
}
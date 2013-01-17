<?php

require_once 'Mdal/Abstract.php';

class Mdal_School_Class extends Mdal_Abstract
{
    /**
     * user table name
     *
     * @var string
     */

    protected static $_instance;

    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * get class info
     *
     * @param integer $cid
     * @return array
     */
    public function getClassInfo($cid)
    {
        $sql = "SELECT * FROM school_class WHERE cid=:cid";

        return $this->_rdb->fetchRow($sql, array('cid' => $cid));
    }

	/**
     * get class info by lock
     *
     * @param integer $cid
     * @return array
     */
    public function getClassInfoLock($cid)
    {
        $sql = 'SELECT * FROM school_class WHERE cid=:cid FOR UPDATE';
        return $this->_rdb->fetchRow($sql, array('cid' => $cid));
    }

    /**
     * insert member
     *
     * @param integer $info
     */
    public function insertMember($info)
    {
		$this->_wdb->insert('school_class_member', $info);
    }

    public function insertClass($info)
    {
    	$this->_wdb->insert('school_class', $info);
    	return $this->_wdb->lastInsertId();
    }

    /* update Class
     *
     * @param array $info
     * @param integer $id
     * @return integer
     */
    public function updateClass($info, $cid)
    {
        $where = array($this->_wdb->quoteInto('cid=?', $cid));

        return $this->_wdb->update('school_class', $info, $where);
    }

    /**
     * list joined class ids by uid
     *
     * @param integer $uid
     * @return array
     */
    public function listJoinedClassIds($uid)
    {
        $sql = "SELECT cid FROM school_class_member WHERE uid=:uid";
        return $this->_rdb->fetchAll($sql, array('uid' => $uid));
    }

    /**
     * get class forecast count
     *
     * @param integer $cid
     * @param boolean $isBad [true - bad / false - good]
     * @return integer
     */
    public function getClassForecastCount($cid, $isBad=false)
    {
        $sql = "SELECT COUNT(uid) FROM school_class_member WHERE cid=:cid AND is_forecast_bad=0";
        if ($isBad) {
            $sql = "SELECT COUNT(uid) FROM school_class_member WHERE cid=:cid AND is_forecast_bad=1";
        }
        return $this->_rdb->fetchOne($sql, array('cid' => $cid));
    }

	/**
     * get class friend bad forecast count
     *
     * @param integer $cid
     * @param array $fids
     * @return integer
     */
    public function getClassFriendBadForecastCount($cid, $fids)
    {
        $ids = $this->_rdb->quote($fids);
        $sql = "SELECT COUNT(uid) FROM school_class_member WHERE cid=:cid AND uid IN ($ids) AND is_forecast_bad=1";
        return $this->_rdb->fetchOne($sql, array('cid' => $cid));
    }

	/**
     * get class not friend bad forecast count
     *
     * @param integer $cid
     * @param array $fids
     * @return integer
     */
    public function getClassNotFriendBadForecastCount($cid, $fids)
    {
        $ids = $this->_rdb->quote($fids);
        $sql = "SELECT COUNT(uid) FROM school_class_member WHERE cid=:cid AND uid NOT IN ($ids) AND is_forecast_bad=1";
        return $this->_rdb->fetchOne($sql, array('cid' => $cid));
    }

	/**
     * get class vote info by cid and uid
     *
     * @param integer $cid
     * @param integer $uid
     * @return integer
     */
    public function getVotedInfo($cid, $uid)
    {
        $sql = "SELECT * FROM school_class_vote WHERE cid=:cid AND	uid=:uid";
        return $this->_rdb->fetchRow($sql, array('cid' => $cid, 'uid' => $uid));
    }

	/**
     * get class averge vote result
     *
     * @param integer $cid
     * @return integer
     */
    public function getAvgVoteResult($cid)
    {
        $sql = "SELECT IFNULL(AVG(vote_content),0) AS vote_content,
                       IFNULL(AVG(vote_difficult),0) AS vote_difficult,
                       IFNULL(AVG(vote_work),0) AS vote_work,
                       IFNULL(AVG(vote_test),0) AS vote_test,
                       IFNULL(AVG(vote_attend),0) AS vote_attend FROM school_class_vote WHERE cid=:cid";
        return $this->_rdb->fetchRow($sql, array('cid' => $cid));
    }

	/**
     * insert class vote
     *
     * @param array $info
     * @return integer
     */
    public function insertVote($info)
    {
        return $this->_wdb->insert('school_class_vote', $info);
    }

    /**
     * update class vote
     *
     * @param array $info
     * @param integer $cid
     * @param integer $uid
     * @return integer
     */
    public function updateVote($info, $cid, $uid)
    {
        $where = array($this->_wdb->quoteInto('cid=?', $cid),
                       $this->_wdb->quoteInto('uid=?', $uid));
        return $this->_wdb->update('school_class_vote', $info, $where);
    }

    /**
     * delete class vote
     *
     * @param integer $cid
     * @param integer $uid
     * @return integer
     */
    public function deleteVote($cid, $uid)
    {
        $sql = "DELETE FROM school_class_vote WHERE cid=:cid AND uid=:uid ";
        return $this->_wdb->query($sql, array('cid' => $cid, 'uid' => $uid));
    }

    /**
     * delete vote by uid
     *
     * @param integer $uid
     * @return integer
     */
    public function deleteVoteByUid($uid)
    {
        $sql = "DELETE FROM school_class_vote WHERE uid=:uid ";
        return $this->_wdb->query($sql, array('uid' => $uid));
    }

	/**
	 * update class votecount
	 *
	 * @param integer $cid
	 * @param integer $isAdd
	 */
    public function updateClassVoteCount($cid, $isAdd = 1)
    {
    	if ($isAdd) {
    		$sql = "UPDATE school_class SET vote_count = vote_count + 1 WHERE cid = :cid";
    	} else {
    		$sql = "UPDATE school_class SET vote_count = vote_count - 1 WHERE cid = :cid";
    	}
		$this->_wdb->query($sql, array('cid' => $cid));
    }


/** xial
 * ***************************************************************
 */

    /**
     * update class member
     *
     * @param array $info
     * @param integer $cid
     * @param integer $uid
     * @return integer
     */
    public function updateClassMember($info, $cid, $uid)
    {
        $where = array($this->_wdb->quoteInto('cid=?', $cid),
                       $this->_wdb->quoteInto('uid=?', $uid));
        return $this->_wdb->update('school_class_member', $info, $where);
    }

	/**
	 * update class membercount
	 *
	 * @param integer $cid
	 * @param integer $isAdd
	 */
    public function updateClassMemberCount($cid, $isAdd = 1)
    {
    	if ($isAdd) {
    		$sql = "UPDATE school_class SET member_count = member_count + 1 WHERE cid = :cid";
    	} else {
    		$sql = "UPDATE school_class SET member_count = member_count - 1 WHERE cid = :cid";
    	}

		$this->_wdb->query($sql, array('cid' => $cid));
    }

    /**
     * delete class member
     *
     * @param integer $cid
     * @param integer $uid
     */

    public function delClassMember($cid, $uid)
    {
    	$ary = array('uid' => $uid);
    	$sql = "DELETE FROM school_class_member WHERE uid = :uid";
    	if ($cid) {
    		$sql .= " AND cid = :cid";
    		$ary['cid'] = $cid;
    	}

		$this->_wdb->query($sql, $ary);
    }

    /**
     * check is class member
     *
     * @param integer $cid
     * @param integer $uid
     * @return boolean
     */
    public function isClassMember($cid, $uid)
    {
		$sql = "SELECT COUNT(1) FROM school_class_member WHERE cid = :cid AND uid = :uid";
		$result = $this->_rdb->fetchOne($sql, array('cid' => $cid, 'uid' => $uid));
		return $result == 1 ? true : false;
    }
    /**
     * get class member
     *
     * @param integer $cid
     * @param integer $pageIndex
     * @param integer $pageSize
     * @return array
     */
    public function getlstClassMember($cid, $pageIndex = 1, $pageSize = 10)
    {
		$start = ($pageIndex - 1) * $pageSize;
    	$sql = "SELECT uid FROM school_class_member WHERE cid=:cid ORDER BY create_time DESC LIMIT $start, $pageSize";
    	return $this->_rdb->fetchAll($sql, array('cid' => $cid));
    }

    /**
     * get class member count
     * @param integer $cid
     * @return array
     */
    public function getCountClassMemberByCid($cid)
    {
    	$sql = "SELECT COUNT(1) FROM school_class_member WHERE cid=:cid ";
    	return $this->_rdb->fetchOne($sql, array('cid' => $cid));
    }

    public function getCidByName($cName, $school_code)
    {
    	$sql = "SELECT COUNT(cid) FROM school_class WHERE name=:cName AND school_code = :school_code";
    	return $this->_rdb->fetchOne($sql, array('cName' => $cName, 'school_code' => $school_code));
    }

    /**
     * get list like name
     *
     * @param integer $school_code
     * @param string $likeName
     * @param integer $pageIndex
     * @param integer $pageSize
     * @return array
     */
	public function getlstLikeNameById($school_code, $likeName, $pageIndex = 1, $pageSize = 10)
    {
    	$start = ($pageIndex - 1) * $pageSize;
    	$sql = "SELECT cid,`name`,teacher FROM school_class WHERE `name` LIKE '$likeName%'
    			AND school_code = :school_code LIMIT $start, $pageSize";

    	return $this->_rdb->fetchAll($sql, array('school_code' => $school_code));
    }

    /**
     * get like name count
     * @param integer $school_code
     * @param string $likeName
     * @return integer
     */
    public function getLikeNameCountById($school_code, $likeName)
    {
    	$sql = "SELECT COUNT(1) FROM school_class WHERE `name` LIKE '$likeName%' AND school_code = :school_code";
		return $this->_rdb->fetchOne($sql, array('school_code' => $school_code));
    }

    /**
     * get all member info
     *
     * @param integer $uid
     * @return array
     */
    public function getCidsById($uid)
    {
		$sql = "SELECT cid FROM school_class_member WHERE uid = :uid";
		return $this->_rdb->fetchAll($sql, array('uid' => $uid));
    }

    /**
     * get same name
     *
     * @param integer $school_code
     * @param string $cname
     * @param integer $pageIndex
     * @param integer $pageSize
     * @return array
     */
    public function getLstClassNameByName($school_code, $cname, $pageIndex = 1, $pageSize = 10)
    {
    	$start = ($pageIndex - 1) * $pageSize;
    	$sql = "SELECT cid,`name`,teacher FROM school_class WHERE `name` = :cname
    			AND school_code = :school_code LIMIT $start, $pageSize";

    	return $this->_rdb->fetchAll($sql, array('school_code' => $school_code, 'cname' => $cname));
    }

    public function getCntClassNameByName($school_code, $cname)
    {
		$sql = "SELECT COUNT(1) FROM school_class WHERE `name` = :cname AND school_code = :school_code";
		return $this->_rdb->fetchOne($sql, array('school_code' => $school_code, 'cname' => $cname));
    }
}
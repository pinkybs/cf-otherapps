<?php

require_once 'Mdal/Abstract.php';

class Mdal_School_Topic extends Mdal_Abstract
{
    protected static $_instance;

    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * list class topic
     *
     * @param integer $cid
     * @param integer $pageindex
     * @param integer $pagesize
     * @return array
     */
    public function listClassTopic($cid, $pageIndex, $pagesize)
    {
    	$start = ($pageIndex - 1) * $pagesize;
    	$sql = "SELECT * FROM school_class_topic WHERE cid=:cid AND isdelete = 0
    	        ORDER BY create_time DESC LIMIT $start, $pagesize";
    	return $this->_rdb->fetchAll($sql, array('cid' => $cid));
    }

    /**
     * class topic count
     *
     * @param integer $cid
     * @return integer
     */
    public function getClassTopicCount($cid)
    {
    	$sql = "SELECT COUNT(1) FROM school_class_topic WHERE cid=:cid AND isdelete = 0 ";

    	return $this->_rdb->fetchOne($sql, array('cid' => $cid));
    }

	/**
     * list newest topic
     *
     * @param array $aryCid
     * @param integer $pageindex
     * @param integer $pagesize
     * @return array
     */
    public function listNewestTopic($aryCid, $pageIndex, $pagesize)
    {
    	$start = ($pageIndex - 1) * $pagesize;
    	$ids = $this->_rdb->quote($aryCid);
    	$sql = "SELECT * FROM school_class_topic WHERE cid IN ($ids) AND isdelete = 0
    	        ORDER BY create_time DESC LIMIT $start, $pagesize";
    	return $this->_rdb->fetchAll($sql);
    }

    /**
     * list newest topic count
     *
     * @param array $aryCid
     * @return integer
     */
    public function getNewestTopicCount($aryCid)
    {
        $ids = $this->_rdb->quote($aryCid);
    	$sql = "SELECT COUNT(tid) FROM school_class_topic WHERE cid IN ($ids) AND isdelete = 0 ";
    	return $this->_rdb->fetchOne($sql);
    }

	/**
     * list my newest topic
     *
     * @param integer $uid
     * @param integer $pageindex
     * @param integer $pagesize
     * @return array
     */
    public function listMyNewestTopic($uid, $pageIndex, $pagesize)
    {
    	$start = ($pageIndex - 1) * $pagesize;
    	$sql = "SELECT * FROM school_class_topic WHERE uid=:uid AND isdelete = 0
    	        ORDER BY create_time DESC LIMIT $start, $pagesize";
    	return $this->_rdb->fetchAll($sql, array('uid' => $uid));
    }

    /**
     * list my newest topic count
     *
     * @param integer $uid
     * @return integer
     */
    public function getMyNewestTopicCount($uid)
    {
    	$sql = "SELECT COUNT(tid) FROM school_class_topic WHERE uid=:uid AND isdelete = 0 ";
    	return $this->_rdb->fetchOne($sql, array('uid' => $uid));
    }

    /**
     * list my newest class topic
     *
     * @param integer $uid
     * @param integer $pageindex
     * @param integer $pagesize
     * @return array
     */
    public function listMyNewestTopicGroupByCid($uid, $pageIndex, $pagesize)
    {
    	$start = ($pageIndex - 1) * $pagesize;
    	$sql = "SELECT cid,MAX(tid) AS tid FROM school_class_topic WHERE uid=:uid AND isdelete = 0 GROUP BY cid
    	        ORDER BY tid DESC LIMIT $start, $pagesize";

    	return $this->_rdb->fetchAll($sql, array('uid' => $uid));
    }

    /**
     * list my newest class topic count
     *
     * @param integer $uid
     * @return integer
     */
    public function getMyNewestTopicGroupByCidCount($uid)
    {
    	$sql = "SELECT COUNT(a.cid) FROM (SELECT cid FROM school_class_topic WHERE uid=:uid AND isdelete = 0 GROUP BY cid) a";
    	return $this->_rdb->fetchOne($sql, array('uid' => $uid));
    }

	/**
     * get class topic
     *
     * @param integer $tid
     * @return array
     */
    public function getClassTopic($tid)
    {
        $sql = 'SELECT * FROM school_class_topic WHERE tid=:tid AND isdelete = 0';
        return $this->_rdb->fetchRow($sql, array('tid' => $tid));
    }

    /**
     * insert a new topic
     *
     * @param array $info
     *
     */
    public function insertTopic($info)
    {
    	$this->_wdb->insert('school_class_topic', $info);
    	return $this->_wdb->lastInsertId();
    }

	/**
     * get topic
     *
     * @param integer $uid
     * @return unknown
     */
    public function getClassTopicLock($tid)
    {
        $sql = "SELECT * FROM school_class_topic WHERE tid=:tid FOR UPDATE";
        return $this->_rdb->fetchRow($sql, array('tid' => $tid));
    }


    /**
     * check if user have assessed to this comment
     *
     * @param integer $comment_id
     * @param integer $uid
     *
     */
    public function isUserHaveAssessed($comment_id, $uid)
    {
        $sql = "select * from school_class_topic_comment_good WHERE comment_id=:comment_id AND uid=:uid";

        $row = $this->_rdb->fetchRow($sql, array('comment_id' => $comment_id, 'uid' => $uid));

        return $row ? true : false;
    }

    /**
     * insert comment good table
     *
     * @param array $info
     */
    public function insertCommentGood($info)
    {
        $this->_wdb->insert('school_class_topic_comment_good', $info);
    }

    /**
     * update topic comment
     * @param array $info
     *
     * @param integer $commentId
     */
    public function updateClassTopic($info, $tid)
    {
        $where = $this->_wdb->quoteInto('tid=?', $tid);

        return $this->_wdb->update('school_class_topic', $info, $where);
    }

/**
* xial *******************************
*/

    /**
     * get topic comment for update
     *
     * @param integer $commentid
     * @return array
     */
    public function getTopicCommentLock($commentid)
    {
        $sql = "SELECT * FROM school_class_topic_comment WHERE comment_id=:comment_id FOR UPDATE";
        return $this->_rdb->fetchRow($sql, array('comment_id' => $commentid));
    }

    /**
     * get topic comment
     *
     * @param integer $commentid
     * @return array
     */
	public function getTopicComment($commentid)
    {
        $sql = "SELECT * FROM school_class_topic_comment WHERE comment_id=:comment_id";
        return $this->_rdb->fetchRow($sql, array('comment_id' => $commentid));
    }

    /**
     * get topic comment list
     *
     * @param integer $tid
     * @param integer $pageIndex
     * @param integer $pagesize
     * @param string $order_by
     * @return array
     */
     public function getLstTopicCommentById($tid, $pageindex, $pagesize, $order_by)
    {
    	$order_by = $order_by == null ? 'create_time DESC' : $order_by;

    	$start = ($pageindex - 1) * $pagesize;
    	$sql = "SELECT * FROM school_class_topic_comment WHERE tid=:tid AND isdelete = 0
    			ORDER BY $order_by LIMIT $start, $pagesize";
    	return $this->_rdb->fetchAll($sql, array('tid' => $tid));
    }

    /**
     * get topic comment count by id
     *
     * @param integer $tid
     * @return integer
     */
    public function getCntTopicCommentById($tid)
    {
    	$sql = " SELECT COUNT(1) FROM school_class_topic_comment WHERE tid = :tid  AND isdelete = 0";
        return $this->_rdb->fetchOne($sql, array('tid' => $tid));
    }

    /**
     * insert topic comment
     *
     * @param array $info
     * @return integer
     */
    public function insertTopicComment($info)
    {
        return $this->_wdb->insert('school_class_topic_comment', $info);
    }

    /**
     * update topic comment
     * @param array $info
     *
     * @param integer $commentId
     */
    public function updateTopicComment($info, $commentId)
    {
        $where = $this->_wdb->quoteInto('comment_id=?', $commentId);

        return $this->_wdb->update('school_class_topic_comment', $info, $where);
    }

    public function getCntCommentById($tid)
    {
		$sql = "SELECT MAX(no) FROM school_class_topic_comment WHERE tid = :tid";
		$result =  $this->_rdb->fetchOne($sql, array('tid' => $tid));
		return empty($result) ? 0 : $result;
    }
}
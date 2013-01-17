<?php

require_once 'Admin/Dal/Abstract.php';

/**
 * Admin Dal School
 * LinNo Admin School Data Access Layer
 *
 * @package    Admin/Dal
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/11/26    zhangxin
 */
class Admin_Dal_School extends Admin_Dal_Abstract
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
            self::$_instance = new self(getAmazonDBConfig());
        }
        return self::$_instance;
    }

/****************************************** class note ************************************************/
	/**
     * get school watch class status
     *
     * @param Integer $status
     * @return Integer
     */
    public function getClassnoteByStatus($status)
    {
        $sql = "SELECT COUNT(*) FROM school_class WHERE status=:status AND introduce IS NOT NULL AND introduce <> ''";
        return $this->_rdb->fetchOne($sql, array('status' => $status));
    }

   /**
     * get school watch class list
     *
     * @param Integer $pageindex
     * @param Integer $pagesize
     * @param Integer $status 1-未処理 2-容疑 3-問題なし 4-保留 5-違反 0-All
     * @param string $keyword ''=All
     * @return array
     */
    public function getSchoolWatchClassnoteList($pageindex = 1, $pagesize = 10, $status = 0, $keyword = '')
    {
        $start = ($pageindex - 1) * $pagesize;

        $aryParm = array();
        $sql = "SELECT *,FROM_UNIXTIME(create_time) AS format_time FROM school_class WHERE introduce IS NOT NULL AND introduce <> ''";

        if (!empty($status)) {
            $sql .= " AND status = :status ";
            $aryParm['status'] = $status;
        }
        if (!empty($keyword)) {
            $sql .= " AND introduce LIKE :introduce ";
            $aryParm['introduce'] = '%' . $keyword . '%';
        }

        $sql .= " ORDER BY last_new_update_time LIMIT $start, $pagesize ";
        return $this->_rdb->fetchAll($sql, $aryParm);
    }

    /**
     * get school watch class count
     *
     * @param Integer $status 1-未処理 2-容疑 3-問題なし 4-保留 5-違反 0-All
     * @param string $keyword ''=All
     * @return Integer
     */
    public function getSchoolWatchClassnoteCount($status = 0, $keyword = '')
    {
        $aryParm = array();
        $sql = "SELECT COUNT(cid) FROM school_class WHERE introduce IS NOT NULL AND introduce <> ''";

        if (!empty($status)) {
            $sql .= " AND status=:status ";
            $aryParm['status'] = $status;
        }
        if (!empty($keyword)) {
            $sql .= " AND introduce LIKE :introduce ";
            $aryParm['introduce'] = '%' . $keyword . '%';
        }
        return $this->_rdb->fetchOne($sql, $aryParm);
    }

/****************************************** Enquiry Comment ************************************************/
	/**
     * get school watch enquiry comment status
     *
     * @param Integer $status
     * @return Integer
     */
    public function getEnquiryCommentByStatus($status)
    {
        $sql = "SELECT COUNT(*) FROM school_enquiry_comment WHERE status=:status ";
        return $this->_rdb->fetchOne($sql, array('status' => $status));
    }

   /**
     * get school watch Enquiry comment list
     *
     * @param Integer $pageindex
     * @param Integer $pagesize
     * @param Integer $status 1-未処理 2-容疑 3-問題なし 4-保留 5-違反 0-All
     * @param string $keyword ''=All
     * @return array
     */
    public function getSchoolWatchEnquiryCommentList($pageindex = 1, $pagesize = 10, $status = 0, $keyword = '')
    {
        $start = ($pageindex - 1) * $pagesize;

        $aryParm = array();
        $sql = "SELECT *,FROM_UNIXTIME(create_time) AS format_time FROM school_enquiry_comment WHERE 1=1 ";

        if (!empty($status)) {
            $sql .= " AND status = :status ";
            $aryParm['status'] = $status;
        }
        if (!empty($keyword)) {
            $sql .= " AND comment LIKE :comment ";
            $aryParm['comment'] = '%' . $keyword . '%';
        }

        $sql .= " ORDER BY create_time LIMIT $start, $pagesize ";
        return $this->_rdb->fetchAll($sql, $aryParm);
    }

    /**
     * get school watch Enquiry comment count
     *
     * @param Integer $status 1-未処理 2-容疑 3-問題なし 4-保留 5-違反 0-All
     * @param string $keyword ''=All
     * @return Integer
     */
    public function getSchoolWatchEnquiryCommentListCount($status = 0, $keyword = '')
    {
        $aryParm = array();
        $sql = "SELECT COUNT(comment_id) FROM school_enquiry_comment WHERE 1=1 ";

        if (!empty($status)) {
            $sql .= " AND status=:status ";
            $aryParm['status'] = $status;
        }
        if (!empty($keyword)) {
            $sql .= " AND comment LIKE :comment ";
            $aryParm['comment'] = '%' . $keyword . '%';
        }

        return $this->_rdb->fetchOne($sql, $aryParm);
    }

	/**
     * update enquiry comment
     * @param array $info
     *
     * @param integer $commentId
     */
    public function updateEnquiryComment($info, $commentId)
    {
        $where = $this->_wdb->quoteInto('comment_id=?', $commentId);
        return $this->_wdb->update('school_enquiry_comment', $info, $where);
    }

    /**
     * get enquiry comment
     *
     * @param integer $commentid
     * @return array
     */
	public function getEnquiryComment($commentid)
    {
        $sql = "SELECT * FROM school_enquiry_comment WHERE comment_id=:comment_id";
        return $this->_rdb->fetchRow($sql, array('comment_id' => $commentid));
    }

/****************************************** Enquiry ************************************************/
	/**
     * get school watch enquiry status
     *
     * @param Integer $status
     * @return Integer
     */
    public function getEnquiryByStatus($status)
    {
        $sql = "SELECT COUNT(*) FROM school_enquiry WHERE status=:status ";
        return $this->_rdb->fetchOne($sql, array('status' => $status));
    }

     /**
     * get school watch Enquiry list
     *
     * @param Integer $pageindex
     * @param Integer $pagesize
     * @param Integer $status 1-未処理 2-容疑 3-問題なし 4-保留 5-違反 0-All
     * @param string $keyword ''=All
     * @return array
     */
    public function getSchoolWatchEnquiryList($pageindex = 1, $pagesize = 10, $status = 0, $keyword = '')
    {
        $start = ($pageindex - 1) * $pagesize;

        $aryParm = array();
        $sql = "SELECT *,FROM_UNIXTIME(create_time) AS format_time FROM school_enquiry WHERE 1=1 ";

        if (!empty($status)) {
            $sql .= " AND status = :status ";
            $aryParm['status'] = $status;
        }
        if (!empty($keyword)) {
            $sql .= " AND question LIKE :question ";
            $aryParm['question'] = '%' . $keyword . '%';
        }

        $sql .= " ORDER BY create_time LIMIT $start, $pagesize ";
        return $this->_rdb->fetchAll($sql, $aryParm);
    }

    /**
     * get school watch Enquiry count
     *
     * @param Integer $status 1-未処理 2-容疑 3-問題なし 4-保留 5-違反 0-All
     * @param string $keyword ''=All
     * @return Integer
     */
    public function getSchoolWatchEnquiryListCount($status = 0, $keyword = '')
    {
        $aryParm = array();
        $sql = "SELECT COUNT(qid) FROM school_enquiry WHERE 1=1 ";

        if (!empty($status)) {
            $sql .= " AND status=:status ";
            $aryParm['status'] = $status;
        }
        if (!empty($keyword)) {
            $sql .= " AND question LIKE :question ";
            $aryParm['question'] = '%' . $keyword . '%';
        }

        return $this->_rdb->fetchOne($sql, $aryParm);
    }

	/**
     * update enquiry
     * @param array $info
     *
     * @param integer $qid
     */
    public function updateEnquiry($info, $qid)
    {
        $where = $this->_wdb->quoteInto('qid=?', $qid);
        return $this->_wdb->update('school_enquiry', $info, $where);
    }

    /**
     * get enquiry
     *
     * @param integer $qid
     * @return array
     */
	public function getEnquiry($qid)
    {
        $sql = "SELECT * FROM school_enquiry WHERE qid=:qid";
        return $this->_rdb->fetchRow($sql, array('qid' => $qid));
    }

/****************************************** Topic ************************************************/
	/**
     * get school watch topic status
     *
     * @param Integer $status
     * @return Integer
     */
    public function getTopicCountByStatus($status)
    {
        $sql = "SELECT COUNT(*) FROM school_class_topic WHERE status=:status ";
        return $this->_rdb->fetchOne($sql, array('status' => $status));
    }


    /**
     * get school watch topic comment list
     *
     * @param Integer $pageindex
     * @param Integer $pagesize
     * @param Integer $status 1-未処理 2-容疑 3-問題なし 4-保留 5-違反 0-All
     * @param string $keyword ''=All
     * @return array
     */
    public function getSchoolWatchTopicList($pageindex = 1, $pagesize = 10, $status = 0, $keyword = '')
    {
        $start = ($pageindex - 1) * $pagesize;

        $aryParm = array();
        $sql = "SELECT *,FROM_UNIXTIME(create_time) AS format_time FROM school_class_topic WHERE 1=1 ";

        if (!empty($status)) {
            $sql .= " AND status=:status ";
            $aryParm['status'] = $status;
        }
        if (!empty($keyword)) {
            $sql .= " AND title LIKE :title ";
            $aryParm['title'] = '%' . $keyword . '%';
        }

        $sql .= " ORDER BY create_time LIMIT $start, $pagesize ";
        return $this->_rdb->fetchAll($sql, $aryParm);
    }

    /**
     * get school watch topic comment count
     *
     * @param Integer $status 1-未処理 2-容疑 3-問題なし 4-保留 5-違反 0-All
     * @param string $keyword ''=All
     * @return Integer
     */
    public function getSchoolWatchTopicListCount($status = 0, $keyword = '')
    {
        $aryParm = array();
        $sql = "SELECT COUNT(tid) FROM school_class_topic WHERE 1=1 ";

        if (!empty($status)) {
            $sql .= " AND status=:status ";
            $aryParm['status'] = $status;
        }
        if (!empty($keyword)) {
            $sql .= " AND title LIKE :title ";
            $aryParm['title'] = '%' . $keyword . '%';
        }

        return $this->_rdb->fetchOne($sql, $aryParm);
    }

	/**
     * get topic
     *
     * @param integer $tid
     * @return array
     */
	public function getTopic($tid)
    {
        $sql = "SELECT * FROM school_class_topic WHERE tid=:tid";
        return $this->_rdb->fetchRow($sql, array('tid' => $tid));
    }

	/**
     * update topic
     * @param array $info
     *
     * @param integer $tid
     */
    public function updateTopic($info, $tid)
    {
        $where = $this->_wdb->quoteInto('tid=?', $tid);
        return $this->_wdb->update('school_class_topic', $info, $where);
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
     * get class info by lock
     *
     * @param integer $cid
     * @return array
     */
    public function getClassInfo($cid)
    {
        $sql = 'SELECT * FROM school_class WHERE cid=:cid';
        return $this->_rdb->fetchRow($sql, array('cid' => $cid));
    }

    /****************************************** Topic Comment ************************************************/
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

	/**
     * get school watch topic comment status
     *
     * @param Integer $status
     * @return Integer
     */
    public function getTopicCommentCountByStatus($status)
    {
        $sql = "SELECT COUNT(*) FROM school_class_topic_comment WHERE status=:status ";
        return $this->_rdb->fetchOne($sql, array('status' => $status));
    }

    /**
     * get school watch topic comment list
     *
     * @param Integer $pageindex
     * @param Integer $pagesize
     * @param Integer $status 1-未処理 2-容疑 3-問題なし 4-保留 5-違反 0-All
     * @param string $keyword ''=All
     * @return array
     */
    public function getSchoolWatchTopicCommentList($pageindex = 1, $pagesize = 10, $status = 0, $keyword = '')
    {
        $start = ($pageindex - 1) * $pagesize;

        $aryParm = array();
        $sql = "SELECT *,FROM_UNIXTIME(create_time) AS format_time FROM school_class_topic_comment WHERE 1=1 ";

        if (!empty($status)) {
            $sql .= " AND status=:status ";
            $aryParm['status'] = $status;
        }
        if (!empty($keyword)) {
            $sql .= " AND comment LIKE :comment ";
            $aryParm['comment'] = '%' . $keyword . '%';
        }

        $sql .= " ORDER BY create_time LIMIT $start, $pagesize ";
        return $this->_rdb->fetchAll($sql, $aryParm);
    }

    /**
     * get school watch topic comment count
     *
     * @param Integer $status 1-未処理 2-容疑 3-問題なし 4-保留 5-違反 0-All
     * @param string $keyword ''=All
     * @return Integer
     */
    public function getSchoolWatchTopicCommentListCount($status = 0, $keyword = '')
    {
        $aryParm = array();
        $sql = "SELECT COUNT(comment_id) FROM school_class_topic_comment WHERE 1=1 ";

        if (!empty($status)) {
            $sql .= " AND status=:status ";
            $aryParm['status'] = $status;
        }
        if (!empty($keyword)) {
            $sql .= " AND comment LIKE :comment ";
            $aryParm['comment'] = '%' . $keyword . '%';
        }

        return $this->_rdb->fetchOne($sql, $aryParm);
    }




	/******************* school_watch_changestatus_log *******************/

    /**
     * get SchoolWatchChangestatusLog
     *
     * @param Integer $bid
     * @param Integer $type [1-topic 2-topic comment 3-enquiry 4-enquiry comment]
     * @return array
     */
    public function getSchoolWatchChangestatusLogByIdType($bid, $type)
    {
        $sql = "SELECT * FROM school_watch_changestatus_log
                WHERE watch_bid=:watch_bid AND watch_type=:watch_type
                ORDER BY create_time DESC LIMIT 0, 1 ";

        return $this->_rdb->fetchRow($sql, array('watch_bid' => $bid, 'watch_type' => $type));
    }

    /**
     * insert school watch changestatus log
     *
     * @param array $info
     * @return integer
     */
    public function insertSchoolWatchChangestatusLog($info)
    {
        $this->_wdb->insert('school_watch_changestatus_log', $info);
        return $this->_wdb->lastInsertId();
    }

    /**
     * get user daily deal contents count list by month
     *
     * @param Integer $uid
     * @param Integer $year
     * @param Integer $month
     * @return array
     */
    public function getUserDailyDealtCountByMonth($uid, $year, $month)
    {
        $sql = "SELECT DATE(FROM_UNIXTIME(create_time)) AS sel_year_month, COUNT(*) AS deal_count
                FROM school_watch_changestatus_log
                WHERE admin_id=:uid AND MONTH(DATE(FROM_UNIXTIME(create_time)))=:month AND YEAR(DATE(FROM_UNIXTIME(create_time)))=:year
                GROUP BY DATE(FROM_UNIXTIME(create_time)) ORDER BY sel_year_month ";
        return $this->_rdb->fetchAll($sql, array('uid' => $uid, 'month' => $month, 'year' => $year));
    }

   /**
     * get common note is locked status
     *
     * @param integer $cid
     * @return boolean
     */
    public function isClassNoteLocked($cid)
    {
        $sql = "SELECT COUNT(uid) FROM school_class_common_note_editor WHERE cid=:cid AND islock=1 ";
        $rst = $this->_rdb->fetchOne($sql, array('cid' => $cid));
        return $rst > 0;
    }

    /**
     * get common note editor
     *
     * @param integer $cid
     * @param integer $uid
     * @return integer
     */
    public function getCommonNoteUserByPk($cid, $uid)
    {
        $sql = "SELECT * FROM school_class_common_note_editor WHERE cid=:cid AND uid=:uid ";
        return $this->_rdb->fetchRow($sql, array('cid' => $cid, 'uid' => $uid));
    }


   /**
     * get common note is locked user count
     *
     * @param integer $cid
     * @return integer
     */
    public function getClassNoteLockedCount($cid)
    {
        $sql = "SELECT COUNT(uid) FROM school_class_common_note_editor WHERE cid=:cid AND islock=1 ";
        $rst = $this->_rdb->fetchOne($sql, array('cid' => $cid));
        return $rst;
    }

    /**
     * update common note editor
     *
     * @param integer $info
     * @param integer $cid
     * @param integer $uid
     * @return integer
     */
    public function updateCommonNoteUser($info, $cid, $uid)
    {
        $where = array($this->_wdb->quoteInto('cid=?', $cid),
                       $this->_wdb->quoteInto('uid=?', $uid));
        return $this->_wdb->update('school_class_common_note_editor', $info, $where);
    }

    /**
     * insert common note editor
     *
     * @param array $info
     * @return integer
     */
    public function insertCommonNoteUser($info)
    {
        return $this->_wdb->insert('school_class_common_note_editor', $info);
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
     * list locked common note by cid
     *
     * @param integer $cid
     * @return integer
     */
    public function listClassNoteLocked($cid)
    {
        $sql = "SELECT * FROM school_class_common_note_editor WHERE cid=:cid AND islock=1 ";
        return $this->_rdb->fetchAll($sql, array('cid' => $cid));
    }
}
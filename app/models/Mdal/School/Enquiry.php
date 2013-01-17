<?php

require_once 'Mdal/Abstract.php';

class Mdal_School_Enquiry extends Mdal_Abstract
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
     * list user enquiry
     *
     * @param integer $uid
     * @param integer $pageindex
     * @param integer $pagesize
     * @return array
     */
    public function getLstEnquiryByUid($uid, $pageIndex, $pagesize)
    {
    	$start = ($pageIndex - 1) * $pagesize;
    	//$sql = "SELECT * FROM school_enquiry WHERE uid = :uid AND answer_count > 0 AND isdelete = 0 ORDER BY $order_by DESC LIMIT $start, $pagesize";
		$sql = "SELECT comment_id,comment,good_count,qid,uid FROM school_enquiry_comment WHERE isdelete = 0
				AND uid = :uid ORDER BY create_time DESC LIMIT $start, $pagesize";
    	return $this->_rdb->fetchAll($sql, array('uid' => $uid));
    }

   /**
     * get Enquiry count
     *
     * @param integer $uid
     * @return integer
     */
    public function getCntEnquiryByUid($uid)
    {
		$sql = "SELECT COUNT(1) FROM school_enquiry_comment WHERE uid = :uid AND isdelete = 0";

    	return $this->_rdb->fetchOne($sql, array('uid' => $uid));
    }

    /**
     * get mine new enquiry list
     *
     * @param integer $uid
     * @param integer $pageIndex
     * @param integer $pagesize
     * @param string $order_by
     * @return array
     */
    public function getMyNewLstEnquiryById($uid, $pageIndex, $pagesize, $order_by = 'update_time')
    {
		$start = ($pageIndex - 1) * $pagesize;
    	$sql = " SELECT e.*, c.name FROM school_enquiry AS e,school_nb_enquiry_category AS c
    			 WHERE uid = :uid AND e.category = c.id AND isdelete = 0
    			 ORDER BY $order_by DESC LIMIT $start, $pagesize";

    	return $this->_rdb->fetchAll($sql, array('uid' => $uid));
    }

    /**
     * get mine new enquiry count
     *
     * @param integer $uid
     * @return integer
     */
    public function getMyNewCntEnquiryById($uid)
    {
		$sql = "SELECT COUNT(1) FROM school_enquiry WHERE uid = :uid AND isdelete = 0 ";

    	return $this->_rdb->fetchOne($sql, array('uid' => $uid));
    }

    /**
     * list user enquiry answer
     *
     * @param integer qid
     * @param integer $pageindex
     * @param integer $pagesize
     * @param string $order_by
     * @return array
     */
    public function getLstEnquiryAnswerById($qid, $pageIndex, $pagesize, $order_by)
    {
    	$order_by = $order_by == null ? 'create_time DESC' : $order_by;

    	$start = ($pageIndex - 1) * $pagesize;
    	$sql = "SELECT * FROM school_enquiry_comment WHERE qid=:qid AND isdelete = 0
    			ORDER BY $order_by LIMIT $start, $pagesize";

    	return $this->_rdb->fetchAll($sql, array('qid' => $qid));
    }

    /**
     * get enquiry answer count
     *
     * @param integer $qid
     * @return integer
     */
    public function getCntEnquiryAnswerById($qid)
    {
		$sql = "SELECT COUNT(1) FROM school_enquiry_comment WHERE qid=:qid AND isdelete = 0";

    	return $this->_rdb->fetchOne($sql, array('qid' => $qid));
    }

    /**
     * list user enquiry answer
     *
     * @param integer $uid
     * @param integer $qid
     * @param integer $pageindex
     * @param integer $pagesize
     * @return array
     */
    public function getLstEnquiryCategoryById($cid, $pageIndex, $pagesize, $order_by)
    {
    	$result = null;
    	$order_by = $order_by == null ? 'update_time ASC' : $order_by;

    	$start = ($pageIndex - 1) * $pagesize;
    	if ($cid) {
    		$sql = "SELECT * FROM school_enquiry WHERE category=:category  AND isdelete = 0
    				ORDER BY $order_by LIMIT $start, $pagesize";
			$result = $this->_rdb->fetchAll($sql, array('category' => $cid));
    	} else {
    		$sql = "SELECT e.*, c.name FROM school_enquiry AS e,school_nb_enquiry_category AS c WHERE
    				e.category = c.id  AND isdelete = 0 ORDER BY $order_by LIMIT $start, $pagesize";
			$result = $this->_rdb->fetchAll($sql);
    	}
    	return $result;
    }

    /**
     * get enquiry answer count
     *
     * @param integer $uid
     * @param integer $qid
     * @return integer
     */
    public function getCntEnquiryCategoryById($cid)
    {
    	$result = null;
    	if ($cid) {
    		$sql = "SELECT COUNT(1) FROM school_enquiry WHERE category=:category  AND isdelete = 0";
			$result = $this->_rdb->fetchOne($sql, array('category' => $cid));
    	} else {
    		$sql = "SELECT COUNT(1) FROM school_enquiry WHERE isdelete = 0";
			$result = $this->_rdb->fetchOne($sql);
    	}
    	return $result;
    }

	/**
     * get Enquiry
     *
     * @param integer $qid
     * @return array
     */
    public function getEnquiry($qid)
    {
        $sql = 'SELECT e.*, c.name FROM school_enquiry AS e,school_nb_enquiry_category AS c WHERE qid=:qid AND e.category = c.id AND e.isdelete = 0';
        return $this->_rdb->fetchRow($sql, array('qid' => $qid));
    }

    /**
     * insert a new Enquiry
     *
     * @param array $info
     *
     */
    public function insertEnquiry($info)
    {
    	$this->_wdb->insert('school_enquiry', $info);
    	return $this->_wdb->lastInsertId();
    }

	/**
     * get Enquiry
     *
     * @param integer $uid
     * @return array
     */
    public function getEnquiryLock($qid)
    {
        $sql = "SELECT * FROM school_enquiry WHERE qid=:qid FOR UPDATE";
        return $this->_rdb->fetchRow($sql, array('qid' => $qid));
    }

    /**
     * update Enquiry comment
     * @param array $info
     *
     * @param integer $commentId
     */
    public function updateEnquiry($info, $qid)
    {
        $where = $this->_wdb->quoteInto('qid=?', $qid);

        return $this->_wdb->update('school_enquiry', $info, $where);
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
        $sql = "select * from school_enquiry_comment_good WHERE comment_id=:comment_id AND uid=:uid";

        $row = $this->_rdb->fetchRow($sql, array('comment_id' => $comment_id, 'uid' => $uid));

        return $row ? true : false;
    }

    /**
     * insert comment good table
     *
     * @param array $info
     */
    public function insertEnquiryCommentGood($info)
    {
        $this->_wdb->insert('school_enquiry_comment_good', $info);
    }

    /**
     * get Enquiry comment for update
     *
     * @param integer $uid
     * @return array
     */
    public function getEnquiryCommentLock($commentid)
    {
        $sql = "SELECT * FROM school_enquiry_comment WHERE comment_id=:comment_id AND isdelete = 0 FOR UPDATE";
        return $this->_rdb->fetchRow($sql, array('comment_id' => $commentid));
    }

    /**
     * get Enquiry comment
     *
     * @param integer $uid
     * @return array
     */
	public function getEnquiryComment($commentid)
    {
        $sql = "SELECT * FROM school_enquiry_comment WHERE comment_id=:comment_id AND isdelete = 0";
        return $this->_rdb->fetchRow($sql, array('comment_id' => $commentid));
    }

    /**
     * insert Enquiry comment
     *
     * @param array $info
     * @return integer
     */
    public function insertEnquiryComment($info)
    {
        return $this->_wdb->insert('school_enquiry_comment', $info);
    }

    /**
     * update Enquiry comment
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
     * get bn enquiry category list
     *
     * @return array
     */
    public function getNbLstEnquiryType()
    {
    	$sql = "SELECT id,name FROM school_nb_enquiry_category";
    	return $this->_rdb->fetchPairs($sql);
    }

    public function getNbCategoryEnquiry()
    {
    	$sql = "SELECT * FROM school_nb_enquiry_category";
    	return $this->_rdb->fetchAll($sql);
    }

    public function getRowNbCategoryById($cid)
    {
    	$sql = "SELECT id,name FROM school_nb_enquiry_category WHERE id=:cid";
    	return $this->_rdb->fetchRow($sql, array('cid' => $cid));
    }

    public function getMaxGoodCountRowById($qid)
    {
    	$sql = "SELECT * FROM school_enquiry_comment WHERE qid = :qid AND isdelete = 0 AND good_count =
				(SELECT MAX(good_count) FROM school_enquiry_comment WHERE qid = :qid LIMIT 0,1) LIMIT 0,1";

    	return $this->_rdb->fetchRow($sql, array('qid' => $qid));
    }

    public function getCntEnquiryCommentById($qid)
    {
		$sql = "SELECT MAX(no) FROM school_enquiry_comment WHERE qid = :qid";
		$result = $this->_rdb->fetchOne($sql, array('qid' => $qid));
		return empty($result) ? 0 : $result;
    }

    /**
     * check if user have enquiry to this comment
     *
     * @param integer $uid
     * @param integer $qid
     *
     */
    public function isUserHaveComment($uid, $qid)
    {
        $sql = "select comment_id from school_enquiry_comment WHERE uid=:uid AND qid=:qid AND isdelete=0";

        return $this->_rdb->fetchOne($sql, array('uid' => $uid, 'qid' => $qid));
    }

    /**
     * get is all user didn't answer qid list
     *
     * @param integer $uid
     * @return array
     */
    public function getLstQidById($uid)
    {
    	$sql = "SELECT qid FROM school_enquiry WHERE qid NOT IN
    			(SELECT qid FROM school_enquiry_comment WHERE uid = :uid AND isdelete = 0) AND isdelete = 0 LIMIT 0,20";

    	return $this->_rdb->fetchAll($sql, array('uid' => $uid));
    }
}
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
class Admin_Bll_School extends Admin_Dal_Abstract
{
	/**
	 * update class table's topic count
	 *
	 * @param integer $cid
	 * @param integer $isdelte
	 * @return boolean
	 */
	public function updateTopicCount($info, $isdelte = 1)
	{
		try {
            require_once 'Admin/Dal/School.php';
            $dalSchool = Admin_Dal_School::getDefaultInstance();

            $this->_wdb->beginTransaction();
			$classRow = $dalSchool->getClassInfo($info['cid']);
			$topicRow = $dalSchool->getTopic($info['tid']);
			if ($classRow && $topicRow) {
				$aryClass = array();
				if ($isdelte) {
					$aryClass['topic_count'] = $classRow['topic_count'] - 1;
				} else {
					$aryClass['topic_count'] = $classRow['topic_count'] + 1;
				}
				$dalSchool->updateClass($aryClass, $info['cid']);
				$dalSchool->updateTopic(array('status' => $info['status'], 'isdelete' => $info['isdelete']), $info['tid']);
			} else {
				$this->_wdb->rollBack();
				return false;
			}
            $this->_wdb->commit();
            return true;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            debug_log('Admin/Bll/School/updateTopicCount:' . $e->getMessage());
            return false;
        }
        return true;
	}

	/**
	 * update topic table's comment_count
	 *
	 * @param integer $tid
	 * @param integer $isdelte = 1 comment_count -1 ; =0 comment_count +1
	 * @return boolean
	 */
	public function updateCommentCount($info, $isdelte = 1)
	{
		try {
            require_once 'Admin/Dal/School.php';
            $dalSchool = Admin_Dal_School::getDefaultInstance();

            $this->_wdb->beginTransaction();
			$topicRow = $dalSchool->getTopic($info['tid']);
			if ($topicRow) {
				$aryClass = array();
				if ($isdelte) {
					$aryClass['comment_count'] = $topicRow['comment_count'] - 1;
				} else {
					$aryClass['comment_count'] = $topicRow['comment_count'] + 1;
				}
				$dalSchool->updateTopic($aryClass, $info['tid']);
				$dalSchool->updateTopicComment(array('status' => $info['status'], 'isdelete' => $info['isdelete']), $info['comment_id']);
			} else {
				$this->_wdb->rollBack();
				return false;
			}

            $this->_wdb->commit();
            return true;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            debug_log('Admin/Bll/School/updateCommentCount:' . $e->getMessage());
            return false;
        }
        return true;
	}

	/**
	 * update enquiry table's answer_count
	 *
	 * @param integer $qid
	 * @param integer $isdelte
	 * @return boolean
	 */
	public function updateAnswerCount($info, $isdelte = 1)
	{
		try {
            require_once 'Admin/Dal/School.php';
            $dalSchool = Admin_Dal_School::getDefaultInstance();

            $this->_wdb->beginTransaction();
			$enquiryRow = $dalSchool->getEnquiry($info['qid']);
			if ($enquiryRow) {
				$aryClass = array();
				if ($isdelte) {
					$aryClass['answer_count'] = $enquiryRow['answer_count'] - 1;
				} else {
					$aryClass['answer_count'] = $enquiryRow['answer_count'] + 1;
				}
				$dalSchool->updateEnquiry($aryClass, $info['qid']);
				$dalSchool->updateEnquiryComment(array('status' => $info['status'], 'isdelete' => $info['isdelete']), $info['comment_id']);
			}
            $this->_wdb->commit();
            return true;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            debug_log('Admin/Bll/School/updateAnswerCount:' . $e->getMessage());
            return false;
        }
        return true;
	}

	/**
     * try to add lock class note
     *
     * @param integer $cid
     * @param integer $uid
     * @return boolean
     */
	public function addLockClassCommonNote($cid, $uid)
    {
        try {
            require_once 'Admin/Dal/School.php';
            $dalSchool = Admin_Dal_School::getDefaultInstance();

            $mynoteLog = $dalSchool->getCommonNoteUserByPk($cid, $uid);
            //class note is locked by other user
            if ($dalSchool->isClassNoteLocked($cid)) {
                if (empty($mynoteLog) || $mynoteLog['islock'] != 1) {
                    return false;
                }
                //too many lock user
                $cntLock = $dalSchool->getClassNoteLockedCount($cid);
                if ($cntLock > 1) {
                    return false;
                }
                else if (1 == $mynoteLog['islock'] && 1 == $cntLock) {
                	return true;
                }
            }

            $this->_wdb->beginTransaction();
            //insert / update comment log table add lock
            if (empty($mynoteLog)) {
                $aryLogInfo = array();
                $aryLogInfo['cid'] = $cid;
                $aryLogInfo['uid'] = $uid;
                $aryLogInfo['islock'] = 1;
                $aryLogInfo['start_lock_time'] = time();
                $dalSchool->insertCommonNoteUser($aryLogInfo);
            }
            else {
                $aryLogInfo = array();
                $aryLogInfo['islock'] = 1;
                $aryLogInfo['start_lock_time'] = time();
                $dalSchool->updateCommonNoteUser($aryLogInfo, $cid, $uid);
            }
            $this->_wdb->commit();
        }
        catch (Exception $e) {
            try {
                $this->_wdb->rollBack();
            }
            catch (Exception $e1) {
            }
            debug_log('Admin/Bll/School/addLockClassCommonNote:' . $e->getMessage());
            return false;
        }

        return true;
    }

	/**
     * release lock class note
     *
     * @param integer $cid
     * @param integer $uid
     * @return boolean
     */
    public function releaseLockClassCommonNote($cid, $uid)
    {
        try {
            require_once 'Admin/Dal/School.php';
            $dalSchool = Admin_Dal_School::getDefaultInstance();

            $mynoteLog = $dalSchool->getCommonNoteUserByPk($cid, $uid);
            if (empty($mynoteLog)) {
                return false;
            }

            if (0 == $mynoteLog['islock']) {
                return false;
            }
            //release lock
            $aryLogInfo = array();
            $aryLogInfo['islock'] = 0;
            $aryLogInfo['end_lock_time'] = time();
            $dalSchool->updateCommonNoteUser($aryLogInfo, $cid, $uid);
        }
        catch (Exception $e) {
            debug_log('Admin/Bll/School/releaseLockClassCommonNote:' . $e->getMessage());
            return false;
        }
        return true;
    }

    /**
     * edit class common note
     *
     * @param array $info
     * @param integer $cid
     * @param integer $uid
     * @return boolean
     */
    public function editClassCommonNote($info, $uid)
    {
        try {
            require_once 'Admin/Dal/School.php';
            $dalSchool = Admin_Dal_School::getDefaultInstance();

            $this->_wdb->beginTransaction();

            $rowClass = $dalSchool->getClassInfoLock($info['cid']);
            if (empty($rowClass)) {
                $this->_wdb->rollBack();
                return false;
            }

            $mynoteLog = $dalSchool->getCommonNoteUserByPk($info['cid'], $uid);
            //class note is locked by other user
            if ($dalSchool->isClassNoteLocked($info['cid'])) {
                if (empty($mynoteLog) || $mynoteLog['islock'] != 1) {
                    $this->_wdb->rollBack();
                    return false;
                }
            }

            //insert / update comment log table  release lock
            if (empty($mynoteLog)) {
                $aryLogInfo = array();
                $aryLogInfo['cid'] = $info['cid'];
                $aryLogInfo['uid'] = $uid;
                $aryLogInfo['content'] = $info['introduce'];
                $aryLogInfo['start_lock_time'] = time();
                $aryLogInfo['end_lock_time'] = time();
                $aryLogInfo['update_time'] = time();
                $dalSchool->insertCommonNoteUser($aryLogInfo);
            }
            else {
                $aryLogInfo = array();
                $aryLogInfo['content'] = $info['introduce'];
                $aryLogInfo['islock'] = 0;
                $aryLogInfo['end_lock_time'] = time();
                $aryLogInfo['update_time'] = time();
                $dalSchool->updateCommonNoteUser($aryLogInfo, $info['cid'], $uid);
            }

            $dalSchool->updateClass(array('introduce' => $info['introduce']), $info['cid']);

            $this->_wdb->commit();
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            debug_log('Admin/Bll/School/editClassCommonNote:' . $e->getMessage());
            return false;
        }

        return true;
    }

   /**
     * deal timeout lock
     *
     * @param integer $cid
     * @param integer $timeoutMinute  [default :15 minutes]
     * @return boolean
     */
	public function dealCommonNoteTimeoutLock($cid, $timeoutMinute = 15)
    {
        try {
            require_once 'Admin/Dal/School.php';
            $dalSchool = Admin_Dal_School::getDefaultInstance();

            $lstLocked = $dalSchool->listClassNoteLocked($cid);
            if (empty($lstLocked)) {
                return true;
            }

            $now = time();
            foreach ($lstLocked as $ldata) {
                //lock is time out
                if ($now - $ldata['start_lock_time'] >= $timeoutMinute*60) {
                    $this->releaseLockClassCommonNote($cid, $ldata['uid']);
                }
            }
        }
        catch (Exception $e) {
            debug_log('Admin/Bll/School/dealCommonNoteTimeoutLock:' . $e->getMessage());
            return false;
        }
        return true;
    }
}
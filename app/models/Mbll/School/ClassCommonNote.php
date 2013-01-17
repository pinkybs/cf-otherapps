<?php

require_once 'Mbll/Abstract.php';

/**
 * Mixi App School Class Common Note logic Operation
 *
 * @package    Mbll/School
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/12/01
 */
final class Mbll_School_ClassCommonNote extends Mbll_Abstract
{

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
            require_once 'Mdal/School/ClassCommonNote.php';
            $mdalCommonNote = Mdal_School_ClassCommonNote::getDefaultInstance();
            $lstLocked = $mdalCommonNote->listClassNoteLocked($cid);
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
            debug_log('Mbll/School/ClassCommonNote/dealCommonNoteTimeoutLock:' . $e->getMessage());
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
            require_once 'Mdal/School/Class.php';
            $mdalClass = Mdal_School_Class::getDefaultInstance();
            require_once 'Mdal/School/ClassCommonNote.php';
            $mdalCommonNote = Mdal_School_ClassCommonNote::getDefaultInstance();

            $rowClass = $mdalClass->getClassInfo($cid);
            if (empty($rowClass)) {
                return false;
            }
            if (!$mdalClass->isClassMember($cid, $uid)) {
                return false;
            }

            $mynoteLog = $mdalCommonNote->getCommonNoteUserByPk($cid, $uid);
            //class note is locked by other user
            if ($mdalCommonNote->isClassNoteLocked($cid)) {
                if (empty($mynoteLog) || $mynoteLog['islock'] != 1) {
                    return false;
                }
                //too many lock user
                $cntLock = $mdalCommonNote->getClassNoteLockedCount($cid);
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
                $mdalCommonNote->insertCommonNoteUser($aryLogInfo);
            }
            else {
                $aryLogInfo = array();
                $aryLogInfo['islock'] = 1;
                $aryLogInfo['start_lock_time'] = time();
                $mdalCommonNote->updateCommonNoteUser($aryLogInfo, $cid, $uid);
            }
            $this->_wdb->commit();
        }
        catch (Exception $e) {
            try {
                $this->_wdb->rollBack();
            }
            catch (Exception $e1) {
            }
            debug_log('Mbll/School/ClassCommonNote/addLockClassCommonNote:' . $e->getMessage());
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

            require_once 'Mdal/School/ClassCommonNote.php';
            $mdalCommonNote = Mdal_School_ClassCommonNote::getDefaultInstance();
            $mynoteLog = $mdalCommonNote->getCommonNoteUserByPk($cid, $uid);
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
            $mdalCommonNote->updateCommonNoteUser($aryLogInfo, $cid, $uid);
        }
        catch (Exception $e) {
            debug_log('Mbll/School/ClassCommonNote/releaseLockClassCommonNote:' . $e->getMessage());
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
    public function editClassCommonNote($info, $cid, $uid)
    {
        try {
            require_once 'Mdal/School/Class.php';
            $mdalClass = Mdal_School_Class::getDefaultInstance();
            require_once 'Mdal/School/ClassCommonNote.php';
            $mdalCommonNote = Mdal_School_ClassCommonNote::getDefaultInstance();

            if (!$mdalClass->isClassMember($cid, $uid)) {
                return false;
            }

            $this->_wdb->beginTransaction();

            $rowClass = $mdalClass->getClassInfoLock($cid);
            if (empty($rowClass)) {
                $this->_wdb->rollBack();
                return false;
            }

            $mynoteLog = $mdalCommonNote->getCommonNoteUserByPk($cid, $uid);
            //class note is locked by other user
            if ($mdalCommonNote->isClassNoteLocked($cid)) {
                if (empty($mynoteLog) || $mynoteLog['islock'] != 1) {
                    $this->_wdb->rollBack();
                    return false;
                }
            }

            //insert / update comment log table  release lock
            if (empty($mynoteLog)) {
                $aryLogInfo = array();
                $aryLogInfo['cid'] = $cid;
                $aryLogInfo['uid'] = $uid;
                $aryLogInfo['content'] = $info['introduce'];
                $aryLogInfo['start_lock_time'] = time();
                $aryLogInfo['end_lock_time'] = time();
                $aryLogInfo['update_time'] = time();
                $mdalCommonNote->insertCommonNoteUser($aryLogInfo);
            }
            else {
                $aryLogInfo = array();
                $aryLogInfo['content'] = $info['introduce'];
                $aryLogInfo['islock'] = 0;
                $aryLogInfo['end_lock_time'] = time();
                $aryLogInfo['update_time'] = time();
                $mdalCommonNote->updateCommonNoteUser($aryLogInfo, $cid, $uid);
            }

            //update class
            $info['last_new_update_time'] = time();
            $info['status'] = 1;
            $mdalClass->updateClass($info, $cid);

            $this->_wdb->commit();
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            debug_log('Mbll/School/ClassCommonNote/editClassCommonNote:' . $e->getMessage());
            return false;
        }

        return true;
    }

}
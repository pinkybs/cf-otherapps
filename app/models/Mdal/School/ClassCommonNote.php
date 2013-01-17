<?php

require_once 'Mdal/Abstract.php';

/**
 * Mdal School
 * MixiApp School Class Common Note Data Access Layer
 *
 * @package    Mdal/School
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/12/01    zhangxin
 */
class Mdal_School_ClassCommonNote extends Mdal_Abstract
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
     * list common note editor user
     *
     * @param integer $cid
     * @param integer $pageindex
     * @param integer $pagesize
     * @return array
     */
    public function listCommonNoteUser($cid, $pageindex = 1, $pagesize = 10)
    {
        $start = ($pageindex - 1) * $pagesize;
        $sql = "SELECT * FROM school_class_common_note_editor WHERE cid=:cid AND update_time IS NOT NULL
                AND uid != 0 ORDER BY update_time DESC LIMIT $start, $pagesize";

        return $this->_rdb->fetchAll($sql, array('cid' => $cid));
    }

    /**
     * get common note editor user count
     *
     * @param integer $cid
     * @return integer
     */
    public function getCommonNoteUserCount($cid)
    {
        $sql = "SELECT COUNT(uid) FROM school_class_common_note_editor WHERE cid=:cid AND update_time IS NOT NULL ";
        return $this->_rdb->fetchOne($sql);
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

}
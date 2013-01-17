<?php

require_once 'Dal/Abstract.php';

/**
 * Dal Chat
 * MixiApp ChatAttendConfirm Data Access Layer
 *
 * @package    Dal/Chat
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/05/08    zhangxin
 */
class Dal_Chat_AttendConfirm extends Dal_Abstract
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
     * list chat attend confirm uids
     *
     * @param Integer $cid
     * @param Integer $pageindex
     * @param Integer $pagesize
     * @return array
     */
    public function listChatAttendConfirmUids($cid, $pageindex = 1, $pagesize = 10)
    {
        $start = ($pageindex - 1) * $pagesize;
        $sql = "SELECT uid FROM chat_attend_cofirm WHERE cid=:cid
                ORDER BY create_time LIMIT $start, $pagesize ";

        return $this->_rdb->fetchAll($sql, array('cid'=>$cid));
    }

	/**
     * list chat attend confirm
     *
     * @param Integer $cid
     * @param Integer $pageindex
     * @param Integer $pagesize
     * @return array
     */
    public function listChatAttendConfirm($cid, $pageindex = 1, $pagesize = 10)
    {
        $start = ($pageindex - 1) * $pagesize;
        $sql = "SELECT * FROM chat_attend_cofirm WHERE cid=:cid
                ORDER BY create_time LIMIT $start, $pagesize ";

        return $this->_rdb->fetchAll($sql, array('cid'=>$cid));
    }

    /**
     * get chat attend confirm count
     *
     * @param Integer $cid
     * @return integer
     */
    public function getChatAttendConfirmCount($cid)
    {
        $sql = 'SELECT COUNT(id) FROM chat_attend_cofirm WHERE cid=:cid ';

        return $this->_rdb->fetchOne($sql, array('cid'=>$cid));
    }


    /**
     * get chat attend confirm by id
     *
     * @param Integer $id
     * @return array
     */
    public function getChatAttendConfirmById($id)
    {
        $sql = 'SELECT * FROM chat_attend_cofirm WHERE id=:id ';
        return $this->_rdb->fetchRow($sql, array('id' => $id));
    }

    /**
     * get chat attend confirm by cid uid
     *
     * @param integer $cid
     * @param string $userId
     * @return array
     */
    public function getChatAttendConfirmByCidUid($cid, $userId)
    {
        $sql = 'SELECT * FROM chat_attend_cofirm WHERE cid=:cid AND uid=:uid';
        return $this->_rdb->fetchRow($sql, array('cid' => $cid, 'uid' => $userId));
    }

    /**
     * insert chat attend confirm
     *
     * @param array $info
     * @return integer
     */
    public function insertChatAttendConfirm($info)
    {
        $this->_wdb->insert('chat_attend_cofirm', $info);
        return $this->_wdb->lastInsertId();
    }

    /**
     * update chat attend confirm
     *
     * @param array $info
     * @param integer $id
     * @return integer
     */
    public function updateChatAttendConfirm($info, $id)
    {
        $where = $this->_wdb->quoteInto('id = ?', $id);
        return $this->_wdb->update('chat_attend_cofirm', $info, $where);
    }

    /**
     * update chat attend confirm by cid uid
     *
     * @param array $info
     * @param integer $cid
     * @param string $userId
     * @return integer
     */
    public function updateChatAttendConfirmByCidUid($info, $cid, $userId)
    {
        $where = array($this->_wdb->quoteInto('cid=?', $cid),
                       $this->_wdb->quoteInto('uid=?', $userId));

        return $this->_wdb->update('chat_attend_cofirm', $info, $where);
    }

    /**
     * delete chat attend confirm
     *
     * @param integer $id
     * @return integer
     */
    public function deleteChatAttendConfirm($id)
    {
        $sql = "DELETE FROM chat_attend_cofirm WHERE id=:id ";
        return $this->_wdb->query($sql, array('id' => $id));
    }

	/**
     * delete chat attend confirm by cid
     *
     * @param integer $cid
     * @return integer
     */
    public function deleteChatAttendConfirmByCid($cid)
    {
        $sql = "DELETE FROM chat_attend_cofirm WHERE cid=:cid ";
        return $this->_wdb->query($sql, array('cid' => $cid));
    }

	/**
     * delete chat attend confirm by cid uid
     *
     * @param integer $cid
     * @param string $uid
     * @return integer
     */
    public function deleteChatAttendConfirmByCidUid($cid, $uid)
    {
        $sql = "DELETE FROM chat_attend_cofirm WHERE cid=:cid AND uid=:uid ";
        return $this->_wdb->query($sql, array('cid' => $cid, 'uid' => $uid));
    }

}
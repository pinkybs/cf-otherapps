<?php

require_once 'Dal/Abstract.php';

/**
 * Dal Chat Member
 * MixiApp Chat Member Data Access Layer
 *
 * @package    Dal/Chat
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/05/08    zhangxin
 */
class Dal_Chat_Member extends Dal_Abstract
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
     * list chat member uids
     *
     * @param Integer $cid
     * @param Integer $pageindex
     * @param Integer $pagesize
     * @return array
     */
    public function listChatMemberUids($cid, $pageindex = 1, $pagesize = 10)
    {
        $start = ($pageindex - 1) * $pagesize;
        $sql = "SELECT uid FROM chat_member WHERE cid=:cid
                ORDER BY create_time LIMIT $start, $pagesize ";

        return $this->_rdb->fetchAll($sql, array('cid'=>$cid));
    }

    /**
     * list chat member
     *
     * @param Integer $cid
     * @param Integer $pageindex
     * @param Integer $pagesize
     * @return array
     */
    public function listChatMember($cid, $pageindex = 1, $pagesize = 10)
    {
        $start = ($pageindex - 1) * $pagesize;
        $sql = "SELECT * FROM chat_member WHERE cid=:cid
                ORDER BY create_time LIMIT $start, $pagesize ";

        return $this->_rdb->fetchAll($sql, array('cid'=>$cid));
    }

    /**
     * get chat count by owner
     *
     * @param Integer $cid
     * @return integer
     */
    public function getChatMemberCount($cid)
    {
        $sql = 'SELECT COUNT(*) FROM chat_member WHERE cid=:cid ';

        return $this->_rdb->fetchOne($sql, array('cid'=>$cid));
    }


    /**
     * get chat member by pk
     *
     * @param Integer $cid
     * @param string $userId
     * @return array
     */
    public function getChatMemberByPk($cid, $userId)
    {
        $sql = 'SELECT * FROM chat_member WHERE cid=:cid AND uid=:uid ';
        return $this->_rdb->fetchRow($sql, array('cid' => $cid, 'uid' => $userId));
    }

    /**
     * insert chat member
     *
     * @param array $info
     * @return integer
     */
    public function insertChatMember($info)
    {
        return $this->_wdb->insert('chat_member', $info);
    }

    /**
     * update chat member
     *
     * @param array $info
     * @param integer $cid
     * @param string $userId
     * @return integer
     */
    public function updateChatMember($info, $cid, $userId)
    {
        $where = array($this->_wdb->quoteInto('cid=?', $cid),
                       $this->_wdb->quoteInto('uid=?', $userId));

        return $this->_wdb->update('chat_member', $info, $where);
    }

    /**
     * delete chat member
     *
     * @param integer $cid
     * @param string $userId
     * @return integer
     */
    public function deleteChatMember($cid, $userId)
    {
        $sql = 'DELETE FROM chat_member WHERE cid=:cid AND uid=:uid ';
        return $this->_wdb->query($sql, array('cid' => $cid, 'uid' => $userId));
    }

   	/**
     * is chat member
     *
     * @param integer $cid
     * @param string $uid
     * @return boolean
     */
    public function isChatMember($cid, $uid)
    {
        $sql = 'SELECT COUNT(*) FROM chat_member WHERE cid=:cid AND uid=:uid ';

        $result = $this->_rdb->fetchOne($sql, array('cid'=>$cid, 'uid' => $uid));
        return $result > 0 ? true : false;
    }

}
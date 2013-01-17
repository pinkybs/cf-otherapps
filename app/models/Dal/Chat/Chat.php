<?php

require_once 'Dal/Abstract.php';

/**
 * Dal Chat
 * MixiApp Chat Data Access Layer
 *
 * @package    Dal/Chat
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/05/07    zhangxin
 */
class Dal_Chat_Chat extends Dal_Abstract
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
     * list chat
     *
     * @param Integer $pageindex
     * @param Integer $pagesize
     * @return array
     */
    public function listChat($pageindex = 1, $pagesize = 10)
    {
        $start = ($pageindex - 1) * $pagesize;
        $sql = "SELECT * FROM chat ORDER BY create_time LIMIT $start, $pagesize";

        return $this->_rdb->fetchAll($sql);
    }

	/**
     * list chat by owner
     *
     * @param string $ownerId
     * @param Integer $pageindex
     * @param Integer $pagesize
     * @return array
     */
    public function listChatByOwner($ownerId, $pageindex = 1, $pagesize = 10)
    {
        $start = ($pageindex - 1) * $pagesize;
        $sql = "SELECT * FROM chat
                WHERE uid=:uid AND iscanceled=0
                ORDER BY create_time LIMIT $start, $pagesize ";

        return $this->_rdb->fetchAll($sql, array('uid'=>$ownerId));
    }

    /**
     * get chat count by owner
     *
     * @param string $ownerId
     * @return integer
     */
    public function getChatByOwnerCount($ownerId)
    {
        $sql = 'SELECT COUNT(cid) FROM chat
                WHERE uid=:uid AND iscanceled=0 ';

        return $this->_rdb->fetchOne($sql, array('uid'=>$ownerId));
    }

	/**
     * list chat by joiner
     *
     * @param string $joinerId
     * @param Integer $pageindex
     * @param Integer $pagesize
     * @return array
     */
    public function listChatByJoiner($joinerId, $pageindex = 1, $pagesize = 10)
    {
        $start = ($pageindex - 1) * $pagesize;
        $sql = "SELECT c.* FROM chat c, chat_member m
                WHERE c.cid=m.cid AND m.uid=:uid AND c.uid<>:uid AND c.iscanceled=0
                ORDER BY c.create_time LIMIT $start, $pagesize ";

        return $this->_rdb->fetchAll($sql, array('uid'=>$joinerId));
    }

	/**
     * get chat count by joiner
     *
     * @param string $joinerId
     * @return integer
     */
    public function getChatByJoinerCount($joinerId)
    {
        $sql = 'SELECT COUNT(c.cid) FROM chat c, chat_member m
                WHERE c.cid=m.cid AND m.uid=:uid AND c.uid<>:uid AND c.iscanceled=0 ';

        return $this->_rdb->fetchOne($sql, array('uid'=>$joinerId));
    }

	/**
     * list chat by invite to confirm
     *
     * @param string $inviteeId
     * @param Integer $pageindex
     * @param Integer $pagesize
     * @return array
     */
    public function listChatByNeedConfirm($inviteeId, $pageindex = 1, $pagesize = 10)
    {
        $start = ($pageindex - 1) * $pagesize;
        $sql = "SELECT c.* FROM chat c, chat_attend_cofirm a
                WHERE c.cid=a.cid AND a.uid=:uid AND a.isdenied=0 AND c.iscanceled=0
                ORDER BY c.create_time LIMIT $start, $pagesize ";

        return $this->_rdb->fetchAll($sql, array('uid'=>$inviteeId));
    }

	/**
     * get chat count by invite to confirm
     *
     * @param string $inviteeId
     * @return integer
     */
    public function getChatByNeedConfirmCount($inviteeId)
    {
        $sql = 'SELECT COUNT(c.cid) FROM chat c, chat_attend_cofirm a
                WHERE c.cid=a.cid AND a.uid=:uid AND a.isdeny=0 AND c.iscanceled=0 ';

        return $this->_rdb->fetchOne($sql, array('uid'=>$inviteeId));
    }


    /**
     * get chat by id
     *
     * @param Integer $id
     * @return array
     */
    public function getChatById($id)
    {
        $sql = 'SELECT * FROM chat WHERE cid=:cid ';
        return $this->_rdb->fetchRow($sql, array('cid' => $id));
    }

    /**
     * insert chat
     *
     * @param array $info
     * @return integer
     */
    public function insertChat($info)
    {
        $this->_wdb->insert('chat', $info);
        return $this->_wdb->lastInsertId();
    }

    /**
     * update chat
     *
     * @param array $info
     * @param integer $id
     * @return integer
     */
    public function updateChat($info, $id)
    {
        $where = $this->_wdb->quoteInto('cid = ?', $id);
        return $this->_wdb->update('chat', $info, $where);
    }

    /**
     * delete chat
     *
     * @param integer $id
     * @return integer
     */
    public function deleteChat($id)
    {
        $sql = "DELETE FROM chat WHERE cid=:cid ";
        return $this->_wdb->query($sql, array('cid' => $id));
    }

   	/**
     * is chat ended or canceled
     *
     * @param integer $cid
     * @return boolean
     */
    public function isChatEnded($cid)
    {
        $sql = 'SELECT COUNT(cid) FROM chat
                WHERE cid=:cid AND iscanceled=1 ';

        $result = $this->_rdb->fetchOne($sql, array('cid'=>$cid));
        return $result > 0 ? true : false;
    }

	/**
     * is chat started
     *
     * @param integer $cid
     * @return boolean
     */
    public function isChatStarted($cid)
    {
        $sql = 'SELECT COUNT(cid) FROM chat
                WHERE cid=:cid AND iscanceled=0 AND isstarted=1 ';

        $result = $this->_rdb->fetchOne($sql, array('cid'=>$cid));
        return $result > 0 ? true : false;
    }

	/**
     * is chat started
     *
     * @param integer $curDate
     * @return boolean
     */
    public function listReadyToBeginChat($curDate)
    {
        $sql = 'SELECT cid,uid,title,start_time FROM chat
                WHERE iscanceled=0 AND isstarted=0 AND isbatchfeedsent=0
                AND (UNIX_TIMESTAMP(start_time) - :curDate)<=60*15
                AND (UNIX_TIMESTAMP(start_time) - :curDate)>0 ';

        return $this->_rdb->fetchAll($sql, array('curDate'=>$curDate));
    }

}
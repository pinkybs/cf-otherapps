<?php

require_once 'Dal/Abstract.php';

/**
 * Dal Chat
 * MixiApp ChatDetail Data Access Layer
 *
 * @package    Dal/Chat
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/05/08    zhangxin
 */
class Dal_Chat_Detail extends Dal_Abstract
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
     * list chat detail
     *
     * @param Integer $cid
     * @param Integer $lastId
     * @param Integer $issystem
     * @param Integer $pageindex
     * @param Integer $pagesize
     * @return array
     */
    public function listChatDetail($cid, $lastId, $issystem, $pageindex = 1, $pagesize = 10)
    {
        $start = ($pageindex - 1) * $pagesize;
        $sql = "SELECT id AS did,cid,uid,content,issystem,create_time AS last_id,FROM_UNIXTIME(create_time) AS create_time FROM chat_detail
                WHERE cid=:cid AND create_time>:create_time
                AND issystem=:issystem
                ORDER BY create_time LIMIT $start, $pagesize";

        return $this->_rdb->fetchAll($sql, array('cid'=>$cid, 'create_time'=>$lastId, 'issystem'=>$issystem));
    }

    /**
     * get chat detail by id
     *
     * @param Integer $id
     * @return array
     */
    public function getChatDetailById($id)
    {
        $sql = 'SELECT * FROM chat_detail WHERE id=:id ';
        return $this->_rdb->fetchRow($sql, array('id' => $id));
    }

    /**
     * insert chat chat detail
     *
     * @param array $info
     * @return integer
     */
    public function insertChatDetail($info)
    {
        $this->_wdb->insert('chat_detail', $info);
        return $this->_wdb->lastInsertId();
    }

    /**
     * update chat detail
     *
     * @param array $info
     * @param integer $id
     * @return integer
     */
    public function updateChatDetail($info, $id)
    {
        $where = $this->_wdb->quoteInto('id = ?', $id);
        return $this->_wdb->update('chat_detail', $info, $where);
    }

    /**
     * delete chat detail
     *
     * @param integer $id
     * @return integer
     */
    public function deleteChatDetail($id)
    {
        $sql = 'DELETE FROM chat_detail WHERE id=:id ';
        return $this->_wdb->query($sql, array('id' => $id));
    }

  	/**
     * delete chat detail by cid
     *
     * @param integer $cid
     * @return integer
     */
    public function deleteChatDetailByCid($cid)
    {
        $sql = 'DELETE FROM chat_detail WHERE cid=:cid ';
        return $this->_wdb->query($sql, array('cid' => $cid));
    }

  	/**
     * delete chat detail by uid
     *
     * @param string $uid
     * @return integer
     */
    public function deleteChatDetailByUid($uid)
    {
        $sql = 'DELETE FROM chat_detail WHERE uid=:uid ';
        return $this->_wdb->query($sql, array('uid' => $uid));
    }

}
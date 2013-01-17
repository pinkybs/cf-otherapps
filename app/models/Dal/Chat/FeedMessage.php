<?php

require_once 'Dal/Abstract.php';

/**
 * Dal FeedMessage
 * MixiApp FeedMessage Data Access Layer
 *
 * @package    Dal/Chat
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/06/12    zhangxin
 */
class Dal_Chat_FeedMessage extends Dal_Abstract
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
     * list feed message
     *
     * @param string $uid
     * @param Integer $pageindex
     * @param Integer $pagesize
     * @return array
     */
    public function listFeedMessage($uid, $pageindex = 1, $pagesize = 10)
    {
        $start = ($pageindex - 1) * $pagesize;
        $sql = "SELECT *,DATE_FORMAT(FROM_UNIXTIME(create_time), '%Y年%m月%d日 %H:%i') AS format_time
                FROM chat_feed_message WHERE tar_uid=:uid AND isdelete=0
                ORDER BY create_time DESC LIMIT $start, $pagesize";

        return $this->_rdb->fetchAll($sql, array('uid' => $uid));
    }

    /**
     * get feed message count
     *
     * @param string $uid
     * @return integer
     */
    public function getFeedMessageCount($uid)
    {
        $sql = 'SELECT COUNT(id) FROM chat_feed_message WHERE tar_uid=:uid AND isdelete=0 ';
        return $this->_rdb->fetchOne($sql, array('uid' => $uid));
    }

    /**
     * get feed message by id
     *
     * @param Integer $id
     * @return array
     */
    public function getFeedMessageById($id)
    {
        $sql = 'SELECT * FROM chat_feed_message WHERE id=:id ';
        return $this->_rdb->fetchRow($sql, array('id' => $id));
    }

    /**
     * insert feed message
     *
     * @param array $info
     * @return integer
     */
    public function insertFeedMessage($info)
    {
        $this->_wdb->insert('chat_feed_message', $info);
        return $this->_wdb->lastInsertId();
    }

    /**
     * update feed message
     *
     * @param array $info
     * @param integer $id
     * @return integer
     */
    public function updateFeedMessage($info, $id)
    {
        $where = $this->_wdb->quoteInto('id = ?', $id);
        return $this->_wdb->update('chat_feed_message', $info, $where);
    }

    /**
     * delete feed message
     *
     * @param integer $id
     * @return integer
     */
    public function deleteFeedMessage($id)
    {
        $sql = "DELETE FROM chat_feed_message WHERE id=:id ";
        return $this->_wdb->query($sql, array('id' => $id));
    }

}
<?php

require_once 'Mdal/Abstract.php';

class Mdal_School_Message extends Mdal_Abstract
{
    /**
     * user table name
     *
     * @var string
     */

    protected static $_instance;
    protected $table_user = 'school_message';

    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * list user's message
     *
     * @param integer $uid
     * @param integer $pageindex
     * @param integer $pagesize
     * @return array
     */
    public function listMessage($uid, $pageindex = 1, $pagesize = 10)
    {
        $start = ($pageindex - 1) * $pagesize;
        $sql = "SELECT id,target_uid,type,COUNT(id) AS msg_count FROM school_message WHERE target_uid=:target_uid
                GROUP BY type ORDER BY type LIMIT $start, $pagesize";

        return $this->_rdb->fetchAll($sql, array('target_uid' => $uid));
    }

    /**
     * get message count
     *
     * @param integer $uid
     * @return integer
     */
    public function getMessageCount($uid)
    {
        $sql = 'SELECT COUNT(id) FROM school_message WHERE target_uid=:target_uid ';
        return $this->_rdb->fetchOne($sql, array('target_uid' => $uid));
    }

    /**
     * get message by key
     *
     * @param integer $id
     * @param integer $visit_uid
     * @return integer
     */
    public function getMessage($id)
    {
        $sql = "SELECT * FROM school_message WHERE id=:id ";
        return $this->_rdb->fetchRow($sql, array('id' => $id));
    }

    /**
     * insert message
     *
     * @param array $info
     * @return integer
     */
    public function insertMessage($info)
    {
        $this->_wdb->insert($this->table_user, $info);
        return $this->_wdb->lastInsertId();
    }

    /**
     * delete message
     *
     * @param integer $id
     * @return integer
     */
    public function deleteMessage($id)
    {
        $sql = "DELETE FROM school_message WHERE id=:id ";
        return $this->_wdb->query($sql, array('id' => $id));
    }

    /**
     * update Forbidword
     *
     * @param array $info
     * @param integer $id
     * @return integer
     */
    public function updateMessage($info, $id)
    {
        $where = $this->_wdb->quoteInto('id = ?', $id);
        return $this->_wdb->update('school_message', $info, $where);
    }

    /**
     * check invite class exites
     *
     * @param array $info
     * @return integer
     */
    public function isInviteClassExites($info)
    {
        $sql = "SELECT id FROM $this->table_user WHERE uid=:uid AND target_uid=:target_uid
				AND `type` = :type AND object_id=:object_id";
        $result = $this->_rdb->fetchRow($sql, array('uid' => $info['uid'], 'target_uid' => $info['target_uid'], 'type' => $info['type'], 'object_id' => $info['object_id']));
        return $result;
    }

    public function isMessageExites($target_uid, $type)
    {
    	$sql = "SELECT id FROM $this->table_user WHERE target_uid=:target_uid AND `type` = :type ";
        $result = $this->_rdb->fetchRow($sql, array('target_uid' => $target_uid, 'type' => $type));
        return $result;
    }

/**
 * xial ***************************************************
 */
    /**
     * get invite message
     *
     * @param integer $uid
     * @param integer $pageindex
     * @param integer $pagesize
     * @return array
     */
    public function lstInviteMessage($uid, $type = 1, $pageindex = 1, $pagesize = 10)
    {
        $start = ($pageindex - 1) * $pagesize;
        $sql = "SELECT * FROM school_message WHERE target_uid=:target_uid AND `type` = $type
        		ORDER BY create_time LIMIT $start, $pagesize";

        return $this->_rdb->fetchAll($sql, array('target_uid' => $uid));
    }

    /**
     * get count invite message
     * @param integer $uid
     * @param integer $type
     *
     * @return integer
     */
    public function getCntInviteMessageById($uid, $type = 1)
    {
    	$sql = "SELECT COUNT(1) FROM school_message WHERE target_uid=:target_uid AND `type` = $type";
    	return $this->_rdb->fetchOne($sql, array('target_uid' => $uid));
    }

    /**
     * delete all same's invite
     *
     * @param integer $uid
     * @param integer $wday
     * @param integer $part
     * @param integer $type
     * @return integer
     */
    public function deleteInvite($uid, $wday, $part, $type = 1)
    {
		$sql = "DELETE FROM school_message WHERE target_uid = :uid AND wday = :wday AND part = :part AND `type` = $type";
        return $this->_wdb->query($sql, array('uid' => $uid, 'wday' => $wday, 'part' => $part));
    }

    /**
     * get same's invite
     *
     * @param integer $uid
     * @param integer $wday
     * @param integer $part
     * @param integer $type
     * @return integer
     */
    public function isSameTimeScheduleInvite($uid, $wday, $part, $type = 1)
    {
    	$sql = "SELECT id FROM school_message WHERE target_uid = :uid AND wday = :wday AND part = :part AND `type` = $type";
    	return $this->_rdb->fetchRow($sql, array('uid' => $uid, 'wday' => $wday, 'part' => $part));
    }
}
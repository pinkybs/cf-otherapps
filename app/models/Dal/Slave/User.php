<?php

require_once 'Dal/Abstract.php';

class Dal_Slave_User extends Dal_Abstract
{
    /**
     * user table name
     *
     * @var string
     */
    protected $table_user = 'slave_user';

    protected static $_instance;

    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function getUser($uid)
    {
        $sql = "SELECT * FROM $this->table_user WHERE uid=:uid";

        return $this->_rdb->fetchRow($sql, array('uid' => $uid));
    }

    public function getUsers($uids, $contactNum)
    {
        $sql = "SELECT uid, rand() AS r
                FROM $this->table_user where uid in ($uids)
                    AND status = 0
                ORDER BY r LIMIT 0, $contactNum";

        return $this->_rdb->fetchAll($sql);
    }

    public function updateUser($uid, $status = 0)
    {
        $time = time();
        $sql = "INSERT INTO $this->table_user (uid, status, time) "
             . "VALUES (:uid, :status, $time) ON DUPLICATE KEY UPDATE "
             . "status = :status, time = $time";

        $params = array(
            'uid' => $uid,
            'status' => $status
        );

        return $this->_wdb->query($sql, $params);
    }

    public function getAppFriendIds($fids)
    {
        $ids = $this->_rdb->quote($fids);
        $sql = "SELECT uid FROM $this->table_user WHERE uid in ($ids)";

        $rows = $this->_rdb->fetchAll($sql);

        if ($rows) {
            $result = array();
            foreach ($rows as $row) {
                $result[] = $row['uid'];
            }

            return implode(',', $result);
        }
        else {
            return '';
        }

    }

	/**
     * list app uids
     *
     * @param Integer $idxStart
     * @param Integer $pagesize
     * @return array
     */
    public function listAppUids($idxStart = 1, $pagesize = 10)
    {
        $start = $idxStart > 0 ? ($idxStart - 1) : 0;
        $sql = "SELECT uid AS fid FROM slave_user
                ORDER BY uid LIMIT $start, $pagesize ";

        return $this->_rdb->fetchAll($sql);
    }

    /**
     * get app uids count
     *
     * @return integer
     */
    public function getAppUidsCount()
    {
        $sql = 'SELECT COUNT(uid) FROM slave_user ';
        return $this->_rdb->fetchOne($sql);
    }

	/**
     * list app uids
     *
     * @param Integer $uid
     * @return array
     */
    public function getBeforeUidCount($uid)
    {
        $sql = "SELECT COUNT(uid) FROM slave_user WHERE uid<:uid";

        return $this->_rdb->fetchOne($sql, array('uid' => $uid));
    }
}
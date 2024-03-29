<?php

require_once 'Dal/Abstract.php';

class Dal_Slave_Friend extends Dal_Abstract
{
    /**
     * user table name
     *
     * @var string
     */
    protected $table_friend = 'slave_friend';

    protected static $_instance;

    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function getFriends($uid)
    {
        $sql = "SELECT fid FROM $this->table_friend WHERE uid=:uid";

        $result = $this->_rdb->fetchAll($sql, array('uid' => $uid));

        $fids = array();
        if ($result) {
            foreach ($result as $row) {
                $fids[] = $row['fid'];
            }
        }

        return $fids;
    }

    public function deleteFriends($uid)
    {
        $sql = "DELETE FROM $this->table_friend WHERE uid=:uid OR fid=:fid";

        return $this->_wdb->query($sql, array('uid' => $uid, 'fid' => $uid));
    }

    public function insertFriends($uid, $fids)
    {
        $count = count($fids);
        if ($count == 0) {
            return;
        }

        $uid = $this->_wdb->quote($uid);
        $fid = $this->_wdb->quote($fids[0]);

        $sql = "INSERT INTO $this->table_friend(uid, fid) VALUES"
             . '(' . $uid . ',' . $fid . '),'
             . '(' . $fid . ',' . $uid . ')';

        for($i = 1; $i < $count; $i++) {
            $fid = $this->_wdb->quote($fids[$i]);
            $sql .= ',(' . $uid . ',' . $fid . ')'
                  . ',(' . $fid . ',' . $uid . ')';
        }

        return $this->_wdb->query($sql);
    }

	/**
     * list mixi friend
     *
     * @param string $uid
     * @param Integer $pageindex
     * @param Integer $pagesize
     * @return array
     */
    public function listMixiFriend($uid, $pageindex = 1, $pagesize = 10)
    {
        $start = ($pageindex - 1) * $pagesize;
        $sql = "SELECT fid FROM mixi_friend WHERE uid=:uid
                ORDER BY fid LIMIT $start, $pagesize ";

        return $this->_rdb->fetchAll($sql, array('uid' => $uid));
    }

    /**
     * get mixi friend count
     *
     * @param string $uid
     * @return integer
     */
    public function getMixiFriendCount($uid)
    {
        $sql = 'SELECT COUNT(fid) FROM mixi_friend WHERE uid=:uid ';
        return $this->_rdb->fetchOne($sql, array('uid' => $uid));
    }




//*******************************************************************************
   /* slave friend list
     * @param string $uid
     * @return array
     */
    public function listSlaveFriend($uid)
    {
        $sql = "SELECT fid FROM slave_friend WHERE uid=:uid";
		return $this->_rdb->fetchAll($sql, array('uid' => $uid));
    }
}
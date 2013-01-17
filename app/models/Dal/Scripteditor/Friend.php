<?php

require_once 'Dal/Abstract.php';

class Dal_Scripteditor_Friend extends Dal_Abstract
{
    /**
     * user table name
     *
     * @var string
     */
    protected $table_friend = 'scripteditor_friend';
    
    protected static $_instance;
    
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        
        return self::$_instance;
    }

    public function getFriendIds($uid)
    {
        $sql = "SELECT fids FROM $this->table_friend WHERE uid=:uid";
        
        return $this->_rdb->fetchOne($sql, array('uid' => $uid));
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
    
    public function updateFriendIds($uid, $fids)
    {
        $time = time();
        $sql = "INSERT INTO $this->table_friend (uid, fids, time) "
             . "VALUES (:uid, :fids, $time) ON DUPLICATE KEY UPDATE "
             . "fids = :fids, time = $time";
        
        $params = array(
            'uid' => $uid,
            'fids' => $fids
        );
        
        return $this->_wdb->query($sql, $params);
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
}
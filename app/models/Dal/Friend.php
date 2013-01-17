<?php

require_once 'Dal/Abstract.php';

class Dal_Friend extends Dal_Abstract
{
    /**
     * user table name
     *
     * @var string
     */
    protected $table_friend = 'mixi_friend';
    
    protected static $_instance;
    
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        
        return self::$_instance;
    }
    
    public function getTableName($id)
    {
        $n = $id % 10;
        return $this->table_friend . '_' . $n;
    }
    
    public function getFriends($uid)
    {
        $tname = $this->getTableName($uid);
        
        $sql = "SELECT fid FROM $tname WHERE uid=:uid";
        
        $result = $this->_rdb->fetchAll($sql, array('uid' => $uid));
        
        $fids = array();
        if ($result) {
            foreach ($result as $row) {
                $fids[] = $row['fid'];
            }
        }
        
        return $fids;
    }   
    
    /*
    public function deleteFriends($uid)
    {
        $sql = "DELETE FROM $this->table_friend WHERE uid=:uid";
        
        $sql2 = "DELETE FROM $this->table_friend WHERE fid=:fid";
        
        $this->_wdb->query($sql, array('uid' => $uid));
        $this->_wdb->query($sql2, array('fid' => $uid));
    }
    */
    
    public function deleteFriend($uid, $fid)
    {
        $tname1 = $this->getTableName($uid);
        $tname2 = $this->getTableName($fid);
        
        $sql = "DELETE FROM $tname1 WHERE uid=:uid AND fid=:fid";
        
        $sql2 = "DELETE FROM $tname2 WHERE uid=:uid AND fid=:fid";
        
        $this->_wdb->query($sql, array('uid' => $uid, 'fid' => $fid));
        $this->_wdb->query($sql2, array('uid' => $fid, 'fid' => $uid));
    }
    
    /*
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
    */
    
    public function insertFriend($uid, $fid)
    {
        $tname1 = $this->getTableName($uid);
        $tname2 = $this->getTableName($fid);
        
        $sql = "INSERT IGNORE INTO $tname1(uid, fid) VALUES(:uid, :fid)";
        
        $sql2 = "INSERT IGNORE INTO $tname2(uid, fid) VALUES(:uid, :fid)";
        
        $this->_wdb->query($sql, array('uid' => $uid, 'fid' => $fid));
        $this->_wdb->query($sql2, array('uid' => $fid, 'fid' => $uid));
    }
}
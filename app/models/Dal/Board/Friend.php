<?php

require_once 'Dal/Abstract.php';

class Dal_Board_Friend extends Dal_Abstract
{
    /**
     * user table name
     *
     * @var string
     */
    protected $table_friend = 'board_friend';
    
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
}
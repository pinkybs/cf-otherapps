<?php

require_once 'Dal/Abstract.php';

class Dal_Board_User extends Dal_Abstract
{
    /**
     * user table name
     *
     * @var string
     */
    protected $table_user = 'board_user';
    
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
        $sql = "SELECT uid
                FROM $this->table_user where uid in ($uids)
                    AND status = 0
                ORDER BY time LIMIT 0, $contactNum";
        
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
}
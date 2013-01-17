<?php

require_once 'Dal/Abstract.php';

class Dal_Parking_User extends Dal_Abstract
{
    /**
     * user table name
     *
     * @var string
     */
    protected $table_user = 'parking_user';
    
    protected static $_instance;
    
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        
        return self::$_instance;
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
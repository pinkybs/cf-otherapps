<?php

require_once 'Dal/Abstract.php';

class Dal_Linno_Luser extends Dal_Abstract
{
    /**
     * user table name
     *
     * @var string
     */
    protected $table_user = 'linno_user';
    
    protected static $_instance;
    
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        
        return self::$_instance;
    }
    
    public function getLinnoUser($uid)
    {
        $sql = "SELECT * FROM linno_user WHERE uid=:uid";
        
        return $this->_rdb->fetchRow($sql, array('uid' => $uid));
    }
    
    public function getCommonNetworkUser($uid, $network)
    {
        $sql = "SELECT * FROM linno_user WHERE uid<>:uid AND network_id=:network_id ORDER BY RAND() LIMIT 5";
        
        return $this->_rdb->fetchAll($sql, array('uid' => $uid, 'network_id' => $network));
    }
}
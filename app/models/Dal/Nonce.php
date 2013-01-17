<?php

require_once 'Dal/Abstract.php';

class Dal_Nonce extends Dal_Abstract
{
    /**
     * nonce table name
     *
     * @var string
     */
    protected $table_nonce = 'mixi_nonce';
    
    protected static $_instance;
    
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        
        return self::$_instance;
    }

    public function getNonce($nonce)
    {
        $sql = "SELECT * FROM $this->table_nonce WHERE nonce=:nonce";
        
        return $this->_rdb->fetchRow($sql, array('nonce' => $nonce));
    }
    
    public function newNonce($nonce, $expires, $app_id, $owner_id, $viewer_id, $app_name)
    {
        $sql = "INSERT INTO $this->table_nonce (nonce, expires, app_id, owner_id, viewer_id, app_name) VALUES(:nonce, $expires, :app_id, :owner_id, :viewer_id, :app_name)";
        $params = array(
            'nonce'     => $nonce,
            'app_id'    => $app_id,
            'owner_id'  => $owner_id,
            'viewer_id' => $viewer_id,
            'app_name'  => $app_name
        );
        
        return $this->_wdb->query($sql, $params);
    }
    
    public function deleteNonce($nonce)
    {
        $sql = "DELETE FROM $this->table_nonce WHERE nonce=:nonce";
        
        return $this->_wdb->query($sql, array('nonce' => $nonce));
    }
}
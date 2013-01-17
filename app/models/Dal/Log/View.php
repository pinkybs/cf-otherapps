<?php

require_once 'Dal/Abstract.php';

class Dal_Log_View extends Dal_Abstract
{
    /**
     * app invite table name
     *
     * @var string
     */
    protected $table_log_view = 'mixi_app_log_view';
    
    protected static $_instance;
    
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        
        return self::$_instance;
    }

    public function add($app_id, $owner_id, $viewer_id, $view, $time = null)
    {
        if (!$time) {
            $time = time();
        }
        
        $sql = "INSERT INTO $this->table_log_view (app_id, owner_id, viewer_id, view, time) VALUES (:app_id, :owner_id, :viewer_id, :view, $time)";
        
        $params = array(
            'app_id' => $app_id,
            'owner_id' => $owner_id,
            'viewer_id' => $viewer_id,
            'view' => $view
        );
        
        return $this->_wdb->query($sql, $params);
    }
    
    public function getTotalCount($app_id, $time)
    {
        $sql = "SELECT COUNT(*) AS total FROM $this->table_log_view WHERE app_id=:app_id AND time < $time";
        $params = array(
            'app_id' => $app_id
        );
        
        $rows = $this->_rdb->fetchRow($sql, $params);
    }
    
}
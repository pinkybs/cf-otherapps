<?php

require_once 'Dal/Abstract.php';

class Dal_Log_Activity extends Dal_Abstract
{
    /**
     * app invite table name
     *
     * @var string
     */
    protected $table_log_activity = 'mixi_app_log_activity';
    
    protected static $_instance;
    
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        
        return self::$_instance;
    }

    public function add($app_id, $actor, $target, $count, $type, $time = null)
    {
        if (!$time) {
            $time = time();
        }
        
        $sql = "INSERT INTO $this->table_log_activity (app_id, actor, target, count, type, time) VALUES (:app_id, :actor, :target, :count, :type, $time)";
        
        $params = array(
            'app_id' => $app_id,
            'actor' => $actor,
            'target' => $target,
            'count' => $count,
            'type' => $type
        );
        
        return $this->_wdb->query($sql, $params);
    }
    
    public function getTotalCount($app_id, $time)
    {
        $sql = "SELECT SUM(count) AS total FROM $this->table_log_activity WHERE app_id=:app_id AND time < $time";
        $params = array(
            'app_id' => $app_id
        );
        
        $rows = $this->_rdb->fetchRow($sql, $params);
    }
    
}
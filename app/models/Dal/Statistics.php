<?php

/**
 * Board datebase's Operation
 *
 *
 * @package    Dal
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/02/27    Huch
 */
class Dal_Statistics extends Dal_Abstract
{
	protected static $_instance;

    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }
    
    public function getAppId($canvas)
    {
    	$sql = 'SELECT aid FROM admin_app WHERE canvas_name=:canvas';
    	return $this->_rdb->fetchOne($sql,array('canvas' => $canvas));
    }
    
	public function insertLog($info)
	{
		$this->_wdb->insert('app_log', $info);
	}
	
	
    public function getLoginLog($app_id, $uid)
    {
        $sql = 'SELECT * FROM app_login_log WHERE app_id=:app_id AND uid = :uid';
        return $this->_rdb->fetchOne($sql,array('app_id' => $app_id, 'uid' => $uid));
    }
    
	public function insertLoginLog($info)
    {
        $this->_wdb->insert('app_login_log', $info);
    }
    
    public function getRemoveLog($app_id, $uid)
    {
        $sql = 'SELECT * FROM app_remove_log WHERE app_id=:app_id AND uid = :uid';
        return $this->_rdb->fetchOne($sql,array('app_id' => $app_id, 'uid' => $uid));
    }
    
    public function insertRemoveLog($info)
    {
        $this->_wdb->insert('app_remove_log', $info);
    }
}
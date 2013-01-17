<?php

require_once 'Mdal/Abstract.php';

/**
 * Mdal Ship Store
 * MixiApp Ship Store Data Access Layer
 *
 * @package    Mdal/Ship
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/11/30    Liz
 */
class Mdal_Ship_Store extends Mdal_Abstract
{        
    /**
     * table name
     *
     * @var string
     */
    protected $table_store = 'ship_store';
    
    protected static $_instance;

    /**
     * getDefaultInstance
     *
     * @return Mdal_Ship_User
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * update user card count by card id
     * 
     * @param integer $cid
     * @param integer $uid
     * @param integer $change
     * @return void
     */
    public function updateUserCardCoutBySid($cid, $uid, $change)
    {
        $now = time();
        
        $sql = "UPDATE ship_user_card SET count=count+$change,buy_time=$now WHERE uid=:uid AND sid=:sid";
        $this->_wdb->query($sql, array('sid'=>$cid, 'uid' => $uid));
    }
    
    /**
     * get card info by card id
     * 
     * @param integer $sid
     * @return array
     */
    public function getCardInfo($sid)
    {
        $sql = "SELECT * FROM $this->table_store WHERE sid=:sid";
        return $this->_rdb->fetchRow($sql, array('sid'=>$sid));
    }

    /**
     * get store list
     * 
     * @param integer $pageIndex
     * @param integer $pageSize
     * @return array
     */
    public function getStoreList($pageIndex, $pageSize)
    {
        $start = ($pageIndex - 1) * $pageSize;
        
        $sql = "SELECT * FROM $this->table_store LIMIT $start,$pageSize";
        return $this->_rdb->fetchAll($sql);
    }
    
    
    
}
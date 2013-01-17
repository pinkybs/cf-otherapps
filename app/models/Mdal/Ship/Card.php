<?php

require_once 'Mdal/Abstract.php';

/**
 * Mdal Ship Card
 * MixiApp Ship Card Data Access Layer
 *
 * @package    Mdal/Ship
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/11/30    Liz
 */
class Mdal_Ship_Card extends Mdal_Abstract
{        
    /**
     * table name
     *
     * @var string
     */
    protected $table_user_card = 'ship_user_card';
    
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
     * update card count
     * 
     * @param integer $cid
     * @param integer $uid
     * @param integer $change
     * @return void
     */
    public function updateUserCardCoutByCid($cid, $uid, $change)
    {
        $now = time();
        $sql = "UPDATE $this->table_user_card SET count=count+$change,buy_time=$now WHERE uid=:uid AND sid=:sid";
        $this->_wdb->query($sql, array('sid'=>$cid, 'uid' => $uid));
    }

    /**
     * get user card count by cid
     *
     * @param integer $cid
     * @param integer $uid
     * @return integer
     */
    public function getUserCardCoutByCid($cid, $uid)
    {
        $sql = "SELECT `count` FROM $this->table_user_card WHERE sid=:sid AND uid=:uid";
        return $this->_rdb->fetchOne($sql, array('sid'=>$cid, 'uid'=>$uid));
    }
    
}
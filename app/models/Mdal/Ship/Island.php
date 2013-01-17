<?php

require_once 'Mdal/Abstract.php';

/**
 * Mdal Ship Island
 * MixiApp Ship Island Data Access Layer
 *
 * @package    Mdal/Ship
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/11/30    Liz
 */
class Mdal_Ship_Island extends Mdal_Abstract
{
    /**
     * table name
     *
     * @var string
     */
    protected $table_background = 'ship_background';
        
    protected static $_instance;

    /**
     * getDefaultInstance
     *
     * @return Mdal_Ship_Island
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * insert user new background
     * 
     * @param array $background
     * @return integer
     */
    public function insertUserIsland($background)
    {
        $this->_wdb->insert('ship_user_background', $background);
        return $this->_wdb->lastInsertId();
    }
    
    /**
     * get island by type
     *
     * @param string $type
     * @return array
     */
    public function getIslandByType($type)
    {
        $sql = "SELECT * FROM $this->table_background WHERE type=:type";
        return $this->_rdb->fetchAll($sql, array('type'=>$type));
    }
    
    /**
     * get island by user
     *
     * @param integer $uid
     * @return array
     */
    public function getIslandByUser($uid)
    {
        if ($uid < 0) {
            $sql = "SELECT * FROM ship_background AS b,ship_neighbor AS u WHERE u.background=b.id AND u.id=:uid";
            return $this->_rdb->fetchRow($sql, array('uid'=>$uid));
        }
        else {
            $sql = "SELECT * FROM ship_background AS b,ship_user AS u WHERE u.background=b.id AND u.uid=:uid";
            return $this->_rdb->fetchRow($sql, array('uid'=>$uid));
        }
    }    
}
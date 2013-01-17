<?php

require_once 'Mdal/Abstract.php';

/**
 * Mdal Ship Nopark
 * MixiApp Ship Nopark Data Access Layer
 *
 * @package    Mdal/Ship
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/11/30    Liz
 */
class Mdal_Ship_Nopark extends Mdal_Abstract
{
    /**
     * table name
     *
     * @var string
     */
    protected $table_nopark = 'ship_nopark';
        
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
     * insert no park
     *
     * @param array $info
     * @return integer
     */
    public function insertNoPark($info)
    {
        $this->_wdb->insert($this->table_nopark, $info);
        return $this->_wdb->lastInsertId();
    }
    
    /**
     * delete no park
     *
     * @param integer $uid
     * @param integer $user_ship_id
     * @return void
     */
    public function deleteNoPark($uid, $user_ship_id)
    {
        $sql = "DELETE FROM $this->table_nopark WHERE uid=:uid AND user_ship_id=:user_ship_id ";
        $this->_wdb->query($sql,array('uid'=>$uid, 'user_ship_id'=>$user_ship_id));
    }
    
    
    /**
     * get no park ship
     *
     * @param integer $uid
     * @param integer $id
     * @return array
     */
    public function getNoPark($uid, $id)
    {
        $sql = "SELECT * FROM $this->table_nopark WHERE uid=:uid AND user_ship_id=:user_ship_id ";
        return $this->_rdb->fetchRow($sql,array('uid'=>$uid, 'user_ship_id'=>$id));
    }
    
    
    
    
}
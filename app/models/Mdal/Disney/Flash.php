<?php

require_once 'Mdal/Abstract.php';

/**
 * Mdal Disney Flash
 * MixiApp Disney Flash Data Access Layer
 *
 * @package    Mdal/Disney
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/11/18    Liz
 */
class Mdal_Disney_Flash extends Mdal_Abstract
{
    /**
     * table name
     *
     * @var string
     */
    protected $table_flash_point = 'disney_flash_point';
    
    /**
     * table name
     *
     * @var string
     */
    protected $table_flash_type = 'disney_flash_type';
    
    protected static $_instance;

    /**
     * get default instance
     *
     * @return Mdal_Disney_Cup
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * insert user flash point info
     *
     * @param array $info
     * @return integer
     */
    public function insertUserFlashPoint($info)
    {
        $sql = "SELECT * FROM $this->table_flash_point WHERE uid=:uid ";
        $result =  $this->_wdb->fetchRow($sql, array('uid'=>$info['uid']));
        
        if ( $result ) {
            $where = $this->_wdb->quoteInto('uid = ?', $info['uid']);
            return $this->_wdb->update($this->table_flash_point, $info, $where);
        }
        else {
            $this->_wdb->insert($this->table_flash_point, $info);
            return $this->_wdb->lastInsertId();
        }
    }

    /**
     * update user flash point info
     *
     * @param integer $uid
     * @param array $info
     * @return integer
     */
    public function updateUserFlashPoint($uid, $info)
    {
        $where = $this->_wdb->quoteInto('uid = ?', $uid);
        return $this->_wdb->update($this->table_flash_point, $info, $where);
    }

    /**
     * delete user flash point info
     *
     * @param integer $uid
     * @return void
     */
    public function deleteUserFlashPoint($uid)
    {
        $sql = "UPDATE $this->table_flash_point SET type=0,status=0 WHERE uid=:uid ";
        $this->_wdb->query($sql, array('uid'=>$uid));
    }
    
    /**
     * get user flash point info
     *
     * @param integer $uid
     * @return array
     */
    public function getUserFlashPoint($uid)
    {
        $sql = "SELECT * FROM $this->table_flash_point WHERE uid=:uid ";
        return  $this->_wdb->fetchRow($sql, array('uid'=>$uid));
    }

    /**
     * get flash point info by type
     *
     * @param integer $id
     * @return array
     */
    public function getFlashPointInfoByType($id)
    {
        $sql = "SELECT * FROM $this->table_flash_type WHERE id=:id ";
        return  $this->_wdb->fetchRow($sql, array('id'=>$id));
    }
    
}
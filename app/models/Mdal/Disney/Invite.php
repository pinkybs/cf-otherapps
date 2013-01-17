<?php

require_once 'Mdal/Abstract.php';

/**
 * Mdal Disney Invite
 * MixiApp Disney Invite Data Access Layer
 *
 * @package    Mdal/Disney
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/10/12    Liz
 */
class Mdal_Disney_Invite extends Mdal_Abstract
{
    /**
     * table name
     *
     * @var string
     */
    protected $table_invite = 'disney_invite';
    
    protected static $_instance;

    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * insert invite info
     *
     * @param integer $uid
     * @param integer $fid
     * @return integer
     */
    public function insertInvite($uid, $fid)
    {
        $sql = "SELECT * FROM $this->table_invite WHERE uid=:uid AND fid=:fid ";
        $result = $this->_wdb->fetchRow($sql, array('uid'=>$uid, 'fid'=>$fid));
        
        if ( !$result ) {
            $invite = array('uid' => $uid, 'fid' => $fid, 'create_time'=>time());
            return $this->_wdb->insert($this->table_invite, $invite);
        }
    }
    
    /**
     * get invite user count
     *
     * @param integer $uid
     * @return integer
     */
    public function getInviteCount($uid)
    {
        $sql = "SELECT COUNT(1) FROM $this->table_invite WHERE uid=:uid ";
        return $this->_rdb->fetchOne($sql, array('uid'=>$uid));
        
    }

    /**
     * get invite user info 
     *
     * @param integer $uid
     * @return array
     */
    public function getInviteUserInfo($fid)
    {
        $sql = "SELECT * FROM $this->table_invite WHERE fid=:fid ";
        return $this->_rdb->fetchAll($sql, array('fid'=>$fid));
        
    }
    
    
    
    
}
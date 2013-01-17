<?php

require_once 'Mdal/Abstract.php';

/**
 * Mdal Ship Invite
 * MixiApp Ship Invite Data Access Layer
 *
 * @package    Mdal/Ship
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/11/30    Liz
 */
class Mdal_Ship_Invite extends Mdal_Abstract
{
    /**
     * table name
     *
     * @var string
     */
    protected $table_invite = 'ship_invite';
        
    protected static $_instance;

    /**
     * getDefaultInstance
     *
     * @return Mdal_Ship_Invite
     */
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
        $result = $this->_rdb->fetchRow($sql, array('uid'=>$uid, 'fid'=>$fid));
        
        if ( !$result ) {
            $invite = array('uid' => $uid, 'fid' => $fid, 'status' => 0);
            return $this->_wdb->insert($this->table_invite, $invite);
        }
    }

    /**
     * update invite info status
     * 
     * @param integer $uid
     * @param integer fid
     * @return void
     */
    public function updateInviteStatus($uid, $fid)
    {
        $sql = "UPDATE $this->table_invite SET status = 1 WHERE uid=:uid AND fid=:fid ";
        $this->_wdb->query($sql, array('uid'=>$uid, 'fid' => $fid));
    }
    
    /**
     * get invite info 
     *
     * @param integer $uid
     * @param integer $fid
     * @return array
     */
    public function getInviteInfo($uid, $fid)
    {
        $sql = "SELECT * FROM $this->table_invite WHERE uid=:uid AND fid=:fid ";
        return $this->_rdb->fetchRow($sql, array('uid'=>$uid, 'fid'=>$fid));
        
    }

    /**
     * get invite user count,have not gift 
     *
     * @param integer $uid
     * @return integer
     */
    public function getInviteHaveNotGiftCount($uid)
    {
        $sql = "SELECT count(1) FROM ship_invite AS i,ship_user AS u WHERE u.uid=i.fid AND i.status<>1 AND i.uid=:uid ";
        return $this->_rdb->fetchOne($sql, array('uid'=>$uid));
    }

    /**
     * update invite gift
     * 
     * @param integer $uid
     * @return void
     */
    public function updateInviteGift($uid)
    {
        $sql = "UPDATE ship_invite AS i,ship_user AS u SET i.status=1 WHERE u.uid=i.fid AND i.status<>1 AND i.uid=:uid";
        //return $this->_wdb->query($sql, array('uid'=>$uid));
        $stmt = $this->_wdb->query($sql, array('uid'=>$uid));
        return $stmt->rowCount();
    }
}
<?php

require_once 'Mdal/Abstract.php';

/**
 * Mdal Disney Cup
 * MixiApp Disney Cup Data Access Layer
 *
 * @package    Mdal/Disney
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/10/12    Liz
 */
class Mdal_Disney_Cup extends Mdal_Abstract
{
    /**
     * table name
     *
     * @var string
     */
    protected $table_cup = 'disney_cup';
    
    /**
     * table name
     *
     * @var string
     */
    protected $table_user_cup = 'disney_user_cup';
    
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
     * insert cup
     *
     * @param integer $uid
     * @param integer $cid
     * @return integer
     */
    public function insertCup($uid, $cid)
    {
        $sql = "SELECT * FROM $this->table_user_cup WHERE uid=:uid AND cid=:cid ";
        $result = $this->_wdb->fetchRow($sql, array('uid'=>$uid, 'cid'=>$cid));
        
        if ( $result ) {
            return -1;
        }
        else {
            $cup = array('uid' => $uid, 'cid' => $cid, 'create_time' => time());
            return $this->_wdb->insert($this->table_user_cup, $cup);
        }
    }
    
    /**
     * get User cup
     *
     * @param integer $uid
     * @param integer $cid
     * @return array
     */
    public function getUserCup($uid, $cid)
    {
        $sql = "SELECT * FROM $this->table_user_cup WHERE uid=:uid AND cid=:cid ";
        return  $this->_wdb->fetchRow($sql, array('uid'=>$uid, 'cid'=>$cid));
    }
    
    /**
     * get User cup
     *
     * @param integer $uid
     * @return array
     */
    public function getUserCupRand($uid)
    {
        $sql = "SELECT a.*,b.name,b.point,b.icon FROM $this->table_user_cup AS a,$this->table_cup AS b WHERE a.cid=b.cid AND a.uid=:uid AND a.status=0 ORDER BY RAND() LIMIT 1 ";
        return  $this->_wdb->fetchRow($sql, array('uid'=>$uid));
    }
    
    public function updateCupStatus($id)
    {
        $sql = "UPDATE $this->table_user_cup SET status=1 WHERE id=:id";
        
        $this->_wdb->query($sql, array('id'=>$id));
    }
    
    /**
     * get user award info
     *
     * @param integer $uid
     * @return array
     */
    public function getUserAward($uid)
    {
        $sql = "SELECT * FROM disney_user_award WHERE count>0 AND uid=:uid";
        return  $this->_wdb->fetchAll($sql, array('uid'=>$uid));
    }

    /**
     * get app user
     *
     * @param integer $uidStart
     * @param integer $uidEnd
     * @return array
     */
    public function getAppUser($uidStart, $uidEnd)
    {
        $sql = "SELECT uid FROM disney_user WHERE uid>=:uidstart AND uid<=:uidend";
        return  $this->_rdb->fetchAll($sql, array('uidstart'=>$uidStart, 'uidend'=>$uidEnd));
    }
}
<?php

require_once 'Mdal/Abstract.php';

/**
 * Mdal Ship Item
 * MixiApp Ship Item Data Access Layer
 *
 * @package    Mdal/Ship
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/11/30    Liz
 */
class Mdal_Ship_Item extends Mdal_Abstract
{
    /**
     * table name
     *
     * @var string
     */
    protected $table_item = 'ship_item';
    
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
     * delete user overdue island
     *
     * @param integer $uid
     * @return void
     */
    public function deleteUserOverdueIsland($uid)
    {
        $now = time();
        $sql = "DELETE u FROM ship_background AS b,ship_user_background AS u
                WHERE b.id=u.bg_id AND b.type<>'A' AND uid=:uid AND ($now-u.create_time)>b.usable_time ";

        $this->_wdb->query($sql, array('uid' => $uid));
    }
    
    /**
     * update user insurance card
     *
     * @param string $uid
     * @param integer $chage
     * @return void
     */
    public function updateUserInsuranceCount($uid, $change)
    {
        $sql = " UPDATE ship_user SET insurance_card=insurance_card+:change WHERE uid=:uid";
        $this->_wdb->query($sql,array('uid'=>$uid, 'change'=>$change));
    }
    
    /**
     * select user location bomb card info
     *
     * @param integer $uid
     * @param string $location
     * @return void
     */
    public function updateBombCount($uid, $location)
    {
        $sql = "UPDATE ship_user_bomb SET " . $location . " =0 WHERE uid=:uid ";

        $this->_wdb->query($sql, array('uid' => $uid));
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
     * update user bomb info
     *
     * @param integer $uid
     * @param array $bomb
     * @return array
     */
    public function updateUserBomb($uid, $bomb)
    {
        $where = $this->_wdb->quoteinto('uid = ?', $uid);
        $this->_wdb->update('ship_user_bomb', $bomb, $where);
    }
    
    /**
     * select user location bomb card info
     *
     * @param integer $uid
     * @return array
     */
    public function getBombInfo($uid)
    {
        $sql = "SELECT * FROM ship_user_bomb WHERE uid=:uid ";
        return $this->_rdb->fetchRow($sql, array('uid'=>$uid));
    }

    /**
     * get user item list 
     *
     * @param integer $uid
     * @return array
     */
    public function getUserItemList($uid)
    {
        $sql = "SELECT s.*,c.uid,c.count FROM ship_store AS s LEFT JOIN ship_user_card AS c ON c.sid=s.sid WHERE c.uid=:uid ";
        return $this->_rdb->fetchAll($sql, array('uid'=>$uid));
    }

    /**
     * get user item count by item id
     *
     * @param integer $uid
     * @param integer $sid
     * @return array
     */
    public function getUserItemCountBySid($uid, $sid)
    {
        $sql = "SELECT `count` FROM ship_user_card WHERE uid=:uid AND sid=:sid ";
        return $this->_rdb->fetchOne($sql, array('uid'=>$uid, 'sid'=>$sid));
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

    /**
     * get user island list
     * 
     * @param integer $uid
     * @return array
     */
    public function getUserIslandList($uid)
    {
        $sql = "SELECT b.*,t.ship FROM ship_background AS b,ship_background_type AS t,ship_user_background AS u
                WHERE b.type=t.type AND b.id=u.bg_id AND u.uid=:uid ORDER BY b.id ";
        return $this->_rdb->fetchAll($sql, array('uid'=>$uid));
    }
    
    /**
     * get user island info by island id
     * 
     * @param integer $uid
     * @param integer $id
     * @return array
     */
    public function getUserIslandById($uid, $id)
    {
        $sql = "SELECT b.*,t.ship FROM ship_background AS b,ship_background_type AS t,ship_user_background AS u
                WHERE b.type=t.type AND b.id=u.bg_id AND u.uid=:uid AND u.bg_id=:id ";
        return $this->_rdb->fetchRow($sql, array('uid'=>$uid, 'id'=>$id));
    }

    /**
     * get user free island 
     * 
     * @param integer $uid
     * @return array
     */
    public function getUserFreeIsland($uid)
    {
        $sql = "SELECT b.* FROM ship_user_background AS u,ship_background AS b 
                WHERE u.bg_id=b.id AND b.type='A' AND uid=:uid ";
        return $this->_rdb->fetchRow($sql, array('uid'=>$uid));
    }
    
    /**
     * get island list
     * 
     * @param integer $pageIndex
     * @param integer $pageSize
     * @return array
     */
    public function getIslandList($pageIndex, $pageSize)
    {
        $start = ($pageIndex - 1) * $pageSize;

        $sql = "SELECT b.*,t.ship FROM ship_background AS b,ship_background_type AS t 
                WHERE b.type=t.type AND b.type <> 'A' ORDER BY b.id LIMIT $start,$pageSize";
        return $this->_rdb->fetchAll($sql);
    }

    
    /**
     * get island count
     * 
     * @return integer
     */
    public function getIslandCount()
    {
        $sql = "SELECT COUNT(1) FROM ship_background WHERE type <> 'A'";
        return $this->_rdb->fetchOne($sql);
    }

    /**
     * get inland info by id
     * 
     * @param integer $id
     * @return array
     */
    public function getIslandInfo($id)
    {
        $sql = "SELECT b.*,t.ship FROM ship_background AS b,ship_background_type AS t WHERE b.type=t.type AND id=:id ";
        return $this->_rdb->fetchRow($sql, array('id'=>$id));
    }

    /**
     * get user island info
     * 
     * @param integer $uid
     * @return array
     */
    public function getUserIslandInfo($uid)
    {
        $sql = "SELECT b.id,b.price,b.type,t.ship FROM ship_background AS b,ship_user AS u,ship_background_type AS t 
                WHERE t.type=b.type AND u.background=b.id AND u.uid=:uid";
        return $this->_rdb->fetchRow($sql, array('uid'=>$uid));
    }

    /**
     * get user island info
     * 
     * @param integer $uid
     * @param integer $id
     * @return array
     */
    public function getUserIslandInfoById($uid, $id)
    {
        $sql = "SELECT * FROM ship_user_background WHERE uid=:uid AND bg_id=:bg_id ";
        return $this->_rdb->fetchRow($sql, array('uid'=>$uid, 'bg_id'=>$id));
    }
    
}
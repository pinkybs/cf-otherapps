<?php

require_once 'Mdal/Abstract.php';

/**
 * Mdal Ship User
 * MixiApp Ship User Data Access Layer
 *
 * @package    Mdal/Ship
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/11/30    Liz
 */
class Mdal_Ship_User extends Mdal_Abstract
{
    /**
     * table name
     *
     * @var string
     */
    protected $table_user = 'ship_user';
        
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
     * insert ship user
     *
     * @param array $ship
     * @param integer $uid
     * @return integer
     */
    public function insertShipUser($ship, $uid)
    {
        $this->_wdb->insert($this->table_user, $ship);
        
        $sql = "INSERT INTO ship_user_bomb (uid) VALUES (:uid)";
        $this->_wdb->query($sql,array('uid'=>$uid));
                
        $sql = "INSERT INTO ship_user_card (uid,sid) SELECT :uid,sid FROM ship_store";
        $this->_wdb->query($sql,array('uid'=>$uid));
    }
    
    /**
     * update user ship
     *
     * @param integer $uid
     * @param array $info
     * @return integer
     */
    public function updateShipUser($uid, $info)
    {
        $where = $this->_wdb->quoteInto('uid = ?', $uid);
        return $this->_wdb->update($this->table_user, $info, $where);
    }
    
    /**
     * update user ship
     *
     * @param integer $uid
     * @return void
     */
    public function updateUserShip($uid)
    {
        $sql = "UPDATE $this->table_user SET ship_count=(SELECT COUNT(1) FROM ship_user_ship WHERE uid=:uid) WHERE uid=:uid";
        $this->_wdb->query($sql, array('uid' => $uid));

        $sql = "UPDATE $this->table_user SET ship_price=(SELECT IFNULL(SUM(c.price),0) FROM ship_user_ship AS u,ship_ship AS c
                WHERE u.ship_id=c.sid AND u.uid=:uid) WHERE uid=:uid";
        $this->_wdb->query($sql, array('uid' => $uid));
    }

    /**
     * update user ship price
     * 
     * @param integer $price
     * @param integer $uid
     * @return void
     */
    public function updateShipPrice($price, $uid)
    {
        $sql = "UPDATE ship_user SET ship_price=ship_price-:price WHERE uid=:uid ";

        $this->_wdb->query($sql, array('price'=>$price, 'uid' => $uid));
    }

    /**
     * update user asset
     * 
     * @param integer $price
     * @param integer $uid
     * @return void
     */
    public function updateUserAsset($price, $uid)
    {
        $sql = "UPDATE ship_user SET asset=asset+:price WHERE uid=:uid ";
        $this->_wdb->query($sql, array('price'=>$price, 'uid' => $uid));
    }

    /**
     * update user diamond
     * 
     * @param integer $diamond
     * @param integer $uid
     * @return void
     */
    public function updateUserDiamond($diamond, $uid)
    {
        $sql = "UPDATE ship_user SET asset_diamond = asset_diamond + :diamond WHERE uid=:uid ";
        $this->_wdb->query($sql, array('diamond'=>$diamond, 'uid' => $uid));
    }

    /**
     * update user send gift info
     * 
     * @param integer $uid
     * @param integer $giftId
     * @return void
     */
    public function updateUserSendGift($uid, $giftId)
    {
        $sql = "UPDATE ship_user SET send_gift = :giftId WHERE uid=:uid ";
        $this->_wdb->query($sql, array('uid' => $uid, 'giftId'=>$giftId));
    }
    
    /**
     * update user ship info
     * 
     * @param integer $uid
     * @return void
     */
    public function updateUserShipInfo($uid)
    {
        $sql = "UPDATE ship_user SET ship_count=(SELECT COUNT(1) FROM ship_user_ship WHERE uid=:uid) WHERE uid=:uid";
        $this->_wdb->query($sql, array('uid' => $uid));

        $sql = "UPDATE ship_user SET ship_price=(SELECT IFNULL(SUM(c.price),0) FROM ship_user_ship AS u,ship_ship AS c
                WHERE u.ship_id=c.sid AND u.uid=:uid) WHERE uid=:uid";
        $this->_wdb->query($sql, array('uid' => $uid));
    }
    
    /**
     * is in app
     *
     * @param integer $uid
     * @return boolean
     */
    public function isInApp($uid)
    {
        $sql = "SELECT * FROM $this->table_user WHERE uid=:uid ";

        $result = $this->_rdb->fetchRow($sql, array('uid'=>$uid));
        
        return $result ? true : false;
    }

    /**
     * get user ship
     * 
     * @param integer $uid
     * @return array
     */
    public function getUserPark($uid)
    {
        $sql = "SELECT p.*,b.name,b.fee,b.type AS bgtype,b.cav_name AS bg_cav_name,1 AS type,
                m.location1 AS bomb1,m.location2 AS bomb2,m.location3 AS bomb3,m.location4 AS bomb4,
                m.location5 AS bomb5,m.location6 AS bomb6,m.location7 AS bomb7,m.location8 AS bomb8 
                FROM ship_user AS p,ship_background AS b,ship_user_bomb AS m 
                WHERE b.id=p.background AND m.uid=p.uid AND p.uid=:uid";

        return $this->_rdb->fetchRow($sql,array('uid'=>$uid));
    }

    /**
     * get user usable ship count
     *
     * @param integer $uid
     * @return integer
     */
    public function getUserUsableShipCount($uid)
    {
        $sql = "SELECT COUNT(1) FROM ship_user_ship WHERE activation = 1 AND uid=:uid";

        return $this->_rdb->fetchOne($sql,array('uid'=>$uid));
    }
    
    /**
     * get user neighbor park
     *
     * @param integer $id
     * @return array
     */
    public function getUserNeighborPark($id)
    {
        $sql = "SELECT  n.id AS uid,n.name AS displayName,n.sex,n.free_park,n.background,b.name,b.fee,2 AS type,
                b.type AS bgtype,4 AS locaCount,b.cav_name AS bg_cav_name,0 AS asset_diamond,0 AS asset 
                FROM ship_neighbor AS n,ship_background AS b WHERE n.background=b.id AND n.id=:id";

        return $this->_rdb->fetchRow($sql,array('id'=>$id));
    }
    
    /**
     * get neighbor park
     *
     * @param integer $uid
     * @param integer $neighbor
     * @return array
     */
    public function getNeighborPark($uid, $neighbor)
    {
        $sql = "SELECT p.*,c.cav_name,c.times,c.type AS ship_type,UNIX_TIMESTAMP(now())-p.parked_time AS parked_time
                FROM ship AS p,ship_ship AS c WHERE p.ship_id=c.sid AND p.type=2 AND p.parking_uid=:neighbor AND p.uid=:uid";

        return $this->_rdb->fetchAll($sql,array('neighbor'=>$neighbor,'uid'=>$uid));
    }

    /**
     * get user asset
     * 
     * @param integer $uid
     * @return string
     */
    public function getAsset($uid)
    {
        $sql = "SELECT asset FROM ship_user WHERE uid=:uid";
        return $this->_rdb->fetchOne($sql, array('uid' => $uid));
    }

    /**
     * get user all asset
     * 
     * @param integer $uid
     * @return string
     */
    public function getUserAllAsset($uid)
    {
        $sql = "SELECT p.asset+p.ship_price+b.price AS allAsset 
                FROM ship_user AS p,ship_background AS b WHERE p.background=b.id AND p.uid=:uid";
        return $this->_rdb->fetchOne($sql, array('uid' => $uid));
    }
    
    /**
     * get user bomb location info
     * 
     * @param integer $uid
     * @return array
     */
    public function getUserBombLocation($uid)
    {
        $sql = "SELECT c.ship,d.* FROM ship_user AS a,ship_background AS b,ship_background_type AS c,ship_user_bomb AS d
                WHERE a.uid=d.uid AND a.background=b.id AND b.type=c.type AND a.uid=:uid";
        return $this->_rdb->fetchRow($sql ,array('uid' => $uid));
    }
    
    /**
     * get user assetrank number in all
     *
     * @param integer $uid
     * @return integer
     */
    public function getUserAllAssetRankNmInAll($uid)
    {
        $sql1 = "SET @pos=0";
        $this->_rdb->query($sql1);
        
        $sql = "SELECT b.rank,a.uid,b.ass FROM ship_user AS a,
                (SELECT @pos:=@pos+1 AS rank,uid,p.asset+p.ship_price+b.price AS ass 
                FROM ship_user AS p,ship_background AS b 
                WHERE p.background=b.id ORDER BY ass DESC, p.id ASC) AS b 
                WHERE a.uid=b.uid AND a.uid=:uid";
        
        $reuslt = $this->_rdb->fetchRow($sql, array('uid' => $uid));
        
        return $reuslt['rank'];
    }

    /**
     * get user assetrank number in friends
     *
     * @param integer $uid
     * @param array $fids
     * @return integer
     */
    public function getUserAllAssetRankNmInFriends($uid, $fids)
    {
        $fids = $this->_rdb->quote($fids);
        
        $sql1 = "SET @pos=0";
        $this->_rdb->query($sql1);
        
        $sql = "SELECT b.rank,a.uid,b.ass FROM ship_user AS a,
                (SELECT @pos:=@pos+1 AS rank,uid,p.asset+p.ship_price+b.price AS ass 
                FROM ship_user AS p,ship_background AS b 
                WHERE uid IN ($fids, :uid) AND p.background=b.id ORDER BY ass DESC, p.id ASC) AS b 
                WHERE a.uid=b.uid AND a.uid=:uid";
        
        $reuslt = $this->_rdb->fetchRow($sql, array('uid' => $uid));
        
        return $reuslt['rank'];
    }

    /**
     * get user shipprice rank number in all
     *
     * @param integer $uid
     * @return integer
     */
    public function getUserShipPriceRankNmInAll($uid)
    {
        $sql1 = "SET @pos=0";
        $this->_rdb->query($sql1);
        
        $sql = "select b.rank,a.uid,a.ship_price FROM ship_user AS a,
                (SELECT @pos:=@pos+1 AS rank,uid,ship_price FROM ship_user ORDER BY ship_price DESC, id ASC) AS b 
                WHERE a.uid=b.uid AND a.uid=:uid";
        
        $reuslt = $this->_rdb->fetchRow($sql, array('uid' => $uid));
        
        return $reuslt['rank'];
    }

    /**
     * get user shipprice rank number in friends
     *
     * @param integer $uid
     * @return integer
     */
    public function getUserShipPriceRankNmInFriends($uid, $fids)
    {
        $fids = $this->_rdb->quote($fids);
        
        $sql1 = "SET @pos=0";
        $this->_rdb->query($sql1);
        
        $sql = "SELECT b.rank,a.uid,a.ship_price FROM ship_user AS a,
                (SELECT @pos:=@pos+1 AS rank,uid,ship_price FROM ship_user 
                WHERE uid IN ($fids, :uid) ORDER BY ship_price DESC, id ASC) AS b 
                WHERE a.uid=b.uid AND a.uid=:uid";
        
        $reuslt = $this->_rdb->fetchRow($sql, array('uid' => $uid));
        
        return $reuslt['rank'];
    }

    /**
     * get app friendids array
     *
     * @param array $friendIds
     * @return array
     */
    public function getAppFriendsArray($friendIds)
    {
        $friendIds = $this->_wdb->quote($friendIds);
        
        $sql = "SELECT uid FROM $this->table_user WHERE uid IN($friendIds) ";
        
        return $this->_rdb->fetchAll($sql);
    }
    
    /**
     * get app friendids
     *
     * @param array $friendIds
     * @return array
     */
    public function getAppFriends($friendIds)
    {
        $friendIds = $this->_rdb->quote($friendIds);
        
        $sql = "SELECT uid FROM $this->table_user WHERE uid IN($friendIds) ";
        
        $result = $this->_rdb->fetchAll($sql);
        
        $fids = array();
        if ($result) {
            foreach ($result as $row) {
                $fids[] = $row['uid'];
            }
        }

        return $fids;
    }

}
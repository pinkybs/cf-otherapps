<?php

require_once 'Mdal/Abstract.php';

/**
 * Mdal Ship Ship
 * MixiApp Ship Ship Data Access Layer
 *
 * @package    Mdal/Ship
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/11/30    Liz
 */
class Mdal_Ship_Ship extends Mdal_Abstract
{
    /**
     * table name
     *
     * @var string
     */
    protected $table_ship = 'ship_ship';
        
    protected static $_instance;

    /**
     * getDefaultInstance
     *
     * @return Mdal_Ship_Ship
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * insert user new ship
     * 
     * @param array $ship
     * @return integer
     */
    public function insertUserShip($ship)
    {
        $this->_wdb->insert('ship_user_ship', $ship);
        return $this->_wdb->lastInsertId();
    }

    /**
     * delete user ship
     *
     * @param integer $sid
     */
    public function deleteUserShip($sid)
    {
        $sql = "DELETE FROM ship WHERE sid=:sid";
        $this->_wdb->query($sql, array('sid'=>$sid));
    }
    
    /**
     * insert ship parking info
     *
     * @param array $info
     * @return integer
     */
    public function insertParkingInfo($info)
    {
        $this->_wdb->insert('ship', $info);
        return $this->_wdb->lastInsertId();
    }

    /**
     * update user ship info
     *
     * @param integer $uid
     * @param array $info
     * @return integer
     */
    public function updateUserShip($uid, $info)
    {
        $where = $this->_wdb->quoteInto('uid = ?', $uid);
        return $this->_wdb->update('ship_user_ship', $info, $where);
    }

    /**
     * update user ship info by user ship id
     *
     * @param integer $userShipId
     * @param array $info
     * @return integer
     */
    public function updateUserShipByUserShipId($userShipId, $info)
    {
        $where = $this->_wdb->quoteInto('id = ?', $userShipId);
        return $this->_wdb->update('ship_user_ship', $info, $where);
    }
    
    /**
     * update user ship status
     *
     * @param string $uid
     * @param integer $user_ship_id
     * @param integer $status
     */
    public function updateUserShipStatus($uid, $user_ship_id, $status)
    {
        $sql = "UPDATE ship_user_ship SET status=:status WHERE uid=:uid AND id=:user_ship_id ";
        $this->_wdb->query($sql,array('status'=>$status,'uid'=>$uid,'user_ship_id'=>$user_ship_id));
    }

    /**
     * update user ship count
     *
     * @param integer $uid
     * @return void
     */
    public function updateUserShipCount($uid)
    {
        $sql = "UPDATE ship_user SET ship_count=(SELECT COUNT(1) FROM ship_user_ship WHERE uid=:uid) WHERE uid=:uid";
        $this->_wdb->query($sql, array('uid' => $uid));

        $sql = "UPDATE ship_user SET ship_price=(SELECT IFNULL(SUM(c.price),0) FROM ship_user_ship AS u,ship_ship AS c
                WHERE u.ship_id=c.sid AND u.uid=:uid AND u.status = 1) WHERE uid=:uid";
        $this->_wdb->query($sql, array('uid' => $uid));
    }
    
    /**
     * update user ship parking info
     * 
     * @param integer uid
     * @param integer $oldUserShipId
     * @param integer $newUserShipId
     * @param integer $newShipId
     * @return void
     */
    public function updateParkingInfo($uid, $oldUserShipId, $newUserShipId, $newShipId)
    {
        $sql = "UPDATE ship SET ship_id=:newShipId,user_ship_id=:newUserShipId WHERE uid=:uid AND user_ship_id=:oldUserShipId ";
        $this->_wdb->query($sql, array('uid'=>$uid, 'oldUserShipId'=>$oldUserShipId, 'newUserShipId'=>$newUserShipId, 'newShipId'=>$newShipId));
    }

    /**
     * update ship no park info
     * 
     * @param integer uid
     * @param integer $oldUserShipId
     * @param integer $newUserShipId
     * @param integer $newShipId
     * @return void
     */
    public function updateNoParkInfo($uid, $oldUserShipId, $newUserShipId, $newShipId)
    {
        $sql = "UPDATE ship_nopark SET ship_id=:newShipId,user_ship_id=:newUserShipId WHERE uid=:uid AND user_ship_id=:oldUserShipId ";
        $this->_wdb->query($sql, array('uid'=>$uid, 'oldUserShipId'=>$oldUserShipId, 'newUserShipId'=>$newUserShipId, 'newShipId'=>$newShipId));
    }
    
    /**
     * delete ship parking info
     *
     * @param integer $sid
     * @return void
     */
    public function deleteParkingInfo($sid)
    {
        $sql = "DELETE FROM ship WHERE sid=:sid";
        $this->_wdb->query($sql, array('sid'=>$sid));
    }

    /**
     * delete user ship
     * 
     * @param integer $uid
     * @param integer $user_ship_id
     * @return void
     */
    public function deleteUserShips($uid, $user_ship_id)
    {
        $sql = "DELETE FROM ship_user_ship WHERE uid=:uid AND id=:user_ship_id ";
        $this->_wdb->query($sql,array('uid'=>$uid, 'user_ship_id'=>$user_ship_id));
    }
    
    /**
     * get ship info by id
     *
     * @param integer $sid
     * @return array
     */
    public function getShipInfo($sid)
    {
        $sql = "SELECT * FROM ship_ship WHERE sid=:sid ";
        return $this->_rdb->fetchRow($sql,array('sid'=>$sid));
    }
    
    public function getShipDetailInfo($uid, $pid)
    {
        if ($pid < 0) {
            $sql = 'SELECT p.uid,location,p.ship_id,p.parking_uid,p.parked_time,p.type,c.cav_name,c.times,
                    IFNULL(s.ship_name,c.name) as name,IFNULL(s.captain_uid,p.parking_uid) AS cuid 
                    FROM ship AS p,ship_ship AS c,ship_user_ship as s WHERE s.id=p.user_ship_id AND c.sid=p.ship_id 
                    AND p.uid=:uid AND p.parking_uid=:pid';

            return $this->_rdb->fetchAll($sql, array('uid' => $uid, 'pid' => $pid));
        }
        else {
            $sql = 'SELECT p.uid,location,p.ship_id,p.parking_uid,p.parked_time,p.type,c.cav_name,c.times,
                    IFNULL(s.ship_name,c.name) as name,IFNULL(s.captain_uid,p.parking_uid) AS cuid 
                    FROM ship AS p,ship_ship AS c,ship_user_ship as s WHERE s.id=p.user_ship_id AND c.sid=p.ship_id AND p.parking_uid=:pid';

            return $this->_rdb->fetchAll($sql, array('pid' => $pid));
        } 
    }

    public function getFlashShipImage($ship_id)
    {
        $sql = "SELECT * FROM ship_mobile_flash WHERE sid=:sid";

        return $this->_rdb->fetchRow($sql, array('sid' => $ship_id));
    }
    
    /**
     * get ship info by user ship id
     *
     * @param integer $userShipId
     * @return array
     */
    public function getShipByUserShipId($userShipId)
    {
        $sql = "SELECT u.id,u.uid,u.ship_id,IFNULL(u.ship_name,s.name) AS shipName,u.captain_uid,u.status,s.cav_name,s.name,s.price,s.times
                FROM ship_user_ship AS u,ship_ship AS s 
                WHERE u.ship_id=s.sid AND u.id=:userShipId ";
        return $this->_rdb->fetchRow($sql,array('userShipId'=>$userShipId));
    }

    /**
     * get ship parking info by user ship id
     *
     * @param integer $userShipId
     * @return array
     */
    public function getShipParkingInfoByUserShipId($userShipId)
    {
        $sql = "SELECT * FROM ( SELECT u.id,u.uid,u.ship_id,IFNULL(u.ship_name,s.name) AS shipName,u.captain_uid,u.status,u.activation,u.status AS shipStatus,s.cav_name,s.name,
                s.price,s.times FROM ship_user_ship AS u,ship_ship AS s WHERE u.ship_id=s.sid AND u.id=:userShipId) AS a
                LEFT JOIN ship AS p ON p.user_ship_id = a.id ";
        return $this->_rdb->fetchRow($sql,array('userShipId'=>$userShipId));
    }
    
    /**
     * get friend park
     *
     * @param integer $uid
     * @return array
     */
    public function getFriendPark($uid)
    {
        $sql = "SELECT p.*,IFNULL(us.ship_name, c.name) AS shipName,c.name,c.cav_name,c.times,c.type AS ship_type,
                UNIX_TIMESTAMP(now())-p.parked_time AS parked_time,IFNULL(us.captain_uid,us.uid) AS captain_uid 
                FROM ship AS p,ship_ship AS c,ship_user_ship AS us 
                WHERE us.id=p.user_ship_id AND p.ship_id=c.sid AND p.type=1 AND p.parking_uid=:uid";

        return $this->_rdb->fetchAll($sql,array('uid'=>$uid));
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
        $sql = "SELECT p.*,IFNULL(us.ship_name, c.name) AS shipName,c.name,c.cav_name,c.times,c.type AS ship_type,
                UNIX_TIMESTAMP(now())-p.parked_time AS parked_time,IFNULL(us.captain_uid,us.uid) AS captain_uid 
                FROM ship AS p,ship_ship AS c,ship_user_ship AS us 
                WHERE p.ship_id=c.sid AND p.type=2 AND p.parking_uid=:neighbor AND p.uid=:uid AND us.id=p.user_ship_id";

        return $this->_rdb->fetchAll($sql,array('neighbor'=>$neighbor,'uid'=>$uid));
    }

    /**
     * get user ships
     * 
     * @param integer $uid
     * @return array
     */
    public function getUserShips($uid)
    {
        $sql = "SELECT a.*,p.sid,p.parking_uid,p.parked_time,p.type,p.location FROM
                (SELECT u.*,IFNULL(u.ship_name,c.name) AS shipName,c.name,c.price,c.cav_name,c.times,c.type AS ship_type,u.status AS shipStatus 
                FROM ship_user_ship AS u,ship_ship AS c WHERE u.ship_id=c.sid AND u.uid=:uid) AS a
                LEFT JOIN ship AS p ON a.uid=p.uid AND a.ship_id=p.ship_id AND a.id=p.user_ship_id ";
        return $this->_rdb->fetchAll($sql,array('uid'=>$uid));
    }
    
    /**
     * get ship info by user ship id
     *
     * @param integer $uid
     * @param integer $id
     * @return array
     */
    public function getUserShipById($uid, $id)
    {
        $sql = "SELECT * FROM ship_user_ship WHERE uid=:uid AND id=:id ";

        return $this->_rdb->fetchRow($sql, array('uid'=>$uid, 'id'=>$id));
    }

    /**
     * get park info
     *
     * @param integer $uid
     * @param integer $user_ship_id
     * @return array
     */
    public function getParkInfo($uid, $user_ship_id)
    {
        $sql = "SELECT type FROM ship WHERE uid=:uid AND user_ship_id=:user_ship_id ";
        $type = $this->_rdb->fetchOne($sql,array('uid'=>$uid, 'user_ship_id'=>$user_ship_id));

        if ($type == 1) {
            $sql = "SELECT p.sid,p.parked_time,b.fee,p.parking_uid,p.type,p.location,u.free_park FROM ship AS p,ship_user AS u,ship_background AS b WHERE b.id=u.background
                    AND p.parking_uid=u.uid AND p.uid=:uid AND p.user_ship_id=:user_ship_id AND p.type=1 ";
        }
        else {
            $sql = "SELECT p.sid,p.parked_time,b.fee,p.parking_uid,p.type,p.location,n.free_park FROM ship AS p,ship_neighbor AS n,ship_background AS b WHERE b.id=n.background
                    AND p.parking_uid=n.id AND p.uid=:uid AND p.user_ship_id=:user_ship_id AND p.type=2 ";
        }

        return $this->_rdb->fetchRow($sql,array('uid'=>$uid, 'user_ship_id'=>$user_ship_id));
    }

    /**
     * get user park info by location
     *
     * @param integer $uid
     * @param integer $location
     * @return array
     */
    public function getUserParkInfoByLocation($uid, $location)
    {
        $sql = "SELECT * FROM ship WHERE parking_uid=:uid AND location=:location AND type=1";

        return $this->_rdb->fetchRow($sql, array('uid'=>$uid, 'location'=>$location));
    }
        
    /**
     * check the user park location is empty
     *
     * @param integer $uid
     * @param integer $parking_uid
     * @param integer $location
     * @param integer $type
     * @return boolean
     */
    public function isEmptyLocation($uid, $parking_uid, $location, $type)
    {
        if ($type == 1) {
            $sql = "SELECT COUNT(1) FROM ship WHERE parking_uid=:parking_uid AND location=:location AND type=1";
            $result = $this->_rdb->fetchOne($sql,array('parking_uid'=>$parking_uid, 'location'=>$location));
        }
        else {
            $sql = "SELECT COUNT(1) FROM ship WHERE uid=:uid AND parking_uid=:parking_uid AND location=:location AND type=2";
            $result = $this->_rdb->fetchOne($sql,array('parking_uid'=>$parking_uid, 'uid'=>$uid, 'location'=>$location));
        }

        return $result==0;
    }

    /**
     * get send car friends info
     * 
     * @param integer $ship_id
     * @param array $fids
     * @return array
     */
    public function getSendShipFriendsInfo($ship_id, $fids)
    {
        $fids = $this->_rdb->quote($fids);
        
        $sql = "SELECT u.id,u.uid,u.ship_count,u.receive_ship_time,c.count1,c.ship_id FROM ship_user AS u,
                (SELECT uid,count(1) AS count1,ship_id FROM ship_user_ship WHERE uid IN ($fids) AND ship_id=:ship_id GROUP BY uid) AS c
                WHERE u.uid=c.uid";
        return $this->_rdb->fetchAll($sql, array('ship_id' => $ship_id));
    }

    /**
     * get user ship count by ship id
     * 
     * @param integer $uid
     * @param integer $sid
     * @return array
     */
    public function getUserShipCountBySid($uid, $sid)
    {        
        $sql = "SELECT count(1) FROM ship_user_ship WHERE uid=:uid AND ship_id=:sid ";
        return $this->_rdb->fetchOne($sql, array('uid' => $uid, 'sid' => $sid));
    }
    
    /**
     * get ship list info by user id
     * 
     * @param integer $uid
     * @param integer $pageIndex
     * @param integer $pageSize
     * @return array
     */
    public function getShipListByUid($uid, $pageIndex, $pageSize)
    {        
        $start = ($pageIndex - 1) * $pageSize;
        
        $sql = "SELECT * FROM ship_ship AS s
                LEFT JOIN (SELECT uid,count(1) AS ship_count,ship_id FROM ship_user_ship WHERE uid=:uid GROUP BY ship_id ) AS u 
                ON u.ship_id=s.sid WHERE s.type=1 ORDER BY s.price LIMIT $start,$pageSize ";
        return $this->_rdb->fetchAll($sql, array('uid' => $uid));
    }

    /**
     * get ship count
     * 
     * @return integer
     */
    public function getShipCount()
    {        
        $sql = "SELECT COUNT(1) FROM ship_ship WHERE type=1 ";
        return $this->_rdb->fetchOne($sql);
    }
    
    /**
     * get user ship max price
     * 
     * @param integer $uid
     * @return integer
     */
    public function getUserShipMaxPrice($uid)
    {
        $sql = "SELECT MAX(s.price) AS maxprice FROM ship_ship AS s,ship_user_ship AS u 
                WHERE s.sid=u.ship_id AND u.status=1 AND u.uid=:uid ";
        return $this->_rdb->fetchOne($sql, array('uid'=>$uid));
    }

    /**
     * get user ship list info
     * 
     * @param integer $uid
     * @return array
     */
    public function getUserShipList($uid)
    {
        $sql = "SELECT c.price,c.name,c.cav_name,IFNULL(u.ship_name,c.name) AS shipName,u.* FROM ship_ship AS c,ship_user_ship AS u
                WHERE c.sid=u.ship_id AND u.status=1 AND c.type=1 AND u.uid=:uid ORDER BY price ";
        return $this->_rdb->fetchAll($sql, array('uid'=>$uid));
    }

    /**
     * get user parked location
     *
     * @param string $uid
     * @return array
     */
    public function getPakingLocation($uid) 
    {
        $sql = "SELECT location FROM ship WHERE parking_uid=:uid AND type=1 ORDER BY location";
        
        return $this->_rdb->fetchAll($sql, array('uid' => $uid));
    }
    
    /**
     * get ship sid
     *
     * @param integer $uid
     * @return string
     */
    public function getShipSidByParkingUid($uid)
    {
        $sql = "SELECT sid FROM ship WHERE parking_uid=:uid";
        $result = $this->_rdb->fetchAll($sql, array('uid'=>$uid));
        return $this->_rdb->quote($result);
    }
    
    public function getShipSidByUid($uid)
    {
        $sql = "SELECT sid FROM ship WHERE uid=:uid";
        $result = $this->_rdb->fetchAll($sql, array('uid'=>$uid));
        return $this->_rdb->quote($result);
    }
    
    /**
     * get park ship fee
     *
     * @param integer $uid
     * @return array
     */
    public function getParkShipFee($uid)
    {
        $sql = "SELECT s.sid,s.ship_id,s.parking_uid,s.parked_time,ss.times,IFNULL(us.ship_name, ss.name) AS shipName,b.fee
                FROM ship AS s,ship_user AS u,ship_background AS b,ship_ship AS ss,ship_user_ship AS us 
                WHERE us.id=s.user_ship_id AND s.ship_id=ss.sid AND u.background=b.id AND s.uid=u.uid AND parking_uid=:uid";
        return $this->_rdb->fetchAll($sql, array('uid'=>$uid));
    }
    
    public function getParkingBySid($sids)
    {
        $sql = "SELECT s.uid,s.sid,s.ship_id,ss.times,IFNULL(us.ship_name, ss.name) AS shipName,s.parking_uid,s.parked_time,b.fee
                FROM ship AS s,ship_user AS u,ship_background AS b,ship_ship AS ss,ship_user_ship AS us 
                WHERE us.id=s.user_ship_id AND ss.sid=s.ship_id AND u.background=b.id AND s.parking_uid=u.uid AND s.sid IN ($sids)";
        
        return $this->_rdb->fetchAll($sql, array('uid'=>$uid));
    }
}
<?php

require_once 'Mdal/Abstract.php';

/**
 * Mdal Ship Neighbor
 * MixiApp Ship Neighbor Data Access Layer
 *
 * @package    Mdal/Ship
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/11/30    Liz
 */
class Mdal_Ship_Neighbor extends Mdal_Abstract
{
    /**
     * table name
     *
     * @var string
     */
    protected $table_neighbor = 'ship_neighbor';
        
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
     * get neighbor name
     *
     * @param integer $id
     * @return string
     */
    public function getNeighborName($id) 
    {
        $sql = "SELECT name FROM ship_neighbor WHERE id=:id";
        return $this->_rdb->fetchOne($sql,array('id'=>$id));
    }
    
    /**
     * get neighbor info
     *
     * @param integer $uid
     * @param integer $left
     * @param integer $right
     * @return array
     */
    public function getNeighbor($uid, $left, $right, $center)
    {
        $sql = "SELECT n.id,n.id AS uid,n.name AS nickname,IFNULL(a.remain_park,4) AS remain_park,n.free_park,background,b.name,b.fee,2 AS type
                FROM ship_neighbor AS n LEFT JOIN
                (SELECT parking_uid,4-COUNT(1) AS remain_park FROM ship WHERE uid=:uid AND parking_uid=:left AND type=2 GROUP BY parking_uid)
                AS a ON n.id=a.parking_uid
                INNER JOIN ship_background AS b ON b.id=n.background WHERE n.id =:left
                UNION
                SELECT n.id,n.id AS uid,n.name AS nickname,IFNULL(b.remain_park,4) AS remain_park,n.free_park,background,b.name,b.fee,2 AS type
                FROM ship_neighbor AS n LEFT JOIN
                (SELECT parking_uid,4-COUNT(1) AS remain_park FROM ship WHERE uid=:uid AND parking_uid=:right AND type=2 GROUP BY parking_uid)
                AS b ON n.id=b.parking_uid
                INNER JOIN ship_background AS b ON b.id=n.background WHERE n.id =:right
                UNION
                SELECT n.id,n.id AS uid,n.name AS nickname,IFNULL(b.remain_park,4) AS remain_park,n.free_park,background,b.name,b.fee,2 AS type
                FROM ship_neighbor AS n LEFT JOIN
                (SELECT parking_uid,4-COUNT(1) AS remain_park FROM ship WHERE uid=:uid AND parking_uid=:center AND type=2 GROUP BY parking_uid)
                AS b ON n.id=b.parking_uid
                INNER JOIN ship_background AS b ON b.id=n.background WHERE n.id =:center";

        return $this->_rdb->fetchAll($sql,array('uid' => $uid, 'left' => $left, 'right' => $right, 'center' => $center));
    }
    
}
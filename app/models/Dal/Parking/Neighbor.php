<?php

require_once 'Dal/Abstract.php';

class Dal_Parking_Neighbor extends Dal_Abstract
{
    /**
     * user table name
     *
     * @var string
     */
    protected $table_neighbor = 'parking_neighbor';
    
    protected static $_instance;
    
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        
        return self::$_instance;
    }

    /**
     * get user neighbor park
     *
     * @param integer $id
     * @return array
     */
    public function getUserNeighborPark($id)
    {
        $sql = "SELECT n.id AS uid,n.name AS nickname,n.free_park,n.background,b.name,b.fee,2 AS type,b.cav_name AS bg_cav_name
                FROM parking_neighbor AS n,parking_background AS b WHERE n.background=b.id AND n.id=:id";

        return $this->_rdb->fetchRow($sql,array('id'=>$id));
    }

    /**
     * get neighbor name
     *
     * @param integer $id
     * @return string
     */
    public function getNeighborName($id) 
    {
        $sql = "SELECT name FROM parking_neighbor WHERE id=:id";
        return $this->_rdb->fetchOne($sql,array('id'=>$id));
    }

    /**
     * get neighbor
     *
     * @param integer $uid
     * @param integer $left
     * @param integer $right
     * @return array
     */
    public function getNeighbor($uid,$left,$right)
    {
        $sql = "SELECT n.id,n.id AS uid,n.name AS nickname,IFNULL(a.remain_park,4) AS remain_park,n.free_park,background,b.name,b.fee,2 AS type
                FROM parking_neighbor AS n LEFT JOIN
                (SELECT parking_uid,4-COUNT(1) AS remain_park FROM parking WHERE uid=:uid AND parking_uid=:left AND type=2 GROUP BY parking_uid)
                AS a ON n.id=a.parking_uid
                INNER JOIN parking_background AS b ON b.id=n.background WHERE n.id =:left
                UNION
                SELECT n.id,n.id AS uid,n.name AS nickname,IFNULL(b.remain_park,4) AS remain_park,n.free_park,background,b.name,b.fee,2 AS type
                FROM parking_neighbor AS n LEFT JOIN
                (SELECT parking_uid,4-COUNT(1) AS remain_park FROM parking WHERE uid=:uid AND parking_uid=:right AND type=2 GROUP BY parking_uid)
                AS b ON n.id=b.parking_uid
                INNER JOIN parking_background AS b ON b.id=n.background WHERE n.id =:right";

        return $this->_rdb->fetchAll($sql,array('uid'=>$uid,'left'=>$left,'right'=>$right));
    }
    
    
    
    
    
    
}
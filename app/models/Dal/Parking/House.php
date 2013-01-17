<?php

require_once 'Dal/Abstract.php';

class Dal_Parking_House extends Dal_Abstract
{
    /**
     * user table name
     *
     * @var string
     */
    protected $table_store = 'parking_background';

    protected static $_instance;

    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * get house count
     * @author lp
     * @return array
     */
    public function getHouseCount(){
        $sql = "SELECT COUNT(id) FROM parking_background WHERE type <> 'A'";
        return $this->_rdb->fetchOne($sql);
    }
    /**
     * get old house price
     * @author lp
     * @return array
     */
    public function getOldHousePrice($uid){
        $sql = "SELECT pb.price FROM parking_background AS pb,parking_user AS pu WHERE pu.uid=:uid AND pu.background=pb.id";
        return $this->_rdb->fetchOne($sql,array('uid'=>$uid));
    }
    /**
     * get old house type
     * @author lp
     * @return array
     */
    public function getOldHouseType($uid){
        $sql = "SELECT pb.type FROM parking_background AS pb,parking_user AS pu WHERE pu.uid=:uid AND pu.background=pb.id";
        return $this->_rdb->fetchOne($sql,array('uid'=>$uid));
    }
    /**
     * get house array
     * @author lp
     * @param integer $pageIndex
     * @param integer $pageSize
     * @return array
     */
    public function getHouseList($pageIndex=1,$pageSize=8)
    {
        $start = ($pageIndex - 1) * $pageSize;

        $sql = "SELECT * FROM parking_background WHERE type <> 'A' ORDER BY price LIMIT $start,$pageSize";

        return $this->_rdb->fetchAll($sql);
    }
    /**
     * get house info by card id
     * @author lp
     * @param integer $hid
     * @return array
     */
    public function getHouseInfo($hid)
    {
        $sql = "SELECT * FROM parking_background WHERE id=:id";

        return $this->_rdb->fetchRow($sql,array('id'=>$hid));
    }
    /**
     * get user park
     * @author lp
     * @param integer $uid
     * @return array
     */
    public function getUserPark($uid)
    {
        $sql = "SELECT p.*,b.name,b.fee,b.type FROM parking_user AS p,parking_background AS b
                WHERE  b.id=p.background AND p.uid=:uid";

        return $this->_rdb->fetchRow($sql,array('uid'=>$uid));
    }
    /**
     * update user parking info
     * @author lp
     * @param integer $uid
     * @param array $info
     * @return void
     */
    public function updateUserParking($uid, $info)
    {
        $where = $this->_wdb->quoteInto('uid = ?', $uid);

        return $this->_wdb->update('parking_user', $info, $where);
    }
    /**
     * update user asset
     * @author lp
     * @param integer $price
     * @param integer $uid
     * @return void
     */
    public function updateUserAsset($price, $uid)
    {
        $sql = "UPDATE parking_user SET asset=asset-:price WHERE uid=:uid ";
        $this->_wdb->query($sql, array('price'=>$price, 'uid' => $uid));
    }
    /**
     * insert minifeed
     * @author lp
     * @param array $info
     * @return integer
     */
    public function insertMinifeed($info)
    {
        $this->_wdb->insert('parking_minifeed', $info);
        return $this->_wdb->lastInsertId();
    }
    /**
     * insert newsfeed
     * @author lp
     * @param array $info
     * @return integer
     */
    public function insertNewsfeed($info)
    {
        $this->_wdb->insert('parking_newsfeed', $info);
        return $this->_wdb->lastInsertId();
    }

    /**
     * get house by type
     *
     * @param string $type
     * @return array
     */
	public function getHouseByType($type)
	{
		$sql = "SELECT * FROM parking_background WHERE type=:type";
		return $this->_rdb->fetchAll($sql, array('type'=>$type));
	}
    /**
     * get old house infomation
     * @author lp
     * @return array
     */
    public function getOldHouseInfo($uid){
        $sql = "SELECT pb.* FROM parking_background AS pb,parking_user AS pu WHERE pu.uid=:uid AND pu.background=pb.id";
        return $this->_rdb->fetchRow($sql,array('uid'=>$uid));
    }
}
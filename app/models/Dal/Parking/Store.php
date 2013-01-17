<?php

require_once 'Dal/Abstract.php';

class Dal_Parking_Store extends Dal_Abstract
{
    /**
     * user table name
     *
     * @var string
     */
    protected $table_store = 'parking_store';

    protected static $_instance;

    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * get card info by card id
     * @author lp
     * @param integer $sid
     * @return array
     */
    public function getCardInfo($sid)
    {
        $sql = "SELECT * FROM $this->table_store WHERE sid=:sid";
        return $this->_rdb->fetchRow($sql,array('sid'=>$sid));
    }
    /**
     * get user card count where card id = cid
     * @author lp
     * @param integer $cid
     * @param integer $uid
     * @return integer
     */
    public function getUserCardCoutByCid($cid, $uid)
    {
        $sql = "SELECT `count` FROM parking_user_card WHERE sid=:sid AND uid=:uid";
        return $this->_rdb->fetchOne($sql,array('sid'=>$cid, 'uid'=>$uid));
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
     * update card count
     * @author lp
     * @param integer $cid
     * @param integer $uid
     * @return void
     */
    public function updateUserCardCoutByCid($cid, $uid, $change)
    {
        $sql = "UPDATE parking_user_card SET count=count+$change,buy_time=now() WHERE uid=:uid AND sid=:sid";
        $this->_wdb->query($sql, array('sid'=>$cid, 'uid' => $uid));
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
     * get items count
     * @author lp
     * @return array
     */
    public function getItemCount(){
        $sql = "SELECT COUNT(sid) FROM parking_store";
        return $this->_rdb->fetchOne($sql);
    }
    /**
     * get items array
     * @author lp
     * @param integer $pageIndex
     * @param integer $pageSize
     * @return array
     */
    public function getItemsList($pageIndex=1,$pageSize=8)
    {
        $start = ($pageIndex - 1) * $pageSize;

        $sql = "SELECT * FROM parking_store ORDER BY price LIMIT $start,$pageSize";

        return $this->_rdb->fetchAll($sql);
    }
    /**
     * get user asset
     * @author lp
     */
    public function getAsset($uid){
        $sql="SELECT asset FROM parking_user WHERE uid=:uid";
        return $this->_rdb->fetchRow($sql,array('uid'=>$uid));
    }
    /**
     * get user free park
     * @author lp
     * @return array
     */
    public function getFreePark($uid){
        $sql="SELECT free_park FROM parking_user WHERE uid=:uid";
        return $this->_rdb->fetchRow($sql,array('uid'=>$uid));
    }
    /**
     * get user yanki item infomation
     * @author lp
     * @return array
     */
    public function getUserYanKiItemInfo($uid){
        $sql="SELECT * FROM parking_user_yanki WHERE uid=:uid";
        return $this->_rdb->fetchRow($sql,array('uid'=>$uid));
    }
    /**
     * get user all items
     * @author lp
     * @param integer $uid
     * @return array
     */
    public function getUserAllItems($uid){
        $sql="SELECT IFNULL(pc.pc_uid,:uid) AS uid,IFNULL(pc.count,0) AS count,
                     ps.sid,ps.name,ps.introduce,
                     pu.last_bribery_time,pu.last_evasion_time,pu.last_check_time,pu.insurance_card
              FROM parking_user AS pu
              INNER JOIN
              (SELECT uid AS pc_uid,sid,count FROM parking_user_card WHERE uid=:uid) AS pc
              ON pu.uid=pc.pc_uid
              RIGHT JOIN
              (SELECT * FROM parking_store) AS ps ON pc.sid=ps.sid";
        return $this->_rdb->fetchAll($sql,array("uid"=>$uid));
    }


}
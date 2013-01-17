<?php

require_once 'Dal/Abstract.php';

/**
 * Dal Hotel Item
 * MixiApp hotel Item Data Access Layer
 *
 * @package    Dal/Hotel
 * @copyright  Copyright (c) 2009 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/09/16    Zhaoxh
 */
class Dal_Hotel_Item extends Dal_Abstract
{
    /**
     * Item table name
     *
     * @var string
     */
    protected $table_user = 'hotel_user_item';

    protected static $_instance;

    /**
     * get Dal_Hotel_Item default
     *
     * @return Dal_Hotel_Item
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * get One Page Of Data of hotel_user_item table by uid
     *
     * @param string $uid
     * @param string $page
     * @param string $size
     * @return array
     */
    public function getItemList($uid,$page,$size=12)
    {
        $start = ($page - 1) * $size ;

        $sql = "SELECT a.sid,a.number,b.name,b.introduce FROM hotel_user_item AS a INNER JOIN
         hotel_store AS b ON a.sid = b.sid  WHERE uid=:uid ORDER BY sid LIMIT $start,$size";

        $result = $this->_rdb->fetchAll($sql,array('uid' => $uid));

        return $result;
    }

    /**
     * get Item Price by sid
     *
     * @param string $sid
     * @return string
     */
    public function getItemPrice($sid)
    {
        $sql = "SELECT price FROM hotel_store WHERE sid=:sid";

        $result = $this->_rdb->fetchOne($sql,array('sid' => $sid));

        return $result;
    }


    /**
     * get Item number
     *
     * @param string $uid
     * @param string $sid
     * @return string
     */
    public function getItemNum($uid,$sid)
    {
        $sql = "SELECT number FROM $this->table_user WHERE uid=:uid AND sid=:sid";

        $result = $this->_rdb->fetchOne($sql,array('uid' => $uid,'sid' => $sid));

        return $result == null ? 0 : $result;
    }

    /**
     * insert a item data
     *
     * @param array $itemInfo
     */
    public function insertItem($itemInfo)
    {
         $this->_wdb->insert($this->table_user, $itemInfo);
    }

    /**
     * add item numbers
     *
     * @param string $uid
     * @param string $sid
     * @param number $addNum
     * @return Pdo Object
     */
    public function addItem($uid,$sid,$addNum=1)
    {
        $sql = "UPDATE $this->table_user SET number=number+$addNum WHERE uid=:uid AND sid=:sid";

        $result = $this->_wdb->query($sql,array('uid' => $uid,'sid' => $sid));

        return $result;
    }

    /**
     * delete item info
     *
     * @param string $uid
     * @param string $sid
     * @return Pdo Obj
     */
    public function delItem($uid,$sid)
    {
        $sql = "DELETE FROM $this->table_user WHERE uid=:uid AND sid=:sid";

        $result = $this->_wdb->query($sql,array('uid' => $uid,'sid' => $sid));

        return $result;
    }

    /**
     * insert a item time
     *
     * @param array $itemTimeInfo
     */
    public function insertItemTime($itemTimeInfo)
    {
         $this->_wdb->insert('hotel_item_time', $itemTimeInfo);
    }

    /**
     * show item list in shop
     *
     * @param string $type
     * @param string $page
     * @param number/string $size
     * @return array
     */
    public function shopShow($type,$page=1,$size=4)
    {
        $start = ($page - 1) * $size ;

        $sql = "SELECT sid,name,price,introduce,image_url FROM hotel_store WHERE type=:type LIMIT $start,$size";

        $result = $this->_rdb->fetchAll($sql,array('type'=>$type));

        return $result;
    }

    /**
     * count  items in hotel_store
     *
     * @return string
     */
    public function cntStore()
    {
        $sql = "SELECT COUNT(1) FROM hotel_store";

        $result = $this->_rdb->fetchOne($sql);

        return $result;
    }
    
    /**
     * get item info
     *
     * @param string $sid
     * @return  array
     */
    public function getItemInfo($sid) 
    {
        $sql = "SELECT * FROM hotel_store WHERE sid=:sid";

        $result = $this->_rdb->fetchRow($sql,array('sid' => $sid));

        return $result;
    }
    
    /*
     * get item time info
     * string $uid
     * string $sid
     * @return string
     */
	public function getItemTimeInfo($uid,$sid) 
    {
    	if ($sid != 2 && $sid != 3) {
        	$sql = "SELECT max(id),max(begin_time),max(over_time) FROM hotel_item_time WHERE sid=:sid AND uid=:uid";
        	$result = $this->_rdb->fetchRow($sql,array('sid' => $sid,'uid' => $uid));
    	}
    	else {
    		$sql = "SELECT max(id),max(begin_time),max(over_time) FROM hotel_item_time WHERE (sid=2 OR sid=3) AND uid=:uid";
    		$result = $this->_rdb->fetchRow($sql,array('uid' => $uid));
    	}
        return $result;
    }
    
    /**
     * update item time
     *
     * @param string $id
     * @param string $time
     * @return unknown
     */
	public function updateItemTime($id,$time) 
    {
        $sql = "UPDATE hotel_item_time SET over_time = over_time + $time WHERE id=$id";

        $result = $this->_wdb->query($sql);
        
        return $result;
    }
//****************************************************************************************************
    /**
     * get store list
     *
     * @param integer $uid
     * @param integer $type
     * @param integer $page
     * @param integer $pageSize
     * @return array
     */
    public function getlistStore($uid, $type = 1, $page = 1, $pageSize = 5)
    {
    	$start = ($page - 1) * $pageSize ;

    	$sql = " SELECT uitem.uid, uitem.number, store.* FROM hotel_user_item AS uitem, hotel_store AS store
    	         WHERE uitem.uid = :uid AND store.sid = uitem.sid AND store.type = :type
    	         LIMIT $start, $pageSize";

    	$result = $this->_rdb->fetchAll($sql, array('uid' => $uid, 'type' => $type));

        return $result;
    }

    /**
     * get my's one store
     *
     * @param integer $uid
     * @param integer $sid
     * @return array
     */
    public function getStore($uid, $sid)
    {
        $sql = " SELECT uitem.uid, uitem.number, store.* FROM hotel_user_item AS uitem, hotel_store AS store
                 WHERE uitem.uid = :uid AND store.sid = uitem.sid AND store.sid = :sid";

        $result = $this->_rdb->fetchRow($sql, array('uid' => $uid, 'sid' => $sid));

        return $result;
    }

    /**
     * get one store info
     *
     * @param integer $sid
     * @return array
     */
    public function getStoreById($sid)
    {
        $sql = " SELECT * FROM hotel_store WHERE sid = :sid";

        $result = $this->_rdb->fetchRow($sql, array('sid' => $sid));

        return $result;

    }

    /**
     * get  item count
     *
     * @param integer $uid
     * @return integer
     */
    public function getItemCount($uid)
    {
        $sql = "SELECT COUNT(1) FROM hotel_user_item WHERE uid = :uid";

        $result = $this->_rdb->fetchOne($sql, array('uid' => $uid));

        return $result;
    }
}
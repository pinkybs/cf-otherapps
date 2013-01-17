<?php

require_once 'Dal/Abstract.php';

/**
 * Dal Hotel friend
 * MixiApp hotel feed Data Access Layer
 *
 * @package    Dal/Hotel
 * @copyright  Copyright (c) 2009 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/09/16    Zhaoxh
 */
class Dal_Hotel_Feed extends Dal_Abstract
{
    /**
     * Friend table name
     *
     * @var string
     */
    protected $table_user = 'hotel_user_feed';

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
     * insert feed
     *
     * @param array $feed
     * @param string $tableName
     * @return void
     */
    public function insertFeed($feed, $tableName)
    {
    	$this->table_feed = $tableName;
        return $this->_wdb->insert($this->table_feed, $feed);
    }

    /**
     * get feed
     *
     * @param integer $uid
     * @param integer $pageIndex
     * @param integer $pageSize
     * @return array
     */
    public function getFeed($uid, $pageIndex=1, $pageSize=20, $tableName)
    {
    	$this->table_feed = $tableName;
        $start = ($pageIndex - 1) * $pageSize;
        $sql = "SELECT * FROM $this->table_feed WHERE action_uid=:uid ORDER BY id DESC LIMIT $start,$pageSize";
        return $this->_rdb->fetchAll($sql, array('uid'=>$uid));
    }

    /**
     * get feed action template
     *
     * @param integer $id
     * @return array
     */
    public function getFeedTplById($id)
    {
        $sql = 'SELECT * FROM hotel_feed_action WHERE id=:id ';
        return $this->_rdb->fetchRow($sql, array('id' => $id));
    }
}
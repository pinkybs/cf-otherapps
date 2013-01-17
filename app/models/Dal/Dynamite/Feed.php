<?php

require_once 'Dal/Abstract.php';

/**
 * Dal Dynamite Feed
 * MixiApp Dynamite Feed Data Access Layer
 *
 * @package    Dal/Dynamite
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/07/06    Liz
 */
class Dal_Dynamite_Feed extends Dal_Abstract
{
    /**
     * feed table name
     *
     * @var string
     */
    protected $table_feed = 'dynamite_feed';

    protected static $_instance;

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
     * @return void
     */
    public function insertFeed($feed, $tableName)
    {
        /*
    	$this->table_feed = $tableName;
        return $this->_wdb->insert($this->table_feed, $feed);
        */

    	$this->table_feed = $tableName;

    	$sql = "INSERT DELAYED INTO $this->table_feed(uid, template_id, actor, target, feed_type, icon, title, create_time)
    	        VALUES(:uid, :template_id, :actor, :target, :feed_type, :icon, :title, :create_time)";

        $this->_wdb->query($sql, $feed);
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
        $sql = "SELECT * FROM $this->table_feed WHERE uid=:uid ORDER BY id DESC LIMIT $start,$pageSize";
        return $this->_rdb->fetchAll($sql, array('uid'=>$uid));
    }

    /**
     * do batch get delete start feed id
     *
     * @param string $feedTable
     * @param string $time
     * @return integer
     */
    public function getStartId($feedTable, $time)
    {
        $sql = "SELECT id FROM $feedTable WHERE create_time LIKE '$time%' LIMIT 1 ";
        return $this->_rdb->fetchOne($sql);
    }

    /**
     * do batch delete unused feed
     *
     * @param string $feedTable
     * @param integer $startId
     */
    public function cleanFeed($feedTable, $startId)
    {
        $sql = "DELETE FROM $feedTable WHERE id < $startId";

        $this->_wdb->query($sql);
    }

}
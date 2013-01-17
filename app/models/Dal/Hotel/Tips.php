<?php

require_once 'Dal/Abstract.php';

/**
 * Dal Hotel tips
 * MixiApp hotel tips Data Access Layer
 *
 * @package    Dal/Hotel
 * @copyright  Copyright (c) 2009 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/10/27    Xiali
 */
class Dal_Hotel_Tips extends Dal_Abstract
{
    /**
     * tips table name
     *
     * @var string
     */
    protected $table = 'hotel_system_tips';

    protected static $_instance;

    /**
     * get Dal_Hotel_tips default
     *
     * @return Dal_Hotel_tips
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

	/**
	 * get tips
	 *
	 * @param integer $lv
	 * @return array
	 */
    public function getTipsBylv($lv)
    {
        $sql = "SELECT * FROM $this->table WHERE level = :lv";
        return $this->_rdb->fetchAll($sql, array('lv'=>$lv));
    }

    /**
     * get one tips
     * @param integer $lv
     * @param integer $page
     * @param integer $pageSize
     * @return array
     */
    public function getTipsBylv($lv, $page, $pageSize)
    {
    	if ($page && $pageSize) {
    		$start = ($page - 1) * $pageSize;
    		$limit = "LIMIT $start, $pageSize ";
    	}

        $sql = "SELECT * FROM $this->table WHERE level = :lv " . $limit;
        return $this->_rdb->fetchAll($sql, array('lv'=>$lv));
    }
}
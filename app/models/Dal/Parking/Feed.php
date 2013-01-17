<?php

require_once 'Dal/Abstract.php';

class Dal_Parking_Feed extends Dal_Abstract
{
	/**
	 * user	table name
	 *
	 * @var	string
	 */
	protected $table_feed	= 'parking_minifeed';

	protected static $_instance;

	public static function getDefaultInstance()
	{
		if (self::$_instance ==	null) {
			self::$_instance = new self();
		}

		return self::$_instance;
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
     * get minifeed
     *
     * @param integer $uid
     * @param integer $pageIndex
     * @param integer $pageSize
     * @return array
     */
    public function getMinifeed($uid,$pageIndex=1,$pageSize=10)
    {
        $start = ($pageIndex - 1) * $pageSize;
        $sql = "SELECT * FROM parking_minifeed WHERE uid=:uid ORDER BY create_time DESC LIMIT $start,$pageSize";
        return $this->_rdb->fetchAll($sql, array('uid'=>$uid));
    }

    /**
     * get newsfeed
     *
     * @param integer $uid
     * @param integer $pageIndex
     * @param integer $pageSize
     * @return array
     */
    public function getNewsfeed($uid, $fids, $pageIndex=1, $pageSize=10)
    {
       $fids = $this->_rdb->quote($fids); 
       $start = ($pageIndex - 1) * $pageSize;
       $sql = "SELECT p1.* FROM parking_newsfeed AS p1,
               (SELECT * FROM parking_newsfeed WHERE uid IN ($fids) AND target<>:uid AND actor<>:uid
               GROUP BY create_time ORDER BY create_time DESC LIMIT $start,$pageSize) AS p2
               WHERE p1.id=p2.id ORDER BY p1.create_time DESC";
        return $this->_rdb->fetchAll($sql, array('uid'=>$uid));
    }

        /**
     * get newsfeed
     *
     * @param integer $uid
     * @param integer $pageIndex
     * @param integer $pageSize
     * @return array
     */
    public function getNotice($uid,$pageIndex=1,$pageSize=10)
    {
       $start = ($pageIndex - 1) * $pageSize;
       $sql = "SELECT p1.* FROM parking_newsfeed AS p1,
               (SELECT id,group_concat( DISTINCT create_time) FROM parking_newsfeed WHERE uid IN
               (SELECT uid2 FROM friend WHERE uid1=:uid) GROUP BY create_time ORDER BY create_time DESC LIMIT $start,$pageSize) AS p2
               WHERE p1.id=p2.id  AND p1.template_id <>97 ORDER BY p1.create_time DESC";
        return $this->_rdb->fetchAll($sql, array('uid'=>$uid));
    }
}
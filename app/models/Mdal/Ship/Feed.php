<?php

require_once 'Mdal/Abstract.php';

class Mdal_Ship_Feed extends Dal_Abstract
{
	/**
	 * user	table name
	 *
	 * @var	string
	 */
	protected $table_minifeed	= 'ship_minifeed';

    /**
     * user table name
     *
     * @var string
     */
    protected $table_newsfeed   = 'ship_newsfeed';
    
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
     * 
     * @param array $info
     * @return integer
     */
    public function insertMinifeed($info)
    {
        $this->_wdb->insert($this->table_minifeed, $info);
        return $this->_wdb->lastInsertId();
    }

    /**
     * insert newsfeed
     * 
     * @param array $info
     * @return integer
     */
    public function insertNewsfeed($info)
    {
        $this->_wdb->insert($this->table_newsfeed, $info);
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
    public function getMinifeed($uid, $pageIndex=1, $pageSize=10)
    {
        $start = ($pageIndex - 1) * $pageSize;
        $sql = "SELECT * FROM $this->table_minifeed WHERE uid=:uid ORDER BY create_time DESC LIMIT $start,$pageSize";
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
       $sql = "SELECT p1.* FROM $this->table_newsfeed AS p1,
               (SELECT * FROM $this->table_newsfeed WHERE uid IN ($fids) AND target<>:uid AND actor<>:uid
               GROUP BY create_time ORDER BY create_time DESC LIMIT $start,$pageSize) AS p2
               WHERE p1.id=p2.id ORDER BY p1.create_time DESC";
        return $this->_rdb->fetchAll($sql, array('uid'=>$uid));
    }
}
<?php
/**
 * enemp Operation
 * @package    Dal
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create
 */
class Dal_Dynamite_Enemy extends Dal_Abstract
{
    protected static $_instance;

    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * get ememy count
     * @param integer $uid
     * @return integer
     */
	public function getEnemyCount($uid)
	{
		$sql = "SELECT COUNT(bomb_uid) FROM dynamite_enemy WHERE bomb_uid=:uid ";
		return $this->_rdb->fetchOne($sql, array('uid' => $uid));
	}

	/**
     * get last ememy
     * @param integer $uid
     * @return integer
     */
	public function getLastEnemy($uid)
	{
		$sql = "SELECT * FROM dynamite_enemy WHERE bomb_uid=:uid ORDER BY update_time DESC LIMIT 0,1 ";
		return $this->_rdb->fetchRow($sql, array('uid' => $uid));
	}

	/**
     * insert into enemy table
     * @param integer $uid
     * @param integer $bombUid
     */
    public function insertEnemy($uid, $bombUid)
    {
        $sql = "INSERT INTO dynamite_enemy(uid, bomb_uid, update_time)
                VALUES (:uid, :bomb_uid, :update_time) ON DUPLICATE KEY UPDATE
                uid = :uid, bomb_uid = :bomb_uid, update_time = :update_time";

        $this->_wdb->query($sql, array('uid' => $uid,
                                       'bomb_uid' => $bombUid,
                                       'update_time' => time()
                                       ));
    }

}
<?php

require_once 'Dal/Abstract.php';

/**
 * Dal Dynamite Alliance
 * MixiApp Dynamite Alliance Data Access Layer
 *
 * @package    Dal/Dynamite
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/07/06    Liz
 */
class Dal_Dynamite_Alliance extends Dal_Abstract
{
    /**
     * alliance table name
     *
     * @var string
     */
    protected $table_alliance = 'dynamite_alliance';

    protected static $_instance;

    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * insert alliance
     *
     * @param array $alliance
     * @return void
     */
    public function insertAlliance($alliance)
    {
        $this->_wdb->insert($this->table_alliance, $alliance);
        return $this->_wdb->lastInsertId();
    }

    /**
     * check is had alliance apply
     *
     * @param integer $uid
     * @param integer $aid
     * @return boolean
     */
    public function isHadApply($uid, $aid)
    {
        $sql = "SELECT * FROM $this->table_alliance WHERE uid=:uid AND aid=:aid AND status=0 ";

        $result = $this->_rdb->fetchAll($sql, array('uid'=>$uid, 'aid'=>$aid));

        return $result ? true : false;
    }

    /**
     * delete alliance info
     *
     * @param integer $uid
     * @param integer $aid
     * @return void
     */
    public function deleteAlliance($uid, $aid)
    {
        $sql = "DELETE FROM $this->table_alliance WHERE uid=:uid AND aid=:aid ";
        $this->_wdb->query($sql, array('uid'=>$uid, 'aid'=>$aid));
    }

    /**
     * get user alliance
     *
     * @param integer $uid
     * @param integer $status
     * @return array
     */
    public function getUserAlliance($uid, $status=1)
    {
        $sql = "SELECT aid FROM $this->table_alliance WHERE uid=:uid AND status=:status ";

        return $this->_rdb->fetchAll($sql, array('uid'=>$uid, 'status'=>$status));
    }

    /**
     * get user alliance
     *
     * @param integer $uid
     * @param integer $status
     * @return array
     */
    public function getSendAllianceUser($uid)
    {
        $sql = "SELECT uid FROM $this->table_alliance WHERE aid=:uid AND status=0 AND isInviter=1";

        return $this->_rdb->fetchAll($sql, array('uid'=>$uid));
    }

    /**
     * confirm  Alliance apply
     *
     * @param integer $uid
     * @param integer $status
     * @return array
     */
    public function updateAllianceStatus($uid, $aid)
    {
    	$sql = "UPDATE $this->table_alliance  SET status=1 WHERE (uid=:uid AND aid=:aid) OR (uid=:aid AND aid=:uid) ";
        $this->_wdb->query($sql, array('uid'=>$uid, 'aid'=>$aid));
    }

    /**
     * check if have the apply
     * @param integer $uid
     * @param integer $aid
     * @return array
     */
    public function haveTheAllianceApply($uid, $aid)
    {
    	$sql = "SELECT * FROM $this->table_alliance WHERE uid=:uid AND aid=:aid AND status=0 AND isInviter=1";

        return $this->_rdb->fetchRow($sql, array('uid'=>$aid, 'aid'=>$uid));
    }
}
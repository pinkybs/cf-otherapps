<?php

require_once 'Mdal/Abstract.php';

/**
 * Mdal School
 * MixiApp School Design Data Access Layer
 *
 * @package    Mdal/School
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/11/23    zhangxin
 */
class Mdal_School_Design extends Mdal_Abstract
{

    /**
     * class default instance
     * @var self instance
     */
    protected static $_instance;

    /**
     * return self's default instance
     *
     * @return self instance
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * get nb design info
     *
     * @param integer $did
     * @return integer
     */
    public function getNbDesign($did)
    {
        $sql = "SELECT * FROM school_nb_design WHERE did=:did ";
        return $this->_rdb->fetchRow($sql, array('did' => $did));
    }


    /***********************************************************************************/


 	/**
     * insert user design
     *
     * @param array $user
     * @return integer
     */
    public function insertDesign($info)
    {
        return $this->_wdb->insert('school_user_design', $info);
    }

	/**
     * delete user design
     *
     * @param integer $uid
     * @return integer
     */
    public function deleteDesign($uid)
    {
        $sql = "DELETE FROM school_user_design WHERE uid=:uid ";
        return $this->_wdb->query($sql, array('uid' => $uid));
    }

    /**
     * get have's design list
     *
     * @param integer $uid
     * @param integer $pageIndex
     * @param integer $pageSize
     * @return array
     */
 	public function getlstDesign($uid, $pageIndex = 1, $pageSize = 9)
    {
		$start = ($pageIndex - 1) * $pageSize;
		$sql = "SELECT did FROM school_user_design WHERE uid = :uid LIMIT $start, $pageSize";
		return $this->_rdb->fetchAll($sql, array('uid' => $uid));
    }

    /**
     * get count design
     *
     * @param integer $uid
     * @return integer
     */
    public function getCntDesign($uid)
    {
    	$sql = "SELECT COUNT(1) FROM school_user_design WHERE uid = :uid";
		return $this->_rdb->fetchOne($sql, array('uid' => $uid));
    }

    /**
     * check whether have design
     *
     * @param integer $uid
     * @param integer $did
     * @return integer
     */
    public function isDesignExists($uid, $did)
    {
    	$sql = "SELECT COUNT(1) FROM school_user_design WHERE uid = :uid AND did = :did";
		return $this->_rdb->fetchOne($sql, array('uid' => $uid, 'did' => $did));
    }

    /**
     * get design nb
     *
     * @return array
     */
    public function getCntNbDesign()
    {
    	$sql = "SELECT did FROM school_nb_design";
		return $this->_rdb->fetchAll($sql);
    }

    /**
     * get user invite count
     *
     * @param integer $app_id
     * @param integer $uid
     * @return integer
     */
    public function getInviteCntById($app_id, $uid)
    {
		$sql = "SELECT COUNT(1) FROM mixi_app_invite WHERE actor=:uid AND app_id=:app_id AND `process` = 'finished'";
		return $this->_rdb->fetchOne($sql, array('app_id' => $app_id, 'uid' => $uid));
    }
}
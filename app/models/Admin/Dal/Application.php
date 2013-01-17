<?php

require_once 'Admin/Dal/Abstract.php';

/**
 * Dal Application
 * LinNo AdminApplication Data Access Layer
 *
 * @package    Admin/Dal
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/02/19    zhangxin
 */
class Admin_Dal_Application extends Admin_Dal_Abstract
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
     * get app all
     *
     * @param Integer $uid
     * @param Integer $pageindex
     * @param Integer $pagesize
     * @return array
     */
    public function getAppAll()
    {
        $sql = "SELECT * FROM admin_app WHERE deleted = 0 ORDER BY create_time ";

        return $this->_rdb->fetchAll($sql);
    }

    /**
     * list app
     *
     * @param Integer $uid
     * @param Integer $pageindex
     * @param Integer $pagesize
     * @return array
     */
    public function getAppList($uid, $pageindex = 1, $pagesize = 10)
    {
        $start = ($pageindex - 1) * $pagesize;
        $sql = "SELECT a.* FROM admin_app a,admin_user_app u WHERE a.aid = u.aid AND deleted = 0 AND u.uid=:uid
                ORDER BY a.create_time DESC LIMIT $start, $pagesize ";

        return $this->_rdb->fetchAll($sql, array('uid' => $uid));
    }

	/**
     * get list app count
     *
     * @param Integer $uid
     * @return integer
     */
    public function getAppListCount($uid)
    {
        $sql = "SELECT count(*) FROM admin_app a,admin_user_app u WHERE a.aid = u.aid AND deleted = 0 AND u.uid=:uid";
        return $this->_rdb->fetchOne($sql, array('uid' => $uid));
    }

    /**
     * insert Application
     *
     * @param array $info
     * @return integer
     */
    public function insertApplication($info)
    {
        $this->_wdb->insert('admin_app', $info);
        return $this->_wdb->lastInsertId();
    }

    /**
     * update Application
     *
     * @param array $info
     * @param integer $id
     * @return integer
     */
    public function updateApplication($info, $id)
    {
        $where = $this->_wdb->quoteInto('aid = ?', $id);
        return $this->_wdb->update('admin_app', $info, $where);
    }

    /**
     * delete Application
     *
     * @param integer $id
     * @return integer
     */
    public function deleteApplication($id)
    {
        $sql = "DELETE FROM admin_app WHERE aid=:aid ";
        return $this->_wdb->query($sql, array('aid' => $id));
    }

	/**
     * insert User App
     *
     * @param array $info
     * @return integer
     */
    public function insertUserApp($info)
    {
        $this->_wdb->insert('admin_user_app', $info);
        //return $this->_wdb->lastInsertId();
    }

    /**
     * delete User App
     *
     * @param integer $uid
     * @return integer
     */
    public function deleteUserAppByUid($uid)
    {
        $sql = "DELETE FROM admin_user_app WHERE uid=:uid ";
        return $this->_wdb->query($sql, array('uid' => $uid));
    }

    /**
     * check App is binded to User
     *
     * @param string $uid
     * @param integer $uid
     * @return boolean
     */
    public function isAppAllowedToUser($appCanvasName, $uid)
    {
        $sql = "SELECT count(*) FROM admin_app a,admin_user_app u WHERE a.aid = u.aid AND deleted = 0 AND u.uid=:uid AND a.canvas_name=:canvas_name";
        $result = $this->_rdb->fetchOne($sql, array('uid' => $uid, 'canvas_name' => $appCanvasName));
        return $result > 0 ? true : false;
    }

}
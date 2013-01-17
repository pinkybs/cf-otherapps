<?php

require_once 'Admin/Dal/Abstract.php';

/**
 * Dal User
 * LinNo AdminUser Data Access Layer
 *
 * @package    Admin/Dal
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/02/19    zhangxin
 */
class Admin_Dal_User extends Admin_Dal_Abstract
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
     * get user info
     *
     * @param Integer $uid
     * @return array
     */
    public function getUser($uid)
    {
        $sql = "SELECT * FROM admin_user WHERE uid=:uid";

        return $this->_rdb->fetchRow($sql, array('uid' => $uid));
    }

    /**
     * get user info by email
     *
     * @param string $email
     * @return array
     */
    public function getUserByEmail($email)
    {
        $sql = "SELECT * FROM admin_user WHERE email=:email";

        return $this->_rdb->fetchRow($sql, array('email' => $email));
    }

    /**
     * get user info by uuid
     *
     * @param string $uuid
     * @return array
     */
    public function getUserByUuid($uuid)
    {
        $sql = "SELECT * FROM admin_user WHERE uuid=:uuid";

        return $this->_rdb->fetchRow($sql, array('uuid' => $uuid));
    }

    /**
     * insert user
     *
     * @param array $info
     * @return integer
     */
    public function insertUser($info)
    {
        $this->_wdb->insert('admin_user', $info);
        return $this->_wdb->lastInsertId();
    }

    /**
     * update user
     *
     * @param array $info
     * @param integer $id
     * @return integer
     */
    public function updateUser($info, $id)
    {
        $where = $this->_wdb->quoteInto('uid = ?', $id);
        return $this->_wdb->update('admin_user', $info, $where);
    }

    /**
     * delete user
     *
     * @param integer $id
     * @return integer
     */
    public function deleteUser($id)
    {
        $sql = "DELETE FROM admin_user WHERE uid=:uid ";
        return $this->_wdb->query($sql, array('uid' => $id));
    }

	/**
     * list user
     *
     * @param Integer $pageindex
     * @param Integer $pagesize
     * @return array
     */
    public function getUserList($pageindex = 1, $pagesize = 10)
    {
        $start = ($pageindex - 1) * $pagesize;
        $sql = "SELECT uid,name,email,status,create_time FROM admin_user
                ORDER BY create_time DESC LIMIT $start, $pagesize ";

        return $this->_rdb->fetchAll($sql);
    }

	/**
     * get list user count
     *
     * @return integer
     */
    public function getUserListCount()
    {
        $sql = 'SELECT count(*) FROM admin_user';
        return $this->_rdb->fetchOne($sql);
    }

	/**
     * get user password forgot info
     *
     * @param string $uuid
     * @return array
     */
    public function getUserPassForgot($uuid)
    {
        $sql = 'SELECT uid,email,create_time FROM admin_user_pass_forgot WHERE uuid=:uuid';
        return $this->_rdb->fetchRow($sql, array('uuid' => $uuid));
    }

	/**
     * insert user password forgot
     *
     * @param array $info
     * @return integer
     */
    public function insertUserPassForgot($info)
    {
        $this->_wdb->insert('admin_user_pass_forgot', $info);
        return $this->_wdb->lastInsertId();
    }

    /**
     * delete forgot info
     *
     * @param string $uuid
     * @return void
     */
    public function deleteUserPassForgot($uuid)
    {
        $sql = "DELETE FROM admin_user_pass_forgot WHERE uuid=:uuid";
        return $this->_wdb->query($sql, array('uuid' => $uuid));
    }

    /**
     * insert user deleted
     *
     * @param integer $uid
     * @return integer
     */
    public function insertUserDeleted($uid)
    {
        $sql = 'INSERT INTO admin_user_deleted (uid, name, email, create_time)
                (SELECT uid, name, email, now() FROM admin_user WHERE uid = :uid)';
        return $this->_wdb->query($sql, array('uid' => $uid));
    }

}
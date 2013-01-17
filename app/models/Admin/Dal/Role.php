<?php

require_once 'Admin/Dal/Abstract.php';

/**
 * Dal Role
 * LinNo AdminRole Data Access Layer
 *
 * @package    Admin/Dal
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/02/23    zhangxin
 */
class Admin_Dal_Role extends Admin_Dal_Abstract
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
     * list user's role
     *
     * @param Integer $uid
     * @return array
     */
    public function getUserRole($uid)
    {
        $sql = "SELECT r.* FROM admin_user_role us, admin_role r WHERE us.rid = r.rid AND us.uid=:uid ";
        return $this->_rdb->fetchAll($sql, array('uid' => $uid));
    }

    /**
     * insert Role
     *
     * @param array $info
     * @return integer
     */
    public function insertRole($info)
    {
        $this->_wdb->insert('admin_role', $info);
        return $this->_wdb->lastInsertId();
    }

    /**
     * update Role
     *
     * @param array $info
     * @param integer $id
     * @return integer
     */
    public function updateRole($info, $id)
    {
        $where = $this->_wdb->quoteInto('rid = ?', $id);
        return $this->_wdb->update('admin_role', $info, $where);
    }

    /**
     * delete Role
     *
     * @param integer $id
     * @return integer
     */
    public function deleteRole($id)
    {
        $sql = "DELETE FROM admin_role WHERE rid=:rid ";
        return $this->_wdb->query($sql, array('rid' => $id));
    }

	/**
     * insert User Role
     *
     * @param array $info
     * @return integer
     */
    public function insertUserRole($info)
    {
        $this->_wdb->insert('admin_user_role', $info);
        //return $this->_wdb->lastInsertId();
    }

    /**
     * delete User Role
     *
     * @param integer $uid
     * @return integer
     */
    public function deleteUserRoleByUid($uid)
    {
        $sql = "DELETE FROM admin_user_role WHERE uid=:uid ";
        return $this->_wdb->query($sql, array('uid' => $uid));
    }

	/**
     * list resource
     *
     * @return array
     */
    public function getResourceList()
    {
        $sql = "SELECT * FROM admin_page";
        return $this->_rdb->fetchAll($sql);
    }

	/**
     * list role
     *
     * @return array
     */
    public function getRoleList()
    {
        $sql = "SELECT * FROM admin_role";
        return $this->_rdb->fetchAll($sql);
    }

	/**
     * get role resource allowed
     *
     * @return array
     */
    public function getRoleResource()
    {
        $sql = 'SELECT rp.*,r.role_name AS role,p.page_url AS resource
                FROM admin_role_page rp,admin_role r,admin_page p WHERE rp.rid=r.rid AND rp.pid=p.pid ';
        return $this->_rdb->fetchAll($sql);
    }

	/**
     * get role resource by rid
     * @param integer $rid
     *
     * @return array
     */
    public function getRoleResourceByRid($rid)
    {
        $sql = 'SELECT * FROM admin_role_page WHERE rid=:rid';
        return $this->_rdb->fetchAll($sql, array('rid' => $rid));
    }

	/**
     * insert Role Resource
     *
     * @param array $info
     * @return integer
     */
    public function insertRoleResource($info)
    {
        $this->_wdb->insert('admin_role_page', $info);
        //return $this->_wdb->lastInsertId();
    }

	/**
     * delete Role Resource by rid
     *
     * @param integer $rid
     * @return integer
     */
    public function deleteRoleResourceByRid($rid)
    {
        $sql = "DELETE FROM admin_role_page WHERE rid=:rid ";
        return $this->_wdb->query($sql, array('rid' => $rid));
    }

}
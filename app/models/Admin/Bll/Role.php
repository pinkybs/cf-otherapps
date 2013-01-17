<?php

/**
 * Role logic's Operation
 *
 * @package    Admin/Bll
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/02/25    zhangxin
 */
final class Admin_Bll_Role
{
    /**
     * db config
     * @var array
     */
    protected $_config;

    /**
     * db read adapter
     * @var Zend_Db_Abstract
     */
    protected $_rdb;

    /**
     * db write adapter
     * @var Zend_Db_Abstract
     */
    protected $_wdb;

    protected static $_instance;

    /**
     * init the user's variables
     *
     * @param array $config ( config info )
     */
    public function __construct($config = null)
    {
        if (is_null($config)) {
            $config = getDBConfig();
        }

        $this->_config = $config;
        $this->_rdb = $config['readDB'];
        $this->_wdb = $config['writeDB'];
    }

    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * get system role
     *
     * @return array
     */
    public static function getSysRole()
    {
        require_once 'Admin/Dal/Role.php';
        $dalRole = Admin_Dal_Role::getDefaultInstance();
        return $dalRole->getRoleList();
    }

    /**
     * get system resource
     *
     * @return array
     */
    public static function getSysResource()
    {
        require_once 'Admin/Dal/Role.php';
        $dalRole = Admin_Dal_Role::getDefaultInstance();
        return $dalRole->getResourceList();
    }

    /**
     * get system resource
     *
     * @return array
     */
    public static function getSysRoleResource()
    {
        require_once 'Admin/Dal/Role.php';
        $dalRole = Admin_Dal_Role::getDefaultInstance();
        return $dalRole->getRoleResource();
    }

	/**
     * delete user
     *
     * @param integer $uid
     * @param array $aryRes
     * @return boolean
     */
    public function updateRoleOfResource($rid, $aryRes)
    {
        try {
            require_once 'Admin/Dal/Role.php';
            $dalRole = Admin_Dal_Role::getDefaultInstance();

            $this->_wdb->beginTransaction();

            //delete role res
            $dalRole->deleteRoleResourceByRid($rid);

            //insert role res
            if (!empty($aryRes)) {
                foreach ($aryRes as $res) {
                    $dalRole->insertRoleResource(array('rid' => $rid, 'pid' => $res));
                }
            }

            $this->_wdb->commit();
            $result = true;

            return $result;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return false;
        }
    }

}
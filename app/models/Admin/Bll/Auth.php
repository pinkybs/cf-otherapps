<?php

/**
 * Auth
 * authenticate,getIdentity,loaduser
 *
 * @package    Admin/Bll
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/02/19    zhangxin
 */
final class Admin_Bll_Auth
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

    /**
     * PC Authentication
     *
     * @param string $email
     * @param string $password
     * @return boolean
     */
    public static function authenticate($email, $password)
    {
        if (!Zend_Registry::isRegistered('db')) {
            getDBConfig();
        }
        $db = Zend_Registry::get('db');
        require_once 'Zend/Auth/Adapter/DbTable.php';
        $authAdapter = new Zend_Auth_Adapter_DbTable($db);
        $authAdapter->setTableName('admin_user');
        $authAdapter->setIdentityColumn('email');
        $authAdapter->setCredentialColumn('password');

        $authAdapter->setIdentity($email);
        $authAdapter->setCredential($password);

        //do the Authentication
        $auth = Zend_Auth::getInstance();
        $result = $authAdapter->authenticate();
        if ($result->isValid()) {
            $user = $authAdapter->getResultRowObject(array('uid', 'uuid', 'status', 'name', 'email'));

            //check status
            if (1 != $user->status) {
                return 0;
            }
            $adminStorage = new Zend_Auth_Storage_Session('Zend_Auth_Admin');
            $auth->setStorage($adminStorage);
            $auth->getStorage()->write($user->uid);
            return 1;
        }
        else {
            return 0;
        }
    }

    public static function getAuthInstance()
    {
        $auth = Zend_Auth::getInstance();
        $adminStorage = new Zend_Auth_Storage_Session('Zend_Auth_Admin');
        $auth->setStorage($adminStorage);
        return $auth;
    }

    /**
     * get user identity
     *
     * @return class user
     */
    public static function getIdentity()
    {
        $uid = self::getAuthInstance()->getIdentity();

        require_once 'Admin/Bll/User.php';
        $userArray = Admin_Bll_User::getUserInfo($uid);
        $user = new stdClass();
        if (is_array($userArray)) {
            foreach ($userArray as $resultColumn => $resultValue) {
                if ($resultColumn != 'password') {
                    $user->{$resultColumn} = $resultValue;
                }
            }
        }

        return $user;
    }

    /**
     * get user roles
     *
     * @return array roles
     */
    public static function getRoles()
    {
        $uid = self::getAuthInstance()->getIdentity();
        require_once 'Admin/Bll/User.php';
        $roleArray = Admin_Bll_User::getUserRole($uid);
        return $roleArray;
    }

    /**
     * refresh user info
     *
     */
    public static function refreshIdentity()
    {
        $uid = self::getAuthInstance()->getIdentity();
        require_once 'Bll/Cache/User.php';
        Bll_Cache_User::cleanInfo($uid);
    }

    /**
     * check user is current user
     *
     * @param int $uid
     * @return true/false
     */
    public static function isCurrentUser($uid)
    {
        return self::getAuthInstance()->getIdentity() == $uid;
    }

}
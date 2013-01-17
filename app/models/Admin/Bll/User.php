<?php

/**
 * User logic's Operation
 *
 * @package    Admin/Bll
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/02/19    zhangxin
 */
final class Admin_Bll_User
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
     * get user all (admin's list)
     *
     * @param null
     * @return array
     */
    public static function getUserAll()
    {
        require_once 'Admin/Bll/Cache/User.php';
        return Admin_Bll_Cache_User::getAllAdmin();
    }

    /**
     * get user info
     *
     * @param Integer $uid
     * @return array
     */
    public static function getUserInfo($uid)
    {
        require_once 'Admin/Bll/Cache/User.php';
        return Admin_Bll_Cache_User::getInfo($uid);
    }

    /**
     * get user role
     *
     * @param Integer $uid
     * @return array
     */
    public static function getUserRole($uid)
    {
        require_once 'Admin/Dal/Role.php';
        $dalRole = Admin_Dal_Role::getDefaultInstance();
        return $dalRole->getUserRole($uid);
    }

    /**
     * get user role
     *
     * @param Integer $uid
     * @return array
     */
    public static function getUserApp($uid)
    {
        require_once 'Admin/Dal/Application.php';
        $dalApp = Admin_Dal_Application::getDefaultInstance();
        return $dalApp->getAppList($uid, 1, 1000);
    }

 	/**
     * change user password
     *
     * @param Integer $uid
     * @param string $oldPass
     * @param string $newPass
     * @return boolean
     */
    public function changeUserPassword($uid, $oldPass, $newPass)
    {
        if (empty($uid) || empty($oldPass) || empty($newPass)) {
            return false;
        }

        try {
            require_once 'Admin/Dal/User.php';
            $dalUser = Admin_Dal_User::getDefaultInstance();
            $rowUser = $dalUser->getUser($uid);
            //old password correct
            if ($rowUser['password'] != sha1($oldPass)) {
                return false;
            }

            //update password
            $dalUser->updateUser(array('password' => sha1($newPass)), $uid);

            return true;
        }
        catch (Exception $e) {
            return false;
        }
    }

 	/**
     * send user forgot password email
     *
     * @param string $email
     * @return boolean
     */
    public function sendUserForgotPasswordEmail($email)
    {
        if (empty($email)) {
            return false;
        }

        try {
            require_once 'Admin/Dal/User.php';
            $dalUser = Admin_Dal_User::getDefaultInstance();
            $rowUser = $dalUser->getUserByEmail($email);
            //is user exist
            if (empty($rowUser) || 0 == $rowUser['status']) {
                return false;
            }

            //insert user pass forgot in db
            require_once 'Bll/Secret.php';
            $uuid = Bll_Secret::getUUID();

            $forgotInfo = array('uuid' => $uuid, 'uid' => $rowUser['uid'],
                                'email' => $rowUser['email'], 'create_time' => time());

            $result = $dalUser->insertUserPassForgot($forgotInfo);

            //send mail
            if ($result) {
                require_once 'Admin/Bll/Email/Pc/SetPassword.php';
                $forgotEmail = new Admin_Bll_Email_PC_SetPassword();
                $info = array('uuid' => $forgotInfo['uuid'], 'title' => 'パスワード再発行', 'action' =>'setpass' , 'email' => $forgotInfo['email']);
                $forgotEmail->send($info);
            }

            return $result;
        }
        catch (Exception $e) {
            return false;
        }
    }

    /**
     * reset password from forgot or new user
     *
     * @param string $uuid
     * @param string $password
     * @return boolean
     */
    public function resetPassword($uuid, $password)
    {
        if (empty($uuid) || empty($password)) {
            return false;
        }

        try {
            require_once 'Admin/Dal/User.php';
            $dalUser = new Admin_Dal_User($this->_config);

            //get forgot info
            $info = $dalUser->getUserPassForgot($uuid);
            if (empty($info)) {
                return false;
            }
            //if time() subtract $info['create_time'] > 3600 * 24
            if (time() - $info['create_time'] > 3600 * 24) {
                return false;
            }

            $this->_wdb->beginTransaction();
            //update password
            $dalUser->updateUser(array('password' => sha1($password)), $info['uid']);
            //delete forgot log info
            $dalUser->deleteUserPassForgot($uuid);
            $this->_wdb->commit();

            return true;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return false;
        }
    }

	/**
     * register user and  set password
     *
     * @param string $uuid
     * @param string $password
     * @return boolean
     */
    public function registerPassword($uuid, $password)
    {
        if (empty($uuid) || empty($password)) {
            return false;
        }

        try {
            require_once 'Admin/Dal/User.php';
            $dalUser = new Admin_Dal_User($this->_config);

            //get Not Active user info
            $info = $dalUser->getUserByUuid($uuid);
            if (empty($info) || 1 == $info['status']) {
                return false;
            }

            //if time() subtract $info['create_time'] > 3600 * 24
            //if (time() - $info['create_time'] > 3600 * 24) {
            //    return false;
            //}

            $this->_wdb->beginTransaction();
            //update password and update user status
            $dalUser->updateUser(array('password' => sha1($password), 'status' => 1), $info['uid']);
            $this->_wdb->commit();

            return true;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return false;
        }
    }

	/**
     * add user
     *
     * @param array $userInfo
     * @param array $roleInfo
     * @param array $appInfo
     * @return boolean
     */
    public function addUser($userInfo, $roleInfo = array(), $appInfo = array())
    {
        if (empty($userInfo)) {
            return false;
        }

        try {
            require_once 'Bll/Secret.php';
            $uuid = Bll_Secret::getUUID();

            require_once 'Admin/Dal/User.php';
            $dalUser = new Admin_Dal_User($this->_config);
            require_once 'Admin/Dal/Role.php';
            $dalRole = new Admin_Dal_Role($this->_config);
            require_once 'Admin/Dal/Application.php';
            $dalApp = new Admin_Dal_Application($this->_config);

            $this->_wdb->beginTransaction();

            //insert user
            $userInfo['uuid'] = $uuid;
            $userInfo['create_time'] = date('Y-m-d H:i:s');
            $uid = $dalUser->insertUser($userInfo);

            //insert user roles
            $dalRole->deleteUserRoleByUid($uid);
            foreach ($roleInfo as $role) {
                $dalRole->insertUserRole(array('uid' => $uid, 'rid' => $role));
            }

            //insert user apps
            $dalApp->deleteUserAppByUid($uid);
            foreach ($appInfo as $app) {
                $dalApp->insertUserApp(array('uid' => $uid, 'aid' => $app));
            }

            $this->_wdb->commit();
            $result = true;

            //send mail to this user
            if ($result) {
                require_once 'Admin/Bll/Email/Pc/SetPassword.php';
                $registerEmail = new Admin_Bll_Email_PC_SetPassword();
                $info = array('uuid' => $userInfo['uuid'], 'title' => '管理ユーザー追加', 'action' => 'registerpass', 'email' => $userInfo['email']);
                $registerEmail->send($info);
            }

            return $result;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return false;
        }
    }

    /**
     * edit user
     *
     * @param integer $uid
     * @param integer $userRole
     * @param array $userInfo
     * @param array $roleInfo
     * @param array $appInfo
     * @return boolean
     */
    public function editUser($uid, $userRole, $userInfo, $roleInfo = array(), $appInfo = array())
    {
        if (empty($userInfo)) {
            return false;
        }

        try {
            require_once 'Admin/Dal/User.php';
            $dalUser = new Admin_Dal_User($this->_config);
            require_once 'Admin/Dal/Role.php';
            $dalRole = new Admin_Dal_Role($this->_config);
            require_once 'Admin/Dal/Application.php';
            $dalApp = new Admin_Dal_Application($this->_config);

            $this->_wdb->beginTransaction();

            //update user
            //$userInfo['create_time'] = date('Y-m-d H:i:s');
            $dalUser->updateUser($userInfo, $uid);

            if (1 != $userRole) {
                //update user roles
                $dalRole->deleteUserRoleByUid($uid);
                foreach ($roleInfo as $role) {
                    $dalRole->insertUserRole(array('uid' => $uid, 'rid' => $role));
                }

                //update user apps
                $dalApp->deleteUserAppByUid($uid);
                foreach ($appInfo as $app) {
                    $dalApp->insertUserApp(array('uid' => $uid, 'aid' => $app));
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

    /**
     * delete user
     *
     * @param integer $uid
     * @return boolean
     */
    public function deleteUser($uid)
    {
        try {
            require_once 'Admin/Dal/User.php';
            $dalUser = new Admin_Dal_User($this->_config);
            require_once 'Admin/Dal/Role.php';
            $dalRole = new Admin_Dal_Role($this->_config);
            require_once 'Admin/Dal/Application.php';
            $dalApp = new Admin_Dal_Application($this->_config);

            $this->_wdb->beginTransaction();

            //delete user's apps
            $dalApp->deleteUserAppByUid($uid);

            //delete user's role
            $dalRole->deleteUserRoleByUid($uid);

            //insert user deleted history
            $dalUser->insertUserDeleted($uid);

            //delete user
            $dalUser->deleteUser($uid);

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
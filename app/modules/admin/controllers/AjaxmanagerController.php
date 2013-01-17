<?php

/** @see Zend_Json */
require_once 'Zend/Json.php';

/**
 * Admin Manager Ajax Controller
 * Manager ajax operation
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create    2009/02/19    zhangxin
 */
class Admin_AjaxmanagerController extends MyLib_Zend_Controller_Action_AdminAjax
{
    /**
     * post-Initialize
     * called after parent::init method execution.
     * it can override
     * @return void
     */
    public function postInit()
    {
    }

    /**
     * application list view
     *
     */
    public function listappAction()
    {
        $pageIndex = (int)$this->_request->getPost('pageIndex', 1);
        $pageSize = (int)$this->_request->getPost('pageSize', 10);

        require_once 'Admin/Dal/Application.php';
        $dalApp = Admin_Dal_Application::getDefaultInstance();
        $result = $dalApp->getAppList($this->_user->uid, $pageIndex, $pageSize);
        $count = (int)$dalApp->getAppListCount($this->_user->uid);
        $response = array('info' => $result, 'count' => $count);
        $response = Zend_Json::encode($response);
        echo $response;

    }

	/**
     * user list view
     *
     */
    public function listuserAction()
    {
        $pageIndex = (int)$this->_request->getPost('pageIndex', 1);
        $pageSize = (int)$this->_request->getPost('pageSize', 10);

        require_once 'Admin/Dal/User.php';
        $dalUser = Admin_Dal_User::getDefaultInstance();
        $result = $dalUser->getUserList($pageIndex, $pageSize);
        $count = (int)$dalUser->getUserListCount();

        require_once 'Admin/Dal/Role.php';
        $dalRole = Admin_Dal_Role::getDefaultInstance();
        foreach ($result as $key=>$data) {
            $roles = $dalRole->getUserRole($data['uid']);
            $strRole = '|';
            foreach ($roles as $sRole) {
                $strRole .= $sRole['role_name'];
            }
            $strRole = substr($strRole, 1);
            $result[$key]['role_name'] = $strRole;
        }

        $response = array('info' => $result, 'count' => $count);
        $response = Zend_Json::encode($response);
        echo $response;
    }

	/**
     * change password
     *
     */
    public function changepassAction()
    {
        if ($this->_request->isPost()) {
            $oldPass = $this->_request->getPost('txtOldPw');
            $newPass = $this->_request->getPost('txtNewPw');
            $confirmPass = $this->_request->getPost('txtConfirmPw');

            if (empty($oldPass) || empty($newPass) || empty($confirmPass)) {
                echo 'false';
                return;
            }
            if ($newPass !== $confirmPass) {
                echo 'false';
                return;
            }

            //change user pass
            require_once 'Admin/Bll/User.php';
            $bllUser = Admin_Bll_User::getDefaultInstance();
            $result = $bllUser->changeUserPassword($this->_user->uid, $oldPass, $newPass);

            echo $result ? 'true' : 'false';
        }
    }

	/**
     * manager add user
     *
     */
    public function adduserAction()
    {
        if ($this->_request->isPost()) {
            $userName = $this->_request->getPost('txtName');
            $userEmail = $this->_request->getPost('txtEmail');
            $userAuth = (int)$this->_request->getPost('selAuth');
            $userApps = $this->_request->getPost('selApp');

            if (empty($userName) || empty($userEmail) || empty($userAuth) || empty($userApps)) {
                echo '項目をすべて入力してください。';
                return;
            }

            require_once 'Admin/Dal/User.php';
            $dalUser = Admin_Dal_User::getDefaultInstance();
            $rowUser = $dalUser->getUserByEmail($userEmail);
            if (!empty($rowUser)) {
                echo 'このメールアドレスは既に登録されました。';
                return;
            }

            //add user
            $userInfo['name'] = $userName;
            $userInfo['email'] = $userEmail;
            $roleInfo = array();
            $roleInfo[] = $userAuth;
            //get not repeated app list
            $appInfo = array();
            foreach ($userApps as $app) {
                $blnRepeat = false;
                foreach ($appInfo as $temp) {
                    if ($temp == $app) {
                        $blnRepeat = true;
                        break;
                    }
                }
                if (!empty($app) && !$blnRepeat) {
                    $appInfo[] = $app;
                }
            }

            require_once 'Admin/Bll/User.php';
            $bllUser = new Admin_Bll_User();
            $result = $bllUser->addUser($userInfo, $roleInfo, $appInfo);

            if ($result) {
                require_once 'Admin/Bll/Cache/User.php';
                Admin_Bll_Cache_User::clearAllAdmin();
            }

            echo $result ? 'true' : 'false';
        }
    }

    /**
     * manager edit user
     *
     */
    public function edituserAction()
    {
        if ($this->_request->isPost()) {
            $userName = $this->_request->getPost('txtName');
            $userAuth = (int)$this->_request->getPost('selAuth');
            $userApps = $this->_request->getPost('selApp');
            $userRole = $this->_request->getPost('userRole');
            $uid = $this->_request->getPost('uid');

            //super user
            if (1 == $userRole) {
                if (empty($userName)) {
                    echo '項目をすべて入力してください。';
                    return;
                }
            } else {
                if (empty($userName) || empty($userAuth) || empty($userApps)) {
                    echo '項目をすべて入力してください。';
                    return;
                }
            }

            $userInfo = array();
            $roleInfo = array();
            $appInfo = array();

            //add user
            $userInfo['name'] = $userName;
            if (1 != $userRole) {
                $roleInfo[] = $userAuth;

                //get app list
                foreach ($userApps as $app) {
                    $blnRepeat = false;
                    foreach ($appInfo as $temp) {
                        if ($temp == $app) {
                            $blnRepeat = true;
                            break;
                        }
                    }
                    if (!empty($app) && !$blnRepeat) {
                        $appInfo[] = $app;
                    }
                }
            }

            require_once 'Admin/Bll/User.php';
            $bllUser = new Admin_Bll_User();
            $result = $bllUser->editUser($uid, $userRole, $userInfo, $roleInfo, $appInfo);

            if ($result) {
                require_once 'Admin/Bll/Cache/User.php';
                Admin_Bll_Cache_User::clearAllAdmin();
                Admin_Bll_Cache_User::clearInfo($uid);
            }

            echo $result ? 'true' : 'false';
        }
    }

    /**
     * manager delete user
     *
     */
    public function deleteuserAction()
    {
        if ($this->_request->isPost()) {
            $uid = $this->_request->getPost('uid');

            require_once 'Admin/Bll/User.php';
            $bllUser = new Admin_Bll_User();
            $result = $bllUser->deleteUser($uid);

            if ($result) {
                require_once 'Admin/Bll/Cache/User.php';
                Admin_Bll_Cache_User::clearAllAdmin();
                Admin_Bll_Cache_User::clearInfo($uid);
            }

            echo $result ? 'true' : 'false';
        }
    }



    /**
     * manage role and resource (SuperUser Only) *****
     *
     */
    public function getresourceofroleAction()
    {
        $rid = $this->_request->getPost('id');
        if (!empty($rid)) {
            require_once 'Admin/Dal/Role.php';
            $dalRole = Admin_Dal_Role::getDefaultInstance();
            $aryAllRes = $dalRole->getResourceList();
            $aryRelRes = $dalRole->getRoleResourceByRid($rid);
            foreach ($aryAllRes as $key=>$data) {
                $blnChecked = false;
                foreach ($aryRelRes as $data2) {
                    if ($data2['pid'] == $data['pid']) {
                        $blnChecked = true;
                        break;
                    }
                }
                $aryAllRes[$key]['ischeck'] = $blnChecked;
            }

            $response = array('info' => $aryAllRes, 'count' => count($aryAllRes));
            $response = Zend_Json::encode($response);
            echo $response;
        }
    }

    /**
     * manage role and resource (SuperUser Only) *****
     *
     */
    public function manageroleandresourceAction()
    {
        if ($this->_request->isPost()) {
            $rid = (int)$this->_request->getPost('rid');
            $aryChecked = $this->_request->getPost('chkRes');

            require_once 'Admin/Bll/Role.php';
            $bllRole = Admin_Bll_Role::getDefaultInstance();
            $result = $bllRole->updateRoleOfResource($rid, $aryChecked);

            echo $result ? 'true' : 'false';
        }
    }






    /**
     * check is validate admin user before action
     *
     */
    function preDispatch()
    {

    }
}
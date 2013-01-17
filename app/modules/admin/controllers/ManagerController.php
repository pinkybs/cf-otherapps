<?php

/**
 * Admin Manager Controller(modules/admin/controllers/Admin_ManagerController.php)
 * Linno Admin Manager Controller
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create    2009/02/18    zhangxin
 */
class Admin_ManagerController extends MyLib_Zend_Controller_Action_Admin
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
     * application controller index action
     *
     */
    public function indexAction()
    {
        $this->_forward('listapp', 'manager', 'admin');
        return;
    }

    /**
     * manager controller list action
     *
     */
    public function listappAction()
    {

        $this->view->title = 'アプリ一覧｜OPENSOCIAL APPS ADMIN｜CF';
        $this->render();
    }

    /**
     * manager controller changepassword action
     *
     */
    public function changepassAction()
    {

        $this->view->title = 'パスワード変更｜OPENSOCIAL APPS ADMIN｜CF';
        $this->render();
    }

    /**
     * manager controller manage user action
     *
     */
    public function manageuserAction()
    {
        $this->_forward('adduser', 'manager', 'admin');
        return;
    }

    /**
     * manager controller manage user action
     *
     */
    public function adduserAction()
    {
        require_once 'Admin/Dal/Role.php';
        $dalRole = Admin_Dal_Role::getDefaultInstance();
        $this->view->roles = $dalRole->getRoleList();

        require_once 'Admin/Dal/Application.php';
        $dalApp = Admin_Dal_Application::getDefaultInstance();
        $this->view->apps = $dalApp->getAppAll();
        $this->view->title = 'ユーザーID管理｜OPENSOCIAL APPS ADMIN｜CF';
        $this->view->pageIndex = 1;
        $this->render();
    }

    /**
     * manager controller manage user action
     *
     */
    public function edituserAction()
    {
        $pageIndex = (int)$this->_request->getParam('pageIndex', 1);
        $this->view->pageIndex = $pageIndex;

        require_once 'Admin/Dal/Role.php';
        $dalRole = Admin_Dal_Role::getDefaultInstance();
        $this->view->roles = $dalRole->getRoleList();
        $uid = (int)$this->_request->getParam('uid');
        $this->view->uid = $uid;

        require_once 'Admin/Bll/User.php';
        $bllUser = Admin_Bll_User::getDefaultInstance();
        $userInfo = $bllUser->getUserInfo($uid);

        //if user not exist
        if (empty($userInfo)) {
            $this->_forward('adduser', 'manager', 'admin');
            return;
        }

        $this->view->userInfo = $userInfo;
        $userRoles = $bllUser->getUserRole($uid);
        $this->view->userRole = $userRoles[0]['rid'];
        $userApps = $bllUser->getUserApp($uid);
        $this->view->userApps = $userApps;
        $this->view->userAppCount = count($userApps);

        require_once 'Admin/Dal/Application.php';
        $dalApp = Admin_Dal_Application::getDefaultInstance();
        $this->view->apps = $dalApp->getAppAll();
        $this->view->title = 'ユーザーID管理｜OPENSOCIAL APPS ADMIN｜CF';
        $this->render();
    }


    /**
     * manager controller manage role and resource (SuperUser Only) *****
     *
     */
    public function manageroleandresourceAction()
    {
        require_once 'Admin/Dal/Role.php';
        $dalRole = Admin_Dal_Role::getDefaultInstance();
        $this->view->roles = $dalRole->getRoleList();

        $this->view->title = 'role&resource｜OPENSOCIAL APPS ADMIN｜CF';
        $this->render();
    }

    /**
     * preDispatch
     *
     */
    function preDispatch()
    {

    }
}
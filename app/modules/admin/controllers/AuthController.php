<?php

/**
 * Admin Auth Controller(modules/admin/controllers/Admin_AuthController.php)
 * Linno Admin Auth Controller
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create    2009/02/18    zhangxin
 */
class Admin_AuthController extends Zend_Controller_Action
{
    /**
     * admin website base URL
     * @var string
     */
    protected $_baseUrl;

    /**
     * page init
     *
     */
    function init()
    {
        //get admin website base url
        $this->_baseUrl = $this->_request->getBaseUrl();
        $this->view->baseUrl = $this->_baseUrl;
        $this->view->staticUrl = Zend_Registry::get('static');
        $this->view->version = Zend_Registry::get('version');
    }

    /**
     * auth controller login action
     *
     */
    public function loginAction()
    {
        //if is post
        if ($this->_request->isPost()) {
            //get posted data from client
            $email = $this->_request->getPost('txtId');
            $password = $this->_request->getPost('txtPw');
            $this->view->errmsg = '';
            $this->view->adminId = $email;

            //check validate
            require_once 'Zend/Validate/EmailAddress.php';
            $vdEmail = new Zend_Validate_EmailAddress();
            //check string length
            if (empty($email) || empty($password)) {
                $this->view->errmsg = 'メールアドレスとパスワードを入力してください。';
            }
            else if (strlen($email) > 200) {
                $this->view->errmsg = 'メールアドレスは200字以下で入力してください。';
            }
            else if (strlen($password) > 12 || strlen($password) < 6) {
                $this->view->errmsg = 'パスワードは6字以上12字以下で入力してください。';
            }
            //check $email is right format
            else if (!$vdEmail->isValid($email)) {
                $this->view->errmsg = '有効なメールアドレスを入力してください。';
            }
            else {
                require_once 'Admin/Bll/Auth.php';
                $result = Admin_Bll_Auth::authenticate($email, sha1($password));

                if ($result == 1) {
                    //$user = Admin_Bll_Auth::getIdentity();
                    $this->_redirect($this->_baseUrl . '/manager');
                    return;
                }
                //reject to pass
                else {
                    $this->view->errmsg = '登録しているメールアドレス、またはパスワードが違います。';
                }
            }
        }
        else {
            require_once 'Admin/Bll/Auth.php';
            $auth = Admin_Bll_Auth::getAuthInstance();
            if ($auth->hasIdentity()) {
                $this->_redirect($this->_baseUrl . '/manager');
                return;
            }
        }

        $this->view->title = 'ログイン｜OPENSOCIAL APPS ADMIN｜LinNo ( リンノ )';
        $this->render();
    }

	/**
     * auth controller forgot password action
     *
     */
    public function forgotpassAction()
    {
        $this->view->step = 1;
        //if is post
        if ($this->_request->isPost()) {
            //get posted data from client
            $email = $this->_request->getPost('txtId');
            $this->view->errmsg = '';
            $this->view->adminId = $email;

            //check validate
            require_once 'Zend/Validate/EmailAddress.php';
            $vdEmail = new Zend_Validate_EmailAddress();
            if (empty($email)) {
                $this->view->errmsg = 'メールアドレスとパスワードを入力してください。';
            }
            else if (strlen($email) > 200) {
                $this->view->errmsg = 'メールアドレスは200字以下で入力してください。';
            }
            //check $email is right format
            else if (!$vdEmail->isValid($email)) {
                $this->view->errmsg = '有効なメールアドレスを入力してください。';
            }
            else {
                //send mail
                require_once 'Admin/Bll/User.php';
                $bllforgotpassword = new Admin_Bll_User();
                $result = $bllforgotpassword->sendUserForgotPasswordEmail($email);
                if ($result) {
                    $this->view->step = 2;
                }
                else {
                    $this->view->errmsg = 'このメールアドレスは登録されていません。';
                }
            }
        }
        //init page
        else {

        }

        $this->view->title = 'パスワードリマインダー｜OPENSOCIAL APPS ADMIN｜LinNo ( リンノ )';
        $this->render();
    }

	/**
     * auth controller password forgot and reset the password
     *
     *
     */
    public function setpassAction()
    {
        $this->view->step = 1;
        //if is post
        if ($this->_request->isPost()) {
            $newPass = $this->_request->getPost('txtNewPw');
            $confirmPass = $this->_request->getPost('txtConfirmPw');
            $uuid = $this->_request->getPost('uuid');

            if (empty($newPass) || empty($confirmPass) || empty($uuid)) {
                $this->view->errmsg = 'パスワードは必要ですまたはキー（uuid）は必要です。';
            }
            else if ($newPass !== $confirmPass) {
                $this->view->errmsg = 'パスワードと異なります。';
            }
            else {
                //reset password
                require_once 'Admin/Bll/User.php';
                $bllResetPass = new Admin_Bll_User();
                $result = $bllResetPass->resetPassword($uuid, $newPass);
                if ($result) {
                    $this->view->step = 2;
                }
                else {
                    $this->view->uuid = $uuid;
                    $this->view->errmsg = '有効期間は1日です。1日以内にアクセスしていただけなかった場合は、再度以下のURLよりお手続きをお願い致します。'. Zend_Registry::get('host') . '/auth/forgotpass';
                }
            }
        }
        //init page
        else {
            $uuid = $this->_request->getParam('uuid');

            //check uuid
            if (empty($uuid)) {
                $this->_forward('noauthority', 'error', 'admin');
                return;
            }

            require_once 'Admin/Dal/User.php';
            $forgot = Admin_Dal_User::getDefaultInstance();
            $info = $forgot->getUserPassForgot($uuid);

            //if the $info empty
            if (empty($info)) {
                $this->_forward('noauthority', 'error', 'admin');
                return;
            }
            $this->view->uuid = $uuid;
        }

        $this->view->title = 'パスワードを再発行｜OPENSOCIAL APPS ADMIN｜LinNo ( リンノ )';
        $this->render();
    }

	/**
     * auth controller register user and set the password
     *
     *
     */
    public function registerpassAction()
    {
        $this->view->step = 1;
        //if is post
        if ($this->_request->isPost()) {
            $newPass = $this->_request->getPost('txtNewPw');
            $confirmPass = $this->_request->getPost('txtConfirmPw');
            $uuid = $this->_request->getPost('uuid');

            if (empty($newPass) || empty($confirmPass) || empty($uuid)) {
                $this->view->errmsg = 'パスワードは必要ですまたはキー（uuid）は必要です。';
            }
            else if ($newPass !== $confirmPass) {
                $this->view->errmsg = 'パスワードと異なります。';
            }
            else {
                //registe and set password
                require_once 'Admin/Bll/User.php';
                $bllResetPass = new Admin_Bll_User();
                $result = $bllResetPass->registerPassword($uuid, $newPass);
                if ($result) {
                    $this->view->step = 2;
                }
                else {
                    $this->_forward('error', 'error', 'admin', array('message' => 'エラーが出ました。登録失敗しました。'));
                    return;
                }
            }
        }
        //init page
        else {
            $uuid = $this->_request->getParam('uuid');

            //check uuid
            if (empty($uuid)) {
                $this->_forward('noauthority', 'error', 'admin');
                return;
            }

            require_once 'Admin/Dal/User.php';
            $register = Admin_Dal_User::getDefaultInstance();
            $info = $register->getUserByUuid($uuid);

            //if the $info empty
            if (empty($info)) {
                $this->_forward('noauthority', 'error', 'admin');
                return;
            }

            if (1 == $info['status']) {
                $this->_forward('error', 'error', 'admin', array('message' => 'そのユーザーが既に登録しました。'));
                return;
            }
            $this->view->uuid = $uuid;
        }

        $this->view->title = '登録を確認｜OPENSOCIAL APPS ADMIN｜LinNo ( リンノ )';
        $this->render();
    }

    /**
     * auth controller logout action
     *
     */
    public function logoutAction()
    {
        //clear admin session
        require_once 'Admin/Bll/Auth.php';
        $auth = Admin_Bll_Auth::getAuthInstance();
        if ($auth->hasIdentity()) {
            //clear Session
            $auth->clearIdentity();
        }

        Zend_Session::regenerateId();
        $this->_redirect($this->_baseUrl . '/');
        return;
    }

    /**
     * call
     *
     */
    function __call($methodName, $args)
    {
        return $this->_forward('notfound', 'error', 'admin');
    }
}
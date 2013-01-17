<?php

/** @see Zend_Json */
require_once 'Zend/Json.php';

/**
 * Admin Board Ajax Controller
 * Manager ajax operation
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create    2009/03/06    zhangxin
 */
class Admin_AjaxaboardController extends MyLib_Zend_Controller_Action_AdminAjax
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

    /*********** forbid words setting ***********/
    /**
     * forbid words list view
     *
     */
    public function listforbidwordAction()
    {
        $pageIndex = (int)$this->_request->getPost('pageIndex', 1);
        $pageSize = (int)$this->_request->getPost('pageSize', 10);

        require_once 'Admin/Dal/Board.php';
        $dalBoard = Admin_Dal_Board::getDefaultInstance();
        $result = $dalBoard->listForbidWord($pageIndex, $pageSize);
        $count = count($result);
        $lastDate = '';
        $lastName = '';
        if ($count > 0) {
            $lastDate = $result[0]['update_time'];
            $lastName = $result[0]['admin_name'];
            foreach ($result as $data) {
                if ($lastDate < $data['update_time']) {
                    $lastDate = $data['update_time'];
                    $lastName = $data['admin_name'];
                }
            }
        }
        $lastDate = empty($lastDate) ? '' : substr($lastDate, 0, 10);
        $response = array('info' => $result, 'count' => $count, 'date' => $lastDate, 'name' => $lastName);
        $response = Zend_Json::encode($response);
        echo $response;
    }

    /**
     * add a forbid word
     *
     */
    public function addforbidwordAction()
    {
        if ($this->_request->isPost()) {
            $word = $this->_request->getPost('word');
            $type = (int)$this->_request->getPost('type');

            if (empty($word) || empty($type)) {
                echo 'false';
                return;
            }
            if (!($this->_isSuperUser || $this->_isEditor)) {
                echo 'false';
                return;
            }

            //insert forbid word
            require_once 'Admin/Dal/Board.php';
            $dalBoard = Admin_Dal_Board::getDefaultInstance();
            $info = array();
            $info['tid'] = $type;
            $info['word'] = $word;
            $info['admin_id'] = $this->_user->uid;
            $info['create_time'] = date('Y-m-d H:i:s');
            $info['update_time'] = date('Y-m-d H:i:s');
            $result = $dalBoard->insertForbidWord($info);

            echo $result ? 'true' : 'false';
        }
    }

    /**
     * edit a forbid word
     *
     */
    public function editforbidwordAction()
    {
        if ($this->_request->isPost()) {
            $word = $this->_request->getPost('word');
            $type = (int)$this->_request->getPost('type');
            $id = (int)$this->_request->getPost('id');

            if (empty($word) || empty($type) || empty($id)) {
                echo 'false';
                return;
            }
            if (!($this->_isSuperUser || $this->_isEditor)) {
                echo 'false';
                return;
            }

            //update forbid word
            require_once 'Admin/Dal/Board.php';
            $dalBoard = Admin_Dal_Board::getDefaultInstance();
            $rowWord = $dalBoard->getForbidWordById($id);
            if (empty($rowWord)) {
                echo 'false';
                return;
            }
            $info = array();
            $info['tid'] = $type;
            $info['word'] = $word;
            $info['admin_id'] = $this->_user->uid;
            $info['update_time'] = date('Y-m-d H:i:s');
            $result = $dalBoard->updateForbidWord($info, $id);

            echo $result >= 0 ? 'true' : 'false';
        }
    }

    /**
     * delete a forbid word
     *
     */
    public function delforbidwordAction()
    {
        if ($this->_request->isPost()) {
            $id = (int)$this->_request->getPost('id');

            if (empty($id)) {
                echo 'false';
                return;
            }
            if (!($this->_isSuperUser || $this->_isEditor)) {
                echo 'false';
                return;
            }

            //delete forbid word
            require_once 'Admin/Dal/Board.php';
            $dalBoard = Admin_Dal_Board::getDefaultInstance();
            $result = $dalBoard->deleteForbidWord($id);

            echo $result >= 0 ? 'true' : 'false';
        }
    }

    /*********** watch comment contents ***********/
    /**
     * list watch comment
     *
     */
    public function listwatchcommentAction()
    {
        //get hidden post data
        $pageIndex = (int)$this->_request->getPost('pageIndex', 1);
        $pageSize = (int)$this->_request->getPost('pageSize', 10);
        $hidSrhStatus = (int)$this->_request->getPost('hidSrhStatus');
        $hidSrhKeyword = $this->_request->getPost('hidSrhKeyword');
        $hidShowType = (int)$this->_request->getPost('hidShowType');
        $hidTypeId = $this->_request->getPost('hidTypeId');

        require_once 'Admin/Bll/User.php';
        require_once 'Admin/Dal/Board.php';
        $dalBoard = Admin_Dal_Board::getDefaultInstance();
        //show type [0-search|1-ID別コンテンツ一覧｜2-監視対象別のコンテンツ一覧|3-監視担当者別のコンテンツ一覧]
        //search show
        if (empty($hidShowType)) {
            $result = $dalBoard->getBoardWatchCommentList($pageIndex, $pageSize, $hidSrhStatus, $hidSrhKeyword);
            $count = (int)$dalBoard->getBoardWatchCommentListCount($hidSrhStatus, $hidSrhKeyword);
        }
        //comment user show
        else if (1 == $hidShowType) {
            $result = $dalBoard->getBoardWatchCommentListById($pageIndex, $pageSize, $hidTypeId, 'comment_uid');
            $count = (int)$dalBoard->getBoardWatchCommentListCountById($hidTypeId, 'comment_uid');
        }
        //object user show
        else if (2 == $hidShowType) {
            $result = $dalBoard->getBoardWatchCommentListById($pageIndex, $pageSize, $hidTypeId, 'uid');
            $count = (int)$dalBoard->getBoardWatchCommentListCountById($hidTypeId, 'uid');
        }
        //admin user show
        else if (3 == $hidShowType) {
            $result = $dalBoard->getBoardWatchCommentListById($pageIndex, $pageSize, $hidTypeId, 'admin_id');
            $count = (int)$dalBoard->getBoardWatchCommentListCountById($hidTypeId, 'admin_id');
            $row = Admin_Bll_User::getUserInfo($hidTypeId);
            $adminName = empty($row) ? '' : $row['name'];
        }
        else {
            echo 'error!';
            return;
        }

        //get admin user name
        foreach ($result as $key => $rowData) {
            $adminId = (int)$rowData['admin_id'];
            $result[$key]['admin_name'] = '';
            if (!empty($adminId)) {
                $rowAdmin = Admin_Bll_User::getUserInfo($adminId);
                $result[$key]['admin_name'] = empty($rowAdmin) ? '' : $rowAdmin['name'];
            }
        }

        $response = array('info' => $result, 'count' => $count, 'name' => (empty($adminName) ? '' : $adminName));
        $response = Zend_Json::encode($response);
        echo $response;
    }

    /**
     * deal watch comment
     *
     */
    public function dealwatchcommentAction()
    {
        if ($this->_request->isPost()) {
            $aryIds = $this->_request->getPost('commentId');
            $aryStatus = $this->_request->getPost('selStatus');

            if (0 == count($aryIds) || count($aryIds) != count($aryStatus)) {
                echo '0';
                return;
            }

            if ($this->_isViewer) {
                echo 'false';
                return;
            }

            //combine ids and status info [$key=>$value]
            $aryStatusInfo = array();
            foreach ($aryIds as $key => $bid) {
                $aryStatusInfo[$bid] = $aryStatus[$key];
            }
            require_once 'Admin/Bll/Board.php';
            $bllBoard = new Admin_Bll_Board();
            $result = $bllBoard->dealWatchComment($aryStatusInfo, $this->_user->uid, $this->_isWatcher);
            echo $result;
        }
    }

    /*********** watch title contents ***********/
    /**
     * list watch title
     *
     */
    public function listwatchtitleAction()
    {
        //get hidden post data
        $pageIndex = (int)$this->_request->getPost('pageIndex', 1);
        $pageSize = (int)$this->_request->getPost('pageSize', 10);
        $hidSrhStatus = (int)$this->_request->getPost('hidSrhStatus');
        $hidSrhKeyword = $this->_request->getPost('hidSrhKeyword');
        $hidShowType = (int)$this->_request->getPost('hidShowType');
        $hidTypeId = $this->_request->getPost('hidTypeId');

        require_once 'Admin/Bll/User.php';
        require_once 'Admin/Dal/Board.php';
        $dalBoard = Admin_Dal_Board::getDefaultInstance();
        //show type [0-search|1-ID別コンテンツ一覧｜2-監視対象別のコンテンツ一覧|3-監視担当者別のコンテンツ一覧]
        //search show
        if (empty($hidShowType)) {
            $result = $dalBoard->getBoardWatchTitleList($pageIndex, $pageSize, $hidSrhStatus, $hidSrhKeyword, 'title_status', 'title');
            $count = (int)$dalBoard->getBoardWatchTitleListCount($hidSrhStatus, $hidSrhKeyword, 'title_status', 'title');
        }
        //commented user show / object user show
        else if (1 == $hidShowType || 2 == $hidShowType) {
            $result = $dalBoard->getBoardWatchTitleListById($pageIndex, $pageSize, $hidTypeId, 'uid');
            $count = (int)$dalBoard->getBoardWatchTitleListCountById($hidTypeId, 'uid');
        }
        //admin user show
        else if (3 == $hidShowType) {
            $result = $dalBoard->getBoardWatchTitleListById($pageIndex, $pageSize, $hidTypeId, 't_admin_id');
            $count = (int)$dalBoard->getBoardWatchTitleListCountById($hidTypeId, 't_admin_id');
            $row = Admin_Bll_User::getUserInfo($hidTypeId);
            $adminName = empty($row) ? '' : $row['name'];
        }
        else {
            echo 'error!';
            return;
        }

        //get admin user name
        foreach ($result as $key => $rowData) {
            $adminId = (int)$rowData['t_admin_id'];
            $result[$key]['admin_name'] = '';
            if (!empty($adminId)) {
                $rowAdmin = Admin_Bll_User::getUserInfo($adminId);
                $result[$key]['admin_name'] = empty($rowAdmin) ? '' : $rowAdmin['name'];
            }
        }

        $response = array('info' => $result, 'count' => $count, 'name' => (empty($adminName) ? '' : $adminName));
        $response = Zend_Json::encode($response);
        echo $response;
    }

    /**
     * deal watch title
     *
     */
    public function dealwatchtitleAction()
    {
        if ($this->_request->isPost()) {
            $aryIds = $this->_request->getPost('titleId');
            $aryStatus = $this->_request->getPost('selStatus');

            if (0 == count($aryIds) || count($aryIds) != count($aryStatus)) {
                echo '0';
                return;
            }

            if ($this->_isViewer) {
                echo 'false';
                return;
            }

            //combine ids and status info [$key=>$value]
            $aryStatusInfo = array();
            foreach ($aryIds as $key => $uid) {
                $aryStatusInfo[$uid] = $aryStatus[$key];
            }
            require_once 'Admin/Bll/Board.php';
            $bllBoard = new Admin_Bll_Board();
            $result = $bllBoard->dealWatchTitle($aryStatusInfo, $this->_user->uid, $this->_isWatcher);
            echo $result;
        }
    }

    /*********** watch des contents ***********/
    /**
     * list watch des
     *
     */
    public function listwatchdesAction()
    {
        //get hidden post data
        $pageIndex = (int)$this->_request->getPost('pageIndex', 1);
        $pageSize = (int)$this->_request->getPost('pageSize', 10);
        $hidSrhStatus = (int)$this->_request->getPost('hidSrhStatus');
        $hidSrhKeyword = $this->_request->getPost('hidSrhKeyword');
        $hidShowType = (int)$this->_request->getPost('hidShowType');
        $hidTypeId = $this->_request->getPost('hidTypeId');

        require_once 'Admin/Bll/User.php';
        require_once 'Admin/Dal/Board.php';
        $dalBoard = Admin_Dal_Board::getDefaultInstance();
        //show type [0-search|1-ID別コンテンツ一覧｜2-監視対象別のコンテンツ一覧|3-監視担当者別のコンテンツ一覧]
        //search show
        if (empty($hidShowType)) {
            $result = $dalBoard->getBoardWatchTitleList($pageIndex, $pageSize, $hidSrhStatus, $hidSrhKeyword, 'des_status', 'title');
            $count = (int)$dalBoard->getBoardWatchTitleListCount($hidSrhStatus, $hidSrhKeyword, 'des_status', 'introduce');
        }
        //commented user show / object user show
        else if (1 == $hidShowType || 2 == $hidShowType) {
            $result = $dalBoard->getBoardWatchTitleListById($pageIndex, $pageSize, $hidTypeId, 'uid');
            $count = (int)$dalBoard->getBoardWatchTitleListCountById($hidTypeId, 'uid');
        }
        //admin user show
        else if (3 == $hidShowType) {
            $result = $dalBoard->getBoardWatchTitleListById($pageIndex, $pageSize, $hidTypeId, 'd_admin_id');
            $count = (int)$dalBoard->getBoardWatchTitleListCountById($hidTypeId, 'd_admin_id');
            $row = Admin_Bll_User::getUserInfo($hidTypeId);
            $adminName = empty($row) ? '' : $row['name'];
        }
        else {
            echo 'error!';
            return;
        }

        //get admin user name
        foreach ($result as $key => $rowData) {
            $adminId = (int)$rowData['d_admin_id'];
            $result[$key]['admin_name'] = '';
            if (!empty($adminId)) {
                $rowAdmin = Admin_Bll_User::getUserInfo($adminId);
                $result[$key]['admin_name'] = empty($rowAdmin) ? '' : $rowAdmin['name'];
            }
        }

        $response = array('info' => $result, 'count' => $count, 'name' => (empty($adminName) ? '' : $adminName));
        $response = Zend_Json::encode($response);
        echo $response;
    }

    /**
     * deal watch des
     *
     */
    public function dealwatchdesAction()
    {
        if ($this->_request->isPost()) {
            $aryIds = $this->_request->getPost('desId');
            $aryStatus = $this->_request->getPost('selStatus');

            if (0 == count($aryIds) || count($aryIds) != count($aryStatus)) {
                echo '0';
                return;
            }

            if ($this->_isViewer) {
                echo 'false';
                return;
            }

            //combine ids and status info [$key=>$value]
            $aryStatusInfo = array();
            foreach ($aryIds as $key => $uid) {
                $aryStatusInfo[$uid] = $aryStatus[$key];
            }
            require_once 'Admin/Bll/Board.php';
            $bllBoard = new Admin_Bll_Board();
            $result = $bllBoard->dealWatchDes($aryStatusInfo, $this->_user->uid, $this->_isWatcher);
            echo $result;
        }
    }

    /**
     * check is validate admin user before action
     *
     */
    function preDispatch()
    {
        require_once 'Admin/Dal/Application.php';
        $dalApp = Admin_Dal_Application::getDefaultInstance();
        $allow = $dalApp->isAppAllowedToUser('board', $this->_user->uid);
        if (!$allow) {
            $this->_request->setDispatched(true);
            echo 'You Have Not Allow To View This Page!!';
            exit();
        }
    }
}
<?php

/** @see Zend_Json */
require_once 'Zend/Json.php';

/**
 * Admin School Ajax Controller
 * Manager ajax operation
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create    2009/03/06    zhangxin
 */
class Admin_AjaxaschoolController extends MyLib_Zend_Controller_Action_AdminAjax
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
     * check is validate admin user before action
     *
     */
    function preDispatch()
    {
        require_once 'Admin/Dal/Application.php';
        $dalApp = Admin_Dal_Application::getDefaultInstance();
        $allow = $dalApp->isAppAllowedToUser('school', $this->_user->uid);
        if (!$allow) {
            $this->_request->setDispatched(true);
            echo 'You Have Not Allow To View This Page!!';
            exit();
        }
    }

/**
     * list watch class note
     *
     */
    public function listwatchclassnoteAction()
    {
        //get hidden post data
        $pageIndex = (int)$this->_request->getPost('pageIndex', 1);
        $pageSize = (int)$this->_request->getPost('pageSize', 10);
        $hidSrhStatus = (int)$this->_request->getPost('hidSrhStatus');
        $hidSrhKeyword = $this->_request->getPost('hidSrhKeyword');
        $hidShowType = (int)$this->_request->getPost('hidShowType');
        $hidTypeId = $this->_request->getPost('hidTypeId');

        require_once 'Admin/Bll/User.php';
        require_once 'Admin/Dal/School.php';
        $dalSchool = Admin_Dal_School::getDefaultInstance();
        //show type [0-search|1-ID別コンテンツ一覧｜2-監視対象別のコンテンツ一覧|3-監視担当者別のコンテンツ一覧]
        //search show
        if (empty($hidShowType)) {
            $result = $dalSchool->getSchoolWatchClassnoteList($pageIndex, $pageSize, $hidSrhStatus, $hidSrhKeyword);
            $count = (int)$dalSchool->getSchoolWatchClassnoteCount($hidSrhStatus, $hidSrhKeyword);
        }
        //commented user show / object user show
        else if (1 == $hidShowType || 2 == $hidShowType) {
            //$result = $dalSchool->getSchoolWatchTitleListById($pageIndex, $pageSize, $hidTypeId, 'uid');
            //$count = (int)$dalSchool->getSchoolWatchTitleListCountById($hidTypeId, 'uid');
        }
        //admin user show
        else if (3 == $hidShowType) {
            //$result = $dalSchool->getSchoolWatchTitleListById($pageIndex, $pageSize, $hidTypeId, 't_admin_id');
            //$count = (int)$dalSchool->getSchoolWatchTitleListCountById($hidTypeId, 't_admin_id');
            //$row = Admin_Bll_User::getUserInfo($hidTypeId);
            //$adminName = empty($row) ? '' : $row['name'];
        }
        else {
            echo 'error!';
            return;
        }

        //get admin user name
        foreach ($result as $key => $rowData) {
            $rowLog = $dalSchool->getSchoolWatchChangestatusLogByIdType($rowData['cid'], 5);
            $adminId = empty($rowLog) ? 0 : $rowLog['admin_id'];
            $result[$key]['admin_name'] = '';
            if (!empty($adminId)) {
                $rowAdmin = Admin_Bll_User::getUserInfo($adminId);
                $result[$key]['admin_id'] = $adminId;
                $result[$key]['admin_name'] = empty($rowAdmin) ? '' : $rowAdmin['name'];
            }
        }

        $response = array('info' => $result, 'count' => $count, 'name' => (empty($adminName) ? '' : $adminName));
        $response = Zend_Json::encode($response);
        echo $response;
    }

	/**
     * list watch topiccomment
     *
     */
    public function listwatchtopiccommentAction()
    {
        //get hidden post data
        $pageIndex = (int)$this->_request->getPost('pageIndex', 1);
        $pageSize = (int)$this->_request->getPost('pageSize', 10);
        $hidSrhStatus = (int)$this->_request->getPost('hidSrhStatus');
        $hidSrhKeyword = $this->_request->getPost('hidSrhKeyword');
        $hidShowType = (int)$this->_request->getPost('hidShowType');
        $hidTypeId = $this->_request->getPost('hidTypeId');

        require_once 'Admin/Bll/User.php';
        require_once 'Admin/Dal/School.php';
        $dalSchool = Admin_Dal_School::getDefaultInstance();
        //show type [0-search|1-ID別コンテンツ一覧｜2-監視対象別のコンテンツ一覧|3-監視担当者別のコンテンツ一覧]
        //search show
        if (empty($hidShowType)) {
            $result = $dalSchool->getSchoolWatchTopicCommentList($pageIndex, $pageSize, $hidSrhStatus, $hidSrhKeyword);
            $count = (int)$dalSchool->getSchoolWatchTopicCommentListCount($hidSrhStatus, $hidSrhKeyword);
        }
        //commented user show / object user show
        else if (1 == $hidShowType || 2 == $hidShowType) {
            //$result = $dalSchool->getSchoolWatchTitleListById($pageIndex, $pageSize, $hidTypeId, 'uid');
            //$count = (int)$dalSchool->getSchoolWatchTitleListCountById($hidTypeId, 'uid');
        }
        //admin user show
        else if (3 == $hidShowType) {
            //$result = $dalSchool->getSchoolWatchTitleListById($pageIndex, $pageSize, $hidTypeId, 't_admin_id');
            //$count = (int)$dalSchool->getSchoolWatchTitleListCountById($hidTypeId, 't_admin_id');
            //$row = Admin_Bll_User::getUserInfo($hidTypeId);
            //$adminName = empty($row) ? '' : $row['name'];
        }
        else {
            echo 'error!';
            return;
        }

        //get admin user name
        foreach ($result as $key => $rowData) {
            $rowLog = $dalSchool->getSchoolWatchChangestatusLogByIdType($rowData['comment_id'], 2);
            $adminId = empty($rowLog) ? 0 : $rowLog['admin_id'];
            $result[$key]['admin_name'] = '';
            if (!empty($adminId)) {
                $rowAdmin = Admin_Bll_User::getUserInfo($adminId);
                $result[$key]['admin_id'] = $adminId;
                $result[$key]['admin_name'] = empty($rowAdmin) ? '' : $rowAdmin['name'];
            }
        }

        $response = array('info' => $result, 'count' => $count, 'name' => (empty($adminName) ? '' : $adminName));
        $response = Zend_Json::encode($response);
        echo $response;
    }

	/**
     * list watch topic
     *
     */
    public function listwatchtopicAction()
    {
        //get hidden post data
        $pageIndex = (int)$this->_request->getPost('pageIndex', 1);
        $pageSize = (int)$this->_request->getPost('pageSize', 10);
        $hidSrhStatus = (int)$this->_request->getPost('hidSrhStatus');
        $hidSrhKeyword = $this->_request->getPost('hidSrhKeyword');
        $hidShowType = (int)$this->_request->getPost('hidShowType');
        $hidTypeId = $this->_request->getPost('hidTypeId');

        require_once 'Admin/Bll/User.php';
        require_once 'Admin/Dal/School.php';
        $dalSchool = Admin_Dal_School::getDefaultInstance();
        //show type [0-search|1-ID別コンテンツ一覧｜2-監視対象別のコンテンツ一覧|3-監視担当者別のコンテンツ一覧]
        //search show
        if (empty($hidShowType)) {
            $result = $dalSchool->getSchoolWatchTopicList($pageIndex, $pageSize, $hidSrhStatus, $hidSrhKeyword);
            $count = (int)$dalSchool->getSchoolWatchTopicListCount($hidSrhStatus, $hidSrhKeyword);
        }
        //commented user show / object user show
        else if (1 == $hidShowType || 2 == $hidShowType) {
            //$result = $dalSchool->getSchoolWatchTitleListById($pageIndex, $pageSize, $hidTypeId, 'uid');
            //$count = (int)$dalSchool->getSchoolWatchTitleListCountById($hidTypeId, 'uid');
        }
        //admin user show
        else if (3 == $hidShowType) {
            //$result = $dalSchool->getSchoolWatchTitleListById($pageIndex, $pageSize, $hidTypeId, 't_admin_id');
            //$count = (int)$dalSchool->getSchoolWatchTitleListCountById($hidTypeId, 't_admin_id');
            //$row = Admin_Bll_User::getUserInfo($hidTypeId);
            //$adminName = empty($row) ? '' : $row['name'];
        }
        else {
            echo 'error!';
            return;
        }

        //get admin user name
        foreach ($result as $key => $rowData) {
            $rowLog = $dalSchool->getSchoolWatchChangestatusLogByIdType($rowData['tid'], 1);
            $adminId = empty($rowLog) ? 0 : $rowLog['admin_id'];
            $result[$key]['admin_name'] = '';
            if (!empty($adminId)) {
                $rowAdmin = Admin_Bll_User::getUserInfo($adminId);
                $result[$key]['admin_id'] = $adminId;
                $result[$key]['admin_name'] = empty($rowAdmin) ? '' : $rowAdmin['name'];
            }
        }

        $response = array('info' => $result, 'count' => $count, 'name' => (empty($adminName) ? '' : $adminName));
        $response = Zend_Json::encode($response);
        echo $response;
    }

    /**
     * list watch enquiry comment
     *
     */
	public function listwatchenquirycommentAction()
    {
        //get hidden post data
        $pageIndex = (int)$this->_request->getPost('pageIndex', 1);
        $pageSize = (int)$this->_request->getPost('pageSize', 10);
        $hidSrhStatus = (int)$this->_request->getPost('hidSrhStatus');
        $hidSrhKeyword = $this->_request->getPost('hidSrhKeyword');
        $hidShowType = (int)$this->_request->getPost('hidShowType');
        $hidTypeId = $this->_request->getPost('hidTypeId');

        require_once 'Admin/Bll/User.php';
        require_once 'Admin/Dal/School.php';
        $dalSchool = Admin_Dal_School::getDefaultInstance();
        //show type [0-search|1-ID別コンテンツ一覧｜2-監視対象別のコンテンツ一覧|3-監視担当者別のコンテンツ一覧]
        //search show
        if (empty($hidShowType)) {
            $result = $dalSchool->getSchoolWatchEnquiryCommentList($pageIndex, $pageSize, $hidSrhStatus, $hidSrhKeyword);
            $count = (int)$dalSchool->getSchoolWatchEnquiryCommentListCount($hidSrhStatus, $hidSrhKeyword);
        }
        //commented user show / object user show
        else if (1 == $hidShowType || 2 == $hidShowType) {
            //$result = $dalSchool->getSchoolWatchTitleListById($pageIndex, $pageSize, $hidTypeId, 'uid');
            //$count = (int)$dalSchool->getSchoolWatchTitleListCountById($hidTypeId, 'uid');
        }
        //admin user show
        else if (3 == $hidShowType) {
            //$result = $dalSchool->getSchoolWatchTitleListById($pageIndex, $pageSize, $hidTypeId, 't_admin_id');
            //$count = (int)$dalSchool->getSchoolWatchTitleListCountById($hidTypeId, 't_admin_id');
            //$row = Admin_Bll_User::getUserInfo($hidTypeId);
            //$adminName = empty($row) ? '' : $row['name'];
        }
        else {
            echo 'error!';
            return;
        }

        //get admin user name
        foreach ($result as $key => $rowData) {
            $rowLog = $dalSchool->getSchoolWatchChangestatusLogByIdType($rowData['comment_id'], 4);
            $adminId = empty($rowLog) ? 0 : $rowLog['admin_id'];
            $result[$key]['admin_name'] = '';
            if (!empty($adminId)) {
                $rowAdmin = Admin_Bll_User::getUserInfo($adminId);
                $result[$key]['admin_id'] = $adminId;
                $result[$key]['admin_name'] = empty($rowAdmin) ? '' : $rowAdmin['name'];
            }
        }

        $response = array('info' => $result, 'count' => $count, 'name' => (empty($adminName) ? '' : $adminName));
        $response = Zend_Json::encode($response);
        echo $response;
    }

	/**
     * list watch enquiry comment
     *
     */
	public function listwatchenquiryAction()
    {
        //get hidden post data
        $pageIndex = (int)$this->_request->getPost('pageIndex', 1);
        $pageSize = (int)$this->_request->getPost('pageSize', 10);
        $hidSrhStatus = (int)$this->_request->getPost('hidSrhStatus');
        $hidSrhKeyword = $this->_request->getPost('hidSrhKeyword');
        $hidShowType = (int)$this->_request->getPost('hidShowType');
        $hidTypeId = $this->_request->getPost('hidTypeId');

        require_once 'Admin/Bll/User.php';
        require_once 'Admin/Dal/School.php';
        $dalSchool = Admin_Dal_School::getDefaultInstance();
        //show type [0-search|1-ID別コンテンツ一覧｜2-監視対象別のコンテンツ一覧|3-監視担当者別のコンテンツ一覧]
        //search show
        if (empty($hidShowType)) {
            $result = $dalSchool->getSchoolWatchEnquiryList($pageIndex, $pageSize, $hidSrhStatus, $hidSrhKeyword);
            $count = (int)$dalSchool->getSchoolWatchEnquiryListCount($hidSrhStatus, $hidSrhKeyword);
        }
        //commented user show / object user show
        else if (1 == $hidShowType || 2 == $hidShowType) {
            //$result = $dalSchool->getSchoolWatchTitleListById($pageIndex, $pageSize, $hidTypeId, 'uid');
            //$count = (int)$dalSchool->getSchoolWatchTitleListCountById($hidTypeId, 'uid');
        }
        //admin user show
        else if (3 == $hidShowType) {
            //$result = $dalSchool->getSchoolWatchTitleListById($pageIndex, $pageSize, $hidTypeId, 't_admin_id');
            //$count = (int)$dalSchool->getSchoolWatchTitleListCountById($hidTypeId, 't_admin_id');
            //$row = Admin_Bll_User::getUserInfo($hidTypeId);
            //$adminName = empty($row) ? '' : $row['name'];
        }
        else {
            echo 'error!';
            return;
        }

        //get admin user name
        foreach ($result as $key => $rowData) {
            $rowLog = $dalSchool->getSchoolWatchChangestatusLogByIdType($rowData['qid'], 3);
            $adminId = empty($rowLog) ? 0 : $rowLog['admin_id'];
            $result[$key]['admin_name'] = '';
            if (!empty($adminId)) {
                $rowAdmin = Admin_Bll_User::getUserInfo($adminId);
                $result[$key]['admin_id'] = $adminId;
                $result[$key]['admin_name'] = empty($rowAdmin) ? '' : $rowAdmin['name'];
            }
        }

        $response = array('info' => $result, 'count' => $count, 'name' => (empty($adminName) ? '' : $adminName));
        $response = Zend_Json::encode($response);
        echo $response;
    }

  /* deal watch topic
     *
    */
    public function dealwatchtopicAction()
    {
        if ($this->_request->isPost()) {
            $aryIds = $this->_request->getPost('tId');
            $aryStatus = $this->_request->getPost('selStatus');

            if (0 == count($aryIds) || count($aryIds) != count($aryStatus)) {
                echo 'false';
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

            require_once 'Admin/Dal/School.php';
            $dalSchool = Admin_Dal_School::getDefaultInstance();
            require_once 'Admin/Bll/School.php';
            $bllSchool = new Admin_Bll_School();

            $result = 0;
            //update status
            foreach ($aryStatusInfo as $key => $value) {
                //get curren status
                $oldStatus = $dalSchool->getTopic($key);

                //watcher not allow to deal already dealed data
                if ($this->_isWatcher && (3 <= (int)$oldStatus['status'])) {
                    continue;
                }
                //unchanged or no deal
                if (2 >= (int)$value || (int)$oldStatus['status'] == (int)$value) {
                    continue;
                }

                //save status change logs
                $aryLog = array();
                $aryLog['admin_id'] = $this->_user->uid;
                $aryLog['watch_bid'] = $key;
                $aryLog['watch_type'] = 1; //1-topic 2-topic comment 3-enquiry 4-enquiry comment 5-class common note
                $aryLog['from_status'] = (int)$oldStatus['status'];
                $aryLog['to_status'] = (int)$value;
                $aryLog['create_time'] = time();
                $dalSchool->insertSchoolWatchChangestatusLog($aryLog);

                $newInfo['status'] = $value;
                $newInfo['cid'] = $oldStatus['cid'];
                $newInfo['tid'] = $key;
                //set new status
                if ($value == 5) {
                    $newInfo['isdelete'] = 1;
                    if ($oldStatus['isdelete']) {
						$dalSchool->updateTopic(array('status' => $value), $key);
                    } else {
						//topic_count -1
                    	$bllSchool->updateTopicCount($newInfo);
                    }
                } elseif ($oldStatus['status'] == 5 && $value < 5) {
					$newInfo['isdelete'] = 0;
					//topic_count + 1
                    $bllSchool->updateTopicCount($newInfo, 0);
                } else {
                	$dalSchool->updateTopic(array('status' => $value), $key);
                }
                $result++;
            }
            echo $result;
        }
    }

	/**
     * deal watch topiccomment
     *
     */
    public function dealwatchtopiccommentAction()
    {
        if ($this->_request->isPost()) {
            $aryIds = $this->_request->getPost('commentId');
            $aryStatus = $this->_request->getPost('selStatus');

            if (0 == count($aryIds) || count($aryIds) != count($aryStatus)) {
                echo 'false';
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

            require_once 'Admin/Dal/School.php';
            $dalSchool = Admin_Dal_School::getDefaultInstance();

            require_once 'Admin/Bll/School.php';
            $bllSchool = new Admin_Bll_School();

            $result = 0;
            //update status
            foreach ($aryStatusInfo as $key => $value) {
                //get curren status
                $oldStatus = $dalSchool->getTopicComment($key);

                //watcher not allow to deal already dealed data
                if ($this->_isWatcher && (3 <= (int)$oldStatus['status'])) {
                    continue;
                }
                //unchanged or no deal
                if (2 >= (int)$value || (int)$oldStatus['status'] == (int)$value) {
                    continue;
                }

                //save status change logs
                $aryLog = array();
                $aryLog['admin_id'] = $this->_user->uid;
                $aryLog['watch_bid'] = $key;
                $aryLog['watch_type'] = 2; //1-topic 2-topic comment 3-enquiry 4-enquiry comment 5-class common note
                $aryLog['from_status'] = (int)$oldStatus['status'];
                $aryLog['to_status'] = (int)$value;
                $aryLog['create_time'] = time();
                $dalSchool->insertSchoolWatchChangestatusLog($aryLog);

                $newInfo['status'] = $value;
                $newInfo['tid'] = $oldStatus['tid'];
                $newInfo['comment_id'] = $key;
                //set new status
                if ($value == 5) {
                    $newInfo['isdelete'] = 1;
                    if ($oldStatus['isdelete']) {
						$dalSchool->updateTopicComment(array('status' => $info['status']), $key);
                    } else {
						$bllSchool->updateCommentCount($newInfo);
                    }
                }//comment_count -1
                elseif ($oldStatus['status'] == 5 && $value < 5) {
					$newInfo['isdelete'] = 0;
					//comment_count +1
					$bllSchool->updateCommentCount($newInfo, 0);
                } else {
                	$dalSchool->updateTopicComment(array('status' => $value), $key);
                }
                $result++;
            }
            echo $result;
        }
    }

    /* deal watch enquirycomment
     *
     */
    public function dealwatchenquirycommentAction()
    {
        if ($this->_request->isPost()) {
            $aryIds = $this->_request->getPost('commentId');
            $aryStatus = $this->_request->getPost('selStatus');

            if (0 == count($aryIds) || count($aryIds) != count($aryStatus)) {
                echo 'false';
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

            require_once 'Admin/Dal/School.php';
            $dalSchool = Admin_Dal_School::getDefaultInstance();
            require_once 'Admin/Bll/School.php';
            $bllSchool = new Admin_Bll_School();

            $result = 0;
            //update status
            foreach ($aryStatusInfo as $key => $value) {
                //get curren status
                $oldStatus = $dalSchool->getEnquiryComment($key);

                //watcher not allow to deal already dealed data
                if ($this->_isWatcher && (3 <= (int)$oldStatus['status'])) {
                    continue;
                }
                //unchanged or no deal
                if (2 >= (int)$value || (int)$oldStatus['status'] == (int)$value) {
                    continue;
                }

                //save status change logs
                $aryLog = array();
                $aryLog['admin_id'] = $this->_user->uid;
                $aryLog['watch_bid'] = $key;
                $aryLog['watch_type'] = 4; //1-topic 2-topic comment 3-enquiry 4-enquiry comment 5-class common note
                $aryLog['from_status'] = (int)$oldStatus['status'];
                $aryLog['to_status'] = (int)$value;
                $aryLog['create_time'] = time();
                $dalSchool->insertSchoolWatchChangestatusLog($aryLog);

                $newInfo['status'] = $value;
                $newInfo['qid'] = $oldStatus['qid'];
                $newInfo['comment_id'] = $key;

                //set new status
                if ($value == 5) {
                    $newInfo['isdelete'] = 1;
                    if ($oldStatus['isdelete']) {
                    	$dalSchool->updateEnquiryComment(array('status' => $value), $key);
                    } else {
                    	$bllSchool->updateAnswerCount($newInfo);
                    }
                }//answer_count -1
                elseif ($oldStatus['status'] == 5 && $value < 5) {
					$newInfo['isdelete'] = 0;
					$bllSchool->updateAnswerCount($newInfo, 0);
                } else {
                	$dalSchool->updateEnquiryComment(array('status' => $value), $key);
                }
                $result++;
            }
            echo $result;
        }
    }

 /* deal watch enquirycomment
     *
     */
    public function dealwatchenquiryAction()
    {
        if ($this->_request->isPost()) {
            $aryIds = $this->_request->getPost('qId');
            $aryStatus = $this->_request->getPost('selStatus');

            if (0 == count($aryIds) || count($aryIds) != count($aryStatus)) {
                echo 'false';
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

            require_once 'Admin/Dal/School.php';
            $dalSchool = Admin_Dal_School::getDefaultInstance();
            $result = 0;
            //update status
            foreach ($aryStatusInfo as $key => $value) {
                //get curren status
                $oldStatus = $dalSchool->getEnquiry($key);

                //watcher not allow to deal already dealed data
                if ($this->_isWatcher && (3 <= (int)$oldStatus['status'])) {
                    continue;
                }
                //unchanged or no deal
                if (2 >= (int)$value || (int)$oldStatus['status'] == (int)$value) {
                    continue;
                }

                //save status change logs
                $aryLog = array();
                $aryLog['admin_id'] = $this->_user->uid;
                $aryLog['watch_bid'] = $key;
                $aryLog['watch_type'] = 3; //1-topic 2-topic comment 3-enquiry 4-enquiry comment 5-class common note
                $aryLog['from_status'] = (int)$oldStatus['status'];
                $aryLog['to_status'] = (int)$value;
                $aryLog['create_time'] = time();
                $dalSchool->insertSchoolWatchChangestatusLog($aryLog);
                //set new status
                if ($value == 5) {
                    $newInfo['isdelete'] = 1;
                }
                elseif ($oldStatus['status'] == 5 && $value < 5) {
					$newInfo['isdelete'] = 0;
                }
                $newInfo['status'] = $value;
                $dalSchool->updateEnquiry($newInfo, $key);

                $result++;
            }
            echo $result;
        }
    }

	/* deal watch classnote
     *
     */
    public function dealwatchclassnoteAction()
    {
        if ($this->_request->isPost()) {
            $aryIds = $this->_request->getPost('cId');
            $aryStatus = $this->_request->getPost('selStatus');

            if (0 == count($aryIds) || count($aryIds) != count($aryStatus)) {
                echo 'false';
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

            require_once 'Admin/Dal/School.php';
            $dalSchool = Admin_Dal_School::getDefaultInstance();
            $result = 0;
            //update status
            foreach ($aryStatusInfo as $key => $value) {
                //get curren status
                $oldStatus = $dalSchool->getClassInfo($key);

                //watcher not allow to deal already dealed data
                if ($this->_isWatcher && (3 <= (int)$oldStatus['status'])) {
                    continue;
                }
                //unchanged or no deal
                if (2 >= (int)$value || (int)$oldStatus['status'] == (int)$value) {
                    continue;
                }

                //save status change logs
                $aryLog = array();
                $aryLog['admin_id'] = $this->_user->uid;
                $aryLog['watch_bid'] = $key;
                $aryLog['watch_type'] = 5; //1-topic 2-topic comment 3-enquiry 4-enquiry comment 5-class common note
                $aryLog['from_status'] = (int)$oldStatus['status'];
                $aryLog['to_status'] = (int)$value;
                $aryLog['create_time'] = time();
                $dalSchool->insertSchoolWatchChangestatusLog($aryLog);
                //set new status
                /*if ($value == 5) {
                    $newInfo['isdelete'] = 1;
                }
                elseif ($oldStatus['status'] == 5 && $value < 5) {
					$newInfo['isdelete'] = 0;
                }*/
                $newInfo['status'] = $value;
                $dalSchool->updateClass($newInfo, $key);

                $result++;
            }
            echo $result;
        }
    }
}
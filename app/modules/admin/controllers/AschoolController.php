<?php

/**
 * Admin School Controller(modules/admin/controllers/Admin_AschoolController.php)
 * Linno Admin School Controller
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create    2009/03/06    zhangxin
 */
class Admin_AschoolController extends MyLib_Zend_Controller_Action_Admin
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
     * preDispatch
     *
     */
    function preDispatch()
    {
        require_once 'Admin/Dal/Application.php';
        $dalApp = Admin_Dal_Application::getDefaultInstance();
        $allow = $dalApp->isAppAllowedToUser('school', $this->_user->uid);
        if (!$allow) {
            $this->_forward('noauthority', 'error', 'admin', array('message' => 'You Have Not Allow To View This Page!!'));
            return;
        }
    }

    /**
     * application controller index action
     *
     */
    public function indexAction()
    {
        $this->_forward('watchtop', 'aschool', 'admin');
        return;
    }

    /**
     * aschool controller watchtop action
     *
     */
    public function watchtopAction()
    {
        require_once 'Admin/Dal/School.php';
        $mdalTopic = Admin_Dal_School::getDefaultInstance();
        $aryTopicCount = array();
        $aryTopicCommentCount = array();
        $aryEnquiryCommentCount = array();
        $aryEnquiryCount = array();
		$aryClassnoteCount = array();

        //1-未処理 2-容疑 3-問題なし 4-保留 5-違反
        for ($status = 1; $status <= 5; $status++) {
            //・授業Q&A 質問・回答
            $aryTopicCommentCount[$status] = $mdalTopic->getTopicCommentCountByStatus($status);
            //・授業Q&A 質問・title
            $aryTopicCount[$status] = $mdalTopic->getTopicCountByStatus($status);
            //・教えて!プロフ 質問・回答
            $aryEnquiryCommentCount[$status] = $mdalTopic->getEnquiryCommentByStatus($status);
            //・教えて!プロフ 質問
            $aryEnquiryCount[$status] = $mdalTopic->getEnquiryByStatus($status);
            $aryClassnoteCount[$status] = $mdalTopic->getClassnoteByStatus($status);
        }

        $this->view->lstTopicComment = $aryTopicCommentCount;
		$this->view->lstTopic = $aryTopicCount;
		$this->view->lstEnquiryComment = $aryEnquiryCommentCount;
		$this->view->lstEnquiry = $aryEnquiryCount;
		$this->view->lstClassnote = $aryClassnoteCount;

        $this->view->title = 'みんなの時間割 - コンテンツ監視トップページ｜OPENSOCIAL APPS ADMIN｜CF';
        $this->render();
    }

	/**
     * aschool controller watchtopiccomment action
     *
     */
    public function watchtopiccommentAction()
    {
        //filter ステータス
        $srhStatus = (int)$this->_request->getPost('srhStatus');
        //filter キーword
        $srhKeyword = $this->_request->getPost('srhKeyword', '');

        //show type [0-search|1-ID別コンテンツ一覧｜2-監視対象別のコンテンツ一覧|3-監視担当者別のコンテンツ一覧]
        $showType = (int)$this->_request->getPost('showType');
        $typeId = $this->_request->getPost('typeId');

        $this->view->srhStatus = $srhStatus;
        $this->view->srhKeyword = $srhKeyword;
        $this->view->showType = $showType;
        $this->view->typeId = $typeId;
        $this->view->title = 'みんなの時間割 - コンテンツ監視[掲示板のタイトル]｜OPENSOCIAL APPS ADMIN｜CF';
        $this->render();
    }

	/**
     * aschool controller watchtopic action
     *
     */
    public function watchtopicAction()
    {
        //filter ステータス
        $srhStatus = (int)$this->_request->getPost('srhStatus');
        //filter キーword
        $srhKeyword = $this->_request->getPost('srhKeyword', '');

        //show type [0-search|1-ID別コンテンツ一覧｜2-監視対象別のコンテンツ一覧|3-監視担当者別のコンテンツ一覧]
        $showType = (int)$this->_request->getPost('showType');
        $typeId = $this->_request->getPost('typeId');

        $this->view->srhStatus = $srhStatus;
        $this->view->srhKeyword = $srhKeyword;
        $this->view->showType = $showType;
        $this->view->typeId = $typeId;
        $this->view->title = 'みんなの時間割 - コンテンツ監視[掲示板のタイトル]｜OPENSOCIAL APPS ADMIN｜CF';
        $this->render();
    }

	/**
     * aschool controller watchenquiry action
     *
     */
    public function watchenquiryAction()
    {
        //filter ステータス
        $srhStatus = (int)$this->_request->getPost('srhStatus');
        //filter キーword
        $srhKeyword = $this->_request->getPost('srhKeyword', '');

        //show type [0-search|1-ID別コンテンツ一覧｜2-監視対象別のコンテンツ一覧|3-監視担当者別のコンテンツ一覧]
        $showType = (int)$this->_request->getPost('showType');
        $typeId = $this->_request->getPost('typeId');

        $this->view->srhStatus = $srhStatus;
        $this->view->srhKeyword = $srhKeyword;
        $this->view->showType = $showType;
        $this->view->typeId = $typeId;
        $this->view->title = 'みんなの時間割 - コンテンツ監視[掲示板のタイトル]｜OPENSOCIAL APPS ADMIN｜CF';
        $this->render();
    }

/**
     * aschool controller watchenquirycomment action
     *
     */
    public function watchenquirycommentAction()
    {
        //filter ステータス
        $srhStatus = (int)$this->_request->getPost('srhStatus');
        //filter キーword
        $srhKeyword = $this->_request->getPost('srhKeyword', '');

        //show type [0-search|1-ID別コンテンツ一覧｜2-監視対象別のコンテンツ一覧|3-監視担当者別のコンテンツ一覧]
        $showType = (int)$this->_request->getPost('showType');
        $typeId = $this->_request->getPost('typeId');

        $this->view->srhStatus = $srhStatus;
        $this->view->srhKeyword = $srhKeyword;
        $this->view->showType = $showType;
        $this->view->typeId = $typeId;
        $this->view->title = 'みんなの時間割 - コンテンツ監視[掲示板のタイトル]｜OPENSOCIAL APPS ADMIN｜CF';
        $this->render();
    }

	/**
     * aschool controller watchclassnote action
     *
     */
    public function watchclassnoteAction()
    {
        //filter ステータス
        $srhStatus = (int)$this->_request->getPost('srhStatus');
        //filter キーword
        $srhKeyword = $this->_request->getPost('srhKeyword', '');

        //show type [0-search|1-ID別コンテンツ一覧｜2-監視対象別のコンテンツ一覧|3-監視担当者別のコンテンツ一覧]
        $showType = (int)$this->_request->getPost('showType');
        $typeId = $this->_request->getPost('typeId');

        $this->view->srhStatus = $srhStatus;
        $this->view->srhKeyword = $srhKeyword;
        $this->view->showType = $showType;
        $this->view->typeId = $typeId;
        $this->view->title = 'みんなの時間割 - コンテンツ監視[掲示板のタイトル]｜OPENSOCIAL APPS ADMIN｜CF';
        $this->render();
    }

    public function watchclassnoteeditAction()
    {
    	$uid = $this->_user->uid;
    	$cid = (int)$this->_request->getParam('cid');
    	$step = $this->_request->getParam('step', 'start');
		require_once 'Admin/Bll/School.php';
        $bllSchool = new Admin_Bll_School();

    	if ($step == 'roolback'){
			$bllSchool->releaseLockClassCommonNote($cid, 0);
			return $this->_redirect($this->_baseUrl . '/aschool/watchclassnote');
    	}
		//check lock is time out default:15 minutes
        $drst = $bllSchool->dealCommonNoteTimeoutLock($cid, 15);
    	//check if common note is locked
        if (!$bllSchool->addLockClassCommonNote($cid, 0)) {
            $step = "error";
        }

		if ($step == 'start') {
			if ($_SESSION['school_admin_classnoteedit'] != null) {
    			$errorAry = $_SESSION['school_admin_classnoteedit'];
    			$this->view->classInfo = $errorAry;
    			$this->view->errorMsg = $errorAry['error'];

    			//clear session
    			$_SESSION['school_admin_classnoteedit'] = null;
    			unset($_SESSION['school_admin_classnoteedit']);
    		} else {
				require_once 'Admin/Dal/School.php';
	        	$dalSchool = Admin_Dal_School::getDefaultInstance();
				$classInfo = $dalSchool->getClassInfo($cid);
				$this->view->classInfo = $classInfo;
    		}
		}
    	elseif ($step == 'confirm') {
    		$txtIntroduce = $this->_request->getParam('introduce');
			$strMsg = '';
            if (empty($txtIntroduce)) {
                $strMsg .= "･ﾉｰﾄが未入力です｡";
            }
    	    else if (mb_strlen($txtIntroduce, 'UTF-8') > 30000) {
                $strMsg .= (empty($strMsg)?'':"\n") . "･30000文字以内で入力してください｡";
            }

            /*require_once 'Mbll/Emoji.php';
            $bllEmoji = new Bll_Emoji();
            $txtIntroduce = $bllEmoji->escapeEmoji($txtIntroduce);*/

            $sessionInfo = array();
            //save to session
            $sessionInfo = array('introduce' => $txtIntroduce, 'cid' => $cid);
            if (!empty($strMsg)) {
            	$sessionInfo['error'] = $strMsg;
            	$_SESSION['school_admin_classnoteedit'] = $sessionInfo;
                $this->_redirect($this->_baseUrl . '/aschool/watchclassnoteedit?CF_cid=' . $cid);
                return;
            }
            $_SESSION['school_admin_classnoteedit'] = $sessionInfo;
            //show confirm info
            $this->view->classInfo = $sessionInfo;
    	}
    	elseif ($step == 'finish') {
    		$introduce = $this->_request->getParam('introduce');
    		if ($_SESSION['school_admin_classnoteedit'] != null) {
    			$info = $_SESSION['school_admin_classnoteedit'];
    			$result = $bllSchool->editClassCommonNote($info, 0);
    			if (!$result) {
    				$step = 'error';
    			}
	    		//clear session
    			$_SESSION['school_admin_classnoteedit'] = null;
    			unset($_SESSION['school_admin_classnoteedit']);
    		}
    	}
    	$this->view->step = $step;
    	$this->view->title = 'みんなの時間割 - コンテンツ監視[掲示板のタイトル]｜OPENSOCIAL APPS ADMIN｜CF';
    	$this->render();
    }
}
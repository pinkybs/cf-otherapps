<?php

/**
 * Admin Board Controller(modules/admin/controllers/Admin_AboardController.php)
 * Linno Admin Board Controller
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create    2009/03/06    zhangxin
 */
class Admin_AboardController extends MyLib_Zend_Controller_Action_Admin
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
        $this->_forward('watchtop', 'aboard', 'admin');
        return;
    }

    /**
     * aboard controller watchtop action
     *
     */
    public function watchtopAction()
    {
        require_once 'Admin/Dal/Board.php';
        $dalBoard = Admin_Dal_Board::getDefaultInstance();
        $aryCommentCount = array();
        $aryTitleCount = array();
        $aryDesCount = array();
        //1-未処理 2-容疑 3-問題なし 4-保留 5-違反
        for ($status = 1; $status <= 5; $status++) {
            //掲示板のコメント
            $aryCommentCount[$status] = $dalBoard->getBoardWatchCommentCountByStatus($status);
            //掲示板のタイトル
            $aryTitleCount[$status] = $dalBoard->getBoardWatchTitleCountByStatus('title_status', $status);
            //掲示板の説明
            $aryDesCount[$status] = $dalBoard->getBoardWatchTitleCountByStatus('des_status', $status);
        }

        $this->view->lstComment = $aryCommentCount;
        $this->view->lstTitle = $aryTitleCount;
        $this->view->lstDes = $aryDesCount;
        $this->view->title = 'あしあと帳 - コンテンツ監視トップページ｜OPENSOCIAL APPS ADMIN｜LinNo ( リンノ )';
        $this->render();
    }

    /**
     * aboard controller forbid words setting action
     *
     */
    public function forbidwordAction()
    {
        require_once 'Admin/Dal/Board.php';
        $dalBoard = Admin_Dal_Board::getDefaultInstance();
        $this->view->lstType = $dalBoard->listForbidWordType();
        $this->view->title = 'あしあと帳 - 禁止語の設定｜OPENSOCIAL APPS ADMIN｜LinNo ( リンノ )';
        $this->render();
    }

    /**
     * aboard controller deal statistic view action
     *
     */
    public function dealstatAction()
    {
        $today = getdate();
        $beginMonth = 1;
        $beginYear = $today['year'];
        $month = $today['mon'];
        $arySelMonth = array();
        for ($i = $beginMonth; $i <= $month; $i++) {
            $arySelMonth[$beginYear . '-' . $i] = $beginYear . '年' . $i . '月';
        }

        //show select month's statistic data
        if ($this->_request->isPost()) {
            $selDate = $this->_request->getPost('selDate');
            $aryTemp = explode('-', $selDate);
            $selYear = $aryTemp[0];
            $selMonth = $aryTemp[1];
            $intDayCount = date('t', mktime(0, 0, 0, $selMonth, 1, $selYear));

            $aryCombineData = array();
            $intTotal = 0;
            require_once 'Admin/Dal/Board.php';
            $dalBoard = Admin_Dal_Board::getDefaultInstance();
            //get all admin user
            require_once 'Admin/Bll/User.php';
            $aryUser = Admin_Bll_User::getUserAll();
            foreach ($aryUser as $userData) {
                $uid = $userData['uid'];
                $aryRole = Admin_Bll_User::getUserRole($uid);
                //check allowed users
                foreach ($aryRole as $roleData) {
                    if ('viewer' == $roleData['role_name']) {
                        continue;
                    }
                    if ('watcher' == $roleData['role_name']) {
                        require_once 'Admin/Dal/Application.php';
                        $dalApp = Admin_Dal_Application::getDefaultInstance();
                        $allow = $dalApp->isAppAllowedToUser('board', $uid);
                        if (!$allow) {
                            continue;
                        }
                    }
                }

                //get user's deal count data
                $aryDealCountData = $dalBoard->getUserDailyDealtCountByMonth($uid, $selYear, $selMonth);
                if (0 == count($aryDealCountData)) {
                    continue;
                }
                $aryUserDayCount = array();
                $intUserDayTotal = 0;
                for ($day = 1; $day <= $intDayCount; $day++) {
                    $aryUserDayCount[$day] = 0;
                    foreach ($aryDealCountData as $cntData) {
                        $aryTmpDay = explode('-', $cntData['sel_year_month']);
                        if (3 == count($aryTmpDay) && $day == $aryTmpDay[2]) {
                            $aryUserDayCount[$day] = $cntData['deal_count'];
                            break;
                        }
                    }
                    $intUserDayTotal += (int)$aryUserDayCount[$day];
                } //end for days


                //userの合計(row total)
                $aryUserDayCount[] = $intUserDayTotal;
                //userの累計(col total)
                $intTotal += $intUserDayTotal;

                $aryCombineData[] = array('name' => $userData['name'], 'aryData' => $aryUserDayCount);
            } //end for each user


            //title column
            $aryDayTitle = array();
            for ($day = 1; $day <= $intDayCount; $day++) {
                $aryDayTitle[] = strlen($day) < 2 ? ('0' . $day) : $day;
            }
            $aryDayTitle[] = '合計';

            $this->view->lstTitle = $aryDayTitle;
            $this->view->lstUserData = $aryCombineData;
            $this->view->cntTotal = $intTotal;
            $this->view->selDate = $selDate;
            $this->view->selYear = $selYear;
            $this->view->selMonth = $selMonth;
        }

        $this->view->lstMonth = $arySelMonth;
        $this->view->title = 'あしあと帳 - 処理の集計｜OPENSOCIAL APPS ADMIN｜LinNo ( リンノ )';
        $this->render();
    }

    /**
     * aboard controller watchcomment action
     *
     */
    public function watchcommentAction()
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
        $this->view->title = 'あしあと帳 - コンテンツ監視[コメント]｜OPENSOCIAL APPS ADMIN｜LinNo ( リンノ )';
        $this->render();
    }

    /**
     * aboard controller watchtitle action
     *
     */
    public function watchtitleAction()
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
        $this->view->title = 'あしあと帳 - コンテンツ監視[掲示板のタイトル]｜OPENSOCIAL APPS ADMIN｜LinNo ( リンノ )';
        $this->render();
    }

    /**
     * aboard controller watchdes action
     *
     */
    public function watchdesAction()
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
        $this->view->title = 'あしあと帳 - コンテンツ監視[掲示板の説明]｜OPENSOCIAL APPS ADMIN｜LinNo ( リンノ )';
        $this->render();
    }

    /**
     * preDispatch
     *
     */
    function preDispatch()
    {
        require_once 'Admin/Dal/Application.php';
        $dalApp = Admin_Dal_Application::getDefaultInstance();
        $allow = $dalApp->isAppAllowedToUser('board', $this->_user->uid);
        if (!$allow) {
            $this->_forward('noauthority', 'error', 'admin', array('message' => 'You Have Not Allow To View This Page!!'));
            return;
        }
    }
}
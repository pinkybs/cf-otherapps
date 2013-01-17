<?php

/** @see MyLib_Zend_Controller_Action_Mobile.php */
require_once 'MyLib/Zend/Controller/Action/Mobile.php';

/**
 * Mobile Board Controller(modules/mobile/controllers/BoardController.php)
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/09/07   zhangxin
 */
class Mobile_BoardController extends MyLib_Zend_Controller_Action_Mobile
{
    protected $_pageSize = 8;

    protected $_BoardInfo;

    protected $_ownerId;

    protected $_viewerId;

    /**
     * initialize object
     * override
     * @return void
     */
    public function init()
    {
        parent::init();
    }

    /**
     * deipatch
     *
     */
    function preRender()
    {
        /*
    	require_once 'Bll/Cache/Board.php';
        Bll_Cache_Board::clearSkinBasicInfo();
        */
        //get friends count
        $aryFids = Bll_Friend::getFriends($this->_viewerId);
        $friendsCount = count($aryFids);

        require_once 'Dal/Board/Board.php';
        $dalBoard = Dal_Board_Board::getDefaultInstance();
        $commentedCount = $dalBoard->getCommentsCount($this->_ownerId);
        $this->view->commentedCount = $commentedCount;

        //get owner info
        $dalBoardUser = Dal_Board_User::getDefaultInstance();
        $ownerInfo = $dalBoardUser->getUser($this->_ownerId);

        Bll_User::appendPerson($ownerInfo, 'uid');
        $this->view->ownerInfo = $ownerInfo;

        //head picture
        $dalBoard = Dal_Board_Board::getDefaultInstance();
        $setInfo = $dalBoard->getUserSetting($this->_ownerId);

        require_once 'Bll/Board/Board.php';
        $bllBoard = new Bll_Board_Board();

        $this->view->headPic = (empty($setInfo) || empty($setInfo['image_url'])) ? '/apps/board/img/head_default.gif' : $setInfo['image_url'];
        $this->view->title = (empty($setInfo) || empty($setInfo['title']) || (!empty($setInfo['title']) && 5 == $setInfo['title_status'])) ? $ownerInfo['displayName'] . "さんのあしあと帳" : $setInfo['title'];
        $this->view->introduce = (empty($setInfo) || empty($setInfo['introduce']) || (!empty($setInfo['introduce']) && 5 == $setInfo['des_status'])) ? '' : $setInfo['introduce'];

        $this->view->setInfo = $setInfo;

        $skinId = $bllBoard->getSkinIdByUrl($setInfo['mobile_image_url'], 1);

        //get all skin basci infomation
        require_once 'Bll/Cache/Board.php';
        $skinInfo = Bll_Cache_Board::getSkinBasicInfo();

        $userSkinInfo = $skinInfo[$skinId - 1];

        $designSetting = array('linkText' => $userSkinInfo['link_text'],
                               'normalText' => $userSkinInfo['normal_text'],
                               'reverseText' => $userSkinInfo['reverse_text'],
                               'bgColor' => $userSkinInfo['bg_color'],
                               'bgImage' => $userSkinInfo['bg_image'],
                               'tplHeadPath' => $userSkinInfo['tpl_head_path'],
                               'tplHeadWidth' => $userSkinInfo['tpl_head_width'],
                               'tplHeadHeight' => $userSkinInfo['tpl_head_height'],
                               'tplFootPath' => $userSkinInfo['tpl_foot_path'],
                               'tplFootWidth' => $userSkinInfo['tpl_foot_width'],
                               'tplFootHeight' => $userSkinInfo['tpl_foot_height'],
                               'tplColor_i' => $userSkinInfo['tpl_color_i'],
                               'tplColor_ii' => $userSkinInfo['tpl_color_ii'],
                               'listColorA' => $userSkinInfo['list_colorA'],
                               'listColorB' => $userSkinInfo['list_colorB']);

        $this->view->designSetting = $designSetting;
        $this->view->friendsCount = $friendsCount;
        $this->view->skinId = $skinId;
        $this->view->ownerId = $this->_ownerId;
        $this->view->viewerId = $this->_viewerId;
        $this->view->ua = Zend_Registry::get('ua');
    }

    /**
     * index Action
     *
     */
    public function indexAction()
    {
        /*require_once 'Mbll/Application/Plugin/Board.php';
        $bllPlugin = new Mbll_Application_Plugin_Board();
        $bllPlugin->postUpdatePerson($this->_user->getId());
		*/
        return $this->_forward('list');
    }

    /**
     * home action
     *
     */
    public function listAction()
    {
        $this->_viewerId = $this->_user->getId();
        $this->_ownerId = $this->getParam('CF_uid', $this->_viewerId);

        $pageIndex = $this->getParam('CF_page_index', 1);
        $pageSize = 5;
        $sort = $this->getParam('CF_sort', 'desc');

        //display the user's introduce
        $intrDispFlag = $this->getParam('CF_page_index') ? 1 : 0;
        $this->view->intrDispFlag = $intrDispFlag;

        require_once 'Dal/Board/Board.php';
        $dalBoard = Dal_Board_Board::getDefaultInstance();
        $array = $dalBoard->getComments($this->_ownerId, $pageIndex, $pageSize, $sort);
        $count = $dalBoard->getCommentsCount($this->_ownerId);

        $this->view->boardCount = $count;

        $this->view->pager = array('count' => $count, 'pageIndex' => $pageIndex, 'requestUrl' => 'mobile/board/list?CF_uid=' . $this->_ownerId . '&CF_sort=' . $sort, 'pageSize' => $pageSize, 'maxPager' => ceil($count / $pageSize));

        if ($count && !empty($array)) {
            $countPerPage = count($array);

            $startCount = ($pageIndex - 1) * $pageSize + 1;
            $endCount = $startCount + $countPerPage - 1;
            $listCount = array('startCount' => $startCount, 'endCount' => $endCount);

            //display no
            for ($i = 0; $i < $countPerPage; $i++) {
                if ('desc' == $sort) {
                    $array[$i]['no'] = $count - (($pageIndex - 1) * $pageSize + $i);
                }
                else if ('asc' == $sort) {
                    $array[$i]['no'] = ($pageIndex - 1) * $pageSize + $i + 1;
                }
            }

            require_once 'Bll/User.php';
            $bllUser = new Bll_User();
            $bllUser->appendPeople($array, 'comment_uid');
        }

        $viewerSetInfo = $dalBoard->getUserSetting($this->_viewerId);

        require_once 'Bll/Board/Board.php';
        $bllBoard = new Bll_Board_Board();
        $viewerSkinId = $bllBoard->getSkinIdByUrl($viewerSetInfo['mobile_image_url'], 1);

        $this->view->boardList = $array;
        $this->view->count = $count;
        $this->view->listCount = $listCount;
        $this->view->pageIndex = $pageIndex;
        $this->view->viewerSkinId = $viewerSkinId;

        if ($sort == 'asc') {
            $sort = 'desc';
        }
        else {
            $sort = 'asc';
        }
        $this->view->sort = $sort;

        $this->render();
    }

    /**
     * get history list
     *
     */
    public function historyAction()
    {
        $this->_viewerId = $this->_user->getId();
        $this->_ownerId = $this->getParam('CF_uid', $this->_viewerId);

        $pageIndex = $this->getParam('CF_page_index', 1);
        $pageSize = 5;
        $sort = $this->getParam('CF_sort', 'desc');

        //display the user's introduce
        $intrDispFlag = $this->getParam('CF_page_index') ? 1 : 0;
        $this->view->intrDispFlag = $intrDispFlag;

        require_once 'Dal/Board/Board.php';
        $dalBoard = Dal_Board_Board::getDefaultInstance();
        $array = $dalBoard->getHistoryList($this->_ownerId, $pageIndex, $pageSize, $sort);
        $count = $dalBoard->getHistoryCount($this->_ownerId);

        $this->view->pager = array('count' => $count, 'pageIndex' => $pageIndex, 'requestUrl' => 'mobile/board/history?CF_uid=' . $this->_ownerId . '&CF_sort=' . $sort, 'pageSize' => $pageSize, 'maxPager' => ceil($count / $pageSize));

        if ($count) {
            $countPerPage = count($array);

            $startCount = ($pageIndex - 1) * $pageSize + 1;
            $endCount = $startCount + $countPerPage - 1;
            $listCount = array('startCount' => $startCount, 'endCount' => $endCount);

            //display no
            for ($i = 0; $i < $countPerPage; $i++) {
                $target = Bll_User::getPerson($array[$i]['uid']);
                $array[$i]['uidName'] = $target->getDisplayName();

                if ('desc' == $sort) {
                    $array[$i]['no'] = $count - (($pageIndex - 1) * $pageSize + $i);
                }
                else if ('asc' == $sort) {
                    $array[$i]['no'] = ($pageIndex - 1) * $pageSize + $i + 1;
                }
            }

            require_once 'Bll/User.php';
            $bllUser = new Bll_User();
            $bllUser->appendPeople($array, 'comment_uid');

            foreach ($array as $key => $value) {
            }
        }

        $this->view->boardHistoryList = $array;
        $this->view->count = $count;
        $this->view->listCount = $listCount;
        $this->view->pageIndex = $pageIndex;

        if ($sort == 'asc') {
            $sort = 'desc';
        }
        else {
            $sort = 'asc';
        }
        $this->view->sort = $sort;

        $this->render();

    }

    public function friendlistAction()
    {
        $this->_viewerId = $this->_user->getId();
        $this->_ownerId = $this->getParam('CF_uid', $this->_viewerId);

        $pageIndex = $this->getParam('CF_page_index', 1);
        $pageSize = 10;

        $aryFids = Bll_Friend::getFriends($this->_viewerId);
        $maxCount = count($aryFids);

        require_once 'Bll/Board/Board.php';
        $bllBoard = Bll_Board_Board::getDefaultInstance();
        $array = $bllBoard->getFriendList($aryFids, $this->_viewerId, $pageIndex, $pageSize);

        $countPerPage = count($array);
        $startCount = ($pageIndex - 1) * $pageSize + 1;
        $endCount = $startCount + $countPerPage - 1;
        $listCount = array('startCount' => $startCount, 'endCount' => $endCount);

        $this->view->pager = array('count' => $maxCount, 'pageIndex' => $pageIndex, 'requestUrl' => 'mobile/board/friendlist?CF_uid=' . $this->_viewerId, 'pageSize' => $pageSize, 'maxPager' => ceil($maxCount / $pageSize));

        $this->view->friendList = $array;
        $this->view->count = $maxCount;
        $this->view->listCount = $listCount;
        $this->view->pageIndex = $pageIndex;

        $this->render();
    }

    /**
     *　add
     *
     */
    public function addAction()
    {
        $this->_viewerId = $this->_user->getId();
        $this->_ownerId = $this->getParam('CF_uid', $this->_viewerId);

        $step = $this->getParam('CF_step', 'start');
        $this->view->step = $step;

        $board = array('content' => '', 'errcomment' => '');

        if ($step == "start") {
            if ($_SESSION['board_add'] != null) {
                $board = $_SESSION['board_add'];
            }
            $this->view->board = $board;
            $this->view->url = urlencode($this->_baseUrl . '/mobile/board/add?CF_step=confirm');
            $this->view->cancelUrl = urlencode($this->_baseUrl . '/mobile/board');
        }
        else if ($step == 'confirm') {
            $content = $this->getParam('postComment');

            $board = array('content' => $content);

            //if no comment
            if ("" == trim($content)) {
                $board['errcomment'] = 1;
            }

            //check content length,if length over 100
            $truncateContent = MyLib_String::truncate($content, 100);
            if ($truncateContent != $content) {
                $board['errcomment'] = 2;
            }

            //convert emoji to the format like [i/e/s:x]
            require_once 'Mbll/Emoji.php';
            $bllEmoji = new Bll_Emoji();
            $content = $bllEmoji->escapeEmoji($content);

            $_SESSION['board_add'] = $board;

            if (1 == $board['errcomment'] || 2 == $board['errcomment']) {
                $this->_redirect($this->_baseUrl . "/mobile/board/add?CF_uid=" . $this->_ownerId);
            }

            $this->view->confirmContent = $content;
            $this->view->board = $board;
            $this->view->url = urlencode($this->_baseUrl . '/mobile/board/add?CF_step=complete&CF_uid=' . $this->_ownerId);
            $this->view->cancelUrl = urlencode($this->_baseUrl . '/mobile/board/add?CF_step=start&CF_uid=' . $this->_ownerId);
        }
        else if ($step == "complete") {
            $content = $this->getParam('postComment');

            $boardInfo = array('uid' => $this->_ownerId, 'comment_uid' => $this->_viewerId, 'content' => $content, 'create_time' => date('Y-m-d H:i:s'));

            //new board
            require_once 'Bll/Board/Board.php';
            $bllBoard = Bll_Board_Board::getDefaultInstance();
            $addResult = $bllBoard->newBoard($boardInfo, 1);

            //send activity
            $title = $addResult['activity'];
            if (!empty($title)) {
                require_once 'Bll/Restful.php';
                //get restful object
                $restful = Bll_Restful::getInstance($this->_ownerId, $this->_APP_ID);
                $restful->createActivity(array('title' => $title), $this->_ownerId);
            }
            //clear session
            $_SESSION['board_add'] = null;
            unset($_SESSION['board_add']);
        }

        $this->render();
    }

    /**
     *　delete
     *
     */
    public function deleteAction()
    {
        $this->_viewerId = $this->_user->getId();
        $this->_ownerId = $this->getParam('CF_uid', $this->_viewerId);

        $bid = $this->getParam('CF_bid');

        $step = $this->getParam('CF_step', 'confirm');
        $this->view->step = $step;

        $this->view->url = urlencode($this->_baseUrl . '/mobile/board/delete?CF_step=complete');

        if ($step == "confirm") {
            require_once 'Bll/Board/Board.php';
            $bllBoard = Bll_Board_Board::getDefaultInstance();
            $board = $bllBoard->getCommentInfo($bid);

            $this->view->board = $board;
            $this->view->cancelUrl = urlencode($this->_baseUrl . '/mobile/board');
        }
        //if step is confirm
        else if ($step == "complete") {
            $bid = $this->getPost('bid');
            $this->_ownerId = $this->getPost('uid');

            require_once 'Dal/Board/Board.php';
            $dalBoard = Dal_Board_Board::getDefaultInstance();
            $result = $dalBoard->deleteBoard($bid);
        }

        $this->render();
    }

    public function introduceAction()
    {
        $this->_viewerId = $this->_user->getId();
        $this->_ownerId = $this->getParam('CF_uid', $this->_viewerId);

        //$subject = $shopData['shop_name'] . "[リンノクーポン]";
        $boardUrl = Zend_Registry::get('host') . '/mobile/board?CF_uid=' . $this->_ownerId;
        $body = 'http://mixi.jp/run_appli.pl?id=4011&owner_id=' . $this->_ownerId;
        $this->view->url = $body;
        //urlencode
        //$mailSubject = urlencode(mb_convert_encoding($subject, 'SJIS', 'auto')); //SJIS(Shift_JIS)


        $mailBody = urlencode(mb_convert_encoding($body, 'SJIS', 'auto'));

        $this->view->mail = 'mailto:?body=' . $mailBody;

        $this->render();
    }

    /**
     * profile action
     *
     */
    public function helpAction()
    {
        $this->_viewerId = $this->_user->getId();
        $this->_ownerId = $this->getParam('CF_uid', $this->_viewerId);

        $this->render();
    }

    /**
     * error action
     *
     */
    public function errorAction()
    {
        $this->render();
    }

    /**
     * magic function
     *   if call the function is undefined,then forward to not found
     *
     * @param string $methodName
     * @param array $args
     * @return void
     */
    function __call($methodName, $args)
    {
        return $this->_redirect($this->_baseUrl . '/mobile/Board/error');
    }

    /**
     * show design init page
     */
    public function designAction()
    {
        $uid = $this->_user->getId();

        $this->_viewerId = $uid;
        $this->_ownerId = $uid;

        require_once 'Bll/Cache/Board.php';
        $skinInfo = Bll_Cache_Board::getSkinBasicInfo();

        $skinInfo = array('1' => array('name' => $skinInfo[0]['skin_name'], 'pic' => $skinInfo[0]['skin_id']), '2' => array('name' => $skinInfo[1]['skin_name'], 'pic' => $skinInfo[1]['skin_id']), '3' => array('name' => $skinInfo[2]['skin_name'], 'pic' => $skinInfo[2]['skin_id']), '4' => array('name' => $skinInfo[3]['skin_name'], 'pic' => $skinInfo[3]['skin_id']), '5' => array('name' => $skinInfo[4]['skin_name'], 'pic' => $skinInfo[4]['skin_id']), '6' => array('name' => $skinInfo[5]['skin_name'], 'pic' => $skinInfo[5]['skin_id']));

        $this->view->skinInfo = $skinInfo;

        $this->render();
    }

    /**
     * preview design
     */
    public function designconfirmAction()
    {
        $uid = $this->_user->getId();

        $this->_viewerId = $uid;
        $this->_ownerId = $uid;

        $selectSkin = $this->getParam("CF_skin");

        require_once 'Bll/Cache/Board.php';
        $skinInfo = Bll_Cache_Board::getSkinBasicInfo();

        $newSkinInfo = $skinInfo[$selectSkin - 1];

        $this->view->newSkinInfo = $newSkinInfo;
        $this->view->selectSkin = $selectSkin;

        $this->render();
    }

    /**
     *  design confirm
     */
    public function designcompleteAction()
    {
        $uid = $this->_user->getId();

        $this->_viewerId = $uid;
        $this->_ownerId = $uid;

        $selectSkin = $this->getParam("CF_skin");

        //change skin
        require_once 'Bll/Board/Board.php';
        $bllBoard = new Bll_Board_Board();
        $bllBoard->changeSkin($uid, $selectSkin);

        $this->render();
    }

    /**
     *  show set title and introduce init page
     */
    public function settingAction()
    {

        $uid = $this->getPost("CF_uid", $this->_user->getId());

        $this->_viewerId = $uid;
        $this->_ownerId = $uid;

        $this->view->url = urlencode($this->_baseUrl . '/mobile/board/settingconfirm');
        $this->view->cancelUrl = urlencode($this->_baseUrl . '/mobile/board');
        //$this->view->ownerId = $uid;
        $this->render();
    }

    /**
     *  confirm title and introduce
     */
    public function settingconfirmAction()
    {
        $uid = $this->_user->getId();

        $this->_viewerId = $uid;
        $this->_ownerId = $uid;

        $postTitle = $this->getParam("postTitle");
        $postDescription = $this->getParam("postDescription");

        $truncatePostTitle = MyLib_String::truncate($postTitle, 20);
        if (empty($postTitle) || ($truncatePostTitle != $postTitle)) {
            $this->_redirect($this->_baseUrl . '/mobile/Board/settingerror?CF_arrTitle=' . $postTitle . '&CF_arrContent=' . $postDescription);
        }

        require_once 'Mbll/Emoji.php';
        $bllEmoji = new Bll_Emoji();

        //convert emoji to the format like [i/e/s:x], param true->delete emoji
        $escapedTitle = $bllEmoji->escapeEmoji($postTitle, true);

        if (empty($escapedTitle)) {
            $this->_redirect($this->_baseUrl . '/mobile/Board/settingerror?CF_arrTitle=' . $postTitle . '&CF_arrContent=' . $postDescription);
        }

        $postDescription = MyLib_String::truncate($postDescription, 100);

        $escapedDescription = $bllEmoji->escapeEmoji($postDescription);

        $this->view->postTitle = $escapedTitle;
        $this->view->postDescription = $escapedDescription;
        $this->view->url = urlencode($this->_baseUrl . '/mobile/board/settingcomplete');
        $this->view->cancelUrl = urlencode($this->_baseUrl . '/mobile/board/setting');
        $this->view->cancelUid = $uid;

        $this->render();
    }

    /**
     *  set arror
     */
    public function settingerrorAction()
    {
        $uid = $this->_user->getId();
        $errorTitle = $this->getParam("CF_arrTitle");
        $errorContent = $this->getParam("CF_arrContent");

        $this->_viewerId = $uid;
        $this->_ownerId = $uid;
        $this->view->url = urlencode($this->_baseUrl . '/mobile/board/settingconfirm');
        $this->view->cancelUrl = urlencode($this->_baseUrl . '/mobile/board');
        $this->view->errorTitle = $errorTitle;
        $this->view->errorContent = $errorContent;

        $this->render();
    }

    /**
     *  set complete
     */
    public function settingcompleteAction()
    {
        $uid = $this->_user->getId();
        $postTitle = $this->getParam("CF_postTitle");
        $postDescription = $this->getParam("CF_postDescription");

        $this->_viewerId = $uid;
        $this->_ownerId = $uid;

        require_once 'Bll/Board/Board.php';
        $bllBoard = new Bll_Board_Board();
        $bllBoard->settingTitleAndIntroduce($uid, $postTitle, $postDescription);

        $this->render();
    }

}
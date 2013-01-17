<?php
/** @see Zend_Json */
require_once 'Zend/Json.php';
/** @see MyLib_Zend_Controller_Action_Ajax */
require_once 'MyLib/Zend/Controller/Action/Ajax.php';

/**
 * Board Ajax Controllers
 * new board
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/02/10   Liz
 */
class Ajax_BoardController extends MyLib_Zend_Controller_Action_Ajax
{

    /**
     * new
     *
     */
    public function newAction()
    {
        if ($this->_request->isPost()) {
            $to_id = $this->_request->getPost('ownerId');
            $txtContent = $this->_request->getPost('txtContent');

            $uid = $this->_user->getId();

            //if $content is null ,redirect return url
            if (rtrim($txtContent) == "") {
                echo 'false';
                return;
            }

            if (!$to_id) {
                echo 'false';
                return;
            }

            $boardInfo = array('uid' => $to_id, 'comment_uid' => $uid, 'content' => $txtContent, 'create_time' => date('Y-m-d H:i:s'));

            //new board
            $bllBoard = Bll_Board_Board::getDefaultInstance();
            $result = $bllBoard->newBoard($boardInfo);

            echo Zend_Json::encode($result);
        }
    }

    /**
     * new
     *
     */
    public function newimageAction()
    {
        if ($this->_request->isPost()) {
            $bllBoard = Bll_Board_Board::getDefaultInstance();

            //get to uid
            $to_id = $this->_request->getPost('touser');
            $uid = $this->_user->getId();

            // Base64 から元のバイナリデータへデコードして受け取り
            $img = base64_decode($this->_request->getParam('img'));

            $filename = $this->_request->getPost('filename');

            $basePhotoUrl = $this->_photoBasePath . "/apps/board/flash/rakugaki/";
            $mobileBasePhotoUrl = $this->_photoBasePath . "/apps/board/flash/mobile/rakugaki/";
            $saveFolder = $bllBoard->getSaveFolder($basePhotoUrl, $mobileBasePhotoUrl);
            //$mobileSaveFolder = $bllBoard->getSaveFolder($mobileBasePhotoUrl);

            // ファイル名の受け取り
            $picUrl = $basePhotoUrl . $saveFolder . '/' . $filename . ".png";

            $mPicUrl = $this->_photoBasePath . '/apps/board/flash/mobile/rakugaki/' . $saveFolder . '/' . $filename . ".gif";

            $boardInfo = array('uid' => $to_id,
                               'comment_uid' => $uid,
                               'content' => '',
                               'pic_url' => "/rakugaki/" . $saveFolder . '/' . $this->_request->getPost('filename') . ".png",
                               'mobile_pic_url' => "/mobile/rakugaki/" . $saveFolder . '/' . $this->_request->getPost('filename') . ".gif",
                               'type' => 1,
                               'create_time' => date('Y-m-d H:i:s'));

            //new board
            $result = $bllBoard->newImage($boardInfo, $img, $picUrl, $mPicUrl);
            echo Zend_Json::encode($result);
        }
    }

    /**
     * get board list
     *
     */
    public function getboardlistAction()
    {

        $id = $this->_request->getParam('id');
        $page = $this->_request->getParam('pageIndex');

        $pageSize = 10;
        require_once 'Dal/Board/Board.php';
        $dalBoard = Dal_Board_Board::getDefaultInstance();
        $array = $dalBoard->getComments($id, $page, $pageSize);
        $count = $dalBoard->getCommentsCount($id);

        if ($count) {

            $startCount = ($page - 1) * $pageSize + 1;
            if (count($array) == '10') {
                $endCount = $page * $pageSize;
            }
            else {
                $endCount = $startCount + count($array) - 1;
            }
            $listCount = array('startCount' => $startCount, 'endCount' => $endCount);

            require_once 'Bll/Board/Board.php';
            $bllBoard = Bll_Board_Board::getDefaultInstance();

            foreach ($array as $key => $value) {
                $newContent = $bllBoard->removeEmoji($value['content']);
                $array[$key]['content'] = $newContent;
            }

            require_once 'Bll/User.php';
            $bllUser = new Bll_User();
            $bllUser->appendPeople($array, 'comment_uid');
        }

        $response = array('info' => $array, 'count' => $count, 'listCount' => $listCount, 'uid' => $this->_user->getId());
        $response = Zend_Json::encode($response);

        echo $response;
    }

    /**
     * delete a board by board id
     *
     */
    public function deleteAction()
    {

        $bownerId = $this->_request->getParam('bownerId');
        $pageName = $this->_request->getParam('pageName');

        require_once 'Dal/Board/Board.php';
        $dalBoard = Dal_Board_Board::getDefaultInstance();
        $result = $dalBoard->deleteBoard($this->_request->getParam('id'));

        $response = array('result' => $result ? 'true' : 'flase', 'bownerId' => $bownerId, 'pageName' => $pageName);

        $response = Zend_Json::encode($response);

        echo $response;
    }

    /**
     * get contact list
     *
     */
    public function getminicontactlistAction()
    {
        $request = $this->_request->getParam('request');
        //decode $request with type=TYPE_OBJECT
        $request = Zend_Json::decode($request, Zend_Json::TYPE_OBJECT);

        require_once 'Bll/Board/Board.php';
        $bllBoard = Bll_Board_Board::getDefaultInstance();
        $arrContactList = $bllBoard->getMiniContactList($request->viewerid, $request->ownerid, $request->contactnumperpage);

        $response = array('info' => $arrContactList['contactUids'], 'maxCount' => $arrContactList['maxCount'], 'pageindex' => $arrContactList['pageindex'], 'maxPage' => $arrContactList['maxPage']);
        $response = Zend_Json::encode($response);

        echo $response;
    }

    /**
     * get contact list
     *
     */
    public function getmorecontactlistAction()
    {
        $viewerid = $this->_request->getParam('viewerid');
        $type = $this->_request->getParam('type');
        $contactPageIndex = $this->_request->getParam('contactPageIndex');
        $contactnumperpage = $this->_request->getParam('contactnumperpage');

        require_once 'Bll/Board/Board.php';
        $bllBoard = Bll_Board_Board::getDefaultInstance();
        $arrContactList = $bllBoard->getMoreContactList($viewerid, $type, $contactPageIndex, $contactnumperpage);

        $response = array('info' => $arrContactList['contactUids'], 'rightCount' => $arrContactList['rightCount'], 'leftCount' => $arrContactList['leftCount']);

        $response = Zend_Json::encode($response);

        echo $response;
    }

    /**
     * get contact list
     *
     */
    public function getcontactlistAction()
    {
        $request = $this->_request->getParam('request');
        //decode $request with type=TYPE_OBJECT
        $request = Zend_Json::decode($request, Zend_Json::TYPE_OBJECT);

        $dalBoard = Dal_Board_Board::getDefaultInstance();
        $array = $dalBoard->getContactList($request->id, $request->page, $request->pageSize);
        $count = $dalBoard->getContactCount($request->id);

        if ($count) {
            $pageSize = 10;
            $startCount = ($request->page - 1) * $pageSize + 1;
            if (count($array) == '10') {
                $endCount = $request->page * $pageSize;
            }
            else {
                $endCount = $startCount + count($array) - 1;
            }
            $listCount = array('startCount' => $startCount, 'endCount' => $endCount);

            require_once 'Bll/User.php';
            $bllUser = new Bll_User();
            $bllUser->appendPeople($array, 'comment_uid');
        }

        $response = array('info' => $array, 'count' => $count, 'listCount' => $listCount, 'uid' => $this->_user->getId());
        $response = Zend_Json::encode($response);

        echo $response;
    }

    /**
     * get contact list
     *
     */
    public function gethistorylistAction()
    {

        $id = $this->_request->getParam('id');
        $page = $this->_request->getParam('page');
        $pageSize = $this->_request->getParam('pageSize');

        require_once 'Dal/Board/Board.php';
        $dalBoard = Dal_Board_Board::getDefaultInstance();
        $array = $dalBoard->getHistoryList($id, $page, $pageSize);
        $count = $dalBoard->getHistoryCount($id);

        if ($count) {

            $startCount = ($page - 1) * $pageSize + 1;
            if (count($array) == '10') {
                $endCount = $page * $pageSize;
            }
            else {
                $endCount = $startCount + count($array) - 1;
            }
            $listCount = array('startCount' => $startCount, 'endCount' => $endCount);

            require_once 'Bll/Board/Board.php';
            $bllBoard = Bll_Board_Board::getDefaultInstance();

            foreach ($array as $key => $value) {
                $newContent = $bllBoard->removeEmoji($value['content']);
                $array[$key]['content'] = $newContent;
            }

            require_once 'Bll/User.php';
            $bllUser = new Bll_User();
            $bllUser->appendPeople($array, 'comment_uid');

            foreach ($array as $key => $value) {
                $target = Bll_User::getPerson($value['uid']);
                $array[$key]['targetName'] = $target->getDisplayName();
            }
        }

        $response = array('info' => $array, 'count' => $count, 'listCount' => $listCount, 'uid' => $this->_user->getId());
        $response = Zend_Json::encode($response);

        echo $response;
    }

    /**
     * edit setting
     *
     */
    public function editAction()
    {
        $uid = $this->_user->getId();
        $title = $this->_request->getPost('txtTitle');
        $title = MyLib_String::truncate($title, 20);
        $introduce = $this->_request->getPost('txtIntroduce');
        $introduce = MyLib_String::truncate($introduce, 100);
        $designSkin = $this->_request->getPost('designSkin');
        $ddlBoardPublicType = $this->_request->getPost('ddlBoardPublicType');
        $ddlCommentPublicType = $this->_request->getPost('ddlCommentPublicType');

        require_once 'Bll/Board/Board.php';
        $bllBoard = Bll_Board_Board::getDefaultInstance();

        $skinId = $bllBoard->getSkinIdByUrl($designSkin);
        $mobileImgUrl = '/apps/board/mobile/img/template/' . $skinId . '/thumb.gif';

        $settingInfo = array('title' => $title, 'introduce' => $introduce, 'openflag' => $ddlBoardPublicType, 'allowComment' => $ddlCommentPublicType, 'image_url' => $designSkin, 'mobile_image_url' => $mobileImgUrl);

        //update setting info

        $result = $bllBoard->editSetting($uid, $settingInfo);

        echo $result ? 'true' : 'flase';
    }

    public function gotoboardAction()
    {
        $uid = $this->_user->getId();
        $bownerId = $this->_request->getParam('uid', $uid);
        $openflag = null;

        if ($this->_request->getParam('uid') && $this->_request->getParam('uid') != $uid) {
            $fid = $this->_request->getParam('uid');
        }

        //contract list


        require_once 'Dal/Board/Board.php';
        $dalBoard = Dal_Board_Board::getDefaultInstance();

        require_once 'Bll/User.php';
        $bownerInfo = Bll_User::getPerson($bownerId);

        require_once 'Bll/Board/Board.php';
        $bllBoard = Bll_Board_Board::getDefaultInstance();
        //user info
        if (empty($fid)) {

            //head picture
            $setInfo = $dalBoard->getUserSetting($uid);
            $headPic = (empty($setInfo) || empty($setInfo['image_url'])) ? '/apps/board/img/head_default.gif' : $setInfo['image_url'];
            $title = (empty($setInfo) || empty($setInfo['title']) || (!empty($setInfo['title']) && 5 == $setInfo['title_status'])) ? $this->_user->getDisplayName() . "さんのあしあと帳" : $setInfo['title'];
            $introduce = (empty($setInfo) || empty($setInfo['introduce']) || (!empty($setInfo['introduce']) && 5 == $setInfo['des_status'])) ? '' : $bllBoard->removeEmoji($setInfo['introduce']);

        }
        else {

            //head picture
            $setInfo = $dalBoard->getUserSetting($fid);
            $headPic = (empty($setInfo) || empty($setInfo['image_url'])) ? '/apps/board/img/head_default.gif' : $setInfo['image_url'];
            $title = (empty($setInfo) || empty($setInfo['title']) || (!empty($setInfo['title']) && 5 == $setInfo['title_status'])) ? $bownerInfo->getDisplayName() . "さんのあしあと帳" : $setInfo['title'];
            $introduce = (empty($setInfo) || empty($setInfo['introduce']) || (!empty($setInfo['introduce']) && 5 == $setInfo['des_status'])) ? '' : $bllBoard->removeEmoji($setInfo['introduce']);
        }

        if ($uid != $bownerId) {

            $userSetting = $dalBoard->getUserSetting($bownerId);
            $allowComment = $userSetting['allowComment'];

            //非公開
            if ($userSetting['openflag'] == '3') {
                $openflag = 3;
            }
            //友達まで公開
            else if ($userSetting['openflag'] == '2') {
                //check is friend or not
                require_once 'Bll/Friend.php';
                $bllFriend = new Bll_Friend();
                $isFriend = $bllFriend->isFriend($uid, $bownerId);

                if (!$isFriend) {
                    $openflag = 2;
                }
            }
            //友達の友達迄公開
            else if ($userSetting['openflag'] == '1') {
                //check is friend's friend or not
                require_once 'Bll/Friend.php';
                $bllFriend = new Bll_Friend();
                $isFriend = $bllFriend->isFriend($uid, $bownerId);
                if (!$isFriend) {
                    $isFriendFriend = $bllFriend->isFriendFriend($uid, $bownerId);
                    if (!$isFriendFriend) {
                        $openflag = 1;
                    }
                }
            }
            //全て公開
            else {
                $openflag = 0;
            }

        }
        //全て公開
        else {
            $openflag = 0;
        }

        if ($openflag == 0) {

            $pageIndex = $this->_request->getParam('page_index', 1);
            $pageSize = 10;

            $array = $dalBoard->getComments($bownerId, $pageIndex, $pageSize);
            $count = $dalBoard->getCommentsCount($bownerId);

            if ($count && !empty($array)) {
                $startCount = ($pageIndex - 1) * $pageSize + 1;
                if (count($array) == '10') {
                    $endCount = $pageIndex * $pageSize;
                }
                else {
                    $endCount = $startCount + count($array) - 1;
                }
                $listCount = array('startCount' => $startCount, 'endCount' => $endCount);

                foreach ($array as $key => $value) {
                    $newContent = $bllBoard->removeEmoji($value['content']);
                    $array[$key]['content'] = $newContent;
                }

                require_once 'Bll/User.php';
                $bllUser = new Bll_User();
                $bllUser->appendPeople($array, 'comment_uid');
            }
        }
        $response = array('bownerId' => $bownerId, 'info' => $array, 'count' => $count, 'listCount' => $listCount, 'uid' => $uid, 'fid' => $fid, 'pageIndex' => $pageIndex, 'openflag' => $openflag, 'allowComment' => $allowComment, 'thumbnailUrl' => $bownerInfo->getThumbnailUrl(), 'userThumbnailUrl' => $this->_user->getThumbnailUrl(), 'title' => $title, 'introduce' => $introduce, 'headPic' => $headPic);

        $response = Zend_Json::encode($response);

        echo $response;
    }

    public function showhistoryAction()
    {
        $uid = $this->_user->getId();
        $bownerId = $this->_request->getParam('uid', $uid);

        if ($this->_request->getParam('uid') && $this->_request->getParam('uid') != $uid) {
            $fid = $this->_request->getParam('uid');
        }

        require_once 'Dal/Board/Board.php';
        $dalBoard = Dal_Board_Board::getDefaultInstance();

        require_once 'Bll/User.php';
        $bownerInfo = Bll_User::getPerson($bownerId);

        require_once 'Bll/Board/Board.php';
        $bllBoard = Bll_Board_Board::getDefaultInstance();

        //user info
        if (empty($fid)) {

            //head picture
            $setInfo = $dalBoard->getUserSetting($uid);
            $headPic = (empty($setInfo) || empty($setInfo['image_url'])) ? '/apps/board/img/head_default.gif' : $setInfo['image_url'];
            $title = (empty($setInfo) || empty($setInfo['title']) || (!empty($setInfo['title']) && 5 == $setInfo['title_status'])) ? $this->_user->getDisplayName() . "さんのあしあと帳" : $setInfo['title'];
            $introduce = (empty($setInfo) || empty($setInfo['introduce']) || (!empty($setInfo['introduce']) && 5 == $setInfo['des_status'])) ? '' : $bllBoard->removeEmoji($setInfo['introduce']);

        }
        else {

            //head picture
            $setInfo = $dalBoard->getUserSetting($fid);
            $headPic = (empty($setInfo) || empty($setInfo['image_url'])) ? '/apps/board/img/head_default.gif' : $setInfo['image_url'];
            $title = (empty($setInfo) || empty($setInfo['title']) || (!empty($setInfo['title']) && 5 == $setInfo['title_status'])) ? $bownerInfo->getDisplayName() . "さんのあしあと帳" : $setInfo['title'];
            $introduce = (empty($setInfo) || empty($setInfo['introduce']) || (!empty($setInfo['introduce']) && 5 == $setInfo['des_status'])) ? '' : $bllBoard->removeEmoji($setInfo['introduce']);
        }

        $openflag = null;
        if ($uid != $bownerId) {

            $userSetting = $dalBoard->getUserSetting($bownerId);

            //非公開
            if ($userSetting['openflag'] == '3') {
                $openflag = 3;
            }
            //友達まで公開
            else if ($userSetting['openflag'] == '2') {
                //check is friend or not
                require_once 'Bll/Friend.php';
                $bllFriend = new Bll_Friend();
                $isFriend = $bllFriend->isFriend($uid, $bownerId);

                if (!$isFriend) {
                    $openflag = 2;
                }
            }
            //友達の友達迄公開
            else if ($userSetting['openflag'] == '1') {
                //check is friend's friend or not
                require_once 'Bll/Friend.php';
                $bllFriend = new Bll_Friend();
                $isFriend = $bllFriend->isFriend($uid, $bownerId);
                if (!$isFriend) {
                    $isFriendFriend = $bllFriend->isFriendFriend($uid, $bownerId);
                    if (!$isFriendFriend) {
                        $openflag = 1;
                    }
                }
            }
        }
        //全て公開
        else {
            $openflag = 0;
        }

        $page = $this->_request->getParam('page_index', 1);
        $pageSize = 10;
        $array = $dalBoard->getHistoryList($bownerId, $page, $pageSize);
        $count = $dalBoard->getHistoryCount($bownerId);

        if ($count) {

            $startCount = ($page - 1) * $pageSize + 1;
            if (count($array) == '10') {
                $endCount = $page * $pageSize;
            }
            else {
                $endCount = $startCount + count($array) - 1;
            }
            $listCount = array('startCount' => $startCount, 'endCount' => $endCount);

            require_once 'Bll/User.php';
            $bllUser = new Bll_User();
            $bllUser->appendPeople($array, 'comment_uid');

            foreach ($array as $key => $value) {
                $target = Bll_User::getPerson($value['uid']);
                $array[$key]['targetName'] = $target->getDisplayName();

                $newContent = $bllBoard->removeEmoji($value['content']);
                $array[$key]['content'] = $newContent;
            }
        }

        $response = array('info' => $array, 'count' => $count, 'listCount' => $listCount, 'uid' => $this->_user->getId(), 'bownerId' => $bownerId, 'thumbnailUrl' => $bownerInfo->getThumbnailUrl(), 'title' => $title, 'introduce' => $introduce, 'fid' => $fid, 'openflag' => $openflag, 'pageIndex' => $page);
        $response = Zend_Json::encode($response);

        echo $response;

    }

    public function getusersetinfoAction()
    {
        $uid = $this->_user->getId();
        $bownerId = $this->_request->getParam('uid', $uid);

        //contract list
        require_once 'Bll/User.php';
        $bownerInfo = Bll_User::getPerson($bownerId);

        require_once 'Dal/Board/Board.php';
        $dalBoard = Dal_Board_Board::getDefaultInstance();
        $setInfo = $dalBoard->getUserSetting($uid);

        require_once 'Bll/Board/Board.php';
        $bllBoard = Bll_Board_Board::getDefaultInstance();

        $setInfo['introduce'] = $bllBoard->removeEmoji($setInfo['introduce']);

        $headPic = (empty($setInfo) || empty($setInfo['image_url'])) ? '/apps/board/img/head_default.gif' : $setInfo['image_url'];
        $title = (empty($setInfo) || empty($setInfo['title']) || (!empty($setInfo['title']) && 5 == $setInfo['title_status'])) ? $this->_user->getDisplayName() . "さんのあしあと帳" : $setInfo['title'];
        $introduce = (empty($setInfo) || empty($setInfo['introduce']) || (!empty($setInfo['introduce']) && 5 == $setInfo['des_status'])) ? '' : $bllBoard->removeEmoji($setInfo['introduce']);

        $response = array('uid' => $uid, 'bownerId' => $bownerId, 'setInfo' => $setInfo, 'title' => $title, 'thumbnailUrl' => $bownerInfo->getThumbnailUrl(), 'introduce' => $introduce);
        $response = Zend_Json::encode($response);

        echo $response;
    }
}


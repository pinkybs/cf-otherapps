<?php

/**
 * board controller
 * init each index page
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/02/10    Liz
 */
class BoardController extends MyLib_Zend_Controller_Action_Default
{
    /**
     * init
     *  init the data
     */
    function preRender()
    {
        $id = $this->_user->getId();

        //contract list
        require_once 'Bll/User.php';

        $dalBoard = Dal_Board_Board::getDefaultInstance();

        $this->view->userInfo = $this->_user;
        $bownerInfo = Bll_User::getPerson($this->view->bownerId);
        $this->view->bownerInfo = $bownerInfo;

        require_once 'Bll/Board/Board.php';
        $bllBoard = Bll_Board_Board::getDefaultInstance();

        //user info
        if (empty($this->view->fid)) {

            //head picture
            $setInfo = $dalBoard->getUserSetting($id);
            $this->view->headPic = (empty($setInfo) || empty($setInfo['image_url'])) ? '/apps/board/img/head_default.gif' : $setInfo['image_url'];
            $this->view->title = (empty($setInfo) || empty($setInfo['title']) || (!empty($setInfo['title']) && 5 == $setInfo['title_status']) ) ? $this->_user->getDisplayName() . "さんのあしあと帳" : $setInfo['title'];
            $this->view->introduce = (empty($setInfo) || empty($setInfo['introduce']) || (!empty($setInfo['introduce']) && 5 == $setInfo['des_status'])  ) ? '' : $bllBoard->removeEmoji($setInfo['introduce']);

        }
        else {

            //head picture
            $setInfo = $dalBoard->getUserSetting($this->view->fid);
            $this->view->headPic = (empty($setInfo) || empty($setInfo['image_url'])) ? '/apps/board/img/head_default.gif' : $setInfo['image_url'];
            $this->view->title = (empty($setInfo) || empty($setInfo['title']) || (!empty($setInfo['title']) && 5 == $setInfo['title_status']) ) ? $bownerInfo->getDisplayName(). "さんのあしあと帳" : $setInfo['title'];
            $this->view->introduce = (empty($setInfo) || empty($setInfo['introduce']) || (!empty($setInfo['introduce']) && 5 == $setInfo['des_status'])  ) ? '' : $bllBoard->removeEmoji($setInfo['introduce']);
        }

        $this->view->setInfo = $setInfo;
		$this->view->csstype = "board";


        $id = empty($this->view->fid) ? $id : $this->view->fid;
    }

    /**
     * index Action
     *
     */
    public function indexAction()
    {
    	return $this->_forward('list');
    	//$this->render();
    }

    /**
     * list Action
     *
     */
    public function listAction()
    {
        $uid = $this->_user->getId();
        $bownerId = $this->_request->getParam('uid', $uid);
        $this->view->uid = $uid;

        require_once 'Bll/Board/Board.php';
        $bllBoard = Bll_Board_Board::getDefaultInstance();

        if ($this->_request->getParam('uid') && $this->_request->getParam('uid') != $uid) {
            $this->view->fid = $this->_request->getParam('uid');
        }

        if ($uid != $bownerId ) {
            $dalBoard = Dal_Board_Board::getDefaultInstance();
            $userSetting = $dalBoard->getUserSetting($bownerId);
            $this->view->allowComment = $userSetting['allowComment'];

            //非公開
            if ($userSetting['openflag'] == '3') {
                $this->view->openflag = 3;
            }
            //友達まで公開
            else if ($userSetting['openflag'] == '2') {
                //check is friend or not
                require_once 'Bll/Friend.php';
                $bllFriend = new Bll_Friend();
                $isFriend = $bllFriend->isFriend($uid, $bownerId);

                if (!$isFriend){
                    $this->view->openflag = 2;
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
                    if (!$isFriendFriend){
                        $this->view->openflag = 1;
                    }
                }
            }
            //全て公開
            else {
                $this->view->openflag = 0;
            }

        }
        //全て公開
        else {
            $this->view->openflag = 0;
        }

        if ($this->view->openflag == 0) {

            $pageIndex = $this->_request->getParam('page_index', 1);
            $pageSize = 10;
            $dalBoard = Dal_Board_Board::getDefaultInstance();
            $array = $dalBoard->getComments($bownerId, $pageIndex, $pageSize);
            $count = $dalBoard->getCommentsCount($bownerId);

            if ($count && !empty($array)) {
                $startCount = ($pageIndex-1)*$pageSize+1;
                if (count($array) == '10') {
                    $endCount = $pageIndex*$pageSize;
                }
                else {
                    $endCount = $startCount+count($array)-1;
                }
                $listCount = array('startCount' => $startCount, 'endCount' => $endCount );

                foreach ($array as $key => $value) {
                	$newContent = $bllBoard->removeEmoji($value['content']);
                	$array[$key]['content'] = $newContent;
                }

                require_once 'Bll/User.php';
                $bllUser = new Bll_User();
                $bllUser->appendPeople($array, 'comment_uid');

            }
        }

        $column = 3;
        $row = 7;
        $contactNumPerPage = $column * $row;

        $arrContactList = $bllBoard->getMiniContactList($uid, $bownerId, $contactNumPerPage);

        $response = array('info' => $arrContactList['contactUids'],
                          'maxCount' => $arrContactList['maxCount'],
                          'pageindex' => $arrContactList['pageindex'],
                          'maxPage' => $arrContactList['maxPage']);

        if ($arrContactList['maxPage'] == 1){
        	$row = ceil(count($arrContactList['contactUids']) / $column);
        }

        $contactHeight = 67;
        $columnWidth = 199;
        $picListBoxHeight = $contactHeight * $row;
        $picListBoxWidth = $arrContactList['maxPage'] * $columnWidth;

        $this->view->bownerId = $bownerId;
        $this->view->info = $array;
        $this->view->count = $count;
        $this->view->listCount = $listCount;
        $this->view->uid = $uid;
        $this->view->pageIndex = $pageIndex;
        $this->view->response = $response;
        $this->view->countContactList = count($arrContactList['contactUids']);
        $this->view->picListBoxHeight = $picListBoxHeight;
        $this->view->picListBoxWidth = $picListBoxWidth;
        $this->view->contactNumPerPage = $contactNumPerPage;

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
        return $this->_forward('notfound','error','default');
    }

 }

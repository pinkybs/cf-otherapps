<?php

/** @see Zend_Json */
require_once 'Zend/Json.php';

/**
 * Mini Comment Ajax Controller
 * Mixi-App Ajax Controller
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create    2009/02/10    shenhw
 */
class Ajax_MinicommentController extends Zend_Controller_Action
{

    /**
     * initialize basic data
     * @return void
     */
    function init()
    {
        $font = $this->getFrontController();
        $font->unregisterPlugin('Zend_Controller_Plugin_ErrorHandler');
        $font->setParam('noViewRenderer', true);
    }

    /**
     * application list view
     *
     */
    public function getminicommentAction()
    {
        if ($this->_request->isPost()) {
            //get owner id
            $ownerId = $this->_request->getParam('ownerId');
            $viewerId = $this->_request->getParam('viewerId');

            /*
            //get comment list
            $dalBoard = Dal_Board_Board::getDefaultInstance();
            $comments = $dalBoard->getComments($ownerId, 1, 2);

            if ($comments) {
                //append comment user's info
                require_once 'Bll/User.php';
                $bllUser = new Bll_User();
                $bllUser->appendPeople($comments, "comment_uid");
            }
            */

            //非公開
            $openflag = 3;
            $allowComment = 0;
            
            if ($viewerId != $ownerId ) {
                $dalBoard = Dal_Board_Board::getDefaultInstance();
                $userSetting = $dalBoard->getUserSetting($ownerId);
                $allowComment = $userSetting['allowComment'];
    
                //友達まで公開
                if ($userSetting['openflag'] == '2') {
                    //check is friend or not
                    require_once 'Bll/Friend.php';
                    $bllFriend = new Bll_Friend();
                    $isFriend = $bllFriend->isFriend($viewerId, $ownerId);
    
                    if ($isFriend){
                        $openflag = 2;
                    }
                }
                //友達の友達迄公開
                else if ($userSetting['openflag'] == '1') {
                    //check is friend's friend or not
                    require_once 'Bll/Friend.php';
                    $bllFriend = new Bll_Friend();
                    $isFriend = $bllFriend->isFriend($viewerId, $ownerId);
                    if (!$isFriend) {
                        $isFriendFriend = $bllFriend->isFriendFriend($viewerId, $ownerId);
                        if ($isFriendFriend){
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
                $allowComment = 0;
            }
            
            $response = array('allowComment' => $allowComment, 'openflag' => $openflag);
            $response = Zend_Json::encode($response);
            echo $response;
        }
    }
    
    /**
     * new
     *
     */
    public function newAction()
    {
        //info_log('before post', 'board');
        if ($this->_request->isPost()) {
            $to_id = $this->_request->getPost('ownerId');
            $uid = $this->_request->getPost('commentUid');
            $txtContent = $this->_request->getPost('txtContent');

            //info_log("to_id : $to_id , txtContent : $txtContent", "board");
            
            //if $content is null ,redirect return url
            if ( rtrim($txtContent) == "" ) {
                echo 'false';
                return;
            }

            if (!$to_id) {
                echo 'false';
                return;
            }

            $boardInfo = array(
                'uid' => $to_id,
                'comment_uid' => $uid,
                'content' => $txtContent,
                'create_time' => date('Y-m-d H:i:s')
            );

            //new board
            $bllBoard = Bll_Board_Board::getDefaultInstance();
            $result = $bllBoard->newBoard($boardInfo);
            echo Zend_Json::encode($result);
        }
    }
}
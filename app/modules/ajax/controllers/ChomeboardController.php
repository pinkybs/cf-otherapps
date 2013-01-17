<?php
/** @see Zend_Json */
require_once 'Zend/Json.php';
/** @see MyLib_Zend_Controller_Action_Ajax */
require_once 'MyLib/Zend/Controller/Action/Ajax.php';

/**
 * Chomeboard Ajax Controllers
 * new board
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/02/10   Liz
 */
class Ajax_ChomeboardController extends MyLib_Zend_Controller_Action_Ajax
{
    
    /**
     * get board list
     *
     */
    public function getboardinfoAction()
    {
        $request = $this->_request->getParam('request');
        //decode $request with type=TYPE_OBJECT
        $request = Zend_Json::decode($request, Zend_Json::TYPE_OBJECT);
        $bownerId = $request->bownerId;
        $viewerId = $request->viewerId;
        
        $ownerInfo['uid'] = $bownerId;

        require_once 'Bll/User.php';
        Bll_User::appendPerson($ownerInfo, 'uid');

        require_once 'Bll/Chomeboard/Chomeboard.php';
        $bllChomeboard = new Bll_Chomeboard_Chomeboard();
        $lastChomeBoard = $bllChomeboard->getLastChomeBoard($bownerId);
        $lastChomeBoardId = $lastChomeBoard[0]['bid'];
        
        $aryBoardHistory = $bllChomeboard->getBoardHistory($bownerId);

        //check is friend's friend or not
        $chomeringFlag = 1;
        require_once 'Bll/Friend.php';
        $bllFriend = new Bll_Friend();
        $isFriend = $bllFriend->isFriend($viewerId, $bownerId);
        if (!$isFriend) {
            $isFriendFriend = $bllFriend->isFriendFriend($viewerId, $bownerId);
            if (!$isFriendFriend){
                $chomeringFlag = 0;
            }
        }
        
        $response = array('lastChomeBoardId' => $lastChomeBoardId,
                            'lastChomeBoard' => $lastChomeBoard[0],
                            'aryBoardHistory' => $aryBoardHistory,
                            'chomeringFlag' => $chomeringFlag,
                            'ownerInfo' => $ownerInfo);
        
        $response = Zend_Json::encode($response);

        echo $response;
    }
    
    /**
     * new
     *
     */
    public function newchomeboardAction()
    {
        if ($this->_request->isPost()) {
            require_once 'Bll/Chomeboard/Chomeboard.php';
            $bllChomeboard = new Bll_Chomeboard_Chomeboard();
            
            //get to uid
            $to_id = $this->_request->getPost('toUid');
            $bid = $this->_request->getPost('bid');
            $viewerId = $this->_user->getId();
            
            // Base64 から元のバイナリデータへデコードして受け取り
            $img = base64_decode($this->_request->getParam('img'));
            
            $filename = $this->_request->getPost('filename');
            
            $basePhotoUrl = $this->_photoBasePath . "/apps/chomeboard/";
            $saveFolder = $bllChomeboard->getSaveFolder($basePhotoUrl);
            
            // ファイル名の受け取り
            $picUrl = $basePhotoUrl . $saveFolder . '/' . $filename . ".png";
            
            $chomeboardInfo = array('uid' => $to_id, 'comment_uid' => $viewerId, 'content' => $saveFolder . '/' . $filename . ".png", 'create_time' => date('Y-m-d H:i:s'));
            
            //new board
            $result = $bllChomeboard->newChomeBoard($bid, $chomeboardInfo, $img, $picUrl);
        
            //pic for feed
            $photoUrl = Zend_Registry::get('photo');
            $feedPicUrl = $photoUrl . "/apps/chomeboard/" . $saveFolder . '/' . $filename . ".png";
            $result['picUrl'] = $feedPicUrl;

            echo Zend_Json::encode($result);
        }
    }

    /**
     * delete board
     *
     */
    public function deleteAction()
    {
        $request = $this->_request->getParam('request');
        
        //decode $request with type=TYPE_OBJECT
        $request = Zend_Json::decode($request, Zend_Json::TYPE_OBJECT);
        
        require_once 'Bll/Chomeboard/Chomeboard.php';
        $bllChomeboard = new Bll_Chomeboard_Chomeboard();
        $result = $bllChomeboard->deleteChomeBoard($request->bid, $request->uid, $request->comment_uid);
        
        //if $result,return ture or return false
        echo $result ? 'true' : 'flase';
    }

    /**
     * ranking action
     *
     */
    public function rankingAction()
    {
        //get search condition
        $request = $this->_request->getParam('request');
        $request = Zend_Json::decode($request, Zend_Json::TYPE_OBJECT);

        require_once 'Dal/Chomeboard/User.php';
        $dalCbUser = new Dal_Chomeboard_User();
        
        $uid = $this->_user->getId();

        $friendIds = Bll_Friend::getFriends($uid);
        //$friendIds = explode(',', $friendIds);

        $count = $dalCbUser->getRankingCount($uid, $request->type1, $friendIds);
        
        require_once 'Bll/Chomeboard/Chomeboard.php';
        $bllCbChomeboard = new Bll_Chomeboard_Chomeboard();
        
        //get top rank info
        $start = $count>2 ? ($count -2) : 0;
        $topRank = $dalCbUser->getRankingUser($uid, $friendIds, $request->type1, $request->type2, 2, 'ASC', $start);

        require_once 'Bll/User.php';
        Bll_User::appendPeople($topRank, 'uid');

        //get rank info about user
        $result = $bllCbChomeboard->getRankInfo($uid, $request->type1, $request->type2);
        $rankInfo = $result['rankInfo'];
        $userRankNm = $result['userRankNm'];
        $rankStatus = $result['rankStatus'];

        //Bll_User::appendPeople($rankInfo, 'uid');
        
        
        $allRank = array();
        if (!$rankInfo) {
            $rankInfo = $allRank;
            $userRankNm = 1;
        }
        else {
            Bll_User::appendPeople($rankInfo, 'uid');
        }
        
        $topCount = count($topRank) < 2 ? 1 : 2;
        
        //get some count info
        $rankCount = count($rankInfo);
        $rightCount = $rankCount > 12 ? ($rankCount-12) : 0;
        $countArr = array('rankCount' => $rankCount,
                          'rightCount' => $rightCount,
                          'allCount' => $count);

        //set output rank data
        $response = array('rankInfo' => $rankInfo,
                          'count' => $userRankNm,
                          'topRank' => $topRank,
                          'topCount' => $topCount,
                          'countArr' => $countArr,
                          'rankStatus' => $rankStatus);
        $response = Zend_Json::encode($response);

        echo $response;
    }

    /**
     * get more rank info
     *
     */
    public function getmorerankAction() {
        //get search condition
        $request = $this->_request->getParam('request');
        $request = Zend_Json::decode($request, Zend_Json::TYPE_OBJECT);

        $uid = $this->_user->getId();
        require_once 'Bll/Chomeboard/Chomeboard.php';
        $bllCbChomeboard = new Bll_Chomeboard_Chomeboard();
        $result = $bllCbChomeboard->getMoreRank($uid, $request->type1, $request->type2, $request->rankId, $request->allCount, $request->isRight);

        $response = Zend_Json::encode($result);
        echo $response;
    }

    /**
     * get last rank info
     *
     */
    public function getlastrankAction() {
        //get search condition
        $request = $this->_request->getParam('request');
        $request = Zend_Json::decode($request, Zend_Json::TYPE_OBJECT);

        $uid = $this->_user->getId();
        
        if ($request->type1 == 1) {
            $friendIds = Bll_Friend::getFriends($uid);
            //$friendIds = explode(',', $friendIds);
    
            if (count($friendIds) < 2) {
                echo '';
                return;
            }
        }
        
        require_once 'Bll/Chomeboard/Chomeboard.php';
        $bllCbChomeboard = new Bll_Chomeboard_Chomeboard();
        $result = $bllCbChomeboard->getLastRank($uid, $request->type1, $request->type2, $request->isRight);

        //set output data
        $response = Zend_Json::encode($result);

        echo $response;
    }
}


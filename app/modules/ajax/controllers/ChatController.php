<?php
/** @see Zend_Json */
require_once 'Zend/Json.php';
/** @see MyLib_Zend_Controller_Action_Ajax */
require_once 'MyLib/Zend/Controller/Action/Ajax.php';

/**
 * Chat Ajax Controllers
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/05/18   zhangxin
 */
class Ajax_ChatController extends MyLib_Zend_Controller_Action_Ajax
{

    /**
     * get friend list
     *
     */
    public function getfriendsAction()
    {
        $uid = $this->_user->getId();
        $cid = (int)$this->_request->getParam('cid');
        $filter = $this->_request->getParam('filter');

        require_once 'Dal/Chat/Member.php';
        $dalChatMem = Dal_Chat_Member::getDefaultInstance();

/*
        //get already invited uids
        $aryChkIds = array();
        if (!empty($cid)) {
            require_once 'Dal/Chat/AttendConfirm.php';
            $dalChatAtt = Dal_Chat_AttendConfirm::getDefaultInstance();
            $lstAttIds = $dalChatAtt->listChatAttendConfirmUids($cid);
            $lstMemIds = $dalChatMem->listChatMemberUids($cid);
            $aryChkIds = array_merge($lstAttIds,$lstMemIds);
        }
*/

        require_once 'Bll/Friend.php';
        require_once 'Bll/User.php';
        require_once 'Dal/Chat/Friend.php';
        //$dalF = Dal_Chat_Friend::getDefaultInstance();
        //$friendIds = $dalF->getFriendsByFilter($uid, $filter);
        $friendIds = Bll_Friend::getFriendIds($uid);
        if (!empty($friendIds)) {
            $aryFriends = Bll_User::getPeople(explode(',', $friendIds));
            $inxNo = 0;
            foreach ($aryFriends as $key=>$friend) {
                if (mb_strpos($friend->getDisplayName(), $filter, 0, 'UTF-8') !== false || empty($filter)) {
                    $aryInfo[$inxNo]['uid'] = $friend->getId();
                    $aryInfo[$inxNo]['displayName'] = $friend->getDisplayName();
                    $aryInfo[$inxNo]['thumbnailUrl'] = $friend->getThumbnailUrl();
                    $aryInfo[$inxNo]['profileUrl'] = $friend->getProfileUrl();
                    $inxNo ++;
                }

    /*
                //checked uids
                $aryInfo[$key]['ischecked'] = 0;
                if ($aryChkIds && count($aryChkIds) > 0) {
                    foreach ($aryChkIds as $data) {
                        if ($friend->getId() == $data['uid']) {
                            $aryInfo[$key]['ischecked'] = 1;
                            $aryInfo[$key]['ismember'] = $dalChatMem->isChatMember($cid, $data['uid']) ? 1 : 0;
                            break;
                        }
                    }
                }
    */
            }
        }

        $response = array('info' => $aryInfo, 'count' => count($aryInfo));
        $response = Zend_Json::encode($response);

        echo $response;
    }

 	/**
     * get chat member list
     *
     */
    public function getmemberlistAction()
    {
        $uid = $this->_user->getId();
        $cid = (int)$this->_request->getParam('cid');

        //check
        require_once 'Dal/Chat/Chat.php';
        $dalChat = Dal_Chat_Chat::getDefaultInstance();
        $rowChat = $dalChat->getChatById($cid);
        //no such chat
        if (empty($rowChat)) {
            echo 'true';
        	return;
        }

        //get chat member
        require_once 'Dal/Chat/Member.php';
        $dalMem = Dal_Chat_Member::getDefaultInstance();
        $lstMember = $dalMem->listChatMember($cid, 1, 100);
        require_once 'Bll/User.php';
        Bll_User::appendPeople($lstMember, 'uid');

        $response = array('info' => $lstMember, 'count' => count($lstMember));
        $response = Zend_Json::encode($response);

        echo $response;
    }

 	/**
     * get feed list
     *
     */
    public function getfeedlistAction()
    {
        $pageIndex = (int)$this->_request->getParam('pageIndex', 1);
        $pageSize = (int)$this->_request->getParam('pageSize', 10);
        $uid = $this->_user->getId();

        require_once 'Dal/Chat/FeedMessage.php';
        $dalFeed = Dal_Chat_FeedMessage::getDefaultInstance();
        $result = $dalFeed->listFeedMessage($uid, $pageIndex, $pageSize);
        $count = (int)$dalFeed->getFeedMessageCount($uid);

        $response = array('info' => $result, 'count' => $count);
        $response = Zend_Json::encode($response);
        echo $response;
    }

    /**
     * delete feed
     *
     */
    public function delfeedAction()
    {
        $id = (int)$this->_request->getParam('id');
        require_once 'Bll/Chat/FeedMessage.php';
        $bllFeed = new Bll_Chat_FeedMessage();
        $result = $bllFeed->delFeedMessage($id);
        echo $result ? 'true' : 'false';
    }

    /**
     * get chat detail list
     *
     */
    public function getdetaillistAction()
    {
        $uid = $this->_user->getId();
        $cid = (int)$this->_request->getParam('cid');
        $lastId = (int)$this->_request->getParam('lastId');
        $type = (int)$this->_request->getParam('type');

        //check
        require_once 'Dal/Chat/Chat.php';
        $dalChat = Dal_Chat_Chat::getDefaultInstance();
        $rowChat = $dalChat->getChatById($cid);
        //no such chat
        if (empty($rowChat)) {
            echo 'true';
        	return;
        }
        //chat already ended
        if (1 == $rowChat['iscanceled']) {
            $response = array('info' => null, 'count' => null, 'ended' => 1);
            $response = Zend_Json::encode($response);
            echo $response;
        	return;
        }

        require_once 'Dal/Chat/Detail.php';
        $dalDetail = Dal_Chat_Detail::getDefaultInstance();
        $lstDetail = $dalDetail->listChatDetail($cid, $lastId, $type, 1, 100);

        $cntDetail = count($lstDetail);
        if (!empty($lstDetail) && $cntDetail > 0) {
            $newLastId = $lstDetail[$cntDetail-1]['last_id'];
            require_once 'Dal/Chat/Member.php';
            $dalMem = Dal_Chat_Member::getDefaultInstance();
            $colLastId = 'last_detail_id';
            if (1 == $type) {
                $colLastId = 'last_detail_id_sys';
            }
            $dalMem->updateChatMember(array($colLastId => $newLastId), $cid, $uid);
        }

        require_once 'Bll/User.php';
        Bll_User::appendPeople($lstDetail, 'uid');

        $response = array('info' => $lstDetail, 'count' => $cntDetail, 'ended' => 0);
        $response = Zend_Json::encode($response);

        echo $response;
    }

	/**
     * get server system time
     *
     */
    public function getsystemtimeAction()
    {
        $cid = (int)$this->_request->getParam('cid');
        require_once 'Dal/Chat/Chat.php';
        $dalChat = Dal_Chat_Chat::getDefaultInstance();
        $rowChat = $dalChat->getChatById($cid);
        //no such chat
        if (empty($rowChat)) {
            echo 'false';
        	return;
        }

        $response = array('systime' => time(), 'extend_count' => $rowChat['extend_count'], 'istimeout_alerted' => $rowChat['istimeout_alerted']);
        $response = Zend_Json::encode($response);
        echo $response;
    }

	/**
     * alert timeout
     *
     */
    public function timeoutalertAction()
    {
        $uid = $this->_user->getId();
        $cid = (int)$this->_request->getParam('cid');

        //check
        require_once 'Dal/Chat/Chat.php';
        $dalChat = Dal_Chat_Chat::getDefaultInstance();
        $rowChat = $dalChat->getChatById($cid);
        //no such chat
        if (empty($rowChat)) {
            echo 'false';
        	return;
        }
        //chat already ended
        if (1 == $rowChat['iscanceled']) {
            echo 'false';
        	return;
        }
        //is not chat owner
        if ($uid != $rowChat['uid']) {
            echo 'false';
        	return;
        }
        //is already alerted
        if (1 == $rowChat['istimeout_alerted']) {
            echo 'false';
        	return;
        }

        $result = $dalChat->updateChat(array('istimeout_alerted' => 1), $cid);
        echo $result > 0 ? 'true' : 'false';
    }

	/**
     * extend chat time
     *
     */
    public function extendtimeAction()
    {
        $uid = $this->_user->getId();
        $cid = (int)$this->_request->getParam('cid');

        //check
        require_once 'Dal/Chat/Chat.php';
        $dalChat = Dal_Chat_Chat::getDefaultInstance();
        $rowChat = $dalChat->getChatById($cid);
        //no such chat
        if (empty($rowChat)) {
            echo 'false';
        	return;
        }
        //chat already ended
        if (1 == $rowChat['iscanceled']) {
            echo 'false';
        	return;
        }
        //is not chat owner
        if ($uid != $rowChat['uid']) {
            echo 'false';
        	return;
        }

        $nowTime = time();
        $extTime = strtotime($rowChat['start_time']) + $rowChat['extend_count']*3600;
        if ($nowTime - $extTime < 3600) {
            echo 'false';
        	return;
        }
        $cntExt = ceil(($nowTime - $extTime)/3600) - 1;
        $cntExtended = $rowChat['extend_count'] + $cntExt;
        $result = $dalChat->updateChat(array('istimeout_alerted' => 0, 'extend_count' => $cntExtended), $cid);

        echo $result > 0 ? 'true' : 'false';
    }

    /**
     * exit chat room
     *
     */
    public function exitroomAction()
    {
        $uid = $this->_user->getId();
        $cid = (int)$this->_request->getParam('cid');
        $lastId = (int)$this->_request->getParam('lastId');
        $lastIdSys = (int)$this->_request->getParam('lastIdSys');

        //check
        require_once 'Dal/Chat/Chat.php';
        $dalChat = Dal_Chat_Chat::getDefaultInstance();
        $rowChat = $dalChat->getChatById($cid);
        //no such chat
        if (empty($rowChat)) {
            echo 'false';
        	return;
        }
        //chat already ended
        if (1 == $rowChat['iscanceled']) {
            echo 'false';
        	return;
        }
        //chat not started
        if (1 != $rowChat['isstarted']) {
            echo 'false';
        	return;
        }

        $blnIsOwner = false;
        if ($uid == $rowChat['uid']) {
            $blnIsOwner = true;
        }

        require_once 'Bll/Chat/Detail.php';
        $bllChat = new Bll_Chat_Detail();
        $aryInfo = array();
        $aryInfo['cid'] = $cid;
        $aryInfo['uid'] = $uid;
        if ($blnIsOwner) {
            $aryInfo['content'] = $this->_user->getDisplayName() . 'さんがチャットを終了したよ。';
        }
        else {
            $aryInfo['content'] = $this->_user->getDisplayName() . 'さんがチャットに退出しました。';
        }
        $aryInfo['issystem'] = 1;
        $aryInfo['last_detail_id'] = $lastId;
        $aryInfo['last_detail_id_sys'] = $lastIdSys;
        $result = $bllChat->exitDetail($aryInfo, $blnIsOwner);

        //if $result,return ture or return false
        echo $result ? 'true' : 'false';
    }

	/**
     * send message
     *
     */
    public function sendAction()
    {
        $uid = $this->_user->getId();
        $cid = (int)$this->_request->getParam('cid');
        $txtContent = $this->_request->getParam('txtContent');
        //check
        require_once 'Dal/Chat/Member.php';
        $dalMem = Dal_Chat_Member::getDefaultInstance();
        //is not chat member
        if (!$dalMem->isChatMember($cid, $uid)) {
            echo 'false';
            return;
        }

        $txtContent = mb_substr($txtContent, 0, 200, 'UTF-8');
        require_once 'Bll/Chat/Detail.php';
        $bllChat = new Bll_Chat_Detail();
        $aryInfo = array();
        $aryInfo['cid'] = $cid;
        $aryInfo['uid'] = $uid;
        $aryInfo['content'] = $txtContent;
        $result = $bllChat->newDetail($aryInfo);

        //if $result,return ture or return false
        echo $result ? 'true' : 'false';
    }

}


<?php

/**
 * chat controller
 * init each index page
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/05/19	zhangxin
 */
class ChatController extends MyLib_Zend_Controller_Action_Default
{

	/**
     * deipatch
     *
     */
    function preDispatch()
    {
        $uid = $this->_user->getId();
        $this->view->mixiAppId = $this->_appId;
        $this->view->mixiAppName = 'chat';
        $this->view->uid = $uid;
        $this->view->mixiHostUrl = MIXI_HOST;
    }

    /**
     * chat guide(first login) Action
     *
     */
    public function guideAction()
    {

        $this->render();
    }

    /**
     * index(list chat) Action
     *
     */
    public function indexAction()
    {
        $uid = $this->_user->getId();

        require_once 'Bll/User.php';
        require_once 'Dal/Chat/Chat.php';
        $dalChat = Dal_Chat_Chat::getDefaultInstance();
        require_once 'Dal/Chat/Member.php';
        $dalMem = Dal_Chat_Member::getDefaultInstance();
        require_once 'Dal/Chat/FeedMessage.php';
        $dalFeed = Dal_Chat_FeedMessage::getDefaultInstance();

        //owner chat
        $lstOwnerChat = $dalChat->listChatByOwner($uid, 1, 200);
        foreach ($lstOwnerChat as $key=>$chatInfo) {
            $lstOwnerChat[$key]['start_time'] = substr($lstOwnerChat[$key]['start_time'], 0, 16);
            $lstMem = $dalMem->listChatMember($chatInfo['cid']);
            if (!empty($lstMem) && count($lstMem) > 0) {
                Bll_User::appendPeople($lstMem, 'uid');
                $lstOwnerChat[$key]['ary_member'] = $lstMem;
            }
        }

        //joint chat
        $lstJoinerChat = $dalChat->listChatByJoiner($uid, 1, 200);
        foreach ($lstJoinerChat as $key=>$chatInfo) {
            $lstJoinerChat[$key]['start_time'] = substr($lstJoinerChat[$key]['start_time'], 0, 16);
            $lstMem = $dalMem->listChatMember($chatInfo['cid']);
            if (!empty($lstMem) && count($lstMem) > 0) {
                Bll_User::appendPeople($lstMem, 'uid');
                $lstJoinerChat[$key]['ary_member'] = $lstMem;
            }
        }

        //need to confirm chat
        $lstNeedConChat = $dalChat->listChatByNeedConfirm($uid, 1, 200);
        foreach ($lstNeedConChat as $key=>$chatInfo) {
            $lstNeedConChat[$key]['start_time'] = substr($lstNeedConChat[$key]['start_time'], 0, 16);
            $lstMem = $dalMem->listChatMember($chatInfo['cid']);
            if (!empty($lstMem) && count($lstMem) > 0) {
                Bll_User::appendPeople($lstMem, 'uid');
                $lstNeedConChat[$key]['ary_member'] = $lstMem;
            }
        }

        if (empty($lstNeedConChat) && empty($lstJoinerChat) && empty($lstOwnerChat)) {
            $this->_redirect($this->_baseUrl . '/chat/add');
        	return;
        }

        $this->view->feedCount = (int)$dalFeed->getFeedMessageCount($uid);
        $this->view->lstOwnerChat = $lstOwnerChat;
        $this->view->lstJoinerChat = $lstJoinerChat;
        $this->view->lstNeedConChat = $lstNeedConChat;
        $this->render();
    }

    /**
     * add Action
     *
     */
    public function addAction()
    {
        //do add request
        if ($this->_request->isPost()) {
            $txtName = $this->_request->getPost('txtName');
            $txtDate = $this->_request->getPost('txtDate');
            $selHour = $this->_request->getPost('selHour');
            $selMinute = $this->_request->getPost('selMinute');
            $txtMessage = $this->_request->getPost('txtMessage');
            $chkMem = $this->_request->getPost('chkMem');

            if (empty($txtName) || empty($txtDate) || empty($chkMem)) {
                $this->_redirect($this->_baseUrl . '/chat/add');
        	    return;
            }

            require_once 'Bll/Chat/Chat.php';
            $bllChat = new Bll_Chat_Chat();
            $aryInfo = array();
            $aryInfo['uid'] = $this->_user->getId();
            $aryInfo['title'] = mb_substr($txtName, 0, 20, 'UTF-8');
            $aryInfo['start_time'] = $txtDate . ' ' . $selHour . ':' . $selMinute . ':00';
            $aryInfo['message'] = mb_substr($txtMessage, 0, 400, 'UTF-8');

            $result = $bllChat->newChat($aryInfo, $chkMem);
            if ($result) {
                return $this->_forward('adddone', 'chat', 'default', array('cid' => $result, 'isadd' => 1));
            }
            $this->_redirect($this->_baseUrl . '/chat/add');
        	return;
        }

        require_once 'Bll/Chat/Activity.php';
        //init add page
        $cancelId = (int)$this->_request->getParam('cancel');
        $cname = $this->_request->getParam('cname');
        if (90001 == $cancelId) {
            $this->view->infoMsg = htmlspecialchars(urldecode($cname)) . 'への招待を拒否しました。';
            //activity
            $tuid = $this->_request->getParam('tuid');
            $this->view->activity = Bll_Chat_Activity::getActivity('', '', array('chat_name'=>urldecode($cname)), 5);
            $this->view->activityUser = $tuid;
        }
        else if (90002 == $cancelId) {
            $this->view->infoMsg = htmlspecialchars(urldecode($cname)) . 'の取り消しをしました。';
            //activity
            $this->view->activity = Bll_Chat_Activity::getActivity('', '', array('chat_name'=>urldecode($cname)), 4);
        }
        else if (90003 == $cancelId) {
            $this->view->infoMsg = htmlspecialchars(urldecode($cname)) . 'から退室しました。';
        }
        else if (90004 == $cancelId) {
            //チャット終了（幹事）
            $cid = (int)$this->_request->getParam('cid');
            require_once 'Dal/Chat/Chat.php';
            $dalChat = Dal_Chat_Chat::getDefaultInstance();
            $rowChat = $dalChat->getChatById($cid);
            if (!empty($rowChat)) {
                require_once 'Dal/Chat/Member.php';
                $dalChatMem = Dal_Chat_Member::getDefaultInstance();
                $lastMem = $dalChatMem->listChatMemberUids($cid);
                require_once 'Bll/User.php';
                Bll_User::appendPeople($lastMem, 'uid');
                require_once 'Zend/Json.php';
                $this->view->lastMem = htmlspecialchars(Zend_Json::encode($lastMem),ENT_QUOTES);
                $this->view->infoMsg = htmlspecialchars($rowChat['title']) . 'を終了しました。';
                $this->view->cancelcode = 90004;
            }
        }
        else if (90005 == $cancelId) {
            $uname = $this->_request->getParam('uname');
            $this->view->infoMsg = urldecode($uname) . 'さんが' . urldecode($cname) . 'を終了しました。';
        }
        else if (90006 == $cancelId) {
            $cid = (int)$this->_request->getParam('cid');
            require_once 'Dal/Chat/Chat.php';
            $dalChat = Dal_Chat_Chat::getDefaultInstance();
            $rowChat = $dalChat->getChatById($cid);
            if (!empty($rowChat)) {
                require_once 'Bll/User.php';
                $ownerInfo = Bll_User::getPerson($rowChat['uid']);
                $ownerName = $ownerInfo->getDisplayName();
                $this->view->infoMsg = htmlspecialchars($rowChat['title']) . 'の取り消しされました。'
                                     . "\n" . $ownerName . 'さんからのメッセージ：「' . $rowChat['cancel_message'] . '」';
            }
        }

        require_once 'Bll/Friend.php';
        $friendIds = Bll_Friend::getFriendIds($this->_user->getId());
        $aryFriends = explode(',', $friendIds);
        $this->view->friendCount = count($aryFriends);
        $this->view->curDate = time();
        require_once 'Dal/Chat/FeedMessage.php';
        $dalFeed = Dal_Chat_FeedMessage::getDefaultInstance();
        $this->view->feedCount = (int)$dalFeed->getFeedMessageCount($this->_user->getId());

        $this->render();
    }

	/**
     * edit Action
     *
     */
    public function editAction()
    {
        $uid = $this->_user->getId();

        //do edit request
        if ($this->_request->isPost()) {
            $cid = (int)$this->_request->getPost('cid');
            if (empty($cid)) {
                $this->_redirect($this->_baseUrl . '/chat');
            	return;
            }
            //get chat info
            require_once 'Dal/Chat/Chat.php';
            $dalChat = Dal_Chat_Chat::getDefaultInstance();
            $rowChat = $dalChat->getChatById($cid);
            if (empty($rowChat)) {
                $this->_redirect($this->_baseUrl . '/chat');
            	return;
            }
            else if (1 == $rowChat['iscanceled']) {
                $this->_redirect($this->_baseUrl . '/chat');
            	return;
            }
            else if (1 == $rowChat['isstarted']) {
                $this->_redirect($this->_baseUrl . '/chat/view?cid=' . $cid);
                return;
            }
            //is chat owner
            if ($uid != $rowChat['uid']) {
                $this->_redirect($this->_baseUrl . '/chat/view?cid=' . $cid);
                return;
            }

            $txtName = $this->_request->getPost('txtName');
            $txtDate = $this->_request->getPost('txtDate');
            $selHour = $this->_request->getPost('selHour');
            $selMinute = $this->_request->getPost('selMinute');
            $txtMessage = $this->_request->getPost('txtMessage');
            $chkMem = $this->_request->getPost('chkMem');

            require_once 'Bll/Chat/Chat.php';
            $bllChat = new Bll_Chat_Chat();
            $aryInfo = array();
            $aryInfo['title'] = mb_substr($txtName, 0, 20, 'UTF-8');
            $aryInfo['start_time'] = $txtDate . ' ' . $selHour . ':' . $selMinute . ':00';
            $aryInfo['message'] = mb_substr($txtMessage, 0, 400, 'UTF-8');

            $result = $bllChat->editChat($cid, $aryInfo, $chkMem);
            if ($result) {
                return $this->_forward('adddone', 'chat', 'default', array('cid' => $cid, 'isadd' => 0));
            }
        }

        //init edit page
        $cid = (int)$this->_request->getParam('cid');
        if (empty($cid)) {
            $this->_redirect($this->_baseUrl . '/chat');
        	return;
        }

        //get chat info
        require_once 'Dal/Chat/Chat.php';
        $dalChat = Dal_Chat_Chat::getDefaultInstance();
        $rowChat = $dalChat->getChatById($cid);
        if (empty($rowChat)) {
            $this->_redirect($this->_baseUrl . '/chat');
        	return;
        }
        else if (1 == $rowChat['iscanceled']) {
            $this->_redirect($this->_baseUrl . '/chat');
        	return;
        }
        else if (1 == $rowChat['isstarted']) {
            $this->_redirect($this->_baseUrl . '/chat/view?cid=' . $cid);
            return;
        }
        //is chat owner
        if ($uid != $rowChat['uid']) {
            $this->_redirect($this->_baseUrl . '/chat/view?cid=' . $cid);
            return;
        }

        //friend count
        require_once 'Bll/Friend.php';
        $friendIds = Bll_Friend::getFriendIds($uid);
        $aryFriends = explode(',', $friendIds);
        $this->view->friendCount = count($aryFriends);
        $this->view->curDate = time();

        //chat info
        $aryStartTime = explode(' ', $rowChat['start_time']);
        $this->view->chatName = $rowChat['title'];
        $this->view->chatDate = $aryStartTime[0];
        $this->view->chatHour = substr($aryStartTime[1], 0, 2);
        $this->view->chatMinute = substr($aryStartTime[1], 3, 2);
        $this->view->chatMessage = $rowChat['message'];
        $this->view->cid = $cid;

        //already member info
        //get already invited uids
        $aryChkIds = array();
        require_once 'Bll/User.php';
        require_once 'Dal/Chat/Member.php';
        $dalChatMem = Dal_Chat_Member::getDefaultInstance();
        require_once 'Dal/Chat/AttendConfirm.php';
        $dalChatAtt = Dal_Chat_AttendConfirm::getDefaultInstance();
        $lstAttIds = $dalChatAtt->listChatAttendConfirmUids($cid);
        $lstMemIds = $dalChatMem->listChatMemberUids($cid);
        $aryChkIds = array_merge($lstMemIds,$lstAttIds);
        $aryInfo = array();
        $index = 0;
        foreach ($aryChkIds as $key=>$data) {
            if ($uid != $data['uid']) {
                $person = Bll_User::getPerson($data['uid']);
                $aryInfo[$index]['uid'] = $person->getId();
                $aryInfo[$index]['displayName'] = $person->getDisplayName();
                $aryInfo[$index]['thumbnailUrl'] = $person->getThumbnailUrl();
                $aryInfo[$index]['profileUrl'] = $person->getProfileUrl();
                $aryInfo[$index]['ismember'] = $dalChatMem->isChatMember($cid, $data['uid']) ? '1' : '0';
                $index ++;
            }
        }
        for ($i=count($aryInfo); $i<5; $i++) {
            $aryInfo[$i]['uid'] = '0';
        }
        $this->view->lstMemCon = $aryInfo;

        $this->render();
    }

	/**
     * del Action
     *
     */
    public function delAction()
    {
        $uid = $this->_user->getId();

        //do edit request
        if ($this->_request->isPost()) {
            $cid = (int)$this->_request->getPost('cid');
            if (empty($cid)) {
                $this->_redirect($this->_baseUrl . '/chat');
            	return;
            }
            //get chat info
            require_once 'Dal/Chat/Chat.php';
            $dalChat = Dal_Chat_Chat::getDefaultInstance();
            $rowChat = $dalChat->getChatById($cid);
            if (empty($rowChat)) {
                $this->_redirect($this->_baseUrl . '/chat');
            	return;
            }
            else if (1 == $rowChat['iscanceled']) {
                $this->_redirect($this->_baseUrl . '/chat');
            	return;
            }
            else if (1 == $rowChat['isstarted']) {
                $this->_redirect($this->_baseUrl . '/chat/view?cid=' . $cid);
                return;
            }
            //is chat owner
            if ($uid != $rowChat['uid']) {
                $this->_redirect($this->_baseUrl . '/chat/view?cid=' . $cid);
                return;
            }

            $txtMessage = $this->_request->getPost('txtMessage');

            require_once 'Bll/Chat/Chat.php';
            $bllChat = new Bll_Chat_Chat();
            $result = $bllChat->delChat($cid, mb_substr($txtMessage, 0, 400, 'UTF-8'));
            if ($result) {
                $this->_redirect($this->_baseUrl . '/chat/add?cancel=90002&cname=' . urlencode($rowChat['title']));
                return;
            }
        }

        //init edit page
        $cid = (int)$this->_request->getParam('cid');
        if (empty($cid)) {
            $this->_redirect($this->_baseUrl . '/chat');
        	return;
        }

        //get chat info
        require_once 'Dal/Chat/Chat.php';
        $dalChat = Dal_Chat_Chat::getDefaultInstance();
        $rowChat = $dalChat->getChatById($cid);
        if (empty($rowChat)) {
            $this->_redirect($this->_baseUrl . '/chat');
        	return;
        }
        else if (1 == $rowChat['iscanceled']) {
            $this->_redirect($this->_baseUrl . '/chat');
        	return;
        }
        else if (1 == $rowChat['isstarted']) {
            $this->_redirect($this->_baseUrl . '/chat/view?cid=' . $cid);
            return;
        }

        //is chat owner
        if ($uid != $rowChat['uid']) {
            $this->_redirect($this->_baseUrl . '/chat/view?cid=' . $cid);
            return;
        }

        //chat info
        $this->view->chatName = $rowChat['title'];
        $this->view->cid = $cid;
        $this->render();
    }

    /**
     * adddone Action
     *
     */
    public function adddoneAction()
    {
        //get cid
        $cid = $this->_request->getParam('cid');
        if (empty($cid)) {
            $this->_redirect($this->_baseUrl . '/chat/add');
        	return;
        }

        //get chat info
        require_once 'Dal/Chat/Chat.php';
        $dalChat = Dal_Chat_Chat::getDefaultInstance();
        $rowChat = $dalChat->getChatById($cid);
        if (empty($rowChat)) {
            $this->_redirect($this->_baseUrl . '/chat/add');
        	return;
        }
        if ($this->_user->getId() != $rowChat['uid']) {
            $this->_redirect($this->_baseUrl . '/chat/add');
        	return;
        }

        $intStartTime = strtotime($rowChat['start_time']);
        $aryStartTime = getdate($intStartTime);
        $this->view->chatName = $rowChat['title'];
        $this->view->chatDate = $aryStartTime['mon'] . '/' . $aryStartTime['mday']
                                . '（' . $this->_getWdayJp($aryStartTime['wday']) . '）の'
                                . (strlen($aryStartTime['hours']) == 1 ? ('0' . $aryStartTime['hours']) : $aryStartTime['hours'])
                                . ':' . (strlen($aryStartTime['minutes']) == 1 ? ('0' . $aryStartTime['minutes']) : $aryStartTime['minutes']);

        //get chat invite member info
        require_once 'Dal/Chat/AttendConfirm.php';
        $dalAC = Dal_Chat_AttendConfirm::getDefaultInstance();
        $lstAttendC = $dalAC->listChatAttendConfirm($cid);
        if (!empty($lstAttendC) && count($lstAttendC) > 0) {
            require_once 'Bll/User.php';
            Bll_User::appendPeople($lstAttendC, 'uid');
        }

        require_once 'Dal/Chat/Member.php';
        $dalChatMem = Dal_Chat_Member::getDefaultInstance();
        $lstMem = $dalChatMem->listChatMember($cid);
        if (!empty($lstMem) && count($lstMem) > 0) {
            require_once 'Bll/User.php';
            Bll_User::appendPeople($lstMem, 'uid');
        }

        //activity
        require_once 'Bll/Chat/Activity.php';
        $type = '1' == $this->_request->getParam('isadd') ? 1 : 3;
        $this->view->activity = Bll_Chat_Activity::getActivity('', '', array('chat_name'=>$rowChat['title']), $type);

        $this->view->lstAttendC = $lstAttendC;
        $this->view->lstMem = $lstMem;
        $this->render();
    }

	/**
     * view chat Action
     *
     */
    public function viewAction()
    {
        $uid = $this->_user->getId();

        //do post request
        if ($this->_request->isPost()) {
            $cid = (int)$this->_request->getPost('cid');
            if (empty($cid)) {
                $this->_redirect($this->_baseUrl . '/chat');
            	return;
            }

            //get chat info
            require_once 'Dal/Chat/Chat.php';
            $dalChat = Dal_Chat_Chat::getDefaultInstance();
            $rowChat = $dalChat->getChatById($cid);
            if (empty($rowChat)) {
                $this->_redirect($this->_baseUrl . '/chat');
            	return;
            }
            else if (1 == $rowChat['iscanceled']) {
                $this->_redirect($this->_baseUrl . '/chat');
            	return;
            }

            //start chat
            if (1 == (int)$this->_request->getPost('hidMode')) {
                if ($uid != $rowChat['uid']) {
                    $this->_redirect($this->_baseUrl . '/chat/view?cid=' . $cid);
            	    return;
                }
                //start
                require_once 'Bll/Chat/Chat.php';
                $bllChat = new Bll_Chat_Chat();
                $result = $bllChat->startChat($cid);
                if ($result) {
                    $this->_redirect($this->_baseUrl . '/chat/room?cid=' . $cid);
                    return;
                }
            }
            //refuse chat
            else if (2 == (int)$this->_request->getPost('hidMode')) {
                if ($uid == $rowChat['uid']) {
                    $this->_redirect($this->_baseUrl . '/chat/view?cid=' . $cid);
            	    return;
                }
                //refuse
                $message = $this->_request->getPost('txtMessage');
                require_once 'Bll/Chat/Member.php';
                $bllChatMem = new Bll_Chat_Member();
                $result = $bllChatMem->delMember($cid, $uid, mb_substr($message, 0, 200, 'UTF-8'));
                if ($result) {
                    $this->_redirect($this->_baseUrl . '/chat/add?cancel=90001&tuid=' . $rowChat['uid'] .'&cname=' . urlencode($rowChat['title']));
                    return;
                }
            }

            $this->_redirect($this->_baseUrl . '/chat/view?cid=' . $cid);
            return;
        }

        //init view page
        else {
            //get cid
            $cid = (int)$this->_request->getParam('cid');
            if (empty($cid)) {
                $this->_redirect($this->_baseUrl . '/chat');
            	return;
            }

            //get chat info
            require_once 'Dal/Chat/Chat.php';
            $dalChat = Dal_Chat_Chat::getDefaultInstance();
            $rowChat = $dalChat->getChatById($cid);
            if (empty($rowChat)) {
                $this->_redirect($this->_baseUrl . '/chat');
            	return;
            }
            else if (1 == $rowChat['iscanceled']) {
                $this->_redirect($this->_baseUrl . '/chat');
            	return;
            }
            else if (1 == $rowChat['isstarted']) {
                $this->_redirect($this->_baseUrl . '/chat/room?cid=' . $cid);
                return;
            }

            require_once 'Bll/User.php';
            require_once 'Dal/Chat/Member.php';
            $dalChatMem = Dal_Chat_Member::getDefaultInstance();
            //is chat member
            if (!$dalChatMem->isChatMember($cid, $uid)) {
                $this->_redirect($this->_baseUrl . '/chat');
            	return;
            }

            //chat member list
            $lstMem = $dalChatMem->listChatMember($cid);
            Bll_User::appendPeople($lstMem, 'uid');

            //chat attender confirm list
            require_once 'Dal/Chat/AttendConfirm.php';
            $dalAC = Dal_Chat_AttendConfirm::getDefaultInstance();
            $lstAttendC = $dalAC->listChatAttendConfirm($cid);
            if (!empty($lstAttendC) && count($lstAttendC) > 0) {
                Bll_User::appendPeople($lstAttendC, 'uid');
            }

            //is chat owner
            if ($uid == $rowChat['uid']) {
                $this->view->isOwner = 1;
            }

            //is chat ready to start
            $dateNow = time();
            $intStartTime = strtotime($rowChat['start_time']);
            if ( $dateNow >= $intStartTime
                 || ($intStartTime - $dateNow) <= (15*60) ) {
                $this->view->isReady = 1;

                if (1 == $this->view->isOwner) {
                    //activity
                    require_once 'Bll/Chat/Activity.php';
                    $this->view->activity = Bll_Chat_Activity::getActivity('', '', array('chat_name'=>$rowChat['title']), 6);
                    $this->view->activityUser = $rowChat['uid'];
                }
            }

            $aryStartTime = getdate($intStartTime);
            $this->view->chatName = $rowChat['title'];
            $this->view->chatDate = $aryStartTime['mon'] . '/' . $aryStartTime['mday']
                                    . '（' . $this->_getWdayJp($aryStartTime['wday']) . '）の'
                                    . (strlen($aryStartTime['hours']) == 1 ? ('0' . $aryStartTime['hours']) : $aryStartTime['hours'])
                                    . ':' . (strlen($aryStartTime['minutes']) == 1 ? ('0' . $aryStartTime['minutes']) : $aryStartTime['minutes']);
            $this->view->chatMessage = $rowChat['message'];
            $this->view->cid = $cid;

            $this->view->lstMem = $lstMem;
            $this->view->lstAttendC = $lstAttendC;
            $this->view->cntMem = count($lstMem);
            $this->view->cntAttendC = count($lstAttendC);
            $this->render();
        }
    }

	/**
     * confirm chat Action
     *
     */
    public function confirmAction()
    {
        $uid = $this->_user->getId();

        //do post request
        if ($this->_request->isPost()) {
            $cid = (int)$this->_request->getPost('cid');
            if (empty($cid)) {
                $this->_redirect($this->_baseUrl . '/chat');
            	return;
            }

            //get chat info
            require_once 'Dal/Chat/Chat.php';
            $dalChat = Dal_Chat_Chat::getDefaultInstance();
            $rowChat = $dalChat->getChatById($cid);
            if (empty($rowChat)) {
                $this->_redirect($this->_baseUrl . '/chat');
            	return;
            }
            else if (1 == $rowChat['iscanceled']) {
                $this->_redirect($this->_baseUrl . '/chat');
            	return;
            }

            require_once 'Dal/Chat/Member.php';
            $dalChatMem = Dal_Chat_Member::getDefaultInstance();
            //is chat member
            if ($dalChatMem->isChatMember($cid, $uid)) {
                $this->_redirect($this->_baseUrl . '/chat/view?cid=' . $cid);
            	return;
            }

            //join chat
            require_once 'Bll/Chat/Member.php';
            $bllChatMem = new Bll_Chat_Member();
            $message = $this->_request->getPost('txtMessage');
            if (1 == (int)$this->_request->getPost('hidMode')) {
                //join
                $aryMem = array();
                $aryMem['cid'] = $cid;
                $aryMem['uid'] = $uid;
                $aryMem['message'] = mb_substr($message, 0, 200, 'UTF-8');
                $result = $bllChatMem->acceptMember($aryMem);
                if ($result) {
                    return $this->_forward('confirmdone', 'chat', 'default', array('cid' => $cid));
                }
            }
            //refuse chat
            else if (0 == (int)$this->_request->getPost('hidMode')) {
                //refuse
                $result = $bllChatMem->refuseMember($cid, $uid, mb_substr($message, 0, 200, 'UTF-8'));
                if ($result) {
                    $this->_redirect($this->_baseUrl . '/chat/add?cancel=90001&tuid=' . $rowChat['uid'] .'&cname=' . urlencode($rowChat['title']));
                    return;
                }
            }

            $this->_redirect($this->_baseUrl . '/chat');
            return;
        }

        //init view page
        else {
            //get cid
            $cid = (int)$this->_request->getParam('cid');
            if (empty($cid)) {
                $this->_redirect($this->_baseUrl . '/chat');
            	return;
            }

            //get chat info
            require_once 'Dal/Chat/Chat.php';
            $dalChat = Dal_Chat_Chat::getDefaultInstance();
            $rowChat = $dalChat->getChatById($cid);
            if (empty($rowChat)) {
                $this->_redirect($this->_baseUrl . '/chat');
            	return;
            }
            else if (1 == $rowChat['iscanceled']) {
                $this->_redirect($this->_baseUrl . '/chat');
            	return;
            }

            require_once 'Bll/User.php';
            require_once 'Dal/Chat/Member.php';
            $dalChatMem = Dal_Chat_Member::getDefaultInstance();
            //is chat member
            if ($dalChatMem->isChatMember($cid, $uid)) {
                $this->_redirect($this->_baseUrl . '/chat/view?cid=' . $cid);
            	return;
            }

            //chat member list
            $lstMem = $dalChatMem->listChatMember($cid);
            Bll_User::appendPeople($lstMem, 'uid');

            //chat attender confirm list
            require_once 'Dal/Chat/AttendConfirm.php';
            $dalAC = Dal_Chat_AttendConfirm::getDefaultInstance();
            $lstAttendC = $dalAC->listChatAttendConfirm($cid);
            if (!empty($lstAttendC) && count($lstAttendC) > 0) {
                Bll_User::appendPeople($lstAttendC, 'uid');
            }

            //is chat ready to start
            $intStartTime = strtotime($rowChat['start_time']);
            $aryStartTime = getdate($intStartTime);
            $this->view->chatName = $rowChat['title'];
            $this->view->chatDate = $aryStartTime['mon'] . '/' . $aryStartTime['mday']
                                    . '（' . $this->_getWdayJp($aryStartTime['wday']) . '）の'
                                    . (strlen($aryStartTime['hours']) == 1 ? ('0' . $aryStartTime['hours']) : $aryStartTime['hours'])
                                    . ':' . (strlen($aryStartTime['minutes']) == 1 ? ('0' . $aryStartTime['minutes']) : $aryStartTime['minutes']);
            $this->view->chatMessage = $rowChat['message'];
            $this->view->cid = $cid;
            $ownerInfo = Bll_User::getPerson($rowChat['uid']);
            $this->view->ownerName = $ownerInfo->getDisplayName();
            $this->view->ownerUrl = $ownerInfo->getProfileUrl();

            $this->view->lstMem = $lstMem;
            $this->view->lstAttendC = $lstAttendC;
            $this->render();
        }
    }

	/**
     * confirmdone Action
     *
     */
    public function confirmdoneAction()
    {
        //get cid
        $cid = $this->_request->getParam('cid');
        if (empty($cid)) {
            $this->_redirect($this->_baseUrl . '/chat');
        	return;
        }

        //get chat info
        require_once 'Dal/Chat/Chat.php';
        $dalChat = Dal_Chat_Chat::getDefaultInstance();
        $rowChat = $dalChat->getChatById($cid);
        if (empty($rowChat)) {
            $this->_redirect($this->_baseUrl . '/chat');
        	return;
        }

        $intStartTime = strtotime($rowChat['start_time']);
        $aryStartTime = getdate($intStartTime);
        $this->view->chatName = $rowChat['title'];
        $this->view->chatDate = $aryStartTime['mon'] . '/' . $aryStartTime['mday']
                                . '（' . $this->_getWdayJp($aryStartTime['wday']) . '）の'
                                . (strlen($aryStartTime['hours']) == 1 ? ('0' . $aryStartTime['hours']) : $aryStartTime['hours'])
                                . ':' . (strlen($aryStartTime['minutes']) == 1 ? ('0' . $aryStartTime['minutes']) : $aryStartTime['minutes']);

        //activity
        require_once 'Bll/Chat/Activity.php';
        $this->view->activity = Bll_Chat_Activity::getActivity('', '', array('chat_name'=>$rowChat['title']), 2);
        $this->view->activityUser = $rowChat['uid'];

        $this->render();
    }

    /**
     * chat feed list Action
     *
     */
    public function feedlistAction()
    {
        $this->render();
    }

	/**
     * room Action
     *
     */
    public function roomAction()
    {

        $cid = $this->_request->getParam('cid');
        $uid = $this->_user->getId();
        if (empty($cid)) {
            $this->_redirect($this->_baseUrl . '/chat/index');
        	return;
        }

        //get chat info
        require_once 'Dal/Chat/Chat.php';
        $dalChat = Dal_Chat_Chat::getDefaultInstance();
        $rowChat = $dalChat->getChatById($cid);
        //no such chat
        if (empty($rowChat)) {
            $this->_redirect($this->_baseUrl . '/chat');
        	return;
        }
        //chat already ended
        if (1 == $rowChat['iscanceled']) {
            $this->_redirect($this->_baseUrl . '/chat');
        	return;
        }
        //chat not started
        if (1 != $rowChat['isstarted']) {
            $this->_redirect($this->_baseUrl . '/chat');
        	return;
        }

        require_once 'Dal/Chat/Member.php';
        $dalMem = Dal_Chat_Member::getDefaultInstance();
        //is not chat member
        if (!$dalMem->isChatMember($cid, $uid)) {
            $this->_redirect($this->_baseUrl . '/chat');
        	return;
        }
        //get last sys message time id
        $rowMem = $dalMem->getChatMemberByPk($cid, $uid);
        $dalMem->updateChatMember(array('isleave' => -1), $cid, $uid);

        //get user info
        $userInfo = Bll_User::getPerson($uid);

        /*
        //get chat member
        $lstMember = $dalMem->listChatMember($cid, 1, 1000);
        require_once 'Bll/User.php';
        $bllUser = new Bll_User();
        Bll_User::appendPeople($lstMember, 'uid');
		*/

        //insert sysytem message
        require_once 'Bll/Chat/Detail.php';
        $bllChat = new Bll_Chat_Detail();
        $aryInfo = array();
        $aryInfo['cid'] = $cid;
        $aryInfo['uid'] = $uid;
        $aryInfo['content'] = $this->_user->getDisplayName() . 'さんがチャットに参加しました。';
        $aryInfo['issystem'] = 1;
        $result = $bllChat->newDetail($aryInfo);

        $this->view->isOwner = 0;
        if ($rowChat['uid'] == $uid) {
            $this->view->isOwner = 1;
            if (0 == $rowMem['last_detail_id_sys']) {
                //activity
                require_once 'Bll/Chat/Activity.php';
                $this->view->activity = Bll_Chat_Activity::getActivity('', '', array('chat_name'=>$rowChat['title']), 7);
            }
        }

        $ownerInfo = Bll_User::getPerson($rowChat['uid']);
        $this->view->ownerName = $ownerInfo->getDisplayName();

        $this->view->chatStartTime = strtotime ($rowChat['start_time']);
        $this->view->chatInfo = $rowChat;
        //$this->view->chatMember = $lstMember;
        $this->view->userInfo = $userInfo;

        $this->view->lastDetailId = 0;//$rowMem['last_detail_id'];
        $this->view->lastDetailIdSys = $result - 1;//$rowMem['last_detail_id_sys'];
        $this->view->curDate = time();

        $this->render();
    }

	/**
     * get weekday value of japan
     *
     * @param integer $wday
     * @return string
     */
    private function _getWdayJp($weekday)
    {
        switch ((int)$weekday) {
            case 0 :
                $weekdaychar = '日';
                break;
            case 1 :
                $weekdaychar = '月';
                break;
            case 2 :
                $weekdaychar = '火';
                break;
            case 3 :
                $weekdaychar = '水';
                break;
            case 4 :
                $weekdaychar = '木';
                break;
            case 5 :
                $weekdaychar = '金';
                break;
            case 6 :
                $weekdaychar = '土';
                break;
            default :
                $weekdaychar = '日';
        }
        return $weekdaychar;
    }

    /**
     * magic function
     * if call the function is undefined,then forward to not found
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
<?php

require_once 'Bll/Abstract.php';

/**
 * Mixi App Chat logic Operation
 *
 * @package    Bll/Chat
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/05/08    zhangxin
 */
final class Bll_Chat_Chat extends Bll_Abstract
{

    /**
     * new chat
     *
     * @param array $aryChatInfo
     * @param array $aryInviteIds
     * @return integer
     */
    public function newChat($aryChatInfo, $aryInviteIds)
    {
        try {
            require_once 'Dal/Chat/Chat.php';
            require_once 'Dal/Chat/Member.php';
            require_once 'Dal/Chat/AttendConfirm.php';
            $dalChat = Dal_Chat_Chat::getDefaultInstance();
            $dalChatMem = Dal_Chat_Member::getDefaultInstance();
            $dalChatConfirm = Dal_Chat_AttendConfirm::getDefaultInstance();
            $this->_wdb->beginTransaction();

            //insert chat
            $aryChatInfo['member_count'] = 1;
            $aryChatInfo['create_time'] = date('Y-m-d H:i:s');
            $cid = $dalChat->insertChat($aryChatInfo);

            //insert chat member
            $aryMemInfo = array('cid' => $cid, 'uid' => $aryChatInfo['uid'], 'create_time' => date('Y-m-d H:i:s'));
            $dalChatMem->insertChatMember($aryMemInfo);

            //insert chat invite and send feed
            require_once 'Bll/Chat/FeedMessage.php';
            $bllFeed = new Bll_Chat_FeedMessage();
            require_once 'Bll/User.php';
            $ownerInfo = Bll_User::getPerson($aryChatInfo['uid']);
            $ownerName = $ownerInfo->getDisplayName();

            $confirmIds = array();
            foreach ($aryInviteIds as $key => $value) {
                $aryInvite = array();
                $aryInvite['cid'] = $cid;
                $aryInvite['uid'] = $value;
                $aryInvite['create_time'] = date('Y-m-d H:i:s');
                $confirmIds[$value] = $dalChatConfirm->insertChatAttendConfirm($aryInvite);

                //send feed message
                $bllFeed->newFeedMessage(1, $cid, $aryChatInfo['uid'], $value, array('{*actor*}' => $ownerName));
            }

            //send ready begin message
            $intDiff = strtotime($aryChatInfo['start_time']) - time();
            if ($intDiff<=60*15 && $intDiff>0){
                $dalChat->updateChat(array('isbatchfeedsent' => 1), $cid);
                $bllFeed->newFeedMessage(6, $cid, $aryChatInfo['uid'], $aryChatInfo['uid'], array('{*chat_name*}' => $aryChatInfo['title']));
            }

            $this->_wdb->commit();
            return $cid;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            debug_log('Bll/Chat/Chat/new:' . $e->getMessage());
            return false;
        }
    }


    /**
     * edit chat
     *
     * @param integer $cid
     * @param array $aryChatInfo
     * @param array $aryInviteIds
     * @return boolean
     */
    public function editChat($cid, $aryChatInfo, $aryInviteIds)
    {
        try {
            require_once 'Dal/Chat/Chat.php';
            require_once 'Dal/Chat/Member.php';
            require_once 'Dal/Chat/AttendConfirm.php';
            $dalChat = Dal_Chat_Chat::getDefaultInstance();
            $dalChatMem = Dal_Chat_Member::getDefaultInstance();
            $dalChatConfirm = Dal_Chat_AttendConfirm::getDefaultInstance();
            $rowChat = $dalChat->getChatById($cid);
            if (empty($rowChat)) {
                return false;
            }

            $this->_wdb->beginTransaction();

            //update chat
            $dalChat->updateChat($aryChatInfo, $cid);

        	//insert feed
            require_once 'Bll/Chat/FeedMessage.php';
            $bllFeed = new Bll_Chat_FeedMessage();
            require_once 'Bll/User.php';
            $ownerInfo = Bll_User::getPerson($rowChat['uid']);
            $ownerName = $ownerInfo->getDisplayName();
            //send ready begin message
            $isSendBeginFeed = false;
            $intDiff = strtotime($aryChatInfo['start_time']) - time();
            if ($intDiff<=60*15 && $intDiff>0){
                $isSendBeginFeed = true;
                $dalChat->updateChat(array('isbatchfeedsent' => 1), $cid);
            }

            //send feed -member
            $lstMem = $dalChatMem->listChatMember($cid);
            foreach ($lstMem as $memData) {
                if ($memData['uid'] != $rowChat['uid']) {
                    $bllFeed->newFeedMessage(3, $cid, $rowChat['uid'], $memData['uid'],
                                             array('{*actor*}' => $ownerName ,'{*chat_name*}' => $aryChatInfo['title']));
                }
                //send ready begin message
                if ($isSendBeginFeed) {
                    $bllFeed->newFeedMessage(6, $cid, $rowChat['uid'], $memData['uid'], array('{*chat_name*}' => $aryChatInfo['title']));
                }
            }

            //send feed -confirm
            $lstCfm = $dalChatConfirm->listChatAttendConfirmUids($cid);
            foreach ($lstCfm as $cfmData) {
                $bllFeed->newFeedMessage(3, $cid, $rowChat['uid'], $cfmData['uid'],
                                         array('{*actor*}' => $ownerName ,'{*chat_name*}' => $aryChatInfo['title']));
            }

            //delete chat attend confirm
            $dalChatConfirm->deleteChatAttendConfirmByCid($cid);

            //update confirm message
            $confirmIds = array();
            $memberIds = array();
            if (!empty($aryInviteIds) && count($aryInviteIds) > 0 ) {
                foreach ($aryInviteIds as $key => $userId) {
                    //update chat invite
                    $rowMember = $dalChatMem->getChatMemberByPk($cid, $userId);
                    //not yet member
                    if (empty($rowMember)) {
                        $rowConfirm = $dalChatConfirm->getChatAttendConfirmByCidUid($cid, $userId);
                        $aryInvite = array();
                        if (empty($rowConfirm)) {
                            //insert chat invite new
                            $aryInvite['cid'] = $cid;
                            $aryInvite['uid'] = $userId;
                            $aryInvite['create_time'] = date('Y-m-d H:i:s');
                            $confirmIds[$userId] = $dalChatConfirm->insertChatAttendConfirm($aryInvite);
							//send feed message
                			$bllFeed->newFeedMessage(1, $cid, $rowChat['uid'], $userId, array('{*actor*}' => $ownerName));
                        }
                        else {
                            $aryInvite['isdenied'] = 0;
                            $dalChatConfirm->updateChatAttendConfirmByCidUid($aryInvite, $cid, $userId);
                            $confirmIds[$userId] = $rowConfirm['id'];
                        }
                    }
                    //already member
                    else {
                        $memberIds[] = $userId;
                    }
                }//end for
            }



            $this->_wdb->commit();
            return true;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            debug_log('Bll/Chat/Chat/edit:' . $e->getMessage());
            return false;
        }
    }

	/**
     * delete chat
     *
     * @param integer $cid
     * @param string $cMessage
     * @return boolean
     */
    public function delChat($cid, $cMessage)
    {
        try {
            require_once 'Dal/Chat/Chat.php';
            require_once 'Dal/Chat/Member.php';
            require_once 'Dal/Chat/AttendConfirm.php';
            $dalChat = Dal_Chat_Chat::getDefaultInstance();
            $dalChatMem = Dal_Chat_Member::getDefaultInstance();
            $dalChatConfirm = Dal_Chat_AttendConfirm::getDefaultInstance();
            $rowChat = $dalChat->getChatById($cid);
            if (empty($rowChat) || 1 == $rowChat['iscanceled'] || 1 == $rowChat['isstarted']) {
                return  false;
            }

            $this->_wdb->beginTransaction();

            //delete chat attend confirm
            //$dalChatConfirm->deleteChatAttendConfirmByCid($cid);
            //delete chat member send message?

            //delete chat
            $aryChatInfo = array();
            $aryChatInfo['iscanceled'] = 1;
            $aryChatInfo['cancel_message'] = $cMessage;
            $dalChat->updateChat($aryChatInfo, $cid);

            //insert feed
            require_once 'Bll/Chat/FeedMessage.php';
            $bllFeed = new Bll_Chat_FeedMessage();
            require_once 'Bll/User.php';
            $ownerInfo = Bll_User::getPerson($rowChat['uid']);
            $ownerName = $ownerInfo->getDisplayName();
            //send feed -member
            $lstMem = $dalChatMem->listChatMember($cid);
            foreach ($lstMem as $memData) {
                if ($memData['uid'] != $rowChat['uid']) {
                    $bllFeed->newFeedMessage(4, $cid, $rowChat['uid'], $memData['uid'],
                                             array('{*actor*}' => $ownerName ,'{*chat_name*}' => $rowChat['title']));
                }
            }

            $this->_wdb->commit();
            return true;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            debug_log('Bll/Chat/Chat/del:' . $e->getMessage());
            return false;
        }
    }

	/**
     * start chat
     *
     * @param integer $cid
     * @return boolean
     */
    public function startChat($cid)
    {
        try {
            require_once 'Dal/Chat/Chat.php';
            $dalChat = Dal_Chat_Chat::getDefaultInstance();

            $rowChat = $dalChat->getChatById($cid);
            if (empty($rowChat) || 1 == $rowChat['iscanceled'] || 1 == $rowChat['isstarted']) {
                return  false;
            }

            $this->_wdb->beginTransaction();

            //start chat
            $result = $dalChat->updateChat(array('isstarted' => 1), $cid);

            //insert feed message
            if ($result) {
                require_once 'Bll/Chat/FeedMessage.php';
                $bllFeed = new Bll_Chat_FeedMessage();
                require_once 'Dal/Chat/Member.php';
                $dalChatMem = Dal_Chat_Member::getDefaultInstance();
                $lstMem = $dalChatMem->listChatMember($cid);
                foreach ($lstMem as $memData) {
                    if ($memData['uid'] != $rowChat['uid']) {
                        $bllFeed->newFeedMessage(7, $cid, $rowChat['uid'], $memData['uid'], array('{*chat_name*}' => $rowChat['title']));
                    }
                }
            }

            $this->_wdb->commit();

            return true;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            debug_log('Bll/Chat/Chat/startchat:' . $e->getMessage());
            return false;
        }
    }

}
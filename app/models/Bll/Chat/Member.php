<?php

require_once 'Bll/Abstract.php';

/**
 * Mixi App Chat Member logic Operation
 *
 * @package    Bll/Chat
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/05/11    zhangxin
 */
final class Bll_Chat_Member extends Bll_Abstract
{

    /**
     * add chat member
     *
     * @param array $aryMember
     * @return boolean
     */
    public function acceptMember($aryMember)
    {
        try {
            require_once 'Dal/Chat/Chat.php';
            require_once 'Dal/Chat/Member.php';
            require_once 'Dal/Chat/AttendConfirm.php';
            $dalChat = Dal_Chat_Chat::getDefaultInstance();
            $dalChatMember = Dal_Chat_Member::getDefaultInstance();
            $dalChatAttC = Dal_Chat_AttendConfirm::getDefaultInstance();

            $rowChat = $dalChat->getChatById($aryMember['cid']);
            if (empty($rowChat)) {
                return  false;
            }

            //check is chat already canceled
            if ($dalChat->isChatEnded($aryMember['cid'])) {
                return false;
            }

            //check is already member
            if ($dalChatMember->isChatMember($aryMember['cid'], $aryMember['uid'])) {
                return false;
            }

            $this->_wdb->beginTransaction();

            //delete chat attend confirm
            $dalChatAttC->deleteChatAttendConfirmByCidUid($aryMember['cid'], $aryMember['uid']);

            //insert chat member
            $aryMember['create_time'] = date('Y-m-d H:i:s');
            $dalChatMember->insertChatMember($aryMember);

            //update member count
            $cntMem = $dalChatMember->getChatMemberCount($aryMember['cid']);
            $dalChat->updateChat(array('member_count' => $cntMem), $aryMember['cid']);

            //send feed message
            require_once 'Bll/Chat/FeedMessage.php';
            $bllFeed = new Bll_Chat_FeedMessage();
            require_once 'Bll/User.php';
            $actorInfo = Bll_User::getPerson($aryMember['uid']);
            $actorName = $actorInfo->getDisplayName();
            $bllFeed->newFeedMessage(2, $aryMember['cid'], $aryMember['uid'], $rowChat['uid'],
                                     array('{*actor*}' => $actorName, '{*chat_name*}' => $rowChat['title']));

            $this->_wdb->commit();
            return true;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            debug_log('Bll/Chat/Member/add:' . $e->getMessage());
            return false;
        }
    }

	/**
     * refuse chat member
     *
     * @param integer $cid
     * @param string $uid
     * @param string $message
     * @return boolean
     */
    public function refuseMember($cid, $uid, $message)
    {
        try {
            require_once 'Dal/Chat/Chat.php';
            require_once 'Dal/Chat/Member.php';
            require_once 'Dal/Chat/AttendConfirm.php';
            $dalChat = Dal_Chat_Chat::getDefaultInstance();
            $dalChatMember = Dal_Chat_Member::getDefaultInstance();
            $dalChatAttC = Dal_Chat_AttendConfirm::getDefaultInstance();

            $rowChat = $dalChat->getChatById($cid);
            if (empty($rowChat)) {
                return  false;
            }

            //check is chat already canceled
            if ($dalChat->isChatEnded($cid)) {
                return false;
            }

            //check is already member
            if ($dalChatMember->isChatMember($cid, $uid)) {
                return false;
            }

            $this->_wdb->beginTransaction();

            //update chat attend confirm
            $attInfo = array();
            $attInfo['message'] = $message;
            $attInfo['isdenied'] = 1;
            $dalChatAttC->updateChatAttendConfirmByCidUid($attInfo, $cid, $uid);

            //send feed message
            require_once 'Bll/Chat/FeedMessage.php';
            $bllFeed = new Bll_Chat_FeedMessage();
            require_once 'Bll/User.php';
            $actorInfo = Bll_User::getPerson($uid);
            $actorName = $actorInfo->getDisplayName();
            $bllFeed->newFeedMessage(5, $cid, $uid, $rowChat['uid'],
                                     array('{*actor*}' => $actorName, '{*chat_name*}' => $rowChat['title']));

            $this->_wdb->commit();
            return true;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            debug_log('Bll/Chat/Member/refuse:' . $e->getMessage());
            return false;
        }
    }

    /**
     * del chat member
     *
     * @param integer $cid
     * @param string $uid
     * @param string $message
     * @return boolean
     */
    public function delMember($cid, $uid, $message)
    {
        try {
            require_once 'Dal/Chat/Chat.php';
            require_once 'Dal/Chat/Member.php';
            require_once 'Dal/Chat/AttendConfirm.php';
            $dalChat = Dal_Chat_Chat::getDefaultInstance();
            $dalChatMember = Dal_Chat_Member::getDefaultInstance();
            $dalChatAttC = Dal_Chat_AttendConfirm::getDefaultInstance();

            $rowChat = $dalChat->getChatById($cid);
            if (empty($rowChat)) {
                return  false;
            }
            //check is chat already canceled
            if ($dalChat->isChatEnded($cid)) {
                return false;
            }

            //check is member
            if (!$dalChatMember->isChatMember($cid, $uid)) {
                return false;
            }

            $this->_wdb->beginTransaction();

            //insert chat attend confirm
            $aryInvite = array();
            $aryInvite['cid'] = $cid;
            $aryInvite['uid'] = $uid;
            $aryInvite['isdenied'] = 1;
            $aryInvite['message'] = $message;
            $aryInvite['create_time'] = date('Y-m-d H:i:s');
            $dalChatAttC->insertChatAttendConfirm($aryInvite);

            //delete chat member
            $dalChatMember->deleteChatMember($cid, $uid);

            //update member count
            $cntMem = $dalChatMember->getChatMemberCount($cid);
            $dalChat->updateChat(array('member_count' => $cntMem), $cid);

            //send feed message
            require_once 'Bll/Chat/FeedMessage.php';
            $bllFeed = new Bll_Chat_FeedMessage();
            require_once 'Bll/User.php';
            $actorInfo = Bll_User::getPerson($uid);
            $actorName = $actorInfo->getDisplayName();
            $bllFeed->newFeedMessage(5, $cid, $uid, $rowChat['uid'],
                                     array('{*actor*}' => $actorName, '{*chat_name*}' => $rowChat['title']));

            $this->_wdb->commit();
            return true;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            debug_log('Bll/Chat/Member/del:' . $e->getMessage());
            return false;
        }
    }

}
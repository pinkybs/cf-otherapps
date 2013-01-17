<?php

require_once 'Bll/Abstract.php';

/**
 * Mixi App Chat Detail logic Operation
 *
 * @package    Bll/Chat
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/05/11    zhangxin
 */
final class Bll_Chat_Detail extends Bll_Abstract
{

    /**
     * new detail
     *
     * @param array $aryDetailInfo
     * @return boolean
     */
    public function newDetail($aryDetailInfo)
    {
        try {
            require_once 'Dal/Chat/Detail.php';
            $dalChatDetail = new Dal_Chat_Detail();

            $this->_wdb->beginTransaction();

            //insert chat detail
            $aryDetailInfo['create_time'] = time();
            $id = $dalChatDetail->insertChatDetail($aryDetailInfo);

            $this->_wdb->commit();
            return $aryDetailInfo['create_time'];
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            debug_log('Bll/Chat/Detail/new:' . $e->getMessage());
            return false;
        }
    }


	/**
     * exit
     *
     * @param array $aryDetailInfo
     * @param boolean $blnIsOwner
     * @return boolean
     */
    public function exitDetail($aryDetailInfo, $blnIsOwner)
    {
        try {
            require_once 'Dal/Chat/Detail.php';
            $dalChatDetail = Dal_Chat_Detail::getDefaultInstance();
            require_once 'Dal/Chat/Member.php';
            $dalChatMem = Dal_Chat_Member::getDefaultInstance();
            require_once 'Dal/Chat/Chat.php';
            $dalChat = Dal_Chat_Chat::getDefaultInstance();

            $rowChat = $dalChat->getChatById($aryDetailInfo['cid']);
            if (empty($rowChat) || 1 == $rowChat['iscanceled']) {
                return  false;
            }

            $this->_wdb->beginTransaction();

            //insert chat detail sysmessage
            $aryNew = array();
            $aryNew['cid'] = $aryDetailInfo['cid'];
            $aryNew['uid'] = $aryDetailInfo['uid'];
            $aryNew['content'] = $aryDetailInfo['content'];
            $aryNew['issystem'] = $aryDetailInfo['issystem'];
            $aryNew['create_time'] = time();
            $id = $dalChatDetail->insertChatDetail($aryNew);

            //chat member update
            $aryMemberLastId = array();
            $aryMemberLastId['last_detail_id'] = $aryDetailInfo['last_detail_id'];
            $aryMemberLastId['last_detail_id_sys'] = $aryNew['create_time'];
            $aryMemberLastId['isleave'] = 1;
            $dalChatMem->updateChatMember($aryMemberLastId, $aryDetailInfo['cid'], $aryDetailInfo['uid']);

            //is owner close chat
            if ($blnIsOwner) {
                $dalChat->updateChat(array('iscanceled' => 1, 'end_time' => date('Y-m-d H:i:s')), $aryDetailInfo['cid']);
            }

            $this->_wdb->commit();
            return true;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            debug_log('Bll/Chat/Detail/exit:' . $e->getMessage());
            return false;
        }
    }

	/**
     * extend detail
     *
     * @param integer $cid
     * @return boolean
     */
    public function extendDetail($cid)
    {
        try {
            require_once 'Dal/Chat/Detail.php';
            $dalChatDetail = Dal_Chat_Detail::getDefaultInstance();
            require_once 'Dal/Chat/Chat.php';
            $dalChat = Dal_Chat_Chat::getDefaultInstance();

            $rowChat = $dalChat->getChatById($cid);
            if (empty($rowChat) || 1 == $rowChat['iscanceled']) {
                return  false;
            }

            $this->_wdb->beginTransaction();

            //insert chat detail sysmessage
            $aryNew = array();
            $aryNew['cid'] = $cid;
            $aryNew['uid'] = $rowChat['uid'];
            $aryNew['content'] = 'チャットを1時間延長しました。';
            $aryNew['issystem'] = 1;
            $aryNew['create_time'] = time();
            $id = $dalChatDetail->insertChatDetail($aryNew);

            $cntExtended = $rowChat['extend_count'] + 1;
            $dalChat->updateChat(array('istimeout_alerted' => 0, 'extend_count' => $cntExtended), $cid);

            $this->_wdb->commit();
            return true;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            debug_log('Bll/Chat/Detail/exit:' . $e->getMessage());
            return false;
        }
    }

}
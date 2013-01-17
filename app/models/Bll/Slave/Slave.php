<?php

require_once 'Bll/Abstract.php';

/**
 * Mixi App Slave logic Operation
 *
 * @package    Bll/Slave
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/06/23    zhangxin
 */
final class Bll_Slave_Slave extends Bll_Abstract
{

    /**
     * daily visit gift
     *
     * @param string $uid
     * @param string $valid
     * @param integer $money
     * @return boolean
     */
    public function dailyVisitGift($uid, $valid, $money)
    {
        try {
            require_once 'Dal/Slave/Slave.php';
            $dalSlave = Dal_Slave_Slave::getDefaultInstance();

            $this->_wdb->beginTransaction();

            $rowSlave = $dalSlave->getSlaveByIdLock($uid);
            if (empty($rowSlave)) {
                $this->_wdb->rollBack();
                return false;
            }
            else if (empty($rowSlave['daily_visit_gift_flag']) || $valid != $rowSlave['daily_visit_gift_flag']) {
                $this->_wdb->rollBack();
                return false;
            }

            $aryInfo = array();
            $aryInfo['daily_visit_gift_flag'] = '';
            $aryInfo['cash'] = $rowSlave['cash'] + $money;
            $dalSlave->updateSlave($aryInfo, $uid);

            //insert change log
            require_once 'Dal/Slave/CashPriceChangeLog.php';
            $dalChangeLog = Dal_Slave_CashPriceChangeLog::getDefaultInstance();
            $aryLog = array();
            $aryLog['actor_uid'] = $uid;
            $aryLog['target_uid'] = $uid;
            $aryLog['c_floating'] = $money;
            $aryLog['type'] = 1;
            $aryLog['create_time'] = time();
            $dalChangeLog->insertCashPriceChangeLog($aryLog);

            $this->_wdb->commit();

            //send feed
            require_once 'Bll/User.php';
            require_once 'Bll/Slave/FeedMessage.php';
            $bllFeed = new Bll_Slave_FeedMessage();
            $userInfo = Bll_User::getPerson($uid);
            $userName = $userInfo->getDisplayName();
            //$userUrl = $userInfo->getProfileUrl();
            //$userPic = $userInfo->getThumbnailUrl();
            $actor = '<a href="/slave/profile?uid=' . $uid . '" >' . $userName . '</a>';

            $aryMsgMine = array('{*money*}' => '￥' . number_format($money));
            $bllFeed->newFeedMessage(1, 1, 'feed_tpl_actor', $uid, $uid, $aryMsgMine, 1);
            //send feed to friends already installed app
            require_once 'Bll/Slave/Friend.php';
            $aryIds = Bll_Slave_Friend::getFriends($uid);
            $aryMsgFriend = array('{*actor*}' => $actor, '{*money*}' => '￥' . number_format($money));
            foreach ($aryIds as $fid) {
                $bllFeed->newFeedMessage(1, 1, 'feed_tpl_friend', $uid, $fid, $aryMsgFriend);
            }

            return true;

        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            debug_log('Bll/Slave/Slave/dailyVisitGift:' . $e->getMessage());
            return false;
        }
    }

    /**
     * new slave user
     *
     * @param string $uid
     * @param boolean $isActive [true:using / false:not using]
     * @return boolean
     */
    public function newSlaveUser($uid, $isActive = false)
    {
        try {
            require_once 'Dal/Slave/Slave.php';
            $dalSlave = Dal_Slave_Slave::getDefaultInstance();

            $this->_wdb->beginTransaction();

            $aryInfo = array();
            $aryInfo['uid'] = $uid;
            $aryInfo['status'] = $isActive ? 0 : 1;
            $aryInfo['create_time'] = time();
            $dalSlave->insertSlave($aryInfo);

            $this->_wdb->commit();

            if ($isActive) {
                //send feed
                require_once 'Bll/User.php';
                require_once 'Bll/Slave/FeedMessage.php';
                $bllFeed = new Bll_Slave_FeedMessage();
                $userInfo = Bll_User::getPerson($uid);
                $userName = $userInfo->getDisplayName();
                //$userUrl = $userInfo->getProfileUrl();
                $userPic = $userInfo->getThumbnailUrl();
                $actor = '<a href="/slave/profile?uid=' . $uid . '" >' . $userName . '</a>';

                $bllFeed->newFeedMessage(1, 15, 'feed_tpl_actor', $uid, $uid, null, 1, $userPic);
                //send feed to friends already installed app
                require_once 'Bll/Slave/Friend.php';
                $aryIds = Bll_Slave_Friend::getFriends($uid);
                foreach ($aryIds as $fid) {
                    $bllFeed->newFeedMessage(1, 15, 'feed_tpl_friend', $uid, $fid, array('{*actor*}' => $actor), 0, $userPic);
                }
            }

            return true;

        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            debug_log('Bll/Slave/Slave/newSlaveUser:' . $e->getMessage());
            return false;
        }
    }

    /**
     * set slave nickname/balloon
     *
     * @param string $uid
     * @param string $tarUid
     * @param string $content
     * @param integer $mode [1-nickname / 2-balloon]
     * @return integer [0-failed 1-success 2-punished]
     */
    public function setNicknameOrBalloon($uid, $tarUid, $content, $mode)
    {
        try {
            require_once 'Dal/Slave/Slave.php';
            $dalSlave = Dal_Slave_Slave::getDefaultInstance();
            require_once 'Dal/Slave/Forbidword.php';
            $dalForbid = Dal_Slave_Forbidword::getDefaultInstance();

            $this->_wdb->beginTransaction();

            $rowMaster = $dalSlave->getSlaveByIdLock($uid);
            $rowSlave = $dalSlave->getSlaveByIdLock($tarUid);

            if ($uid != $rowSlave['master_id']) {
                $this->_wdb->rollBack();
                return false;
            }

            $isPunish = false;
            $aryForbidword = $dalForbid->listForbidword();
            foreach ($aryForbidword as $fword) {
                $pos = strpos($content, $fword['word']);
                if ($pos !== false) {
                    $isPunish = true;
                    break;
                }
            }

            //punish master
            if ($isPunish) {
                //update master info
                $aryMaster = array();
                $aryMaster['price'] = ceil($rowMaster['price'] * 0.8);
                $dalSlave->updateSlave($aryMaster, $uid);

                require_once 'Dal/Slave/CashPriceChangeLog.php';
                $dalChangeLog = Dal_Slave_CashPriceChangeLog::getDefaultInstance();
                $aryLog = array();
                $aryLog['actor_uid'] = $uid;
                $aryLog['target_uid'] = $tarUid;
                $aryLog['c_floating'] = 0;
                $aryLog['p_floating'] = '-' . ceil($rowMaster['price'] * 0.2);
                $aryLog['type'] = ($mode == 1 ? 7 : 8);
                $aryLog['create_time'] = time();
                $dalChangeLog->insertCashPriceChangeLog($aryLog);
                $result = 2;
            }
            //update slave info
            else {
                $arySlave = array();
                if (1 == $mode) {
                    $arySlave['nickname'] = $content;
                }
                else {
                    $arySlave['balloon'] = $content;
                }
                $dalSlave->updateSlave($arySlave, $tarUid);
                $result = 1;
            }

            $this->_wdb->commit();

            //send feed
            if (!$isPunish && 1 == $mode) {
                require_once 'Bll/User.php';
                require_once 'Bll/Slave/FeedMessage.php';
                $bllFeed = new Bll_Slave_FeedMessage();
                $userInfo = Bll_User::getPerson($uid);
                $userName = $userInfo->getDisplayName();
                //$userUrl = $userInfo->getProfileUrl();
                //$userPic = $userInfo->getThumbnailUrl();
                $actor = '<a href="/slave/profile?uid=' . $uid . '" >' . $userName . '</a>';
                $tarInfo = Bll_User::getPerson($tarUid);
                $tarName = $tarInfo->getDisplayName();
                //$tarUrl = $tarInfo->getProfileUrl();
                //$tarPic = $tarInfo->getThumbnailUrl();
                $target = '<a href="/slave/profile?uid=' . $tarUid . '" >' . $tarName . '</a>';

                $aryMsgMine = array('{*target*}' => $target, '{*nickname*}' => htmlspecialchars($content));
                $bllFeed->newFeedMessage(1, 13, 'feed_tpl_actor', $uid, $uid, $aryMsgMine, 1);
                $aryMsgTar = array('{*actor*}' => $actor, '{*nickname*}' => htmlspecialchars($content));
                $bllFeed->newFeedMessage(1, 13, 'feed_tpl_target', $uid, $tarUid, $aryMsgTar, 1);
                //send feed to friends already installed app
                require_once 'Bll/Slave/Friend.php';
                $aryIds = Bll_Slave_Friend::getFriends($uid);
                $aryIds2 = Bll_Slave_Friend::getFriends($tarUid);
                $aryIdsTmp = array_merge($aryIds, $aryIds2);
                $aryIdsSend = array_unique($aryIdsTmp);
                $aryMsgFriend = array('{*actor*}' => $actor, '{*target*}' => $target, '{*nickname*}' => htmlspecialchars($content));
                foreach ($aryIdsSend as $fid) {
                    $bllFeed->newFeedMessage(1, 13, 'feed_tpl_friend', $uid, $fid, $aryMsgFriend, 0);
                }
            }

            return $result;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            debug_log('Bll/Slave/Slave/setNicknameOrBalloon:' . $e->getMessage());
            return 0;
        }
    }

    /**
     * sell slave
     *
     * @param string $uid
     * @param string $sellUid
     * @return boolean
     */
    public function sellSlave($uid, $sellUid)
    {
        try {

            $this->_wdb->beginTransaction();

            $this->_subSellSlave($uid, $sellUid);

            $this->_wdb->commit();

            //send feed
            require_once 'Bll/User.php';
            require_once 'Bll/Slave/FeedMessage.php';
            $bllFeed = new Bll_Slave_FeedMessage();
            $userInfo = Bll_User::getPerson($uid);
            $userName = $userInfo->getDisplayName();
            //$userUrl = $userInfo->getProfileUrl();
            //$userPic = $userInfo->getThumbnailUrl();
            $actor = '<a href="/slave/profile?uid=' . $uid . '" >' . $userName . '</a>';

            $tarUid = $sellUid;
            $tarInfo = Bll_User::getPerson($tarUid);
            $tarName = $tarInfo->getDisplayName();
            //$tarUrl = $tarInfo->getProfileUrl();
            //$tarPic = $tarInfo->getThumbnailUrl();
            $target = '<a href="/slave/profile?uid=' . $tarUid . '" >' . $tarName . '</a>';

            $aryMsgMine = array('{*target*}' => $target);
            $bllFeed->newFeedMessage(1, 3, 'feed_tpl_actor', $uid, $uid, $aryMsgMine, 1);
            $aryMsgTar = array('{*actor*}' => $actor);
            $bllFeed->newFeedMessage(1, 3, 'feed_tpl_target', $uid, $tarUid, $aryMsgTar, 1);
            //send feed to friends already installed app
            require_once 'Bll/Slave/Friend.php';
            $aryIds = Bll_Slave_Friend::getFriends($uid);
            $aryIds2 = Bll_Slave_Friend::getFriends($tarUid);
            $aryIdsTmp = array_merge($aryIds, $aryIds2);
            $aryIdsSend = array_unique($aryIdsTmp);
            $aryMsgFriend = array('{*actor*}' => $actor, '{*target*}' => $target);
            foreach ($aryIdsSend as $fid) {
                $bllFeed->newFeedMessage(1, 3, 'feed_tpl_friend', $uid, $fid, $aryMsgFriend, 0);
            }

            return true;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            debug_log('Bll/Slave/Slave/sellSlave:' . $e->getMessage());
            return false;
        }
    }

    /**
     * private sell slave (must in transaction)
     *
     * @param string $uid
     * @param string $sellUid
     * @return boolean
     */
    private function _subSellSlave($uid, $sellUid)
    {
        require_once 'Dal/Slave/Slave.php';
        $dalSlave = Dal_Slave_Slave::getDefaultInstance();
        require_once 'Dal/Slave/Revolution.php';
        $dalRev = Dal_Slave_Revolution::getDefaultInstance();
        require_once 'Dal/Slave/CashPriceChangeLog.php';
        $dalChangeLog = Dal_Slave_CashPriceChangeLog::getDefaultInstance();

        $rowMaster = $dalSlave->getSlaveByIdLock($uid);
        $rowSlave = $dalSlave->getSlaveByIdLock($sellUid);

        if ($uid != $rowSlave['master_id']) {
            $this->_wdb->rollBack();
            return false;
        }

        //delete revolution info
        $dalRev->deleteRevolutionBegun($sellUid, $uid);

        //update slave info
        $arySlave = array();
        $arySlave['master_id'] = '';
        //$arySlave['balloon'] = '';
        //$arySlave['nickname'] = '';
        $arySlave['price'] = ceil($rowSlave['price'] * 0.8);
        $dalSlave->updateSlave($arySlave, $sellUid);

        //update master info
        $aryMaster = array();
        $aryMaster['slave_count'] = $rowMaster['slave_count'] - 1;
        $aryMaster['cash'] = $rowMaster['cash'] + ceil($rowSlave['price'] * 0.8);
        $aryMaster['total_slave_price'] = $dalSlave->getSlavePriceByMaster($uid);
        $dalSlave->updateSlave($aryMaster, $uid);

        //insert change log
        $aryLog = array();
        //master
        $aryLog['actor_uid'] = $uid;
        $aryLog['target_uid'] = $sellUid;
        $aryLog['c_floating'] = 0;
        $aryLog['p_floating'] = ceil($rowSlave['price'] * 0.8);
        $aryLog['type'] = 2;
        $aryLog['create_time'] = time();
        $dalChangeLog->insertCashPriceChangeLog($aryLog);
        //slave
        $aryLog['actor_uid'] = $sellUid;
        $aryLog['target_uid'] = $uid;
        $aryLog['c_floating'] = 0;
        $aryLog['p_floating'] = '-' . ceil($rowSlave['price'] * 0.2);
        $aryLog['type'] = 2;
        $dalChangeLog->insertCashPriceChangeLog($aryLog);
    }

    /**
     * buy slave
     *
     * @param string $uid
     * @param string $tarUid
     * @param string $sellUid
     * @return boolean
     */
    public function buySlave($uid, $tarUid, $sellUid = 0)
    {
        require_once 'Dal/Slave/Slave.php';
        $dalSlave = Dal_Slave_Slave::getDefaultInstance();
        require_once 'Dal/Slave/Revolution.php';
        $dalRev = Dal_Slave_Revolution::getDefaultInstance();
        require_once 'Dal/Slave/CashPriceChangeLog.php';
        $dalChangeLog = Dal_Slave_CashPriceChangeLog::getDefaultInstance();

        try {

            $this->_wdb->beginTransaction();

            $rowMaster = $dalSlave->getSlaveByIdLock($uid);
            $rowSlave = $dalSlave->getSlaveByIdLock($tarUid);
            $slave_Masterid = $rowSlave['master_id'];

            if ($uid == $tarUid) {
            	$this->_wdb->rollBack();
                return false;
            }

            if (empty($rowSlave)) {
            	$rowSlave['uid'] = $tarUid;
            	$rowSlave['price'] = 4980;
            }

        	//already slave
            if ($uid === $slave_Masterid) {
                $this->_wdb->rollBack();
                return false;
            }

            //whether master_id
            if ($tarUid === $rowMaster['master_id']) {
                $this->_wdb->rollBack();
                return false;
            }

        	//already had 4 slaves
            if (4 <= $rowMaster['slave_count']) {
                if (empty($sellUid)) {
                    $this->_wdb->rollBack();
                    return false;
                }
                //must sell one
                //not master's slave
                if (!$dalSlave->isSlaveByMasterId($sellUid, $uid)) {
                    $this->_wdb->rollBack();
                    return false;
                }
                $this->_subSellSlave($uid, $sellUid);
            }

            //not enough cash
            if ($rowMaster['cash'] - $rowSlave['price'] < 0) {
                $this->_wdb->rollBack();
                return false;
            }

            //check the target slave is in revolution
            if (!empty($rowSlave['master_id'])) {
                //delete target slave's in revolution info
                $dalRev->deleteRevolutionBegun($tarUid, $rowSlave['master_id']);
            }

            //buy slave normal
            //update slave info
            $arySlave = array();
            $arySlave['master_id'] = $uid;
            //$arySlave['balloon'] = '';
            //$arySlave['nickname'] = '';
            $arySlave['price'] = ceil($rowSlave['price'] * 1.2);
            $dalSlave->updateSlave($arySlave, $tarUid);

            //update master info
            $aryMaster = array();
            $aryMaster['slave_count'] = $rowMaster['slave_count'] == 4 ? 4 : $rowMaster['slave_count'] + 1;
            $aryMaster['cash'] = $rowMaster['cash'] - $rowSlave['price'];
            $aryMaster['total_slave_price'] = $dalSlave->getSlavePriceByMaster($uid);
            $dalSlave->updateSlave($aryMaster, $uid);

            //if the slave master
            if (!empty($slave_Masterid)) {
                $row_SlaveMaster = $dalSlave->getSlaveByIdLock($slave_Masterid);
                $arySlaveMaster = array();
                $arySlaveMaster['slave_count'] = $row_SlaveMaster['slave_count'] - 1;
                $arySlaveMaster['cash'] = ceil($dalSlave->getCashById($slave_Masterid) + $rowSlave['price']);
                $arySlaveMaster['total_slave_price'] = $dalSlave->getSlavePriceByMaster($slave_Masterid);
                $dalSlave->updateSlave($arySlaveMaster, $slave_Masterid);

                $aryMasterLog = array();
                $aryMasterLog['actor_uid'] = $uid;
                $aryMasterLog['target_uid'] = $slave_Masterid;
                $aryMasterLog['c_floating'] = 0;
                $aryMasterLog['p_floating'] = 0;
                $aryMasterLog['type'] = 2;
                $aryMasterLog['create_time'] = time();
                $dalChangeLog->insertCashPriceChangeLog($aryMasterLog);
            }

            //insert change log
            $aryLog = array();
            //master
            $aryLog['actor_uid'] = $uid;
            $aryLog['target_uid'] = $tarUid;
            $aryLog['c_floating'] = '-' . $rowSlave['price'];
            $aryLog['p_floating'] = 0;
            $aryLog['type'] = 2;
            $aryLog['create_time'] = time();
            $dalChangeLog->insertCashPriceChangeLog($aryLog);
            //slave
            $aryLog['actor_uid'] = $tarUid;
            $aryLog['target_uid'] = $uid;
            $aryLog['c_floating'] = 0;
            $aryLog['p_floating'] = ceil($rowSlave['price'] * 0.2);
            $aryLog['type'] = 2;
            $dalChangeLog->insertCashPriceChangeLog($aryLog);

            $this->_wdb->commit();

            //send feed
            require_once 'Bll/User.php';
            require_once 'Bll/Slave/FeedMessage.php';
            $bllFeed = new Bll_Slave_FeedMessage();
            $userInfo = Bll_User::getPerson($uid);
            $userName = $userInfo->getDisplayName();

            $tarInfo = Bll_User::getPerson($tarUid);
            $tarName = $tarInfo->getDisplayName();

            $actor = '<a href="/slave/profile?uid=' . $uid . '" >' . $userName . '</a>';
            $target = '<a href="/slave/profile?uid=' . $tarUid . '" >' . $tarName . '</a>';

            $aryMsgMine = array('{*target*}' => $target);
            $bllFeed->newFeedMessage(1, 2, 'feed_tpl_actor', $uid, $uid, $aryMsgMine, 1);

            $aryMsgTar = array('{*actor*}' => $actor, '{*target*}' => $target);
            $bllFeed->newFeedMessage(1, 2, 'feed_tpl_target', $uid, $tarUid, $aryMsgTar, 1);

            if (!empty($slave_Masterid)) {
                $aryMsgRelative = array('{*actor*}' => $actor, '{*target*}' => $target, '{*money*}' => $rowSlave['price']);
                $bllFeed->newFeedMessage(1, 2, 'feed_tpl_relative', $uid, $slave_Masterid, $aryMsgRelative, 1);
            }

            //send feed to friends already installed app
            require_once 'Bll/Slave/Friend.php';
            $aryIds = Bll_Slave_Friend::getFriends($uid);
            $aryIds2 = Bll_Slave_Friend::getFriends($tarUid);
            $aryIdsTmp = array_merge($aryIds, $aryIds2);
            $aryIdsSend = array_unique($aryIdsTmp);
            $aryMsgFriend = array('{*actor*}' => $actor, '{*target*}' => $target);
            foreach ($aryIdsSend as $fid) {
                $bllFeed->newFeedMessage(1, 2, 'feed_tpl_friend', $uid, $fid, $aryMsgFriend, 0);
            }

            return true;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            debug_log('Bll/Slave/Slave/buySlave:' . $e->getMessage());
            return false;
        }

    }

/******************************************************/

}
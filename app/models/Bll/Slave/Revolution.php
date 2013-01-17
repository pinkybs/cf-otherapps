<?php

require_once 'Bll/Abstract.php';

/**
 * Mixi App Slave-revolution logic Operation
 *
 * @package    Bll/Slave
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/07/08    zhangxin
 */
final class Bll_Slave_Revolution extends Bll_Abstract
{

    /**
     * slave revolution
     *
     * @param string $uid
     * @param string $sellUid
     * @return boolean
     */
    public function beginRevolute($uid, $sellUid=0)
    {
        try {
            require_once 'Dal/Slave/Slave.php';
            $dalSlave = Dal_Slave_Slave::getDefaultInstance();
            require_once 'Dal/Slave/Revolution.php';
            $dalRev = Dal_Slave_Revolution::getDefaultInstance();

            $this->_wdb->beginTransaction();

            $rowSlave = $dalSlave->getSlaveByIdLock($uid);
            if (empty($rowSlave)) {
                $this->_wdb->rollBack();
                return false;
            }
            //no master to revolute
            else if (empty($rowSlave['master_id'])) {
                $this->_wdb->rollBack();
                return false;
            }
            $rowInRev = $dalRev->getInRevolutionByIdLock($uid, $rowSlave['master_id']);
            //already in revelution
            if (!empty($rowInRev)) {
                $this->_wdb->rollBack();
                return false;
            }

            $aryInfo = array();
            $aryInfo['slave_uid'] = $uid;
            $aryInfo['master_uid'] = $rowSlave['master_id'];
            if (!empty($sellUid)) {
                $aryInfo['sell_uid'] = $sellUid;
            }
            $aryInfo['status'] = 0;
            $aryInfo['create_time'] = time();
            $dalRev->insertRevolution($aryInfo);

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

			$tarUid = $rowSlave['master_id'];
			$tarInfo = Bll_User::getPerson($tarUid);
            $tarName = $tarInfo->getDisplayName();
            //$tarUrl = $tarInfo->getProfileUrl();
            //$tarPic = $tarInfo->getThumbnailUrl();
            $target = '<a href="/slave/profile?uid=' . $tarUid . '" >' . $tarName . '</a>';

            $aryMsgMine = array('{*target*}' => $target);
			$bllFeed->newFeedMessage(1, 4, 'feed_tpl_actor', $uid, $uid, $aryMsgMine, 1);

			//send feed to friends already installed app
			require_once 'Bll/Slave/Friend.php';
			$aryIds = Bll_Slave_Friend::getFriends($uid);
			$aryIds2 = Bll_Slave_Friend::getFriends($tarUid);
			$aryIdsTmp = array_merge($aryIds, $aryIds2);
			$aryIdsSend = array_unique($aryIdsTmp);
			$aryMsgFriend = array('{*actor*}' => $actor, '{*target*}' => $target);
			foreach ($aryIdsSend as $fid) {
				$bllFeed->newFeedMessage(1, 4, 'feed_tpl_friend', $uid, $fid, $aryMsgFriend, 0);
			}

            return true;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            debug_log('Bll/Slave/Revolution/beginRevolute:' . $e->getMessage());
            return false;
        }
    }

	/**
     * stop revolution
     *
     * @param string $uid
     * @param string $slaveUid
     * @return integer
     */
    public function stopRevolute($uid, $slaveUid)
    {
        try {
            require_once 'Dal/Slave/Slave.php';
            $dalSlave = Dal_Slave_Slave::getDefaultInstance();
            require_once 'Dal/Slave/Revolution.php';
            $dalRev = Dal_Slave_Revolution::getDefaultInstance();
            require_once 'Dal/Slave/CashPriceChangeLog.php';
            $dalChangeLog = Dal_Slave_CashPriceChangeLog::getDefaultInstance();

            $this->_wdb->beginTransaction();

            $rowMaster = $dalSlave->getSlaveByIdLock($uid);
            $rowSlave = $dalSlave->getSlaveByIdLock($slaveUid);
            if (empty($rowSlave) || empty($rowMaster)) {
                $this->_wdb->rollBack();
                return false;
            }
            //is not my slave
            else if ($rowSlave['master_id'] != $uid) {
                $this->_wdb->rollBack();
                return false;
            }

            $rowInRev = $dalRev->getInRevolutionByIdLock($slaveUid, $uid);
            //not in revelution
            if (empty($rowInRev)) {
                $this->_wdb->rollBack();
                return false;
            }

            //12 hour time is over
            if (time() - $rowInRev['create_time'] >= 60*60*12) {
                $this->_wdb->rollBack();
                return false;
            }

            //stop revolution
            $aryInfo = array();
            $aryInfo['status'] = 2;
            $aryInfo['end_time'] = time();
            $dalRev->updateRevolution($aryInfo, $slaveUid, $uid);

            //cash change
            //slave
            $arySlave = array();
            $arySlave['cash'] = $rowSlave['cash'] - ceil($rowSlave['cash'] * 0.2);
            $dalSlave->updateSlave($arySlave, $slaveUid);
            //master
            $aryMaster = array();
            $aryMaster['cash'] = $rowMaster['cash'] + ceil($rowSlave['cash'] * 0.2);
            $dalSlave->updateSlave($aryMaster, $uid);

            //insert change log
            $aryLog = array();
            //master
            $aryLog['actor_uid'] = $uid;
            $aryLog['target_uid'] = $slaveUid;
            $aryLog['c_floating'] = ceil($rowSlave['cash'] * 0.2);
            $aryLog['p_floating'] = 0;
            $aryLog['type'] = 4;
            $aryLog['create_time'] = time();
            $dalChangeLog->insertCashPriceChangeLog($aryLog);
            //slave
            $aryLog['actor_uid'] = $slaveUid;
            $aryLog['target_uid'] = $uid;
            $aryLog['c_floating'] = '-' . ceil($rowSlave['cash'] * 0.2);
            $aryLog['p_floating'] = 0;
            $aryLog['type'] = 4;
            $aryLog['create_time'] = time();
            $dalChangeLog->insertCashPriceChangeLog($aryLog);

            $this->_wdb->commit();

            //send feed
            require_once 'Bll/User.php';
			require_once 'Bll/Slave/FeedMessage.php';
			$bllFeed = new Bll_Slave_FeedMessage();
			$userInfo = Bll_User::getPerson($slaveUid);
			$userName = $userInfo->getDisplayName();
			//$userUrl = $userInfo->getProfileUrl();
			//$userPic = $userInfo->getThumbnailUrl();
			$actor = '<a href="/slave/profile?uid=' . $slaveUid . '" >' . $userName . '</a>';

			$tarUid = $uid;
			$tarInfo = Bll_User::getPerson($tarUid);
            $tarName = $tarInfo->getDisplayName();
            //$tarUrl = $tarInfo->getProfileUrl();
            //$tarPic = $tarInfo->getThumbnailUrl();
            $target = '<a href="/slave/profile?uid=' . $tarUid . '" >' . $tarName . '</a>';

            $aryMsgMine = array('{*target*}' => $target, '{*money*}' => '￥' . number_format(ceil($rowSlave['cash'] * 0.2)));
			$bllFeed->newFeedMessage(1, 6, 'feed_tpl_actor', $slaveUid, $slaveUid, $aryMsgMine, 1);
			$aryMsgTar = array('{*actor*}' => $actor, '{*money*}' => '￥' . number_format(ceil($rowSlave['cash'] * 0.2)));
			$bllFeed->newFeedMessage(1, 6, 'feed_tpl_target', $slaveUid, $tarUid, $aryMsgTar, 1);

			//send feed to friends already installed app
			require_once 'Bll/Slave/Friend.php';
			$aryIds = Bll_Slave_Friend::getFriends($slaveUid);
			$aryIds2 = Bll_Slave_Friend::getFriends($tarUid);
			$aryIdsTmp = array_merge($aryIds, $aryIds2);
			$aryIdsSend = array_unique($aryIdsTmp);
			$aryMsgFriend = array('{*actor*}' => $actor, '{*target*}' => $target, '{*money*}' => '￥' . number_format(ceil($rowSlave['cash'] * 0.2)));
			foreach ($aryIdsSend as $fid) {
				$bllFeed->newFeedMessage(1, 6, 'feed_tpl_friend', $slaveUid, $fid, $aryMsgFriend, 0);
			}

            return ceil($rowSlave['cash'] * 0.2);
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            debug_log('Bll/Slave/Revolution/stopRevolute:' . $e->getMessage());
            return false;
        }
    }

	/**
     * revolution successful
     *
     * @param string $uid
     * @param string $slaveUid
     * @return boolean
     */
    public function doneRevolute($uid, $slaveUid)
    {
        try {
            require_once 'Dal/Slave/Slave.php';
            $dalSlave = Dal_Slave_Slave::getDefaultInstance();
            require_once 'Dal/Slave/Revolution.php';
            $dalRev = Dal_Slave_Revolution::getDefaultInstance();
            require_once 'Dal/Slave/CashPriceChangeLog.php';
            $dalChangeLog = Dal_Slave_CashPriceChangeLog::getDefaultInstance();

            $this->_wdb->beginTransaction();

            $rowMaster = $dalSlave->getSlaveByIdLock($uid);
            $rowSlave = $dalSlave->getSlaveByIdLock($slaveUid);
            if (empty($rowSlave) || empty($rowMaster)) {
                $this->_wdb->rollBack();
                return false;
            }
            //is not my slave
            else if ($rowSlave['master_id'] != $uid) {
                $this->_wdb->rollBack();
                return false;
            }

            $rowInRev = $dalRev->getInRevolutionByIdLock($slaveUid, $uid);
            //not in revelution
            if (empty($rowInRev)) {
                $this->_wdb->rollBack();
                return false;
            }

            //12 hour time has not arrive
            if ((time() - $rowInRev['create_time']) < 60*60*12) {
                $this->_wdb->rollBack();
                return false;
            }

            //master's master
            $rowMastersMaster = null;
            if (!empty($rowMaster['master_id'])) {
                $rowMastersMaster = $dalSlave->getSlaveByIdLock($rowMaster['master_id']);
            }

            //slave count is already four must sell one
            if (4 == $rowSlave['slave_count']) {
                if (!empty($rowInRev['sell_uid'])) {
                    $tmpSellId = $rowInRev['sell_uid'];
                }
                else {
                    $rowCheapS = $dalSlave->getCheapestSlaveByMaster($uid);
                    $tmpSellId = $rowCheapS['uid'];
                }
                $this->_subSellSlave($slaveUid, $tmpSellId);
                $rowSlave['slave_count'] = ($rowSlave['slave_count'] - 1) < 0 ? 0 : ($rowSlave['slave_count'] - 1);
            }

            //roles change
            //original master' master
            if (!empty($rowMastersMaster)) {
                $this->_subSellSlave($rowMastersMaster['uid'], $uid);
            }

            //master
            $aryMaster = array();
            $aryMaster['master_id'] = $slaveUid;
            $aryMaster['slave_count'] = ($rowMaster['slave_count'] - 1) < 0 ? 0 : ($rowMaster['slave_count'] - 1);
            $aryMaster['price'] = $rowMaster['price'] - ceil($rowMaster['price'] * 0.2);
            $aryMaster['total_slave_price'] = $dalSlave->getSlavePriceByMaster($uid);
            $dalSlave->updateSlave($aryMaster, $uid);

            //slave
            $arySlave = array();
            $arySlave['master_id'] = '';
            $arySlave['slave_count'] = ($rowSlave['slave_count'] + 1) > 4 ? 4 : ($rowSlave['slave_count'] + 1);
            $arySlave['price'] = $rowSlave['price'] + ceil($rowSlave['price'] * 0.2);
            $arySlave['total_slave_price'] = $dalSlave->getSlavePriceByMaster($slaveUid);
            $dalSlave->updateSlave($arySlave, $slaveUid);


            //revolution success done
            $aryInfo = array();
            $aryInfo['status'] = 1;
            $revDoneTime = $rowInRev['create_time'] + 60*60*12;
            $aryInfo['end_time'] = $revDoneTime;
            $dalRev->updateRevolution($aryInfo, $slaveUid, $uid);
            //update other revolute later than first person
            $aryInfo['status'] = 2;
            $dalRev->updateRevolutionAfterFirst($aryInfo, $uid, $rowInRev['create_time']);

            //insert change log
            $aryLog = array();
            //slave
            $aryLog['actor_uid'] = $slaveUid;
            $aryLog['target_uid'] = $uid;
            $aryLog['c_floating'] = 0;
            $aryLog['p_floating'] = ceil($rowSlave['price'] * 0.2);
            $aryLog['type'] = 4;
            $aryLog['create_time'] = time();
            $dalChangeLog->insertCashPriceChangeLog($aryLog);
            //master
            $aryLog['actor_uid'] = $uid;
            $aryLog['target_uid'] = $slaveUid;
            $aryLog['c_floating'] = 0;
            $aryLog['p_floating'] = '-' . ceil($rowMaster['price'] * 0.2);;
            $aryLog['type'] = 4;
            $aryLog['create_time'] = time();
            $dalChangeLog->insertCashPriceChangeLog($aryLog);

            $this->_wdb->commit();

            info_log('', 'slave_revolution_done');
            info_log(date('Y-m-d H:i:s') . " Slave:$slaveUid revolute ->Master:$uid Done!! " . date('Y-m-d H:i:s', $revDoneTime), 'slave_revolution_done');
            info_log('', 'slave_revolution_done');

            //send feed
            require_once 'Bll/User.php';
			require_once 'Bll/Slave/FeedMessage.php';
			$bllFeed = new Bll_Slave_FeedMessage();
			$userInfo = Bll_User::getPerson($slaveUid);
			$userName = $userInfo->getDisplayName();
			//$userUrl = $userInfo->getProfileUrl();
			//$userPic = $userInfo->getThumbnailUrl();
			$actor = '<a href="/slave/profile?uid=' . $slaveUid . '" >' . $userName . '</a>';

			$tarUid = $uid;
			$tarInfo = Bll_User::getPerson($tarUid);
            $tarName = $tarInfo->getDisplayName();
            //$tarUrl = $tarInfo->getProfileUrl();
            //$tarPic = $tarInfo->getThumbnailUrl();
            $target = '<a href="/slave/profile?uid=' . $tarUid . '" >' . $tarName . '</a>';

            $aryMsgMine = array('{*target*}' => $target);
			$bllFeed->newFeedMessage(1, 5, 'feed_tpl_actor', $slaveUid, $slaveUid, $aryMsgMine, 1, null, $revDoneTime);
			$aryMsgTar = array('{*actor*}' => $actor);
			$bllFeed->newFeedMessage(1, 5, 'feed_tpl_target', $slaveUid, $tarUid, $aryMsgTar, 1, null, $revDoneTime);

			//send feed to friends already installed app
			require_once 'Bll/Slave/Friend.php';
			$aryIds = Bll_Slave_Friend::getFriends($slaveUid);
			$aryIds2 = Bll_Slave_Friend::getFriends($tarUid);
			$aryIdsTmp = array_merge($aryIds, $aryIds2);
			//original master
            if (!empty($rowMastersMaster)) {
			    $aryIdsTmp[] = $rowMastersMaster['uid'];
            }
			$aryIdsSend = array_unique($aryIdsTmp);
			$aryMsgFriend = array('{*actor*}' => $actor, '{*target*}' => $target);
			foreach ($aryIdsSend as $fid) {
				$bllFeed->newFeedMessage(1, 5, 'feed_tpl_friend', $slaveUid, $fid, $aryMsgFriend, 0, null, $revDoneTime);
			}

            return true;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            debug_log('Bll/Slave/Revolution/doneRevolute:' . $e->getMessage());
            return false;
        }
    }

	/**
     * atuo check revolution status and done revolution
     *
     * @param string $uid
     * @return boolean
     */
    public function autoCheckRevolution($uid)
    {
        require_once 'Dal/Slave/Slave.php';
        $dalSlave = Dal_Slave_Slave::getDefaultInstance();
        require_once 'Dal/Slave/Revolution.php';
        $dalRev = Dal_Slave_Revolution::getDefaultInstance();

        $rowSlave = $dalSlave->getSlaveById($uid);
        if (empty($rowSlave)) {
            return false;
        }

        //check is be revoluted
        $rowRevAsMaster = $dalRev->getInRevolutionByMasterId($uid);

        //check is my master be revoluted
        if (!empty($rowSlave['master_id'])) {
            $rowRevAsSlave = $dalRev->getInRevolutionByMasterId($rowSlave['master_id']);
        }

        //no revolution in process
        if (empty($rowRevAsMaster) && empty($rowRevAsSlave)) {
            return false;
        }

        $aryDoneRev = array();
        //deal revolution order by createtime
        if (!empty($rowRevAsMaster) && !empty($rowRevAsSlave)) {
            if ($rowRevAsMaster['create_time'] < $rowRevAsSlave['create_time']) {
                $result1 = $this->doneRevolute($rowRevAsMaster['master_uid'], $rowRevAsMaster['slave_uid']);
                if ($result1) {
                    $aryDoneRev[] = $rowRevAsMaster['master_uid'] . '|' . $rowRevAsMaster['slave_uid'];
                }
                $result2 = $this->doneRevolute($rowRevAsSlave['master_uid'], $rowRevAsSlave['slave_uid']);
                if ($result2) {
                    $aryDoneRev[] = $rowRevAsSlave['master_uid'] . '|' . $rowRevAsSlave['slave_uid'];
                }
            }
            else {
                $result1 = $this->doneRevolute($rowRevAsSlave['master_uid'], $rowRevAsSlave['slave_uid']);
                if ($result1) {
                    $aryDoneRev[] = $rowRevAsSlave['master_uid'] . '|' . $rowRevAsSlave['slave_uid'];
                }
                $result2 = $this->doneRevolute($rowRevAsMaster['master_uid'], $rowRevAsMaster['slave_uid']);
                if ($result2) {
                    $aryDoneRev[] = $rowRevAsMaster['master_uid'] . '|' . $rowRevAsMaster['slave_uid'];
                }
            }
            return $aryDoneRev;
        }

        if (!empty($rowRevAsMaster)) {
            $result = $this->doneRevolute($rowRevAsMaster['master_uid'], $rowRevAsMaster['slave_uid']);
            if ($result) {
                $aryDoneRev[] = $rowRevAsMaster['master_uid'] . '|' . $rowRevAsMaster['slave_uid'];
            }
            return $aryDoneRev;
        }
        if (!empty($rowRevAsSlave)) {
            $result = $this->doneRevolute($rowRevAsSlave['master_uid'], $rowRevAsSlave['slave_uid']);
            if ($result) {
                $aryDoneRev[] = $rowRevAsSlave['master_uid'] . '|' . $rowRevAsSlave['slave_uid'];
            }
            return $aryDoneRev;
        }
    }

	/**
     * sell slave (must in transaction)
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

        $rowMaster= $dalSlave->getSlaveByIdLock($uid);
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
        $aryMaster['slave_count'] = ($rowMaster['slave_count'] - 1) < 0 ? 0 : ($rowMaster['slave_count'] - 1);
        $aryMaster['cash'] = $rowMaster['cash'] + ceil($rowSlave['price'] * 0.8);
        $aryMaster['total_slave_price'] = $dalSlave->getSlavePriceByMaster($uid);
        $dalSlave->updateSlave($aryMaster, $uid);

        //insert change log
        $aryLog = array();
        //master
        $aryLog['actor_uid'] = $uid;
        $aryLog['target_uid'] = $sellUid;
        $aryLog['c_floating'] = ceil($rowSlave['price'] * 0.8);
        $aryLog['p_floating'] = 0;
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


}
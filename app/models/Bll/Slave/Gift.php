<?php
require_once 'Bll/Abstract.php';

/**
 * Mixi App Gift logic Operation
 *
 * @package    Bll/Slave
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/06/29    xiali
 */
final class Bll_Slave_Gift extends Bll_Abstract
{

    /**
     * add gift fav
     * @param :$info
     * @return:boolean
     */
    public function addGiftFav($info)
    {
        require_once 'Dal/Slave/Gift.php';
        $dalGift = Dal_Slave_Gift::getDefaultInstance();

        $isSubmit = false;
        try {
            $this->_wdb->beginTransaction();
            $uid = $info['uid'];
            $gid = $info['gid'];
            $giftCount = $dalGift->getFavCountById($uid, $gid);

            if ($giftCount > 1) {
            	$this->_wdb->rollBack();
            	return false;
            }

            $dalGift->insertGiftFav($info);
            $this->_wdb->commit();
            $isSubmit = true;
        }
        catch (Exception $e) {
            $isSubmit = false;
            $this->_wdb->rollBack();
            err_log($e->getMessage());
        }
        return $isSubmit;
    }

    public function addNbGift($info, $gid)
    {
        require_once 'Dal/Slave/Gift.php';
        $dalGift = Dal_Slave_Gift::getDefaultInstance();
        try {
            $this->_wdb->beginTransaction();
            $rowNbGift = $dalGift->getNbGift($gid);
            if (empty($rowNbGift)) {
                $dalGift->insertNbGift($info);
            }
            else {
                $dalGift->updateNbGift($info, $gid);
            }

            $this->_wdb->commit();
            $isSubmit = true;
        }
        catch (Exception $e) {
            $isSubmit = false;
            $this->_wdb->rollBack();
            err_log($e->getMessage());
        }
        return $isSubmit;
    }

    /**
     * buy gift
     * @param : $info array
     * @param : $price integer
     * @return: boolean
     */
    public function addGift($info, $price)
    {
        require_once 'Dal/Slave/Slave.php';
        $dalSlave = Dal_Slave_Slave::getDefaultInstance();
        require_once 'Dal/Slave/Gift.php';
        $dalGift = Dal_Slave_Gift::getDefaultInstance();
        require_once 'Dal/Slave/CashPriceChangeLog.php';
        $dalChangeLog = Dal_Slave_CashPriceChangeLog::getDefaultInstance();

        $isSubmit = false;
        try {

            $this->_wdb->beginTransaction();
            $uid = $info['uid'];
            $rowSlave = $dalSlave->getSlaveByIdLock($uid);

            $gid = $info['gid'];
            $rowNbGift = $dalGift->getNbGift($gid);
            if (empty($rowNbGift)) {
                $this->_wdb->rollBack();
                return false;
            }

            if ($rowSlave['cash'] < $price) {
                $this->_wdb->rollBack();
                return false;
            }

            $arySlave = array();
            $slave_Total_Gift_Price = $dalGift->getTotalGiftPriceById($uid);
            $slave_cash = $dalSlave->getCashById($uid);
            $arySlave['total_gift_price'] = $slave_Total_Gift_Price + $price;
            $arySlave['cash'] = $slave_cash - $price;

            $dalSlave->updateSlave($arySlave, $uid);
            $dalGift->insertGift($info);

            //insert change log
            $aryLog = array();
            //user
            $aryLog['actor_uid'] = $uid;
            $aryLog['target_uid'] = 0;
            $aryLog['c_floating'] = '-' . $price;
            $aryLog['p_floating'] = 0;
            $aryLog['type'] = 5;
            $aryLog['create_time'] = time();
            $dalChangeLog->insertCashPriceChangeLog($aryLog);

            $this->_wdb->commit();

            //send feed
            require_once 'Bll/User.php';
            require_once 'Bll/Slave/FeedMessage.php';
            $bllFeed = new Bll_Slave_FeedMessage();

            $userInfo = Bll_User::getPerson($uid);
            $userName = $userInfo->getDisplayName();

            $giftname = $rowNbGift['name'];
            if (strlen($giftname) > 50) {
                $giftname = '「' . htmlspecialchars(mb_substr($giftname, 0, 50, 'UTF-8') . '...') . '」';
            }

            $aryMsgTar = array('{*giftname*}' => $giftname);
            $bllFeed->newFeedMessage(1, 9, 'feed_tpl_actor', $uid, $uid, $aryMsgTar, 1);

            require_once 'Bll/Slave/Friend.php';
            $aryIds = Bll_Slave_Friend::getFriends($uid);

            $actor = '<a href="/slave/profile?uid=' . $uid . '" >' . $userName . '</a>';
            $aryMsgFriend = array('{*actor*}' => $actor, '{*giftname*}' => $giftname);
            foreach ($aryIds as $fid) {
                $bllFeed->newFeedMessage(1, 9, 'feed_tpl_friend', $uid, $fid, $aryMsgFriend, 0);
            }

            $isSubmit = true;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            err_log($e->getMessage());
        }
        return $isSubmit;
    }

    /**
     *sell gift
     * @param : string $uid
     * @param : integer $id
     * @return: boolean
     */
    public function sellGift($id, $uid)
    {
        require_once 'Dal/Slave/Gift.php';
        $dalGift = Dal_Slave_Gift::getDefaultInstance();
        require_once 'Dal/Slave/Slave.php';
        $dalSlave = Dal_Slave_Slave::getDefaultInstance();
        require_once 'Dal/Slave/CashPriceChangeLog.php';
        $dalChangeLog = Dal_Slave_CashPriceChangeLog::getDefaultInstance();

        $isSubmit = 1;
        try {
            $this->_wdb->beginTransaction();

            $rowUser = $dalSlave->getSlaveByIdLock($uid);
            $rowGift = $dalGift->getGidByid($id);

            //update user info
            $aryUser = array();

            $my_Total_Gift_Price = $dalGift->getTotalGiftPriceById($uid);
            $slave_cash = $dalSlave->getCashById($uid);
            $aryUser['total_gift_price'] = $my_Total_Gift_Price - $rowGift['gift_price'];

            $aryUser['cash'] = $slave_cash + ceil($rowGift['gift_price'] * 0.8);
            $dalSlave->updateSlave($aryUser, $uid);

            //insert change log
            $aryLog = array();
            //user
            $aryLog['actor_uid'] = $uid;
            $aryLog['target_uid'] = 0;
            $aryLog['c_floating'] = '-' . ceil($rowGift['gift_price'] * 0.2);
            $aryLog['p_floating'] = 0;
            $aryLog['type'] = 5;
            $aryLog['create_time'] = time();
            $dalChangeLog->insertCashPriceChangeLog($aryLog);

            $dalGift->deleteGift($id);
            $this->_wdb->commit();

            require_once 'Bll/Slave/FeedMessage.php';
            $bllFeed = new Bll_Slave_FeedMessage();

            require_once 'Bll/User.php';
            $userInfo = Bll_User::getPerson($uid);
            $userName = $userInfo->getDisplayName();

            $giftname = $rowGift['gift_name'];
            if (strlen($giftname) > 50) {
                $giftname = '「' . htmlspecialchars(mb_substr($giftname, 0, 50, 'UTF-8') . '...') . '」';
            }

            $money = ceil($rowGift['gift_price'] * 0.8);

            $aryMsgTar = array('{*giftname*}' => $giftname, '{*money*}' => '￥' . $money);
            $bllFeed->newFeedMessage(1, 10, 'feed_tpl_actor', $uid, $uid, $aryMsgTar, 1);

            require_once 'Bll/Slave/Friend.php';
            $aryIds = Bll_Slave_Friend::getFriends($uid);

            $actor = '<a href="/slave/profile?uid=' . $uid . '" >' . $userName . '</a>';

            $aryMsgFriend = array('{*actor*}' => $actor, '{*giftname*}' => $giftname, '{*money*}' => '￥' . $money);
            foreach ($aryIds as $fid) {
                $bllFeed->newFeedMessage(1, 10, 'feed_tpl_friend', $uid, $fid, $aryMsgFriend, 0);
            }
        }
        catch (Exception $e) {
            $isSubmit = 0;
            $this->_wdb->rollBack();
            err_log($e->getMessage());
        }

        return $isSubmit;
    }

    /**
     * remove gift fav
     * @param string $uid
     * @param string $gid
     */
    public function deleteGiftFav($uid, $gid)
    {
        require_once 'Dal/Slave/Gift.php';
        $dalGift = Dal_Slave_Gift::getDefaultInstance();

        try {

            $this->_wdb->beginTransaction();

            $dalGift->deleteGiftFav($uid, $gid);
            $this->_wdb->commit();
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            err_log($e->getMessage());
        }
    }

    public function presentGift($uid, $friendId, $keyId)
    {
        require_once 'Dal/Slave/Slave.php';
        $dalSlave = Dal_Slave_Slave::getDefaultInstance();
        require_once 'Dal/Slave/Gift.php';
        $dalGift = Dal_Slave_Gift::getDefaultInstance();
        require_once 'Dal/Slave/CashPriceChangeLog.php';
        $dalChangeLog = Dal_Slave_CashPriceChangeLog::getDefaultInstance();
        require_once 'Bll/Slave/RakutenApi.php';

        $isSubmit = 1;
        try {

            $isExists = false;
            $rowFriend = $dalSlave->getSlaveByIdLock($friendId);
            if (empty($rowFriend)) {
                require_once 'Bll/Slave/Slave.php';
                $bllSlave = new Bll_Slave_Slave();
                $isExists = $bllSlave->newSlaveUser($friendId, $isActive = false);
                if ($isExists) {
                    $rowFriend['uid'] = $friendId;
                    $rowFriend['price'] = 4980;
                }
                else {
                    return false;
                }
            }

            $this->_wdb->beginTransaction();

            $rowUser = $dalSlave->getSlaveByIdLock($uid);
            $gift = $dalGift->getGidByid($keyId);

            //update user info
            $aryUser = array();
            $my_Total_Gift_Price = $dalGift->getTotalGiftPriceById($uid);
            $aryUser['total_gift_price'] = $my_Total_Gift_Price - $gift['gift_price'];
            $dalSlave->updateSlave($aryUser, $uid);

            //update friend info
            $aryFriend = array();
            $friend_Total_Gift_Price = $dalGift->getTotalGiftPriceById($friendId);
            $friend_price = $dalSlave->getPriceById($friendId);
            $aryFriend['price'] = $friend_price + ceil($gift['gift_price'] * 0.05);
            $aryFriend['total_gift_price'] = $friend_Total_Gift_Price + ceil($gift['gift_price']);
            $dalSlave->updateSlave($aryFriend, $friendId);

            //update slave gift info
            $aryGift = array();
            $aryGift['uid'] = $friendId;
            $aryGift['from_uid'] = $uid;
            $aryGift['isbuy'] = 0;
            $aryGift['create_time'] = time();
            $dalGift->updateGiftByKey($aryGift, $keyId);

            //insert change log
            $aryLog = array();
            //user
            $aryLog['actor_uid'] = $uid;
            $aryLog['target_uid'] = $friendId;
            $aryLog['c_floating'] = 0;
            $aryLog['p_floating'] = 0;
            $aryLog['type'] = 5;
            $aryLog['create_time'] = time();
            $dalChangeLog->insertCashPriceChangeLog($aryLog);

            //friend
            $aryLog['actor_uid'] = $friendId;
            $aryLog['target_uid'] = $uid;
            $aryLog['c_floating'] = 0;
            $aryLog['p_floating'] = ceil($gift['gift_price'] * 0.05);
            $dalChangeLog->insertCashPriceChangeLog($aryLog);

            $this->_wdb->commit();

            //send feed
            require_once 'Bll/User.php';
            require_once 'Bll/Slave/FeedMessage.php';
            $bllFeed = new Bll_Slave_FeedMessage();
            $userInfo = Bll_User::getPerson($uid);
            $userName = $userInfo->getDisplayName();

            $giftname = $gift['gift_name'];
            if (strlen($giftname) > 50) {
                $giftname = '「' . htmlspecialchars(mb_substr($giftname, 0, 50, 'UTF-8') . '...') . '」';
            }

            $actor = '<a href="/slave/profile?uid=' . $uid . '" >' . $userName . '</a>';

            $tarInfo = Bll_User::getPerson($friendId);
            $tarName = $tarInfo->getDisplayName();

            $target = '<a href="/slave/profile?uid=' . $friendId . '" >' . $tarName . '</a>';

            $aryMsgMine = array('{*target*}' => $target, '{*giftname*}' => $giftname);
            $bllFeed->newFeedMessage(1, 7, 'feed_tpl_actor', $uid, $uid, $aryMsgMine, 1);

            $aryMsgTar = array('{*actor*}' => $actor, '{*giftname*}' => $giftname);
            $bllFeed->newFeedMessage(1, 7, 'feed_tpl_target', $uid, $friendId, $aryMsgTar, 1);

            //send feed to friends already installed app
            require_once 'Bll/Slave/Friend.php';
            $aryIds = Bll_Slave_Friend::getFriends($uid);
            $aryIds2 = Bll_Slave_Friend::getFriends($friendId);
            $aryIdsTmp = array_merge($aryIds, $aryIds2);
            $aryIdsSend = array_unique($aryIdsTmp);

            $aryMsgFriend = array('{*actor*}' => $actor, '{*target*}' => $target, '{*giftname*}' => $giftname);

            foreach ($aryIdsSend as $fid) {
                if ($friendId == $fid) {
                    continue;
                }
                $bllFeed->newFeedMessage(1, 7, 'feed_tpl_friend', $uid, $fid, $aryMsgFriend, 0);
            }

            $isSubmit = 1;
        }
        catch (Exception $e) {
            $isSubmit = 0;
            $this->_wdb->rollBack();
            err_log($e->getMessage());
        }
        return $isSubmit;
    }
}
?>
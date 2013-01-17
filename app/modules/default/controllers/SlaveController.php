<?php

/**
 * slave controller
 * init each index page
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/06/23  zhangxin
 */
class SlaveController extends MyLib_Zend_Controller_Action_Default
{
    /**
     * slave info
     *
     * @var array
     */
    protected $_slaveInfo;
    /**
     * visit gift flag
     *
     * @var string
     */
    protected $_isTodayFirstLogin = '';
    protected $_appId = '4947';//test:4947 // real: 6232
    protected $_appName = 'Slave';
    protected $_superUserId = '22677405';

    /**
     * index Action
     *
     */
    public function indexAction()
    {
        $this->_redirect($this->_baseUrl . '/slave/home');
        return;
    }

    /**
     * home Action
     *
     */
    public function homeAction()
    {
        $uid = $this->_user->getId();
        require_once 'Dal/Slave/Slave.php';
        $dalSlave = Dal_Slave_Slave::getDefaultInstance();
        require_once 'Dal/Slave/Work.php';
        $dalWork = Dal_Slave_Work::getDefaultInstance();
        require_once 'Bll/User.php';
        require_once 'Dal/Slave/Revolution.php';
        $dalRevolute = Dal_Slave_Revolution::getDefaultInstance();
        $rowSlave = $this->_slaveInfo;
        if (empty($rowSlave['master_id'])) {
            $this->view->isFreedom = 1;
        }
        else {
            //master info
            $rowMaster = $dalSlave->getSlaveById($rowSlave['master_id']);
            $rowMaster['fomat_cash'] = number_format($rowMaster['cash']);
            $rowMaster['fomat_price'] = number_format($rowMaster['price']);
            $lstWork = $dalWork->listWorkByUid($rowSlave['master_id']);
            $rowMaster['work_category'] = 'フリーター';
            if (!empty($lstWork) && 0 < count($lstWork) && !empty($lstWork[0]['last_working_time'])) {
                $rowMaster['work_category'] = $lstWork[0]['category'];
            }
            $this->view->masterInfo = $rowMaster;
            //mixi user info
            $msInfo = Bll_User::getPerson($rowSlave['master_id']);
            $this->view->msName = $msInfo->getDisplayName();
            $this->view->msUrl = $msInfo->getProfileUrl();
            $this->view->msPic = $msInfo->getThumbnailUrl();
        }
        //my info
        //my - work
        $lstWork = $dalWork->listWorkByUid($uid);
        $rowSlave['work_category'] = 'フリーター';
        if (!empty($lstWork) && 0 < count($lstWork) && !empty($lstWork[0]['last_working_time'])) {
            $rowSlave['work_category'] = $lstWork[0]['category'];
        }
        //my - rank
        $rowSlave['rank_price'] = $dalSlave->getSlavePriceRank($uid);
        $rowSlave['rank_cash'] = $dalSlave->getSlaveCashRank($uid);
        $this->view->slaveInfo = $rowSlave;
        //is already in revolution
        $rowRev = $dalRevolute->getInRevolutionById($uid, $rowSlave['master_id']);
        if (!empty($rowRev)) {
            $this->view->isAlreadyInRev = 1;
        }
        //mixi user info
        $myInfo = Bll_User::getPerson($uid);
        $this->view->myName = $myInfo->getDisplayName();
        $this->view->myUrl = $myInfo->getProfileUrl();
        $this->view->myPic = $myInfo->getThumbnailUrl();
        //my slaves list
        $lstSlaves = $dalSlave->listSlaveByUid($uid);
        if (!empty($lstSlaves) && count($lstSlaves) > 0) {
            foreach ($lstSlaves as $key => $sData) {
                //is work
                $lstSWork = $dalWork->listWorkByUid($sData['uid']);
                $lstSlaves[$key]['work_category'] = 'フリーター';
                if (!empty($lstSWork) && 0 < count($lstSWork) && !empty($lstSWork[0]['last_working_time'])) {
                    $lstSlaves[$key]['work_category'] = $lstSWork[0]['category'];
                }
                //is in revolution
                $rowRevolute = $dalRevolute->getInRevolutionById($sData['uid'], $uid);
                $lstSlaves[$key]['is_in_revolution'] = 0;
                if (!empty($rowRevolute)) {
                    $lstSlaves[$key]['is_in_revolution'] = 1;
                }
            }
            Bll_User::appendPeople($lstSlaves, 'uid');
        }
        $this->view->lstSlaves = $lstSlaves;

        //my gift
        require_once 'Dal/Slave/Gift.php';
        $dalGift = Dal_Slave_Gift::getDefaultInstance();
        $lstGift = $dalGift->listGiftByUid($uid, 1, 1);
        if (!empty($lstGift) && count($lstGift) > 0) {
            require_once 'Bll/Slave/RakutenApi.php';
            $aryImg = Bll_Slave_RakutenApi::getImgByCode($lstGift[0]['gid']);
            $lstGift[0]['gift_small_pic'] = $aryImg['gift_small_pic'];
            $lstGift[0]['gift_big_pic'] = $aryImg['gift_big_pic'];
            $lstGift[0]['format_price'] = number_format($lstGift[0]['price']);
            $lstGift[0]['format_gid'] = urlencode($lstGift[0]['gid']);
        }
        $this->view->lstGift = $lstGift[0];
        $this->view->cntGift = $this->view->mineGiftCnt;

        //daily gift
        if (!empty($this->_isTodayFirstLogin)) {
            $prm = rand(1, 5);
            require_once 'Bll/Slave/Slave.php';
            $bllSlave = new Bll_Slave_Slave();
            if (1 == $prm) {
                $addmoney = 50000;
            }
            else if (2 == $prm) {
                $addmoney = 40000;
            }
            else if (3 == $prm) {
                $addmoney = 30000;
            }
            else if (4 == $prm) {
                $addmoney = 20000;
            }
            else {
                $addmoney = 10000;
            }
            $result = $bllSlave->dailyVisitGift($uid, $this->_isTodayFirstLogin, $addmoney);
            $this->view->flashPrm = $prm;
            $this->view->isTodayFirstLogin = $this->_isTodayFirstLogin;
        }

        //my feed
        require_once 'Dal/Slave/FeedMessage.php';
        $dalFeed = Dal_Slave_FeedMessage::getDefaultInstance();
        $feedInfo = $dalFeed->listFeedMessage($uid, 1, 1, 30);
        $this->view->cntFeed = $dalFeed->getFeedMessageCount($uid);
        $this->view->lstFeed = $feedInfo;
        $this->render();
    }

    /**
     * profile Action
     *
     */
    public function profileAction()
    {
        $uid = $this->_user->getId();
        $profileUid = $this->_request->getParam('uid');

        if (empty($profileUid)) {
            $this->view->hasPos = 1;
        }

        require_once 'Dal/Slave/Slave.php';
        $dalSlave = Dal_Slave_Slave::getDefaultInstance();
        require_once 'Dal/Slave/Work.php';
        $dalWork = Dal_Slave_Work::getDefaultInstance();
        require_once 'Dal/Slave/Revolution.php';
        $dalRevolute = Dal_Slave_Revolution::getDefaultInstance();
        require_once 'Dal/Slave/Friend.php';
        $dalFriend = Dal_Slave_Friend::getDefaultInstance();
        require_once 'Bll/User.php';
        //mine friend count
        $this->view->mineFriendCnt = $dalFriend->getMixiFriendCount($uid);
        //login user profile my
        if ($uid == $profileUid || empty($profileUid)) {
            $profileUid = $uid;
            $rowSlave = $this->_slaveInfo;
            $this->view->isMyprofile = 1;
            //is already in revolution
            $rowRev = $dalRevolute->getInRevolutionById($uid, $rowSlave['master_id']);
            if (!empty($rowRev)) {
                $this->view->isAlreadyInRev = 1;
            }
        } //view profile slave
        else {
            $rowSlave = $dalSlave->getSlaveById($profileUid);
            if (empty($rowSlave)) {
                require_once 'Bll/Slave/Slave.php';
                $bllSlave = new Bll_Slave_Slave();
                $bllSlave->newSlaveUser($profileUid, false);
                $rowSlave = $dalSlave->getSlaveById($profileUid);
            }
            if (empty($rowSlave)) {
                $this->_redirect($this->_baseUrl . '/slave/home');
                return;
            }
            //is my master
            if ($profileUid == $this->_slaveInfo['master_id']) {
                $this->view->isMineMaster = 1;
            }
            //visit foot
            require_once 'Dal/Slave/VisitFoot.php';
            $dalVisit = Dal_Slave_VisitFoot::getDefaultInstance();
            $dalVisit->insertVisitFoot(array('uid' => $uid, 'visit_uid' => $profileUid, 'create_time' => time()));
            $rowSlave['fomat_cash'] = number_format($rowSlave['cash']);
            $rowSlave['fomat_price'] = number_format($rowSlave['price']);
            $this->view->isMyprofile = 0;
            $this->view->isMineSlave = $dalSlave->isSlaveByMasterId($profileUid, $uid);
            if ($this->view->isMineSlave) {
                //is in revolution
                $rowRevolute1 = $dalRevolute->getInRevolutionById($profileUid, $uid);
                $rowSlave['is_in_revolution'] = 0;
                if (!empty($rowRevolute1)) {
                    $rowSlave['is_in_revolution'] = 1;
                }
            }
        }
        if (empty($rowSlave['master_id'])) {
            $this->view->isFreedom = 1;
        }
        else {
            //master info
            $rowMaster = $dalSlave->getSlaveById($rowSlave['master_id']);
            $rowMaster['fomat_cash'] = number_format($rowMaster['cash']);
            $rowMaster['fomat_price'] = number_format($rowMaster['price']);
            $lstWork = $dalWork->listWorkByUid($rowSlave['master_id']);
            $rowMaster['work_category'] = 'フリーター';
            if (!empty($lstWork) && 0 < count($lstWork) && !empty($lstWork[0]['last_working_time'])) {
                $rowMaster['work_category'] = $lstWork[0]['category'];
            }
            $this->view->masterInfo = $rowMaster;
            //mixi user info
            $msInfo = Bll_User::getPerson($rowSlave['master_id']);
            $this->view->msName = $msInfo->getDisplayName();
            $this->view->msUrl = $msInfo->getProfileUrl();
            $this->view->msPic = $msInfo->getThumbnailUrl();
            //is my slave'slave
            if ($uid == $rowMaster['master_id']) {
                //is in revolution
                $rowRevolute2 = $dalRevolute->getInRevolutionById($profileUid, $rowSlave['master_id']);
                $rowSlave['is_in_revolution2'] = 0;
                if (!empty($rowRevolute2)) {
                    $rowSlave['is_in_revolution2'] = 1;
                }
                $this->view->isMineSlaveSlave = 1;
            }
        }
        //profile slave info
        //profile - work
        $lstWork = $dalWork->listWorkByUid($profileUid);
        $rowSlave['work_category'] = 'フリーター';
        if (!empty($lstWork) && 0 < count($lstWork) && !empty($lstWork[0]['last_working_time'])) {
            $rowSlave['work_category'] = $lstWork[0]['category'];
        }
        //profile - rank
        $rowSlave['rank_price'] = $dalSlave->getSlavePriceRank($profileUid);
        $rowSlave['rank_cash'] = $dalSlave->getSlaveCashRank($profileUid);
        $this->view->slaveInfo = $rowSlave;
        //mixi user info
        $mixiInfo = Bll_User::getPerson($profileUid);
        $this->view->mixiName = $mixiInfo->getDisplayName();
        $this->view->mixiUrl = $mixiInfo->getProfileUrl();
        $this->view->mixiPic = $mixiInfo->getThumbnailUrl();
        //profile slaves list
        $lstSlaves = $dalSlave->listSlaveByUid($profileUid);
        if (!empty($lstSlaves) && count($lstSlaves) > 0) {
            foreach ($lstSlaves as $key => $sData) {
                //is work
                $lstSWork = $dalWork->listWorkByUid($sData['uid']);
                $lstSlaves[$key]['work_category'] = 'フリーター';
                if (!empty($lstSWork) && 0 < count($lstSWork) && !empty($lstSWork[0]['last_working_time'])) {
                    $lstSlaves[$key]['work_category'] = $lstSWork[0]['category'];
                }
                //is in revolution
                $rowRevolute = $dalRevolute->getInRevolutionById($sData['uid'], $profileUid);
                $lstSlaves[$key]['is_in_revolution'] = 0;
                if (!empty($rowRevolute)) {
                    $lstSlaves[$key]['is_in_revolution'] = 1;
                }
            }
            Bll_User::appendPeople($lstSlaves, 'uid');
        }
        $this->view->lstSlaves = $lstSlaves;

        //profile - gift
        require_once 'Dal/Slave/Gift.php';
        $dalGift = Dal_Slave_Gift::getDefaultInstance();
        $lstGift = $dalGift->listGiftByUid($profileUid, 1, 1);
        if (!empty($lstGift) && count($lstGift) > 0) {
            require_once 'Bll/Slave/RakutenApi.php';
            $aryImg = Bll_Slave_RakutenApi::getImgByCode($lstGift[0]['gid']);
            $lstGift[0]['gift_small_pic'] = $aryImg['gift_small_pic'];
            $lstGift[0]['gift_big_pic'] = $aryImg['gift_big_pic'];
            $lstGift[0]['format_price'] = number_format($lstGift[0]['price']);
            $lstGift[0]['format_gid'] = urlencode($lstGift[0]['gid']);
        }
        $this->view->lstGift = $lstGift[0];
        $this->view->cntGift = $dalGift->getGiftByUidCount($profileUid);

        //profile - feed
        require_once 'Dal/Slave/FeedMessage.php';
        $dalFeed = Dal_Slave_FeedMessage::getDefaultInstance();
        $feedInfo = $dalFeed->listFeedMessage($profileUid, 1, 1, 30);
        $this->view->lstFeed = $feedInfo;
        $this->view->cntFeed = $dalFeed->getFeedMessageCount($profileUid);
        $this->view->profileUid = $profileUid;

        //profile - neighber
        require_once 'Dal/Slave/User.php';
        $dalUser = Dal_Slave_User::getDefaultInstance();
        $start = 1;
        $fetchSize = 6;
        $pos = (int)$this->_request->getParam('pos');
        if ($pos > $fetchSize || $pos < $start) {
            $pos = 0;
        }

        //get my position
        $posMine = $dalUser->getBeforeUidCount($profileUid) + 1;
        $count = $dalUser->getAppUidsCount();
        if (empty($pos)) {
            //in center
            if ($posMine > ($fetchSize + 3) && ($posMine + $fetchSize) <= $count) {
                $start = $posMine - 3;
            } //last six
            else if (($posMine + $fetchSize) > $count && $count > $fetchSize) {
                $start = $count - $fetchSize + 1;
            }
        }
        else {
            $start = ($posMine - $pos >= 0) ? ($posMine - $pos + 1) : 1;
            $this->view->hasPos = 1;
        }

        $end = ($start + $fetchSize - 1) > $count ? $count : ($start + $fetchSize - 1);
        $listNeighber = $dalUser->listAppUids($start, $fetchSize);
        Bll_User::appendPeople($listNeighber, 'fid');
        for ($i = count($listNeighber); ($count < $fetchSize) && ($i < $fetchSize); $i++) {
            $listNeighber[$i]['fid'] = '0';
        }
        $this->view->lstNeighber = $listNeighber;
        $this->view->neiCount = $count;
        $this->view->neiStart = $start;
        $this->view->neiEnd = $end;
        //is super user
        if ($this->_superUserId == $uid) {
            $this->view->isForbided = $rowSlave['is_fobid_custom_tease'];
            $this->view->isSuperUser = 1;
        }
        $this->render();
    }

    /**
     * forbid tease Action
     *
     */
    public function forbidteaseAction()
    {
        $uid = $this->_user->getId();
        $tarUid = $this->_request->getParam('uid');
        if (!empty($tarUid) && $uid == $this->_superUserId) {
            require_once 'Dal/Slave/Slave.php';
            $dalSlave = Dal_Slave_Slave::getDefaultInstance();
            $dalSlave->updateSlave(array('is_fobid_custom_tease' => 1), $tarUid);
        }
        $this->_redirect($this->_baseUrl . '/slave/profile?uid=' . $tarUid);
        return;
    }

    /**
     * allow tease Action
     *
     */
    public function allowteaseAction()
    {
        $uid = $this->_user->getId();
        $tarUid = $this->_request->getParam('uid');
        if (!empty($tarUid) && $uid == $this->_superUserId) {
            require_once 'Dal/Slave/Slave.php';
            $dalSlave = Dal_Slave_Slave::getDefaultInstance();
            $dalSlave->updateSlave(array('is_fobid_custom_tease' => 0), $tarUid);
        }
        $this->_redirect($this->_baseUrl . '/slave/profile?uid=' . $tarUid);
        return;
    }

    /**
     * help Action
     *
     */
    public function helpAction()
    {
        //$this->_redirect($this->_baseUrl . '/slave/home');
        //return;
        $this->render();
    }

    /**
     * punish Action
     *
     */
    public function punishAction()
    {
        $this->render();
    }

    /**
     * set nickname Action
     *
     */
    public function setnicknameAction()
    {
        $uid = $this->_user->getId();
        $tarUid = $this->_request->getParam('uid');
        if ($uid == $tarUid || empty($tarUid)) {
            $this->_redirect($this->_baseUrl . '/slave/home');
            return;
        }
        $rowSlave = $this->_slaveInfo;
        require_once 'Dal/Slave/Slave.php';
        $dalSlave = Dal_Slave_Slave::getDefaultInstance();
        $rowTarSlave = $dalSlave->getSlaveById($tarUid);
        //is current user's slave
        if (empty($rowTarSlave) || $rowSlave['uid'] != $rowTarSlave['master_id']) {
            $this->_redirect($this->_baseUrl . '/slave/home');
            return;
        }
        //target slave info
        $tarInfo = Bll_User::getPerson($tarUid);
        $this->view->tarName = $tarInfo->getDisplayName();
        $this->view->tarUrl = $tarInfo->getProfileUrl();
        $this->view->tarPic = $tarInfo->getThumbnailUrl();
        $this->view->tarSlaveInfo = $rowTarSlave;
        $this->render();
    }

    /**
     * set balloon Action
     *
     */
    public function setballoonAction()
    {
        $uid = $this->_user->getId();
        $tarUid = $this->_request->getParam('uid');
        if ($uid == $tarUid || empty($tarUid)) {
            $this->_redirect($this->_baseUrl . '/slave/home');
            return;
        }
        $rowSlave = $this->_slaveInfo;
        require_once 'Dal/Slave/Slave.php';
        $dalSlave = Dal_Slave_Slave::getDefaultInstance();
        $rowTarSlave = $dalSlave->getSlaveById($tarUid);
        //is current user's slave
        if (empty($rowTarSlave) || $rowSlave['uid'] != $rowTarSlave['master_id']) {
            $this->_redirect($this->_baseUrl . '/slave/home');
            return;
        }
        //target slave info
        $tarInfo = Bll_User::getPerson($tarUid);
        $this->view->tarName = $tarInfo->getDisplayName();
        $this->view->tarUrl = $tarInfo->getProfileUrl();
        $this->view->tarPic = $tarInfo->getThumbnailUrl();
        $this->view->tarSlaveInfo = $rowTarSlave;
        $this->render();
    }

    /**
     * release Action
     *
     */
    public function sellslaveAction()
    {
        $uid = $this->_user->getId();
        $sellUid = $this->_request->getParam('uid');
        if ($uid == $sellUid || empty($sellUid)) {
            $this->_redirect($this->_baseUrl . '/slave/home');
            return;
        }
        $rowSlave = $this->_slaveInfo;
        require_once 'Dal/Slave/Slave.php';
        $dalSlave = Dal_Slave_Slave::getDefaultInstance();
        $rowSellSlave = $dalSlave->getSlaveById($sellUid);
        //is current user's slave
        if (empty($rowSellSlave) || $rowSlave['uid'] != $rowSellSlave['master_id']) {
            $this->_redirect($this->_baseUrl . '/slave/profile?uid=' . $sellUid);
            return;
        }
        //sell slave info
        $sellInfo = Bll_User::getPerson($sellUid);
        $this->view->sellName = $sellInfo->getDisplayName();
        $this->view->sellUrl = $sellInfo->getProfileUrl();
        $this->view->sellPic = $sellInfo->getThumbnailUrl();
        $this->view->sellSlaveInfo = $rowSellSlave;
        $this->render();
    }

    /**
     * revolution Action
     *
     */
    public function revoluteAction()
    {
        $uid = $this->_user->getId();
        $rowSlave = $this->_slaveInfo;
        if (empty($rowSlave['master_id'])) {
            $this->_redirect($this->_baseUrl . '/slave/home');
            return;
        }
        require_once 'Dal/Slave/Revolution.php';
        $dalRev = Dal_Slave_Revolution::getDefaultInstance();
        $rowInRev = $dalRev->getInRevolutionById($uid, $rowSlave['master_id']);
        //already in revelution
        if (!empty($rowInRev)) {
            $this->_redirect($this->_baseUrl . '/slave/home');
            return;
        }
        //get my master info
        require_once 'Dal/Slave/Slave.php';
        $dalSlave = Dal_Slave_Slave::getDefaultInstance();
        $rowMaster = $dalSlave->getSlaveById($rowSlave['master_id']);
        if (empty($rowMaster)) {
            $this->_redirect($this->_baseUrl . '/slave/home');
            return;
        }
        //my master info
        $masterInfo = Bll_User::getPerson($rowSlave['master_id']);
        $this->view->msName = $masterInfo->getDisplayName();
        $this->view->msUrl = $masterInfo->getProfileUrl();
        $this->view->msPic = $masterInfo->getThumbnailUrl();
        $this->view->masterInfo = $rowMaster;
        require_once 'Bll/User.php';
        //get my slave list
        $this->view->jsonSlaveList = '';
        if (4 == $rowSlave['slave_count']) {
            $lstSlave = $dalSlave->listSlaveByUid($uid);
            if (4 == count($lstSlave)) {
                Bll_User::appendPeople($lstSlave, 'uid');
                require_once 'Zend/Json.php';
                $this->view->jsonSlaveList = htmlspecialchars(Zend_Json::encode($lstSlave), ENT_QUOTES);
            }
        }
        $this->render();
    }

    /**
     * stop revolution Action
     *
     */
    public function stoprevoluteAction()
    {
        $uid = $this->_user->getId();
        $stopUid = $this->_request->getParam('uid');
        if (empty($stopUid)) {
            $this->_redirect($this->_baseUrl . '/slave/home');
            return;
        }
        require_once 'Dal/Slave/Slave.php';
        $dalSlave = Dal_Slave_Slave::getDefaultInstance();
        //get slave info
        $rowSlave = $dalSlave->getSlaveById($stopUid);
        if (empty($rowSlave)) {
            $this->_redirect($this->_baseUrl . '/slave/profile?uid=' . $stopUid);
            return;
        }
        //is not my slave
        if (!$dalSlave->isSlaveByMasterId($stopUid, $uid)) {
            $rowMst = $dalSlave->getSlaveById($rowSlave['master_id']);
            if ($uid == $rowMst['master_id']) {
                //is my slave's slave
                $uid = $rowSlave['master_id'];
            }
            else {
                $this->_redirect($this->_baseUrl . '/slave/profile?uid=' . $stopUid);
                return;
            }
        }
        require_once 'Dal/Slave/Revolution.php';
        $dalRev = Dal_Slave_Revolution::getDefaultInstance();
        //is not in revolution
        $rowInRev = $dalRev->getInRevolutionById($stopUid, $uid);
        if (empty($rowInRev)) {
            $this->_redirect($this->_baseUrl . '/slave/profile?uid=' . $stopUid);
            return;
        }
        //stop revolution
        require_once 'Bll/Slave/Revolution.php';
        $bllRev = new Bll_Slave_Revolution();
        $result = $bllRev->stopRevolute($uid, $stopUid);
        if (false === $result) {
            $this->_redirect($this->_baseUrl . '/slave/profile?uid=' . $stopUid);
            return;
        }
        //my slave info
        $slaveInfo = Bll_User::getPerson($stopUid);
        $this->view->slaveName = $slaveInfo->getDisplayName();
        $this->view->slaveUrl = $slaveInfo->getProfileUrl();
        $this->view->slavePic = $slaveInfo->getThumbnailUrl();
        $this->view->slaveInfo = $rowSlave;
        $this->view->money = number_format($result);
        require_once 'Bll/Slave/Activity.php';
        $this->view->activity = Bll_Slave_Activity::getActivity('', $stopUid, false, 14);
        $this->view->activityUser = $stopUid;
        $this->view->activityPic = $this->_staticUrl . '/apps/slave/img/feed/action/6.jpg';
        $this->render();
    }

    /**
     * work Action
     *
     */
    public function workAction()
    {
        $uid = $this->_user->getId();
        $tarUid = $this->_request->getParam('uid');
        if ($uid == $tarUid || empty($tarUid)) {
            $this->_redirect($this->_baseUrl . '/slave/home');
            return;
        }
        $rowSlave = $this->_slaveInfo;
        require_once 'Dal/Slave/Slave.php';
        $dalSlave = Dal_Slave_Slave::getDefaultInstance();
        $rowTarSlave = $dalSlave->getSlaveById($tarUid);
        //is current user's slave
        if (empty($rowTarSlave) || $rowSlave['uid'] != $rowTarSlave['master_id']) {
            $this->_redirect($this->_baseUrl . '/slave/profile?uid=' . $tarUid);
            return;
        }
        require_once 'Dal/Slave/Work.php';
        $dalWork = Dal_Slave_Work::getDefaultInstance();
        //tar slave - work
        $lstWork = $dalWork->listWorkByUid($tarUid);
        if (empty($lstWork) || 0 == count($lstWork)) {
            require_once 'Bll/Slave/Work.php';
            $bllWork = new Bll_Slave_Work();
            $result = $bllWork->initWork($tarUid);
            if ($result) {
                $lstWork = $dalWork->listWorkByUid($tarUid);
            }
        }
        $rowTarSlave['work_category'] = 'フリーター';
        if (!empty($lstWork) && 0 < count($lstWork) && !empty($lstWork[0]['last_working_time'])) {
            $rowTarSlave['work_category'] = $lstWork[0]['category'];
        }
        //tar slave - health
        $aryHealth = array();
        for ($i = 1; $i <= 10; $i++) {
            $hp = 1;
            if ($i > $rowTarSlave['health']) {
                $hp = 0;
            }
            $aryHealth[] = $hp;
        }
        $rowTarSlave['work_health'] = $aryHealth;
        //list can do work
        foreach ($lstWork as $key => $wItem) {
            $rowWork = $dalWork->getNbWorkByKey($wItem['category_id'], $wItem['wlevel']);
            $lstWork[$key]['wtitle'] = $rowWork['wtitle'];
            $lstWork[$key]['wname'] = $rowWork['wname'];
            $lstWork[$key]['salary'] = $rowWork['salary'];
            $lstWork[$key]['format_salary'] = number_format($rowWork['salary']);
            $lstWork[$key]['consume_health'] = $rowWork['consume_health'];
            $lstWork[$key]['pic_big'] = $rowWork['pic_big'];
            $aryHealth = array();
            for ($i = 1; $i <= 10; $i++) {
                $hp = 1;
                if ($i > $rowWork['consume_health']) {
                    $hp = 0;
                }
                $aryHealth[] = $hp;
            }
            $lstWork[$key]['consume_health_array'] = $aryHealth;
        }
        $this->view->lstWork = $lstWork;
        //tar slave info
        $tarInfo = Bll_User::getPerson($tarUid);
        $this->view->tarName = $tarInfo->getDisplayName();
        $this->view->tarUrl = $tarInfo->getProfileUrl();
        $this->view->tarPic = $tarInfo->getThumbnailUrl();
        $rowTarSlave['price_rank'] = $dalSlave->getSlavePriceRank($tarUid);
        $this->view->tarSlaveInfo = $rowTarSlave;
        //my info
        require_once 'Bll/User.php';
        $myInfo = Bll_User::getPerson($uid);
        $this->view->myName = $myInfo->getDisplayName();
        $this->view->myUrl = $myInfo->getProfileUrl();
        $this->view->myPic = $myInfo->getThumbnailUrl();
        $rowSlave['total_rank'] = $dalSlave->getTotalRankById($uid);
        $this->view->mineInfo = $rowSlave;
        $this->render();
    }

    /**
     * rank Action
     *
     */
    public function rankAction()
    {
        $uid = $this->_user->getId();

        $this->view->hasPos = 1;
        $more = $this->_request->getParam('more');
        if (!empty($more)) {
            $this->view->hasPos = 0;
        }
        $sub_str_size = 8;
        require_once 'Bll/User.php';
        require_once 'Dal/Slave/Slave.php';
        $dalSlave = Dal_Slave_Slave::getDefaultInstance();
        //price rank leader
        $listPriceAllLeader = $dalSlave->listPriceRankAll(1, 2);
        if (count($listPriceAllLeader) == 0 && !empty($uid)) {
            $listPriceAllLeader[0] = $dalSlave->getSlaveById($uid);
        }
        foreach ($listPriceAllLeader as $key => $pdata) {
            $listPriceAllLeader[$key]['rankNo'] = (int)($key + 1);
            $formatPrice = $pdata['price'] . '円';
            if ($pdata['price'] >= 10000) {
                $formatPrice = floor($pdata['price'] / 10000) . '万円';
            }
            $listPriceAllLeader[$key]['format_price'] = $formatPrice;
            $rowInfo = Bll_User::getPerson($pdata['uid']);
            $tmpName = $rowInfo->getDisplayName();

            $tmpName = html_entity_decode($tmpName, ENT_QUOTES, 'UTF-8');
            if (mb_strlen($tmpName, 'UTF-8') > $sub_str_size) {
                $tmpName = mb_substr($tmpName, 0, $sub_str_size, 'UTF-8') . '…';
            }
            $tmpName = htmlentities($tmpName, ENT_QUOTES, 'UTF-8');

            $listPriceAllLeader[$key]['name'] = $tmpName;
            $listPriceAllLeader[$key]['pic'] = $rowInfo->getThumbnailUrl();
        }
        for ($i = count($listPriceAllLeader); $i < 2; $i++) {
            $listPriceAllLeader[$i]['uid'] = 0;
            $listPriceAllLeader[$i]['rankNo'] = (int)($i + 1);
            $listPriceAllLeader[$i]['name'] = '??????';
            $listPriceAllLeader[$i]['format_price'] = '??万円';
        }

        $rankStart = 3;
        $fetchSize = 5;
        //get my position
        $rankMine = $dalSlave->getSlavePriceRank($uid);
        $count = $dalSlave->getPriceRankAllCount();

        //in center
        if ($rankMine > 7 && ($rankMine + $fetchSize) <= $count) {
            $rankStart = $rankMine - 2;
        }
        //last six
        else if (($rankMine + $fetchSize) > $count && ($count-2) > $fetchSize) {
            $rankStart = $count - $fetchSize + 1;
        }
        $rankEnd = ($rankStart + $fetchSize - 1) > $count ? $count : ($rankStart + $fetchSize - 1);

        $listPriceAll = $dalSlave->listPriceRankAll($rankStart, $fetchSize);

        foreach ($listPriceAll as $key => $pdata) {
            $listPriceAll[$key]['rankNo'] = (int)($rankStart + $key);
            $formatPrice = $pdata['price'] . '円';
            if ($pdata['price'] >= 10000) {
                $formatPrice = floor($pdata['price'] / 10000) . '万円';
            }
            $listPriceAll[$key]['format_price'] = $formatPrice;
            $rowInfo = Bll_User::getPerson($pdata['uid']);

            $tmpName = $rowInfo->getDisplayName();
            $tmpName = html_entity_decode($tmpName, ENT_QUOTES, 'UTF-8');
            if (mb_strlen($tmpName, 'UTF-8') > 8) {
                $tmpName = mb_substr($tmpName, 0, 8, 'UTF-8') . '…';
            }
            $tmpName = htmlentities($tmpName, ENT_QUOTES, 'UTF-8');

            $listPriceAll[$key]['name'] = $tmpName;
            $listPriceAll[$key]['pic'] = $rowInfo->getThumbnailUrl();
        }

        if (3 == $rankStart && count($listPriceAll) < 5) {
            for ($i = count($listPriceAll); $i < 5; $i++) {
                $listPriceAll[$i]['uid'] = 0;
                $listPriceAll[$i]['rankNo'] = (int)($rankStart + $i);
                $listPriceAll[$i]['name'] = '??????';
                $listPriceAll[$i]['format_price'] = '??万円';
            }
        }

        $this->view->countTwo = $count;
        $this->view->rankStartTwo = $rankStart;
        $this->view->rankEndTwo = $rankEnd;
        $this->view->lstPriceAll = array_reverse($listPriceAll);
        $this->view->lstPriceAllLeader = array_reverse($listPriceAllLeader);

        //price rank leader friend
        $listPriceFriendLeader = $dalSlave->listPriceRankFriend($uid, 1, 2);
        if (count($listPriceFriendLeader) == 0 && !empty($uid)) {
            $listPriceFriendLeader[0] = $dalSlave->getSlaveById($uid);
            $rowInfo = Bll_User::getPerson($listPriceFriendLeader[0]['uid']);
        }
        foreach ($listPriceFriendLeader as $key => $pFdata) {
            $listPriceFriendLeader[$key]['rankNo'] = (int)($key + 1);
            $formatPrice = $pFdata['price'] . '円';
            if ($pFdata['price'] >= 10000) {
                $formatPrice = floor($pFdata['price'] / 10000) . '万円';
            }
            $listPriceFriendLeader[$key]['format_price'] = $formatPrice;
            $rowInfo = Bll_User::getPerson($pFdata['uid']);
            $tmpName = $rowInfo->getDisplayName();
            $tmpName = html_entity_decode($tmpName, ENT_QUOTES, 'UTF-8');
            if (mb_strlen($tmpName, 'UTF-8') > $sub_str_size) {
                $tmpName = mb_substr($tmpName, 0, $sub_str_size, 'UTF-8') . '…';
            }
            $tmpName = htmlentities($tmpName, ENT_QUOTES, 'UTF-8');

            $listPriceFriendLeader[$key]['name'] = $tmpName;
            $listPriceFriendLeader[$key]['pic'] = $rowInfo->getThumbnailUrl();
        }
        for ($i = count($listPriceFriendLeader); $i < 2; $i++) {
            $listPriceFriendLeader[$i]['uid'] = 0;
            $listPriceFriendLeader[$i]['rankNo'] = (int)($i + 1);
            $listPriceFriendLeader[$i]['name'] = '??????';
            $listPriceFriendLeader[$i]['format_price'] = '??万円';
        }

        $rankStartOne = 3;
        $fetchSizeOne = 5;
        //get my position
        $rankMineOne = $dalSlave->getSlavePriceFriendRank($uid);
        $countOne = $dalSlave->getPriceRankFriendCount($uid);

        //in center
        if ($rankMineOne > 7 && ($rankMineOne + $fetchSizeOne) <= $countOne) {
            $rankStartOne = $rankMineOne - 2;
        }
        //last six
        else if (($rankMineOne + $fetchSizeOne) > $countOne && ($countOne - 2) > $fetchSizeOne) {
            $rankStartOne = $countOne - $fetchSizeOne + 1;
        }

        $rankEndOne = ($rankStartOne + $fetchSizeOne - 1) > $countOne ? $countOne : ($rankStartOne + $fetchSizeOne - 1);

        $listPriceFriend = $dalSlave->listPriceRankFriend($uid, $rankStartOne, $fetchSizeOne);
        foreach ($listPriceFriend as $key => $pFdata) {
            $listPriceFriend[$key]['rankNo'] = (int)($rankStartOne + $key);
            $formatPrice = $pFdata['price'] . '円';
            if ($pFdata['price'] >= 10000) {
                $formatPrice = floor($pFdata['price'] / 10000) . '万円';
            }
            $listPriceFriend[$key]['format_price'] = $formatPrice;
            $rowInfo = Bll_User::getPerson($pFdata['uid']);
            $tmpName = $rowInfo->getDisplayName();

            $tmpName = html_entity_decode($tmpName, ENT_QUOTES, 'UTF-8');
            if (mb_strlen($tmpName, 'UTF-8') > 8) {
                $tmpName = mb_substr($tmpName, 0, 8, 'UTF-8') . '…';
            }
            $tmpName = htmlentities($tmpName, ENT_QUOTES, 'UTF-8');

            $listPriceFriend[$key]['name'] = $tmpName;
            $listPriceFriend[$key]['pic'] = $rowInfo->getThumbnailUrl();
        }

        if (3 == $rankStartOne && count($listPriceFriend) < $fetchSizeOne) {
            for ($i = count($listPriceFriend); $i < $fetchSizeOne; $i++) {
                $listPriceFriend[$i]['uid'] = 0;
                $listPriceFriend[$i]['rankNo'] = (int)($rankStartOne + $i);
                $listPriceFriend[$i]['name'] = '??????';
                $listPriceFriend[$i]['format_price'] = '??万円';
            }
        }

        $this->view->countOne = $countOne;
        $this->view->rankStartOne = $rankStartOne;
        $this->view->rankEndOne = $rankEndOne;
        $this->view->lstPriceFriend = array_reverse($listPriceFriend);
        $this->view->lstPriceFriendLeader = array_reverse($listPriceFriendLeader);


        //total rank leader friend
        $listTotalFriendLeader = $dalSlave->listTotalRankFriend($uid, 1, 2);
        if (count($listTotalFriendLeader) == 0 && !empty($uid)) {
            $listTotalFriendLeader[0] = $dalSlave->getSlaveById($uid);
        }
        foreach ($listTotalFriendLeader as $key => $tFdata) {
            $listTotalFriendLeader[$key]['rankNo'] = (int)($key + 1);
            $formatPrice = $tFdata['rank_total'] . '円';
            if ($tFdata['rank_total'] >= 10000) {
                $formatPrice = floor($tFdata['rank_total'] / 10000) . '万円';
            }
            $listTotalFriendLeader[$key]['format_price'] = $formatPrice;
            $rowInfo = Bll_User::getPerson($tFdata['uid']);
            $tmpName = $rowInfo->getDisplayName();

            $tmpName = html_entity_decode($tmpName, ENT_QUOTES, 'UTF-8');
            if (mb_strlen($tmpName, 'UTF-8') > $sub_str_size) {
                $tmpName = mb_substr($tmpName, 0, $sub_str_size, 'UTF-8') . '…';
            }
            $tmpName = htmlentities($tmpName, ENT_QUOTES, 'UTF-8');

            $listTotalFriendLeader[$key]['name'] = $tmpName;
            $listTotalFriendLeader[$key]['pic'] = $rowInfo->getThumbnailUrl();
        }
        for ($i = count($listTotalFriendLeader); $i < 2; $i++) {
            $listTotalFriendLeader[$i]['uid'] = 0;
            $listTotalFriendLeader[$i]['rankNo'] = (int)($i + 1);
            $listTotalFriendLeader[$i]['name'] = '??????';
            $listTotalFriendLeader[$i]['format_price'] = '??万円';
        }

        //init
        $rankStartThree = 3;
        $fetchSizeThree = 5;
        //get my position
        $rankMineThree = $dalSlave->getSlaveTotalFriendRank($uid);
        $countThree = $dalSlave->getPriceRankFriendCount($uid);

        //in center
        if ($rankMineThree > 7 && ($rankMineThree + $fetchSizeThree) <= $countThree) {
            $rankStartThree = $rankMineThree - 2;
        }
        //last six
        else if (($rankMineThree + $fetchSizeThree) > $countThree && ($countThree - 2) > $fetchSizeThree) {
            $rankStartThree = $countThree - $fetchSizeThree + 1;
        }
        $rankEndThree = ($rankStartThree + $fetchSizeThree - 1) > $countThree ? $countThree : ($rankStartThree + $fetchSizeThree - 1);

        $listTotalFriend = $dalSlave->listTotalRankFriend($uid, $rankStartThree, $fetchSizeThree);
        foreach ($listTotalFriend as $key => $tFdata) {
            $listTotalFriend[$key]['rankNo'] = (int)($rankStartThree + $key);
            $formatPrice = $tFdata['rank_total'] . '円';
            if ($tFdata['rank_total'] >= 10000) {
                $formatPrice = floor($tFdata['rank_total'] / 10000) . '万円';
            }
            $listTotalFriend[$key]['format_price'] = $formatPrice;
            $rowInfo = Bll_User::getPerson($tFdata['uid']);
            $tmpName = $rowInfo->getDisplayName();

            $tmpName = html_entity_decode($tmpName, ENT_QUOTES, 'UTF-8');
            if (mb_strlen($tmpName, 'UTF-8') > 8) {
                $tmpName = mb_substr($tmpName, 0, 8, 'UTF-8') . '…';
            }
            $tmpName = htmlentities($tmpName, ENT_QUOTES, 'UTF-8');
            $listTotalFriend[$key]['name'] = $tmpName;
            $listTotalFriend[$key]['pic'] = $rowInfo->getThumbnailUrl();
        }

        if (3 == $rankStartThree && count($listTotalFriend) < $fetchSizeThree) {
            for ($i = count($listTotalFriend); $i < $fetchSizeThree; $i++) {
                $listTotalFriend[$i]['uid'] = 0;
                $listTotalFriend[$i]['rankNo'] = (int)($rankStartThree + $i);
                $listTotalFriend[$i]['name'] = '??????';
                $listTotalFriend[$i]['format_price'] = '??万円';
            }
        }

        $this->view->countThree = $countThree;
        $this->view->rankStartThree = $rankStartThree;
        $this->view->rankEndThree = $rankEndThree;
        $this->view->lstTotalFriendLeader = array_reverse($listTotalFriendLeader);
        $this->view->lstTotalFriend = array_reverse($listTotalFriend);


        //total rank leader all
        $listTotalAllLeader = $dalSlave->listTotalRankAll(1, 2);
        if (count($listTotalAllLeader) == 0 && !empty($uid)) {
            $listTotalAllLeader[0] = $dalSlave->getSlaveById($uid);
        }
        foreach ($listTotalAllLeader as $key => $tdata) {
            $listTotalAllLeader[$key]['rankNo'] = (int)($key + 1);
            $formatPrice = $tdata['rank_total'] . '円';
            if ($tdata['rank_total'] >= 10000) {
                $formatPrice = floor($tdata['rank_total'] / 10000) . '万円';
            }
            $listTotalAllLeader[$key]['format_price'] = $formatPrice;
            $rowInfo = Bll_User::getPerson($tdata['uid']);
            $tmpName = $rowInfo->getDisplayName();

            $tmpName = html_entity_decode($tmpName, ENT_QUOTES, 'UTF-8');
            if (mb_strlen($tmpName, 'UTF-8') > $sub_str_size) {
                $tmpName = mb_substr($tmpName, 0, $sub_str_size, 'UTF-8') . '…';
            }
            $tmpName = htmlentities($tmpName, ENT_QUOTES, 'UTF-8');
            $listTotalAllLeader[$key]['name'] = $tmpName;
            $listTotalAllLeader[$key]['pic'] = $rowInfo->getThumbnailUrl();
        }
        for ($i = count($listTotalAllLeader); $i < 2; $i++) {
            $listTotalAllLeader[$i]['uid'] = 0;
            $listTotalAllLeader[$i]['rankNo'] = (int)($i + 1);
            $listTotalAllLeader[$i]['name'] = '??????';
            $listTotalAllLeader[$i]['format_price'] = '??万円';
        }

        //total rank init all
        $rankStartFour = 3;
        $fetchSizeFour = 5;
        //get my position
        $rankMineFour = $dalSlave->getTotalRankById($uid);
        $countFour = $dalSlave->getPriceRankAllCount($uid);

        //in center
        if ($rankMineFour > 7 && ($rankMineFour + $fetchSizeFour) <= $countFour) {
            $rankStartFour = $rankMineFour - 2;
        }
        //last six
        else if (($rankMineFour + $fetchSizeFour) > $countFour && ($countFour - 2) > $fetchSizeFour) {
            $rankStartFour = $countFour - $fetchSizeFour + 1;
        }
        $rankEndFour = ($rankStartFour + $fetchSizeFour - 1) > $countFour ? $countFour : ($rankStartFour + $fetchSizeFour - 1);

        $listTotalAll = $dalSlave->listTotalRankAll($rankStartFour, $fetchSizeFour);

        foreach ($listTotalAll as $key => $tdata) {
            $listTotalAll[$key]['rankNo'] = (int)($rankStartFour + $key);
            $formatPrice = $tdata['rank_total'] . '円';
            if ($tdata['rank_total'] >= 10000) {
                $formatPrice = floor($tdata['rank_total'] / 10000) . '万円';
            }
            $listTotalAll[$key]['format_price'] = $formatPrice;
            $rowInfo = Bll_User::getPerson($tdata['uid']);
            $tmpName = $rowInfo->getDisplayName();

            $tmpName = html_entity_decode($tmpName, ENT_QUOTES, 'UTF-8');
            if (mb_strlen($tmpName, 'UTF-8') > 8) {
                $tmpName = mb_substr($tmpName, 0, 8, 'UTF-8') . '…';
            }
            $tmpName = htmlentities($tmpName, ENT_QUOTES, 'UTF-8');
            $listTotalAll[$key]['name'] = $tmpName;
            $listTotalAll[$key]['pic'] = $rowInfo->getThumbnailUrl();
        }

        if (3 == $rankStartFour && count($listTotalAll) < $fetchSizeFour) {
            for ($i = count($listTotalAll); $i < $fetchSizeFour; $i++) {
                $listTotalAll[$i]['uid'] = 0;
                $listTotalAll[$i]['rankNo'] = (int)($rankStartFour + $i);
                $listTotalAll[$i]['name'] = '??????';
                $listTotalAll[$i]['format_price'] = '??万円';
            }
        }

        $this->view->countFour = $countFour;
        $this->view->rankStartFour = $rankStartFour;
        $this->view->rankEndFour = $rankEndFour;
        $this->view->lstTotalAll = array_reverse($listTotalAll);
        $this->view->lstTotalAllLeader = array_reverse($listTotalAllLeader);

        $this->render();
    }

    /**
     * deipatch
     *
     */
    function preDispatch()
    {
        $uid = $this->_user->getId();
        //$this->view->mixiAppId = 3096;
        //auto check processing revolution is ready to done
        if ('home' == $this->_request->getActionName() || 'profile' == $this->_request->getActionName()) {
            require_once 'Bll/Slave/Activity.php';
            require_once 'Bll/Slave/Revolution.php';
            $bllRev = new Bll_Slave_Revolution();
            $result = $bllRev->autoCheckRevolution($uid);
            $activity = '';
            if ($result) {
                foreach ($result as $data) {
                    $objUser = explode('|', $data);
                    //self activity
                    $activity .= Bll_Slave_Activity::getActivity('', $objUser[0], false, 5) . '|';
                    //tar activity
                    $activityNew .= Bll_Slave_Activity::getActivity($objUser[1], $objUser[0], false, 13) . '&' . $objUser[0] . '|';
                }
            }
            $profileUid = $this->_request->getParam('uid');
            if (!empty($profileUid) && $uid != $profileUid) {
                $result = $bllRev->autoCheckRevolution($profileUid);
                if ($result) {
                    foreach ($result as $data) {
                        $objUser = explode('|', $data);
                        //self activity
                        $activity .= Bll_Slave_Activity::getActivity('', $objUser[0], false, 5) . '|';
                        //tar activity
                        $activityNew .= Bll_Slave_Activity::getActivity($objUser[1], $objUser[0], false, 13) . '&' . $objUser[0] . '|';
                    }
                }
            }
            $this->view->activity = $activity;
            $this->view->activityNew = $activityNew;
            $this->view->activityPic = $this->_staticUrl . '/apps/slave/img/feed/action/4.jpg';
        }
        require_once 'Dal/Slave/Slave.php';
        $dalSlave = Dal_Slave_Slave::getDefaultInstance();
        $rowSlave = $dalSlave->getSlaveById($uid);
        if (empty($rowSlave)) {
            require_once 'Bll/Slave/Slave.php';
            $bllSlave = new Bll_Slave_Slave();
            $bllSlave->newSlaveUser($uid, true);
            $rowSlave = $dalSlave->getSlaveById($uid);
        }
        if (empty($rowSlave)) {
            return $this->_forward('notfound', 'error', 'default');
        }
        if ('home' == $this->_request->getActionName()) {
            //check is today's first login
            $todayDate = date('Y-m-d');
            $todayTime = strtotime($todayDate);
            $aryLoginTime = array();
            if (empty($rowSlave['last_login_time']) || $rowSlave['last_login_time'] < $todayTime) {
                require_once 'Bll/Secret.php';
                $guid = Bll_Secret::getUUID();
                $this->_isTodayFirstLogin = $guid;
                $aryLoginTime['daily_visit_gift_flag'] = $guid;
            }
            //update last login time
            $aryLoginTime['last_login_time'] = time();
            $dalSlave->updateSlave($aryLoginTime, $uid);
        }
        $rowSlave['fomat_cash'] = number_format($rowSlave['cash']);
        $rowSlave['fomat_price'] = number_format($rowSlave['price']);
        $this->_slaveInfo = $rowSlave;
        $this->view->uid = $uid;
        $this->view->mineCash = $rowSlave['cash'];
        $this->view->mineSlaveCnt = $rowSlave['slave_count'];
        require_once 'Dal/Slave/Gift.php';
        $dalGift = Dal_Slave_Gift::getDefaultInstance();
        $this->view->mineGiftCnt = $dalGift->getGiftByUidCount($uid);
        require_once 'Bll/User.php';
        //common area: cash and price
        $strCash = number_format($rowSlave['cash']);
        $this->view->cashPic = str_split($strCash);
        //common area: visit foot
        require_once 'Dal/Slave/VisitFoot.php';
        $dalVisit = Dal_Slave_VisitFoot::getDefaultInstance();
        $lstVisitFoot = $dalVisit->listVisitFootByUid($uid);
        if (!empty($lstVisitFoot) && count($lstVisitFoot) > 0) {
            Bll_User::appendPeople($lstVisitFoot, 'uid');
        }
        $this->view->lstVisitFoot = $lstVisitFoot;
        //common area: popular
        $lstPopSlave = $dalSlave->listPopularSlave(1, 10);
        if (!empty($lstPopSlave) && count($lstPopSlave) > 0) {
            Bll_User::appendPeople($lstPopSlave, 'uid');
        }
        $this->view->lstPopSlave = $lstPopSlave;
        //common area:ad box
        $adNum = rand(1, 5);
        $adLink = '';
        if (1 == $adNum) {
            $adLink = $this->_baseUrl . '/ad1';
        }
        else if (2 == $adNum) {
            $adLink = $this->_baseUrl . '/ad2';
        }
        else if (3 == $adNum) {
            $adLink = $this->_baseUrl . '/ad3';
        }
        else if (4 == $adNum) {
            $adLink = $this->_baseUrl . '/ad4';
        }
        else if (5 == $adNum) {
            $adLink = $this->_baseUrl . '/ad5';
        }
        $this->view->objAd = array('adNum' => $adNum, 'adLink' => $adLink);
        $this->view->appId = $this->_appId;
        $this->view->mixiHostUrl = MIXI_HOST;
    }

    /******************************************************/
    /*
     * slave shop action
     */
    function shopAction()
    {
        $uid = $this->_user->getId();
        require_once 'Dal/Slave/Slave.php';
        $dalSlave = Dal_Slave_Slave::getDefaultInstance();
        $personal = $dalSlave->getSlaveById($uid);
        require_once 'Dal/Slave/Slave.php';
        $dalSlave = Dal_Slave_Slave::getDefaultInstance();
        require_once 'Dal/Slave/Work.php';
        $dalWork = Dal_Slave_Work::getDefaultInstance();
        $keyWord = 0;
        $lstSlaveShop = array();
        $count = 0;
        if (1 == $keyWord) {
            $lstSlaveShop = $dalSlave->listSlaveShopMixiUser(1, 10, 0);
            $count = $dalSlave->getMixiuserCount();
        }
        else {
        	//$fidsStr = Bll_Friend::getFriendIds($uid);
            //$fidsArr = explode(',', $fidsStr);
            $lstSlaveShop = $dalSlave->listSlaveShopFirends($uid, 1, 10, 0);
            $count = $dalSlave->getMixiFriendById($uid);
        }
        require_once 'Bll/User.php';
        if (!empty($lstSlaveShop) && count($lstSlaveShop) > 0) {
            foreach ($lstSlaveShop as $key => $value) {
                $lstSlaveShop[$key]['work_category'] = 'フリーター';
                $lstSlaveShop[$key]['format_price'] = number_format($value['price']);
                if (null != $value['cash']) {
                    $lstWork = $dalWork->listWorkByUid($value['uid']);
                    if (!empty($lstWork) && 0 < count($lstWork) && !empty($lstWork[0]['last_working_time'])) {
                        $lstSlaveShop[$key]['work_category'] = $lstWork[0]['category'];
                    }
                }
            }
            Bll_User::appendPeople($lstSlaveShop, 'uid');
        }
        $pageCount = ceil($count / 10);
        $page = array();
        for ($i = 0; $i < $pageCount && $i < 10; $i++) {
            $page[$i] = $i + 1;
        }
        $this->view->uid = $uid;
        $this->view->page = $page;
        $this->view->slaveCount = $personal['slave_count'];
        $this->view->cash = $personal['cash'];
        $this->view->masterId = $personal['master_id'];
        $this->view->Count = $count;
        require_once 'Dal/Slave/Gift.php';
        $dalGift = Dal_Slave_Gift::getDefaultInstance();
        $giftCount = $dalGift->getGiftByUidCount($uid);
        $this->view->giftCount = $giftCount;
        $lstSlave = $dalSlave->listSlaveByUid($uid);
        //slave id string
        $slaveIdStr = '';
        foreach ($lstSlave as $value) {
            $slaveIdStr .= $value['uid'] . "|";
        }
        //is login user's id
        foreach ($lstSlaveShop as $key => $value) {
            $buy = true;
            $tease = true;
            $gift = true;
            if ($uid != $lstSlaveShop[$key]['uid']) {
                //is master or no money
                if ($personal['master_id'] == $lstSlaveShop[$key]['uid']) {
                    $buy = false;
                }
                if ($personal['cash'] < $lstSlaveShop[$key]['price']) {
                    $buy = false;
                }
                //judge whether is login user's slave
                foreach ($lstSlave as $ids) {
                    if ($ids['uid'] == $lstSlaveShop[$key]['uid']) {
                        $buy = false;
                        //only one slave
                        if (1 == $personal['slave_count']) {
                            $tease = false;
                        }
                        break;
                    }
                }
                //no slaves
                if (empty($personal['slave_count'])) {
                    $tease = false;
                }
                //no gift
                if (empty($giftCount)) {
                    $gift = false;
                }
            }
            else {
                $buy = false;
                $tease = false;
                $gift = false;
            }
            $lstSlaveShop[$key]['buy'] = $buy;
            $lstSlaveShop[$key]['tease'] = $tease;
            $lstSlaveShop[$key]['gift'] = $gift;
        }
        $this->view->slaveUidStr = $slaveIdStr;
        $this->view->lstShopLeader = $lstSlaveShop;
        $this->render();
    }

    /*
     * buy slave action
     */
    public function buyslaveAction()
    {
        $slaveid = $this->_request->getParam('uid');

        if (empty($slaveid)) {
            $this->_redirect($this->_baseUrl . '/slave/home');
            return;
        }

        $uid = $this->_user->getId();
        require_once 'Dal/Slave/Slave.php';
        $dalSlave = Dal_Slave_Slave::getDefaultInstance();
        //whether masterId
        if ($dalSlave->isSlaveByMasterId($uid, $slaveid)) {
            $this->_redirect($this->_baseUrl . '/slave/profile?uid=' . $slaveid);
            return;
        }
        //whether revolution
        require_once 'Bll/Slave/Activity.php';
        require_once 'Bll/Slave/Revolution.php';
        $bllRev = new Bll_Slave_Revolution();
        $result = $bllRev->autoCheckRevolution($slaveid);
        if ($result) {
            foreach ($result as $data) {
                $objUser = explode('|', $data);
                //self activity
                $activity .= Bll_Slave_Activity::getActivity('', $objUser[0], false, 5) . '|';
                //tar activity
                $activityNew .= Bll_Slave_Activity::getActivity($objUser[1], $objUser[0], false, 13) . '&' . $objUser[0] . '|';
            }
        }
        $this->view->activity = $activity;
        $this->view->activityNew = $activityNew;
        $this->view->activityPic = $this->_staticUrl . '/apps/slave/img/feed/action/4.jpg';
        require_once 'Dal/Slave/Slave.php';
        $dalSlave = Dal_Slave_Slave::getDefaultInstance();
        $buySlaveInfo = $dalSlave->getSlaveById($slaveid);
        //not slave user
        if (empty($buySlaveInfo)) {
            //defalut value
            $buySlaveInfo['uid'] = $slaveid;
            $buySlaveInfo['price'] = 4980;
            $buySlaveInfo['cash'] = 15000;
        }
        $buySlaveInfo['format_price'] = number_format($buySlaveInfo['price']);
        $buySlaveInfo['work_category'] = 'フリーター';
        require_once 'Dal/Slave/Work.php';
        $dalWork = Dal_Slave_Work::getDefaultInstance();
        $lstWork = $dalWork->listWorkByUid($slaveid);
        //judge whether have work
        if (!empty($lstWork) && 0 < count($lstWork) && !empty($lstWork[0]['last_working_time'])) {
            $buySlaveInfo['work_category'] = $lstWork[0]['category'];
        }
        if (empty($buySlaveInfo['balloon']) || '' == $buySlaveInfo['balloon']) {
            $buySlaveInfo['balloon'] = "誰か私を買って下さい。";
        }
        require_once 'Bll/User.php';
        $msInfo = Bll_User::getPerson($slaveid);
        $tmpName = $msInfo->getDisplayName();
        $this->view->msName = $tmpName;
        $this->view->msUrl = $msInfo->getProfileUrl();
        $this->view->msPic = $msInfo->getThumbnailUrl();
        $nickName = $buySlaveInfo['nickname'];
        if (empty($buySlaveInfo['nickname'])) {
            $tmpName = $msInfo->getDisplayName();
            $buySlaveInfo['nickname'] = htmlspecialchars_decode($tmpName . 'ちゃん', ENT_QUOTES);
        }
        else {
            $buySlaveInfo['nickname'] = htmlspecialchars_decode($nickName, ENT_QUOTES);
        }
        $this->view->slaveInfo = $buySlaveInfo;
        $this->view->slaveCount = $this->_slaveInfo['slave_count'];
        $this->render();
    }

    /**
     * gift shop
     */
    public function giftshopAction()
    {
        $uid = $this->_user->getId();
        $more = $this->_request->getParam('more');
        $this->view->hasPos = 1;
        if (!empty($more)) {
            $this->view->hasPos = 0;
        }

        require_once 'Dal/Slave/Gift.php';
        $dalGift = Dal_Slave_Gift::getDefaultInstance();
        $lstPopularityGift = $dalGift->lstPopularGift(0);
        require_once 'Bll/Slave/RakutenApi.php';
        $gids = $dalGift->listGidsById($uid);
        $gIdStr = '';
        foreach ($gids as $key => $value) {
            $gIdStr .= $gids[$key]['gid'] . "|";
        }
        foreach ($lstPopularityGift as $key => $value) {
            $imgArr = Bll_Slave_RakutenApi::getImgByCode($value['gid']);
	        $giftPic = $imgArr['gift_small_pic'];
	        if (empty($giftPic) || $giftPic == null) {
	            $giftPic = $this->_staticUrl . '/apps/slave/img/dummy/pic_n_s.png';
	        }
            $lstPopularityGift[$key]['gift_small_pic'] = $giftPic;

            $lstPopularityGift[$key]['gift_format_price'] = number_format($lstPopularityGift[$key]['gift_price']);
            if ($this->_slaveInfo['cash'] > $lstPopularityGift[$key]['gift_price']) {
                $lstPopularityGift[$key]['buy'] = false;
            }
            else {
                $lstPopularityGift[$key]['buy'] = true;
            }
            foreach ($gids as $ids) {
                if ($ids['gid'] == $lstPopularityGift[$key]['gid']) {
                    $lstPopularityGift[$key]['add'] = true;
                    break;
                }
                else {
                    $lstPopularityGift[$key]['add'] = false;
                }
            }
        }
        $lstGiftInfo = $dalGift->listGiftFavByid($uid, 1, 10, 1);
        if (!empty($lstGiftInfo) && count($lstGiftInfo) > 0) {
            foreach ($lstGiftInfo as $key => $value) {
                $imgArr = Bll_Slave_RakutenApi::getImgByCode($value['gid']);
	            $giftPic = $imgArr['gift_small_pic'];
	            if (empty($giftPic) || $giftPic == null) {
	                $giftPic = $this->_staticUrl . '/apps/slave/img/dummy/pic_n_s.png';
	            }
                $lstGiftInfo[$key]['gift_small_pic'] = $giftPic;

                $lstGiftInfo[$key]['gift_format_price'] = number_format($lstGiftInfo[$key]['gift_price']);
                if ($this->_slaveInfo['cash'] > $lstGiftInfo[$key]['gift_price']) {
                    $lstGiftInfo[$key]['buy'] = false;
                }
                else {
                    $lstGiftInfo[$key]['buy'] = true;
                }
                foreach ($gids as $ids) {
                    if ($ids['gid'] == $lstGiftInfo[$key]['gid']) {
                        $lstGiftInfo[$key]['add'] = true;
                        break;
                    }
                    else {
                        $lstGiftInfo[$key]['add'] = false;
                    }
                }
            }
        }
        $favCount = $dalGift->getGiftFavCount($uid);
        $pageCount = ceil($favCount / 10);
        $page = array();
        for ($i = 0; $i < $pageCount && $i < 10; $i++) {
            $page[$i] = $i + 1;
        }
        $this->view->myCash = $this->_slaveInfo['cash'];
        $uid = $this->_user->getId();
        $this->view->uid = $uid;
        $this->view->lstPopularityGift = $lstPopularityGift;
        $this->view->favCount = $favCount;
        $this->view->page = $page;
        $this->view->lstGiftInfo = $lstGiftInfo;
        $this->view->gids = $gIdStr;
        $this->view->Pcount = count($lstPopularityGift);
        $this->render();
    }

    /**
     * gift search result
     */
    public function searchresultAction()
    {
        $uid = $this->_user->getId();
        $sort = $this->_request->getParam('radSort',1);
        $this->view->sort = $sort;

        require_once 'Dal/Slave/Gift.php';
        $dalGift = Dal_Slave_Gift::getDefaultInstance();
        $gids = $dalGift->listGidsById($uid);
        $gIdStr = '';
        foreach ($gids as $key => $value) {
            $gIdStr .= $gids[$key]['gid'] . "|";
        }
        $keyWord = $this->_request->getParam('txtKeyWord');
        if (1 == $sort) {
            $sort = '+itemPrice';
        }
        else if (2 == $sort) {
            $sort = '-itemPrice';
        }
        $lstSearchGift = Bll_Slave_RakutenApi::listItems($keyWord, $sort, 1, 10);
        $info = $lstSearchGift['info'];
        array_pop($info);
        if (!empty($info) && count($info) > 0) {
            foreach ($info as $key => $value) {
                if ($this->_slaveInfo['cash'] > $info[$key]['gift_price']) {
                    $info[$key]['buy'] = false;
                }
                else {
                    $info[$key]['buy'] = true;
                }
                foreach ($gids as $ids) {
                    if ($ids['gid'] == $info[$key]['gid']) {
                        $info[$key]['add'] = true;
                        break;
                    }
                    else {
                        $info[$key]['add'] = false;
                    }
                }
            }
        }
        $searchCount = $lstSearchGift['count'];
        $pageCount = ceil($searchCount / 10);
        $page = array();
        for ($i = 0; $i < $pageCount && $i < 10; $i++) {
            $page[$i] = $i + 1;
        }
        $this->view->page = $page;
        $this->view->keyWord = $keyWord;
        $this->view->myCash = $this->_slaveInfo['cash'];
        $this->view->uid = $uid;
        $this->view->gids = $gIdStr;
        $this->view->searchCount = $searchCount;
        $this->view->MaxPage = $pageCount;
        $this->view->lstSearchGift = $info;
        $this->render();
    }

    /**
     * present gift
     */
    public function presentgiftAction()
    {
    	$loginId = $this->_user->getId();

        $uid = $this->_request->getParam('uid');
        $id = $this->_request->getParam('id');

        if (empty($uid) && empty($id)) {
            $this->_redirect($this->_baseUrl . '/slave/home');
            return;
        }

        require_once 'Bll/User.php';
        require_once 'Bll/Slave/RakutenApi.php';

        require_once 'Dal/Slave/Slave.php';
        $dalSlave = Dal_Slave_Slave::getDefaultInstance();

        require_once 'Dal/Slave/Gift.php';
        $dalGift = Dal_Slave_Gift::getDefaultInstance();

        if (!empty($uid)) {
            $msInfo = Bll_User::getPerson($uid);
            $this->view->msName = $msInfo->getDisplayName();
            $this->view->msUrl = $msInfo->getProfileUrl();
            $this->view->msPic = $msInfo->getThumbnailUrl();
            $this->view->friendcss = 'black';
            $this->view->giftcss = 'none';

            $friendInfo = $dalSlave->getSlaveById($uid);
            if (empty($friendInfo['nickname'])) {
                $tmpName = $msInfo->getDisplayName();
                $tmpName = htmlspecialchars_decode($tmpName, ENT_QUOTES);
                $friendInfo['nickname'] = $msInfo->getDisplayName() . 'ちゃん';
            }
            if (empty($friendInfo['balloon'])) {
                $friendInfo['balloon'] = "誰か私を買って下さい。";
            }

	        $lstGiftList = $dalGift->listGiftById($loginId);

	        if (!empty($lstGiftList) && count($lstGiftList) > 0) {
	            foreach ($lstGiftList as $key => $value) {
	                $imgArr = Bll_Slave_RakutenApi::getImgByCode($value['gid']);
		            $giftPic = $imgArr['gift_small_pic'];
	                if (empty($giftPic) || $giftPic == null) {
	                    $giftPic = $this->_staticUrl . '/apps/slave/img/dummy/pic_n_s.png';
	                }
	                $lstGiftList[$key]['gift_small_pic'] = $giftPic;

	                $lstGiftList[$key]['gift_format_price'] = number_format($lstGiftList[$key]['gift_price']);
	            }
	        }
            else {
                $this->_redirect($this->_baseUrl . '/slave/home');
                return;
            }

            $this->view->friendId = $uid;
            $this->view->lstGiftInfo = $lstGiftList;
            $this->view->friendInfo = $friendInfo;
            $this->view->presentClass = 'presentGiftS1';
            $this->view->pinfo = '次のお友達にギフトをプレゼントするよ。贈るギフトを選択してね。';
        }
        if (!empty($id)) {
            $this->view->friendcss = 'none';
            $this->view->giftcss = 'black';
            $this->view->keyGid = $id;

            $aryGift = $dalGift->getGidByid($id);
            $imgArr = Bll_Slave_RakutenApi::getImgByCode($aryGift['gid']);
            $giftPic = $imgArr['gift_small_pic'];
            if (empty($giftPic) || $giftPic == null) {
                $giftPic = $this->_staticUrl . '/apps/slave/img/dummy/pic_n_s.png';
            }
            $aryGift['gift_small_pic'] = $giftPic;

            $aryGift['gift_format_price'] = number_format($aryGift['gift_price']);

	        require_once 'Dal/Slave/Friend.php';
	        $dalFriend = Dal_Slave_Friend::getDefaultInstance();
	        $lstFriendInfo = $dalFriend->listSlaveFriend($loginId);

	        if (!empty($lstFriendInfo) && count($lstFriendInfo) > 0) {
		        foreach ($lstFriendInfo as $key => $value) {
	                $lstFriendInfo[$key] = $dalSlave->getSlaveById($value['fid']);
	                $fInfo = Bll_User::getPerson($value['fid']);
	            }

	            Bll_User::appendPeople($lstFriendInfo, 'uid');
	        }
	        else {
	        	$this->_redirect($this->_baseUrl . '/slave/home');
                return;
	        }

            $this->view->lstFriendInfo = $lstFriendInfo;
            $this->view->id = $id;
            $this->view->giftInfo = $aryGift;
            $this->view->presentClass = 'presentGiftS1a';
            $this->view->pinfo = '次のギフトをお友達にプレゼントするよ。贈り先のお友達を選択してね。';
        }
        $this->render();
    }

    public function teaseAction()
    {
    	$uid = $this->_user->getId();
        $friendId = $this->_request->getParam('uid');

        if ($uid == $friendId || empty($friendId)) {
            $this->_redirect($this->_baseUrl . '/slave/home');
            return;
        }

        require_once 'Dal/Slave/Slave.php';
        $dalSlave = Dal_Slave_Slave::getDefaultInstance();
        $friendInfo = $dalSlave->getSlaveById($friendId);

        require_once 'Bll/User.php';
        $msInfo = Bll_User::getPerson($friendId);

        $this->view->msName = $msInfo->getDisplayName();
        $this->view->msUrl = $msInfo->getProfileUrl();
        $this->view->msPic = $msInfo->getThumbnailUrl();

        if (empty($friendInfo['nickname'])) {
            $friendInfo['nickname'] = $msInfo->getDisplayName() . 'ちゃん';
        }
        if (empty($friendInfo['balloon'])) {
            $friendInfo['balloon'] = "誰か私を買って下さい。";
        }

        $this->view->friendInfo = $friendInfo;
        $this->view->friendId = $friendId;

        $slaveLstInfo = $dalSlave->listSlaveByUid($uid);
        foreach ($slaveLstInfo as $key => $value) {
        	if ($value['uid'] == $friendId) {
        		unset($slaveLstInfo[$key]);
        		continue;
        	}

            $slaveInfo = Bll_User::getPerson($value['uid']);
	        if (empty($value['nickname'])) {
	            $slaveLstInfo[$key]['nickname'] = $slaveInfo->getDisplayName() . 'ちゃん';
	        }

	        if (empty($value['balloon'])) {
	            $slaveLstInfo[$key]['balloon'] = "誰か私を買って下さい。";
	        }
            $slaveLstInfo[$key]['price_rank'] = $dalSlave->getSlavePriceRank($value['uid']);
        }

        require_once 'Bll/User.php';
        Bll_User::appendPeople($slaveLstInfo, 'uid');

        require_once 'Dal/Slave/Tease.php';
        $dalTease = Dal_Slave_Tease::getDefaultInstance();
        $lstNbTease = $dalTease->listNbTease();

        $this->view->slaveInfo = $slaveLstInfo;
        $this->view->lstNbTease = $lstNbTease;
        $fobUser = $this->_slaveInfo;
        $this->view->isForbidCustomTease = $fobUser['is_fobid_custom_tease'];
        $this->render();
    }

    /**
     * buy gift
     */
    public function buygiftAction()
    {
        $gid = $this->_request->getParam('gid');
        $uid = $this->_user->getId();

        if (empty($gid)) {
            $this->_redirect($this->_baseUrl . '/slave/home');
            return;
        }

        require_once 'Dal/Slave/Gift.php';
        $dalGift = Dal_Slave_Gift::getDefaultInstance();

        require_once 'Bll/Slave/Gift.php';
        $bllGift = new Bll_Slave_Gift();

        $aryGift = $dalGift->getGiftFavById($gid);

        require_once 'Bll/Slave/RakutenApi.php';
        $giftInfo = Bll_Slave_RakutenApi::getItemByCode($gid);
        if (empty($aryGift)) {
            $aryGift['gid'] = $gid;
            $aryGift['gift_name'] = $giftInfo['gift_name'];
            $aryGift['gift_price'] = $giftInfo['gift_price'];
        }

        $giftPic = $giftInfo['gift_small_pic'];
        if (empty($giftPic) || $giftPic == null) {
           $giftPic = $this->_staticUrl . '/apps/slave/img/dummy/pic_n_s.png';
        }

        $aryGift['gift_small_pic'] = $giftPic;
        $aryGift['gift_format_price'] = number_format($aryGift['gift_price']);
        $this->view->giftInfo = $aryGift;
        $this->render();
    }

    public function sellgiftAction()
    {
        $id = $this->_request->getParam('id');

        if (empty($id)) {
            $this->_redirect($this->_baseUrl . '/slave/home');
            return;
        }

        $uid = $this->_user->getId();
        require_once 'Dal/Slave/Gift.php';
        $dalGift = Dal_Slave_Gift::getDefaultInstance();
        require_once 'Dal/Slave/Slave.php';
        $dalSlave = Dal_Slave_Slave::getDefaultInstance();
        $myTotalRank = $dalSlave->getTotalRankById($uid);
        $aryGift = $dalGift->getGidByid($id);
        require_once 'Bll/Slave/RakutenApi.php';
        $imgArr = Bll_Slave_RakutenApi::getImgByCode($aryGift['gid']);

        $giftPic = $imgArr['gift_small_pic'];
        if (empty($giftPic) || $giftPic == null) {
           $giftPic = $this->_staticUrl . '/apps/slave/img/dummy/pic_n_s.png';
        }
        $aryGift['gift_small_pic'] = $giftPic;
        $aryGift['gift_format_price'] = number_format($aryGift['gift_price']);
        $this->view->giftInfo = $aryGift;
        $this->view->keyId = $id;
        $this->view->sellPrice = number_format(ceil($aryGift['gift_price'] * 0.8));
        $this->view->myTotalRank = $myTotalRank;
        $this->render();
    }

    /**
     * remove gift fav
     */
    public function removefavAction()
    {
        $gid = $this->_request->getParam('gid');

        if (empty($gid)) {
            $this->_redirect($this->_baseUrl . '/slave/home');
            return;
        }

        $uid = $this->_user->getId();
        require_once 'Bll/Slave/RakutenApi.php';
        $imgArr = Bll_Slave_RakutenApi::getImgByCode($gid);

        $giftPic = $imgArr['gift_small_pic'];
        if (empty($giftPic) || $giftPic == null) {
           $giftPic = $this->_staticUrl . '/apps/slave/img/dummy/pic_n_s.png';
        }

        require_once 'Dal/Slave/Gift.php';
        $dalGift = Dal_Slave_Gift::getDefaultInstance();
        $giftInfo = $dalGift->getGiftFavById($gid);

        if (empty($giftInfo) || $giftInfo == null) {
        	$giftInfo = $dalGift->getNbGift($gid);
        	$giftInfo['gift_price'] = $giftInfo['price'];
        }

        $giftInfo['gift_small_pic'] = $giftPic;
        $giftInfo['gift_format_price'] = number_format($giftInfo['gift_price']);

        require_once 'Bll/Slave/Gift.php';
        $bllGift = new Bll_Slave_Gift();
        $bllGift->deleteGiftFav($uid, $gid);

        $this->view->giftInfo = $giftInfo;
        $this->render();
    }

    /**
     * add gift fav
     */
    public function addfavAction()
    {
        $gid = $this->_request->getParam('gid');

        if (empty($gid)) {
            $this->_redirect($this->_baseUrl . '/slave/home');
            return;
        }

        $uid = $this->_user->getId();
        require_once 'Bll/Slave/Gift.php';
        $bllGift = new Bll_Slave_Gift();
        require_once 'Dal/Slave/Gift.php';
        $dalGift = Dal_Slave_Gift::getDefaultInstance();
        $aryGift = $dalGift->getNbGift($gid);
        $info = array();
        require_once 'Bll/Slave/RakutenApi.php';
        $giftInfo = Bll_Slave_RakutenApi::getItemByCode($gid);
        $isExists = false;
        if (empty($aryGift)) {
            $info['gid'] = $gid;
            $info['name'] = htmlspecialchars($giftInfo['gift_name']);
            $info['caption'] = $giftInfo['gift_caption'];
            $info['price'] = $giftInfo['gift_price'];
            $info['url'] = $giftInfo['gift_url'];
            $info['create_time'] = time();
            $isExists = $bllGift->addNbGift($info, $gid);
            $aryGift['name'] = htmlspecialchars($giftInfo['gift_name']);
        }
        $giftCount = $dalGift->getFavCountById($uid, $gid);
        if ($giftCount == 0) {
            $data = array('uid' => $uid, 'gid' => $gid, 'create_time' => time());
            $bllGift->addGiftFav($data);
        }

        $giftPic = $giftInfo['gift_small_pic'];
        if (empty($giftPic) || $giftPic == null) {
           $giftPic = $this->_staticUrl . '/apps/slave/img/dummy/pic_n_s.png';
        }

        $aryGift['gift_small_pic'] = $giftPic;
        $aryGift['gift_format_price'] = $giftInfo['gift_format_price'];
        $this->view->gid = $gid;
        $this->view->giftInfo = $aryGift;
        $this->render();
    }

    /**
     * go torakuten
     */
    public function torakutenAction()
    {
        $gid = $this->_request->getParam('gid');

        if (empty($gid)) {
            $this->_redirect($this->_baseUrl . '/slave/home');
            return;
        }

        require_once 'Dal/Slave/Gift.php';
        $dalGift = Dal_Slave_Gift::getDefaultInstance();
        $giftInfo = $dalGift->getNbGift($gid);
        require_once 'Bll/Slave/RakutenApi.php';
        if (empty($giftInfo) || $giftInfo == null) {
        	$giftInfo = Bll_Slave_RakutenApi::getItemByCode($gid);
        	$giftInfo['url'] = $giftInfo['gift_url'];
        	$giftInfo['price'] = $giftInfo['gift_price'];
        	$giftInfo['name'] = $giftInfo['gift_name'];
        }
        $imgArr = Bll_Slave_RakutenApi::getImgByCode($gid);
        $giftPic = $imgArr['gift_small_pic'];
        if (empty($giftPic) || $giftPic == null) {
            $giftPic = $this->_staticUrl . '/apps/slave/img/dummy/pic_n_s.png';
        }

        $giftInfo['gift_small_pic'] = $giftPic;
        $giftInfo['gift_format_price'] = number_format($giftInfo['price']);
        $this->view->giftInfo = $giftInfo;
        $this->render();
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
        return $this->_forward('notfound', 'error', 'default');
    }
}
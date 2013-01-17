<?php
/** @see Zend_Json */
require_once 'Zend/Json.php';
/** @see MyLib_Zend_Controller_Action_Ajax */
require_once 'MyLib/Zend/Controller/Action/Ajax.php';

/**
 * Slave Ajax Controllers
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/06/24   zhangxin
 */
class Ajax_SlaveController extends MyLib_Zend_Controller_Action_Ajax
{
    private $_subStrSize = 8;

    private $_staticUrl;

	public function postInit()
    {
    	$this->_staticUrl = Zend_Registry::get('static');
    }

    /**
     * get friend list
     *
     */
    public function listfriendAction()
    {
        $uid = $this->_user->getId();
        $posStart = (int)$this->_request->getParam('posStart', 1);
        $fetchSize = (int)$this->_request->getParam('fetchSize', 10);

        require_once 'Dal/Slave/User.php';
        $dalUser = Dal_Slave_User::getDefaultInstance();
        $aryInfo = $dalUser->listAppUids($posStart, $fetchSize);
        Bll_User::appendPeople($aryInfo, 'fid');
        /*
        require_once 'Bll/User.php';
        require_once 'Dal/Slave/Friend.php';
        $dalFriend = Dal_Slave_Friend::getDefaultInstance();
        $aryInfo = $dalFriend->listMixiFriend($uid, $pageIndex, $pageSize);
        $count = $dalFriend->getMixiFriendCount($uid);
        if ($aryInfo != null && count($aryInfo) > 0) {
            Bll_User::appendPeople($aryInfo, 'fid');
        }
        for ($i = count($aryInfo); (1 == $pageIndex) && ($i < 6); $i++) {
            $aryInfo[$i]['fid'] = 0;
        }
		*/
        $response = array('info' => $aryInfo, 'count' => $count);
        $response = Zend_Json::encode($response);

        echo $response;
    }

    /**
     * get gift info
     *
     */
    public function getgiftinfoAction()
    {
        $uid = $this->_request->getParam('uid');
        $pageIndex = (int)$this->_request->getParam('pageIndex', 1);
        $pageSize = (int)$this->_request->getParam('pageSize', 10);

        require_once 'Dal/Slave/Gift.php';
        $dalGift = Dal_Slave_Gift::getDefaultInstance();
        $count = $dalGift->getGiftByUidCount($uid);
        $pageIndex = $pageIndex > $count ? 1 : $pageIndex;
        $pageIndex = $pageIndex < 1 ? $count : $pageIndex;
        $lstGift = $dalGift->listGiftByUid($uid, $pageIndex, $pageSize);

        $aryInfo = false;
        if (!empty($lstGift) && count($lstGift) > 0) {
            require_once 'Bll/Slave/RakutenApi.php';
            $aryImg = Bll_Slave_RakutenApi::getImgByCode($lstGift[0]['gid']);
            $lstGift[0]['gift_small_pic'] = $aryImg['gift_small_pic'];
            $lstGift[0]['gift_big_pic'] = $aryImg['gift_big_pic'];
            $lstGift[0]['format_price'] = number_format($lstGift[0]['price']);
        }

        $response = array('info' => $lstGift[0], 'count' => $count);
        $response = Zend_Json::encode($response);
        echo $response;
    }

    /**
     * list feed
     *
     */
    public function listfeedAction()
    {
        $uid = $this->_request->getParam('uid');
        $mode = (int)$this->_request->getParam('mode');
        if (empty($uid)) {
            echo 'false';
            return;
        }

        require_once 'Dal/Slave/FeedMessage.php';
        $dalFeed = Dal_Slave_FeedMessage::getDefaultInstance();
        $aryInfo = $dalFeed->listFeedMessage($uid, $mode, 1, 30);
        $count = $dalFeed->getFeedMessageCount($uid);

        $response = array('info' => $aryInfo, 'count' => $count);
        $response = Zend_Json::encode($response);
        echo $response;
    }

    /**
     * set nickname
     *
     */
    public function setnicknameAction()
    {
        $uid = $this->_user->getId();
        $tarUid = $this->_request->getParam('uid');
        $nickname = $this->_request->getParam('txtNickname');
        if (empty($tarUid)) {
            echo 'false';
            return;
        }

        require_once 'Dal/Slave/Slave.php';
        $dalSlave = Dal_Slave_Slave::getDefaultInstance();
        $rowTarSlave = $dalSlave->getSlaveById($tarUid);
        //is current user's slave
        if (empty($rowTarSlave) || $uid != $rowTarSlave['master_id']) {
            echo 'false';
            return;
        }

        require_once 'Bll/Slave/Slave.php';
        $bllSlave = new Bll_Slave_Slave();
        $result = $bllSlave->setNicknameOrBalloon($uid, $tarUid, mb_substr($nickname, 0, 10, 'UTF-8'), 1);

        $lstActivity = array();
        $pic = $this->_staticUrl . '/apps/slave/img/feed/action/13.jpg';

        //$activity = Bll_Slave_Activity::getActivity($uid, '', false, 19);
        $activitySlave = Bll_Slave_Activity::getActivity('', $tarUid, false, 25);
        //$lstActivity[0] = array('id' =>$tarUid, 'info' => $activity, 'pic' => $pic);
        $lstActivity[1] = array('info' => $activitySlave, 'pic' => $pic);

        if ($result == 2) {
            echo '2';
            return;
        }

        $response = $result ? $lstActivity : 'false';
        $response = Zend_Json::encode($response);
        echo $response;
    }

    /**
     * set balloon
     *
     */
    public function setballoonAction()
    {
        $uid = $this->_user->getId();
        $tarUid = $this->_request->getParam('uid');
        $balloon = $this->_request->getParam('txtBalloon');
        if (empty($tarUid)) {
            echo 'false';
            return;
        }

        require_once 'Dal/Slave/Slave.php';
        $dalSlave = Dal_Slave_Slave::getDefaultInstance();
        $rowTarSlave = $dalSlave->getSlaveById($tarUid);
        //is current user's slave
        if (empty($rowTarSlave) || $uid != $rowTarSlave['master_id']) {
            echo 'false';
            return;
        }

        require_once 'Bll/Slave/Slave.php';
        $bllSlave = new Bll_Slave_Slave();
        $result = $bllSlave->setNicknameOrBalloon($uid, $tarUid, mb_substr($balloon, 0, 25, 'UTF-8'), 2);

        echo $result ? $result : 'false';
    }

    /**
     * sell slave
     *
     */
    public function sellslaveAction()
    {
        $uid = $this->_user->getId();
        $sellUid = $this->_request->getParam('uid');
        if (empty($sellUid)) {
            echo 'false';
            return;
        }

        require_once 'Dal/Slave/Slave.php';
        $dalSlave = Dal_Slave_Slave::getDefaultInstance();
        $rowSellSlave = $dalSlave->getSlaveById($sellUid);
        //is current user's slave
        if (empty($rowSellSlave) || $uid != $rowSellSlave['master_id']) {
            echo 'false';
            return;
        }

        require_once 'Bll/Slave/Slave.php';
        $bllSlave = new Bll_Slave_Slave();
        $result = $bllSlave->sellSlave($uid, $sellUid);
        if ($result) {
            require_once 'Bll/Slave/Activity.php';
            $lstActivity = array();
            $pic = $this->_staticUrl . '/apps/slave/img/feed/action/3.jpg';
            $activity = Bll_Slave_Activity::getActivity('', $sellUid, false, 3);
            //$activitySellSlave = Bll_Slave_Activity::getActivity($uid, $sellUid, false, 12);
            $lstActivity[0] = array('id' =>null, 'info' => $activity, 'pic' => $pic);
            //$lstActivity[1] = array('id' =>$sellUid, 'info' => $activitySellSlave, 'pic' => $pic);
        }

        $response = $result ? $lstActivity : 'false';
        $response = Zend_Json::encode($response);
        echo $response;
    }

    /**
     * revolution
     *
     */
    public function revoluteAction()
    {
        $uid = $this->_user->getId();
        $sellUid = $this->_request->getParam('sellUid');
        require_once 'Dal/Slave/Slave.php';
        $dalSlave = Dal_Slave_Slave::getDefaultInstance();
        $rowSlave = $dalSlave->getSlaveById($uid);
        if (empty($rowSlave)) {
            echo 'false';
            return;
        }

        require_once 'Bll/Slave/Revolution.php';
        $bllRev = new Bll_Slave_Revolution();
        $result = $bllRev->beginRevolute($uid, $sellUid);
        if ($result) {
            require_once 'Bll/Slave/Activity.php';
            $activity = Bll_Slave_Activity::getActivity('', $rowSlave['master_id'], false, 4);
            $pic = $this->_staticUrl . '/apps/slave/img/feed/action/4.jpg';
            $lstActivity[0] = array('info' => $activity,'pic' => $pic);
        }

        $response = $result ? $lstActivity : 'false';
        $response = Zend_Json::encode($response);
        echo $response;
    }

    /**
     * work
     *
     */
    public function workAction()
    {
        $uid = $this->_user->getId();
        $tarUid = $this->_request->getParam('uid');
        $workId = $this->_request->getParam('workId');

        if (empty($tarUid) || empty($workId)) {
            echo 'false';
            return;
        }

        require_once 'Dal/Slave/Slave.php';
        $dalSlave = Dal_Slave_Slave::getDefaultInstance();
        $rowTarSlave = $dalSlave->getSlaveById($tarUid);
        //is current user's slave
        if (empty($rowTarSlave) || $uid != $rowTarSlave['master_id']) {
            echo 'false';
            return;
        }

        require_once 'Bll/Slave/Work.php';
        $bllWork = new Bll_Slave_Work();
        $result = $bllWork->work($uid, $tarUid, $workId);
        if (!empty($result)) {
            if (!empty($result['price_up_percent'])) {
                $result['after_work_price'] = number_format($rowTarSlave['price'] + ceil($rowTarSlave['price'] * $result['price_up_percent'] / 100));
                $result['after_work_price_rank'] = $dalSlave->getSlavePriceRank($tarUid);
            }
            //activity
            require_once 'Bll/Slave/Activity.php';
            $activity = Bll_Slave_Activity::getActivity('', $tarUid, false, 24);
            $pic = $this->_staticUrl . '/apps/slave/img/feed/work/' . $workId . '.jpg';

            $result['activity_info'] = $activity;
            $result['activity_pic'] = $pic;

            $result['after_work_assets'] = number_format($dalSlave->getTotalAssetsById($uid));
            $result['after_work_assets_rank'] = $dalSlave->getTotalRankById($uid);
        }

        echo !empty($result) ? Zend_Json::encode($result) : 'false';
    }

    /**
     * get price all rank
     *
     */
    public function listpricerankallAction()
    {
        $uid = $this->_user->getId();
        $rankStart = (int)$this->_request->getParam('rankStart', 1);
        $fetchSize = (int)$this->_request->getParam('fetchSize', 10);

        require_once 'Bll/User.php';
        require_once 'Dal/Slave/Slave.php';
        $dalSlave = Dal_Slave_Slave::getDefaultInstance();
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

        $response = array('info' => array_reverse($listPriceAll), 'ret_count' => count($listPriceAll));
        $response = Zend_Json::encode($response);

        echo $response;
    }

    /******************************************************/
    /*
	 * get slave shop firends
	 */
    public function getslaveshopAction()
    {
        $uid = $this->_user->getId();

        $pageIndex = (int)$this->_request->getParam('pageIndex', 1);
        $pageSize = (int)$this->_request->getParam('pageSize', 10);
        $sort = (int)$this->_request->getParam('sort', 0);
        $keyWord = (int)$this->_request->getParam('keyWord', 0);

        require_once 'Dal/Slave/Slave.php';
        $dalSlave = Dal_Slave_Slave::getDefaultInstance();

        require_once 'Dal/Slave/Work.php';
        $dalWork = Dal_Slave_Work::getDefaultInstance();

        $lstSlaveShop = array();
        $count = 0;
        if (1 == $keyWord) {
            $lstSlaveShop = $dalSlave->listSlaveShopMixiUser($pageIndex, $pageSize, $sort);
            $count = $dalSlave->getMixiuserCount();
        }
        else {
            //$fidsStr = Bll_Friend::getFriendIds($uid);
            //$fidsArr = explode(',', $fidsStr);
            $lstSlaveShop = $dalSlave->listSlaveShopFirends($uid, $pageIndex, $pageSize, $sort);
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
            $response = array('info' => $lstSlaveShop, 'count' => $count);
            $response = Zend_Json::encode($response);
            echo $response;
        }
        else {
            echo 'false';
            return;
        }
    }

    /*
	 * get slave list info
	 */
    public function getslavelstAction()
    {
        $uid = $this->_user->getId();

        require_once 'Dal/Slave/Slave.php';
        $dalSlave = Dal_Slave_Slave::getDefaultInstance();

        $slaveLstInfo = $dalSlave->listSlaveByUid($uid);

        foreach ($slaveLstInfo as $key => $value) {
            $slaveLstInfo[$key]['price_rank'] = $dalSlave->getSlavePriceRank($value['uid']);
        }

        require_once 'Bll/User.php';
        Bll_User::appendPeople($slaveLstInfo, 'uid');
        $response = array('info' => $slaveLstInfo);
        $response = Zend_Json::encode($response);
        echo $response;
    }

    /**
     * gift fav list
     */
    public function getgiftfavAction()
    {
        $uid = $this->_user->getId();

        $pageIndex = (int)$this->_request->getParam('pageIndex', 1);
        $pageSize = (int)$this->_request->getParam('pageSize', 10);
        $sort = (int)$this->_request->getParam('sort');

        require_once 'Dal/Slave/Gift.php';
        $dalGift = Dal_Slave_Gift::getDefaultInstance();
        $lstGiftInfo = $dalGift->listGiftFavByid($uid, $pageIndex, $pageSize, $sort);

        if (!empty($lstGiftInfo) && count($lstGiftInfo) > 0) {
            foreach ($lstGiftInfo as $key => $value) {
                require_once 'Bll/Slave/RakutenApi.php';
                $imgArr = Bll_Slave_RakutenApi::getImgByCode($value['gid']);

				$giftPic = $imgArr['gift_small_pic'];
		        if (empty($giftPic) || $giftPic == null) {
		           $giftPic = $this->_staticUrl . '/apps/slave/img/dummy/pic_n_s.png';
		        }
                $lstGiftInfo[$key]['gift_small_pic'] = $giftPic;
                $lstGiftInfo[$key]['gift_format_price'] = number_format($lstGiftInfo[$key]['gift_price']);
            }
        }
        $favCount = $dalGift->getGiftFavCount($uid);
        $response = array('info' => $lstGiftInfo, 'count' => $favCount);
        $response = Zend_Json::encode($response);
        echo $response;
    }

    /**
     * get Popularity Gift
     */
    public function lstpopularitygiftAction()
    {
    	$sort = (int)$this->_request->getParam('sort', 1);
		require_once 'Dal/Slave/Gift.php';
		$dalGift = Dal_Slave_Gift::getDefaultInstance ();
		$lstPopularityGift = $dalGift->lstPopularGift ($sort);

		foreach ($lstPopularityGift as $key => $value) {
			$imgArr = Bll_Slave_RakutenApi::getImgByCode ($value ['gid']);
			$giftPic = $imgArr['gift_small_pic'];
	        if (empty($giftPic) || $giftPic == null) {
	           $giftPic = $this->_staticUrl . '/apps/slave/img/dummy/pic_n_s.png';
	        }
			$lstPopularityGift[$key]['gift_small_pic'] = $giftPic;

			$lstPopularityGift[$key]['gift_format_price'] = number_format($lstPopularityGift[$key]['gift_price']);
		}
		$response = array('info' => $lstPopularityGift);
        $response = Zend_Json::encode($response);
        echo $response;
    }

    /**
     * get gift search result
     *
     */
    public function getgiftsearchAction()
    {
        $uid = $this->_user->getId();
        $pageIndex = (int)$this->_request->getParam('pageIndex', 1);
        $pageSize = (int)$this->_request->getParam('pageSize', 10);
        $sort = $this->_request->getParam('sort');

        if (1 == $sort) {
            $sort = '+itemPrice';
        }
        else if (2 == $sort) {
            $sort = '-itemPrice';
        }
        else {
            $sort = 'standard';
        }

        $keyWord = $this->_request->getParam('keyWord');

        require_once 'Bll/Slave/RakutenApi.php';
        $giftshoplstInfo = Bll_Slave_RakutenApi::listItems($keyWord, $sort, $pageIndex, $pageSize);
        //$info = array_splice($giftshoplstInfo['info'] , 0 , -1);
        $info = $giftshoplstInfo['info'];
        array_pop($info);
        $response = array('info' => $info, 'count' => $giftshoplstInfo['count']);

        $response = Zend_Json::encode($response);
        echo $response;
    }

    /**
     * buy gift
     */
    public function buygiftAction()
    {
        $uid = $this->_user->getId();
        $gid = $this->_request->getParam('gid');
        $price = $this->_request->getParam('price');

        if (empty($gid)) {
            echo 'false';
            return;
        }
        require_once 'Bll/Slave/Gift.php';
        $bllGift = new Bll_Slave_Gift();

        require_once 'Dal/Slave/Gift.php';
        $dalGift = Dal_Slave_Gift::getDefaultInstance();
        $aryGift = $dalGift->getNbGift($gid);
        $info = array();
        if (empty($aryGift)) {
            require_once 'Bll/Slave/RakutenApi.php';
            $giftInfo = Bll_Slave_RakutenApi::getItemByCode($gid);
            $info['gid'] = $gid;
            $info['name'] = $giftInfo['gift_name'];
            $info['caption'] = $giftInfo['gift_caption'];
            $info['price'] = $giftInfo['gift_price'];
            $info['url'] = $giftInfo['gift_url'];
            $info['create_time'] = time();
            $bllGift->addNbGift($info, $gid);
        }

        $data = array('uid' => $uid, 'gid' => $gid, 'isbuy' => 1, 'create_time' => time());
        $result = $bllGift->addGift($data, $price);

        if ($result) {
            require_once 'Bll/Slave/Activity.php';
            $lstActivity = array();
            $pic = $this->_staticUrl . '/apps/slave/img/feed/action/7.jpg';
            $activity = Bll_Slave_Activity::getActivity('', $sellUid, false, 22);
            $lstActivity[0] = array('info' => $activity, 'pic' => $pic);
        }

        $response = $result ? $lstActivity : 'false';
        $response = Zend_Json::encode($response);
        echo $response;
    }

    /**
     * sell gift
     */
    public function sellgiftAction()
    {
        $uid = $this->_user->getId();
        $keyId = $this->_request->getParam('keyId');

        if (empty($keyId)) {
            echo 'false';
            return;
        }

        require_once 'Bll/Slave/Gift.php';
        $bllGift = new Bll_Slave_Gift();

        $result = $bllGift->sellGift($keyId, $uid);

        if ($result == 1) {
            require_once 'Dal/Slave/Gift.php';
            $dalGift = Dal_Slave_Gift::getDefaultInstance();
            $total = $dalGift->getTotalById($uid);

            require_once 'Dal/Slave/Slave.php';
            $dalSlave = Dal_Slave_Slave::getDefaultInstance();
            $total_Rank = $dalSlave->getTotalRankById($uid);
            $total = number_format($total);

            $info = array('uid' => $uid, 'total' => $total, 'total_Rank' => $total_Rank);
            require_once 'Bll/User.php';
            $ponse = Bll_User::appendPerson($info, 'uid');

            require_once 'Bll/Slave/Activity.php';
            $activity = Bll_Slave_Activity::getActivity('', $friendId, false, 23);
            $pic = $this->_staticUrl . '/apps/slave/img/feed/action/7.jpg';
            $lstActivity[0] = array('info' => $activity, 'pic' => $pic);

            $response = array('info' => $info, 'state' => 'true', 'activity' =>$lstActivity);
            $response = Zend_Json::encode($response);
            echo $response;
        }
        else {
            $response = array('state' => 'false');
            $response = Zend_Json::encode($response);
            echo $response;
        }
    }

    /**
     * buy salve
     */
    public function buyslaveAction()
    {
        //master's id
        $uid = $this->_user->getId();
        //buy slave's id
        $buySlaveId = $this->_request->getParam('buySlaveId');
        //sell slave's id
        $sellSlaveId = $this->_request->getParam('sellSlaveId');

        if (empty($buySlaveId)) {
            echo 'false';
            return;
        }

        if ($uid == $buySlaveId) {
            echo 'false';
            return;
        }

        require_once 'Dal/Slave/Slave.php';
        $dalSlave = Dal_Slave_Slave::getDefaultInstance();
        $buySlaveInfo = $dalSlave->getSlaveById($buySlaveId);

 		//buy slave's masterId
        $buy_MasterId = $dalSlave->getMasterIdById($buySlaveId);

        //whether exists slave_user table
        $isExists = false;
        if (empty($buySlaveInfo)) {
            require_once 'Bll/Slave/Slave.php';
            $bllSlave = new Bll_Slave_Slave();
            $isExists = $bllSlave->newSlaveUser($buySlaveId, $isActive = false);
        }

        //buy slave whether succeed
        $result = false;
        require_once 'Bll/Slave/Slave.php';
        $bllSlave = new Bll_Slave_Slave();
        $result = $bllSlave->buySlave($uid, $buySlaveId, $sellSlaveId);
        if ($result) {
        	$lstActivity = array();

            require_once 'Bll/Slave/Activity.php';
            $activity = Bll_Slave_Activity::getActivity('', $buySlaveId, false, 2);
            //$activitySlave = Bll_Slave_Activity::getActivity($uid, $buySlaveId, false, 11);
            $pic = $this->_staticUrl . '/apps/slave/img/feed/action/2.jpg';
            $lstActivity[0] = array('info' => $activity, 'pic' => $pic);
            //$lstActivity[1] = array('id' => $buySlaveId, 'info' => $activitySlave, 'pic' => $pic);

            /*if (!empty($buy_MasterId)) {
            	 $lstActivity[2] = array('id' => $buy_MasterId, 'info' => $activitySlave, 'pic' =>$pic);
            }*/
        }

        $response = $result ? $lstActivity : 'false';
        $response = Zend_Json::encode($response);
        echo $response;
    }

    /**
     * get gift list
     */
    public function listgiftAction()
    {
        $uid = $this->_user->getId();
        require_once 'Dal/Slave/Gift.php';
        $dalGift = Dal_Slave_Gift::getDefaultInstance();

        $giftList = $dalGift->listGiftById($uid);

        if (!empty($giftList) && count($giftList) > 0) {
            foreach ($giftList as $key => $value) {
                require_once 'Bll/Slave/RakutenApi.php';
                $imgArr = Bll_Slave_RakutenApi::getImgByCode($value['gid']);
                $giftList[$key]['gift_small_pic'] = $imgArr['gift_small_pic'];
                $giftList[$key]['gift_big_pic'] = $imgArr['gift_big_pic'];
                $giftList[$key]['gift_format_price'] = number_format($giftList[$key]['gift_price']);
            }
        }
        $response = array('info' => $giftList);
        $response = Zend_Json::encode($response);
        echo $response;
    }

    /**
     *slave firend list
     */
    public function listfirendAction()
    {
        $uid = $this->_user->getId();

        require_once 'Dal/Slave/Friend.php';
        $dalFriend = Dal_Slave_Friend::getDefaultInstance();

        $friendInfo = $dalFriend->listSlaveFriend($uid);
        foreach ($friendInfo as $key => $value) {
            require_once 'Dal/Slave/Slave.php';
            $dalSlave = Dal_Slave_Slave::getDefaultInstance();
            $friendInfo[$key] = $dalSlave->getSlaveById($value['fid']);
        }
        require_once 'Bll/User.php';
        Bll_User::appendPeople($friendInfo, 'uid');

        $response = array('info' => $friendInfo);
        $response = Zend_Json::encode($response);
        echo $response;
    }

    /**
     * present gift
     */
    public function presentgiftAction()
    {
        $uid = $this->_user->getId();
        $keyGid = $this->_request->getParam('keyGid');
        $friendId = $this->_request->getParam('friendId');

        if (empty($keyGid) || empty($friendId)) {
            echo 'false';
            return;
        }

        require_once 'Bll/Slave/Gift.php';
        $bllGift = new Bll_Slave_Gift();

        $result = $bllGift->presentGift($uid, $friendId, $keyGid);

        $finishInfo = array();
        if ($result == 1) {

            require_once 'Dal/Slave/Gift.php';
            $dalGift = Dal_Slave_Gift::getDefaultInstance();
            $aryGift = $dalGift->getGidByid($keyGid);
            require_once 'Bll/Slave/RakutenApi.php';
            $imgArr = Bll_Slave_RakutenApi::getImgByCode($aryGift['gid']);

            $giftPic = $imgArr['gift_small_pic'];
            if (empty($giftPic) || $giftPic == null) {
                $giftPic = $this->_staticUrl . '/apps/slave/img/dummy/pic_n_s.png';
            }

            require_once 'Dal/Slave/Slave.php';
            require_once 'Bll/User.php';

            $dalSlave = Dal_Slave_Slave::getDefaultInstance();
            $friendInfo = $dalSlave->getSlaveById($friendId);

            $finishInfo['gift_small_pic'] = $giftPic;
            $finishInfo['gid'] = $aryGift['gid'];
            $finishInfo['gift_name'] = $aryGift['gift_name'];
            $finishInfo['gift_format_price'] = number_format($aryGift['gift_price']);

            $finishInfo['uid'] = $friendId;
            $finishInfo['format_price'] = number_format($friendInfo['price']);

            Bll_User::appendPerson($finishInfo, 'uid');

            require_once 'Bll/Slave/Activity.php';
            $activity = Bll_Slave_Activity::getActivity('', $friendId, false, 21);

            $pic = $this->_staticUrl . '/apps/slave/img/feed/action/7.jpg';
            $lstActivity[0] = array('id' => $friendId, 'info' => $activity, 'pic' => $pic);

            $response = array('info' => $finishInfo, 'state' => 'true', 'activity' =>$lstActivity);
            $response = Zend_Json::encode($response);
            echo $response;
        }
        else {
            $response = array('state' => 'false');
            $response = Zend_Json::encode($response);
            echo $response;
        }
    }

    /**
     * tease action
     */
    public function teaseAction()
    {
        $uid = $this->_user->getId();
        $slaveId = $this->_request->getParam('slaveId');
        $teaseId = $this->_request->getParam('teaseId');
        $friendId = $this->_request->getParam('frientId');

        if (empty($slaveId) || empty($teaseId) || empty($friendId)) {
            echo 'false';
            return;
        }

        $info = array();
        $info['tid'] = $teaseId;
        $info['master_uid'] = $uid;
        $info['actor_uid'] = $slaveId;
        $info['target_uid'] = $friendId;
        $info['create_time'] = time();

        $pic_small = $this->_request->getParam('pic_small');

        //(tease id = 38) is custom tease
        if ($teaseId == 38) {
            $custom_tease = $this->_request->getParam('custom_tease');

            $info['custom_tease'] = $custom_tease;
            $info['iscustom'] = 1;

            if (!empty($pic_small) && $pic_small != 'http://') {
                $info['custom_pic_small'] = $pic_small;
                $info['custom_pic_big'] = $pic_small;
            }
            else {
                return;
            }
        }
        else {
            if (empty($pic_small)) {
                $imgName = strstr($pic_small, 'apps');
                $fileName_s = Zend_Registry::get('photoBasePath') . '/' . $imgName;
                $fileName_b = str_replace('_s', '_b', $fileName_s);
                unlink($fileName_s);
                unlink($fileName_b);
            }
        }

        require_once 'Bll/Slave/Tease.php';
        $bllTease = new Bll_Slave_Tease();
        $result = $bllTease->addTease($info,$uid);

        //$result is 1 (tease success)
        if ($result == 1) {
            require_once 'Dal/Slave/Slave.php';
            $dalSlave = Dal_Slave_Slave::getDefaultInstance();

            require_once 'Bll/User.php';
            $slaveInfo = $dalSlave->getSlaveById($slaveId);

            $price_rank = $dalSlave->getSlavePriceRank($slaveId);
            $slaveInfo['price_rank'] = $price_rank;
            $slaveInfo['format_price'] = number_format($slaveInfo['price']);
            Bll_User::appendPerson($slaveInfo, 'uid');

            $lstActivity = null;

            require_once 'Bll/Slave/Activity.php';
            //$activityFriend = Bll_Slave_Activity::getActivity('', $friendId, false, 15);
            //$activityBuySlave = Bll_Slave_Activity::getActivity($slaveId, '', false, 16);
            $activity = Bll_Slave_Activity::getActivity($slaveId, $friendId, false, 26);

            if ($teaseId != 38 ) {
            	$pic = $this->_staticUrl . '/apps/slave/img/feed/pork/' . $teaseId . '.jpg';
            }
            else {
                $pic = $pic_small;
            }

            $idStr = '';
            require_once 'Bll/Friend.php';
            if (Bll_Friend::isFriend($uid,$slaveId)) {
            	$idStr = $slaveId;
            }

            if (Bll_Friend::isFriend($uid,$friendId)) {
                $idStr .= ',' . $friendId;
            }
            if (!empty($idStr)) {
            	$lstActivity = array('id' => $idStr, 'info' => $activity, 'pic' => $pic);
            }

            $response = array('info' => $slaveInfo, 'state' => 'true', 'activity' => $lstActivity);
            $response = Zend_Json::encode($response);
            echo $response;
        } //$result is 2 (have 禁止語)
        else if ($result == 2) {
            $response = array('state' => 'false');
            $response = Zend_Json::encode($response);
            echo $response;
        } //tease break
        else {
            echo 0;
        }
    }

    /**
     * get tease confrim's data
     */
    public function teaseconfrimAction()
    {
        $slaveId = $this->_request->getParam('slaveId');
        $teaseId = (int)$this->_request->getParam('teaseId');
        $friendId = $this->_request->getParam('frientId');

        require_once 'Dal/Slave/Tease.php';
        $dalTease = Dal_Slave_Tease::getDefaultInstance();
        require_once 'Dal/Slave/Slave.php';
        $dalSlave = Dal_Slave_Slave::getDefaultInstance();

        require_once 'Bll/User.php';

        //$friendId not exists slave_user table
        $friendInfo = $dalSlave->getMixiUserById($friendId);
        $slaveInfo = $dalSlave->getSlaveById($slaveId);
        $teaseInfo = $dalTease->getTeaseById($teaseId);
        Bll_User::appendPerson($slaveInfo, 'uid');

        $info['friendInfo'] = $friendInfo;
        $info['slaveInfo'] = $slaveInfo;
        $info['teaseInfo'] = $teaseInfo;

        $response = array('info' => $info);
        $response = Zend_Json::encode($response);
        echo $response;
    }

    /**
     * upload photo
     */
    public function upphotoAction()
    {
        $uid = $this->_user->getId();
        require_once 'Bll/Slave/Tease.php';
        $bllTease = new Bll_Slave_Tease();
        $custom_pic = $bllTease->upPhoto('upPhoto', $uid);
        if ($custom_pic != null) {
        	$custom = array('pic' => $custom_pic);
            $response = array('info' => $custom);
            $response = Zend_Json::encode($response);
            echo '<script> parent.setCustomPic(' . $response . ');</script>';
        }
    }

    /**
     * get price friend rank
     *
     */
    public function listpricerankfriendAction()
    {
        $uid = $this->_user->getId();
        $rankStart = (int)$this->_request->getParam('rankStart', 1);
        $fetchSize = (int)$this->_request->getParam('fetchSize', 10);

        require_once 'Bll/User.php';
        require_once 'Dal/Slave/Slave.php';
        $dalSlave = Dal_Slave_Slave::getDefaultInstance();
        $listPriceFriend = $dalSlave->listPriceRankFriend($uid, $rankStart, $fetchSize);

        foreach ($listPriceFriend as $key => $pFdata) {
            $listPriceFriend[$key]['rankNo'] = (int)($rankStart + $key);
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

        $response = array('info' => array_reverse($listPriceFriend), 'ret_count' => count($listPriceFriend));
        $response = Zend_Json::encode($response);
        echo $response;
    }

    /**
     * get total friend rank
     *
     */
    public function listtotalrankfriendAction()
    {
        $uid = $this->_user->getId();
        $rankStart = (int)$this->_request->getParam('rankStart', 1);
        $fetchSize = (int)$this->_request->getParam('fetchSize', 10);

        require_once 'Bll/User.php';
        require_once 'Dal/Slave/Slave.php';
        $dalSlave = Dal_Slave_Slave::getDefaultInstance();
        $listTotalFriend = $dalSlave->listTotalRankFriend($uid, $rankStart, $fetchSize);

        foreach ($listTotalFriend as $key => $tFdata) {
            $listTotalFriend[$key]['rankNo'] = (int)($rankStart + $key);
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

        $response = array('info' => array_reverse($listTotalFriend), 'ret_count' => count($listTotalFriend));
        $response = Zend_Json::encode($response);
        echo $response;
    }

    /**
     * get total all rank
     *
     */
    public function listtotalrankallAction()
    {
        $uid = $this->_user->getId();
        $rankStart = (int)$this->_request->getParam('rankStart', 1);
        $fetchSize = (int)$this->_request->getParam('fetchSize', 10);

        require_once 'Bll/User.php';
        require_once 'Dal/Slave/Slave.php';
        $dalSlave = Dal_Slave_Slave::getDefaultInstance();
        $listTotalAll = $dalSlave->listTotalRankAll($rankStart, $fetchSize);

        foreach ($listTotalAll as $key => $tdata) {
            $listTotalAll[$key]['rankNo'] = (int)($rankStart + $key);
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
        $response = array('info' => array_reverse($listTotalAll), 'ret_count' => count($listTotalAll));
        $response = Zend_Json::encode($response);

        echo $response;
    }


    /**
     * validate image url and write image
     */
    public function validateurlAction()
    {
        $imgUrl = $this->_request->getParam('upUrl');
        $photoUrl = Zend_Registry::get('photo');
        $uid = $this->_user->getId();

        require_once 'Bll/Slave/Tease.php';
        $bllTease = new Bll_Slave_Tease();
        $newImageUrl = $bllTease->writeImage($uid, $imgUrl, $photoUrl);
        echo $newImageUrl === false ? 'false' : $newImageUrl;
    }
}
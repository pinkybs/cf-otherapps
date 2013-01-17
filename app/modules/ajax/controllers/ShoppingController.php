<?php
/** @see Zend_Json */
require_once 'Zend/Json.php';
/** @see MyLib_Zend_Controller_Action_Ajax */
require_once 'MyLib/Zend/Controller/Action/Ajax.php';

/**
 * shopping Ajax Controllers
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/06/24   zhangxin
 */
class Ajax_ShoppingController extends MyLib_Zend_Controller_Action_Ajax
{
    private $_subStrSize = 8;

    private $_staticUrl;
    private $_hostUrl;

    protected $_appName = 'shopping';
    protected $_basicDepart = 100000;

	public function postInit()
    {
    	$this->_staticUrl = Zend_Registry::get('static');
    	$this->_hostUrl = Zend_Registry::get('host');
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

        require_once 'Dal/Shopping/User.php';
        $dalUser = Dal_Shopping_User::getDefaultInstance();
        $aryInfo = $dalUser->listAppUids($posStart, $fetchSize);
        Bll_User::appendPeople($aryInfo, 'uid');

        $response = array('info' => $aryInfo, 'count' => $count);
        $response = Zend_Json::encode($response);

        echo $response;
    }

 	/**
     * get pop item list
     *
     */
    public function listpopitemAction()
    {
        $uid = $this->_user->getId();
        $pageIndex = (int)$this->_request->getParam('pageIndex', 1);
        $pageSize = (int)$this->_request->getParam('pageSize', 10);
        //$cid = (int)$this->_request->getParam('cid');

        //challenge info
        require_once 'Dal/Shopping/Shopping.php';
        $dalShopping = Dal_Shopping_Shopping::getDefaultInstance();
        $rowShopping = $dalShopping->getShoppingById($uid);
        if (empty($rowShopping) || empty($rowShopping['challenge_id'])) {
            echo 'false';
            return;
        }
        $cid = $rowShopping['challenge_id'];

        //popitem info
        require_once 'Dal/Shopping/Challenge.php';
        $dalChallenge = Dal_Shopping_Challenge::getDefaultInstance();
        $lstPopItem = $dalChallenge->listPopularItem($pageIndex, $pageSize);
        $count = $dalChallenge->getPopularItemCount();
        $cartCount = $dalChallenge->getChallengeCartCount($cid);
        require_once 'Dal/Shopping/WishItem.php';
        $dalItem = Dal_Shopping_WishItem::getDefaultInstance();
        foreach ($lstPopItem as $key=>$idata) {
            $rowItem = $dalItem->getNbItem($idata['iid']);
            $lstPopItem[$key]['name'] = empty($rowItem) ? '' : $rowItem['name'];
            $lstPopItem[$key]['pic_small'] =  empty($rowItem) ? '' : $rowItem['pic_small'];
            $lstPopItem[$key]['real_price'] =  empty($rowItem) ? '' : $rowItem['price'];
            $lstPopItem[$key]['is_in_cart'] = $dalChallenge->isItemInCart($cid, $idata['iid']) ? '1' : '0';
            $lstPopItem[$key]['guess_price'] = 0;
            if ($lstPopItem[$key]['is_in_cart'] == '1') {
                $cartInfo = $dalChallenge->getCartInfoById($cid, $idata['iid']);
            	$lstPopItem[$key]['guess_price'] = number_format($cartInfo['guess_price']);
            }
        }

        $response = array('info' => $lstPopItem, 'count' => $count, 'cartCount' => $cartCount);
        $response = Zend_Json::encode($response);
        echo $response;
    }

	/**
     * get pop item list
     *
     */
    public function listgenreitemAction()
    {
        $uid = $this->_user->getId();
        $pageIndex = (int)$this->_request->getParam('pageIndex', 1);
        $pageSize = (int)$this->_request->getParam('pageSize', 10);
        $gid = $this->_request->getParam('gid');

        //challenge info
        require_once 'Dal/Shopping/Shopping.php';
        $dalShopping = Dal_Shopping_Shopping::getDefaultInstance();
        $rowShopping = $dalShopping->getShoppingById($uid);
        if (empty($rowShopping) || empty($rowShopping['challenge_id'])) {
            echo 'false';
            return;
        }
        $cid = $rowShopping['challenge_id'];

        require_once 'Dal/Shopping/Challenge.php';
        $dalChallenge = Dal_Shopping_Challenge::getDefaultInstance();
        require_once 'Bll/Shopping/RakutenApi.php';
        $aryGenreItem = Bll_Shopping_RakutenApi::listItemsByGenre($gid, 'standard', $pageIndex, $pageSize);
        if ($aryGenreItem && !empty($aryGenreItem['info'])) {
            $lstGenreItem = $aryGenreItem['info'];
            array_pop($lstGenreItem);
            require_once 'Dal/Shopping/WishItem.php';
            $dalItem = Dal_Shopping_WishItem::getDefaultInstance();
            foreach ($lstGenreItem as $key=>$idata) {
                //if item is in db
                $rowItem = $dalItem->getNbItemByCode($idata['item_code']);
                $lstGenreItem[$key]['is_in_cart'] = '0';
                $lstGenreItem[$key]['iid'] = '0';
                $lstGenreItem[$key]['item_code'] = urlencode($idata['item_code']);
                $lstGenreItem[$key]['real_price'] =  $idata['item_price'];
                //if item is in cart
                if (!empty($rowItem)) {
                    $lstGenreItem[$key]['iid'] = $rowItem['iid'];
                    $lstGenreItem[$key]['is_in_cart'] = $dalChallenge->isItemInCart($cid, $rowItem['iid']) ? '1' : '0';
                    $lstGenreItem[$key]['real_price'] =  $rowItem['price'];
                    $cartInfo = $dalChallenge->getCartInfoById($cid, $rowItem['iid']);
                    $lstGenreItem[$key]['guess_price'] = number_format($cartInfo['guess_price']);
                }
            }
        }

        $cartCount = $dalChallenge->getChallengeCartCount($cid);
        $response = array('info' => $lstGenreItem, 'count' => $aryGenreItem['count'], 'cartCount' => $cartCount);
        $response = Zend_Json::encode($response);
        echo $response;
    }

	/**
     * flash parameter request get rand depart
     *
     */
    public function randdepartAction()
    {
        $uid = $this->_user->getId();

        $randDepart = rand(1, 4);
        $response = array();
        $response[] = array('section' => $randDepart);
        $response[] = array('nextPageURL' => $this->_hostUrl . '/shopping/depart');

        //start shopping game
        require_once 'Bll/Shopping/Challenge.php';
        $bllChallenge = new Bll_Shopping_Challenge();
        if (1==$randDepart) {
            $randDepart = $this->_basicDepart;
        }
        else if (2 == $randDepart) {
            $randDepart = $this->_basicDepart * 5;
        }
        else if (3 == $randDepart) {
            $randDepart = $this->_basicDepart * 10;
        }
        else if (4 == $randDepart) {
            $randDepart = $this->_basicDepart * 50;
        }
        $bllChallenge->newChallenge($uid, $randDepart);

        $response = 'openingData=' . Zend_Json::encode($response);
        echo $response;
    }

	/**
     * flash parameter request get timer
     *
     */
    public function gametimerAction()
    {
        $uid = $this->_user->getId();

        require_once 'Dal/Shopping/Shopping.php';
        $dalShopping = Dal_Shopping_Shopping::getDefaultInstance();
        $rowShopping = $dalShopping->getShoppingById($uid);
        if (empty($rowShopping) || empty($rowShopping['challenge_id'])) {
            echo 'false';
            return;
        }
        $cid = $rowShopping['challenge_id'];

        require_once 'Dal/Shopping/Challenge.php';
        $dalChallenge = Dal_Shopping_Challenge::getDefaultInstance();
        $rowChallenge = $dalChallenge->getChallengeByPk($cid, $uid);
        if (empty($rowChallenge) || 1 == $rowChallenge['is_ended']) {
            echo 'false';
            return;
        }

        $nowTime = time();
        $gameTime = $nowTime - $rowChallenge['start_time'];
        $remainTime = $rowChallenge['game_seconds'] - $gameTime + 2;

        $guessPrice = $dalChallenge->getChallengeCartPrice($cid); //get guess price all
        $response = array();
        $response[] = array('minutes' => floor($remainTime/60), 'seconds' => ($remainTime%60));
        $response[] = array('price' => $guessPrice);

        $response = 'timerData=' . Zend_Json::encode($response);
        echo $response;
    }

	/**
     * flash parameter request show geme result
     *
     */
    public function gameresultAction()
    {
        $uid = $this->_user->getId();
        require_once 'Dal/Shopping/Shopping.php';
        $dalShopping = Dal_Shopping_Shopping::getDefaultInstance();
        $rowShopping = $dalShopping->getShoppingById($uid);
        if (empty($rowShopping) || empty($rowShopping['challenge_id'])) {
            echo 'false';
            return;
        }
        $cid = $rowShopping['challenge_id'];

        require_once 'Dal/Shopping/Challenge.php';
        $dalChallenge = Dal_Shopping_Challenge::getDefaultInstance();
        $rowChallenge = $dalChallenge->getChallengeByPk($cid, $uid);
        if (empty($rowChallenge) || 1 == $rowChallenge['is_ended']) {
            echo 'false';
            return;
        }

        //result info
        $targetPrice = $rowChallenge['target_price']; //target price
        $guessPrice = $dalChallenge->getChallengeCartPrice($cid); //get guess price all
        $realPrice = $dalChallenge->getChallengeCartRealPrice($cid); //get real price all

        //end shopping game
        require_once 'Bll/Shopping/Challenge.php';
        $bllChallenge = new Bll_Shopping_Challenge();
        $bllChallenge->endChallenge($uid, $cid, $guessPrice, $realPrice);

        //depart section
        if ($this->_basicDepart == $targetPrice) {
            $section = 0;
            $departCol = 'price_depart10';
        }
        else if ($this->_basicDepart * 5 == $targetPrice) {
            $section = 1;
            $departCol = 'price_depart50';
        }
        else if ($this->_basicDepart * 10 == $targetPrice) {
            $section = 2;
            $departCol = 'price_depart100';
        }
        else if ($this->_basicDepart * 50 == $targetPrice) {
            $section = 3;
            $departCol = 'price_depart500';
        }

        //rank winner info
        require_once 'Bll/User.php';
        $lstRank = $dalShopping->lstShoppingAllRank($departCol, $targetPrice, 1, 4);
        if (!empty($lstRank) && count($lstRank) > 0) {
            Bll_User::appendPeople($lstRank, 'uid');
        }
        $aryUser = array();
        $isWinner = false;
        for ($i=0; $i<4; $i++) {
            $aryUser[$i] = array();
            if ($i<count($lstRank)) {
                $aryUser[$i]['name'] = $lstRank[$i]['displayName'];
                $aryUser[$i]['pic'] = $lstRank[$i]['miniThumbnailUrl'];
                if ($uid == $lstRank[$i]['uid'] && $i<3) {
                    $isWinner = true;
                }
            }
            else {
                $aryUser[$i]['name'] = '???';
                $aryUser[$i]['pic'] = $this->_staticUrl . '/apps/shopping/img/content/thum_invite.png';
            }
        }

        if (!$isWinner) {
            $aryMeInfo = array('uid' => $uid);
            Bll_User::appendPerson($aryMeInfo, 'uid');
            $aryUser[3]['name'] = $aryMeInfo['displayName'];
            $aryUser[3]['pic'] = $aryMeInfo['miniThumbnailUrl'];
        }

        $priceVal = '2';
        if ($targetPrice == $realPrice) {
            $priceVal = '0';
        }
        else if (abs($realPrice-$targetPrice) <= ceil($targetPrice*0.03)) {
            $priceVal = '1';
        }
        $response = array();
        $response[] = array('price' => $realPrice, 'priceTarget' => $targetPrice, 'priceExpect' => $guessPrice);
        $response[] = array('section' => $section, 'priceVal' => $priceVal,'rankingVal' => $isWinner ? '0' : '1');
        //$response[] = array('userName1' => '', 'userName2' => '',
        //                    'userName3' => '', 'userName4' => '');
        $response[] = array('userName1' => html_entity_decode($aryUser[0]['name'], ENT_QUOTES, 'UTF-8'),
                            'userName2' => html_entity_decode($aryUser[1]['name'], ENT_QUOTES, 'UTF-8'),
                            'userName3' => html_entity_decode($aryUser[2]['name'], ENT_QUOTES, 'UTF-8'),
                            'userName4' => html_entity_decode($aryUser[3]['name'], ENT_QUOTES, 'UTF-8'));
        $response[] = array('userIconURL1' => urlencode($aryUser[0]['pic']), 'userIconURL2' => urlencode($aryUser[1]['pic']),
                            'userIconURL3' => urlencode($aryUser[2]['pic']), 'userIconURL4' => urlencode($aryUser[3]['pic']));
        $response[] = array('linkURL1' => urlencode($this->_hostUrl . '/shopping/restart?cid=' . $cid),
                            'linkURL2' => 'javascript:void(0);',
                            'linkURL3' => urlencode($this->_hostUrl . '/shopping/home'));

        $response = 'resultData=' . Zend_Json::encode($response);
        echo $response;
    }

	/**
     * stop game
     *
     */
    public function stopgameAction()
    {
        $uid = $this->_user->getId();

        require_once 'Dal/Shopping/Shopping.php';
        $dalShopping = Dal_Shopping_Shopping::getDefaultInstance();
        $rowShopping = $dalShopping->getShoppingById($uid);
        if (empty($rowShopping) || empty($rowShopping['challenge_id'])) {
            echo 'false';
            return;
        }
        $cid = $rowShopping['challenge_id'];

        require_once 'Dal/Shopping/Challenge.php';
        $dalChallenge = Dal_Shopping_Challenge::getDefaultInstance();
        $rowChallenge = $dalChallenge->getChallengeByPk($cid, $uid);
        if (empty($rowChallenge) || 1 == $rowChallenge['is_ended']) {
            echo 'false';
            return;
        }

        //end shopping game
        $guessPrice = $dalChallenge->getChallengeCartPrice($cid); //get guess price all
        $realPrice = $dalChallenge->getChallengeCartRealPrice($cid); //get real price all
        require_once 'Bll/Shopping/Challenge.php';
        $bllChallenge = new Bll_Shopping_Challenge();
        $result = $bllChallenge->endChallenge($uid, $cid, $guessPrice, $realPrice);
        echo $result ? 'true' : 'false';
    }

    /******************************************************/


    public function listinitAction()
    {
    	$uid = $this->_user->getId();
    	$sub_str_size = 8;
        require_once 'Bll/User.php';
        require_once 'Dal/Shopping/Shopping.php';

        //response result
        $result = array();
        $departCol = '';
        $numTarget = 0;
        $pos = (int)$this->_request->getParam('pos', 1);
        $model = $this->_request->getParam('model');
        switch ($pos) {
        	case 1:
        	   $departCol = 'price_depart10';
        	   $numTarget = $this->_basicDepart;
        	   break;
        	case 2:
                $departCol = 'price_depart50';
                $numTarget = $this->_basicDepart * 5;
                break;
            case 3:
                $departCol = 'price_depart100';
                $numTarget = $this->_basicDepart * 10;
                break;

        	default:
        		$departCol = 'price_depart500';
        		$numTarget = $this->_basicDepart * 50;
        	    break;
        }

        $dalShopp = Dal_Shopping_Shopping::getDefaultInstance();
        $listLeader = null;
	    if ($model == 'all') {
            $listLeader = $dalShopp->lstShoppingAllRank($departCol, $numTarget, 1, 2);
	    }
	    else {
            $listLeader = $dalShopp->lstShoppingFriendRank($uid, $departCol, $numTarget, 1, 2);
	    }

        //price rank leader
        if (count($listLeader) != 0 && !empty($listLeader)) {
            foreach ($listLeader as $key => $pdata) {
                $listLeader[$key]['rankNo'] = (int)($key + 1);

                $dbPre = $pdata['diff2'];
                $strPre = '';
                if ($dbPre > 0) {
                    $strPre = '';
                }
                else if ($dbPre < 0) {
                    $strPre = '-';
                }

                $formatPrice = $strPre . abs($dbPre) . '円';
                if (abs($dbPre) >= 10000) {
                    $formatPrice = $strPre . floor(abs($dbPre) / 10000) . '万円';
                }

                $listLeader[$key]['format_diff'] = $formatPrice;

                $rowInfo = Bll_User::getPerson($pdata['uid']);
                $tmpName = $rowInfo->getDisplayName();
                $tmpName = html_entity_decode($tmpName, ENT_QUOTES, 'UTF-8');
                if (mb_strlen($tmpName, 'UTF-8') > $sub_str_size) {
                    $tmpName = mb_substr($tmpName, 0, $sub_str_size, 'UTF-8') . '…';
                }
                $tmpName = htmlentities($tmpName, ENT_QUOTES, 'UTF-8');

                $listLeader[$key]['name'] = $tmpName;
                $listLeader[$key]['pic'] = $rowInfo->getThumbnailUrl();
            }
        }
        for ($i = count($listLeader); $i < 2; $i++) {
            $listLeader[$i]['uid'] = 0;
            $listLeader[$i]['rankNo'] = (int)($i + 1);
        }

        //mixi all 1-2
        $result[0] = array_reverse($listLeader);

         //get my position
        $fidsStr = Bll_Friend::getFriendIds($uid);
        $fids = explode(',', $fidsStr);

        $rankStart = 3;
        $fetchSize = 5;

        //get my position
        $rankMine = 0;
        $count = 0;
        if ($model == 'all') {
            $count = $dalShopp->getShoppingAllCount($departCol);
            $rankMine = $dalShopp->getShoppingRankByDepart($uid, $departCol, $numTarget);
        }
        else {
            $count = $dalShopp->getFriendCountById($uid, $departCol);
            $rankMine = $dalShopp->getShoppingFriendRankByDepart($uid, $departCol, $numTarget, $fids);
        }

        //in center
        if ($rankMine > 7 && ($rankMine + $fetchSize) <= $count) {
            $rankStart = $rankMine - 2;
        }
        //last six
        else if (($rankMine + $fetchSize) > $count && ($count-2) > $fetchSize) {
            $rankStart = $count - $fetchSize + 1;
        }
        $rankEnd = ($rankStart + $fetchSize - 1) > $count ? $count : ($rankStart + $fetchSize - 1);

        $listRank = null;
        if ($model == 'all') {
            $listRank = $dalShopp->lstShoppingAllRank($departCol, $numTarget, $rankStart, $fetchSize);
        }
        else {
            $listRank = $dalShopp->lstShoppingFriendRank($uid, $departCol, $numTarget, $rankStart, $fetchSize);
        }
        if (count($listRank) != 0 && !empty($listRank)) {
            foreach ($listRank as $key => $pdata) {
                $listRank[$key]['rankNo'] = (int)($rankStart + $key);

                $dbPre = $pdata['diff2'];
                $strPre = '';
                if ($dbPre > 0) {
                    $strPre = '';
                }
                else if ($dbPre < 0) {
                    $strPre = '-';
                }

                $formatPrice = $strPre . abs($dbPre) . '円';
                if (abs($dbPre) >= 10000) {
                    $formatPrice = $strPre . floor(abs($dbPre) / 10000) . '万円';
                }

                $listRank[$key]['format_diff'] = $formatPrice;
                $rowInfo = Bll_User::getPerson($pdata['uid']);

                $tmpName = $rowInfo->getDisplayName();

                $tmpName = html_entity_decode($tmpName, ENT_QUOTES, 'UTF-8');
                if (mb_strlen($tmpName, 'UTF-8') > 8) {
                    $tmpName = mb_substr($tmpName, 0, 8, 'UTF-8') . '…';
                }
                $tmpName = htmlentities($tmpName, ENT_QUOTES, 'UTF-8');

                $listRank[$key]['name'] = $tmpName;
                $listRank[$key]['pic'] = $rowInfo->getThumbnailUrl();
            }
        }

        if (3 == $rankStart && count($listRank) < 5) {
            for ($i = count($listRank); $i < 5; $i++) {
                $listRank[$i]['uid'] = 0;
                $listRank[$i]['rankNo'] = (int)($rankStart + $i);
            }
        }

        $result[1] = array_reverse($listRank);

        $response = array('info' => $result, 'count' => $count, 'rankprev' => $rankStart, 'ranknext' => $rankEnd);
        $response = Zend_Json::encode($response);

        echo $response;
    }

    public function listrankAction()
    {
        $uid = $this->_user->getId();
        $rankStart = (int)$this->_request->getParam('rankStart', 1);
        $fetchSize = (int)$this->_request->getParam('fetchSize', 10);

        $pos = (int)$this->_request->getParam('pos', 1);
        $model = $this->_request->getParam('model');
        switch ($pos) {
            case 1:
               $departCol = 'price_depart10';
               $numTarget = 100000;
               break;
            case 2:
                $departCol = 'price_depart50';
                $numTarget = 500000;
                break;
            case 3:
                $departCol = 'price_depart100';
                $numTarget = 1000000;
                break;

            default:
                $departCol = 'price_depart500';
                $numTarget = 5000000;
                break;
        }

        require_once 'Bll/User.php';
        require_once 'Dal/Shopping/Shopping.php';
        $dalShopp = Dal_Shopping_Shopping::getDefaultInstance();

        $listRank = null;
        if ($model == 'all') {
            $listRank = $dalShopp->lstShoppingAllRank($departCol, $numTarget, $rankStart, $fetchSize);
        }
        else {
            $listRank = $dalShopp->lstShoppingFriendRank($uid, $departCol, $numTarget, $rankStart, $fetchSize);
        }

        if (count($listRank) > 0  && !empty($listRank)) {
	        foreach ($listRank as $key => $pdata) {
	            $listRank[$key]['rankNo'] = (int)($rankStart + $key);

	            $dbPre = $pdata['diff2'];
                $strPre = '';
                if ($dbPre > 0) {
                    $strPre = '';
                }
                else if ($dbPre < 0) {
                    $strPre = '-';
                }

                $formatPrice = $strPre . abs($dbPre) . '円';
                if (abs($dbPre) >= 10000) {
                    $formatPrice = $strPre . floor(abs($dbPre) / 10000) . '万円';
                }

	            $listRank[$key]['format_diff'] = $formatPrice;
	            $rowInfo = Bll_User::getPerson($pdata['uid']);
	            $tmpName = $rowInfo->getDisplayName();

	            $tmpName = html_entity_decode($tmpName, ENT_QUOTES, 'UTF-8');
	            if (mb_strlen($tmpName, 'UTF-8') > 8) {
	                $tmpName = mb_substr($tmpName, 0, 8, 'UTF-8') . '…';
	            }
	            $tmpName = htmlentities($tmpName, ENT_QUOTES, 'UTF-8');

	            $listRank[$key]['name'] = $tmpName;
	            $listRank[$key]['pic'] = $rowInfo->getThumbnailUrl();
	        }
        }

        $response = array('info' => array_reverse($listRank), 'ret_count' => count($listRank));
        $response = Zend_Json::encode($response);

        echo $response;
    }

    public function listwishitemAction()
    {
    	$uid = $this->_user->getId();
        require_once 'Dal/Shopping/WishItem.php';
        $dalWishItem = Dal_Shopping_WishItem::getDefaultInstance();

        $rankStart = (int)$this->_request->getParam('pageIndex', 1);
        $fetchSize = (int)$this->_request->getParam('pageSize', 10);
        $profileUid = $this->_request->getParam('profileUid');

        $listWishItem = $dalWishItem->listWishItemByUid($profileUid, $rankStart, $fetchSize);
        $itemCount = $dalWishItem->getWishItemByUidCount($profileUid);
        if (!empty($listWishItem) && count($listWishItem) > 0) {
            foreach ($listWishItem as $key => $value) {
                if ($profileUid != $uid) {
                    $iids = $dalWishItem->listIidsById($uid);
                    $model = 'add';
                    foreach ($iids as $iid) {
                        if ($iid['iid'] == $value['iid']) {
                            $model = 'remove';
                            break;
                        }
                    }
                    $listWishItem[$key]['model'] = $model;
                }
                if ($value['pic_small'] == null) {
                    $listWishItem[$key]['pic_small'] = $this->_staticUrl . 'apps/shopping/img/dummy/pic_item.png';
                }
                $listWishItem[$key]['format_price'] = number_format($value['price']);
            }
        }

        $response = array('info' => $listWishItem, 'state' => 'true', 'count' => $itemCount);
        $response = Zend_Json::encode($response);
        echo $response;
    }

    public function removewishitemAction()
    {
        $uid = $this->_user->getId();
        $iid = $this->_request->getParam('iid');

        if (empty($iid)) {
            echo 'false';
            return;
        }

        require_once 'Bll/Shopping/WishItem.php';
        $bllWishItem = new Bll_Shopping_WishItem();

        $result = $bllWishItem->removeWishItem($uid, $iid);
        echo $result ? $result : 'false';
    }

    public function addwishitemAction()
    {
        $uid = $this->_user->getId();
        $iid = $this->_request->getParam('iid');

        if (empty($iid)) {
        	echo 'false';
        	return;
        }

        require_once 'Bll/Shopping/WishItem.php';
        $bllWishItem = new Bll_Shopping_WishItem();

        $result = $bllWishItem->addWishItem($uid, $iid);
        echo $result ? $result : 'false';
    }

    public function removecartAction()
    {
        $uid = $this->_user->getId();
        $iid = (int)$this->_request->getParam('iid');

        require_once 'Dal/Shopping/Shopping.php';
        $dalShopping = Dal_Shopping_Shopping::getDefaultInstance();
        $rowShopping = $dalShopping->getShoppingById($uid);
        if (empty($rowShopping) || empty($rowShopping['challenge_id'])) {
            echo 'false';
            return;
        }
        $cid = $rowShopping['challenge_id'];

        if (empty($iid)) {
            echo 'false';
            return;
        }

        require_once 'Bll/Shopping/Challenge.php';
        $bllChallenge = new Bll_Shopping_Challenge();

        require_once 'Dal/Shopping/Challenge.php';
        $dalChallenge = Dal_Shopping_Challenge::getDefaultInstance();
        $cartInfo = $dalChallenge->getCartInfoById($cid, $iid);

        $result = $bllChallenge->removeChallengeCart($cid, $iid);

        if ($result && !empty($cartInfo)) {
            $guess_price = number_format($cartInfo['guess_price']);
            echo $guess_price;
        }
        else {
        	echo 'false';
        }
    }

    public function addcartAction()
    {
        $uid = $this->_user->getId();
        $iid = (int)$this->_request->getParam('iid');
        $code = $this->_request->getParam('code');
        $guess_price = (int)$this->_request->getParam('guess_price');

        require_once 'Dal/Shopping/Shopping.php';
        $dalShopping = Dal_Shopping_Shopping::getDefaultInstance();
        $rowShopping = $dalShopping->getShoppingById($uid);
        if (empty($rowShopping) || empty($rowShopping['challenge_id'])) {
            echo 'false';
            return;
        }
        $cid = $rowShopping['challenge_id'];

        require_once 'Dal/Shopping/WishItem.php';
        $dalWishItem = Dal_Shopping_WishItem::getDefaultInstance();

        require_once 'Dal/Shopping/Challenge.php';
        $dalChallenge = Dal_Shopping_Challenge::getDefaultInstance();

        $isInCart = $dalChallenge->isItemInCart($cid, $iid);
        if ($isInCart) {
            echo 'false';
            return;
        }

        $itemInfo = array();

        if (!empty($iid)) {
            $nbItem = $dalWishItem->getNbItem($iid);
            $code = $nbItem['item_code'];
            $itemInfo['real_price'] = $nbItem['price'];
        }

	    $itemInfo['item_code'] = $code;
        $itemInfo['cid'] = $cid;
        $itemInfo['iid'] = $iid;
        $itemInfo['guess_price'] = $guess_price;
        $itemInfo['uid'] = $uid;

        require_once 'Bll/Shopping/Challenge.php';
        $bllChallenge = new Bll_Shopping_Challenge();

        $result = $bllChallenge->addChallengeCart($itemInfo);
        echo $result ? $result : 'false';
    }

    /**
     * change price
     *
     */
    public function changepriceAction()
    {
        $uid = $this->_user->getId();

        $iid = (int)$this->_request->getParam('iid');
        $guess_price = (int)$this->_request->getParam('guess_price');

        if (empty($iid)) {
            echo 'false';
            return;
        }

        require_once 'Dal/Shopping/Shopping.php';
        $dalShopping = Dal_Shopping_Shopping::getDefaultInstance();
        $rowShopping = $dalShopping->getShoppingById($uid);
        if (empty($rowShopping) || empty($rowShopping['challenge_id'])) {
            echo 'false';
            return;
        }
        $cid = $rowShopping['challenge_id'];

        require_once 'Dal/Shopping/Challenge.php';
        $dalChallenge = Dal_Shopping_Challenge::getDefaultInstance();

        $isInCart = $dalChallenge->isItemInCart($cid, $iid);
        if (!$isInCart) {
            echo 'false';
            return;
        }
        $aryInfo =array();
        $aryInfo['guess_price'] = $guess_price;
        $aryInfo['create_time'] = time();
        $result = $dalChallenge->updateChallengeCart($aryInfo, $cid, $iid);

        echo $result == 1 ? 'true' : 'false';
    }

    public function listcartitemAction()
    {
        $uid = $this->_user->getId();
        $pageIndex = (int)$this->_request->getParam('pageIndex', 1);
        $pageSize = (int)$this->_request->getParam('pageSize', 10);

        require_once 'Dal/Shopping/Shopping.php';
        $dalShopping = Dal_Shopping_Shopping::getDefaultInstance();
        $rowShopping = $dalShopping->getShoppingById($uid);
        if (empty($rowShopping) || empty($rowShopping['challenge_id'])) {
            echo 'false';
            return;
        }
        $cid = $rowShopping['challenge_id'];

        require_once 'Dal/Shopping/Challenge.php';
        $dalChallenge = Dal_Shopping_Challenge::getDefaultInstance();
        $listCartItem = array();
        $listCartItem = $dalChallenge->listChallengeCart($cid, $pageIndex, $pageSize);
        $cartCount = $dalChallenge->getChallengeCartCount($cid);
        if (!empty($listCartItem) && count($listCartItem) > 0) {
            foreach ($listCartItem as $key => $value) {
                require_once 'Dal/Shopping/WishItem.php';
                $dalWishItem = Dal_Shopping_WishItem::getDefaultInstance();
                $rowItem = $dalWishItem->getNbItem($value['iid']);

                $listCartItem[$key]['name'] = empty($rowItem) ? '' : $rowItem['name'];
                $listCartItem[$key]['pic_small'] =  empty($rowItem) ? '' : $rowItem['pic_small'];

                if ($listCartItem[$key]['pic_small'] == '') {
                    $listCartItem[$key]['pic_small'] = $this->_staticUrl . '/apps/shopping/img/dummy/pic_item.png';
                }
                $listCartItem[$key]['format_guess_price'] = number_format($value['guess_price']);
            }


        }
        $response = array('info' => $listCartItem, 'count' => $cartCount);
        $response = Zend_Json::encode($response);
        echo $response;

    }
}
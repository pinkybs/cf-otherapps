<?php

/**
 * shopping controller
 * init each index page
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/08/10  zhangxin
 */
class ShoppingController extends MyLib_Zend_Controller_Action_Default
{
    /**
     * shopping info
     *
     * @var array
     */
    protected $_shoppingInfo;
    protected $_isInGame;

    protected $_appName = 'shopping';
    protected $_basicDepart = 100000;


    /**
     * index Action
     *
     */
    public function indexAction()
    {
        $this->_redirect($this->_baseUrl . '/shopping/home');
        return;
    }

    /**
     * home Action
     *
     */
    public function homeAction()
    {
        //if in game
        if ($this->_isInGame) {
            $this->_redirect($this->_baseUrl . '/shopping/depart');
            return;
        }

        $this->rank();
        $this->render();
    }

    /**
     * wish Action
     *
     */
    public function wishAction()
    {
        //if in game
        if ($this->_isInGame) {
            $this->_redirect($this->_baseUrl . '/shopping/depart');
            return;
        }

        $uid = $this->_user->getId();
        $profileUid = $this->_request->getParam('uid');
        $isMine = false;
        if(empty($profileUid)) {
            $profileUid = $uid;
            $isMine = true;
        }

        //profile - neighber
        require_once 'Dal/Shopping/User.php';
        $dalUser = Dal_Shopping_User::getDefaultInstance();
        $start = 1;
        $fetchSize = 7;
        $pos = (int)$this->_request->getParam('pos');

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
        }

        $end = ($start + $fetchSize - 1) > $count ? $count : ($start + $fetchSize - 1);
        $listNeighber = $dalUser->listAppUids($start, $fetchSize);
        Bll_User::appendPeople($listNeighber, 'uid');
        for ($i = count($listNeighber); ($count < $fetchSize) && ($i < $fetchSize); $i++) {
            $listNeighber[$i]['uid'] = '0';
        }
        $this->view->lstNeighber = $listNeighber;
        $this->view->neiCount = $count;
        $this->view->neiStart = $start;
        $this->view->neiEnd = $end;
        $this->view->profileUid = $profileUid;


        //**********************************************************************************
        require_once 'Dal/Shopping/WishItem.php';
        $dalWishItem = Dal_Shopping_WishItem::getDefaultInstance();

        $listWishItem = $dalWishItem->listWishItemByUid($profileUid, 1, 10);

        if (!empty($listWishItem) && count($listWishItem) > 0) {

        	$itemCount = $dalWishItem->getWishItemByUidCount($profileUid);

            foreach ($listWishItem as $key => $value) {
            	if ($isMine == false) {
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

	        $pageCount = ceil($itemCount / 10);
	        $page = array();
	        for ($i = 0; $i < $pageCount && $i < 10; $i++) {
	            $page[$i] = $i + 1;
	        }

            $this->view->page = $page;
	        $this->view->lstWishItem = $listWishItem;
            $this->view->itemCount = $itemCount;
            $this->view->isMine = $isMine;
        }

        $this->render();
    }

    /**
     * remove wishitem action
     *
     */
    public function removeitemAction()
    {
        $iid = (int)$this->_request->getParam('iid');
        if (empty($iid)) {
            $this->_redirect($this->_baseUrl . '/shopping/home');
            return;
        }

        require_once 'Dal/Shopping/WishItem.php';
        $dalWishItem = Dal_Shopping_WishItem::getDefaultInstance();
        $rowItemInfo = $dalWishItem->getNbItem($iid);
        if (empty($rowItemInfo)) {
            $this->_redirect($this->_baseUrl . '/shopping/error');
            return;
        }
        $rowItemInfo['format_price'] = number_format($rowItemInfo['price']);
        $this->view->itemInfo = $rowItemInfo;
        $this->render();
    }

    /**
     * add wishitem action
     *
     */
    public function additemAction()
    {
        $iid = (int)$this->_request->getParam('iid');
        if (empty($iid)) {
            $this->_redirect($this->_baseUrl . '/shopping/home');
            return;
        }

        require_once 'Dal/Shopping/WishItem.php';
        $dalWishItem = Dal_Shopping_WishItem::getDefaultInstance();
        $rowItemInfo = $dalWishItem->getNbItem($iid);
        if (empty($rowItemInfo)) {
            $this->_redirect($this->_baseUrl . '/shopping/error');
            return;
        }
        $rowItemInfo['format_price'] = number_format($rowItemInfo['price']);
        $this->view->itemInfo = $rowItemInfo;
        $this->render();
    }

	/**
     * cart Action
     *
     */
    public function mycartAction()
    {
        $uid = $this->_user->getId();
        require_once 'Dal/Shopping/Challenge.php';
        $dalChallenge = Dal_Shopping_Challenge::getDefaultInstance();

        $rowShopping = $this->_shoppingInfo;
        $cid = $rowShopping['challenge_id'];

        $this->view->cid = $cid;
        //not in game
        if (!$this->_isInGame) {
            $this->_redirect($this->_baseUrl . '/shopping/home');
            return;
        }
        //get challenge info
        require_once 'Dal/Shopping/Challenge.php';
        $dalChallenge = Dal_Shopping_Challenge::getDefaultInstance();
        $rowChallenge = $dalChallenge->getChallengeByPk($cid, $uid);
        if (empty($rowChallenge) || 1 == $rowChallenge['is_ended']) {
            $this->_redirect($this->_baseUrl . '/shopping/home');
            return;
        }
        //is ready begin
        if (empty($rowChallenge['start_time'])) {
            $dalChallenge->updateChallenge(array('start_time' => time()), $cid, $uid);
            $rowChallenge['start_time'] = time();
        }
        //is timeover
        $nowTime = time();
        $gameTime = $nowTime - $rowChallenge['start_time'];
        if ($gameTime >= $rowChallenge['game_seconds']) {
            //game end
            $this->_redirect($this->_baseUrl . '/shopping/gameend');
            return;
        }

        //get cart count and cart price
        $rowChallenge['format_target_price'] = number_format($rowChallenge['target_price']);
        $rowChallenge['cart_count'] = $dalChallenge->getChallengeCartCount($cid);
        $rowChallenge['cart_price'] = $dalChallenge->getChallengeCartPrice($cid);
        $rowChallenge['format_cart_price'] = number_format($rowChallenge['cart_price']);
        $numDiff = $rowChallenge['cart_price'] - $rowChallenge['target_price'];
        $rowChallenge['diff'] = $numDiff;
        $rowChallenge['format_diff'] = number_format(abs($numDiff));
        $rowChallenge['remain_time'] = $rowChallenge['game_seconds'] - $gameTime;
        $this->view->challengeInfo = $rowChallenge;

        $listCartItem = $dalChallenge->listChallengeCart($cid, 1, 10);

        if (!empty($listCartItem) && count($listCartItem) > 0) {

            $cartCount = $dalChallenge->getChallengeCartCount($cid);

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

            $pageCount = ceil($cartCount / 10);
            $page = array();
            for ($i = 0; $i < $pageCount && $i < 10; $i++) {
                $page[$i] = $i + 1;
            }

            $this->view->page = $page;
            $this->view->cid = $cid;
            $this->view->lstCartItem = $listCartItem;
            $this->view->challengeCount = $cartCount;
        }

        $this->render();
    }









	/****************************************************************/

    /**
     * gamestart Action
     *
     */
    public function gamestartAction()
    {
        $uid = $this->_user->getId();
        //in game already
        if ($this->_isInGame) {
            $this->_redirect($this->_baseUrl . '/shopping/depart');
            return;
        }

        require_once 'Dal/Shopping/Challenge.php';
        $dalChallenge = Dal_Shopping_Challenge::getDefaultInstance();
        $rowLastCha = $dalChallenge->getLastChallengeByUid($uid);
        if (!empty($rowLastCha)) {
            $lastDiff = $rowLastCha['real_price_all'] - $rowLastCha['target_price'];
            $strPre = '';
            if ($lastDiff > 0) {
                $strPre = '+¥';
            }
            else if ($lastDiff < 0) {
                $strPre = '-¥';
            }
            $this->view->lastDiff = $strPre . number_format(abs($lastDiff));
        }

        $this->view->flashUrl = urlencode($this->view->hostUrl . '/ajax/shopping/randdepart');

        $this->rank();

        $this->render();
    }

	/**
     * game restart Action
     *
     */
    public function restartAction()
    {
        $uid = $this->_user->getId();
        $oldcid = $this->_request->getParam('cid');

        $rowShopping = $this->_shoppingInfo;
        $cid = $rowShopping['challenge_id'];

        require_once 'Bll/Shopping/Challenge.php';
        $bllChallenge = new Bll_Shopping_Challenge();
        //end now challenge
        if (!empty($cid)) {
            $bllChallenge->endChallenge($uid, $cid, 0, 0);
            //$oldcid = $cid;
        }

        //restart a new game
        if (empty($oldcid)) {
            $this->_redirect($this->_baseUrl . '/shopping/gamestart');
            return;
        }

        //begin new challenge
        require_once 'Dal/Shopping/Challenge.php';
        $dalChallenge = Dal_Shopping_Challenge::getDefaultInstance();
        //get old challenge depart
        $rowChallenge = $dalChallenge->getChallengeByPk($oldcid, $uid);
        if (empty($rowChallenge)) {
            $this->_redirect($this->_baseUrl . '/shopping/home');
            return;
        }
        $result = $bllChallenge->newChallenge($uid, $rowChallenge['target_price']);
        if ($result) {
            $this->_redirect($this->_baseUrl . '/shopping/depart');
        }
        else {
            $this->_redirect($this->_baseUrl . '/shopping/home');
        }
        return;
    }

	/**
     * depart Action
     *
     */
    public function departAction()
    {
        $uid = $this->_user->getId();
        $rowShopping = $this->_shoppingInfo;
        $cid = $rowShopping['challenge_id'];

        $this->view->cid = $cid;
        //not in game
        if (!$this->_isInGame) {
            $this->_redirect($this->_baseUrl . '/shopping/home');
            return;
        }
        //get challenge info
        require_once 'Dal/Shopping/Challenge.php';
        $dalChallenge = Dal_Shopping_Challenge::getDefaultInstance();
        $rowChallenge = $dalChallenge->getChallengeByPk($cid, $uid);
        if (empty($rowChallenge) || 1 == $rowChallenge['is_ended']) {
            $this->_redirect($this->_baseUrl . '/shopping/home');
            return;
        }
        //is ready begin
        if (empty($rowChallenge['start_time'])) {
            $dalChallenge->updateChallenge(array('start_time' => time()), $cid, $uid);
            $rowChallenge['start_time'] = time();
        }
        //is timeover
        $nowTime = time();
        $gameTime = $nowTime - $rowChallenge['start_time'];
        if ($gameTime >= $rowChallenge['game_seconds']) {
            //game end
            $this->_redirect($this->_baseUrl . '/shopping/gameend');
            return;
        }

        //get cart count and cart price
        $rowChallenge['format_target_price'] = number_format($rowChallenge['target_price']);
        $rowChallenge['cart_count'] = $dalChallenge->getChallengeCartCount($cid);
        $rowChallenge['cart_price'] = $dalChallenge->getChallengeCartPrice($cid);
        $rowChallenge['format_cart_price'] = number_format($rowChallenge['cart_price']);
        $numDiff = $rowChallenge['cart_price'] - $rowChallenge['target_price'];
        $rowChallenge['diff'] = $numDiff;
        $rowChallenge['format_diff'] = number_format(abs($numDiff));
        $rowChallenge['remain_time'] = $rowChallenge['game_seconds'] - $gameTime;
        $this->view->challengeInfo = $rowChallenge;

        //get parent genre list
        require_once 'Bll/Shopping/ItemGenre.php';
        $this->view->lstGParent = Bll_Shopping_ItemGenre::listItemGenreParent();

        //init popular items
        $lstPopItem = $dalChallenge->listPopularItem(1, 10);
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
        $this->view->lstPopItem = $lstPopItem;
        $this->view->cntPopItem = $dalChallenge->getPopularItemCount();

        $pageCount = ceil($this->view->cntPopItem / 10);
        $page = array();
        for ($i = 0; $i < $pageCount && $i<10; $i++) {
            $page[$i] = $i + 1;
        }
        $this->view->page = $page;
        $this->render();
    }

	/**
     * departsub Action
     *
     */
    public function departsubAction()
    {
        $gid = $this->_request->getParam('gid');
        $uid = $this->_user->getId();
        $rowShopping = $this->_shoppingInfo;
        $cid = $rowShopping['challenge_id'];
        $this->view->cid = $cid;

        //not in game
        if (!$this->_isInGame) {
            $this->_redirect($this->_baseUrl . '/shopping/home');
            return;
        }
        //get challenge info
        require_once 'Dal/Shopping/Challenge.php';
        $dalChallenge = Dal_Shopping_Challenge::getDefaultInstance();
        $rowChallenge = $dalChallenge->getChallengeByPk($cid, $uid);
        if (empty($rowChallenge) || 1 == $rowChallenge['is_ended']) {
            $this->_redirect($this->_baseUrl . '/shopping/home');
            return;
        }
        //is timeover
        $nowTime = time();
        $gameTime = $nowTime - $rowChallenge['start_time'];
        if ($gameTime >= $rowChallenge['game_seconds']) {
            //game end
            $this->_redirect($this->_baseUrl . '/shopping/gameend');
            return;
        }

        //child genre not found
        require_once 'Dal/Shopping/ItemGenre.php';
        $dalGenre = Dal_Shopping_ItemGenre::getDefaultInstance();
        $rowGParent = $dalGenre->getItemGenreParent($gid);
        if (empty($gid) || empty($rowGParent)) {
            $this->_redirect($this->_baseUrl . '/shopping/depart');
            return;
        }

        //get cart count and cart price
        $rowChallenge['format_target_price'] = number_format($rowChallenge['target_price']);
        $rowChallenge['cart_count'] = $dalChallenge->getChallengeCartCount($cid);
        $rowChallenge['cart_price'] = $dalChallenge->getChallengeCartPrice($cid);
        $rowChallenge['format_cart_price'] = number_format($rowChallenge['cart_price']);
        $numDiff = $rowChallenge['cart_price'] - $rowChallenge['target_price'];
        $rowChallenge['diff'] = $numDiff;
        $rowChallenge['format_diff'] = number_format(abs($numDiff));
        $rowChallenge['remain_time'] = $rowChallenge['game_seconds'] - $gameTime;
        $this->view->challengeInfo = $rowChallenge;

        //get child genre list
        $this->view->genreParentInfo = $rowGParent;
        require_once 'Bll/Shopping/ItemGenre.php';
        $this->view->lstGChild = Bll_Shopping_ItemGenre::listItemGenreChildByParent($gid);

        //init child genre items
        require_once 'Bll/Shopping/RakutenApi.php';
        $aryGenreItem = Bll_Shopping_RakutenApi::listItemsByGenre($gid, 'standard', 1, 10);
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
            $this->view->lstGenreItem = $lstGenreItem;
            $this->view->cntGenreItem = $aryGenreItem['count'];

            $pageCount = ceil($this->view->cntGenreItem / 10);
            $page = array();
            for ($i = 0; $i < $pageCount && $i<10; $i++) {
                $page[$i] = $i + 1;
            }
            $this->view->page = $page;
        }

        $this->render();
    }

	/**
     * gameend Action
     *
     */
    public function gameendAction()
    {
        //not in game
        if (!$this->_isInGame) {
            $this->_redirect($this->_baseUrl . '/shopping/home');
            return;
        }

        require_once 'Dal/Shopping/Challenge.php';
        $dalChallenge = Dal_Shopping_Challenge::getDefaultInstance();
        $cartCount = $dalChallenge->getChallengeCartCount($cid);

        $this->view->flashUrl = urlencode($this->view->hostUrl . '/ajax/shopping/gameresult');
        $this->render();
    }


	/**
     * torakuten Action
     *
     */
    public function torakutenAction()
    {
        $iid = (int)$this->_request->getParam('iid');
        if (empty($iid)) {
        	$item_code = $this->_request->getParam('code');
        	if (empty($item_code)) {
        		$this->_redirect($this->_baseUrl . '/shopping/home');
                return;
        	}
        }

        $rowItemInfo = array();
        require_once 'Dal/Shopping/WishItem.php';
        $dalWishItem = Dal_Shopping_WishItem::getDefaultInstance();
        $rowItemInfo = $dalWishItem->getNbItem($iid);

        if (empty($rowItemInfo) && $iid == 0) {
            require_once 'Bll/Shopping/RakutenApi.php';
            $item = Bll_Shopping_RakutenApi::getItemByCode($item_code);
            $rowItemInfo['name'] = $item['item_name'];
            $rowItemInfo['item_code'] = $item['item_code'];
            $rowItemInfo['pic_small'] = $item['item_small_pic'];
            $rowItemInfo['price'] = $item['item_price'];
            $rowItemInfo['caption'] = $item['item_caption'];
            $rowItemInfo['url'] = urlencode($item['item_url']);
            $rowItemInfo['pic_big'] = $item['item_big_pic'];
        }

        if (empty($rowItemInfo)) {
            $this->_redirect($this->_baseUrl . '/shopping/error');
            return;
        }
        $rowItemInfo['format_price'] = number_format($rowItemInfo['price']);
        $this->view->itemInfo = $rowItemInfo;
        $this->view->isInGame = $this->_isInGame;
        $this->render();
    }

	/**
     * help Action
     *
     */
    public function helpAction()
    {
        $this->render();
    }

    /**
     * error Action
     *
     */
    public function errorAction()
    {
        $this->render();
    }

    /**
     * deipatch
     *
     */
    function preDispatch()
    {
        $uid = $this->_user->getId();

        //login user info
        require_once 'Dal/Shopping/Shopping.php';
        $dalShopping = Dal_Shopping_Shopping::getDefaultInstance();
        $rowShopping = $dalShopping->getShoppingById($uid);
        if (empty($rowShopping)) {
            require_once 'Bll/Shopping/Shopping.php';
            $bllShopping = new Bll_Shopping_Shopping();
            $bllShopping->newShoppingUser($uid);
            $rowShopping = $dalShopping->getShoppingById($uid);
        }
        if (empty($rowShopping)) {
            return $this->_forward('notfound', 'error', 'default');
        }

        //update last login time
        if ('home' == $this->_request->getActionName()) {
            //check is today's first login
            $todayDate = date('Y-m-d');
            $todayTime = strtotime($todayDate);
            $aryLoginTime = array();
            if (empty($rowShopping['last_login_time']) || $rowShopping['last_login_time'] < $todayTime) {
                require_once 'Bll/Secret.php';
                //$guid = Bll_Secret::getUUID();
                //$this->_isTodayFirstLogin = $guid;
                //$aryLoginTime['daily_visit_gift_flag'] = $guid;
            }
            //update last login time
            $aryLoginTime['last_login_time'] = time();
            $dalShopping->updateShopping($aryLoginTime, $uid);
        }

        require_once 'Bll/User.php';
        //common area: prof info
        $rowShopping['fomat_price_depart10'] = ($rowShopping['price_depart10'] - $this->_basicDepart>0?'+':'') . number_format($rowShopping['price_depart10'] - $this->_basicDepart);
        $rowShopping['fomat_price_depart50'] = ($rowShopping['price_depart50'] - $this->_basicDepart*5>0?'+':'') . number_format($rowShopping['price_depart50'] - $this->_basicDepart*5);
        $rowShopping['fomat_price_depart100'] = ($rowShopping['price_depart100'] - $this->_basicDepart*10>0?'+':'') . number_format($rowShopping['price_depart100']- $this->_basicDepart*10);
        $rowShopping['fomat_price_depart500'] = ($rowShopping['price_depart500'] - $this->_basicDepart*50>0?'+':'') . number_format($rowShopping['price_depart500'] - $this->_basicDepart*50);
        $rowShopping['rank10'] = $dalShopping->getShoppingRankByDepart($uid, 'price_depart10', $this->_basicDepart);
        $rowShopping['rank50'] = $dalShopping->getShoppingRankByDepart($uid, 'price_depart50', $this->_basicDepart*5);
        $rowShopping['rank100'] = $dalShopping->getShoppingRankByDepart($uid, 'price_depart100', $this->_basicDepart*10);
        $rowShopping['rank500'] = $dalShopping->getShoppingRankByDepart($uid, 'price_depart500', $this->_basicDepart*50);

        Bll_User::appendPerson($rowShopping, 'uid');
        $this->_shoppingInfo = $rowShopping;
        $this->_isInGame = !empty($rowShopping['challenge_id']);
        $this->view->uid = $uid;
        $this->view->myProfInfo = $rowShopping;
        $this->view->isInGame = $this->_isInGame ? '1' : '0';
        $this->view->flashTimer = urlencode($this->view->hostUrl . '/ajax/shopping/gametimer');

        //common area: wish item info
        require_once 'Dal/Shopping/WishItem.php';
        $dalWishItem = Dal_Shopping_WishItem::getDefaultInstance();
        $lstWishItem = $dalWishItem->listWishItemByUid($uid, 1, 1);
        if (!empty($lstWishItem) && count($lstWishItem) > 0) {
            $this->view->myWishItemInfo = $lstWishItem[0];
        }

/*
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
*/
        $this->view->mixiHostUrl = MIXI_HOST;
        $this->view->appId = $this->_appId;
    }

    protected function rank()
    {
    	$uid = $this->_user->getId();
    	//get my position
        $fidsStr = Bll_Friend::getFriendIds($uid);
        $fids = explode(',', $fidsStr);

        $sub_str_size = 8;
        require_once 'Bll/User.php';
        require_once 'Dal/Shopping/Shopping.php';
        $dalShopp = Dal_Shopping_Shopping::getDefaultInstance();
        //price rank leader
        $list10FriendLeader = $dalShopp->lstShoppingFriendRank($uid, 'price_depart10', $this->_basicDepart, 1, 2);
        if (count($list10FriendLeader) != 0 && !empty($list10FriendLeader)) {
            foreach ($list10FriendLeader as $key => $pdata) {
                $list10FriendLeader[$key]['rankNo'] = (int)($key + 1);

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
                $list10FriendLeader[$key]['format_diff'] = $formatPrice;

                $rowInfo = Bll_User::getPerson($pdata['uid']);
                $tmpName = $rowInfo->getDisplayName();
                $tmpName = html_entity_decode($tmpName, ENT_QUOTES, 'UTF-8');
                if (mb_strlen($tmpName, 'UTF-8') > $sub_str_size) {
                    $tmpName = mb_substr($tmpName, 0, $sub_str_size, 'UTF-8') . '…';
                }
                $tmpName = htmlentities($tmpName, ENT_QUOTES, 'UTF-8');

                $list10FriendLeader[$key]['name'] = $tmpName;
                $list10FriendLeader[$key]['pic'] = $rowInfo->getThumbnailUrl();
            }
        }
        for ($i = count($list10FriendLeader); $i < 2; $i++) {
            $list10FriendLeader[$i]['uid'] = 0;
            $list10FriendLeader[$i]['rankNo'] = (int)($i + 1);
        }

        $rankStart = 3;
        $fetchSize = 5;
        //get my position
        $rankMine = $dalShopp->getShoppingFriendRankByDepart($uid, 'price_depart10', $this->_basicDepart, $fids);
        $count = $dalShopp->getFriendCountById($uid, 'price_depart10');

        //in center
        if ($rankMine > 7 && ($rankMine + $fetchSize) <= $count) {
            $rankStart = $rankMine - 2;
        }
        //last six
        else if (($rankMine + $fetchSize) > $count && ($count-2) > $fetchSize) {
            $rankStart = $count - $fetchSize + 1;
        }
        $rankEnd = ($rankStart + $fetchSize - 1) > $count ? $count : ($rankStart + $fetchSize - 1);

        $list10Friend = $dalShopp->lstShoppingFriendRank($uid, 'price_depart10', $this->_basicDepart, $rankStart, $fetchSize);

        if (count($list10Friend) != 0 && !empty($list10Friend)) {
            foreach ($list10Friend as $key => $pdata) {
                $list10Friend[$key]['rankNo'] = (int)($rankStart + $key);

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

                $list10Friend[$key]['format_diff'] = $formatPrice;
                $rowInfo = Bll_User::getPerson($pdata['uid']);

                $tmpName = $rowInfo->getDisplayName();

                $tmpName = html_entity_decode($tmpName, ENT_QUOTES, 'UTF-8');
                if (mb_strlen($tmpName, 'UTF-8') > 8) {
                    $tmpName = mb_substr($tmpName, 0, 8, 'UTF-8') . '…';
                }
                $tmpName = htmlentities($tmpName, ENT_QUOTES, 'UTF-8');

                $list10Friend[$key]['name'] = $tmpName;
                $list10Friend[$key]['pic'] = $rowInfo->getThumbnailUrl();
            }
        }

        if (3 == $rankStart && count($list10Friend) < 5) {
            for ($i = count($list10Friend); $i < 5; $i++) {
                $list10Friend[$i]['uid'] = 0;
                $list10Friend[$i]['rankNo'] = (int)($rankStart + $i);
            }
        }

        $this->view->countOne = $count;
        $this->view->rankStartOne = $rankStart;
        $this->view->rankEndOne = $rankEnd;
        $this->view->lst10FriendRank = array_reverse($list10Friend);
        $this->view->lst10FriendRankLeader = array_reverse($list10FriendLeader);


        //price_depart50
        $list50FriendLeader = $dalShopp->lstShoppingFriendRank($uid, 'price_depart50', $this->_basicDepart * 5, 1, 2);
        if (count($list50FriendLeader) != 0 && !empty($list50FriendLeader)) {
            foreach ($list50FriendLeader as $key => $pdata) {
                $list50FriendLeader[$key]['rankNo'] = (int)($key + 1);

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

                $list50FriendLeader[$key]['format_diff'] = $formatPrice;

                $rowInfo = Bll_User::getPerson($pdata['uid']);
                $tmpName = $rowInfo->getDisplayName();

                $tmpName = html_entity_decode($tmpName, ENT_QUOTES, 'UTF-8');
                if (mb_strlen($tmpName, 'UTF-8') > $sub_str_size) {
                    $tmpName = mb_substr($tmpName, 0, $sub_str_size, 'UTF-8') . '…';
                }
                $tmpName = htmlentities($tmpName, ENT_QUOTES, 'UTF-8');

                $list50FriendLeader[$key]['name'] = $tmpName;
                $list50FriendLeader[$key]['pic'] = $rowInfo->getThumbnailUrl();
            }
        }

        for ($i = count($list50FriendLeader); $i < 2; $i++) {
            $list50FriendLeader[$i]['uid'] = 0;
            $list50FriendLeader[$i]['rankNo'] = (int)($i + 1);
        }

        $rankStartTwo = 3;
        $fetchSizeTwo = 5;
        //get my position
        $rankMineTwo = $dalShopp->getShoppingFriendRankByDepart($uid, 'price_depart50', $this->_basicDepart * 5, $fids);
        $countTwo = $dalShopp->getFriendCountById($uid, 'price_depart50');

        //in center
        if ($rankMineTwo > 7 && ($rankMineTwo + $fetchSizeTwo) <= $countTwo) {
            $rankStartTwo = $rankMineTwo - 2;
        }
        //last six
        else if (($rankMineTwo + $fetchSizeTwo) > $countTwo && ($countTwo-2) > $fetchSizeTwo) {
            $rankStartTwo = $countTwo - $fetchSizeTwo + 1;
        }
        $rankEndTwo = ($rankStartTwo + $fetchSizeTwo - 1) > $countTwo ? $countTwo : ($rankStartTwo + $fetchSizeTwo - 1);

        $list50Friend = $dalShopp->lstShoppingFriendRank($uid, 'price_depart50', $this->_basicDepart * 5, $rankStartTwo, $fetchSizeTwo);

        if (count($list50Friend) != 0 && !empty($list50Friend)) {
            foreach ($list50Friend as $key => $pdata) {
                $list50Friend[$key]['rankNo'] = (int)($rankStartTwo + $key);

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

                $list50Friend[$key]['format_diff'] = $formatPrice;
                $rowInfo = Bll_User::getPerson($pdata['uid']);

                $tmpName = $rowInfo->getDisplayName();

                $tmpName = html_entity_decode($tmpName, ENT_QUOTES, 'UTF-8');
                if (mb_strlen($tmpName, 'UTF-8') > 8) {
                    $tmpName = mb_substr($tmpName, 0, 8, 'UTF-8') . '…';
                }
                $tmpName = htmlentities($tmpName, ENT_QUOTES, 'UTF-8');

                $list50Friend[$key]['name'] = $tmpName;
                $list50Friend[$key]['pic'] = $rowInfo->getThumbnailUrl();
            }
        }

        if (3 == $rankStartTwo && count($list50Friend) < 5) {
            for ($i = count($list50Friend); $i < 5; $i++) {
                $list50Friend[$i]['uid'] = 0;
                $list50Friend[$i]['rankNo'] = (int)($rankStartTwo + $i);
            }
        }

        $this->view->countTwo = $countTwo;
        $this->view->rankStartTwo = $rankStartTwo;
        $this->view->rankEndTwo = $rankEndTwo;
        $this->view->lst50FriendRank = array_reverse($list50Friend);
        $this->view->lst50FriendRankLeader = array_reverse($list50FriendLeader);


        $list100FriendLeader = $dalShopp->lstShoppingFriendRank($uid, 'price_depart100', $this->_basicDepart * 10, 1, 2);
        if (count($list100FriendLeader) != 0 && !empty($list100FriendLeader)) {
            foreach ($list100FriendLeader as $key => $pdata) {
                $list100FriendLeader[$key]['rankNo'] = (int)($key + 1);

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
                $list100FriendLeader[$key]['format_diff'] = $formatPrice;

                $rowInfo = Bll_User::getPerson($pdata['uid']);
                $tmpName = $rowInfo->getDisplayName();

                $tmpName = html_entity_decode($tmpName, ENT_QUOTES, 'UTF-8');
                if (mb_strlen($tmpName, 'UTF-8') > $sub_str_size) {
                    $tmpName = mb_substr($tmpName, 0, $sub_str_size, 'UTF-8') . '…';
                }
                $tmpName = htmlentities($tmpName, ENT_QUOTES, 'UTF-8');

                $list100FriendLeader[$key]['name'] = $tmpName;
                $list100FriendLeader[$key]['pic'] = $rowInfo->getThumbnailUrl();
            }
        }

        for ($i = count($list100FriendLeader); $i < 2; $i++) {
            $list100FriendLeader[$i]['uid'] = 0;
            $list100FriendLeader[$i]['rankNo'] = (int)($i + 1);
        }

        $rankStartThree = 3;
        $fetchSizeThree = 5;
        //get my position
        $rankMineThree = $dalShopp->getShoppingFriendRankByDepart($uid, 'price_depart100', $this->_basicDepart * 10, $fids);
        $countThree = $dalShopp->getFriendCountById($uid, 'price_depart100');

        //in center
        if ($rankMineThree > 7 && ($rankMineThree + $fetchSizeThree) <= $countThree) {
            $rankStartThree = $rankMineThree - 2;
        }
        //last six
        else if (($rankMineThree + $fetchSizeThree) > $countThree && ($countThree-2) > $fetchSizeThree) {
            $rankStartThree = $countThree - $fetchSizeThree + 1;
        }
        $rankEndThree = ($rankStartThree + $fetchSizeThree - 1) > $countThree ? $countThree : ($rankStartThree + $fetchSizeThree - 1);

        $list100Friend = $dalShopp->lstShoppingFriendRank($uid, 'price_depart100', $this->_basicDepart * 10, $rankStartThree, $fetchSizeThree);

        if (count($list100Friend) != 0 && !empty($list100Friend)) {
            foreach ($list100Friend as $key => $pdata) {
                $list100Friend[$key]['rankNo'] = (int)($rankStartThree + $key);

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

                $list100Friend[$key]['format_diff'] = $formatPrice;
                $rowInfo = Bll_User::getPerson($pdata['uid']);

                $tmpName = $rowInfo->getDisplayName();
                $tmpName = html_entity_decode($tmpName, ENT_QUOTES, 'UTF-8');
                if (mb_strlen($tmpName, 'UTF-8') > 8) {
                    $tmpName = mb_substr($tmpName, 0, 8, 'UTF-8') . '…';
                }
                $tmpName = htmlentities($tmpName, ENT_QUOTES, 'UTF-8');

                $list100Friend[$key]['name'] = $tmpName;
                $list100Friend[$key]['pic'] = $rowInfo->getThumbnailUrl();
            }
        }

        if (3 == $rankStartThree && count($list100Friend) < 5) {
            for ($i = count($list100Friend); $i < 5; $i++) {
                $list100Friend[$i]['uid'] = 0;
                $list100Friend[$i]['rankNo'] = (int)($rankStartThree + $i);
            }
        }

        $this->view->countThree = $countThree;
        $this->view->rankStartThree = $rankStartThree;
        $this->view->rankEndThree = $rankEndThree;
        $this->view->lst100FriendRank = array_reverse($list100Friend);
        $this->view->lst100FriendRankLeader = array_reverse($list100FriendLeader);

        $list500FriendLeader = $dalShopp->lstShoppingFriendRank($uid, 'price_depart500', $this->_basicDepart * 50, 1, 2);
        if (count($list500FriendLeader) != 0 && !empty($list500FriendLeader)) {
            foreach ($list500FriendLeader as $key => $pdata) {
                $list500FriendLeader[$key]['rankNo'] = (int)($key + 1);

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

                $list500FriendLeader[$key]['format_diff'] = $formatPrice;

                $rowInfo = Bll_User::getPerson($pdata['uid']);
                $tmpName = $rowInfo->getDisplayName();

                $tmpName = html_entity_decode($tmpName, ENT_QUOTES, 'UTF-8');
                if (mb_strlen($tmpName, 'UTF-8') > $sub_str_size) {
                    $tmpName = mb_substr($tmpName, 0, $sub_str_size, 'UTF-8') . '…';
                }
                $tmpName = htmlentities($tmpName, ENT_QUOTES, 'UTF-8');

                $list500FriendLeader[$key]['name'] = $tmpName;
                $list500FriendLeader[$key]['pic'] = $rowInfo->getThumbnailUrl();
            }
        }
        for ($i = count($list500FriendLeader); $i < 2; $i++) {
            $list500FriendLeader[$i]['uid'] = 0;
            $list500FriendLeader[$i]['rankNo'] = (int)($i + 1);
        }

        $rankStartFour = 3;
        $fetchSizeFour = 5;

        $rankMineFour = $dalShopp->getShoppingFriendRankByDepart($uid, 'price_depart500', $this->_basicDepart * 50, $fids);
        $countFour = $dalShopp->getFriendCountById($uid, 'price_depart500');

        //in center
        if ($rankMineFour > 7 && ($rankMineFour + $fetchSizeFour) <= $countFour) {
            $rankStartFour = $rankMineFour - 2;
        }
        //last six
        else if (($rankMineFour + $fetchSizeFour) > $countFour && ($countFour-2) > $fetchSizeFour) {
            $rankStartFour = $countFour - $fetchSizeFour + 1;
        }
        $rankEndFour = ($rankStartFour + $fetchSizeFour - 1) > $countFour ? $countFour : ($rankStartFour + $fetchSizeFour - 1);

        $list500Friend = $dalShopp->lstShoppingFriendRank($uid, 'price_depart500', $this->_basicDepart * 50, $rankStartFour, $fetchSizeFour);

        if (count($list500Friend) != 0 && !empty($list500Friend)) {
            foreach ($list500Friend as $key => $pdata) {
                $list500Friend[$key]['rankNo'] = (int)($rankStartFour + $key);

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

                $list500Friend[$key]['format_diff'] = $formatPrice;
                $rowInfo = Bll_User::getPerson($pdata['uid']);

                $tmpName = $rowInfo->getDisplayName();
                $tmpName = html_entity_decode($tmpName, ENT_QUOTES, 'UTF-8');
                if (mb_strlen($tmpName, 'UTF-8') > 8) {
                    $tmpName = mb_substr($tmpName, 0, 8, 'UTF-8') . '…';
                }
                $tmpName = htmlentities($tmpName, ENT_QUOTES, 'UTF-8');

                $list500Friend[$key]['name'] = $tmpName;
                $list500Friend[$key]['pic'] = $rowInfo->getThumbnailUrl();
            }
        }

        if (3 == $rankStartFour && count($list500Friend) < 5) {
            for ($i = count($list500Friend); $i < 5; $i++) {
                $list500Friend[$i]['uid'] = 0;
                $list500Friend[$i]['rankNo'] = (int)($rankStartFour + $i);
            }
        }

        $this->view->countFour = $countFour;
        $this->view->rankStartFour = $rankStartFour;
        $this->view->rankEndFour = $rankEndFour;
        $this->view->lst500FriendRank = array_reverse($list500Friend);
        $this->view->lst500FriendRankLeader = array_reverse($list500FriendLeader);
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
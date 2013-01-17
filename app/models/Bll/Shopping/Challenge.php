<?php

require_once 'Bll/Abstract.php';

/**
 * Mixi App Shopping Challenge logic Operation
 *
 * @package    Bll/Shopping
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/08/10    zhangxin
 */
final class Bll_Shopping_Challenge extends Bll_Abstract
{
    /**
     * new Challenge
     *
     * @param string $uid
     * @param integer $depart
     * @return boolean
     */
    public function newChallenge($uid, $depart)
    {
        try {
            require_once 'Dal/Shopping/Challenge.php';
            $dalChallenge = Dal_Shopping_Challenge::getDefaultInstance();
            require_once 'Dal/Shopping/Shopping.php';
            $dalShopping = Dal_Shopping_Shopping::getDefaultInstance();

            $this->_wdb->beginTransaction();

            //update other opening challenge
            $dalChallenge->updateOpenChallengeToClose($uid);

            //start challenge
            $aryInfo = array();
            $aryInfo['uid'] = $uid;
            $aryInfo['target_price'] = $depart;
            $aryInfo['game_seconds'] = (100000 == $depart || 500000 == $depart) ? 300 : 600;
            $aryInfo['create_time'] = time();
            $cid = $dalChallenge->insertChallenge($aryInfo);

            //update shopping user
            $dalShopping->updateShopping(array('challenge_id' => $cid), $uid);

            $this->_wdb->commit();
            return true;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            debug_log('Bll/Shopping/Challenge/newChallenge:' . $e->getMessage());
            return false;
        }
    }

	/**
     * end Challenge
     *
     * @param string $uid
     * @param integer $cid
     * @param integer $guessPrice
     * @param integer $realPrice
     * @return boolean
     */
    public function endChallenge($uid, $cid, $guessPrice, $realPrice)
    {
        try {
            require_once 'Dal/Shopping/Challenge.php';
            $dalChallenge = Dal_Shopping_Challenge::getDefaultInstance();
            require_once 'Dal/Shopping/Shopping.php';
            $dalShopping = Dal_Shopping_Shopping::getDefaultInstance();

            $this->_wdb->beginTransaction();

            $rowShopping = $dalShopping->getShoppingById($uid);
            if (empty($rowShopping) || empty($rowShopping['challenge_id'])) {
                $this->_wdb->rollBack();
                return false;
            }

            $rowChallenge = $dalChallenge->getChallengeByPk($cid, $uid);
            if (empty($rowChallenge) || 1 == $rowChallenge['is_ended']) {
                $this->_wdb->rollBack();
                return false;
            }

            //end challenge
            $aryInfo = array();
            $aryInfo['end_time'] = time();
            $aryInfo['is_ended'] = 1;
            $aryInfo['guess_price_all'] = $guessPrice;
            $aryInfo['real_price_all'] = $realPrice;
            $dalChallenge->updateChallenge($aryInfo, $cid, $uid);

            //update shopping user
            $aryUser = array();
            $aryUser['challenge_id'] = 0;
            if (100000 == $rowChallenge['target_price']) {
                $deparCol = 'price_depart10';
            }
            else if (500000 == $rowChallenge['target_price']) {
                $deparCol = 'price_depart50';
            }
            else if (1000000 == $rowChallenge['target_price']) {
                $deparCol = 'price_depart100';
            }
            else if (5000000 == $rowChallenge['target_price']) {
                $deparCol = 'price_depart500';
            }
            if (0 == $rowShopping[$deparCol]
                || abs($realPrice - $rowChallenge['target_price']) < abs($rowShopping[$deparCol] - $rowChallenge['target_price'])) {
                $aryUser[$deparCol] = $realPrice;
            }

            $dalShopping->updateShopping($aryUser, $uid);

            $this->_wdb->commit();
            return true;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            debug_log('Bll/Shopping/Challenge/endChallenge:' . $e->getMessage());
            return false;
        }
    }


/******************************************************/

    /**
     * add Challenge cart
     *
     * @param array $info
     * @return boolean
     */
    public function addChallengeCart($info)
    {
        try {
            require_once 'Dal/Shopping/WishItem.php';
            $dalWishItem = Dal_Shopping_WishItem::getDefaultInstance();

            require_once 'Dal/Shopping/Challenge.php';
            $dalChallenge = Dal_Shopping_Challenge::getDefaultInstance();

            if ($info['cid'] != null && $info['cid'] != '') {
                $isInCart = $dalChallenge->isItemInCart($info['cid'], $info['iid']);
                if ($isInCart) {
                    return false;
                }
            }

	        //get challenge info
	        $rowChallenge = $dalChallenge->getChallengeByPk($info['cid'], $info['uid']);
	        if (empty($rowChallenge) || 1 == $rowChallenge['is_ended']) {
	            return false;
	        }

	        //is timeover
	        $nowTime = time();
	        $gameTime = $nowTime - $rowChallenge['start_time'];
	        if ($gameTime >= $rowChallenge['game_seconds']) {
	            //game end
	            return false;
	        }

            $this->_wdb->beginTransaction();

            //judge whether nb table have info
            $itemInfo = $dalWishItem->getNbItemByCode($info['item_code']);

            if (empty($itemInfo) && count($itemInfo)) {
                require_once 'Bll/Shopping/RakutenApi.php';
                $item = Bll_Shopping_RakutenApi::getItemByCode($info['item_code']);

                $itemInfo = array();
                $itemInfo['name'] = $item['item_name'];
                $itemInfo['item_code'] = $item['item_code'];
                $itemInfo['pic_small'] = $item['item_small_pic'];
                $itemInfo['price'] = $item['item_price'];
                $itemInfo['caption'] = $item['item_caption'];
                $itemInfo['url'] = $item['item_url'];
                $itemInfo['pic_big'] = $item['item_big_pic'];
                $itemInfo['create_time'] = time();

                $info['real_price'] = $item['item_price'];
                //add nb item
                $lastId = $dalWishItem->insertNbItem($itemInfo);
                $info['iid'] = $lastId;
             }
             else {
                 $info['real_price'] = $itemInfo['price'];
             }

            if ($info['iid'] == 0 || empty($info['iid'])) {
                $this->_wdb->rollBack();
                return false;
            }

            //judge whether wishitem table
            $isExist = $dalWishItem->isWishItemExist($info['uid'], $info['iid']);
            if (!$isExist) {
                $aryWishInfo = array();
                $aryWishInfo['uid'] = $info['uid'];
                $aryWishInfo['iid'] = $info['iid'];
                $aryWishInfo['create_time'] = time();
                //add wishitem
                $dalWishItem->insertWishItem($aryWishInfo);
            }

            //add challenge cart
            $aryInfo = array();
            $aryInfo['iid'] = $info['iid'];
            $aryInfo['cid'] = $info['cid'];
            $aryInfo['guess_price'] = $info['guess_price'];
            $aryInfo['real_price'] = $info['real_price'];
            $aryInfo['create_time'] = time();

            $dalChallenge->insertChallengeCart($aryInfo);

            $this->_wdb->commit();

            return true;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            debug_log('Bll/Shopping/Challenge/addChallengeCart:' . $e->getMessage());
            return false;
        }
    }

    /**
     * remove wishitem
     *
     * @param integer $id
     * @return boolean
     */
    public function removeChallengeCart($cid, $iid)
    {
        try {
            require_once 'Dal/Shopping/Challenge.php';
            $dalChallenge = Dal_Shopping_Challenge::getDefaultInstance();

            $this->_wdb->beginTransaction();

            $dalChallenge->deleteChallengeCart($cid, $iid);

            $this->_wdb->commit();

            return true;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            debug_log('Bll/Shopping/Challenge/removeChallengeCart:' . $e->getMessage());
            return false;
        }
    }
}
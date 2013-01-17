<?php

require_once 'Bll/Abstract.php';
require_once 'Zend/Json.php';
/** @see Bll_Cache */
require_once 'Bll/Cache.php';

/**
 * Mixi App Shopping Rakuten Api logic Operation
 *
 * @package    Bll/Shopping
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/08/10    zhangxin
 */
final class Bll_Shopping_RakutenApi
{

    private static $_rakutenUrl = 'http://api.rakuten.co.jp/rws/2.0/json';
    private static $_developId = '?developerId=2bc48d0d246859f30aac653221ede7b8';
    private static $_fixedItemSrh = '&operation=ItemSearch&version=2009-04-15&imageFlag=1';
    private static $_fixedItemCodeSrh = '&operation=ItemCodeSearch&version=2007-04-11';

	/**
     * list items from RakutenApi by genreId
     *
     * @param integer $genreId
     * @param string $sort [+itemPrice：価格順（昇順）  /  -itemPrice：価格順（降順） / standard：楽天標準ソート順]
     * @param integer $page
     * @param integer $pageCount
     * @return array
     */
    public static function listItemsByGenre($genreId, $sort='standard', $page = 1, $pageCount = 10)
    {
        $strUrl = self::$_rakutenUrl . self::$_developId . self::$_fixedItemSrh;
        $strUrl .= '&page=' . $page;
        $strUrl .= '&hits=' . $pageCount;
        $strUrl .= '&sort=' . urlencode($sort);
        $strUrl .= '&genreId=' . $genreId;

        //get from cache
        $cacheKey = Bll_Cache::getCacheKey('Bll_Shopping_RakutenApi', 'listItemsByGenre', $strUrl);
        if (!($aryInfo = Bll_Cache::get($cacheKey))) {
            $client = new Zend_Http_Client($strUrl, array(
                    'maxredirects' => 5,
                    'timeout'      => 10));
            $response = $client->request();

            $result = false;
            $aryInfo = null;
            $cntTotal = 0;
            if ($response->isSuccessful()) {
                $result = Zend_Json::decode($response->getBody(), Zend_Json::TYPE_ARRAY);
                if ($result && $result['Body'] && $result['Body']['ItemSearch'] && $result['Body']['ItemSearch']['Items']['Item']) {
                    $aryItems = $result['Body']['ItemSearch']['Items']['Item'];
                    $cntTotal = $result['Body']['ItemSearch']['count'];
                    foreach ($aryItems as $key=>$item) {
                        $aryInfo[$key]['item_code'] = $item['itemCode'];
                        $aryInfo[$key]['item_name'] = $item['itemName'];
                        $aryInfo[$key]['item_price'] = $item['itemPrice'];
                        $aryInfo[$key]['item_format_price'] = number_format($item['itemPrice']);
                        $aryInfo[$key]['item_caption'] = $item['itemCaption'];
                        $aryInfo[$key]['item_url'] = $item['itemUrl'];
                        $aryInfo[$key]['item_small_pic'] = $item['smallImageUrl'];
                        $aryInfo[$key]['item_big_pic'] = $item['mediumImageUrl'];
                    }
                    $aryInfo['total_count'] = $cntTotal;
                    Bll_Cache::set($cacheKey, $aryInfo, Bll_Cache::LIFE_TIME_ONE_MINUTE * 15);
                }
            }
        }

        $aryInfo = is_null($aryInfo) ? array() : $aryInfo;
        return array('info' => $aryInfo, 'count' => $aryInfo['total_count']);
    }

    /**
     * list items from RakutenApi
     *
     * @param string $keyword
     * @param string $sort [+itemPrice：価格順（昇順）  /  -itemPrice：価格順（降順） / standard：楽天標準ソート順]
     * @param integer $page
     * @param integer $pageCount
     * @return array
     */
    public static function listItemsByKeyword($keyword, $sort='standard', $page = 1, $pageCount = 10)
    {
        $strUrl = self::$_rakutenUrl . self::$_developId . self::$_fixedItemSrh;
        $strUrl .= '&page=' . $page;
        $strUrl .= '&hits=' . $pageCount;
        $strUrl .= '&sort=' . urlencode($sort);
        $strUrl .= '&keyword=' . urlencode($keyword);

        //get from cache
        $cacheKey = Bll_Cache::getCacheKey('Bll_Shopping_RakutenApi', 'listItemsByKeyword', $strUrl);
        if (!($aryInfo = Bll_Cache::get($cacheKey))) {
            $client = new Zend_Http_Client($strUrl, array(
                    'maxredirects' => 5,
                    'timeout'      => 10));
            $response = $client->request();

            $result = false;
            $aryInfo = null;
            $cntTotal = 0;
            if ($response->isSuccessful()) {
                $result = Zend_Json::decode($response->getBody(), Zend_Json::TYPE_ARRAY);
                if ($result && $result['Body'] && $result['Body']['ItemSearch'] && $result['Body']['ItemSearch']['Items']['Item']) {
                    $aryItems = $result['Body']['ItemSearch']['Items']['Item'];
                    $cntTotal = $result['Body']['ItemSearch']['count'];
                    foreach ($aryItems as $key=>$item) {
                        $aryInfo[$key]['item_code'] = $item['itemCode'];
                        $aryInfo[$key]['item_name'] = $item['itemName'];
                        $aryInfo[$key]['item_price'] = $item['itemPrice'];
                        $aryInfo[$key]['item_format_price'] = number_format($item['itemPrice']);
                        $aryInfo[$key]['item_caption'] = $item['itemCaption'];
                        $aryInfo[$key]['item_url'] = $item['itemUrl'];
                        $aryInfo[$key]['item_small_pic'] = $item['smallImageUrl'];
                        $aryInfo[$key]['item_big_pic'] = $item['mediumImageUrl'];
                    }
                    $aryInfo['total_count'] = $cntTotal;
                    Bll_Cache::set($cacheKey, $aryInfo, Bll_Cache::LIFE_TIME_ONE_MINUTE * 10);
                }
            }
        }

        $aryInfo = is_null($aryInfo) ? array() : $aryInfo;
        return array('info' => $aryInfo, 'count' => $aryInfo['total_count']);
    }

    /**
     * get item by code from RakutenApi
     *
     * @param string $code
     * @return array
     */
    public static function getItemByCode($code)
    {
        $strUrl = self::$_rakutenUrl . self::$_developId . self::$_fixedItemCodeSrh;
        $strUrl .= '&itemCode=' . $code;

        $aryInfo = null;
        //get from cache
        $key = Bll_Cache::getCacheKey('Bll_Shopping_RakutenApi', 'getItemByCode', $code);
        if (!($aryInfo = Bll_Cache::get($key))) {
            //no cache
            $client = new Zend_Http_Client($strUrl, array(
                    'maxredirects' => 5,
                    'timeout'      => 10));
            $response = $client->request();

            if ($response->isSuccessful()) {
                $result = Zend_Json::decode($response->getBody(), Zend_Json::TYPE_ARRAY);
                if ($result) {
                    $aryItem = $result['Body']['ItemCodeSearch']['Items']['Item'];
                    if (!empty($aryItem) && count($aryItem) > 0) {
                        $aryInfo['item_code'] = $aryItem[0]['itemCode'];
                        $aryInfo['item_name'] = $aryItem[0]['itemName'];
                        $aryInfo['item_price'] = $aryItem[0]['itemPrice'];
                        $aryInfo['item_format_price'] = number_format($aryItem[0]['itemPrice']);
                        $aryInfo['item_caption'] = $aryItem[0]['itemCaption'];
                        $aryInfo['item_url'] = $aryItem[0]['itemUrl'];
                        $aryInfo['item_small_pic'] = $aryItem[0]['smallImageUrl'];
                        $aryInfo['item_big_pic'] = $aryItem[0]['mediumImageUrl'];

                        Bll_Cache::set($key, $aryInfo, Bll_Cache::LIFE_TIME_ONE_DAY);
                    }
                }
            }
        }

        return is_null($aryInfo) ? false : $aryInfo;
    }

}
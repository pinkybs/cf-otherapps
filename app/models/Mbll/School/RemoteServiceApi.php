<?php

/** @see Bll_Cache */
require_once 'Bll/Cache.php';

/**
 * Remote service api
 *
 * @package    Mbll/School
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create
 */
class Mbll_School_RemoteServiceApi
{
    const MAX_REDIRECTS = 3;
    const TIMEOUT = 2;

    private $_secretKey;

    private static $_prefix = 'Mbll_School_RemoteServiceApi';

    public function __construct()
    {
        $secret = Zend_Registry::get('secret');
        $this->_secretKey = $secret['validationKey'];
    }

    private function getCacheKey($salt, $params = null)
    {
        return Bll_Cache::getCacheKey(self::$_prefix, $salt, $params);
    }

	/**
     * get remote sever for api service - list ashiato
     *
     * @param integer $uid
     * @param integer $size
     * @return array
     */
    public function listAshiato($uid, $size)
    {
        $key = self::getCacheKey('listAshiato', $uid);

        if (!$result = Bll_Cache::get($key)) {

            $result = null;
            $strUrl = SCHOOL_REMOTE_SERVER_HOST . '/mobile/boardserviceschoolapi/listashiato';
            $strParam = '?CF_uid=' . $uid . '&CF_size=' . $size . '&CF_valid=' . md5($uid . $size . $this->_secretKey) . '&CF_time=' . time();
            $client = new Zend_Http_Client($strUrl . $strParam, array(
                        'maxredirects' => self::MAX_REDIRECTS,
                        'timeout'      => self::TIMEOUT));

            try {
                $response = $client->request();
                if ($response->isSuccessful()) {
                    $result = Zend_Json::decode($response->getBody(), Zend_Json::TYPE_ARRAY);
                }
            }
            catch (Exception $e) {
                return null;
            }

            if ($result) {
                Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_ONE_MINUTE*5);
            }
        }

        return $result;
    }

	/**
     * get remote sever for api service - get ashiato count
     *
     * @param integer $uid
     * @return array
     */
    public function getAshiatoCount($uid)
    {
        $key = self::getCacheKey('getAshiatoCount', $uid);

        if (!$result = Bll_Cache::get($key)) {
            $result = null;
            $strUrl = SCHOOL_REMOTE_SERVER_HOST . '/mobile/boardserviceschoolapi/getashiatocount';
            $strParam = '?CF_uid=' . $uid . '&CF_valid=' . md5($uid . $this->_secretKey) . '&CF_time=' . time();
            $client = new Zend_Http_Client($strUrl . $strParam, array(
                        'maxredirects' => self::MAX_REDIRECTS,
                        'timeout'      => self::TIMEOUT));
            try {
                $response = $client->request();
                if ($response->isSuccessful()) {
                    $result = (int)$response->getBody();
                }
            }
            catch (Exception $e) {
                return 0;
            }

            if ($result) {
                Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_ONE_MINUTE*5);
            }
        }

        return $result;
    }


	/**
     * get remote sever for api service - is ashiato app user
     *
     * @param integer $uid
     * @return boolean
     */
    public function isAshiatoUser($uid)
    {
        $key = self::getCacheKey('isAshiatoUser', $uid);

        if (!$result = Bll_Cache::get($key)) {
            $result = null;
            $strUrl = SCHOOL_REMOTE_SERVER_HOST . '/mobile/boardserviceschoolapi/getashiatouser';
            $strParam = '?CF_uid=' . $uid . '&CF_valid=' . md5($uid . $this->_secretKey) . '&CF_time=' . time();
            $client = new Zend_Http_Client($strUrl . $strParam, array(
                        'maxredirects' => self::MAX_REDIRECTS,
                        'timeout'      => self::TIMEOUT));

            try {
                $response = $client->request();
                if ($response->isSuccessful()) {
                    $result = Zend_Json::decode($response->getBody(), Zend_Json::TYPE_ARRAY);
                }
            }
            catch (Exception $e) {
                return null;
            }

            if ($result) {
                Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_ONE_MINUTE*5);
            }
        }

        return !empty($result);
    }

	/**
     * get remote sever for api service - add ashiato
     *
     * @param integer $uid
     * @param integer $targetUid
     * @param string $comment
     * @return boolean
     */
    public function addAshiato($uid, $targetUid, $comment)
    {
        $result = null;
        $strUrl = SCHOOL_REMOTE_SERVER_HOST . '/mobile/boardserviceschoolapi/addashiato';
        $time = time();
        $strParam = '?CF_uid=' . $uid . '&CF_targetuid=' . $targetUid
                  . '&CF_comment=' . urlencode($comment)
                  . '&CF_time=' . $time
                  . '&CF_valid=' . md5($uid . $targetUid . $comment . $time . $this->_secretKey);
        $client = new Zend_Http_Client($strUrl . $strParam, array(
                    'maxredirects' => self::MAX_REDIRECTS,
                    'timeout'      => self::TIMEOUT));

        try {
            $response = $client->request();
            if ($response->isSuccessful()) {
                $result = $response->getBody();
            }
        }
        catch (Exception $e) {
            return null;
        }
        return $result;
    }

}
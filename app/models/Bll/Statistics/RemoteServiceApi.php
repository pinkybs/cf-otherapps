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
class Bll_Statistics_RemoteServiceApi
{
    const MAX_REDIRECTS = 3;
    const TIMEOUT = 2;

    private $_secretKey;

    private static $_prefix = 'Bll_Statistics_RemoteServiceApi';

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
     * get remote sever for api service - set app Statistics
     *
     * @param integer $uid
     * @param integer $targetUid
     * @param string $comment
     * @return boolean
     */
    public function setStatistics($app_id, $aryInfo)
    {
        $result = null;
        $strUrl = STATISTICS_REMOTE_SERVER_HOST . '/serviceapi/addstatistics';
        $strParam = '?CF_app_id=' . $app_id
                  . '&CF_report_date=' . $aryInfo['report_date']. '&CF_daily_access=' . $aryInfo['daily_access']
                  . '&CF_daily_login=' . $aryInfo['daily_login'] . '&CF_app_login=' . $aryInfo['app_login']
                  . '&CF_app_login_invite=' . $aryInfo['app_login_invite']
                  . '&CF_user_invite=' . $aryInfo['user_invite']
                  . '&CF_invited_other_user=' . $aryInfo['invited_other_user']
                  . '&CF_remove_app=' . $aryInfo['remove_app']. '&CF_others=' . urlencode($aryInfo['others'])
                  . '&CF_valid=' . md5($app_id . $aryInfo['report_date'] . $aryInfo['daily_access']
                  . $aryInfo['daily_login'] . $aryInfo['app_login']
                  . $aryInfo['app_login_invite'] . $aryInfo['user_invite'] .
                   $aryInfo['invited_other_user'] . $aryInfo['remove_app'] . $aryInfo['others']. $this->_secretKey);
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
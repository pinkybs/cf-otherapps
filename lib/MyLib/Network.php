<?php

require_once 'Zend/Http/Client.php';
/**
 * network utilty functions
 *
 * @package    MyLib
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/06/24     Hulj
 */
class MyLib_Network
{
    public static function validateUrl($url)
    {
        $client = new Zend_Http_Client();
        $config = array(
            'useragent'       => 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; )',
            'strictredirects' => true,
            'timeout'         => 5
        );
        $client->setConfig($config);
        $client->setMethod(Zend_Http_Client::HEAD);
        $client->setCookieJar(true);
        $client->setUri($url);
        
        try {
            $response = $client->request();
            if ($response->isSuccessful()) {
                return true;
            }
        }
        catch(exception $e) {
        }
        
        return false;
    }

}
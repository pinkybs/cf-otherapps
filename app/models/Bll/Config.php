<?php

/** @see Zend_Config_Xml */
require_once 'Zend/Config/Xml.php';

/**
 * config logic's Operation
 * get config
 * 
 * @package    Bll
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2008/07/28    HCH
 */
class Bll_Config
{

    /**
     * get college config xml
     *
     * @param string $xml
     * @param string $prefix
     *  college hostname
     * @return xml
     */
    public static function get($xml, $prefix = null)
    {        
        $config = new Zend_Config_Xml($xml, null);
        
        return $config;
    }
}
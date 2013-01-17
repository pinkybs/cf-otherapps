<?php

/** @see MyLib_Mobile_Japan_UA */
require_once 'MyLib/Mobile/Japan/UA.php';

/** @see MyLib_Mobile_Japan_Device */
require_once 'MyLib/Mobile/Japan/Device.php';

/**
 * Mobile Japan Device FlashLite version classify
 * 
 * @package    MyLib_Mobile
 * @subpackage Japan
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create    2009/10/20    Hulj
 */
class MyLib_Mobile_Japan_FlashLite
{
    /**
     * mobile phone ua type
     *
     * @var int
     */
    private $_ua;
    
    /**
     * invalid flashlite device list (flashlite < 1.1)
     *
     * @var object
     */
    private $_list;
    
    /**
     * constuct function
     *
     * @param int $ua
     * @param string $xml
     */
    public function __construct($ua = null, $xml = null)
    {
        if ($ua == null) {
            $ktaiUA = new MyLib_Mobile_Japan_UA();
            $ua = $ktaiUA->getUA();
        }
                
        $this->_ua = $ua;
        $this->_list = $this->getFlashLiteList($xml);
    }
    
    /**
     * convert object to array
     *
     * @param object $element
     * @return array
     */
    private function _getArray($element)
    {
        $arr = array();
        
        foreach ($element->children() as $name => $node) {
            $arr[] = (string)$node;
        }
        
        return $arr;
    }
    
    public function isValid($model = null)
    {        
        if($model == null) {
            $device = new MyLib_Mobile_Japan_Device();
            $model = $device->getDevice();
        }
        
        switch ($this->_ua) {
            case MyLib_Mobile_Japan_UA::DOCOMO:
                $list = $this->_getArray($this->_list->device->docomo);
                break;
            case MyLib_Mobile_Japan_UA::AU:
                $list = $this->_getArray($this->_list->device->au);
                break;
            case MyLib_Mobile_Japan_UA::SOFTBANK:
                $list = $this->_getArray($this->_list->device->softbank);
                break;
            default:
                $list = array();
                break;                    
        }
        
        return $this->_check($model, $list);
    }
    
    private function _check($model, $list)
    {
        if (empty($list)) {
            return true;
        }
        
        foreach ($list as $v) {
            if (strcasecmp($v, $model) == 0) {
                return false;
            }
        }
        
        return true;
    }
        
    public function getFlashLiteList($xml = null)
    {
        if($xml == null) {
            $xml = LIB_DIR . '/MyLib/Mobile/Japan/flashlite.xml';
        }
        
        return simplexml_load_file($xml);
    }
}
<?php

/**
 * Mobile Japan IP Check
 * 
 * @package    MyLib_Mobile
 * @subpackage Japan
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create    2008/07/18    Hulj
 */
class MyLib_Mobile_Japan_IP
{    
    /**
     * ip zone of one ua
     *
     * @var object
     */
    private $_ipZone;
    
    /**
     * constuct function
     *
     * @param object $ipZone
     */
    public function __construct($ipZone)
    {
        $this->_ipZone = $ipZone;
    }
    
    /**
     * convert object to array
     *
     * @param object $element
     * @return array
     */
    private function _getIPArray($element)
    {
        $arr = array();
        
        foreach ($element->children() as $name => $node) {
            $arr[] = (string)$node;
        }
        
        return $arr;
    }

    /**
     * get ip zone from ua
     *
     * @param int $ua
     * @return array
     */
    protected function _getZone($ua = null)
    {
        require_once('MyLib/Mobile/Japan/UA.php');
        if(is_null($ua)) {
            $ktaiUA = new MyLib_Mobile_Japan_UA();
            $ua = $ktaiUA->getUA();
        }

        switch ($ua) {
            case MyLib_Mobile_Japan_UA::DOCOMO:
                $arr = $this->_getIPArray($this->_ipZone->zone->docomo);
                break;
            case MyLib_Mobile_Japan_UA::AU:
                $arr = $this->_getIPArray($this->_ipZone->zone->au);
                break;
            case MyLib_Mobile_Japan_UA::SOFTBANK:
                $arr = $this->_getIPArray($this->_ipZone->zone->softbank);
                break;
            case MyLib_Mobile_Japan_UA::WILLCOM:
                $arr = $this->_getIPArray($this->_ipZone->zone->willcom);
                break;
            default:
                $arr = array();
                break;                    
        }
        
        return $arr;
    }
    
    /**
     * check ip address is valid
     *
     * @param array $ipList
     * @param string $addr
     * @return boolean
     */
    protected function _checkIP($ipList, $addr = null)
    {
        if (is_null($addr)) {
            $addr = $_SERVER['REMOTE_ADDR'];
        }

        $i = 0;
        $count = count($ipList);
        $flag = false;

        while ($i < $count) {
            list($ip, $sub) = explode('/', $ipList[$i]);
            list($mask, $plus) = $this->_switchtomask($sub);
            if ($mask === false && $plus === false) {
              return false;
            }
            
            $ip = explode('.', $ip);
            $mask = explode('.', $mask);
            
            $network[0] = bindec(decbin($ip[0]) & decbin($mask[0]));
            $network[1] = bindec(decbin($ip[1]) & decbin($mask[1]));
            $network[2] = bindec(decbin($ip[2]) & decbin($mask[2]));
            $network[3] = bindec(decbin($ip[3]) & decbin($mask[3]));
            
            $naddr = sprintf('%u', ip2long(implode('.', $network)));
            $baddr = $naddr + $plus -1;
            
            $addr = sprintf('%u', ip2long($addr));
        
            if ($naddr <= $addr && $addr <= $baddr) {
                $flag = true;
                break;
            }
            
            $i++;
        }
    
        return $flag;
    }

    /**
     * switch to mask
     *
     * @param int $sub
     * @return array
     */
    protected function _switchtomask($sub)
    {
        switch($sub) {
            case 32 :
                $mask = '255.255.255.255';
                $plus = 1;
                break;
            case 31 :
                $mask = '255.255.255.254';
                $plus = 2;
                break;
            case 30 :
                $mask = '255.255.255.252';
                $plus = 4;
                break;
            case 29 :
                $mask = '255.255.255.248';
                $plus = 8;
                break;
            case 28 :
                $mask = '255.255.255.240';
                $plus = 16;
                break;
            case 27 :
                $mask = '255.255.255.224';
                $plus = 32;
                break;
            case 26 :
                $mask = '255.255.255.192';
                $plus = 64;
                break;
            case 25 :
                $mask = '255.255.255.128';
                $plus = 128;
                break;
            case 24 :
                $mask = '255.255.255.0';
                $plus = 256;
                break;
            case 23 :
                $mask = '255.255.254.0';
                $plus = 512;
                break;
            case 22 :
                $mask = '255.255.252.0';
                $plus = 1024;
                break;
            case 21 :
                $mask = '255.255.248.0';
                $plus = 2048;
                break;
            case 20 :
                $mask = '255.255.240.0';
                $plus = 4096;
                break;
            case 19 :
                $mask = '255.255.224.0';
                $plus = 8192;
                break;
            case 18 :
                $mask = '255.255.192.0';
                $plus = 16384;
                break;
            case 17 :
                $mask = '255.255.128.0';
                $plus = 32768;
                break;
            case 16 :
                $mask = '255.255.0.0';
                $plus = 65536;
                break;
            case 15 :
                $mask = '255.254.0.0';
                $plus = 131072;
                break;
            case 14 :
                $mask = '255.252.0.0';
                $plus = 262144;
                break;
            case 13 :
                $mask = '255.248.0.0';
                $plus = 524288;
                break;
            case 12 :
                $mask = '255.240.0.0';
                $plus = 1048576;
                break;
            case 11 :
                $mask = '255.224.0.0';
                $plus = 2097152;
                break;
            case 10 :
                $mask = '255.192.0.0';
                $plus = 4194304;
                break;
            case 9 :
                $mask = '255.128.0.0';
                $plus = 8388608;
                break;
            case 8 :
                $mask = '255.0.0.0';
                $plus = 16777216;
                break;
            case 7 :
                $mask = '254.0.0.0';
                $plus = 33554432;
                break;
            case 6 :
                $mask = '252.0.0.0';
                $plus = 67108864;
                break;
            case 5 :
                $mask = '248.0.0.0';
                $plus = 134217728;
                break;
            case 4 :
                $mask = '240.0.0.0';
                $plus = 268435456;
                break;
            case 3 :
                $mask = '224.0.0.0';
                $plus = 536870912;
                break;
            case 2 :
                $mask = '192.0.0.0';
                $plus = 1073741824;
                break;
            case 1 :
                $mask = '128.0.0.0';
                $plus = 2147483648;
                break;
            case 0 :
                $mask = '0.0.0.0';
                $plus = 4294967296;
                break;
            default :
                $mask = false;
                $plus = false;
            break;
        }
        
        return array($mask, $plus);
    }
    
    /**
     * check ip is valid mobile ip
     *
     * @param int $ua
     * @param string $ip
     * @return boolean
     */
    public function isKtaiIP($ua = null, $ip = null)
    {
        $ipList = $this->_getZone($ua);
        
        return $this->_checkIP($ipList, $ip);
    }

}
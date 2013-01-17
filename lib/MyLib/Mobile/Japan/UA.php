<?php

/**
 * Mobile Japan UA classify
 * 
 * @package    MyLib_Mobile
 * @subpackage Japan
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create    2008/07/18    Hulj
 */
class MyLib_Mobile_Japan_UA
{
    const PC        = 0;  // PC: ua is pc agent
    const DOCOMO    = 1;  // Docomo: ua is docomo agent
    const SOFTBANK  = 2;  // Softbank: ua is softbank|vodafone agent
    const AU        = 3;  // Au: ua is au agent
    const WILLCOM   = 4;  // Willcom: ua is willcom
    const OHTER     = 4;  // Other: ua is other mobile agent     ||| modify by huch 2009-5-15
    
    /**
     * priorities where the keys are the
     * priority numbers and the values are the priority names
     * 
     * @var array
     */
    private $_priorities = array();
    
    /**
     * ua code of agent
     *
     * @var int
     */
    private $_ua = 0;
    
    /**
     * constuct function
     *
     * @param array $server
     * @return void
     */
    public function __construct($server = null)
    {
        $r = new ReflectionClass($this);
        $this->_priorities = array_flip($r->getConstants());
        
        if (is_null($server)) {
            $server = $_SERVER;
        }
        
        $this->_classify($server);
    }
    
    /**
     * classify agent
     *
     * @param array $server
     * @return void
     */
    protected function _classify($server)
    {
        $agent = $server['HTTP_USER_AGENT'];
        
        if (strpos($agent, 'DoCoMo') !== false) {
            $this->_ua = self::DOCOMO;
        }
        else if(strpos($agent, 'SoftBank') !== false || strpos($agent, 'Vodafone') !== false || 
            strpos($agent, 'J-PHONE') !== false || strpos($agent, 'MOT-') !== false) {
            $this->_ua = self::SOFTBANK;
        }
        else if(strpos($agent, 'KDDI-') !== false || strpos($agent, 'UP.Browser/') !== false) {
            $this->_ua = self::AU;
        }
        else if(strpos($agent, 'WILLCOM') !== false || strpos($agent, 'DDIPOCKET') !== false) {
            $this->_ua = self::WILLCOM;
        }
        else if(strpos($agent, 'L-MODE') !== false || strpos($agent, 'Nintendo Wii;') !== false || 
            strpos($agent, 'PlayStation Portable') !== false || strpos($agent, 'EGBROWSER') !== false || 
            strpos($agent, 'AveFront') !== false || strpos($agent, 'PLAYSTATION 3;') !== false || 
            strpos($agent, 'ASTEL') !== false || strpos($agent, 'PDXGW') !== false) {
            $this->_ua = self::OHTER;
        }else {
            $this->_ua = self::PC;
        }        
    }
    
    /**
     * get ua code
     *
     * @return int
     */
    public function getUA()
    {
        return $this->_ua;
    }
    
    /**
     * get ua name
     *
     * @return string
     */
    public function getUAName()
    {
        return $this->_priorities[$this->_ua];
    }
    
    /**
     * get ua name
     *
     * @return string
     */
    public function getUAType()
    {
        $agent = $_SERVER['HTTP_USER_AGENT'];
        if ($this->_ua == 1) {
            if($agent != false) {
                $aryStr = explode(' ',$agent);
                $str = explode('(',$aryStr[1]);
                return $str[0];
            }
            else{
                return false;
            }
        }
        else if ($this->_ua == 2) {
           if($agent != false) {
                $str = explode('/',$agent);
                return $str[2];
            }
            else{
                return false;
            }
        }
        else if ($this->_ua == 3) {
            if($agent != false) {
                $aryStr = explode(' ',$agent);
                $str = explode('-',$aryStr[0]);
                return $str[1];
            }
            else{
                return false;
            }
        }
        else {
            return false;
        }
    }
    
    /**
     * check is ktai(mobile)
     *
     * @return boolean
     */
    public function isKtaiUA()
    {
        return $this->_ua !== self::PC && $this->_ua !== self::OHTER;
    }
    
    /**
     * check is docomo
     *
     * @return boolean
     */
    public function isDoCoMo()
    {
        return $this->_ua === self::DOCOMO;
    }
    
    /**
     * check is softbank
     *
     * @return boolean
     */
    public function isSoftBank()
    {
        return $this->_ua === self::SOFTBANK;
    }

    /**
     * check is au
     *
     * @return boolean
     */
    public function isAu()
    {
        return $this->_ua === self::AU;
    }

    /**
     * check is wiilcom
     *
     * @return boolean
     */
    public function isWillCom()
    {
        return $this->_ua === self::WILLCOM;
    }    
    
}
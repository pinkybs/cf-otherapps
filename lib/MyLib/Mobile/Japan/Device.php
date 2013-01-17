<?php

/**
 * Mobile Japan Device classify
 * 
 * @package    MyLib_Mobile
 * @subpackage Japan
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create    2008/07/18    Hulj
 */
class MyLib_Mobile_Japan_Device
{
    /**
     * japan mobile device name
     *
     * @var string
     */
    private $_device = '';
    
    /**
     * constuct function
     *
     * @param array $server
     * @return void
     */
    public function __construct($server = null)
    {
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
        $device = '';
        $agent = $server['HTTP_USER_AGENT'];
                
        // DoCoMo
        if (strpos($agent, 'DoCoMo') !== false) {
            if (substr($agent, 7, 3) === '1.0') {
                if (strpos($agent, '/', 11) !== false) {
                    $device = substr($agent, 11, (strpos($agent, '/', 11) - 11));
                }
                else {
                    $device = substr($agent, 11);
                }
            }
            else if (substr($agent, 7, 3) === '2.0' && strpos($agent, '(', 11) >= 0) {
                $device = substr($agent, 11, (strpos($agent, '(', 11) - 11));
            }
            else {
                $device = substr($agent, 11);
            }
        }
        // au
        else if (strpos($agent, 'KDDI-') !== false || strpos($agent, 'UP.Browser/') !== false) {
            $device = substr($agent, (strpos($agent, '-') + 1), (strpos($agent, ' ') - strpos($agent, '-') - 1));
        }
        // SoftBank
        else if(strpos($agent, 'SoftBank') !== false || strpos($agent, 'Vodafone') !== false 
              || strpos($agent, 'J-PHONE') !== false || strpos($agent, 'MOT-') !== false) {
            if ($server['HTTP_X_JPHONE_MSNAME']) {
                $device = $server['HTTP_X_JPHONE_MSNAME'];
            }
        }
        
        $this->_device = $device;
    }
    
    /**
     * get mobile device name string
     *
     * @return string
     */
    public function getDevice()
    {
        return $this->_device;
    }
        
}
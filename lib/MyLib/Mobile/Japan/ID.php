<?php

/**
 * Mobile Japan ID classify
 * 
 * @package    MyLib_Mobile
 * @subpackage Japan
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create    2008/07/18    Hulj
 */
class MyLib_Mobile_Japan_ID
{
    /**
     * mobile serial number id
     *
     * @var string
     */
    private $_id = '';
    
    private $_docomo_guid = '';
    
    /**
     * constuct function
     *
     * @param array $server
     * @return void
     */
    public function __construct($server = null)
    {
        if(is_null($server)) {
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
        $id = '';
        $ua = $server['HTTP_USER_AGENT'];
                
        // DoCoMo
        if (!strncmp($ua, 'DoCoMo', 6)) {
            //set utn
            // mova
            if (substr($ua, 7, 3) === '1.0') {
                $pieces = explode('/', $ua);
                $ser = array_pop($pieces);

                if (!strncmp($ser, 'ser', 3)) {
                    $id = $ser;
                }
            }
            // FOMA
            elseif (substr($ua, 7, 3) === '2.0') {
                $icc = substr($ua, -24, -1);

                if (!strncmp($icc, 'icc', 3)) {
                    $id = $icc;
                }
            }
            
            // guid=ON (mobile phone number guid)
            if ($server['HTTP_X_DCMGUID']) {
                $this->_docomo_guid = $server['HTTP_X_DCMGUID'];
            }
        }
        // Vodafone(PDC)
        else if (!strncmp($ua, 'J-PHONE', 7)) {
            $pieces = explode('/', $ua);
            $piece_sn = explode(' ', $pieces[3]);
            $sn = array_shift($piece_sn);

            if (!strncmp($sn, 'SN', 2)) {
                $id = $sn;
            }
        }
        // Vodafone(3G)
        else if (!strncmp($ua, 'Vodafone', 8)) {
            $pieces = explode('/', $ua);
            $piece_sn = explode(' ', $pieces[4]);
            $sn = array_shift($piece_sn);

            if (!strncmp($sn, 'SN', 2)) {
                $id = $sn;
            }
        }
        // SoftBank
        else if (!strncmp($ua, 'SoftBank', 8)) {
            if ($server['HTTP_X_JPHONE_UID']) {
                $id = $server['HTTP_X_JPHONE_UID'];
            }
            else {
                $pieces = explode('/', $ua);
                $piece_sn = explode(' ', $pieces[4]);
                $sn = array_shift($piece_sn);
    
                if (!strncmp($sn, 'SN', 2)) {
                    $id = $sn;
                }
            }
        }
        // au
        else if (!strncmp($ua, 'KDDI', 4)
              || !strncasecmp($ua, 'up.browser', 10)
            ) {
            if ($server['HTTP_X_UP_SUBNO']) {
                $id = $server['HTTP_X_UP_SUBNO'];
            }
        }

        $this->_id = $id;    
    }
    
    /**
     * get mobile serial number id
     *
     * @return string
     */
    public function getID()
    {
        return $this->_id;
    }
    
    public function getDCMGUID()
    {
        return $this->_docomo_guid;
    }
        
}
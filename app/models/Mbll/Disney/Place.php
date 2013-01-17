<?php
/** @see Bll_Abstract.php */
require_once 'Bll/Abstract.php';

/**
 * disney place logic's Operation
 *
 * @package    Mbll
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/10/12    Liz
 */
class Mbll_Disney_Place extends Bll_Abstract
{
    /**
     * get distance by plance id
     *
     * @param integer $currentId
     * @param integer $targetId
     * @return integer
     */
    public function getDistanceByPid($currentId, $targetId)
    {
        require_once 'Mdal/Disney/Place.php';
        $mdalPlace = Mdal_Disney_Place::getDefaultInstance();
        
        //get place info by pid
        $currentInfo = $mdalPlace->getPlaceById($currentId);
        
        $targetInfo = $mdalPlace->getPlaceById($targetId);
        
        //get distance
        $distance = $this->getDistance($currentInfo['latitude'], $currentInfo['longitude'], $targetInfo['latitude'], $targetInfo['longitude']);
       
        return $distance;
    }

    public function getDistance($lat1, $lon1, $lat2, $lon2)  
    {        
        require_once 'MyLib/Geomobilejp/Converter.php';
        $s1 = new Geomobilejp_Converter($lat1, $lon1);
        $s1->format('degree');
        $lat1 = $s1->getLatitude();$lon1 = $s1->getLongitude();
        
        $s2 = new Geomobilejp_Converter($lat2, $lon2);
        $s2->format('degree');
        $lat2 = $s2->getLatitude();$lon2 = $s2->getLongitude();
        
        return round(sqrt(pow(($lat1 - $lat2)/0.0111, 2) + pow(($lon1 - $lon2)/0.0091, 2)), 0);
    }
    
    private function _rad($d)
    {  
        return $d * 3.1415926535898 / 180.0;
    }
    
    public function distanceLog($uid, $lat1, $lon1, $lat2, $lon2)
    {
        $log_name = date('Y-m-d') . '-location';
        if (!Zend_Registry::isRegistered($log_name)) {
            $writer = new Zend_Log_Writer_Stream(LOG_DIR . '/location/' . $log_name . '.log');
            $logger = new Zend_Log($writer);
            Zend_Registry::set($log_name, $logger);
        }
        else {
            $logger = Zend_Registry::get($log_name);
        }
    
        try {
            $msg = "UserID:$uid  LatF:$lat2  LongF:$lon2  LatN:$lat1  LongN:$lon1  Distance:" . $this->getDistance($lat1, $lon1, $lat2, $lon2);
            $logger->log($msg, Zend_Log::INFO);
        }
        catch (Exception $e) {
    
        }
    }
}
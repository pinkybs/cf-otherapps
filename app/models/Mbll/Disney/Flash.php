<?php
/** @see Bll_Abstract.php */
require_once 'Bll/Abstract.php';

/**
 * disney flash logic's Operation
 *
 * @package    Mbll
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/11/18    Liz
 */
class Mbll_Disney_Flash extends Bll_Abstract
{
    /**
     * get flash point type
     *
     * @param integer $uid
     * @return integer
     */
    public function getFlashPoint($uid)
    {
        require_once 'Mdal/Disney/Flash.php';
        $mdalFlash = Mdal_Disney_Flash::getDefaultInstance();
        
        //get user flash point info
        $userFlashPoint = $mdalFlash->getUserFlashPoint($uid);
        if ( $userFlashPoint['type'] > 0 ) {
            $result = array('flashType' => $userFlashPoint['type'],
                            'load' => 2);
        }
        else {
            $flashType = self::_randFlashType();
            
            $flashInfo = array('uid' => $uid,
                               'type' => $flashType,
                               'status' => 0);
            //insert user flash point info
            $mdalFlash->insertUserFlashPoint($flashInfo);
            
            $result = array('flashType' => $flashType,
                            'load' => 1);
        }
        
        return $result;
    }
    
    /**
     * set user flash point
     *
     * @param integer $uid
     * @return integer
     */
    public function setFlashPoint($uid)
    {
        $result = array('status' => -1);
        
        require_once 'Mdal/Disney/Flash.php';
        $mdalFlash = Mdal_Disney_Flash::getDefaultInstance();
        
        //get user flash point info
        $userFlashPoint = $mdalFlash->getUserFlashPoint($uid);
        if ( !$userFlashPoint ) {
            return array('status' => -2);
        }
        
        //get flash point info by type
        $flashPoint = $mdalFlash->getFlashPointInfoByType($userFlashPoint['type']);
        if ( !$flashPoint ) {
            return array('status' => -3);
        }
            
        require_once 'Mdal/Disney/User.php';
        $mdalUser = Mdal_Disney_User::getDefaultInstance();
        
        $flashDistance = $flashPoint['distance'];
        
        /*if ( $userFlashPoint['type'] == 1 ) {
            
        }
        else {
            $flashDistance = $flashPoint['distance'];
        }*/
        
        try {
            $this->_wdb->beginTransaction();
            
            //update user flash distance
            $mdalUser->updateUserFlashDistance($uid, $flashDistance);
            
            //update user game ticket count
            $mdalUser->updateUserGameTicket($uid, -1);
            
            //add user point
            $mdalUser->updateUserPoint($uid, 10);
            
            //delete user flash point
            $mdalFlash->deleteUserFlashPoint($uid);
            
            $this->_wdb->commit();
            
            $result['status'] = 1;
            $result['distance'] = $flashDistance;
            $result['flashType'] = $userFlashPoint['type'];
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return array('status' => -1);
        }
        
        //add ticket log
        require_once 'Mdal/Disney/Log.php';
    	$mdalLog = Mdal_Disney_Log::getDefaultInstance();
    	$mdalLog->insertTicket(array('uid'=>$uid, 'create_time'=>time(), 'distance'=>$flashDistance));
        	
        return $result;
    }
    
    public function _randFlashType()
    {
        $number = rand(1, 100);
        switch ($number) {
            //probability 2
            case $number >= 1 && $number <= 2 :
                $flashType = 1;
                break;
            //8
            case $number >= 3 && $number <= 10 :
                $flashType = 2;
                break;
            //10
            case $number >= 11 && $number <= 20 :
                $flashType = 3;
                break;
            //18
            case $number >= 21 && $number <= 38 :
                $flashType = 4;
                break;
            //25
            case $number >= 39 && $number <= 63 :
                $flashType = 5;
                break;
            //32
            case $number >= 64 && $number <= 95 :
                $flashType = 6;
                break;
            //5
            case $number >= 96 && $number <= 100 :
                $flashType = 7;
                break;
        }
        
        return $flashType;
    }

}
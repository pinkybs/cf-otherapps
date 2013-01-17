<?php
/** @see Bll_Abstract.php */
require_once 'Bll/Abstract.php';

/**
 * ship ship logic's Operation
 *
 * @package    Mbll
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/11/30    Liz
 */
class Mbll_Ship_Ship extends Bll_Abstract
{
    /**
     * buy ship
     * 
     * @param integer $uid
     * @param integer $shipId
     * @return array
     */

    public function buyShip($uid, $shipId, $payType, $appId)
    {
        $result = array('status' => -1);

        require_once 'Mdal/Ship/Ship.php';
        $mdalShip = new Mdal_Ship_Ship();

        $shipInfo = $mdalShip->getShipInfo($shipId);
        
        $result['shipName'] = $shipInfo['name'];
        $result['shipCavName'] = $shipInfo['cav_name'];
        $result['assetType'] = $payType;
        //check user this ship count
        $userShipCount = $mdalShip->getUserShipCountBySid($uid, $shipId);
        if ( $userShipCount >= 3 ) {
            $result['status'] = -2;
            return $result;
        }
        
        require_once 'Mdal/Ship/User.php';
        $mdalUser = new Mdal_Ship_User();
        //get user park info
        $shipUser = $mdalUser->getUserPark($uid);
        
        //check user asset
        if ( $payType == 1 ) {
            if ( $shipInfo['price'] <= 0 ) {
                return $result;
            }
            $result['remainAsset'] = $shipUser['asset'] - $shipInfo['price'];
            
            if ( $shipUser['asset'] < $shipInfo['price'] ) {
                $result['status'] = -3;
                return $result;
            }
        }
        else {
            if ( $shipInfo['diamond'] <= 0 ) {
                return $result;
            }
            $result['remainAsset'] = $shipUser['asset_diamond'] - $shipInfo['diamond'];
            
            if ( $shipUser['asset_diamond'] < $shipInfo['diamond'] ) {
                $result['status'] = -3;
                return $result;
            }
        }
        
        //get user usable ship count
        $userUsableShipCount = $mdalUser->getUserUsableShipCount($uid);
        //check user ship count
        if ( $userUsableShipCount >= 8 ) {
            $activation = 0;
            $result['status'] = -4;
            return $result;
        }
        else {
            $activation = 1;
        }
        
        $this->_wdb->beginTransaction();
        try {
            //update user asset
            if ( $result['assetType'] == 1 ) {
                $mdalUser->updateUserAsset(-$shipInfo['price'], $uid);
            }
            else {
                $mdalUser->updateUserDiamond(-$shipInfo['diamond'], $uid);
            }

            //insert into user ship
            $newShip = array('uid' => $uid,
                             'ship_id' => $shipId,
                             'activation' => $activation,
                             'create_time' => time());
            $mdalShip->insertUserShip($newShip);

            //update user ship count and price
            $mdalUser->updateUserShipInfo($uid);
            
            $this->_wdb->commit();
            $result['status'] = 1;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return array('status' => -1);
        }

        require_once 'Mdal/Ship/Feed.php';
        $mdalFeed = new Mdal_Ship_Feed();
        
        $create_time = date('Y-m-d H:i:s');
        //insert into minifeed
        $minifeed = array('uid' => $uid,
                          'template_id' => 73,
                          'actor' => $uid,
                          'title' => '{"shipName":"'. $shipInfo['name'] . '"}',
                          'create_time' => $create_time);
        $mdalFeed->insertMinifeed($minifeed);
        
        //require_once 'Bll/User.php';
        //$user = Bll_User::getPerson($uid);
        $title = '海賊船「' . $shipInfo['name'] . '号」を造船しました！';
        //海賊船「xx号」を造船しました！
        
        //send activity
        require_once 'Bll/Restful.php';
        //get restful object
        $restful = Bll_Restful::getInstance($uid, $appId);
        $picUrl = Zend_Registry::get('static') . "/apps/ship/img/ship/" . $shipInfo['cav_name'] . "_s.gif";
        $restful->createActivityWithPic(array('title'=>$title), $picUrl, 'image/gif');

        return $result;
    }

    /**
     * change ship
     * 
     * @param integer $uid
     * @param integer $userShipId
     * @param integer $shipId
     * @return array
     */
    public function changeShip($uid, $userShipId, $shipId)
    {
        $result = array('status' => -1);

        require_once 'Mdal/Ship/Ship.php';
        $mdalShip = Mdal_Ship_Ship::getDefaultInstance();

        //get user shipinfo by user ship id
        $userShipInfo = $mdalShip->getShipByUserShipId($userShipId);
        //check is user's ship
        if ( $userShipInfo['uid'] != $uid ) {
            return array('status' => -1);
        }
        //check the ship status
        if ( $userShipInfo['status'] != 1 ) {
            return array('status' => -2);
        }
        
        //check user this ship count
        $newShipCount = $mdalShip->getUserShipCountBySid($uid, $shipId);
        if ( $newShipCount >= 3 ) {
            return array('status' => -3);
        }
        
        //get new ship info by ship id
        $newShipInfo = $mdalShip->getShipInfo($shipId);
        
        $oldShipPrice = floor($userShipInfo['price'] * 0.9);
        
        require_once 'Mdal/Ship/User.php';
        $mdalUser = new Mdal_Ship_User();
        
        //check user hava enough asset
        $shipUser = $mdalUser->getUserPark($uid);
        if ( $shipUser['asset'] + $oldShipPrice < $newShipInfo['price'] ) {
            return array('status' => -4);
        }
        
        //change ship
        $this->_wdb->beginTransaction();
        try {
            //update user asset
            $mdalUser->updateUserAsset(-($newShipInfo['price'] - $oldShipPrice), $uid);

            //delete user old ship
            $mdalShip->deleteUserShips($uid, $userShipId);

            //insert into user ship
            $newShip = array('uid' => $uid,
                             'ship_id' => $shipId,
                             'create_time' => time());
            $newUserShipId = $mdalShip->insertUserShip($newShip);

            //update user parking info
            $mdalShip->updateParkingInfo($uid, $userShipId, $newUserShipId, $shipId);
            
            //update user ship count and price
            $mdalUser->updateUserShipInfo($uid);
            
            $this->_wdb->commit();
            $result['status'] = 1;
            $result['shipName'] = $newShipInfo['name'];
            $result['shipCavName'] = $newShipInfo['cav_name'];
            $result['remainAsset'] = $shipUser['asset'] + $oldShipPrice - $newShipInfo['price'];
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return array('status' => -1);
        }
        
        require_once 'Mdal/Ship/Feed.php';
        $mdalFeed = new Mdal_Ship_Feed();
        
        $create_time = date('Y-m-d H:i:s');
        //insert into minifeed
        $minifeed = array('uid' => $uid,
                          'template_id' => 73,
                          'actor' => $uid,
                          'title' => '{"shipName":"'. $newShipInfo['name'] . '"}',
                          'create_time' => $create_time);
        $mdalFeed->insertMinifeed($minifeed);
        
        return $result;
    }
    
    /**
     * repair ship
     * 
     * @param integer $uid
     * @param integer $userShipId
     * @return array
     */
    public function repair($uid, $userShipId)
    {
        $result = array('status' => -1);

        require_once 'Mdal/Ship/Ship.php';
        $mdalShip = Mdal_Ship_Ship::getDefaultInstance();

        //get user shipinfo by user ship id
        $userShipInfo = $mdalShip->getShipByUserShipId($userShipId);
        
        if ( $userShipInfo['status'] == 1 ) {
            return array('status' => -1);
        }
        
        require_once 'Mdal/Ship/User.php';
        $mdalUser = new Mdal_Ship_User();
        //check user hava enough asset
        $shipUser = $mdalUser->getUserPark($uid);
        if ( $shipUser['asset'] < $userShipInfo['price'] * 0.2 ) {
            return array('status' => -2);
        }
        
        //change ship
        $this->_wdb->beginTransaction();
        try {
            //update user ship status
            $mdalShip->updateUserShipStatus($uid, $userShipId, 1);
            
            //update user ship price
            $mdalUser->updateShipPrice(-$userShipInfo['price'], $uid);
                        
            //update user asset
            $mdalUser->updateUserAsset(-$userShipInfo['price']*0.2, $uid);
            
            $this->_wdb->commit();
            $result['status'] = 1;
            $result['shipName'] = $userShipInfo['shipName'];
            $result['shipCavName'] = $userShipInfo['cav_name'];
            $result['remainAsset'] = $shipUser['asset'] - $userShipInfo['price']*0.2;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return array('status' => -1);
        }
        return $result;
    }
    
    /**
     * activation ship
     * 
     * @param integer $uid
     * @param integer $userShipId
     * @return array
     */
    public function activationShip($uid, $userShipId)
    {
        $result = array('status' => -1);

        require_once 'Mdal/Ship/Ship.php';
        $mdalShip = Mdal_Ship_Ship::getDefaultInstance();

        //get user shipinfo by user ship id
        $userShipInfo = $mdalShip->getShipByUserShipId($userShipId);
        
        $result['shipName'] = $userShipInfo['shipName'];
        $result['shipCavName'] = $userShipInfo['cav_name'];
        
        require_once 'Mdal/Ship/User.php';
        $mdalUser = new Mdal_Ship_User();
        //get user usable ship count
        $userUsableShipCount = $mdalUser->getUserUsableShipCount($uid);
        if ( $userUsableShipCount >= 8 ) {
            $result['status'] = -2;
            return $result;
        }
        
        $ship = array('activation' => 1);
        //update user ship 
        $mdalShip->updateUserShipByUserShipId($userShipId, $ship);
        
        $result['status'] = 1;
        return $result;
    }
    
    /**
     * prohibit ship
     * 
     * @param integer $uid
     * @param integer $userShipId
     * @return array
     */
    public function prohibitShip($uid, $userShipId)
    {
        $result = array('status' => -1);

        require_once 'Mdal/Ship/Ship.php';
        $mdalShip = Mdal_Ship_Ship::getDefaultInstance();

        //get user shipinfo by user ship id
        $userShipInfo = $mdalShip->getShipByUserShipId($userShipId);
        
        $result['shipName'] = $userShipInfo['shipName'];
        $result['shipCavName'] = $userShipInfo['cav_name'];
        
        require_once 'Mdal/Ship/User.php';
        $mdalUser = new Mdal_Ship_User();
        //get user usable ship count
        $userUsableShipCount = $mdalUser->getUserUsableShipCount($uid);
        if ( $userUsableShipCount <= 1 ) {
            $result['status'] = -2;
            return $result;
        }
        
        //get ship last park info
        $lastParkInfo = $mdalShip->getParkInfo($uid, $userShipId);
                        
        //change ship
        $this->_wdb->beginTransaction();
        try {
            
            if (count($lastParkInfo) > 0) {
                //update user asset
                $time = floor((time()-$lastParkInfo['parked_time'])/900);
                $time = $time>32 ? 32 : $time;
    
                //get money
                $money = $time*$lastParkInfo['fee']*$userShipInfo['times'];
                //update user asset
                $mdalUser->updateUserAsset($money, $uid);
                //delete last parking info
                $mdalShip->deleteParkingInfo($lastParkInfo['sid']);
            }
        
            $ship = array('activation' => 0);
            //update user ship 
            $mdalShip->updateUserShipByUserShipId($userShipId, $ship);
             
            $this->_wdb->commit();
            $result['status'] = 1;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return array('status' => -1);
        }
        return $result;
    }
    
}
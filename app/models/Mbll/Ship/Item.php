<?php
/** @see Bll_Abstract.php */
require_once 'Bll/Abstract.php';

/**
 * ship item logic's Operation
 *
 * @package    Mbll
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/11/30    Liz
 */
class Mbll_Ship_Item extends Bll_Abstract
{
    /**
     * buy item
     *
     * @param integer $uid
     * @param integer $sid
     * @return array
     */
    public function buyItem($uid, $sid)
    {
        $result = array('status' => -1);
        
        require_once 'Mdal/Ship/Item.php';
        $mdalItem = Mdal_Ship_Item::getDefaultInstance();
            
        //get card info
        $cardInfo = $mdalItem->getCardInfo($sid);
        
        require_once 'Mdal/Ship/User.php';
        $mdalUser = new Mdal_Ship_User();
        //check user hava enough asset
        $shipUser = $mdalUser->getUserPark($uid);
        
        $result['cardName'] = $cardInfo['name'];
        $result['price'] = $cardInfo['price'];
        $result['diamond'] = $cardInfo['diamond'];
        $result['introduce'] = $cardInfo['introduce'];
        $result['sid'] = $cardInfo['sid'];
        
        if ( $cardInfo['price'] > 0 ) {
            $result['remainAsset'] = $shipUser['asset'] - $cardInfo['price'];
            $result['assetType'] = 1;
        }
        else {
            $result['remainAsset'] = $shipUser['asset_diamond'] - $cardInfo['diamond'];
            $result['assetType'] = 2;
        }
        
        if ( !$cardInfo ) {
            $result['status'] = -1;
            return $result;
        }
        
        $payType = 1;
        //check user asset
        if ( $cardInfo['diamond'] > 0 ) {
            $payType = 2;
            
            if ( $shipUser['asset_diamond'] < $cardInfo['diamond'] ) {
                $result['status'] = -2;
                return $result;
            }
        }
        else if ( $shipUser['asset'] < $cardInfo['price'] ) {            
            $result['status'] = -2;
            return $result;
        }
            
        $this->_wdb->beginTransaction();
        try {
            //update user card count by cid
            $mdalItem->updateUserCardCoutBySid($sid, $uid, 1);
            
            //update user asset,type == 1->diamond, 2->price
            if ( $payType == 1 ) {
                $mdalUser->updateUserAsset(-$cardInfo['price'], $uid);
            }
            else {
                $mdalUser->updateUserDiamond(-$cardInfo['diamond'], $uid);
            }

            $this->_wdb->commit();
            $result['status'] = 1;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return $result;
        }
        return $result;
    }
    
    /**
     * use item
     *
     * @param integer $uid
     * @param integer $sid
     * @return array
     */
    public function useItem($uid, $sid)
    {
        $result = array('status' => -1);
        
        require_once 'Mdal/Ship/Item.php';
        $mdalItem = Mdal_Ship_Item::getDefaultInstance();
            
        //get card info
        $cardInfo = $mdalItem->getCardInfo($sid);
        
        $result['cardName'] = $cardInfo['name'];
        $result['cardIntroduce'] = $cardInfo['introduce'];
        $result['cardId'] = $cardInfo['sid'];
            
        if ( !$cardInfo ) {
            $result['status'] = -1;
            return $result;
        }
        
        //get user item count by id
        $cardCount = $mdalItem->getUserItemCountBySid($uid, $sid);
        if ( $cardCount < 1 ) {
            $result['status'] = -2;
            return $result;
        }
        
        require_once 'Mdal/Ship/User.php';
        $mdalUser = new Mdal_Ship_User();
        //check user hava enough asset
        $shipUser = $mdalUser->getUserPark($uid);
        
        if ( $sid == 2 ) {
            $useCardResult = $this->useBriberyCard($uid, $sid, $shipUser);
        }
        else if ( $sid == 3 ) {
            $useCardResult = $this->useBombCard($uid, $sid);
            
        }
        else if ( $sid == 4 ) {
            $useCardResult = $this->useInsuranceCard($uid, $sid);
        }
        
        $result['status'] = $useCardResult;
        
        return $result;
    }
    
    /**
     * use bribery card
     *
     * @param integer $uid
     * @param integer $sid
     * @param array $shipUser
     * @return array
     */
    public function useBriberyCard($uid, $sid, $shipUser)
    {
        $result = -1;
        if ( time() - $shipUser['last_bribery_time'] <= 3*24*60*60 ) {
            return -1;
        }
        
        require_once 'Mdal/Ship/User.php';
        $mdalUser = new Mdal_Ship_User();
        
        require_once 'Mdal/Ship/Item.php';
        $mdalItem = Mdal_Ship_Item::getDefaultInstance();
        
        $this->_wdb->beginTransaction();
        try {
            $user = array('last_bribery_time' => time());
            
            //update user asset
            $mdalUser->updateShipUser($uid, $user);
            
            //update user card count by sid
            $mdalItem->updateUserCardCoutBySid($sid, $uid, -1);
            
            $this->_wdb->commit();
            
            $result = 1;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return $result;
        }
        return $result;
    }

    /**
     * use bomb card
     *
     * @param integer $uid
     * @param integer $sid
     * @return array
     */
    public function useBombCard($uid, $sid)
    {
        $result = -1;

        //check user have card
        require_once 'Mdal/Ship/Item.php';
        $mdalItem = Mdal_Ship_Item::getDefaultInstance();
        
        //check location
        require_once 'Mdal/Ship/User.php';
        $mdalUser = new Mdal_Ship_User();
        $bombUser = $mdalUser->getUserBombLocation($uid);

        require_once 'Mdal/Ship/Ship.php';
        $mdalShip = new Mdal_Ship_Ship();
        //get user parking location info
        $parkLocation = $mdalShip->getPakingLocation($uid);

        $bombLocation = array();
        for ($i = 1; $i <= $bombUser['ship']; $i++) {
            $canBomb = 1;
            //this location don't have ship
            foreach ($parkLocation AS $key) {
                if ( $key['location'] == $i ) {
                    $canBomb = 0;
                }
            }
            
            //this location don't have bomb
            if ( $canBomb == 1 && $bombUser['location' . $i] != 1 ) {
                $bombLocation[] = $i;
            }
        }

        if ( !$bombLocation ) {
            return -3;
        }

        $this->_wdb->beginTransaction();
        try {
            //update user card count by sid
            $mdalItem->updateUserCardCoutBySid($sid, $uid, -1);

            //update parking_user_bomb
            $rand = rand(0, count($bombLocation)-1);

            $array = array('location' . $bombLocation[$rand] => 1);
            $mdalItem->updateUserBomb($uid, $array);

            $this->_wdb->commit();
            $result = 1;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
        }

        return $result;
    }

    /**
     * use insurance card
     *
     * @param integer $uid
     * @param integer $sid
     * @return array
     */
    public function useInsuranceCard($uid, $sid)
    {                
        require_once 'Mdal/Ship/Item.php';
        $mdalItem = Mdal_Ship_Item::getDefaultInstance();
        
        $this->_wdb->beginTransaction();
        try {            
            //update user insurance count
            $mdalItem->updateUserInsuranceCount($uid, 1);
            
            //update user card count by sid
            $mdalItem->updateUserCardCoutBySid($sid, $uid, -1);
            
            $this->_wdb->commit();
            
            $result = 1;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return -1;
        }
        return $result;
    }

    /**
     * buy island
     * 
     * @param integer $uid
     * @param integer $id
     * @return array
     */
    public function buyIsland($uid, $id, $payType, $appId)
    {
        $result = array('status' => -1);

        require_once 'Mdal/Ship/Item.php';
        $mdalItem = Mdal_Ship_Item::getDefaultInstance();

        //get island info
        $islandInfo = $mdalItem->getIslandInfo($id);
        
        $result['islandName'] = $islandInfo['name'];
        $result['islandId'] = $islandInfo['id'];
        $result['assetType'] = $payType;
        
        if ( !$islandInfo ) {
            return $result;
        }
        
        //get user island by island id
        $userIsland = $mdalItem->getUserIslandInfoById($uid, $id);
        if ( $userIsland ) {
            $result['status'] = -5;
            return $result;
        }
        
        //Computate balance between old island and new island
        $userIslandInfo = $mdalItem->getUserIslandInfo($uid);
        //$balance = $islandInfo['price'] - $userIslandInfo['price'] * 0.9;
        
        require_once 'Mdal/Ship/User.php';
        $mdalUser = new Mdal_Ship_User();
        //check user hava enough asset
        $shipUser = $mdalUser->getUserPark($uid);

        if ( $shipUser['background'] == $id ) {
            $result['status'] = -2;
            return $result;
        }

        //check ship location count
        if ( $islandInfo['ship'] < $userIslandInfo['ship'] ) {
            $result['status'] = -3;
            return $result;
        }
        
        //check user have enough asset
        if ( $payType == 1 ) {
            $result['remainAsset'] = $shipUser['asset'] - $islandInfo['price'];
            if ( $shipUser['asset'] < $islandInfo['price'] ){
                $result['status'] = -4;
                return $result;
            }
        }
        else {
            $result['remainAsset'] = $shipUser['asset_diamond'] - $islandInfo['diamond'];
            if ( $shipUser['asset_diamond'] < $islandInfo['diamond'] ) {
                $result['status'] = -4;
                return $result;
            }
        }
        
        require_once 'Mdal/Ship/Island.php';
        $mdalIsland = Mdal_Ship_Island::getDefaultInstance();
                
        try {
            $this->_wdb->beginTransaction();
            
            if ( $payType == 1 ) {
                $user = array('asset' => $shipUser['asset'] - $islandInfo['price'], 
                              'background' => $id);
            }
            else {
                $user = array('asset_diamond' => $shipUser['asset_diamond'] - $islandInfo['diamond'],
                              'background' => $id);
            }
            
            //update user parking background
            $mdalUser->updateShipUser($uid, $user);
            
            //insert user new island
            $newIsland = array('uid' => $uid, 'bg_id' => $islandInfo['id'], 'create_time' => time());
            $mdalIsland->insertUserIsland($newIsland);
            
            $this->_wdb->commit();
            $result['status'] = 1;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return $result;
        }
        
        require_once 'Mdal/Ship/Feed.php';
        $mdalFeed = new Mdal_Ship_Feed();
        
        $create_time = date('Y-m-d H:i:s');

        //insert into minifeed
        $minifeed = array('uid' => $uid,
                          'template_id' => 72,
                          'actor' => $uid,
                          'title' => '{"islandName":"'. $islandInfo['name'] . '","shipCount":"'. $islandInfo['ship'] . '","fee":"' . number_format($islandInfo['fee']) . '"}',
                          'icon' => Zend_Registry::get('static') . "/apps/parking/img/icon/island.gif",
                          'create_time' => $create_time);
        $mdalFeed->insertMinifeed($minifeed);

        $title = '海賊島「' . $islandInfo['name'] . '」を開拓しました！';
        //海賊島「xxx」を開拓しました！
        
        //send activity
        require_once 'Bll/Restful.php';
        //get restful object
        $restful = Bll_Restful::getInstance($uid, $appId);
        $restful->createActivity(array('title'=>$title));
        
        return $result;
    }

    /**
     * change island
     * 
     * @param integer $uid
     * @param integer $id
     * @return array
     */
    public function changeIsland($uid, $id)
    {
        $result = array('status' => -1);

        require_once 'Mdal/Ship/Item.php';
        $mdalItem = Mdal_Ship_Item::getDefaultInstance();

        //get island info
        $islandInfo = $mdalItem->getIslandInfo($id);
    
        if ( !$islandInfo ) {
            return $result;
        }
        
        $result['islandName'] = $islandInfo['name'];
        $result['islandId'] = $islandInfo['id'];
        
        //get user island info
        $userIslandInfo = $mdalItem->getUserIslandById($uid, $id);
        if ( !$userIslandInfo ) {
            return $result;
        }
        
        require_once 'Mdal/Ship/User.php';
        $mdalUser = Mdal_Ship_User::getDefaultInstance();
        
        //update user island
        $user = array('background' => $userIslandInfo['id']);
        $mdalUser->updateShipUser($uid, $user);
        
        $result['status'] = 1;
        return $result;
     }
}
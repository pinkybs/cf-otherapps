<?php
/** @see Bll_Abstract.php */
require_once 'Bll/Abstract.php';

/**
 * Ship user logic's Operation
 *
 * @package    Mbll
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/11/30    Liz
 */
class Mbll_Ship_User extends Bll_Abstract
{
    /**
     * check user in joined
     *
     * @param integer $uid
     * @return boolean
     */
    public function isJoined($uid)
    {
        require_once 'Mdal/Ship/User.php';
        $mdalShipUser = Mdal_Ship_User::getDefaultInstance();
        //get user info
        return $mdalShipUser->isInApp($uid);
    }

    /**
     * join app
     *
     * @param integer $uid
     * @return boolean
     */
    public function join($uid, $appId)
    {
        $result = false;

        try {
            $this->_wdb->beginTransaction();

            require_once 'Mdal/Ship/Island.php';
            $mdalIsland = Mdal_Ship_Island::getDefaultInstance();
            $island = $mdalIsland->getIslandByType('A');

            $ship = array('uid' => $uid,
                          'asset' => 1000,
                          'asset_diamond' => 2,
                          'background' => $island[rand(0, count($island)-1)]['id'],
                          'free_park' => 0,
                          'neighbor_left' => -1,
                          'neighbor_right' => -2,
                          'neighbor_center' => -3);
            
            require_once 'Mdal/Ship/User.php';
            $mdalUser = Mdal_Ship_User::getDefaultInstance();
            $mdalUser->insertShipUser($ship, $uid);

            //insert user new island
            $newIsland = array('uid' => $uid, 'bg_id' => $ship['background'], 'create_time' => time());
            $mdalIsland->insertUserIsland($newIsland);
            
            $ship = array('uid' => $uid,
                          'ship_id' => 1,
                          'create_time' => time());

            require_once 'Mdal/Ship/Ship.php';
            $mdalShip = new Mdal_Ship_Ship();

            $mdalShip->insertUserShip($ship);

            $mdalUser->updateUserShip($uid);

            $this->_wdb->commit();
            $result = true;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return $result;
        }
        
        require_once 'Bll/User.php';
        $user = Bll_User::getPerson($uid);
        $title = $user->getDisplayName() . 'さんが「海賊船」を追加しました';
        
        require_once 'Bll/Restful.php';
        //get restful object
        $restful = Bll_Restful::getInstance($uid, $appId);
        $restful->createActivity(array('title'=>$title));
        
        return $result;
    }
    
    /**
     * invite user
     *
     * @param integer $uid
     * @param string $recipientIds
     * @return boolean
     */
    public function invite($uid, $recipientIds)
    {
        $result = false;
        
        $inviteArray = explode(',', $recipientIds);
        
        if ( !$inviteArray ) {
            return $result;
        }
        
        require_once 'Mdal/Ship/Invite.php';
        $mdalInvite = Mdal_Ship_Invite::getDefaultInstance();
        
        try {
            $this->_wdb->beginTransaction();
            
            //insert invite info
            for ( $i = 0, $iCount = count($inviteArray); $i < $iCount; $i++ ) {
                $mdalInvite->insertInvite($uid, $inviteArray[$i]);
            }
        
            $this->_wdb->commit();
            $result = true;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return $result;
        }
        
        return $result;
    }
    
    /**
     * invite user
     *
     * @param integer $uid
     * @param string $recipientIds
     * @return boolean
     */
    public function inviteComplete($uid, $inviteUid)
    {
        $result = false;
        
        //check invite
        $haveInviteGift = 0;
        require_once 'Mdal/Ship/Invite.php';
        $mdalInvite = Mdal_Ship_Invite::getDefaultInstance();
        //get invite info 
        $inviteInfo = $mdalInvite->getInviteInfo($inviteUid, $uid);
        if ( $inviteInfo && $inviteInfo['status'] != 1 ) {
            $haveInviteGift = 1;
        }
        else {
            return $result;
        }

        require_once 'Mdal/Ship/User.php';
        $mdalUser = Mdal_Ship_User::getDefaultInstance();
    
        try {
            $this->_wdb->beginTransaction();
        
            //send invite gift
            if ( $haveInviteGift == 1 ) {
                //add 1 diamond
                $mdalUser->updateUserDiamond(1, $inviteUid);
                //update invite info status
                $mdalInvite->updateInviteStatus($inviteUid, $uid);
            }
        
            $this->_wdb->commit();
            $result = true;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return $result;
        }
        
        return $result;
    }

    /**
     * invite Gift user
     *
     * @param integer $uid
     * @return boolean
     */
    public function inviteGift($uid)
    {
        $result = false;
        
        //check invite
        require_once 'Mdal/Ship/Invite.php';
        $mdalInvite = Mdal_Ship_Invite::getDefaultInstance();
        //get invite info 
        $giftCount = $mdalInvite->getInviteHaveNotGiftCount($uid);
        if ( $giftCount <= 0 ) {
            return $result;
        }

        require_once 'Mdal/Ship/User.php';
        $mdalUser = Mdal_Ship_User::getDefaultInstance();
    
        try {
            $this->_wdb->beginTransaction();
            
            //update invite info status
            $count = $mdalInvite->updateInviteGift($uid);
            
            //add diamond
            $mdalUser->updateUserDiamond($count, $uid);
            
            $this->_wdb->commit();
            $result = true;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return $result;
        }
        
        return $result;
    }
    
    /**
     * remove user
     *
     * @param integer $uid
     * @return boolean
     */
    public function removeShipUser($uid)
    {
        
    }
}
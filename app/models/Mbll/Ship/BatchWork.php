<?php

/**
 * Mbll BatchWork
 * DB Auto Statistic Batch Work Logic Layer
 *
 * @package    Mbll/Ship
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/11/30    Liz
 */
class Mbll_Ship_BatchWork extends Bll_Abstract
{  
    /**
     * calculate user ship park fee
     *
     * @param array $userPark
     * @return array
     */
    public function calculateFee($userPark)
    {
        //get user current ship info
        $currentShip = array();
        
        for ( $j = 1; $j <= $userPark['user']['locaCount']; $j++ ) {
            $currentShip[$j] = array('sid' => 0);
        }
        
        $countUserPark = count($userPark['ship']);
        
        for ( $i = 0; $i < $countUserPark; $i++ ) {
            if ( $userPark['ship'][$i]['location'] == $userPark['user']['free_park'] ) {
                $money = 0;
                $temp = 0;
            }
            else {
                //get ship parked money
                $time = floor($userPark['ship'][$i]['parked_time']/900);
                $hour = $time > 32 ? 32 : $time;
                $temp = $hour/4 + 1;
                $temp = floor($temp);
                $money = round($hour * $userPark['user']['fee'] * $userPark['ship'][$i]['times']);
            }
            
            
            $userPark['ship'][$i]['park_money'] = $money;
            $userPark['ship'][$i]['temp'] = $temp;
            $currentShip[$userPark['ship'][$i]['location']] = $userPark['ship'][$i];
        }
        
        return $currentShip;
    }

    /**
     * batch report
     *
     * @param integer $uid
     */
    public function report($uid)
    {
        //check ship self reported
        require_once 'Mdal/Ship/Report.php';
        $mdalReport = Mdal_Ship_Report::getDefaultInstance();
        $report = $mdalReport->getReportByUid($uid);
        
        foreach ($report as $item) {
            if (time() - $item['create_time'] >= 3600 && time() - $item['last_bribery_time'] > 3*24*3600 ) {
                $time = floor(($item['create_time']-$item['parked_time']+3600)/900);
                $time = $time > 32 ? 32 : $time;
                $fee = round($item['fee'] * $time * 1.2, 0);
                $this->dealReport($item['sid'], $item['report_uid'], $fee, $item['uid'], $item['shipName'], $item['anonymous']);
            }
        }
        
        
        //check ship self
        require_once 'Mdal/Ship/User.php';
        $mdalUser = Mdal_Ship_User::getDefaultInstance();
        $shipUser = $mdalUser->getUserPark($uid);
        if ( time() - $shipUser['last_bribery_time'] > 3*24*3600 ) {
            require_once 'Mdal/Ship/Ship.php';
            $mdalShip = Mdal_Ship_Ship::getDefaultInstance();
            $reportSid = $mdalShip->getShipSidByUid($uid);
            if (empty($reportSid)) return;
            
            require_once 'Mdal/Ship/Report.php';
            $mdalReport = Mdal_Ship_Report::getDefaultInstance();
            $report = $mdalReport->getReportBySid($reportSid);
            if (empty($report)) return;
            
            foreach ($report as $item) {
                if (time() - $item['create_time'] >= 3600) {
                    $time = floor(($item['create_time']-$item['parked_time']+3600)/900);
                    $time = $time > 32 ? 32 : $time;
                    $fee = round($item['fee'] * $time * 1.2, 0);
                    $this->dealReport($item['sid'], $item['report_uid'], $fee, $item['uid'], $item['shipName'], $item['anonymous']);
                }
            }
        }
    }
    
    public function reportBoatShip($uid)
    {
        //check fid's boat house
        if ($uid <= 0) return;
        
        require_once 'Mdal/Ship/Ship.php';
        $mdalShip = Mdal_Ship_Ship::getDefaultInstance();
        $reportSid = $mdalShip->getShipSidByParkingUid($uid);
        if (empty($reportSid)) return;
        
        require_once 'Mdal/Ship/Report.php';
        $mdalReport = Mdal_Ship_Report::getDefaultInstance();
        $report = $mdalReport->getReportBySid($reportSid);
        if (empty($report)) return;
        
        foreach ($report as $item) {
            if (time() - $item['create_time'] >= 3600 && time() - $item['last_bribery_time'] > 3*24*3600 ) {
                $time = floor(($item['create_time']-$item['parked_time']+3600)/900);
                $time = $time > 32 ? 32 : $time;
                $fee = round($item['fee'] * $time * 1.2, 0);
                $this->dealReport($item['sid'], $item['report_uid'], $fee, $item['uid'], $item['shipName'], $item['anonymous']);
            }
        }
    }
    
    /**
     * private deal report
     *
     * @param integer $sid
     * @param integer $uid
     * @param integer $asset
     * @return boolean
     */
    private function dealReport($sid, $uid, $asset, $parkingUid, $shipName, $isAnonymous)
    {
        $result = false;
        $this->_wdb->beginTransaction();
        
        try {
            //update user asset
            require_once 'Mdal/Ship/User.php';
            $mdalUser = Mdal_Ship_User::getDefaultInstance();
            $mdalUser->updateUserAsset($asset, $uid);
            
            //delete ship
            require_once 'Mdal/Ship/Ship.php';
            $mdalShip = Mdal_Ship_Ship::getDefaultInstance();
            $mdalShip->deleteUserShip($sid);            

            //delete report
            require_once 'Mdal/Ship/Report.php';
            $mdalReport = Mdal_Ship_Report::getDefaultInstance();
            $mdalReport->deleteReportBySid($sid);
            
            $this->_wdb->commit();
            $result = true;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
        }
        
        require_once 'Mdal/Ship/Feed.php';
        $mdalFeed = new Mdal_Ship_Feed();
        $create_time = date('Y-m-d H:i:s');
        
        $templateId1 = $isAnonymous == 1 ? 65 : 63;
        $minifeed = array('uid' => $uid,
                          'template_id' => $templateId1,
                          'actor' => $uid,
                          'target' => $parkingUid,
                          'title' => '{"money":"' . number_format($asset) . '"}',
                          'icon' => Zend_Registry::get('static') . "/apps/ship/img/icon/money.gif",
                          'create_time' => $create_time);
        $mdalFeed->insertMinifeed($minifeed);

        $templateId2 = $isAnonymous == 1 ? 66 : 64;
        $minifeed['uid'] = $parkingUid;
        $minifeed['template_id'] = $templateId2;
        $minifeed['title'] = '{"shipName":"' . $shipName . '","money":"' . number_format($asset) . '"}';
        $minifeed['icon'] = Zend_Registry::get('static') . "/apps/ship/img/icon/loss.gif";
        $mdalFeed->insertMinifeed($minifeed);
        
        return $result;
    }
    
    public function stick($uid)
    {
        if ($uid < 0) return;
        
        //check ship user
        require_once 'Mdal/Ship/Ship.php';
        $mdalShip = Mdal_Ship_Ship::getDefaultInstance();
        $sids = $mdalShip->getShipSidByUid($uid);
        if (empty($sids)) return;
        
        $userShips = $mdalShip->getParkingBySid($sids);
        
        foreach ($userShips as $item) {
            if (time() - $item['parked_time'] > 72 * 3600) {
                //park more than 3 days,auto move
                $fee = $item['times'] * $item['fee'] * 32;
                $this->autoMove($item['sid'], $item['uid'], $fee, $item['shipName']);
            }
        }
        
        //check ship on user
        $parkShips = $mdalShip->getParkShipFee($uid);
        foreach ($parkShips as $item) {
            if (time() - $item['parked_time'] > 72 * 3600) {
                //park more than 3 days,auto move
                $fee = $item['times'] * $item['fee'] * 32;                
                $this->autoMove($item['sid'], $item['parking_uid'], $fee, $item['shipName']);
            }
        }
    }
    
    /**
     * private auto move
     *
     * @param integer $sid
     * @param integer $uid
     * @param integer $asset
     * @return boolean
     */
    private function autoMove($sid, $uid, $asset, $shipName)
    {
        $result = false;
        $this->_wdb->beginTransaction();
        
        try {
            //delete ship
            require_once 'Mdal/Ship/Ship.php';
            $mdalShip = Mdal_Ship_Ship::getDefaultInstance();
            $mdalShip->deleteUserShip($sid);
            
            //update user asset
            require_once 'Mdal/Ship/User.php';
            $mdalUser = Mdal_Ship_User::getDefaultInstance();
            $mdalUser->updateUserAsset($asset, $uid);
            
            //insert into feed
            
            $this->_wdb->commit();
            $result = true;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
        }
        
        require_once 'Mdal/Ship/Feed.php';
        $mdalFeed = new Mdal_Ship_Feed();
        $create_time = date('Y-m-d H:i:s');
        
        $minifeed = array('uid' => $uid,
                          'template_id' => 74,
                          'actor' => $uid,
                          'target' => $uid,
                          'title' => '{"shipName":"' . $shipName . '"}',
                          'icon' => Zend_Registry::get('static') . "/apps/ship/img/icon/stick.gif",
                          'create_time' => $create_time);
        $mdalFeed->insertMinifeed($minifeed);
        
        return $result;
    }
    
    /**
     * check island
     *
     * @param integer $uid
     * @return void
     */
    public function checkIsland($uid)
    {
        if ($uid < 0) return;
        
        $result = false;
        
        require_once 'Mdal/Ship/Item.php';
        $mdalItem = Mdal_Ship_Item::getDefaultInstance();
        require_once 'Mdal/Ship/User.php';
        $mdalUser = Mdal_Ship_User::getDefaultInstance();
        //get user info
        $shipUser = $mdalUser->getUserPark($uid);
        
        $this->_wdb->beginTransaction();
        try {
            //delete user overdue island
            $mdalItem->deleteUserOverdueIsland($uid);
            //get user island info by island info
            $userIslandInfo = $mdalItem->getUserIslandById($uid, $shipUser['background']);
            if ( !$userIslandInfo ) {
                //get user free island
                $userFreeIsland = $mdalItem->getUserFreeIsland($uid);
                //update user island 
                $user = array('background'=>$userFreeIsland['id']);
                $mdalUser->updateShipUser($uid, $user);
            }

            $this->_wdb->commit();
            $result = true;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
        }
        
        return $result;
    }
}
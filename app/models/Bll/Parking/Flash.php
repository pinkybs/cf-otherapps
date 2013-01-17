<?php

require_once 'Bll/Abstract.php';

/**
 * parking flash logic's Operation
 *
 * @package    Bll
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2008/04/17    Huch
 */
class Bll_Parking_Flash extends Bll_Abstract
{
	protected $_host =  '/run_appli.pl?id=507&owner_id=';
	
	public function getGadetData($owner, $viewer)
	{		
		$data = array();		
		
		//get user info
		require_once 'Dal/Parking/Puser.php';
		$dalPuser = new Dal_Parking_Puser();
		$userInfo = $dalPuser->getGadetUser($owner);
		
		if (empty($userInfo)) {
			return $data;
		}		
		
		$staticUrl = Zend_Registry::get('static');
		
		$parkingUrl = urlencode(MIXI_HOST . $this->_host . $owner);
		
		//add user info
		require_once 'Bll/User.php';
        Bll_User::appendPerson($userInfo, 'uid');
        $data[] = array('user' => $userInfo['displayName'],
        				'limit' => $userInfo['limit'],
        				'imageFolder' => $staticUrl . '/apps/parking/img/',
        				'bgImageURL' => 'gadget/bg' . $userInfo['cav_name'] . '.gif',
        				'parkingURL' => $parkingUrl);
        
        //init $data parking info
     	for ($i = 1; $i <= $userInfo['limit']; $i++) {
     		$sign = $i == $userInfo['free_park'] ? 'OK' : 'NG';
     		$data[] = array('parkingSign' => $sign,'parkingImageURL' => 'none');
     	}
     	
     	//add parking car info
        $userPark = $dalPuser->getGadetUserPark($owner);
        
        foreach ($userPark as $item) {
        	$data[$item['location']]['parkingImageURL'] = 'car/' . $item['cav_name'] . '/big/' . $item['car_color'] . '.png';
        }
        
        //add yanki card info
		require_once 'Dal/Parking/Item.php';
		$dalItem = new Dal_Parking_Item();
		$yankiInfo = $dalItem->getYankiInfo($owner);
		for ($i = 1; $i <= 8; $i++) {
			if (time() - $yankiInfo['location'.$i] <= 72*3600) {
				$data[$i]['parkingImageURL'] = 'yankee.png';
			}
		}		
		
        //$viewerInfo = $dalPuser->getUserPark($viewer);
        //if ( (time() - $user['last_evasion_time']) <= 48*3600 ) {
        
        //check viewer can see owner bomb		
		if ( $owner == $viewer ) {
			//add bomb card info
			$bombInfo = $dalItem->getBombInfo($owner);
			for ($i = 1; $i <= 8; $i++) {
				if ($bombInfo['location'.$i] == 1) {
					$data[$i]['parkingImageURL'] = 'trap.png';
				}
			}
		}
		
		return $data;
	}	
}
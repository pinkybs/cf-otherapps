<?php

require_once 'Bll/Abstract.php';

/**
 * Chomeboard flash logic's Operation
 *
 * @package    Bll
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2008/05/25    shenhw
 */
class Bll_Chomeboard_Flash extends Bll_Abstract
{
	protected $_data_limit = 20;
	
	public function getGadetData($owner)
	{
		$data = array();
		
		//get user info
		require_once 'Dal/Chomeboard/User.php';
		$dalUser = new Dal_Chomeboard_User();
		$userInfo = $dalUser->getUser($owner);
		
		if (empty($userInfo)) {
			return $data;
		}
		
		$photoUrl = Zend_Registry::get('photo');
		
		$ownerID = substr($owner, 8 , strlen($owner)-48);
		$chomeboardUrl = urlencode(MIXI_HOST . '/run_appli.pl?id=2385&owner_id=' . $ownerID);
		
        require_once 'Bll/Chomeboard/Chomeboard.php';
        $bllChomeboard = new Bll_Chomeboard_Chomeboard();
        $aryBoardHistory = $bllChomeboard->getBoardHistory($owner);
        $boardCount = count($aryBoardHistory);
        
        $limit = 0;
        if ($boardCount > $this->_data_limit) {
            $data[] = array('limit' => $this->_data_limit, 'appURL' => $chomeboardUrl);
        } else {
            $data[] = array('limit' => $boardCount, 'appURL' => $chomeboardUrl);
        }

        //init $data
     	for ($i = 0; $i < $boardCount && $i < $this->_data_limit; $i++) {
     		$data[] = array('imgURL' => urlencode($photoUrl . '/apps/chomeboard/'. $aryBoardHistory[$i]['content']));
     	}
     	
		return $data;
	}
}
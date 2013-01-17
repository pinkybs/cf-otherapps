<?php
/**
 * application callback controller
 *
 * @copyright  Copyright (c) 2009 Community Factory Inc. (http://communityfactory.com)
 * @create    2009/10/23    Huch
 */
class Mobile_DisneyflashController extends Zend_Controller_Action
{
    private $_uid;
    
    private $_disneyUser;
    
    function init()
    {
        $this->_uid = $this->_request->getParam('uid');
        
        require_once 'Mdal/Disney/User.php';
        $mdalUser = Mdal_Disney_User::getDefaultInstance();
        //get user disney info
        $this->_disneyUser = $mdalUser->getUser($this->_uid);
        
        $this->_baseUrl = Zend_Registry::get('host');
    }
    
    public function startAction()
    {
        require_once 'Mbll/Disney/Flash.php';
        $mbllFlash = new Mbll_Disney_Flash();
        //get flash point
        $result = $mbllFlash->getFlashPoint($this->_uid);

        $flashgame_file = Zend_Registry::get('photo') . "/swf/roulette_" . $result['load'] . "_" . $result['flashType'] . ".swf";
        
		$flash = file_get_contents($flashgame_file);

		ob_end_clean();
        ob_start();
        header("Accept-Ranges: bytes");
        header("Cache-Control: no-cache, must-revalidate");
        header("Content-Type: application/x-shockwave-flash");
        
        echo $flash;
        exit(0);
    }
    
    /**
     * show map action
     *
     */
    public function showmapAction()
    {
        $uid = $this->_request->getParam('uid', $this->_uid);
        $aid = $this->_request->getParam('CF_aid');
                
        require_once 'Mdal/Disney/Place.php';
        $mdalPlace = Mdal_Disney_Place::getDefaultInstance();
        //get place list by area id
        $placeList = $mdalPlace->getUserPlaceListByAid($uid, $aid);
        require_once 'Mbll/Disney/ImageCombine.php';
        Mbll_Disney_ImageCombine::getLocalMap($aid, $placeList);
        
        exit(0);
    }
    
    public function downloadawardimgAction()
    {
        $pid = $this->_request->getParam('pid');
        $type = $this->_request->getParam('type', 1);
        
        require_once 'Mdal/Disney/Place.php';
        $mdalPlace = Mdal_Disney_Place::getDefaultInstance();
        
        if ($type == 1) {
	        //get user download award info by pid
	        $downloadAward = $mdalPlace->getDownloadAwardInfo($this->_uid, $pid);
        }
        else {
        	$downloadAward = $mdalPlace->getDesktopAwardInfo($this->_uid, $pid);
        }
        
        //get place info by pid
        $placeInfo = $mdalPlace->getPlaceById($pid);
        
        if ( !$downloadAward || !$placeInfo ) {
            exit;
        }
        
        if ($type == 1) {
        	header('Content-Type: image/gif');
        	$imgUrl = Zend_Registry::get('photo') . "/img/decome_dl/" . $placeInfo['award_icon'] . ".gif";
        }
        else {
        	$ua = Zend_Registry::get('ua');
        	//docomo       jpg
        	if ($ua == 1) {
        		header('Content-Type: image/jpeg');
        		$imgUrl = Zend_Registry::get('photo') . "/img/wallpaper_dl/" . $placeInfo['award_icon'] . "_d.jpg";
        	}        	
        	//au    jpg
        	else if ($ua == 3) {
        		header('Content-Type: image/jpeg');
        		$imgUrl = Zend_Registry::get('photo') . "/img/wallpaper_dl/" . $placeInfo['award_icon'] . "_a.jpg";
        	}
        	//Softbank/Disney   jpz
        	else {
        		//header('Content-Type: image/jpeg');
        		header('Content-type: image/png');
            	header("x-jphone-copyright: no-transfer, no-peripheral");
        		$imgUrl = Zend_Registry::get('photo') . "/img/wallpaper_dl/" . $placeInfo['award_icon'] . "_s.pnz";
        	}
        }
        
        $img = file_get_contents($imgUrl);
        echo $img;
        
        exit(0);
    }
}
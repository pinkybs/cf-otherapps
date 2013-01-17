<?php

/** @see Zend_Json */
require_once 'Zend/Json.php';

/**
 * mixi-parking Ajax Controller
 * mixi-parking Ajax Controller
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create    2009/03/12    huch
 */
class Ajax_OpenparkingController extends Zend_Controller_Action
{

    /**
     * initialize basic data
     * @return void
     */
    function init()
    {
        $font = $this->getFrontController();
        $font->unregisterPlugin('Zend_Controller_Plugin_ErrorHandler');
        $font->setParam('noViewRenderer', true);
    }

    /**
     * application list view
     *
     */
    public function getscoreAction()
    {
        if ($this->_request->isPost()) {
            //get owner id
            $ownerId = $this->_request->getParam('ownerId');

            //get score
            require_once 'Dal/Parking/Puser.php';
        	$dalUser = new Dal_Parking_Puser();
        	$asset = $dalUser->getAllAss($ownerId);
            
        	if (empty($asset)) {
        		echo Zend_Json::encode(array('asset'=>0));
        	}
        	else {
            	echo Zend_Json::encode(array('asset'=>number_format($asset['asset'])));
        	}
        }
    }
    
    /**
     * get gadet flash data
     *
     */
    public function gadetloadAction()
    {
    	if ($this->_request->isPost()) {
    		$id = $this->_request->getPost('userID');
    		
    		if (empty($id)) {
	    		echo 'gadgetData=' . Zend_Json::encode(array());
	    	}    	
	    	
	    	$temp = explode ('*', $id);
	    	
	    	require_once 'Bll/Parking/Flash.php';
	    	$bllFlash = new Bll_Parking_Flash();
			$data = $bllFlash->getGadetData($temp[0], $temp[1]);
			
			echo 'gadgetData=' . Zend_Json::encode($data);
    	}
    	else {
    		echo 'gadgetData=' . Zend_Json::encode(array());
    	}
    }
}
<?php

/** @see Zend_Json */
require_once 'Zend/Json.php';

/**
 * mixi-slave Ajax Controller
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create    2009/07/20    zhangxin
 */
class Ajax_OpenslaveController extends Zend_Controller_Action
{

    protected $_appId = '4947';//test:4947 // real: 6232

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
     * get gadet flash data
     *
     */
    public function gadetloadAction()
    {
    	if ($this->_request->isPost()) {
    		$uid = $this->_request->getPost('ownerId');

    		if (empty($uid)) {
	    		echo Zend_Json::encode(array());
	    		return;
	    	}

	    	require_once 'Dal/Slave/Slave.php';
	    	$dalSlave = Dal_Slave_Slave::getDefaultInstance();
	    	$rowInfo = $dalSlave->getSlaveById($uid);
    	    $aryInfo = array();
	    	if (empty($rowInfo)) {
                $aryInfo['nickname'] = '';
                $aryInfo['balloon'] = '';
                $aryInfo['url'] = '';
                $aryInfo['thumb'] = '';
	    	}
	    	else {
	    	    require_once 'Bll/User.php';
	    	    $msInfo = Bll_User::getPerson($uid);
                $aryInfo['nickname'] = htmlspecialchars(empty($rowInfo['nickname']) ? $msInfo->getDisplayName() . 'ちゃん' : $rowInfo['nickname']);
                $aryInfo['balloon'] = htmlspecialchars($rowInfo['balloon']);
                $aryInfo['url'] = MIXI_HOST . '/run_appli.pl?id=' . $this->_appId . '&owner_id=' . $uid;
                $aryInfo['thumb'] = $msInfo->getThumbnailUrl();
	    	}
			echo Zend_Json::encode($aryInfo);
    	}
    	else {
    		echo Zend_Json::encode(array());
    	}
    }
}
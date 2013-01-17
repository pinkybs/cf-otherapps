<?php

/** @see Zend_Controller_Plugin_Abstract */
require_once 'Zend/Controller/Plugin/Abstract.php';

/**
 * Implement the privilege controller.
 *
 * @package    MyLib_Controller
 * @subpackage MyLib_Controller_Plugin
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/02/27     Huch
 */
class MyLib_Zend_Controller_Plugin_Statistics extends Zend_Controller_Plugin_Abstract
{    
	/**
     * An instance of Zend_Auth
     * @var Zend_Auth
     */
    private $_auth;
    
    /**
     * Constructor.
     *
     * @return void
     */
    public function __construct($auth)
    {
        $this->_auth = $auth;
    }
    
    /**
     * Track user privileges.
     *
     * @param  Zend_Controller_Request_Abstract $request
     * @return void
     */
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        $module = $request->getModuleName();
        $front  = Zend_Controller_Front::getInstance();

        // if module name is null, set default
        if ($module == null) {
            //$module = 'default';
            $module = $front->getDefaultModule();
        }

        if ('ajax' == $module) {
            return;
        }

		$controller = $request->getControllerName();
		
        if ($this->_auth->hasIdentity()) {
        	$id = $this->_auth->getIdentity();
        	
        	require_once 'Dal/Statistics.php';
        	$dalStatistics = Dal_Statistics::getDefaultInstance();
        	
        	$appid = $dalStatistics->getAppId($controller);
        	
        	if (empty($appid)) {
        		return;
        	}
        	
        	require_once 'MyLib/Browser.php';
        	
        	$array = array('app_id' => $appid,
        				   'uid' => $id,
        				   'login_time' => time(),
        				   'ip' => MyLib_Browser::getIP(),
        				   'browser' => MyLib_Browser::getBrowser(),
        				   'os' => MyLib_Browser::getOS());
        	
        	try {
        		$dalStatistics->insertLog($array);
        	}
        	catch (Exception $e) {
        		
        	}
        }
    }
}
<?php

/**
 * advertisement controller
 * 
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/08/17    Huch
 */
class AdController extends Zend_Controller_Action
{
    protected $_uid;
    
    protected $_appId;
    
    public function init()
    {
        $this->_uid = $this->_request->getParam('uid', 0);        
        $this->_appId = $this->_request->getParam('app_id');
        
        if ($this->_uid == 0) {
            $this->_uid = $_COOKIE['app_mixi_uid'];
        }
        
        $this->view->appId = $this->_appId;
        $this->view->staticUrl = Zend_Registry::get('static');
    }
    
    public function topAction()
    {
        require_once 'Dal/Ad/Ad.php';
        $dalAd = Dal_Ad_Ad::getDefaultInstance();
        $ad = $dalAd->getTopAd($this->_appId, $this->_uid);
        
        if (count($ad) == 0) {
            exit(0);
        }
        
        $this->view->ad = $ad;
        $this->render();        
    }
    
    public function rightAction()
    {        
        //require_once 'Dal/Ad/Ad.php';
        //$dalAd = Dal_Ad_Ad::getDefaultInstance();
        //$this->view->ad = $dalAd->getRightAd($this->_appId);
        $this->render();
    }
    
    public function closetopadAction()
    {
        if (empty($this->_appId) || empty($this->_uid)) {
            exit(0);
        }
        
        require_once 'Dal/Ad/Ad.php';
        $dalAd = Dal_Ad_Ad::getDefaultInstance();
        $dalAd->closeAd($this->_appId, $this->_uid);
        
        exit(0);
    }    
    
    public function checkhasadAction()
    {
        $controller = $this->getFrontController();
        $controller->unregisterPlugin('Zend_Controller_Plugin_ErrorHandler');
        $controller->setParam('noViewRenderer', true);
        
        require_once 'Dal/Ad/Ad.php';
        $dalAd = Dal_Ad_Ad::getDefaultInstance();
        $result = $dalAd->checkTopAd($this->_appId, $this->_uid);
        echo Zend_Json::encode($result);
    }
    
    public function showadAction()
    {
        $aid = $this->_request->getParam('aid', 1);
        
        require_once 'Dal/Ad/Ad.php';
        $dalAd = Dal_Ad_Ad::getDefaultInstance();
        $url = $dalAd->getLinkUrlById($aid);
        
        $this->_redirect($url);
    }
    
    public function hotmailAction()
    {
        
    }
}
<?php

/**
 * Admin Index Controller(modules/admin/controllers/Admin_IndexController.php)
 * Admin Index
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create    2009/02/19    zhangxin
 */
class Admin_IndexController extends Zend_Controller_Action
{

    /**
     * page init
     *
     */
    function init()
    {
        $this->_baseUrl = $this->_request->getBaseUrl();
        $this->view->baseUrl = $this->_baseUrl;
        $this->view->staticUrl = Zend_Registry::get('static');
        $this->view->version = Zend_Registry::get('version');

    }

    /**
     * admin index controller index action
     *
     */
    public function indexAction()
    {
        $this->view->title = 'Li-No | Admin Index';
        $this->_forward('login', 'auth');
    }
}
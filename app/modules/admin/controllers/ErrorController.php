<?php

/**
 * Admin Error Controller(modules/admin/controllers/Admin_ErrorController.php)
 * Linno Admin Error Controller
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create    2009/02/18    zhangxin
 */
class Admin_ErrorController extends Zend_Controller_Action
{

    /**
     * page init
     *
     */
    function init()
    {
        $this->view->baseUrl = $this->_request->getBaseUrl();
        $this->view->staticUrl = Zend_Registry::get('static');
        $this->view->version = Zend_Registry::get('version');
    }

    /**
     * page not found
     *
     */
    public function notfoundAction()
    {
        $this->getResponse()->setRawHeader('HTTP/1.1 404 Not Found');
        $this->view->title = '404 Not Found｜OPENSOCIAL APPS ADMIN｜LinNo ( リンノ )';
        $this->render();
    }

    /**
     * page error
     *
     */
    public function errorAction()
    {
        $message = $this->_request->getParam('message', '');
        $this->view->message = $message;
        $this->view->title = 'Error｜OPENSOCIAL APPS ADMIN｜LinNo ( リンノ )';
        $this->render();
    }

    /**
     * page no authority
     *
     */
    public function noauthorityAction()
    {
        $message = $this->_request->getParam('message', '');
        $this->view->message = $message;
        $this->view->title = 'No Authority｜OPENSOCIAL APPS ADMIN｜LinNo ( リンノ )';
        $this->render();
    }

    /**
     * call
     *
     */
    function __call($methodName, $args)
    {
        return $this->_forward('notfound');
    }
}
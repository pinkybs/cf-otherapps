<?php

/** @see MyLib_Zend_Controller_Action_Mobile.php */
require_once 'MyLib/Zend/Controller/Action/Mobile.php';

/**
 * error controller
 * init each error page
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create    2009/09/11    HCH
 */
class Mobile_ErrorController extends MyLib_Zend_Controller_Action_Mobile
{
    /**
     * notfound Action
     *
     */
    public function notfoundAction()
    {
        //$this->getResponse()->setRawHeader('HTTP/1.1 404 Not Found');
        $this->view->title = '404 Not Found';
        $this->render();
    }

    public function invalidflashliteAction()
    {
        $this->view->title = 'invalid flashlite version';
        $this->render();
    }

    /**
     * error Action
     *
     */
    public function errorAction()
    {
        $errors = $this->_getParam('error_handler');

        switch ($errors->type) {
        case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
        case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
            // 404 error -- controller or action not found
            return $this->_forward('notfound');
            break;
        default:
            // application error; display error page, but don't change
            // status code
            // ...
            // Log the exception:
            $exception = $errors->exception;
            if ($exception) {
                $content = $exception->getMessage() . "\n" .  $exception->getTraceAsString();
                err_log($content);
            }

            break;
        }

        // Clear previous content
        $this->getResponse()->clearBody();
        $this->view->title = 'Error';
        $this->render();
    }

    /**
     * magic function
     *   if call the function is undefined,then forward to not found
     *
     * @param string $methodName
     * @param array $args
     * @return void
     */
    function __call($methodName, $args)
    {
        return $this->_forward('notfound');
    }

}

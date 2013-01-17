<?php
 
/* Bll_Application */
require_once 'Bll/Application.php';

/**
 * application run controller
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create    2009/02/10    HLJ
 */
class IndexController extends Zend_Controller_Action
{
    /**
     * web site base url
     *
     * @var string
     */
    protected $_baseUrl;

    /**
     * init
     *  init the data
     */
    function init()
    {

    }

    /**
     * index Action
     *
     */
    public function indexAction()
    {
    	$this->render();
    }
 
    public function runAction()
    {
        $application = Bll_Application::newInstance($this);
        if ($application->autoRegisterPlugin()) {
            $application->run();
        } else {
            $application->redirect404();
        }
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
        return $this->_forward('notfound','error','default');
    }

 }

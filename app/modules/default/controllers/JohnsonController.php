<?php

/**
 * johnson controller
 * init each index page
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/10/22  lp
 */

class JohnsonController extends MyLib_Zend_Controller_Action_Default
{

	public function preDispatch()
	{
	    $uid = $this->_user->getId();

	    require_once 'Bll/Johnson/Johnson.php';
        $bllJohnson = new Bll_Johnson_Johnson();

        $result = $bllJohnson->isTodayFirstLogin($uid, $this->_appId);

        $this->view->firstLogin = $result['firstLogin'];
        $this->view->itemIdString = $result['itemIdString'];
        $this->view->uid = $uid;
        $this->view->csstype = 'johnson';
	}

	public function indexAction()
	{
        $uid = $this->_user->getId();

        require_once 'Bll/Johnson/Johnson.php';
        $bllJohnson = new Bll_Johnson_Johnson();

        //get incentive ids
        $incentiveId = $bllJohnson->getIncentiveId($this->_appId, $uid);

        //get rank
        $rankInfo = $bllJohnson->rank($uid);

        $this->view->incentiveId = $incentiveId;
        $this->view->rankBegin = $rankInfo['rankBegin'];
        $this->view->rankEnd = $rankInfo['rankEnd'];
        $this->view->userCnt = $rankInfo['userCnt'];
        $this->view->rankInfo = $rankInfo['rankInfo'];

        $this->render();
    }

    /**
     * error action
     *
     */
    public function errorAction()
    {
        $this->render();
    }
    /**
     * magic function
     * if call the function is undefined,then forward to not found
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
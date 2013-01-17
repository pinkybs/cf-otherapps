<?php

/**
 * Admin Aparking Controller(modules/admin/controllers/Aparking.php)
 * Linno Admin Manager Controller
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create    2009/02/24    shenhw
 */
class Admin_AparkingController extends MyLib_Zend_Controller_Action_Admin
{

    /**
     * post-Initialize
     * called after parent::init method execution.
     * it can override
     * @return void
     */
    public function postInit()
    {
    }

    /**
     * application controller index action
     *
     */
    public function indexAction()
    {
        $this->_forward('top', 'Aparking', 'admin');
        return;
    }

    /**
     * manager controller manage user action
     *
     */
    public function topAction()
    {
        $this->view->title = '駐車戦争｜OPENSOCIAL APPS ADMIN｜LinNo ( リンノ )';
        $this->render();
    }

    /**
     * add car action
     *
     */
    public function addcarAction()
    {
        $this->view->pageIndex = 1;
        $this->view->title = '自動車 追加・編集｜OPENSOCIAL APPS ADMIN｜LinNo ( リンノ )';
        $this->render();
    }

    /**
     * edit car action
     *
     */
    public function editcarAction()
    {
        $cid = (int)$this->_request->getParam('cid');
        $pageIndex = (int)$this->_request->getParam('pageIndex', 1);

        require_once 'Admin/Bll/Parking.php';
        $bllParking = Admin_Bll_Parking::getDefaultInstance();
        $carInfo = $bllParking->getCarInfo($cid);

        $this->view->pageIndex = $pageIndex;
        $this->view->cid = $cid;
        $this->view->carInfo = $carInfo;
        $this->view->title = '自動車 追加・編集｜OPENSOCIAL APPS ADMIN｜LinNo ( リンノ )';
        $this->render();
    }

    /**
     * add background action
     *
     */
    public function addbackgroundAction()
    {
        require_once 'Admin/Bll/Parking.php';
        $bllParking = Admin_Bll_Parking::getDefaultInstance();
        $aryRank = $bllParking->getRankList();

        $this->view->aryRank = $aryRank;
        
        $this->view->pageIndex = 1;
        $this->view->ranks = array(1 => 'A', 2 => 'B', 3 => 'C', 4 => 'D', 5 => 'E', 6 => 'F');
        $this->view->title = '不動産 追加・編集｜OPENSOCIAL APPS ADMIN｜LinNo ( リンノ )';
        $this->render();
    }

    /**
     * edit background action
     *
     */
    public function editbackgroundAction()
    {
        require_once 'Admin/Bll/Parking.php';
        $bllParking = Admin_Bll_Parking::getDefaultInstance();
        $aryRank = $bllParking->getRankList();

        $this->view->aryRank = $aryRank;
        
        $id = (int)$this->_request->getParam('id');
        $pageIndex = (int)$this->_request->getParam('pageIndex', 1);

        require_once 'Admin/Bll/Parking.php';
        $bllParking = Admin_Bll_Parking::getDefaultInstance();
        $backgroundInfo = $bllParking->getBackgroundInfo($id);

        $this->view->pageIndex = $pageIndex;
        $this->view->id = $id;
        $this->view->backgroundInfo = $backgroundInfo;
        $this->view->title = '不動産 追加・編集｜OPENSOCIAL APPS ADMIN｜LinNo ( リンノ )';
        $this->view->ranks = array(1 => 'A', 2 => 'B', 3 => 'C', 4 => 'D', 5 => 'E', 6 => 'F');
        $this->render();
    }

    /**
     * preDispatch
     *
     */
    function preDispatch()
    {
        require_once 'Admin/Dal/Application.php';
        $dalApp = Admin_Dal_Application::getDefaultInstance();
        $allow = $dalApp->isAppAllowedToUser('parking', $this->_user->uid);
        if (!$allow) {
            $this->_forward('noauthority', 'error', 'admin', array('message'=>'You Have Not Allow To View This Page!!'));
            return;
        }
    }
}
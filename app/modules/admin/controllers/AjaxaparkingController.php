<?php

/** @see Zend_Json */
require_once 'Zend/Json.php';

/**
 * Admin Aparking Ajax Controller
 * Aparking ajax operation
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create    2009/02/24    shenhw
 */
class Admin_AjaxaparkingController extends MyLib_Zend_Controller_Action_AdminAjax
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
     * add car action
     *
     */
    public function addcarAction()
    {
        $txtName = $this->_request->getPost('txtName');
        $txtPrice = (int)$this->_request->getPost('txtPrice');
        $txtCavName = $this->_request->getPost('txtCavName');
        $txtTimes = $this->_request->getPost('txtTimes');

        require_once 'Admin/Bll/Parking.php';
        $bllParking = Admin_Bll_Parking::getDefaultInstance();
        $isCavNameExist = $bllParking->isCavNameExist4Car($txtCavName);

        if ($isCavNameExist) {
            echo "-1";
            return;
        }
        else {
            //insert car info
            $info = array();
            $info['name'] = $txtName;
            $info['price'] = $txtPrice;
            $info['cav_name'] = $txtCavName;
            $info['times'] = $txtTimes;
            $result = $bllParking->addCar($info);
            if (1 == $result) {
                echo 'true';
                return;
            }
            else {
                echo 'false';
                return;
            }
        }
    }

    /**
     * edit car action
     *
     */
    public function editcarAction()
    {
        $txtName = $this->_request->getPost('txtName');
        $txtPrice = (int)$this->_request->getPost('txtPrice');
        $txtCavName = $this->_request->getPost('txtCavName');
        $txtTimes = $this->_request->getPost('txtTimes');
        $cid = $this->_request->getPost('cid');

        require_once 'Admin/Bll/Parking.php';
        $bllParking = Admin_Bll_Parking::getDefaultInstance();
        $isCavNameExist = $bllParking->isCavNameExist4Car($txtCavName, $cid);

        if ($isCavNameExist) {
            echo "-1";
            return;
        }
        else {
            //update car info
            $info = array();
            $info['name'] = $txtName;
            $info['price'] = $txtPrice;
            $info['cav_name'] = $txtCavName;
            $info['times'] = $txtTimes;
            $result = $bllParking->editCar($cid, $info);
            if (1 == $result) {
                echo 'true';
                return;
            }
            else {
                echo 'false';
                return;
            }
        }
    }

    /**
     * car list view
     *
     */
    public function listcarAction()
    {
        $pageIndex = (int)$this->_request->getPost('pageIndex', 1);
        $pageSize = (int)$this->_request->getPost('pageSize', 10);

        require_once 'Admin/Bll/Parking.php';
        $bllParking = Admin_Bll_Parking::getDefaultInstance();
        $carList = $bllParking->getCarList($pageIndex, $pageSize);
        $count = $bllParking->getCarCount();

        $response = array('info' => $carList, 'count' => $count);
        $response = Zend_Json::encode($response);
        echo $response;
    }

    /**
     * add background action
     *
     */
    public function addbackgroundAction()
    {
        $txtName = $this->_request->getPost('txtName');
        $txtCavName = $this->_request->getPost('txtCavName');
        $txtRank = $this->_request->getPost('selRank');
        $txtIntroduce = $this->_request->getPost('txtIntroduce');
        $txtFee = $this->_request->getPost('txtFee');
        $txtPrice = $this->_request->getPost('txtPrice');
        
        require_once 'Admin/Bll/Parking.php';
        $bllParking = Admin_Bll_Parking::getDefaultInstance();
        $isCavNameExist = $bllParking->isCavNameExist4Background($txtCavName);

        if ($isCavNameExist) {
            echo "-1";
            return;
        }
        else {
            //insert background info
            $info = array();
            $info['name'] = $txtName;
            $info['cav_name'] = $txtCavName;
            $info['type'] = $txtRank;
            $info['introduce'] = $txtIntroduce;
            $info['fee'] = $txtFee;
            $info['price'] = $txtPrice;
            
            $result = $bllParking->addBackground($info);
            if (1 == $result) {
                echo 'true';
                return;
            }
            else {
                echo 'false';
                return;
            }
        }
    }

    /**
     * edit background action
     *
     */
    public function editbackgroundAction()
    {
        $txtName = $this->_request->getPost('txtName');
        $txtCavName = $this->_request->getPost('txtCavName');
        //$txtRank = $this->_request->getPost('selRank');
        $id = $this->_request->getPost('id');
        $txtIntroduce = $this->_request->getPost('txtIntroduce');
        $txtFee = $this->_request->getPost('txtFee');
        $txtPrice = $this->_request->getPost('txtPrice');

        require_once 'Admin/Bll/Parking.php';
        $bllParking = Admin_Bll_Parking::getDefaultInstance();
        $isCavNameExist = $bllParking->isCavNameExist4Background($txtCavName, $id);

        if ($isCavNameExist) {
            echo "-1";
            return;
        }
        else {
            //update background info
            $info = array();
            $info['name'] = $txtName;
            $info['cav_name'] = $txtCavName;
            //$info['type'] = $txtRank;
            $info['introduce'] = $txtIntroduce;
            $info['fee'] = $txtFee;
            $info['price'] = $txtPrice;
            
            $result = $bllParking->editBackground($id, $info);
            if (1 == $result) {
                echo 'true';
                return;
            }
            else {
                echo 'false';
                return;
            }
        }
    }

    /**
     * background list view
     *
     */
    public function listbackgroundAction()
    {
        $pageIndex = (int)$this->_request->getPost('pageIndex', 1);
        $pageSize = (int)$this->_request->getPost('pageSize', 10);

        require_once 'Admin/Bll/Parking.php';
        $bllParking = Admin_Bll_Parking::getDefaultInstance();
        $backgroungList = $bllParking->getBackgroundList($pageIndex, $pageSize);
        $count = $bllParking->getBackgroundCount();

        $response = array('info' => $backgroungList, 'count' => $count);
        $response = Zend_Json::encode($response);
        echo $response;
    }

    /**
     * check is validate admin user before action
     *
     */
    function preDispatch()
    {
        require_once 'Admin/Dal/Application.php';
        $dalApp = Admin_Dal_Application::getDefaultInstance();
        $allow = $dalApp->isAppAllowedToUser('parking', $this->_user->uid);
        if (!$allow) {
            $this->_request->setDispatched(true);
            echo 'You Have Not Allow To View This Page!!';
            exit();
        }
    }
}
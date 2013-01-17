<?php

/**
 * parking logic's Operation
 *
 * @package    Bll
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/02/24    shenhw
 */

class Admin_Bll_Parking extends Bll_Abstract
{
    protected static $_instance;

    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * get car list
     *
     * @param Integer $pageindex
     * @param Integer $pagesize
     * @return array
     */
    public function getCarList($pageindex = 1, $pagesize = 10)
    {
        require_once 'Admin/Dal/Parking.php';
        $dalParking = Admin_Dal_Parking::getDefaultInstance();
        $carList = $dalParking->getCarList($pageindex, $pagesize);
        return $carList;
    }

    /**
     * get car count
     *
     * @return Integer
     */
    public function getCarCount()
    {
        require_once 'Admin/Dal/Parking.php';
        $dalParking = Admin_Dal_Parking::getDefaultInstance();
        $carCount = $dalParking->getCarCount();
        return $carCount;
    }

    /**
     * check car's cav name whether exist
     *
     * @param string $cavName
     * @param integer $uid
     * @return boolean
     */
    public function isCavNameExist4Car($cavName, $cid = 0)
    {
        require_once 'Admin/Dal/Parking.php';
        $adminDalPark = Admin_Dal_Parking::getDefaultInstance();
        $result = $adminDalPark->checkCavName4Car($cavName, $cid);

        if (0 != $result) {
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * add a new car
     *
     * @param array $info
     * @return integer
     */
    public function addCar($info)
    {
        $result = 0;

        try {

            $this->_wdb->beginTransaction();

            require_once 'Admin/Dal/Parking.php';
            $adminDalPark = Admin_Dal_Parking::getDefaultInstance();

            //get car colors
            $carColors = $adminDalPark->getCarColors();
            $rand = array_rand($carColors);
            $color = $carColors[$rand];
            $info['color'] = $color['name'];
            $info['create_time'] = date('Y-m-d H:i:s');

            //insert car
            $adminDalPark->insertCar($info);
            $this->_wdb->commit();
            $result = 1;

            require 'Bll/Parking/Cache.php';
            Bll_Parking_Cache ::clearCarShop();
        }
        catch (Exception $e) {
            debug_log($e);
            $this->_wdb->rollBack();
        }
        return $result;
    }

    /**
     * edit car info
     *
     * @param integer $cid
     * @param array $info
     * @return integer
     */
    public function editCar($cid, $info)
    {
        $result = 0;

        try {

            $this->_wdb->beginTransaction();

            require_once 'Admin/Dal/Parking.php';
            $adminDalPark = Admin_Dal_Parking::getDefaultInstance();
            $info['create_time'] = date('Y-m-d H:i:s');

            //insert car
            $adminDalPark->updateCar($cid, $info);
            $this->_wdb->commit();
            $result = 1;

            require 'Bll/Parking/Cache.php';
            Bll_Parking_Cache ::clearCarShop();
        }
        catch (Exception $e) {
            debug_log($e);
            $this->_wdb->rollBack();
        }
        return $result;
    }

    /**
     * get car info
     *
     * @param integer $cid
     * @return array
     */
    public function getCarInfo($cid)
    {
        require_once 'Admin/Dal/Parking.php';
        $adminDalPark = Admin_Dal_Parking::getDefaultInstance();
        return $adminDalPark->getCarInfo($cid);
    }

    /**
     * get background list
     *
     * @param Integer $pageindex
     * @param Integer $pagesize
     * @return array
     */
    public function getBackgroundList($pageindex = 1, $pagesize = 10)
    {
        require_once 'Admin/Dal/Parking.php';
        $dalParking = Admin_Dal_Parking::getDefaultInstance();
        $backgroundList = $dalParking->getBackgroundList($pageindex, $pagesize);
        return $backgroundList;
    }

    /**
     * get background count
     *
     * @param Integer $pageindex
     * @param Integer $pagesize
     * @return array
     */
    public function getBackgroundCount()
    {
        require_once 'Admin/Dal/Parking.php';
        $dalParking = Admin_Dal_Parking::getDefaultInstance();
        $backgroundCount = $dalParking->getBackgroundCount();
        return $backgroundCount;
    }

    /**
     * check background's cav name whether exist
     *
     * @param string $cavName
     * @param integer $id
     * @return boolean
     */
    public function isCavNameExist4Background($cavName, $id = 0)
    {
        require_once 'Admin/Dal/Parking.php';
        $adminDalPark = Admin_Dal_Parking::getDefaultInstance();
        $result = $adminDalPark->checkCavName4Background($cavName, $id);

        if (0 != $result) {
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * add a new background
     *
     * @param array $info
     * @return integer
     */
    public function addBackground($info)
    {
        $result = 0;

        try {

            $this->_wdb->beginTransaction();

            require_once 'Admin/Dal/Parking.php';
            $adminDalPark = Admin_Dal_Parking::getDefaultInstance();
            //$rankInfo = $adminDalPark->getRankInfo($info['type']);
            //$info['fee'] = $rankInfo['fee'];
            //$info['price'] = $rankInfo['price'];
            $info['create_time'] = date('Y-m-d H:i:s');

            //insert background
            $adminDalPark->insertBackground($info);
            $this->_wdb->commit();
            $result = 1;

            require 'Bll/Parking/Cache.php';
            Bll_Parking_Cache ::clearHouse();
        }
        catch (Exception $e) {
            debug_log($e);
            $this->_wdb->rollBack();
        }
        return $result;
    }

    /**
     * edit background
     *
     * @param integer $id
     * @param array $info
     * @return integer
     */
    public function editBackground($id, $info)
    {
        $result = 0;

        try {

            $this->_wdb->beginTransaction();

            require_once 'Admin/Dal/Parking.php';
            $adminDalPark = Admin_Dal_Parking::getDefaultInstance();
            //$rankInfo = $adminDalPark->getRankInfo($info['type']);
            //$info['fee'] = $rankInfo['fee'];
            //$info['price'] = $rankInfo['price'];

            //update background
            $adminDalPark->updateBackground($id, $info);
            $this->_wdb->commit();
            $result = 1;

            require 'Bll/Parking/Cache.php';
            Bll_Parking_Cache ::clearHouse();
        }
        catch (Exception $e) {
            debug_log($e);
            $this->_wdb->rollBack();
        }
        return $result;
    }

    /**
     * get background info
     *
     * @param integer $id
     * @return array
     */
    public function getBackgroundInfo($id)
    {
        require_once 'Admin/Dal/Parking.php';
        $adminDalPark = Admin_Dal_Parking::getDefaultInstance();
        return $adminDalPark->getBackgroundInfo($id);
    }

    /**
     * background list
     *
     * @return array
     */
    public function getRankList()
    {
        require_once 'Admin/Dal/Parking.php';
        $adminDalPark = Admin_Dal_Parking::getDefaultInstance();

        return $adminDalPark->getRankList();
    }

    /**
     * background list
     *
     * @param string $rank
     * @return array
     */
    public function getRankInfo($rank)
    {
        require_once 'Admin/Dal/Parking.php';
        $adminDalPark = Admin_Dal_Parking::getDefaultInstance();

        return $adminDalPark->getRankInfo($rank);
    }
}
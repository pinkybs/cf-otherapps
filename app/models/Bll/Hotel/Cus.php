<?php

require_once 'Bll/Abstract.php';

/**
 * Mixi App cus action logic Operation
 *
 * @package    Bll/Hotel
 * @copyright  Copyright (c) 2009 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/11/2   zhaoxh
 */
class Bll_Hotel_Cus extends Bll_Abstract
{

   
    public function cusClick($uid,$opresult)
    {
        $resultArray = array('result' => 0);
        try {

            $this->_wdb->beginTransaction();

            require_once 'Dal/Hotel/Cus.php';
            $dalCus = Dal_Hotel_Cus::getDefaultInstance();

            $canOperatedNum = $dalCus->countOperatedById($uid,0);

            if ($canOperatedNum > 0) {
            	$dalCus->updateCusById($uid,$opresult);
            }
            else {
            	$resultArray = array('result' => -1);
            	return $resultArray;
            }
            
            $cus_param = $dalCus->countOperatedById($uid, 0);
            $this->_wdb->commit();

            $resultArray = array('result' => 1);
            
            $resultArray['cus_param'] = $cus_param;
            $resultArray['opresult'] = $opresult;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return $resultArray;
        }
        return $resultArray;
    }
    
	public function cusInsert($uid)
    {
        $resultArray = array('result' => 0);
        try {

            $this->_wdb->beginTransaction();

            require_once 'Dal/Hotel/Cus.php';
            $dalCus = Dal_Hotel_Cus::getDefaultInstance();

            $canInsert = $dalCus->canInsertToday($uid);

            if ($canInsert) {
            	$set = array('uid' =>$uid, 'create_time' => time());
            	$dalCus->insertCusClick($set);
            }
            else {
            	$resultArray = array('result' => -1);
            	return $resultArray;
            }
            
            $cus_param = $dalCus->countOperatedById($uid, 0);
            $this->_wdb->commit();

            $resultArray = array('cus_param' => $cus_param);
            
            
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return $resultArray;
        }
        return $resultArray;
    }
}
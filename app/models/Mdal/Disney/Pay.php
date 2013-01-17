<?php
/** @see Mdal_Abstract.php */
require_once 'Mdal/Abstract.php';

/**
 * Mdal Disney pay
 *
 * @package    Mdal/Disney
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/11/09    huch
 */
class Mdal_Disney_Pay extends Mdal_Abstract
{
    protected static $_instance;

    /**
     * get default instance
     *
     * @return Mdal_Disney_Pay
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    } 
    
    /**
     * insert disney_payment
     *
     * @param array $payment
     * @return integer
     */
    public function insert($payment)
    {
        $this->_wdb->insert('disney_payment', $payment);
        return $this->_wdb->lastInsertId();
    }
    
    /**
     * delete disney_payment
     *
     * @param varchar $paycode
     */
    public function delete($paycode)
    {
        $sql = "DELETE FROM disney_payment WHERE point_code=:code";
        $this->_wdb->query($sql, array('code'=>$paycode));
    }
    
    /**
     * update disney_payment
     *
     * @param integer $status
     * @param integer $finishTime
     * @param string $code
     */
    public function updatePayStatus($status, $finishTime, $code)
    {
        $sql = "UPDATE disney_payment SET status=:status,finish_time=:finish_time WHERE point_code=:code";
        
        $this->_wdb->query($sql, array('status'=>$status, 'finish_time'=>$finishTime, 'code'=>$code));
    }
    
    /**
     * get payment by point_code
     *
     * @param string $code
     * @return array
     */
    public function getPaymentByCode($code, $status=0)
    {
        $sql = "SELECT * FROM disney_payment WHERE point_code=:code AND status=:status";
        
        return $this->_wdb->fetchRow($sql, array('code'=>$code, 'status'=>$status));
    }
    
    /**
     * check payment by code
     *
     * @param unknown_type $code
     * @return unknown
     */
    public function checkPaymentByCode($code) 
    {
        $sql = "SELECT COUNT(1) FROM disney_payment WHERE point_code=:code AND status=1";
        
        $result = $this->_wdb->fetchOne($sql, array('code'=>$code));
        
        return $result > 0 ? 1 : 0;
    }
    
    /**
     * get payment
     *
     * @return array
     */
    public function getPayment()
    {
        $sql = "SELECT * FROM disney_payment_type";
        
        return $this->_rdb->fetchAll($sql);
    }
    
    /**
     * insert into disney_download_award
     *
     * @param array $downloadInfo
     * @return integer
     */
    public function insertDownloadAward($downloadInfo)
    {
        $this->_wdb->insert('disney_download_award', $downloadInfo);
        return $this->_wdb->lastInsertId();
    }
    
    /**
     * insert into disney_desktop_award
     *
     * @param array $downloadInfo
     * @return integer
     */
    public function insertDesktopAward($downloadInfo)
    {
        $this->_wdb->insert('disney_desktop_award', $downloadInfo);
        return $this->_wdb->lastInsertId();
    }

    /**
     * insert into disney_send_award
     *
     * @param array $sendInfo
     * @return integer
     */
    public function insertSendAward($sendInfo)
    {
        $this->_wdb->insert('disney_send_award', $sendInfo);
        return $this->_wdb->lastInsertId();
    }
}
?>
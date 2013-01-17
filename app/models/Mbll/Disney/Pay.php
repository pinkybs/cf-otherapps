<?php
/** @see Mbll_Abstract.php */
require_once 'Mbll/Abstract.php';

/**
 * disney pay logic's Operation
 *
 * @package    Mbll
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/11/09    huch
 */
class  Mbll_Disney_Pay extends Mbll_Abstract
{
    public function getPayData($payid, $param=array())
    {
        $pay = $this->getPayment($payid);
        
        if ( $payid == 1 || $payid == 2 || $payid == 6) {
            require_once 'Zend/Json.php';
            $param = Zend_Json::decode($param);
        }
        
        if ($payid == 1 || $payid == 6) {
            $pid = $param['pid'];
            require_once 'Mbll/Disney/Cache.php';
            $place = Mbll_Disney_Cache::getPlace();
            
            foreach ($place as $item) {
                if ($item['pid'] == $pid) {
                    $placeName = $item['award_name'];
                    break;
                }
            }
            
            $pay['name'] = str_replace('{*mixi_name*}', $placeName, $pay['name']);
        }

        $hostUrl = Zend_Registry::get('host');
        
        $disney_pay_data = array(
            1 => array(
                        'callback_url' => $hostUrl . '/mobile/disney/paydownload/CF_pid/' . $param['pid'],
                        'finish_url'   => $hostUrl . '/mobile/disney/downloadaward/CF_isdownload/1/CF_step/complete/CF_pid/' . $param['pid']
                      ),
            2 => array(
                        'callback_url' => $hostUrl . '/mobile/disney/paysendaward/CF_pid/'.$param['pid'].'/CF_fid/'.$param['fid'],
                        'finish_url'   => $hostUrl . '/mobile/disney/sendaward/CF_pid/' . $param['pid'] . '/CF_fid/' . $param['fid'] . '/CF_step/complete'
                      ),
            3 => array(
                        'callback_url' => $hostUrl . '/mobile/disney/payticket',
                        'finish_url'   => $hostUrl . '/mobile/disney/getticket'
                      ),
            4 => array(
                        'callback_url' => $hostUrl . '/mobile/disney/payticket',
                        'finish_url'   => $hostUrl . '/mobile/disney/getticket'
                      ),
            5 => array(
                        'callback_url' => $hostUrl . '/mobile/disney/payticket',
                        'finish_url'   => $hostUrl . '/mobile/disney/getticket'
                      ),
            6 => array(
                        'callback_url' => $hostUrl . '/mobile/disney/paydesktop/CF_pid/' . $param['pid'],
                        'finish_url'   => $hostUrl . '/mobile/disney/desktopaward/CF_isdownload/1/CF_step/complete/CF_pid/' . $param['pid']
                      ),
            7 => array(
                        'callback_url' => $hostUrl . '/mobile/disney/payshoes/CF_sid/7',
                        'finish_url'   => $hostUrl . '/mobile/disney/index',
                      ),
            8 => array(
                        'callback_url' => $hostUrl . '/mobile/disney/payshoes/CF_sid/8',
                        'finish_url'   => $hostUrl . '/mobile/disney/index',
                      ),
            9 => array(
                        'callback_url' => $hostUrl . '/mobile/disney/payshoes/CF_sid/9',
                        'finish_url'   => $hostUrl . '/mobile/disney/index',
                      ),
        );

        if (isset($disney_pay_data[$payid])) {
            return array('callback_url' => $disney_pay_data[$payid]['callback_url'],
                         'finish_url'   => $disney_pay_data[$payid]['finish_url'],
                         'item'         => array(array('id'    => $pay['id'],
                                                       'name'  => $pay['name'],
                                                       'point' => $pay['point'])));
        }

        return null;
    }
    
    /**
     * get payment from cache
     *
     * @param integer $payid
     * @return array
     */
    private function getPayment($payid) 
    {
        require_once 'Mbll/Disney/Cache.php';
        Mbll_Disney_Cache::clearPayment();
        $paymentArray = Mbll_Disney_Cache::getPayment();        
        
        foreach ($paymentArray as $item) {
            if ($item['id'] == $payid) {
                return $item;
            }
        }
    }
    
    /**
     * insert disney_payment
     *
     * @param integer $payid
     * @param integer $uid
     * @param string $point_code
     * @param integer $time
     * @return boolean
     */
    public function insertPay($payid, $uid, $point_code, $time)
    {
        $result = false;
        
        $this->_wdb->beginTransaction();
        
        try {
            $pay = array('point_code' => $point_code,
                         'uid' => $uid,
                         'item_id' => $payid,
                         'create_time' => $time);
                         
            //insert into disney_payment
            require_once 'Mdal/Disney/Pay.php';
            $dalPay = Mdal_Disney_Pay::getDefaultInstance();
            $dalPay->insert($pay);
            
            $this->_wdb->commit();
            
            $result = true;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
        }
        
        return $result;
    }
    
    /**
     * pa finish
     *
     * @param string $code
     * @return boolean
     */
    public function payTicketFinish($code)
    {
        $result = false;
        
        //check point
        require_once 'Mdal/Disney/Pay.php';
        $dalPay = Mdal_Disney_Pay::getDefaultInstance();
        
        $payment = $dalPay->getPaymentByCode($code);
        
        if (empty($payment)) {
            return $result;
        }
        
        $ticketCount = 0;
        
        if ($payment['item_id'] == 3) {
            $ticketCount = 1;
        }
        if ($payment['item_id'] == 4) {
            $ticketCount = 5;
        }
        if ($payment['item_id'] == 5) {
            $ticketCount = 10;
        }
        
        $this->_wdb->beginTransaction();
        
        try {
            //update user ticket            
            require_once 'Mdal/Disney/User.php';
            $mdalUser = Mdal_Disney_User::getDefaultInstance();
            $mdalUser->updateUserGameTicket($payment['uid'], $ticketCount);
            
            //update pay status to complete
            $dalPay->updatePayStatus(1, time(), $code);
            
            $this->_wdb->commit();
            
            $result = true;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
        }
        
        //insert into disney_log_pay
        require_once 'Mdal/Disney/Log.php';
        $mdalLog = Mdal_Disney_Log::getDefaultInstance();
        $mdalLog->insertPay(array('uid'=>$payment['uid'], 'type'=>1, 'content'=>$ticketCount . '枚', 'create_time'=>time()));
        
        return $result;
    }
    
    /**
     * pay download finish
     *
     * @param integer $uid
     * @param integer $pid
     * @param integer $code
     * @return boolean
     */
    public function payDownloadFinish($uid, $pid, $code, $appId)
    {
        $result = false;
                
        //check point
        require_once 'Mdal/Disney/Pay.php';
        $dalPay = Mdal_Disney_Pay::getDefaultInstance();
        
        $payment = $dalPay->getPaymentByCode($code);
        
        if (empty($payment)) {
            return $result;
        }
        
        //check place
        require_once 'Mdal/Disney/Place.php';
        $mdalPlace = Mdal_Disney_Place::getDefaultInstance();
    
        //get place info by pid
        $placeInfo = $mdalPlace->getPlaceById($pid);
        if ( !$placeInfo ) {
            return $result;
        }
        
        $this->_wdb->beginTransaction();
        
        try {
            //update pay ststus
            $dalPay->updatePayStatus(1, time(), $code);
            
            //update user game point
            require_once 'Mdal/Disney/User.php';
            $mdalUser = Mdal_Disney_User::getDefaultInstance();
            $mdalUser->updateUserPoint($uid, 10);
            
            //insert into download history
            $dalPay->insertDownloadAward(array('uid'=>$uid, 'award'=>$pid, 'create_time'=>time()));
        
            $this->_wdb->commit();
            
            $result = true;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
        }
        
        //send activity
        require_once 'Bll/User.php';
        $user = Bll_User::getPerson($uid);
        $title = $user->getDisplayName() . 'さんが' . $placeInfo['award_name'] . 'ｽﾃｨｯﾁのデコメをGET!';        
        require_once 'Bll/Restful.php';
        //get restful object
        $restful = Bll_Restful::getInstance($uid, $appId);
        $picUrl = Zend_Registry::get('static') . "/apps/disney/img/chara_activity/" . $placeInfo['award_icon'] . ".gif";
        $restful->createActivityWithPic(array('title'=>$title), $picUrl, 'image/gif');
        
        //insert into disney_log_pay
        require_once 'Mdal/Disney/Log.php';
        $mdalLog = Mdal_Disney_Log::getDefaultInstance();
        $mdalLog->insertPay(array('uid'=>$payment['uid'], 'type'=>2, 'content'=>$pid, 'create_time'=>time()));
        
        return $result;
    }
    
    /**
     * pay desktop finish
     *
     * @param integer $uid
     * @param integer $pid
     * @param integer $code
     * @return boolean
     */
    public function payDesktopFinish($uid, $pid, $code, $appId)
    {
        $result = false;
                
        //check point
        require_once 'Mdal/Disney/Pay.php';
        $dalPay = Mdal_Disney_Pay::getDefaultInstance();
        
        $payment = $dalPay->getPaymentByCode($code);
        
        if (empty($payment)) {
            return $result;
        }
        
        //check place
        require_once 'Mdal/Disney/Place.php';
        $mdalPlace = Mdal_Disney_Place::getDefaultInstance();
    
        //get place info by pid
        $placeInfo = $mdalPlace->getPlaceById($pid);
        if ( !$placeInfo ) {
            return $result;
        }
        
        $this->_wdb->beginTransaction();
        
        try {
            //update pay ststus
            $dalPay->updatePayStatus(1, time(), $code);
            
            //update user game point
            require_once 'Mdal/Disney/User.php';
            $mdalUser = Mdal_Disney_User::getDefaultInstance();
            $mdalUser->updateUserPoint($uid, 20);
            
            //insert into download history
            $dalPay->insertDesktopAward(array('uid'=>$uid, 'award'=>$pid, 'create_time'=>time()));
        
            $this->_wdb->commit();
            
            $result = true;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
        }
        
        //send activity
        require_once 'Bll/User.php';
        $user = Bll_User::getPerson($uid);
        $title = $user->getDisplayName() . 'さんが' . $placeInfo['award_name'] . 'ｽﾃｨｯﾁの待受けをGET!';        
        require_once 'Bll/Restful.php';
        //get restful object
        $restful = Bll_Restful::getInstance($uid, $appId);
        $picUrl = Zend_Registry::get('static') . "/apps/disney/img/chara_activity/" . $placeInfo['award_icon'] . ".gif";
        $restful->createActivityWithPic(array('title'=>$title), $picUrl, 'image/gif');
        
        //insert into disney_log_pay
        require_once 'Mdal/Disney/Log.php';
        $mdalLog = Mdal_Disney_Log::getDefaultInstance();
        $mdalLog->insertPay(array('uid'=>$payment['uid'], 'type'=>4, 'content'=>$pid, 'create_time'=>time()));
        
        return $result;
    }
    
    /**
     * pay download finish
     *
     * @param integer $uid
     * @param integer $pid
     * @param integer $code
     * @return boolean
     */
    public function paySendAwardFinish($uid, $fid, $pid, $appId, $code)
    {
        $result = -1;
                
        //check point
        require_once 'Mdal/Disney/Pay.php';
        $dalPay = Mdal_Disney_Pay::getDefaultInstance();
        
        $payment = $dalPay->getPaymentByCode($code);
        
        if (empty($payment)) {
            return -2;
        }
        
        //check is friend
        require_once 'Bll/Friend.php';
        $isFriend = Bll_Friend::isFriend($uid, $fid);
        if ( !$isFriend ) {
            return -3;
        }
        
        require_once 'Mdal/Disney/Place.php';
        $mdalPlace = Mdal_Disney_Place::getDefaultInstance();
        
        require_once 'Mdal/Disney/User.php';
        $mdalUser = Mdal_Disney_User::getDefaultInstance();
        
        //check friend is in app
        $friendInApp = $mdalUser->isInApp($fid);
        if ( !$friendInApp ) {
            return -4;
        }
        
        //get place info by pid
        $placeInfo = $mdalPlace->getPlaceById($pid);
        if ( !$placeInfo ) {
            return -5;
        }
        
        //get user award count
        $userAwardCount = $mdalUser->getUserAwardCount($uid, $pid);
        if ( $userAwardCount < 1 ) {
            return -6;
        }
        
        require_once 'Mbll/Disney/Index.php';
        $mbllIndex = new Mbll_Disney_Index();
            
        $this->_wdb->beginTransaction();
        
        try {
            //update pay ststus
            $dalPay->updatePayStatus(1, time(), $code);
            
            //delete user award
            //$mdalUser->deleteUserAward($uid, $pid);            
            
            //add friend award
            $mdalUser->addUserAward($fid, $pid);
            
            //update user last target place
            $friendInfo = array('last_target_place' => $pid);
            $mdalUser->updateUser($fid, $friendInfo);
            
            //send area cup
            $mbllIndex->sendAreaCup($fid, $pid);
            
            //update user game point
            $mdalUser->updateUserPoint($uid, 50);
            
            //insert into send award history
            $dalPay->insertSendAward(array('uid'=>$uid, 'fid'=>$fid, 'award'=>$pid, 'create_time'=>time()));
                        
            $this->_wdb->commit();
            
            $result = 1;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
        }
        
        if ( $result != 1 ) {
            return $result;
        }
        
        require_once 'Mdal/Disney/Notice.php';
        $mdalNotice = Mdal_Disney_Notice::getDefaultInstance();
        
        require_once 'Bll/User.php';
        $user = Bll_User::getPerson($uid);
        
        //notice title
        $title = $user->getDisplayName() . 'さんから' . $placeInfo['award_name'] . 'ｽﾃｨｯﾁのﾌﾟﾚｾﾞﾝﾄが届いたよ';

        $notice = array('uid' => $fid,
                        'actor_uid' => $uid,
                        'pid' => $pid,
                        'title' => $title,
                        'type' => 7,
                        'create_time' => time());
        //insert notice
        $mdalNotice->insertNotice($notice);
        
        //insert into disney_log_pay
        require_once 'Mdal/Disney/Log.php';
        $mdalLog = Mdal_Disney_Log::getDefaultInstance();
        $mdalLog->insertPay(array('uid'=>$payment['uid'], 'type'=>3, 'content'=>$pid, 'create_time'=>time()));
        
        return $result;
    }
    
    public function payShoesFinish($uid, $sid, $code)
    {
        $result = false;
        
        $this->_wdb->beginTransaction();
        
        try {
            //update pay ststus
            require_once 'Mdal/Disney/Pay.php';
            $dalPay = Mdal_Disney_Pay::getDefaultInstance();
            $dalPay->updatePayStatus(1, time(), $code);
            
            //insert into user shoes
            require_once 'Mdal/Disney/Shoes.php';
            $mdalShoes = Mdal_Disney_Shoes::getDefaultInstance();
            $mdalShoes->update($uid, $sid);
                        
            $this->_wdb->commit();
            
            $result = true;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
        }
        
        return $result;
    }
}

?>
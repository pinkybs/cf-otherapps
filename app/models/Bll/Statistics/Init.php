<?php

class Bll_Statistics_Init extends Bll_Abstract
{
    public function getStatistics($app_id,$app_table,$reportDate,$aid)
    {
        //$reportDate = time();
        //$reportDate = '1264521600';//2010-1-27 00:00:00
        $report_date = date("Y-m-d",($reportDate - 0));
        require_once 'Dal/AppStatistics.php';
        $dalAppStatistics = Dal_AppStatistics::getDefaultInstance();
        //user daily login app
        $dailyLoginCount = $dalAppStatistics->getDailyLoginCount($app_table,$reportDate);
        //user app add
        //$aryLoginInfo = $dalAppStatistics->getAppLogin($app_id);
        $dailyUserCount = $dalAppStatistics->getDailyAppAddCount($app_id,$reportDate,0);
        //$dailyCount = $dalAppStatistics->getDailyUserCount($app_table);
        //if($aryLoginInfo){
        //    $dailyUserCount = $dailyCount - $aryLoginInfo['app_login'];
        //}
        //$result = self::setAppLogin($app_id,$dailyCount);
        $dailyInviteLoginCount = $dalAppStatistics->getDailyAppAddCount($app_id,$reportDate,1);
        $inviteCount = $dalAppStatistics->getInviteLoginData($app_id,$reportDate);
        //$invite ohter user count
        $inviteOtherUserCount = $dalAppStatistics->getInviteOtherUserCount($app_id,$reportDate);
        //revmove app count
        $removeAppCount = $dalAppStatistics->getDailyAppRemoveCount($app_id,$reportDate);
        $aryInfo = array();
        $aryInfo['report_date'] = $report_date;
        $aryInfo['daily_login'] = $dailyLoginCount;
        $aryInfo['app_login'] = $dailyUserCount;
        $aryInfo['daily_access'] = 0;
        $aryInfo['app_login_invite'] = $dailyInviteLoginCount;
        $aryInfo['user_invite'] = $inviteCount;
        $aryInfo['invited_other_user'] = $inviteOtherUserCount;
        $aryInfo['remove_app'] = $removeAppCount;
        $aryInfo['others'] = '';
        if($app_id == '16235'){
            $aryKitchenPoint = $dalAppStatistics->getKitchenPointData($reportDate);
            $aryKitchenGoldDataPerType = $dalAppStatistics->getKitchenGoldDataPerType($reportDate);
            $aryKitchenUser = $dalAppStatistics->getKitchenUserData($reportDate);
            
            $strKitchen = '';
            $tempKitchenPoint = array('1'=> 0, '2'=> 0, '3'=> 0, '4'=> 0);
            $tempKitchenUser = array('1'=> 0, '2'=> 0, '3'=> 0, '4'=> 0, '5'=> 0, '6'=> 0, '7'=> 0, '8'=> 0, '9'=> 0, '10'=> 0);
            $tempKitchenGoldDataPerType = array('1'=> 0, '2'=> 0, '3'=> 0, '4'=> 0);
            foreach($aryKitchenPoint as $value){
                $tempKitchenPoint[$value['type']] = $value['sum'];
            }
            $strKitchen = implode(",", $tempKitchenPoint);
            foreach($aryKitchenUser as $value){
                $tempKitchenUser[$value['type']] = $value['sum'];
            }
            $strKitchen .= ',' . implode(",", $tempKitchenUser);
            foreach($aryKitchenGoldDataPerType as $value){
                if ("buy_gacha" == $value['description']) {
                    $tempKitchenGoldDataPerType[1] += $value['sum'];
                } else if ("buy_food" == $value['description']) {
                    $tempKitchenGoldDataPerType[2] += $value['sum'];
                } else if ("buy_item" == $value['description'] || "buy_beauty" == $value['description']) {
                    $tempKitchenGoldDataPerType[3] += $value['sum'];
                } else if ("buy_goods" == $value['description']) {
                    $tempKitchenGoldDataPerType[4] += $value['sum'];
                }
            }
            $strKitchen .= ',' . implode(",", $tempKitchenGoldDataPerType);
                        
            $aryInfo['others'] = $strKitchen;
        }
        
        require_once 'Bll/Statistics/RemoteServiceApi.php';
        $bllAppStatistics =new Bll_Statistics_RemoteServiceApi();
        $bllAppStatistics->setStatistics($aid, $aryInfo);
    }
    
    
    /**
     * insert log data
     *
     * @param array $info
     * @param integer $app_id
     * @return  boolean
     */
    public function setAppLogin($app_id,$dailyUserCount)
    {
        try {
            require_once 'Dal/AppStatistics.php';
            $dalAppStatistics = Dal_AppStatistics::getDefaultInstance();

            $aryInfo = $dalAppStatistics->getAppLogin($app_id);

            //$dailyUserCount = $dalAppStatistics->getDailyUserCount($app_table);

            $info = array('app_id' =>$app_id,'app_login' =>$dailyUserCount);
            $this->_wdb->beginTransaction();

            //insert statistics data
            if(!$aryInfo) {
                $dalAppStatistics->insertAppLogin($info);
            } else {
                $dalAppStatistics->updateAppLogin($app_id,$info);
            }

            $this->_wdb->commit();
            return true;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return false;
        }
    }
    
}
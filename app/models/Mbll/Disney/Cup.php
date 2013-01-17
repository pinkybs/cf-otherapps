<?php
/** @see Bll_Abstract.php */
require_once 'Bll/Abstract.php';

/**
 * disney cup logic's Operation
 *
 * @package    Mbll
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/10/12    Liz
 */
class Mbll_Disney_Cup extends Bll_Abstract
{
    
    /** update user area cup all user
     * 
     * @param integer $uidStart
     * @param integer $uidEnd
     * @return void
     */
    public function updateUserAreaCupAllUser($uidStart, $uidEnd)
    {
        require_once 'Mdal/Disney/Cup.php';
        $mdalCup = Mdal_Disney_Cup::getDefaultInstance();
        //get user list
        $userList = $mdalCup->getAppUser($uidStart, $uidEnd);
        
        for ( $j = 0,$jCount = count($userList); $j < $jCount; $j++ ) {
            $this->updateUserAreaCup($userList[$j]['uid']);
        }
    }
    
    
    /** update user area cup buy uid
     * 
     * @param integer $uid
     * @return void
     */
    public function updateUserAreaCup($uid)
    {
        require_once 'Mdal/Disney/Cup.php';
        $mdalCup = Mdal_Disney_Cup::getDefaultInstance();
        //get user award
        $userAwardArray = $mdalCup->getUserAward($uid);
        $allCount = count($userAwardArray);
        
        $areaCount1 = 0;//8
        $areaCount2 = 0;//4
        $areaCount3 = 0;//5
        $areaCount4 = 0;//6
        $areaCount5 = 0;//4
        $areaCount6 = 0;//7
        $areaCount7 = 0;//6
        $areaCount8 = 0;//7
        
        for ( $i = 0; $i < $allCount; $i++ ) {
            $pid = $userAwardArray[$i]['pid'];
            switch ( $pid ) {
                case $pid <= 7 :
                    $areaCount8++;
                    break;
                case $pid == 10 || $pid == 17 || $pid == 18 || $pid == 22 || $pid == 23 || $pid == 24 :
                    $areaCount7++;
                    break;
                case $pid == 8 || $pid == 9 || $pid == 11 || $pid == 12 || $pid == 13 || $pid == 14 || $pid == 15 :
                    $areaCount6++;
                    break;
                case $pid == 16 || $pid == 19 || $pid == 20 || $pid == 21 :
                    $areaCount5++;
                    break;
                case $pid == 25 || $pid == 26 || $pid == 27 || $pid == 28 || $pid == 29 || $pid == 30 :
                    $areaCount4++;
                    break;
                case $pid == 31 || $pid == 32 || $pid == 33 || $pid == 34 || $pid == 35 :
                    $areaCount3++;
                    break;
                case $pid == 36 || $pid == 37 || $pid == 38 || $pid == 39 :
                    $areaCount2++;
                    break;
                case $pid == 40 || $pid == 41 || $pid == 42 || $pid == 43 || $pid == 44 || $pid == 45 || $pid == 46 || $pid == 47 :
                    $areaCount1++;
                    break;
            }
        }
        
        if ( $areaCount1 >= 8 || $areaCount2 >= 4 || $areaCount3 >= 5 || $areaCount4 >= 6 || $areaCount5 >= 4 || $areaCount6 >= 7 || $areaCount7 >= 6 || $areaCount8 >= 7 || $allCount == 47 ) {
            
            try {
                $this->_wdb->beginTransaction();
                
                //insert cup                
                if ( $areaCount1 >= 8 ) {
                    $mdalCup->insertCup($uid, 18);
                }
            
                if ( $areaCount2 >= 4 ) {
                    $mdalCup->insertCup($uid, 17);
                }
                
                if ( $areaCount3 >= 5 ) {
                    $mdalCup->insertCup($uid, 16);
                }
                
                if ( $areaCount4 >= 6 ) {
                    $mdalCup->insertCup($uid, 15);
                }
                
                if ( $areaCount5 >= 4 ) {
                    $mdalCup->insertCup($uid, 14);
                }
                
                if ( $areaCount6 >= 7 ) {
                    $mdalCup->insertCup($uid, 13);
                }
                
                if ( $areaCount7 >= 6 ) {
                    $mdalCup->insertCup($uid, 12);
                }
                
                if ( $areaCount8 >= 7 ) {
                    $mdalCup->insertCup($uid, 11);
                }
                   
                if ( $allCount == 47 ) {
                    $mdalCup->insertCup($uid, 19);
                }
                                
                $this->_wdb->commit();
            }
            catch (Exception $e) {
                $this->_wdb->rollBack();
                
                return -1;
            }
        }
    }
}
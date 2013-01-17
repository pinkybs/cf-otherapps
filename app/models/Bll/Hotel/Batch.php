<?php

require_once 'Bll/Abstract.php';

/**
 * hotel batch logic's Operation
 *
 * @package    Bll
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/10/20    Huch
 */
class Bll_Hotel_Batch extends Bll_Abstract
{
    public function visitUser($uid)
    {
        //get game current date
        require_once 'Bll/Hotel/Config.php';
        $cdate = Bll_Hotel_Config::getGameCurrentDate();

        //check last operate date
        $shutDate = $this->getNextShutDate($uid);
        if (strtotime($cdate) > strtotime($shutDate)) {
            //must pay salary
            return;
        }

        //check user is visit
        require_once 'Dal/Hotel/Batch.php';
        $dalBatch = Dal_Hotel_Batch::getDefaultInstance();
        $vdate = $dalBatch->getUserVisitDate($uid);

        if ($cdate != $vdate) {
            $this->_wdb->beginTransaction();

            try {
                //update user last visit date
                $dalBatch->updateUserVisitDate($uid, $vdate);

                $d = floor((strtotime($cdate) - strtotime($vdate))/3600/24);

                //get current date room customer
                $roomCustomer = $dalBatch->getRoomCustomer($uid, $lastOpDate);
                if (empty($roomCustomer)) {
                    $room1 = $room2 = $room3 = 0;
                }
                else {
                    $room1 = $roomCustomer['room1'];
                    $room2 = $roomCustomer['room2'];
                    $room3 = $roomCustomer['room3'];
                }

                //get user room level
                require_once 'Dal/Hotel/Huser.php';
                $dalHuser = Dal_Hotel_Huser::getDefaultInstance();
                $roomList = $dalHuser->getRoomlist($uid);

                $userInfo = $dalHuser->getUserInfoById($uid);

                //get room type
                require_once 'Bll/Hotel/Cache.php';
                $roomType = Bll_Hotel_Cache::getRoomType();
                $restaurantType = Bll_Hotel_Cache::getRestaurantType();
                $cleanOcc = Bll_Hotel_Cache::getCleanOccupancy();

                $totalIncome = 0;
                $currentClean = $userInfo['clean'];

                for ($i = 0; $i < $d; $i++) {
                    $tempDate = date('Y-m-d',strtotime($vdate . "+$i day"));

                    //reduce user clean
                    $currentClean -= Bll_Hotel_Config::getCleanDownPerDay();

                    //update user log income for salary
                    $income = $roomType[$roomList['room1']]['fee'] * $room1
                            + $roomType[$roomList['room2']]['fee'] * $room2
                            + $roomType[$roomList['room3']]['fee'] * $room3;
                    $dalBatch->insertUserLog($uid, $room1, $room2, $room3, $tempDate, $income);

                    //insert into system feed

                    //update user money  room fee and restaurant fee
                    $totalIncome += $income + ($room1 + $room2 + $room3) * $restaurantType[$roomList['restaurant']]['dinind_rate'] * $restaurantType[$roomList['restaurant']]['fee'];

                    //count local user room custom
                    $currentOccupancy = (80 + $cleanOcc[101 - $currentClean]['occupancy'])/100;
                    $room1 = round($roomType[$roomList['room1']]['living_in'] * $currentOccupancy);
                    $room2 = round($roomType[$roomList['room2']]['living_in'] * $currentOccupancy);
                    $room3 = round($roomType[$roomList['room3']]['living_in'] * $currentOccupancy);

                    if ($i > 1 && $shutDate == $tempDate) {
                        //must pay salary
                        break;
                    }
                }

                //set local user clean and room custom
                $dalBatch->updateUserClean($uid, $currentClean);

                //if not pass 30 days
                if ($shutDate != $tempDate) {
                    $dalBatch->insertUserLog($uid, $room1, $room2, $room3, $cdate, 0);
                }

                //update user money
                $dalBatch->updateUserMoney($uid, $totalIncome);

                //update user visit visit date
                $dalBatch->updateUserVisitDate($uid, $cdate);

                $this->_wdb->commit();
            }
            catch (Exception $e) {
                $this->_wdb->rollBack();
            }
        }
    }

    private function getNextShutDate($uid)
    {
        require_once 'Dal/Hotel/Batch.php';
        $dalBatch = Dal_Hotel_Batch::getDefaultInstance();
        $lastOpDate = $dalBatch->getUserOperateDate($uid);
        $l = Bll_Hotel_Config::getAllowNotLoginDay();
        return date('Y-m-d',strtotime($lastOpDate . "+$l day"));
    }


    public function paySalary($uid, $month)
    {
        $this->_wdb->beginTransaction();

        try {
            //get game current date
            require_once 'Bll/Hotel/Config.php';
            $cdate = Bll_Hotel_Config::getGameCurrentDate();

            require_once 'Dal/Hotel/Batch.php';
            $dalBatch = Dal_Hotel_Batch::getDefaultInstance();
            $lastOpDate = $dalBatch->getUserOperateDate($uid);

            //get user hotel income
            $income = $dalBatch->getUserHotelIncome($uid, $month);

            //update user money
            $dalBatch->updateUserMoney($uid, -$month*0.1);

            //insert into system feed

            require_once 'Bll/Hotel/Feed.php';
            $bllFeed = new Bll_Hotel_Feed();

            $aryInfo = array('{*month*}' => $month);
            $bllFeed->newFeedMessage(5, $uid, null, $aryInfo, '-' . $month * 0.1, 1);

            $this->_wdb->commit();
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
        }
    }
}
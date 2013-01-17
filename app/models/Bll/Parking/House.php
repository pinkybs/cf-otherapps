<?php

require_once 'Bll/Abstract.php';

/**
 * parking use item logic's Operation
 *
 * @package    Bll
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2008/03/04    Huch
 */
class Bll_Parking_House extends Bll_Abstract{

    /**
     * buy house
     * @author lp
     * @param integer $hid
     * @param integer $uid
     * @return boolean
     */
    public function buyHouse($hid, $uid)
    {
        $result = -1;

        try {
            require_once 'Dal/Parking/House.php';

            $dalHouse = new Dal_Parking_House();
            $this->_wdb->beginTransaction();

            //get new house info
            $houseInfo = $dalHouse->getHouseInfo($hid);

            $houseType = 0;

            switch ($houseInfo['type']){
            		case 'A' : $houseType=3; break;
            		case 'B' : $houseType=4; break;
            		case 'C' : $houseType=5; break;
            		case 'D' : $houseType=6; break;
            		case 'E' : $houseType=7; break;
            		case 'F' : $houseType=8; break;
            }

            if ( empty($houseInfo) ) {
                return $result;
            }

            //Computate balance between oldhouse and new house
            $oldHousePrice = $dalHouse->getOldHousePrice($uid);
            $balance = $houseInfo['price']-$oldHousePrice * 0.9;
            
            //get user old parking info
            $userParkingInfo = $dalHouse->getUserPark($uid);

            if ( $userParkingInfo['background'] == $hid ) {
                return 2;
            }
            
            if ( $userParkingInfo['type'] > $houseInfo['type'] ) {
                return 3;
            }
            
            //check user have enough asset
            if ( $userParkingInfo['asset'] < $balance ){
                return 0;
            }

            $info = array('background' => $hid);
            //update user parking background
            $dalHouse->updateUserParking($uid, $info);
            //update user asset
            $dalHouse->updateUserAsset($balance, $uid);

            $create_time = date('Y-m-d H:i:s');

            //insert into minifeed
            $minifeed = array('uid' => $uid,
                              'template_id' => 18,
                              'actor' => $uid,
                              'title' => '{"house_name":"'. $houseInfo['name'] . '","count":"'. $houseType . '","money":"' . number_format($houseInfo['fee']) . '"}',
                              'icon' => Zend_Registry::get('static') . "/apps/parking/img/icon/estate.gif",
                              'create_time' => $create_time);


            $dalHouse->insertMinifeed($minifeed);

            //insert into newsfeed
            $newsfeed = array('uid' => $uid,
                              'template_id' => 19,
                              'actor' => $uid,
                              'title' => '{"house_name":"'. $houseInfo['name'] . '","count":"'. $houseType . '","money":"' . number_format($houseInfo['fee']) . '"}',
                              'icon' => Zend_Registry::get('static') . "/apps/parking/img/icon/estate.gif",
                              'create_time' => $create_time);
            $dalHouse->insertNewsfeed($newsfeed);
            $this->_wdb->commit();
            $result = 1;

        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return $result;
        }
        return $result;
    }

}
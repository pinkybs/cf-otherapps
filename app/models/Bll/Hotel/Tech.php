<?php
/** @see Bll_Abstract.php */
require_once 'Bll/Abstract.php';

/**
 * hotel Tech logic's Operation
 *
 * @package    Bll
 * @copyright  Copyright (c) 2009 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/09/16    zhaoxh
 */
class Bll_Hotel_Tech extends Bll_Abstract
{
    public function upTech($uid,$colName)
    {
        $resultArray = array('result' => -1);

        require_once 'Dal/Hotel/Tech.php';
        $dalTech = Dal_Hotel_Tech::getDefaultInstance();

        try {
            $this->_wdb->beginTransaction();

            //get destination table name
            //$table = 'hotel_technology_type';

            //get ROOM currentLv
            $currentLv = $dalTech->getOneData($uid,$colName);

            // get update_money and update_time
            $priceAndTime = $dalTech->getTechnology($currentLv,$colName);

            // "2" means "tech update"
            $type = 2;

            //in update process or not
            $inUpProcess = $dalTech->inUpProcess($uid,$type);

            require_once 'Dal/Hotel/Huser.php';
            $dalHuser = Dal_Hotel_Huser::getDefaultInstance();
            $money = $dalHuser->getOneData($uid,'money');


            //in update process or money not enough,return error
            if ($inUpProcess || $priceAndTime['update_money'] > $money || !$priceAndTime) {
                $resultArray['result'] = -2;
                return $resultArray;
            }

            $experience = $dalHuser->getOneData($uid,'experience');
            $userLv = $dalHuser->getUserLvByExp(intval($experience) + intval($priceAndTime['update_money']/10000));
            $nextExp = $dalHuser->getExpByLv($userLv + 1);
            
            //set infoArray that will be updated
            $set = array ('money' => $money - $priceAndTime['update_money'],
                          'experience' => intval($experience) + intval($priceAndTime['update_money']/10000),
                          'level' => $userLv);



            //update user info
            $dalHuser->upHuser($uid,$set);

            $time = time();
            $set2 = array ('uid' => $uid,
                           'type' => $type,
                           'name' => $colName,
                           'currentLv' => $currentLv,
                           'begin_time' => $time,
                           'over_time' => intval($time) + intval($priceAndTime['update_time'])
                          );
            //insert upProcess
            $dalTech->insertUpProcess($set2);

            //insert feed
            require_once 'Dal/Hotel/Feed.php';
            $dalFeed = Dal_Hotel_Feed::getDefaultInstance();
            $nextLv = $currentLv + 1;
            /*$set3 = array('uid' => $uid,
                          'template_id' => 4,
                          'title' => '{"techName":"' . $colName . '","currentLv":"' . $currentLv . '","nextLv":"' . $nextLv . '","money":"' . $priceAndTime['update_money'] . '"}',
                          'create_time' => time());
            $tableName = 'hotel_system_message';
            $dalFeed->insertFeed($set3, $tableName);*/

            require_once 'Bll/Hotel/Feed.php';
            $bllFeed = new Bll_Hotel_Feed();

            $aryInfo = array('{*level*}' => $nextLv);
            $bllFeed->newFeedMessage(5, $uid, $aryInfo, null, '-' . $priceAndTime['update_money'], 1);

            $this->_wdb->commit();

            $resultArray['result'] = 1;
            $resultArray['techUpName'] = $colName;
            $resultArray['currentLv'] = $currentLv;
            $resultArray['update_time'] = $priceAndTime['update_time'];
            $resultArray['update_money'] = $priceAndTime['update_money'];
            $resultArray['experience'] = $set['experience'];
            $resultArray['level'] = $userLv;
            $resultArray['effect'] = $priceAndTime['effect'];
            $resultArray['next_exp'] = $nextExp;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return $resultArray;
        }
        return $resultArray;
    }

    public function upTechOver($uid)
    {
        $resultArray = array('result' => -1);
        require_once 'Dal/Hotel/Tech.php';
        $dalTech = Dal_Hotel_Tech::getDefaultInstance();

        try {
            $this->_wdb->beginTransaction();

            // "2" means "technology update"
            $type = 2;

            //in update process or not
            $inUpProcess = $dalTech->inUpProcess($uid,$type);

            if ($inUpProcess) {
                $resultArray['result'] = -2;
                return $resultArray;
            }

            $t = time();
            //update room
            $re = $dalTech->updateTech($uid, $type, $t);

            require_once 'Dal/Hotel/Huser.php';
            $dalHuser = Dal_Hotel_Huser::getDefaultInstance();
            //set updateInfo to operated
            $dalHuser->setOperated($re['id']);

            //get next level info
            $priceAndTime = $dalTech->getTechnology($re['currentLv'] + 1,$re['name']);

            //insert feed
            require_once 'Dal/Hotel/Feed.php';
            $dalFeed = Dal_Hotel_Feed::getDefaultInstance();
            $nextLv = $re['currentLv'] + 1;
            /*$set3 = array('uid' => $uid,
                          'template_id' => 13,
                          'title' => '{"techName":"' . $re['name'] . '","currentLv":"' . $re['currentLv'] . '","nextLv":"' . $nextLv . '"}',
                          'create_time' => time());*/

            require_once 'Bll/Hotel/Feed.php';
            $bllFeed = new Bll_Hotel_Feed();
            $bllFeed->newFeedMessage(13, $uid, null, null, 0, 1);

            /*$tableName = 'hotel_system_message';
            $dalFeed->insertFeed($set3, $tableName);*/

            $this->_wdb->commit();


            $resultArray['result'] = 1;
            $resultArray['techUpName'] = $re['name'];
            $resultArray['currentLv'] = $re['currentLv'] + 1;
            $resultArray['nextPrice'] = $priceAndTime['update_money'];
            $resultArray['effect'] = $priceAndTime['effect'];

            //begin add customer event param
            require_once 'Bll/Hotel/Cus.php';
            $bllCus = new Bll_Hotel_Cus();
            $cusResult = $bllCus->cusInsert($uid);
            $resultArray += $cusResult;
            //end add customer event param

        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return $resultArray;
        }
        return $resultArray;
    }

    public function getTechInfo($uid)
    {
        require_once 'Dal/Hotel/Tech.php';
        $dalTech = Dal_Hotel_Tech::getDefaultInstance();

        $techInfo = $dalTech->getTechInfo($uid);
        $techInfo['desk_level'] = $techInfo[0]['level'];
        $techInfo['desk_update_money'] = $techInfo[0]['update_money'];
        $techInfo['desk_effect'] = $techInfo[0]['effect'];
        $techInfo['cook_level'] = $techInfo[1]['level'];
        $techInfo['cook_update_money'] = $techInfo[1]['update_money'];
        $techInfo['cook_effect'] = $techInfo[1]['effect'];
        $techInfo['service_level'] = $techInfo[2]['level'];
        $techInfo['service_update_money'] = $techInfo[2]['update_money'];
        $techInfo['service_effect'] = $techInfo[2]['effect'];
        $techInfo['learn_level'] = $techInfo[3]['level'];
        $techInfo['learn_update_money'] = $techInfo[3]['update_money'];
        $techInfo['learn_effect'] = $techInfo[3]['effect'];

        $techInfo['desk_next_level'] = $techInfo[4]['level'];
        $techInfo['desk_next_update_money'] = $techInfo[4]['update_money'];
        $techInfo['desk_next_effect'] = $techInfo[4]['effect'];
        $techInfo['cook_next_level'] = $techInfo[5]['level'];
        $techInfo['cook_next_update_money'] = $techInfo[5]['update_money'];
        $techInfo['cook_next_effect'] = $techInfo[5]['effect'];
        $techInfo['service_next_level'] = $techInfo[6]['level'];
        $techInfo['service_next_update_money'] = $techInfo[6]['update_money'];
        $techInfo['service_next_effect'] = $techInfo[6]['effect'];
        $techInfo['learn_next_level'] = $techInfo[7]['level'];
        $techInfo['learn_next_update_money'] = $techInfo[7]['update_money'];
        $techInfo['learn_next_effect'] = $techInfo[7]['effect'];

        return $techInfo;
    }
}
<?php
/** @see Bll_Abstract.php */
require_once 'Bll/Abstract.php';

/**
 * hotel user logic's Operation
 *
 * @package    Bll
 * @copyright  Copyright (c) 2009 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/09/16    zhaoxh
 */
class Bll_Hotel_Huser extends Bll_Abstract
{
    /**
     * insert hotel user
     *
     * @param integer $userInfo
     * @return boolean
     */
    public function insertHuser($userInfo)
    {
        $result = false;

        $this->_wdb->beginTransaction();

        try {
            require_once 'Dal/Hotel/Huser.php';
            $dalHuser = Dal_Hotel_Huser::getDefaultInstance();
            //insert user info
            $dalHuser->insertHuser($userInfo);

            //insert user room
            $dalHuser->insertHotelRoom(array('uid' => $userInfo['uid']));

            //insert user tech
            $dalHuser->insertHotelTech(array('uid' => $userInfo['uid']));

            //insert 2 learner
            $set1 = array('uid' => $userInfo['uid'],
                          'index' => 1,
                          'create_time' => time());
            $set2 = array('uid' => $userInfo['uid'],
                          'index' => 2,
                          'create_time' => time());
            $dalHuser->insertHotelLearn($set1);
            $dalHuser->insertHotelLearn($set2);

            //insert feed
            require_once 'Dal/Hotel/Feed.php';
            $dalFeed = Dal_Hotel_Feed::getDefaultInstance();
            /*$set3 = array('uid' => $userInfo['uid'],
                          'template_id' => 1,
                          'create_time' => time());*/
            require_once 'Bll/Hotel/Feed.php';
            $bllFeed = new Bll_Hotel_Feed();
            $bllFeed->newFeedMessage(1, $userInfo['uid'], null, null, 10000, 1);

            /*$tableName = 'hotel_system_message';
            $dalFeed->insertFeed($set3, 'hotel_feed_tal');*/


            $this->_wdb->commit();

            $result = true;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return $result;
        }
        return $result;
    }


    public function upBuild($uid,$colName)
    {
        $resultArray = array('result' => -1);
        require_once 'Dal/Hotel/Huser.php';
        $dalHuser = Dal_Hotel_Huser::getDefaultInstance();
        $tempId = 4;
        try {
            $this->_wdb->beginTransaction();

            //get destination table name
            switch (substr($colName,0,4)) {
                case 'room' :
                    $table = 'hotel_room_type';
                    break;
                case 'rest' :
                    $table = 'hotel_restaurant_type';
                    break;
                case 'mana' :
                	$tempId = 11;
                    $table = 'hotel_manager_type';
                    break;
                case 'rece' :
                    $table = 'hotel_reception_type';
                    break;
                default :
                    $resultArray['result'] = -2;
                    return $resultArray;
            }

            //get ROOM currentLv
            $currentLv = $dalHuser->getOneData($uid,$colName,'hotel_user_room');

            // get update_money and update_time
            $priceAndTime = $dalHuser->getPriceTime($table,$currentLv);

            // "1" means "building update"
            $type = 1;

            //in update process or not
            $inUpProcess = $dalHuser->inUpProcess($uid,$type);

            //get money
            $money = $dalHuser->getOneData($uid,'money');

            //in update process or money not enough,return error
            if ($inUpProcess || $priceAndTime['update_money'] > $money || !$priceAndTime) {
                $resultArray['result'] = -3;
                return $resultArray;
            }

            $experience = $dalHuser->getOneData($uid,'experience');
            $userLv = $dalHuser->getUserLvByExp(intval($experience) + intval($priceAndTime['update_money']/10000));
            $nextExp = $dalHuser->getExpByLv($userLv + 1);
            
            //set infoArray that will be updated
            $set = array ('money' => $money - $priceAndTime['update_money'],
                          'experience' => $experience + intval($priceAndTime['update_money']/10000),
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
            $dalHuser->insertUpProcess($set2);

            //insert feed
            require_once 'Dal/Hotel/Feed.php';
            $dalFeed = Dal_Hotel_Feed::getDefaultInstance();
            $nextLv = $currentLv + 1;
            /*$set3 = array('uid' => $uid,
                          'template_id' => 3,
                          'title' => '{"buildName":"' . $colName . '","currentLv":"' . $currentLv . '","nextLv":"' . $nextLv . '","money":"' . $priceAndTime['update_money'] . '"}',
                          'create_time' => time());*/
            require_once 'Bll/Hotel/Feed.php';
            $bllFeed = new Bll_Hotel_Feed();

            $aryInfo = $tempId == 4 ? array('{*level*}' => $nextLv) : null;
            $bllFeed->newFeedMessage($tempId, $uid, null, $aryInfo, '-' . $priceAndTime['update_money'], 1);

            //$dalFeed->insertFeed($set3, 'hotel_feed_tal');

            $this->_wdb->commit();

            $resultArray['result'] = 1;
            $resultArray['roomUpName'] = $colName;
            $resultArray['currentLv'] = $currentLv;       //test: + 1 immidiatly
            $resultArray['update_time'] = $priceAndTime['update_time'];
            $resultArray['update_money'] = $priceAndTime['update_money'];
            $resultArray['experience'] = $set['experience'];
            $resultArray['level'] = $userLv;
			$resultArray['next_exp'] = $nextExp;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return $resultArray;
        }
        return $resultArray;
    }

    public function upBuildOver($uid)
    {
        $resultArray = array('result' => -1);
        require_once 'Dal/Hotel/Huser.php';
        $dalHuser = Dal_Hotel_Huser::getDefaultInstance();

        try {
            $this->_wdb->beginTransaction();

            // "1" means "building update"
            $type = 1;

            //in update process or not
            $inUpProcess = $dalHuser->inUpProcess($uid,$type);

            if ($inUpProcess) {
                $resultArray['result'] = -2;
                return $resultArray;
            }

            $t = time();
            //update room
            $re = $dalHuser->updateRoom($uid, $type, $t);

            //set updateInfo to operated
            $dalHuser->setOperated($re['id']);

            //get destination table name
            switch (substr($re['name'],0,4)) {
                case 'room' :
                    $table = 'hotel_room_type';
                    break;
                case 'rest' :
                    $table = 'hotel_restaurant_type';
                    break;
                case 'mana' :
                    $table = 'hotel_manager_type';
                    break;
                case 'rece' :
                    $table = 'hotel_reception_type';
                    break;
                default :
                    $resultArray['result'] = -3;
                    return $resultArray;
            }
            //get next level price
            $priceAndTime = $dalHuser->getPriceTime($table,$re['currentLv'] + 1);

            //get next level living_in and fee
            $liveAndFee = $dalHuser->getLiveFee($table,$re['currentLv'] + 1);
            //get next_next level living_in and fee
            $liveAndFeeNext = $dalHuser->getLiveFee($table,$re['currentLv'] + 2);

            //insert feed
            require_once 'Dal/Hotel/Feed.php';
            $dalFeed = Dal_Hotel_Feed::getDefaultInstance();
            $nextLv = $re['currentLv'] + 1;
            /*$set3 = array('uid' => $uid,
                          'template_id' => 12,
                          'title' => '{"buildName":"' . $re['name'] . '","currentLv":"' . $re['currentLv'] . '","nextLv":"' . $nextLv . '"}',
                          'create_time' => time());
            $tableName = 'hotel_system_message';*/

            require_once 'Bll/Hotel/Feed.php';
            $bllFeed = new Bll_Hotel_Feed();
            $bllFeed->newFeedMessage(13, $uid, null, null, 0, 1);

            //$dalFeed->insertFeed($set3, 'hotel_feed_tal');

            $this->_wdb->commit();

            //begin add customer event param
            require_once 'Bll/Hotel/Cus.php';
            $bllCus = new Bll_Hotel_Cus();
            $cusResult = $bllCus->cusInsert($uid);
            $resultArray += $cusResult;
            //end add customer event param

            $resultArray['result'] = 1;
            $resultArray['roomUpName'] = $re['name'];

            $resultArray['currentLv'] = $re['currentLv'] + 1;
            $resultArray['nextPrice'] = $priceAndTime['update_money'];
            $resultArray['living_in'] = $liveAndFee['living_in'];
            $resultArray['fee'] = $liveAndFee['fee'];
            $resultArray['living_in_next'] = $liveAndFeeNext['living_in'];
            $resultArray['fee_next'] = $liveAndFeeNext['fee'];

        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return $resultArray;
        }
        return $resultArray;
    }

    /**
     * update Huser
     *
     * @param string $uid
     * @param string $colname
     * @param string $value
     * @return 0 or 1
     */
    public function updateHuser($uid,$colname,$value)
    {
        $resultArray = array('result' => 0);
        try {
            require_once 'Dal/Hotel/Huser.php';
            $dalHuser = Dal_Hotel_Huser::getDefaultInstance();

            $this->_wdb->beginTransaction();

            //set infoArray that will be updated
            $set = array ($colname => $value );

            //update user info
            $dalHuser->upHuser($uid,$set);

            $this->_wdb->commit();

            $resultArray = array('result' => 1);
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return $resultArray;
        }
        return $resultArray;
    }

    /**
     * if build up over  UP build,,if tech up over ,UP Tech
     *
     * @param unknown_type $uid
     * @return unknown
     */
    public function handleUpdate($uid)
    {
        $resultArray = array('result' => 0);
        try {
            require_once 'Dal/Hotel/Huser.php';
            $dalHuser = Dal_Hotel_Huser::getDefaultInstance();

            $this->_wdb->beginTransaction();
            $t = time();
            $result = $dalHuser->tryGetUnoperated($uid, $t);
            if ($result[0]) {
                $dalHuser->setOperated($result[0]['id']);
                $dalHuser->updateAtOnce($uid, $result[0]['name'], $result[0]['type']);
            }
            if ($result[1]) {
                $dalHuser->setOperated($result[1]['id']);
                $dalHuser->updateAtOnce($uid, $result[1]['name'], $result[1]['type']);
            }
            $this->_wdb->commit();
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return $resultArray;
        }
        return $resultArray;
    }

    /**
     * get one friend info
     *
     * @param string $uid
     * @param string $fids
     * @param string $direction
     * @param string $position
     * @return unknown
     */
    public function profileFriend($uid,$direction,$position,$fids,$fid)
    {
        $resultArray = array('result' => 0);

        try {
            require_once 'Dal/Hotel/Huser.php';
            $dalHuser = Dal_Hotel_Huser::getDefaultInstance();

            $circle = $dalHuser->getCircle($uid,$fids);
            $circleLen = count($circle);
            //left or right visit
            if ($fid == '0' || $fid == $uid) {
                if ($fids == '') {
                    return $resultArray; //has no friend
                }
                if ($direction === null || $position === null) {
                    $resultArray['result'] = -1;
                    return $resultArray; //input data error
                }
                //get rank in friend circle
                if ($direction == 'left') {
                    if (intval($position) == 0) {
                        $rank = $circleLen - 1;
                    }
                    else {
                        $rank = intval($position) - 1;
                    }
                }
                else {
                    if (intval($position) == $circleLen - 1) {
                        $rank = 0;
                    }
                    else {
                        $rank = intval($position) + 1;
                    }
                }

                //get uid visit
                $uidNow = $circle[$rank]['uid'];
                if ($uid == $uidNow) {
                    $resultArray = $dalHuser->getFullData($uid,1);
                }
                else {
                	$resultArray = $dalHuser->getFullData($uidNow);
                }
                $resultArray['result'] = 1;
                $resultArray['position'] = $rank;
                $resultArray['fid'] = $uidNow;
            }

            //jump visit with fid
            else {
                require_once 'Bll/Friend.php';

                $isFriend = Bll_Friend::isFriend($uid,$fid);
                $isInHotel = $dalHuser->isInHotel($fid);

                if ($isFriend && $isInHotel) {
                    $resultArray = $dalHuser->getFullData($fid);
                    for ($i == 0; $i < $circleLen; $i++) {
                        if ($circle[$i]['uid'] == $fid) {
                            $resultArray['position'] = $circle[$i]['rank'];
                            break;
                        }
                    }
                    $resultArray['result'] = 1;
                    $resultArray['fid'] = $fid;
                }
                else {
                    $resultArray = array('result' => -2);
                }
            }
            
            //make false location 
            if ($resultArray['location'] == 0) {
            	$resultArray['location'] = 2;
            }

        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return $resultArray;
        }
        return $resultArray;
    }
}
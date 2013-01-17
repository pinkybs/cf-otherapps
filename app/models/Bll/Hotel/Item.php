<?php
/** @see Bll_Abstract.php */
require_once 'Bll/Abstract.php';

/**
 * hotel Item logic's Operation
 *
 * @package    Bll
 * @copyright  Copyright (c) 2009 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/09/16    zhaoxh
 */
class Bll_Hotel_Item extends Bll_Abstract
{
    /**
     * buy Item
     *
     * @param string sid
     * @return array
     */
    public function buyItem($uid,$sid,$buyNum=1)
    {
        $resultArray = array('result' => -1);

        try {
            require_once 'Dal/Hotel/Item.php';
            $dalItem = Dal_Hotel_Item::getDefaultInstance();

            //get price and number of sid-item
            $price = $dalItem->getItemPrice($sid);
            $itemNum = $dalItem->getItemNum($uid,$sid);

            require_once 'Dal/Hotel/Huser.php';
            $dalHuser = Dal_Hotel_Huser::getDefaultInstance();
            $money = $dalHuser->getOneData($uid,'money');
            $mixipoint = $dalHuser->getOneData($uid,'mixipoint');
            $this->_wdb->beginTransaction();

            //buy item by money
            if ($price * $buyNum <= $money && $price > 0) {
                if ($itemNum == 0) {
                    //insert user item
                    $itemInfo = array('uid' => $uid,
                                      'sid' => $sid,
                                      'number' => $itemNum + $buyNum);
                    $dalItem->insertItem($itemInfo);
                }
                else {
                    //number + 1
                    $dalItem->addItem($uid,$sid,$buyNum);
                }
                
                $experience = $dalHuser->getOneData($uid,'experience') + intval(($price * $buyNum)/10000);
            	$userLv = $dalHuser->getUserLvByExp(intval($experience));
            	$nextExp = $dalHuser->getExpByLv($userLv + 1);
            	
            	//set infoArray that will be updated
            	$set = array ('money' => $money - $price * $buyNum,
                              'experience' => $experience,
                              'level' => $userLv);
                
                //update user info
                $dalHuser->upHuser($uid,$set);
            }
            //buy item by mixipoint
            else if ($price < 0 && $price * (-1) * $buyNum <= $mixipoint){
           	    if ($itemNum == 0) {
                    //insert user item
                    $itemInfo = array('uid' => $uid,
                                      'sid' => $sid,
                                      'number' => $itemNum + $buyNum);
                    $dalItem->insertItem($itemInfo);
                }
                else {
                    //number + 1
                    $dalItem->addItem($uid,$sid,$buyNum);
                }
                //set infoArray that will be updated
                $set = array ('mixipoint' => $mixipoint - $price * $buyNum * (-1));
                //update user info          
                $dalHuser->upHuser($uid,$set);
                $set['money'] = $money;
                
                $experience = $dalHuser->getOneData($uid,'experience');
            	$userLv = $dalHuser->getUserLvByExp(intval($experience));
            	$nextExp = $dalHuser->getExpByLv($userLv + 1);
            }

            else {
                $resultArray = array('result' => -2);      //not enough money error
                return $resultArray;
            }

            require_once 'Bll/Hotel/Feed.php';
            $bllFeed = new Bll_Hotel_Feed();
            $bllFeed->newFeedMessage(10, $uid, null, null, $price, 1);

            $this->_wdb->commit();

            $resultArray = array('result' => 1);
			$resultArray['money'] = $set['money'];
			$resultArray['buynum'] = $buyNum;
			$resultArray['exp'] = $experience;
			$resultArray['level'] = $userLv;
			$resultArray['next_exp'] = $nextExp;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return $resultArray;
        }
        return $resultArray;
    }

    /**
     * use item by sid
     *
     * @param string $uid
     * @param string $sid
     * @return array
     */
    public function useItem($uid,$sid,$fid)
    {
        $resultArray = array('result' => -1);

        try {
            require_once 'Dal/Hotel/Item.php';
            $dalItem = Dal_Hotel_Item::getDefaultInstance();

            //get item number
            $itemNum = $dalItem->getItemNum($uid,$sid);

            $this->_wdb->beginTransaction();

            if ($itemNum > 1) {
                //number - 1
                $dalItem->addItem($uid,$sid,-1);
            }
            else if ($itemNum ==1) {
                $dalItem->delItem($uid,$sid);
            }
            else {
                //item number error
                $resultArray = array('result' => -2);
                return $resultArray;
            }

            //get item effect
            $effect = $this->_itemEffect($uid,$sid,$fid);

            //effect error
            if ($effect == -1) {
                $resultArray = array('result' => -3);
                return $resultArray;
            }

            //$itemInfo = $dalItem->getItemInfo($sid);
            //insert feed
            /*require_once 'Dal/Hotel/Feed.php';
            $dalFeed = Dal_Hotel_Feed::getDefaultInstance();
            $set3 = array('uid' => $uid,
                          'template_id' => 15,
                          'title' => '{"sname":"' . $itemInfo['name'] . '"}',
                          'create_time' => time());
            $tableName = 'hotel_system_message';
            $dalFeed->insertFeed($set3, $tableName);*/

            require_once 'Bll/Hotel/Feed.php';
            $bllFeed = new Bll_Hotel_Feed();
            $bllFeed->newFeedMessage(12, $uid, null, null, 0, 1);

            $this->_wdb->commit();

            $resultArray = array('result' => $effect);

        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return $resultArray;
        }
        return $resultArray;
    }






    /**
     * get item effect
     *
     * @param string $uid
     * @param string $sid
     * @return string
     */
    function _itemEffect($uid,$sid,$fid=0)
    {
        
        switch ($sid) {
            case 1 :
                break;
            case 4:
                break;
            case 5:
            case 6:
            case 7:
                require_once 'Dal/Hotel/Huser.php';
                $dalHuser = Dal_Hotel_Huser::getDefaultInstance();

                $processInfo = $dalHuser->getProcessInfo($uid,'1');
                $time = time();
                
        		if ($time > $processInfo['over_time'] || $time < $processInfo['begin_time']) {
                    return '-' . $sid . '00';                       //return false
                }                                              
                switch ($sid) {
                    case 5 :
                        $reduceTime = 3600;
                        break;
                    case 6 :
                        $reduceTime = 3600*3;
                        break;
                    case 7 :
                    	//get destination table name
			            switch (substr($processInfo['name'],0,4)) {
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
			                    return -1;
			            }
			            //get update time
			            $updateTime = $dalHuser->getUpdateInfo($uid,$table,1,$time);
                    	
                    	
                        $reduceTime = $updateTime['update_time'] / 2; //ceil(($processInfo['over_time'] - $processInfo['begin_time'])/2);
                        break;
                }

                
                if ($processInfo['over_time'] - $processInfo['begin_time'] <= $reduceTime) {
                    $dalHuser->updateProcess($processInfo['id'], $processInfo['begin_time']);
                    return 100;     // flash {building = ''}
                }
                else {
                    $dalHuser->updateProcess($processInfo['id'], $processInfo['over_time'] - $reduceTime);
                    return 200;    //refresh flash {do nothing}
                }
                break;
            
            case 2 :
            case 3 :
            case 8 :
            case 9 :
                require_once 'Dal/Hotel/Item.php';
                $dalItem = Dal_Hotel_Item::getDefaultInstance();
                
                $info = $dalItem->getItemTimeInfo($uid,$sid);
                
                switch ($sid) {
                	case 2 :
                		$t = 3600*24*3;
                		if ($info['max(over_time)'] > time()) {
                			return -200;                 //item in effect ,can NOT Continuous use
                		}
                		break;
                	case 3 :
                		$t = 3600*24*7;
                		if ($info['max(over_time)'] > time()) {
                			return -300;                 //item in effect ,can NOT Continuous use
                		}
                		break;
                	case 8 :
                	case 9 :
                		$t = 3600*24;
                		if ($info['max(over_time)'] > time()) {
                			$dalItem->updateItemTime($info['max(id)'],$t);  //item in effect ,can Continuous use
                			return $sid;                
                		}
                		break;
                	default :
                		break;	
                }
                $itemTimeInfo = array('uid' => $uid,
                                      'sid' => $sid,
                                      'begin_time' => time(),
                                      'over_time' => time() + $t);
                $dalItem->insertItemTime($itemTimeInfo);
                break;

            case 10 :
                require_once 'Dal/Hotel/Friend.php';
                $dalFriend = Dal_Hotel_Friend::getDefaultInstance();
                $learnCntTotal = $dalFriend->getLearnCountById($uid);
                $learnerInfo =array('uid' => $uid,
                                    'fid' => 0,
                                    'create_time' => time(),
                                    'index' => $learnCntTotal + 1);
                $dalFriend->insertLearner($learnerInfo);
                break;
            case 11 :
                break;
            case 12 :
                break;
            default :
                return -1;
        }
        return $sid;
    }
}
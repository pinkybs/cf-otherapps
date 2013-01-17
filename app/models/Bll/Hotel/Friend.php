<?php
/** @see Bll_Abstract.php */
require_once 'Bll/Abstract.php';

/**
 * hotel Friend logic's Operation
 *
 * @package    Bll
 * @copyright  Copyright (c) 2009 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/09/16    zhaoxh
 */
class Bll_Hotel_Friend extends Bll_Abstract
{
    /**
     * send a learner to friend
     *
     * @param string sid
     * @param string uid
     * @return array
     */
    public function sendLearner($uid,$fid)
    {
        $resultArray = array('result' => -1);

        try {

            require_once 'Dal/Hotel/Friend.php';
            $dalFriend = Dal_Hotel_Friend::getDefaultInstance();

            $hasIdleLearner = $dalFriend->hasLearner($uid);
            $noLearnerAtFriend = $dalFriend->noLearnerAt($uid,$fid);
            $friendHasPlace = $dalFriend->friendHasPlace($fid);

            $this->_wdb->beginTransaction();

            if ($hasIdleLearner && $noLearnerAtFriend && $friendHasPlace) {
                $dalFriend->setLearner($uid,$fid,$hasIdleLearner[0]['index'],time());

                //edit data return to FLEX
                $editArray = array('uid' => $uid);
                require_once 'Bll/User.php';
	            Bll_User::appendPerson($editArray);
	            $learnCnt = $dalFriend->learnCnt($uid);

                $resultArray =array('learnuid' => $uid,
                                    'fid' => $fid,
                                    'learnmoney' => 0,
                                    'displayName' => $editArray['displayName'],
                                    'learnindex' => $hasIdleLearner[0]['index'],
                                    'learnCnt' => $learnCnt,
                                    'result' => 1);

                //insert feed
                /*require_once 'Dal/Hotel/Feed.php';
                $dalFeed = Dal_Hotel_Feed::getDefaultInstance();

                $set3 = array('uid' => $uid,
                              'template_id' => 7,
                              'target' => $fid,
                              'create_time' => time());
                $tableName = 'hotel_user_feed';
                $dalFeed->insertFeed($set3, $tableName);

                //insert feed 2
                $set4 = array('uid' => $fid,
                              'template_id' => 6,
                              'actor' => $uid,
                              'create_time' => time());
                $dalFeed->insertFeed($set4, $tableName);*/

                require_once 'Bll/Hotel/Feed.php';
	            $bllFeed = new Bll_Hotel_Feed();
                require_once 'Bll/User.php';
                $userInfo = Bll_User::getPerson($fid);

                $userName = $userInfo->getDisplayName();
	            $aryInfo = array('{*displayName*}' => $userName);
	            $bllFeed->newFeedMessage(14, $uid, null, $aryInfo, 0, 1);

                $userInfo = Bll_User::getPerson($uid);
                $userName = $userInfo->getDisplayName();
                $aryInfo = array('{*displayName*}' => $userName);
                $bllFeed->newFeedMessage(15, $uid, $fid, $aryInfo, 0, 1);

	            /*require_once 'Bll/Hotel/Feed.php';
                $bllFeed = new Bll_Hotel_Feed();
                $bllFeed->newFeedMessage(14, $uid, null, 0, 1);*/
            }
            if (!$hasIdleLearner) {
                $resultArray = array('result' => -2,
                                     'fid' => $fid  );
            }
            if (!$noLearnerAtFriend) {
                $resultArray = array('result' => -3);
            }
            if (!$friendHasPlace) {
                $resultArray = array('result' => -4);
            }

            $this->_wdb->commit();

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

    /**
     * call a learner back
     *
     * @param string $uid
     * @param string $index
     * @return array
     */
    public function cbkLearner($uid,$index)
    {
        $resultArray = array('result' => -1);

        try {
            require_once 'Dal/Hotel/Friend.php';
            $dalFriend = Dal_Hotel_Friend::getDefaultInstance();
            $isIn30min = $dalFriend->inTimeLimit($uid,$index);
            $learnFid = $dalFriend->getFid($uid,$index);

            $this->_wdb->beginTransaction();

            if (!$isIn30min && $learnFid != 0) {
                $dalFriend->setLearner($uid,0,$index);

                $earn = $dalFriend->earnLearner($uid,$index);


                require_once 'Dal/Hotel/Huser.php';
                $dalHuser = Dal_Hotel_Huser::getDefaultInstance();
                $money = $dalHuser->getOneData($uid,'money');
                //earn learner money
                $set = array ('money' => $money + $earn);
                $dalHuser->upHuser($uid,$set);

                $resultArray['result'] = 1;
                $resultArray['earn'] = $earn;
                $resultArray['index'] = $index;


                //insert feed
                require_once 'Dal/Hotel/Feed.php';
                $dalFeed = Dal_Hotel_Feed::getDefaultInstance();

                /*$set3 = array('uid' => $uid,
                              'template_id' => 11,
                              'target' => $learnFid,
                              'title' => '{"money":"' . $earn . '"}',
                              'create_time' => time());
                $tableName = 'hotel_user_feed';
                $dalFeed->insertFeed($set3, $tableName);*/

                //insert feed 2
                /*$set4 = array('uid' => $learnFid,
                              'template_id' => 10,
                              'actor' => $uid,
                              'title' => '{"money":"' . $earn . '"}',
                              'create_time' => time());
                $dalFeed->insertFeed($set4, $tableName);*/

                require_once 'Bll/Hotel/Feed.php';
                $bllFeed = new Bll_Hotel_Feed();
                require_once 'Bll/User.php';
                $userInfo = Bll_User::getPerson($learnFid);
                $userName = $userInfo->getDisplayName();
                $aryInfo = array('{*displayName*}' => $userName);
                $bllFeed->newFeedMessage(8, $uid, null, $aryInfo, $earn, 1);

                require_once 'Bll/User.php';
                $userInfo = Bll_User::getPerson($uid);
                $userName = $userInfo->getDisplayName();
                $aryInfo = array('{*displayName*}' => $userName);
                $bllFeed->newFeedMessage(17, $uid, $learnFid, $aryInfo, 0, 1);
            }
            if ($isIn30min) {
                $resultArray = array('result' => -2);
            }
            if ($learnFid == 0) {
                $resultArray = array('result' => -3);
            }

            $this->_wdb->commit();

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

    /**
     * banish friend learner
     *
     * @param string $uid
     * @param string $fid
     * @param string $index
     * @return array
     */
    public function banishLearner($uid,$learnUid,$index,$place)
    {
        $resultArray = array('result' => 0);

        try {
            require_once 'Dal/Hotel/Friend.php';
            $dalFriend = Dal_Hotel_Friend::getDefaultInstance();

            $isIn30min = $dalFriend->inTimeLimit($learnUid,$index);
            $learnFid = $dalFriend->getFid($learnUid,$index);

            $this->_wdb->beginTransaction();

            if (!$isIn30min && $learnFid == $uid) {
                $dalFriend->setLearner($learnUid,0,$index);

                $earn = $dalFriend->earnLearner($learnUid,$index);

                require_once 'Dal/Hotel/Huser.php';
                $dalHuser = Dal_Hotel_Huser::getDefaultInstance();
                $money = $dalHuser->getOneData($uid,'money');
                //earn learner money
                $set = array ('money' => $money + $earn);
                $dalHuser->upHuser($uid,$set);

                //edit data return to FLEX
                $editArray = array('uid' => $learnUid);
                require_once 'Bll/User.php';
	            Bll_User::appendPerson($editArray);

                $resultArray = array('result' => 1,
                                     'earn' => $earn,
                                     'displayName' => $editArray['displayName'],
                                     'learnUid' => $learnUid,
                                     'place' => $place);

                //insert feed
                require_once 'Dal/Hotel/Feed.php';
                $dalFeed = Dal_Hotel_Feed::getDefaultInstance();

                /*$set3 = array('uid' => $uid,
                              'template_id' => 9,
                              'target' => $learnUid,
                              'title' => '{"money":"' . $earn . '"}',
                              'create_time' => time());
                $tableName = 'hotel_user_feed';
                $dalFeed->insertFeed($set3, $tableName);

                //insert feed 2
                $set4 = array('uid' => $learnUid,
                              'template_id' => 8,
                              'actor' => $uid,
                              'create_time' => time());
                $dalFeed->insertFeed($set4, $tableName);*/

                require_once 'Bll/Hotel/Feed.php';
                $bllFeed = new Bll_Hotel_Feed();
                require_once 'Bll/User.php';
                $userInfo = Bll_User::getPerson($learnUid);
                $userName = $userInfo->getDisplayName();
                $aryInfo = array('{*displayName*}' => $userName);
                $bllFeed->newFeedMessage(9, $uid, null, $aryInfo, $earn, 1);

                require_once 'Bll/User.php';
                $userInfo = Bll_User::getPerson($uid);
                $userName = $userInfo->getDisplayName();
                $aryInfo = array('{*displayName*}' => $userName);
                $bllFeed->newFeedMessage(16, $uid, $learnUid, $aryInfo, 0, 1);

            }
            if ($isIn30min) {
                $resultArray = array('result' => -1);
            }
            if ($learnFid != $uid) {
                $resultArray = array('result' => -2);
            }

            $this->_wdb->commit();

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

    public function clean($uid,$fid)
    {
        $resultArray = array('result' => -1);

        try {
            require_once 'Dal/Hotel/Friend.php';
            $dalFriend = Dal_Hotel_Friend::getDefaultInstance();

            $this->_wdb->beginTransaction();

            $dalFriend->clean($fid);
            if ($uid != $fid) {
                //feed
                require_once 'Bll/Hotel/Feed.php';
	            $bllFeed = new Bll_Hotel_Feed();
	            require_once 'Bll/User.php';
                $userInfo = Bll_User::getPerson($fid);
                $userName = $userInfo->getDisplayName();
                $aryInfo = array('{*displayName*}' => $userName);
	            $bllFeed->newFeedMessage(6, $uid, $fid, $aryInfo, 2000, 1);
            } else {
            	require_once 'Bll/Hotel/Feed.php';
	            $bllFeed = new Bll_Hotel_Feed();
	            $bllFeed->newFeedMessage(7, $uid, null, $aryInfo, '-2000', 1);
            }

            //insert feed
            /*require_once 'Dal/Hotel/Feed.php';
            $dalFeed = Dal_Hotel_Feed::getDefaultInstance();

            $set3 = array('uid' => $fid,
                          'template_id' => 14,
                          'actor' => $uid,
                          'create_time' => time());
            $tableName = 'hotel_user_feed';
            $dalFeed->insertFeed($set3, $tableName);*/

            $this->_wdb->commit();

            $resultArray['result'] = 1;
            $resultArray['fid'] = $fid;

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

    /**
     * change customer with your friend
     *
     * @param string $uid
     * @param string $fid
     * @return array
     */
	public function exchange($uid,$fid)
    {
        $resultArray = array('result' => -1);

        try {
            require_once 'Dal/Hotel/Friend.php';
            $dalFriend = Dal_Hotel_Friend::getDefaultInstance();

            $this->_wdb->beginTransaction();

            $cnt = $dalFriend->exchangeCnt($uid);
            $exchanged = $dalFriend->todayExchanged($uid,$fid);

            if ($cnt > 0 && !$exchanged) {
	            $set1 = array('uid' => $uid,
	                          'fid' => $fid,
	                          'create_time' => time());

	            $dalFriend->insertExchange($set1);
	            $resultArray['result'] = 1;
	            $cntNew = $cnt - 1;
            }
            if ($cnt <= 0){
            	$resultArray['result'] = -2; //has no  exchange chance today
            }
            if ($exchanged) {
            	$resultArray['result'] = -3; //today has exchanged with fid
            }

            //feed

            require_once 'Bll/Hotel/Feed.php';
            $bllFeed = new Bll_Hotel_Feed();
            require_once 'Bll/User.php';
            $userInfo = Bll_User::getPerson($fid);
            $userName = $userInfo->getDisplayName();
            $aryInfo = array('{*displayName*}' => $userName);
            $bllFeed->newFeedMessage(18, $uid, null, $aryInfo, 0, 1);

            $userInfo = Bll_User::getPerson($uid);
            $userName = $userInfo->getDisplayName();
            $aryInfo = array('{*displayName*}' => $userName);
            $bllFeed->newFeedMessage(19, $uid, $fid, $aryInfo, 0, 1);

            $this->_wdb->commit();

            $resultArray['cntNew'] = $cntNew;
            $resultArray['fid'] = $fid;
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
}
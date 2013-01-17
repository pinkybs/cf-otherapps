<?php
/** @see Bll_Abstract.php */
require_once 'Bll/Abstract.php';

/** @see Dal_Millionminds_Muser.php */
require_once 'Dal/Millionminds/Muser.php';
/**
 * millionminds profile logic's Operation
 *
 * @package    Bll
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/07/27    Liz
 */
class Bll_Millionminds_Profile extends Bll_Abstract
{
    /**
     * finish nature answer
     *
     * @param integer $uid
     * @return array
     */
    public function finishNatureAnswer($uid, $from_uid, $type)
    {
        $result = false;
        
        //get user nature info
        if ( $type == 1 ) {
            $userNature = $this->_getUserNatureByUid($uid);
        }
        else {
            $userNature = $this->_getFriendNature($uid, $from_uid);
        }
        
        if ( $userNature['group_id'] < 1 || $userNature['group_id'] > 30 ) {
            return $result;
        }
        
        $this->_wdb->beginTransaction();
        try {
            $dalMuser = Dal_Millionminds_Muser::getDefaultInstance();
            
            if ( $type == 1 ) {
                //update user nature
                $dalMuser->updateUserNature($uid, $userNature['group_id']);
            }
            else {
                $evaluation = array('uid'=>$uid,
                                    'from_uid'=>$from_uid,
                                    'nature_result'=>$userNature['group_id'],
                                    'create_time'=>time());
                
                //update friend evaluation
                $dalMuser->insertFriendEvaluation($evaluation);
            }
            
            $this->_wdb->commit();
            
            $result = true;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return $result = false;
        }
        
        return $result;
    }
    
    /**
     * get user nature info
     *
     * @param integer $uid
     * @return array
     */
    public function getUserNature($uid)
    {
        $result = array();
        
        $userNature = $this->_getUserNatureByUid($uid);
        
        $result['nature'] = $userNature['group_id'];
        
        return $result;
    }
    
    /**
     * get friend average evaluation
     *
     * @param integer $uid
     * @return array
     */
    public function getFriendAveEvaluation($uid)
    {        
        $dalMuser = Dal_Millionminds_Muser::getDefaultInstance();
        
        $fids = $dalMuser->getEvaluatedFid($uid);
        
        if ( $fids ) {
            $fidCount = count($fids);
            
            //get user question point by question type
            $moralityPoint = $dalMuser->getFriendAveQuestionPoint($uid, 1, $fids, $fidCount);
            $lovePoint = $dalMuser->getFriendAveQuestionPoint($uid, 2, $fids, $fidCount);
            $countPoint = $dalMuser->getFriendAveQuestionPoint($uid, 3, $fids, $fidCount);
            $instinctPoint = $dalMuser->getFriendAveQuestionPoint($uid, 4, $fids, $fidCount);
            $harmonyPoint = $dalMuser->getFriendAveQuestionPoint($uid, 5, $fids, $fidCount);
            
            //get point type
            $moralityType = $this->_getNatureTypeByPoint($moralityPoint);
            $loveType = $this->_getNatureTypeByPoint($lovePoint); 
            $countType = $this->_getNatureTypeByPoint($countPoint);
            $instinctType = $this->_getNatureTypeByPoint($instinctPoint);
            $harmonyType = $this->_getNatureTypeByPoint($harmonyPoint);
            
            //get average nature info
            $aveNature = $dalMuser->getNatureByType($moralityType, $loveType, $countType, $instinctType, $harmonyType);
            
            $natureInfo = $dalMuser->getNatureByGroupId($aveNature['group_id']);
            $result = $natureInfo['content_out'];
        }
        else {
            $result = '';
        }
        return $result;
    }
    
    /**
     * get user answer list
     *
     * @param integer $uid
     * @param integer $questionType
     * @param integer $pageIndex
     * @param integer $pageSize
     * @return array
     */
    public function getUserAnswer($uid, $questionType, $pageIndex, $pageSize, $visitor)
    {
        $result = array();
        
        $dalMuser = Dal_Millionminds_Muser::getDefaultInstance();
                
        $temp = $dalMuser->getUserAnswer($uid, $questionType, $pageIndex, $pageSize);
        
        $length = count($temp);
        $key = 'question_uid';
        
        require_once 'Bll/Friend.php';
        
        for ($i = 0; $i < $length; $i++) {
            //self
            if ($temp[$i][$key] == $visitor) {
                $result[] = $temp[$i];
            }
            else {
                //all open
                if ($temp[$i]['public_type'] == 1) {
                    $result[] = $temp[$i];
                }
                
                //friend open
                if ($temp[$i]['public_type'] == 2) {
                    //check is friend
                    if (Bll_Friend::isFriend($temp[$i][$key], $visitor)) {
                        $result[] = $temp[$i];
                    }
                }
                
                //friend'friend open
                if ($temp[$i]['public_type'] == 3) {
                    //check is friend's friend
                    if (Bll_Friend::isFriendFriend($temp[$i][$key], $visitor) || Bll_Friend::isFriend($temp[$i][$key], $visitor)) {
                        $result[] = $temp[$i];
                    }
                }
            }
        }
        
        $result ? Bll_User::appendPeople($result, 'question_uid') : $result;
        
        return $result;
    }

    /**
     * get complare result
     *
     * @param integer $groupId1
     * @param integer $groupId2
     * @return array
     */
    public function getComplare($groupId1, $groupId2)
    {        
        require_once 'Dal/Millionminds/Complare.php';
        $dalComplare = Dal_Millionminds_Complare::getDefaultInstance();
        
        $complareInfo = $dalComplare->getComplare($groupId1, $groupId2);
        
        return $complareInfo;
    }
    

    /**
     * get all app user rank info
     *
     * @param string $uid
     * @return array
     */
    public function getRankInfo($uid, $rankType)
    {        
        $dalMuser = Dal_Millionminds_Muser::getDefaultInstance();
        
        if ( $rankType == 1 ) {
            //get all user count
            $rankCount = $dalMuser->getAllAppUserCount();
            //get rank number about user
            $userRankNm = $dalMuser->getUserRankNmAllAppUser($uid);
        }
        else {
            require_once 'Bll/Friend.php';
            $friendIds = Bll_Friend::getFriendIds($uid);
            $friendIds = explode(',', $friendIds);
            //get my mixi count
            $rankCount = $dalMuser->getAppMyMixiCount($uid, $friendIds);
            //get rank number about user
            $userRankNm = $dalMuser->getUserRankNmMyMixi($uid, $uid, $friendIds);
        }
        
        //get start number
        $size = 7;

        if ($rankCount <= $size) {
            $start = 0;
            $size = $rankCount;
        }
        else if ( ($userRankNm + 2) >= $rankCount ) {
            $start = 0;
        }
        else if ( $userRankNm < 5 ){
            $start = $rankCount - $size;
        }
        else {
            $start = $rankCount - $userRankNm - 3;
        }

        //get rank info
        if ( $rankType == 1 ) {
            $rankInfo = $dalMuser->getRankingUserAllAppUser($start, $size);
            $lastUserRankNum = $dalMuser->getUserRankNmAllAppUser($rankInfo['0']['uid']);
        }
        else {            
            $rankInfo = $dalMuser->getRankingUserMyMixi($uid, $friendIds, $start, $size);
            $lastUserRankNum = $dalMuser->getUserRankNmMyMixi($rankInfo['0']['uid'], $uid, $friendIds);
        }
        
        $rankInfo ? Bll_User::appendPeople($rankInfo, 'uid') : $rankInfo;

        $uesrRankNm = ($rankCount-$start);
        
        //get invite array
        $onePageNm = 7;
        if ( count($rankInfo) < $onePageNm ) {
            $arrInvite = array();
            for ( $i=0, $icount = ($onePageNm - count($rankInfo)); $i<$icount; $i++ ) {
                $arrInvite[$i] = $i;
            }
        }
        
        $result = array('rankInfo' => $rankInfo, 
                        'userRankNm' => $uesrRankNm, 
                        'allCount' => $rankCount, 
                        'lastUserRankNum' => $lastUserRankNum,
                        'arrInvite' => $arrInvite,
                        'rankPrev' => $lastUserRankNum - ($onePageNm - 1));
        
        return $result;
    }

    /**
     * get more rank info
     *
     * @param integer $rankCount
     * @param integer $lastUserRankNum
     * @param integer $rankPrev
     * @param string $direction
     * @return array
     */
    public function getMoreRank($rankCount, $lastUserRankNum, $rankPrev, $direction, $rankType, $uid)
    {
        $dalMuser = Dal_Millionminds_Muser::getDefaultInstance();

        if ($direction == 'left') {
            //change to reverse order
            $reverseLastUserRankNum = $rankCount - $lastUserRankNum + 1;

            if ($rankCount - $lastUserRankNum <= 7) {
               $start = 0;
               $size = $rankCount - $lastUserRankNum;
            }
            else {
               $start = $reverseLastUserRankNum - 8;
               $size = 7;
            }
        }
        else if ($direction == 'right') {
            $reverseRankPre = $rankCount - $rankPrev + 1;
            $start = $reverseRankPre;
            $size = $rankPrev <= 7 ? $rankPrev : 7;
        }
        
        //get rank info
        if ( $rankType == 1 ) {
            $rankInfo = $dalMuser->getRankingUserAllAppUser($start, $size);
            $lastUserRankNum = $dalMuser->getUserRankNmAllAppUser($rankInfo['0']['uid']);
        }
        else {
            require_once 'Bll/Friend.php';
            $friendIds = Bll_Friend::getFriendIds($uid);
            $friendIds = explode(',', $friendIds);
            
            $rankInfo = $dalMuser->getRankingUserMyMixi($uid, $friendIds, $start, $size);
            $lastUserRankNum = $dalMuser->getUserRankNmMyMixi($rankInfo['0']['uid'], $uid, $friendIds);
        }
        
        $rankInfo ? Bll_User::appendPeople($rankInfo, 'uid') : $rankInfo;
        
        $result = array('rankInfo' => $rankInfo, 'lastUserRankNum' => $lastUserRankNum, 'rankPrev' => $lastUserRankNum-6);
        return $result;
    }
    
    /**
     * update visit log
     *
     * @param integer $uid
     * @param integer $visiterUid
     * @return array
     */
    public function updateVisitLog($uid, $visiterUid)
    {
        require_once 'Dal/Millionminds/Log.php';
        $dalMillionmindsLog = Dal_Millionminds_Log::getDefaultInstance();
        
        //get visit log info
        $visitLog = $dalMillionmindsLog->getVisitLog($uid, $visiterUid);
        if ( $visitLog ) {
            return;
        }
        
        $this->_wdb->beginTransaction();
        
        try {
            //delete visit log
            $dalMillionmindsLog->deleteVisitLog($uid, $visiterUid);
            
            $visitInfo = array('uid' => $uid, 'visiter_uid' => $visiterUid, 'visit_time' => date('Y-m-d H:i:s'));
            //insert visit log
            $dalMillionmindsLog->insertVisitLog($visitInfo);
            
            $this->_wdb->commit();
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return false;
        }
    }
    
    /**
     * get user nature
     *
     * @param integer $uid
     * @return array
     */
    public function _getUserNatureByUid($uid)
    {
        $dalMuser = Dal_Millionminds_Muser::getDefaultInstance();
        
        $result = array();
        
        //get user question point by question type
        $result['morality_point'] = $dalMuser->getUserQuestionPoint($uid, 1);
        $result['love_point'] = $dalMuser->getUserQuestionPoint($uid, 2);
        $result['count_point'] = $dalMuser->getUserQuestionPoint($uid, 3);
        $result['instinct_point'] = $dalMuser->getUserQuestionPoint($uid, 4);
        $result['harmony_point'] = $dalMuser->getUserQuestionPoint($uid, 5);
        
        $result['morality_type'] = $this->_getNatureTypeByPoint($result['morality_point']);
        $result['love_type'] = $this->_getNatureTypeByPoint($result['love_point']);
        $result['count_type'] = $this->_getNatureTypeByPoint($result['count_point']);
        $result['instinct_type'] = $this->_getNatureTypeByPoint($result['instinct_point']);
        $result['harmony_type'] = $this->_getNatureTypeByPoint($result['harmony_point']);
        
        //get user nature info
        $userNature = $dalMuser->getNatureByType($result['morality_type'], $result['love_type'], $result['count_type'], $result['instinct_type'], $result['harmony_type']);
        
        return $userNature;
    }

    /**
     * get user nature
     *
     * @param integer $uid
     * @return array
     */
    public function _getFriendNature($uid, $from_uid)
    {
        $dalMuser = Dal_Millionminds_Muser::getDefaultInstance();
        
        $result = array();
        
        //get user question point by question type
        $result['morality_point'] = $dalMuser->getFriendQuestionPoint($uid, $from_uid, 1);
        $result['love_point'] = $dalMuser->getFriendQuestionPoint($uid, $from_uid, 2);
        $result['count_point'] = $dalMuser->getFriendQuestionPoint($uid, $from_uid, 3);
        $result['instinct_point'] = $dalMuser->getFriendQuestionPoint($uid, $from_uid, 4);
        $result['harmony_point'] = $dalMuser->getFriendQuestionPoint($uid, $from_uid, 5);
        
        $result['morality_type'] = $this->_getNatureTypeByPoint($result['morality_point']);
        $result['love_type'] = $this->_getNatureTypeByPoint($result['love_point']);
        $result['count_type'] = $this->_getNatureTypeByPoint($result['count_point']);
        $result['instinct_type'] = $this->_getNatureTypeByPoint($result['instinct_point']);
        $result['harmony_type'] = $this->_getNatureTypeByPoint($result['harmony_point']);
        
        //get user nature info
        $userNature = $dalMuser->getNatureByType($result['morality_type'], $result['love_type'], $result['count_type'], $result['instinct_type'], $result['harmony_type']);
        
        return $userNature;
    }
    
    /**
     * get nature type by point
     *
     * @param integer $point
     * @return string
     */
    public function _getNatureTypeByPoint($point)
    {
        switch ( $point ) {
            case 0 : 
                $natureType = 'c';
                break;
            case 1 : 
                $natureType = 'c';
                break;
            case 2 : 
                $natureType = 'b';
                break;
            case 3 : 
                $natureType = 'b';
                break;
            case 4 : 
                $natureType = 'a';
                break;
            case 5 : 
                $natureType = 'a';
                break;
        }
        
        return $natureType;
    }
}
<?php
/** @see Bll_Abstract.php */
require_once 'Bll/Abstract.php';

/**
 * millionminds nature logic's Operation
 *
 * @package    Bll
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/07/27    Liz
 */
class Bll_Millionminds_Nature extends Bll_Abstract
{
    /**
     * clear user nature info
     *
     * @param integer $uid
     * @param integer $from_uid
     * @param integer $type
     * @return void
     */
    public function clearNature($uid, $from_uid, $type)
    {
        $result = false;

        $this->_wdb->beginTransaction();
        
        try {
            require_once 'Dal/Millionminds/Muser.php';
            $dalMuser = Dal_Millionminds_Muser::getDefaultInstance();
            
            require_once 'Dal/Millionminds/Useranswer.php';
            $dalUseranswer = Dal_Millionminds_Useranswer::getDefaultInstance();
            
            if ( $type == 1 ) {
                //update user nature
                $dalMuser->updateUserNature($uid, 0);
                
                //delete user nature answer
                $dalUseranswer->deleteUserNatureAnswer($uid);
                
                //update nature question answer count
                $dalUseranswer->updateNatureAnswerCount(-1);
            }
            else {
                //delete friend evaluation
                $dalMuser->deleteFriendEvaluation($uid, $from_uid);
                
                //delete friend evaluation answer
                $dalUseranswer->deleteFriendEvaluationAnswer($uid, $from_uid);
                
                //update nature question answer count
                //$dalUseranswer->updateNatureAnswerCount(-1);
            }
            
            $this->_wdb->commit();
            
            $result = true;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return $result;
        }
        return $result;
    }

    /**
     * insert millionminds user
     *
     * @param integer $userInfo
     * @return boolean
     */
    public function insertMillionmindsUser($userInfo)
    {
        $result = false;

        $this->_wdb->beginTransaction();
        
        try {
            require_once 'Dal/Millionminds/Muser.php';
            $dalMuser = Dal_Millionminds_Muser::getDefaultInstance();
            //insert user nature
            $dalMuser->insertMillionmindsUser($userInfo);
            
            $this->_wdb->commit();
            
            $result = true;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return $result;
        }
        return $result;
    }
        
    /**
     * add user nature answer
     *
     * @param integer $fid
     * @param integer $uid
     * @param integer $qid
     * @param integer $aid
     * @return boolean
     */
    public function addNatureAnswer($fid, $uid, $qid, $aid)
    {
        $result = false;

        if ( $qid > 50 || $qid < 1 || $aid > 3 || $aid < 1 ) {
            return $result;
        }
        
        //check user type
        if ( $fid == $uid ) {
            $userType = 1;
        }
        else {
            $userType = 0;
        }
        
        require_once 'Dal/Millionminds/Useranswer.php';
        $dalMillionmindsUseranswer = Dal_Millionminds_Useranswer::getDefaultInstance();
        //check had answered this question
        if ( $userType == 1 ) {
            $answer = $dalMillionmindsUseranswer->getUserAnswer($uid, $qid);
        }
        else {
            $answer = $dalMillionmindsUseranswer->getFriendAnswer($fid, $uid, $qid);
        }
        if ( $answer ) {
            return $result;
        }
        
        $this->_wdb->beginTransaction();
        
        try {
            $info = array('uid' => $uid,
                          'qid' => $qid,
                          'aid' => $aid,
                          'create_time' => time());
            
            //insert answer
            if ( $userType == 1 ) {
                $dalMillionmindsUseranswer->insertUserAnswer($info);
            }
            else {
                $info['uid'] = $fid;
                $info['from_uid'] = $uid;
                $dalMillionmindsUseranswer->insertFriendAnswer($info);
            }
            
            require_once 'Dal/Millionminds/Question.php';
            $dalQuestion = Dal_Millionminds_Question::getDefaultInstance();
            //update question answer count
            if ( $userType == 1 ) {
                $dalQuestion->updateQuestionAnswerCount($qid, 1);
            }
            
            $this->_wdb->commit();
            
            $result = true;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return $result;
        }
        return $result;
    }
}
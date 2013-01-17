<?php
/** @see Zend_Json */
require_once 'Zend/Json.php';

/** @see MyLib_Zend_Controller_Action_Ajax */
require_once 'MyLib/Zend/Controller/Action/Ajax.php';

/**
 * Chat Ajax Controllers
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/07/21   Huch
 */
class Ajax_MillionmindsController extends MyLib_Zend_Controller_Action_Ajax
{   
    /**
     * insert a new unapproved question
     *
     */
    public function insertquestionAction() 
    {
        if ($this->_request->isPost()) {
            $cat = $this->_request->getPost('cat');
        	$nickname = $this->_request->getPost('nickname');
        	$region = $this->_request->getPost('region');
        	$title = $this->_request->getPost('inputTitle');
        	$answerArray = $this->_request->getPost('answerArray');
        	$answerArray = Zend_Json::decode($answerArray);
        	$uid = $this->_user->getId();
        	require_once 'Bll/Millionminds/Question.php';
        	$bllQuestion = new Bll_Millionminds_Question();
        	
            $response = $bllQuestion->insertQuestion($cat,$nickname,$region,$uid,$title,$answerArray);
        	$response = Zend_Json::encode($response);
            echo $response;
        }
    }
    
    /**
     * approvelist select unapproved question
     *
     */
    public function approvelistAction() 
    {
        if ($this->_request->isPost()) {
            $order = $this->_request->getPost('dayOrder');
        	$pageSize = $this->_request->getPost('pageSize');
        	$pageIndex = $this->_request->getPost('pageIndex');
        	$type = $this->_request->getPost('type');
        	require_once 'Dal/Millionminds/Question.php';
            $dalMillionmindsQuestion = Dal_Millionminds_Question::getDefaultInstance();
            
        	$selectData = $dalMillionmindsQuestion->getUnapprovedQuestion($pageIndex,$pageSize,$type,$order);
        	$count=$dalMillionmindsQuestion->getUnapprovedQuestionCnt($type);
        	
        	$response = array('info' => $selectData, 'count' => $count);
        	$response = Zend_Json::encode($response);
            echo $response;
        }
    }
    
    /**
     * approve a question 
     *
     */
    public function approveAction() 
    {
        if ($this->_request->isPost()) {
            $type = $this->_request->getPost('cat');
        	$nickname_auth = $this->_request->getPost('nickname');
        	$public_type = $this->_request->getPost('region');
        	$question = $this->_request->getPost('inputTitle');
	        $uid = $this->_request->getPost('uid');
	        $id = $this->_request->getPost('id');
        	$answerArray = $this->_request->getPost('answerArray');
        	$answerArray = Zend_Json::decode($answerArray);        	
        	require_once 'Bll/Millionminds/Question.php';
            $bllMillionmindsQuestion = new Bll_Millionminds_Question();
            
        	$response = $bllMillionmindsQuestion->approveQuestion($type,$nickname_auth,$public_type,$question,$answerArray,$uid,$id);
        	$response = Zend_Json::encode($response);
            echo $response;
        }
    }
    
    /**
     * Deny a question 
     *
     */
    public function denyAction() 
    {
        if ($this->_request->isPost()) {
            
	        $id = $this->_request->getPost('id');
        	       	
        	require_once 'Bll/Millionminds/Question.php';
            $bllMillionmindsQuestion = new Bll_Millionminds_Question();
            
        	$response = $bllMillionmindsQuestion->denyQuestion($id);
        	$response = Zend_Json::encode($response);
            echo $response;
        }
    }
    
    /**
     * get question action
     *
     */
    public function getquestionAction()
    {
        if ($this->_request->isPost()) {
            $type = $this->_request->getPost('type');
        	$field = $this->_request->getPost('field');
        	
            require_once 'Bll/Millionminds/Question.php';
            $bllQuestion = new Bll_Millionminds_Question();
            $result = $bllQuestion->getQuestion($this->_user->getId(), 5, $type, $field);
            
            echo Zend_Json::encode($result);
        }
    }

    /**
     * get user answer action
     *
     */
    public function getuseranswerAction()
    {
        if ($this->_request->isPost()) {
            $uid = $this->_request->getPost('uid');
            $type = $this->_request->getPost('type');
            $pageIndex = $this->_request->getPost('pageIndex');
            $pageSize = $this->_request->getPost('pageSize');
            
            require_once 'Dal/Millionminds/Muser.php';
            $dalMillionmindsMuser = Dal_Millionminds_Muser::getDefaultInstance();
            
            //get user answer array
            //$userAnswer = $dalMillionmindsMuser->getUserAnswer($uid, $type, $pageIndex, $pageSize);
            
            //get user's question and new question
            require_once 'Bll/Millionminds/Profile.php';
            $bllProfile = new Bll_Millionminds_Profile();
            
            $pageSize = 6;
            //get user answer array
            //$userAnswer = $bllQuestion->getQuestion($uid, $pageSize, 2, 2, 1, $pageIndex, $type);
            $userAnswer = $bllProfile->getUserAnswer($uid, $type, $pageIndex, $pageSize, $this->_user->getId());

            //get user answer count
            $answerCount = $dalMillionmindsMuser->getUserAnswerCount($uid, $type);
           
            //get user info
            require_once 'Bll/User.php';
            $userInfo = array('uid' => $uid);
            Bll_User::appendPerson($userInfo);
            
            $response = array('userAnswer' => $userAnswer, 'answerCount' => $answerCount, 'userInfo' => $userInfo);
            echo Zend_Json::encode($response);
        }
    }
    
    public function getarchiveAction()
    {
        if ($this->_request->isPost()) {
            $type = $this->_request->getPost('type');
        	$field = $this->_request->getPost('field');
        	$order = $this->_request->getPost('order');
        	$pageIndex = $this->_request->getPost('pageIndex');
        	
            //get archive
            require_once 'Bll/Millionminds/Question.php';
            $bllQuestion = new Bll_Millionminds_Question();
            $question = $bllQuestion->getArchive($this->_user->getId(), $type, $field, $order, $pageIndex);
            
            require_once 'Dal/Millionminds/Question.php';
            $dalQuestion = Dal_Millionminds_Question::getDefaultInstance();
            $questionCount = $dalQuestion->getArchiveCount($type, $field);
            
            echo Zend_Json::encode(array('archive'=>$question,'count'=>$questionCount));
        }
    }
    
    /**
     * get more all user info
     *
     */
    public function getmorealluserAction()
    {
        if ($this->_request->isPost()) {
            $rankCount = $this->_request->getParam('rankCount');
            $lastUserRankNum = $this->_request->getParam('lastUserRankNum');
            $rankPrev = $this->_request->getParam('rankPrev');
            $direction = $this->_request->getParam('direction');
            $uid = $this->_user->getId();
            
            //get archive
            require_once 'Bll/Millionminds/Profile.php';
            $bllProfile = new Bll_Millionminds_Profile();
            $rankInfo = $bllProfile->getMoreRank($rankCount, $lastUserRankNum, $rankPrev, $direction, 1, $uid); 
            echo Zend_Json::encode($rankInfo);
        }
    }
    
    /**
     * get more my mixi info
     *
     */
    public function getmoremymixiAction()
    {
        if ($this->_request->isPost()) {
            $rankCount = $this->_request->getParam('rankCount');
            $lastUserRankNum = $this->_request->getParam('lastUserRankNum');
            $rankPrev = $this->_request->getParam('rankPrev');
            $direction = $this->_request->getParam('direction');
            $uid = $this->_user->getId();
            
            //get archive
            require_once 'Bll/Millionminds/Profile.php';
            $bllProfile = new Bll_Millionminds_Profile();
            $rankInfo = $bllProfile->getMoreRank($rankCount, $lastUserRankNum, $rankPrev, $direction, 2, $uid); 
            echo Zend_Json::encode($rankInfo);
        }
    }
}
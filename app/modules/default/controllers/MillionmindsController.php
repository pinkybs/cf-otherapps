<?php

/**
 * linno ap controller
 * init each index page
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/07/21	Huch
 */
class MillionmindsController extends MyLib_Zend_Controller_Action_Default
{
    /**
     * index action
     *
     */
    public function indexAction()
    {        
        require_once 'Dal/Millionminds/Muser.php';
        $dalMuser = Dal_Millionminds_Muser::getDefaultInstance();
        $this->view->beginNature = $dalMuser->checkUserBeginNature($this->_user->getId());
        
        //get popular question and new question
        require_once 'Bll/Millionminds/Question.php';
        $bllQuestion = new Bll_Millionminds_Question();
        $this->view->popularQuestion = $bllQuestion->getQuestion($this->_user->getId(), 5, 0, 1);
        $this->view->newQuestion = $bllQuestion->getQuestion($this->_user->getId(), 5, 0, 2);
        
        //get friend answer question
        require_once 'Bll/Friend.php';
        $fids = Bll_Friend::getFriends($this->_user->getId());
        
        if (count($fids) > 0) {
            require_once 'Dal/Millionminds/Question.php';
            $dalQuestion = Dal_Millionminds_Question::getDefaultInstance();
            $this->view->friendQuestion = $dalQuestion->getFriendAnswerQuestion($this->_user->getId(), $fids);
        }
        
        //show nature
        $this->_getNotAnswerNature();
        
        //nature complete,show radom question answer
        if (!$this->view->showNature) {
            $questionAnswer = $bllQuestion->getUserRandomAnswer($this->_user->getId());
            $this->view->questionAnswer = $questionAnswer;
            $this->view->qType = $questionAnswer['question']['qid'] > 50 ? 3 : 1;
        }
        
        $this->render();
    }
    
    /**
     * profile action
     *
     */
    public function profileAction()
    {
        $mindsUid = $this->_request->getParam('uid');
        
        require_once 'Dal/Millionminds/Muser.php';
        $dalMuser = Dal_Millionminds_Muser::getDefaultInstance();
        //get user million minds info
        $mindsUser = $dalMuser->getUser($mindsUid);

        if ( !$mindsUser ) {
            $mindsUid = $this->_user->getId();
            $mindsUser = $dalMuser->getUser($mindsUid);
        }

        require_once 'Bll/Millionminds/Profile.php';
        $bllProfile = new Bll_Millionminds_Profile();
        
        $uid = $this->_user->getId();
        Bll_User::appendPerson($mindsUser, 'uid');
        //check user type
        if ( $mindsUid == $uid ) {
            $userType = 1;
        }
        else {
            //check is friend
            $isFriend = Bll_Friend::isFriend($this->_user->getId(), $mindsUid);
            $userType = $isFriend ? 2 : 3;
            
            //get user million minds info
            $myMinds = $dalMuser->getUser($uid);
            
            //get complare info
            $complareInfo = Bll_Cache_Millionminds::getComplare($myMinds['nature_result'], $mindsUser['nature_result']);
            $complareInfo['content'] = str_replace('{%$nickname%}', $mindsUser['displayName'], $complareInfo['content']);
            $this->view->complareInfo = $complareInfo;
            
            $bllProfile->updateVisitLog($mindsUid, $uid);
        }
        $this->view->userType = $userType;
        
        //get user answer count
        require_once 'Dal/Millionminds/Useranswer.php';
        $dalUseranswer = Dal_Millionminds_Useranswer::getDefaultInstance();
        $mindsUser['answerCount'] = $dalUseranswer->getUserAnswerCount($mindsUid);
        $mindsUser['content_in'] = str_replace('{%$nickname%}', $mindsUser['displayName'], $mindsUser['content_in']);
        
        //get user near people array
        $userNearPeople = $dalMuser->getUserNearPeople($mindsUid);
        $userNearPeople ? Bll_User::appendPeople($userNearPeople, 'uid') : $userNearPeople;
        $this->view->userNearPeople = $userNearPeople;
        
        //get user's question and new question
        require_once 'Bll/Millionminds/Question.php';
        $bllQuestion = new Bll_Millionminds_Question();
        
        //get user answer array
        $pageSize = 6;
        $userAnswer = $bllProfile->getUserAnswer($mindsUid, 0, 1, $pageSize, $uid);
        
        //get user answer count
        $userAnswerCount = $dalMuser->getUserAnswerCount($mindsUid, 0);
        $this->view->userAnswer = $userAnswer;
        $this->view->userAnswerCount = $userAnswerCount;
        $this->view->userAnswerPageSize = $pageSize;
        
        //get friend evaluation
        $friendEvaluation = $bllProfile->getFriendAveEvaluation($mindsUid);
        $mindsUser['friend_evaluation'] = str_replace('{%$nickname%}', $mindsUser['displayName'], $friendEvaluation);
        
        //get user evaluation to minds user
        $mindsUser['user_evaluation'] = $dalMuser->getFriendEvaluation($mindsUid, $uid);
        $this->view->mindsUser = $mindsUser;
        
        //get all app user rank info
        $allAppRank = $bllProfile->getRankInfo($mindsUid, 1);
        $this->view->allAppRankArr = $allAppRank;
        
        //get my mixi user rank info
        $myMixiRank = $bllProfile->getRankInfo($mindsUid, 2);
        $this->view->myMixiRankArr = $myMixiRank;
        
        //get user create question list
        $this->view->userQuestion = $bllQuestion->getUserQuestion($mindsUid, 10, 1, $this->_user->getId());
        //get new question list
        $this->view->newQuestion = $bllQuestion->getQuestion($this->_user->getId(), 10, 0, 2);
        
        $this->render();
    }
    
    /**
     * nature action
     *
     */
    public function natureAction()
    {
        $mindsUid = $this->_request->getParam('uid', $this->_user->getId());
        $isAgain = $this->_request->getParam('isAgain');
        $qid = $this->_request->getParam('qid');
        $aid = $this->_request->getParam('aid');
        
        require_once 'Dal/Millionminds/Muser.php';
        $dalMuser = Dal_Millionminds_Muser::getDefaultInstance();
        
        require_once 'Dal/Millionminds/Question.php';
        $dalQuestion = Dal_Millionminds_Question::getDefaultInstance();
        
        require_once 'Bll/Millionminds/Nature.php';
        $bllNature = new Bll_Millionminds_Nature();
        
        require_once 'Bll/Millionminds/Profile.php';
        $bllProfile = new Bll_Millionminds_Profile();
        
        //get user minds info
        $mindsUser = $dalMuser->getUser($mindsUid);
        if ( !$mindsUser ) {
            $this->_redirect($this->_baseUrl . 'millionminds/index');
        }
        
        $uid = $this->_user->getId();
        //check is friend
        $isFriend = Bll_Friend::isFriend($uid, $mindsUid);
        
        //check user type
        if ( $mindsUid == $uid ) {
            $userType = 1;
        }
        else {
            if ( !$isFriend ) {
                $this->_redirect($this->_baseUrl . 'millionminds/index');
            }
            $userType = 0;
        }
        $this->view->userType = $userType;

        if ( $userType == 1 ) {
            //check had nature and is again
            if ( $mindsUser['nature_result'] > 0 ) {
                if ( $isAgain != 1 ) {
                    $this->_redirect($this->_baseUrl . 'millionminds/profile');
                }
                else {
                    //clear user nature info
                    $bllNature->clearNature($mindsUid, $uid, $userType);
                }
            }
            
            if ( $aid >= 1 && $aid <= 3 && $qid <= 50 && $qid >= 1 ) {
                //add user nature answer
                $result = $bllNature->addNatureAnswer($mindsUid, $uid, $qid, $aid, $userType);
                if ( $result ) {
                    $this->view->activityTitle = 'クエスチョンに回答しました。';
                }
            }
            
            //get user had not answered nature question
            $natureQuestion = $dalQuestion->getNotAnswerNature($mindsUid);
        }
        else {
            //get friend evaluation
            $friendEvaluation = $dalMuser->getFriendEvaluation($mindsUid, $uid);
            
            if ( $friendEvaluation ) {
                if ( $isAgain != 1 ) {
                    $this->_redirect($this->_baseUrl . 'millionminds/profile/uid' . $mindsUid);
                }
                else {
                    //clear user nature info
                    $bllNature->clearNature($mindsUid, $uid, $userType);
                }
            }
            
            if ( $aid >= 1 && $aid <= 3 && $qid <= 50 && $qid >= 1 ) {
                //add user nature answer
                $result = $bllNature->addNatureAnswer($mindsUid, $uid, $qid, $aid, $userType);
                if ( $result ) {
                    $this->view->activityTitle = 'クエスチョンに回答しました。';
                }
            }
            
            //get user had not answered nature question
            $natureQuestion = $dalQuestion->getFriendNotAnswerNature($mindsUid, $uid);
        }
                
        $this->view->question = $natureQuestion;
        
        if ( !$natureQuestion ) {
            //update user nature result
            $bllProfile->finishNatureAnswer($mindsUid, $uid, $userType);
            
            $this->_redirect($this->_baseUrl . 'millionminds/profile/uid/' . $mindsUid );
        }
        
        Bll_User::appendPerson($mindsUser, 'uid');
        $this->view->mindsUser = $mindsUser;
        
        $this->render();
    }

    /**
     * answer action
     */
    public function answerAction()
    {
        $qid = $this->_request->getParam('qid');
        $aid = $this->_request->getParam('aid');
        
        require_once 'Dal/Millionminds/Question.php';
        $dalQuestion = Dal_Millionminds_Question::getDefaultInstance();
        //get question info
        $question = $dalQuestion->getOneQst($qid);
        if ( !$question ) {
            $this->_redirect($this->_baseUrl . 'millionminds');
        }
        
        Bll_User::appendPerson($question, 'uid');
            
        require_once 'Bll/Millionminds/Question.php';
        $bllQuestion = new Bll_Millionminds_Question();
        
        require_once 'Dal/Millionminds/Useranswer.php';
        $dalUseranswer = Dal_Millionminds_Useranswer::getDefaultInstance();
        //get user answer about the question
        $userQuestionAnswer = $dalUseranswer->getUserAnswer($this->_user->getId(), $qid);

        $qType = $qid > 50 ? 2 : 1;
        
        if ( !$userQuestionAnswer ) {
            if ( $aid ) {
                //add user answer info about this question
                $insertAnswer = $bllQuestion->insertQuestionAnswer($this->_user->getId(), $qid, $aid);
                //get question info
                $question = $dalQuestion->getOneQst($qid);
                Bll_User::appendPerson($question, 'uid');
                if ( $insertAnswer == 1 ) {
                    $this->view->activityTitle = $qType == 1 ? 'クエスチョンに回答しました。' : $question['displayName'] . 'のクエスチョンに回答しました。';
                }
                else {
                    $this->_redirect($this->_baseUrl . 'millionminds/question/qid/' . $qid);
                }
            }
            else {
                $this->_redirect($this->_baseUrl . 'millionminds/question/qid/' . $qid);
            }
        }

        $this->view->qType = $qType;
        $this->view->userId = $this->_user->getId();
        
        //get this question answer info
        $result = $bllQuestion->getQuestionAnswer($qid, $question);
        $result['question'] = $question;
        $this->view->questionAnswer = $result;
        
        //show nature
        $this->_getNotAnswerNature();
        
        //get popular question list
        $this->view->popularQuestion = $bllQuestion->getQuestion($this->_user->getId(), 5, 0, 1);
        $this->view->popularQuestionSub = $bllQuestion->getQuestion($this->_user->getId(), 10, 0, 1);
        
        $this->render();
    }
    
    /**
     * archive action
     *
     */
    public function archiveAction()
    {
        $field = $this->_request->getParam('field', 1);        
        
        //get archive
        require_once 'Bll/Millionminds/Question.php';
        $bllQuestion = new Bll_Millionminds_Question();
        $this->view->question = $bllQuestion->getArchive($this->_user->getId(), 0, $field);
        
        require_once 'Dal/Millionminds/Question.php';
        $dalQuestion = Dal_Millionminds_Question::getDefaultInstance();
        $this->view->questionCount = $dalQuestion->getArchiveCount(0, $field);
        
        //get popular question
        $this->view->popularQuestion = $bllQuestion->getQuestion($this->_user->getId(), 10, 0, 1);
        
        $this->view->error = $this->_request->getParam('error');
        $this->render();
    }
    
    /**
     * log action
     *
     */
    public function logAction()
    {
        require_once 'Dal/Millionminds/Log.php';
        $dalMillionmindsLog = Dal_Millionminds_Log::getDefaultInstance();
        //get user visitlog info and count
        $visitLogArray = $dalMillionmindsLog->getUserVisitInfo($this->_user->getId());
        $visitLogCount = $dalMillionmindsLog->getUserVisitCount($this->_user->getId());
        
        $visitLogArray ? Bll_User::appendPeople($visitLogArray, 'visiter_uid') : $visitLogArray;
        
        $this->view->logArray = $visitLogArray;
        $this->view->logCount = $visitLogCount;
        
        //show nature
        $this->_getNotAnswerNature();
        
        require_once 'Bll/Millionminds/Question.php';
        $bllQuestion = new Bll_Millionminds_Question();
        $this->view->popularQuestionSub = $bllQuestion->getQuestion($this->_user->getId(), 10, 0, 1);
        
        $this->render();
    }
    
    public function welcomeAction()
    {
    	$this->render();
    }
    
    /**
     * new question action
     */
    public function newquestionAction()
    {   
        require_once 'Bll/Millionminds/Question.php';
        $bllQuestion = new Bll_Millionminds_Question();
        
        //get popular question data
        $this->view->popularQuestion = $bllQuestion->getQuestion($this->_user->getId(), 5, 0, 1);
        $this->view->popularQuestionSub = $bllQuestion->getQuestion($this->_user->getId(), 10, 0, 1);
        
        //show nature
        $this->_getNotAnswerNature();
        
    	$this->render();
    }
    
    /**
     * approvelist action
     *
     */
    public function approvelistAction()
    {   
        $uid = $this->_user->getId();
        if ($uid == 23815104) {
            require_once 'Dal/Millionminds/Question.php';
            $dalQuestion = Dal_Millionminds_Question::getDefaultInstance();
        	
            //get first page
        	$selectData = $dalQuestion->getUnapprovedQuestion(1,30,0);
        	$count=$dalQuestion->getUnapprovedQuestionCnt();
        	
        	$this->view->questionInfo = $selectData;
        	$this->view->questionCnt = $count;
        
            $this->render();
        }
        else {
            $this->_redirect($this->_baseUrl . '/millionminds');
        }
    }
    
    /**
     * approvelist action
     *
     */
    public function approveAction()
    {
        $uid = $this->_user->getId();
        if ($uid == 23815104) {
        	require_once 'Dal/Millionminds/Question.php';
            $dalQuestion = Dal_Millionminds_Question::getDefaultInstance();
            $id = $this->_request->getParam('id');
            $idCheck = $dalQuestion->isUnQstId($id);
            if ($idCheck) {
                //get a unauth question data
            	$selectDataQst = $dalQuestion->getUnQstById($id);
            	$selectDataAsw = $dalQuestion->getUnAswByQid($id);
        
            	$this->view->dataQst = $selectDataQst;
            	$this->view->dataAsw = $selectDataAsw;
                $this->view->dataAswCnt = count($selectDataAsw);
                $this->view->dataRubbCnt = 10-count($selectDataAsw);
                $this->render();
            }
            else {
                $this->_redirect($this->_baseUrl . '/millionminds/approvelist');
            }
        }
        else {
            $this->_redirect($this->_baseUrl . '/millionminds');
        }
        
    }
    
    /**
     * show question action   
     *
     */
    public function questionAction()
    {   
        require_once 'Bll/Millionminds/Question.php';
        $bllQuestion = new Bll_Millionminds_Question();
        $qid = $this->_request->getParam('qid');
        $edit = $this->_request->getParam('edit');
        $uid = $this->_user->getId();
        
        //check question
        $result = $bllQuestion->questionCheck($uid,$qid);
        
        //has answered or not
        require_once 'Dal/Millionminds/Useranswer.php';
        $dalAnswer = Dal_Millionminds_Useranswer::getDefaultInstance();
        $hasAnswered = $dalAnswer->hasAnswered($uid,$qid);
        
        if ($result > 0) {
            if ($hasAnswered == 1 && $edit != 1) {
                $this->_redirect($this->_baseUrl . 'millionminds/answer/qid/' . $qid);
            }
            else if ($hasAnswered == 1 && $edit == 1) {
                $result = $bllQuestion->delAnswer($uid,$qid);
            }
            
            //get question and asker`s basic info
            require_once 'Bll/User.php';
            Bll_User::appendPerson($result);
            $this->view->qst = $result;
            
            //get question answers
            require_once 'Dal/Millionminds/Question.php';
            $dalQuestion = Dal_Millionminds_Question::getDefaultInstance();
            $this->view->asw = $dalQuestion->getAswByQid($qid);
            
            //get users that has answered the question,friend first
            require_once 'Bll/Friend.php';
            $fids = Bll_Friend::getFriendIds($uid);
            $userList = $dalAnswer->getUidList($uid,$qid,$fids);
            
            if (count($userList) > 0) {       
                Bll_User::appendPeople($userList);
                $this->view->listInfo = $userList;
                $this->view->hasList = 1;
            }
            else {
                $this->view->hasList = 0;
            }
            //get popular question data
            $this->view->popularQuestion = $bllQuestion->getQuestion($this->_user->getId(), 5, 0, 1);
            $this->view->popularQuestionSub = $bllQuestion->getQuestion($this->_user->getId(), 10, 0, 1);
            
            $this->render();
        }
        else {
            $this->_redirect($this->_baseUrl . '/millionminds/archive/error/1');
        }
    }
    
    /**
     * help action
     *
     */
    public function helpAction()
    {
        //get popular question
        require_once 'Bll/Millionminds/Question.php';
        $bllQuestion = new Bll_Millionminds_Question();
        $this->view->popularQuestionSub = $bllQuestion->getQuestion($this->_user->getId(), 10, 0, 1);
        
        $this->render();
    }
    
    /**
     * predispatch
     *
     */
    function preDispatch()
    {
        $uid = $this->_user->getId();
        
        //admin user entrance
        $this->view->admin = ($uid == 23815104) ? 1 : 0;
        
        require_once 'Dal/Millionminds/Muser.php';
        $dalMuser = Dal_Millionminds_Muser::getDefaultInstance();

        $isIn = $dalMuser->isInMillionminds($uid);
        
        if (!$isIn) {
            //add user to millionminds
            require_once 'Bll/Millionminds/Nature.php';
            $bllNature = new Bll_Millionminds_Nature();
            $userInfo = array('uid'=>$uid,
                              'nature_result'=>0,
                              'create_time'=>time());
            $bllNature->insertMillionmindsUser($userInfo);
            
            $this->_redirect($this->_baseUrl . '/Millionminds/welcome');
        }
    }
    
    /**
     * get user not answer nature
     *
     */
    function _getNotAnswerNature()
    {
        require_once 'Dal/Millionminds/Muser.php';
        $dalMuser = Dal_Millionminds_Muser::getDefaultInstance();
        $Muser = $dalMuser->getUser($this->_user->getId());
        
        if ($Muser['nature_result'] == "0") {
            $this->view->showNature = true;
            
            require_once 'Dal/Millionminds/Question.php';
            $dalQuestion = Dal_Millionminds_Question::getDefaultInstance();
            $nature = $dalQuestion->getNotAnswerNature($this->_user->getId());
            
            require_once 'Bll/User.php';
            Bll_User::appendPerson($nature);
            $this->view->nature = $nature;
            
            require_once 'Bll/Friend.php';
            $fids = Bll_Friend::getFriendIds($this->_user->getId());
            
            $natureAnswer = $dalQuestion->getNatureAnswerUser($nature['qid'], $fids, 0);
            
            if ($natureAnswer) {
                Bll_User::appendPeople($natureAnswer);
            }
            
            $this->view->natureAnswer = $natureAnswer;
        }
        else {
            $this->view->showNature = false;
        }
    }
    
    /**
     * magic function
     * if call the function is undefined,then forward to not found
     *
     * @param string $methodName
     * @param array $args
     * @return void
     */
    function __call($methodName, $args)
    {
        return $this->_redirect('index','millionminds','default');
    }
}
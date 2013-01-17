<?php

/** @see MyLib_Zend_Controller_Action_Mobile.php */
require_once 'MyLib/Zend/Controller/Action/Mobile.php';

/**
 * Mobile Game Controller(modules/mobile/controllers/GameController.php)
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/09/08   xial
 */
class Mobile_QuizController extends MyLib_Zend_Controller_Action_Mobile
{
    protected $_pageSize = 5;
    protected $_quizCount = 5;
    protected $_answerSize = 0;
    /**
     * initialize object
     * override
     * @return void
     */
    public function init()
    {
        parent::init();
    }

    public function indexAction()
    {
    	return $this->_redirect($this->_baseUrl . '/mobile/quiz/top');
    }

     /**
     * deipatch
     *
     */
    function preDispatch()
    {
        $this->view->ua = Zend_Registry::get('ua');
    }

    /**
     * top action
     *
     * @return unknown
     */
    public function topAction()
    {
    	//Bll_Cache_Quiz::clearGetlistQuiz();
        $CF_uid = $this->_user->getId();

        //mdal quiz object
        require_once 'Mdal/Quiz/Quiz.php';
        $mdalQuiz = Mdal_Quiz_Quiz::getDefaultInstance();
        //mbll quiz object
        require_once 'Mbll/Quiz/Quiz.php';
        $mbllQuiz = new Mbll_Quiz_Quiz();

        //user info
        require_once 'Mdal/Quiz/User.php';
        $mdalUser = Mdal_Quiz_User::getDefaultInstance();

        $pos = (int)$this->getParam('CF_pos', 0);
        if ($pos == 1) {
        	//reset game
            $mdalQuiz->updateStatus($CF_uid);
        }
        $fidsStr = array();
        //friend info
        require_once 'Bll/Friend.php';
        $fidsStr = Bll_Friend::getFriends($CF_uid);
        //info_log('Count($fidsStr)' . count($fidsStr) , 'testQuiz');
        //info_log('implode(fidsStr)' . implode(',', $fidsStr), 'testQuiz');
        //not friend
        if ($fidsStr == null ) {
            return $this->_redirect($this->_baseUrl . '/mobile/quiz/errortwo');
        }
        //right answer
        $rightId = $this->getParam('CF_rightId');
        //choose's answer
        $answer = $this->getParam('CF_answer');
        //is pass
        $model = (int)$this->getParam('CF_model');
        //answered count
        $amswerCount = $mdalQuiz->getQuizCountById($CF_uid);
        $quizSize = $amswerCount + 1;
        $this->view->quizSize = $quizSize;
        $this->view->answeredQuizSize = $quizSize;
        if ($model || $rightId || $answer) {
        	//quiz id
        	$CF_qid = (int)$this->getParam('CF_qid');
        	//choose's friend
            $CF_logoId = $this->getParam('CF_logoId');

        	$dataInfo = array();
            // a quiz info
        	$dataInfo['ask_uid'] = $rightId;
        	$dataInfo['answer_uid'] = $CF_uid;
        	$dataInfo['quiz_id'] = $CF_qid;
        	$dataInfo['status'] = 1;
        	$dataInfo['create_time'] = time();

            //result 3:wrong 1:right 2:pass
        	$result = 0;
        	$title = '';
        	//pass
	        if ($model == 3) {
                $dataInfo['result'] = 2;
                $result = 2;
                $this->view->result = 2;
	        }
	        else {// right or wrong

	        	require_once 'Bll/User.php';
                //$person['uid']  = $rightId;
                $personAry = array('uid' => $rightId);
                Bll_User::appendPerson($personAry, 'uid', true);

                $oldRightAnswer = '';
	            if ($rightId && $answer) {
	            	require_once 'Mbll/Quiz/Activity.php';

	                if ($rightId == $answer) {
                        $dataInfo['result'] = 1;
                        $result = 1;
                        $this->view->result = 1;
                        //activity title
                        //$title = Mbll_Quiz_Activity::getActivity(1, '');
	                }
	                else {
                        $dataInfo['result'] = 0;
                        $result = 3;
                        $this->view->result = 3;

                        $aryQuiz = $mdalQuiz->getQuizById($CF_qid);
                        $quiz_name = $aryQuiz['quiz_name'];
                        //
                        $oldRightAnswer = $mbllQuiz->getRightAnswer($personAry, $aryQuiz['quiz']);

                        //activity title
                        //$title = Mbll_Quiz_Activity::getActivity(2, $quiz_name);

                        $this->view->nickname = $personAry['displayName'];
                        if ($CF_qid == 2 ) {
                        	$this->view->miniRightImg = $personAry['miniThumbnailUrl'];
                        }
                        $this->view->oldRightAnswer = $oldRightAnswer;
                        $this->view->oldQid = $CF_qid;
	                }
	            }
	        }

	        if ($CF_logoId >= $amswerCount ) {
                //insert answer info
                $mbllQuiz->insertQuiz($dataInfo);
                $mbllQuizUser = new Mbll_Quiz_User();
		        //check is joined
		        $isJoined = $mbllQuizUser->isJoined($rightId);
                if ($isJoined) {
                	//have new's message
                	$mdalUser->updateStatus($rightId, 1);
                }

                /*require_once 'Bll/Restful.php';
                //activity sussess url
                $url = $this->_baseUrl . "/mobile/quiz/transcript";
                $joinchar = (stripos($url, '?') === false) ? '?' : '&';
                $joinchar = 'http://ma.mixi.net/' . $this->_APP_ID . '/?url=' . urlencode($url . $joinchar . 'rand=' .rand());
                //get restful object
                $restful = Bll_Restful::getInstance($CF_uid, $this->_APP_ID);
                //$restful = Bll_Restful::getInstance(23815092, 9477);
                $activity = array(
                    'title' => $title,
                    'mobileUrl' => $joinchar,
                    'recipients' => array($rightId)
                );
                $restful->createActivity($activity);*/
            }

            //answered's total count
            $quizSize = $mdalQuiz->getQuizCountById($CF_uid);
            $this->view->quizSize = $quizSize;

            //next question id
            $this->view->answeredQuizSize = $quizSize + 1;
            //total count >= 5 ,current game end
            if ($quizSize >= $this->_quizCount) {
            	$url = '/mobile/quiz/answerresult?CF_qid= '. $CF_qid .'&CF_fid='. $rightId . '&CF_result='. $result . '&CF_rightAnswer=' . $oldRightAnswer;
            	return $this->_redirect($this->_baseUrl . $url);
            }
        }
        //question list
        require_once 'Bll/Cache/Quiz.php';
        $quizList = Bll_Cache_Quiz::getlistQuiz();

        $aryQuiz = array();
        $friendInfo = array();
        //question
        $quiz = '';
        //rand get question AND get friend info
        $aryUsed = array();

        foreach ($quizList as $key => $value) {
        	$listId[] = $value['id'];
        }

        while ($listId) {
            $randQuizId = array_rand($listId, 1);
            $isUsed = false;
            $aryQuiz = $quizList[$randQuizId];
            foreach ($aryUsed as $value) {
                if (($randQuizId + 1) == $value) {
                    $isUsed = true;
                    unset($listId[$randQuizId]);
                    break;
                }
            }
            if($isUsed){
                continue;
            }

            if ($aryQuiz != null) {
                $quiz = $aryQuiz['quiz'];
                //$fidsStr is friend id list
                $friendInfo = $mbllQuiz->getFriendInfo($fidsStr, $quiz);
                if ($friendInfo != null) {
                    break;
                }
                //not info
                $aryUsed[] = $randQuizId + 1;
                continue;
            }
        }

        if (!$listId || !$friendInfo) {
            return $this->_redirect($this->_baseUrl . '/mobile/quiz/errortwo');
        }

        /*foreach ($quizList as $key =>$value) {
            $randQuizId = rand(0, (count($listId) - 1));
            $isUsed = false;
            foreach ($aryUsed as $value) {
                if ($randQuizId == $value) {
                    $isUsed = true;
                    unset($listId[$randQuizId]);
                    break;
                }
            }
            if($isUsed){
                continue;
            }
            $aryQuiz = $quizList[$randQuizId];
            if ($aryQuiz != null) {
                $quiz = $aryQuiz['quiz'];
                //$fidsStr is friend id list
                $friendInfo = $mbllQuiz->getFriendInfo($fidsStr, $quiz);
                if ($friendInfo != null) {
                	//info_log('$friendInfo(break)' .count($friendInfo) . '===quiz:' . $quiz, 'testQuiz');
                    break;
                }
                //not info
                $aryUsed[] = $randQuizId;
                //info_log('$friendInfo(continue)' .count($friendInfo) . '===quiz:' . $quiz, 'testQuiz');
                continue;
            }
        }*/

        //question
        $quizName = $aryQuiz['quiz_name'];
        $this->view->qid = $aryQuiz['id'];

        $chooseAnswer = array();
        $choose = array('A', 'B', 'C', 'D', 'E');
        //$bloodType = array('A型', 'B型', 'AB型', 'O型', '不明');
        foreach ($friendInfo as $key => $value){
        	$chooseAnswer[$key]['uid'] = $value['uid'];
        	$chooseAnswer[$key]['no'] = $choose[$key];
        	$chooseAnswer[$key]['strChoose'] = $value[$quiz];

            if ($quiz == 'bloodType') {
                $chooseAnswer[$key]['strChoose'] = $value['bloodType'] . '型';
                continue;
            }

            if ($quiz == 'gender') {
               $chooseAnswer[$key]['strChoose'] = $value['gender'] == 'MALE' ? '男性' : '女性';
            }

	        if ($quiz == 'age') {
	            $chooseAnswer[$key]['strChoose'] = $value['age'] . '歳';
	        }

	        if ($quiz == 'dateOfBirth') {
	        	list($y, $m, $d) = explode('-', $value['dateOfBirth']);
	        	$chooseAnswer[$key]['strChoose'] = $y . '年' . $m . '月' . $d . '日';
	        }
        }
        //random get correct answer person
        $randFid = array_rand($chooseAnswer, 1);
        $chooseIndex = $chooseAnswer[$randFid];

        $friendPerson = array('uid' => $chooseIndex['uid']);
        Bll_User::appendPerson($friendPerson, 'uid', true);
        if ($quiz != 'miniThumbnailUrl' ){
             $miniImage = $friendPerson['miniThumbnailUrl'];
        }

        //view display's right answer
        $this->view->rightAnswer = $friendPerson['uid'];
        $this->view->miniImage = $miniImage;
        //check whether only 男性 or 女性 friend
        if (count($chooseAnswer) == 1 && $quiz == 'gender') {
        	$chooseAnswer[1]['no'] = 'B';
        	$chooseAnswer[1]['strChoose'] = $chooseAnswer[0]['strChoose'] == '男性' ? '女性' : '男性';
        	$chooseAnswer[1]['uid'] = 1;
        }
        //question tip
        $tips = $mbllQuiz->getTips($friendPerson, $quiz);

        $user = $mdalUser->getuser($CF_uid);

        $status = 0;
        if ($user['status'] == 1) {
            $askedCount = $mdalQuiz->getAllAskedCountById($CF_uid);
            if ($askedCount) {
            	$status = 1;
            }
        }
        //logo id
        $this->view->logoId = number_format($mdalQuiz->getQuizCountById($CF_uid));
        //friend id
        $this->view->fid = $friendPerson['uid'];
        $this->view->status = $status;
        $this->view->tipsInfo = $tips;
        $this->view->quizname = $quizName;
        $this->view->quiz = $quiz;
        $this->view->choose = $chooseAnswer;
        $this->render();
    }

    public function answerresultAction()
    {
        $CF_uid = $this->_user->getId();
        $rightAnswer = $this->getParam('CF_rightAnswer');
        //quiz id
        $qid = (int)$this->getParam('CF_qid');
        //answer's results
        $result = $this->getParam('CF_result');
        $CF_fid = $this->getParam('CF_fid');

        //mdal quiz object
        require_once 'Mdal/Quiz/Quiz.php';
        $mdalQuiz = Mdal_Quiz_Quiz::getDefaultInstance();

        //mbll quiz object
        require_once 'Mbll/Quiz/Quiz.php';
        $mbllQuiz = new Mbll_Quiz_Quiz();

        //get friend info
        require_once 'Bll/User.php';
        $personAry = array('uid' => $CF_fid);
        Bll_User::appendPerson($personAry, 'uid', true);

        //last question
        $aryQuiz = $mdalQuiz->getQuizById($qid);
        $quiz = $aryQuiz['quiz'];

        //result 3:wrong 1:right 2:pass
        if ($result == 3) {
            $this->view->nickname = $personAry['displayName'];
            $rightAnswer = $mbllQuiz->getRightAnswer($personAry, $quiz);
        }

        if ($qid == 2 ) {
            $this->view->miniRightImg = $personAry['miniThumbnailUrl'];
        }
        //current game's right result
        $right = number_format($mdalQuiz->getQuizResultById($CF_uid, 1, 1));
        $wrong = number_format($mdalQuiz->getQuizResultById($CF_uid, 0, 1));
        $pass = number_format($mdalQuiz->getQuizResultById($CF_uid, 2, 1));

        $this->view->wrong = $wrong;
        $this->view->right = $right;
        $this->view->pass = $pass;

        $rightRate = round( $right / $this->_quizCount, 3) * 100;
        $allRight = number_format($mdalQuiz->getQuizResultById($CF_uid, 1, 0)) + number_format($mdalQuiz->getQuizResultById($CF_uid, 1, 1));
        $countSize = $mdalQuiz->getAllQuizCountById($CF_uid);

        $allRate = round($allRight / $countSize, 3) * 100;
        //said 'ﾄﾓﾀﾞﾁ失くすよ!/'
        $this->getTitle($rightRate);
        $title = 'ﾏｲﾐｸ知ってる度0% もっと絡んで！';
        if ($allRate > 0 && $allRate < 20) {
        	$title = 'ﾏｲﾐｸ知ってる度'. $allRate . '% 恥ずかしい…';
        }elseif ($allRate >= 20 && $allRate < 40 ) {
        	$title = 'ﾏｲﾐｸ知ってる度'. $allRate . '% 大丈夫？';
        }elseif ($allRate >= 40 && $allRate < 60 ) {
            $title = 'ﾏｲﾐｸ知ってる度'. $allRate . '% ｷﾞﾘｷﾞﾘ合格点';
        }elseif ($allRate >= 60 && $allRate < 80 ) {
            $title = 'ﾏｲﾐｸ知ってる度'. $allRate . '% 上出来！';
        }elseif ($allRate >= 80 && $allRate < 100 ) {
            $title = 'ﾏｲﾐｸ知ってる度'. $allRate . '% すばらしい！';
        } elseif ($allRate == 100) {
        	$title = 'ﾏｲﾐｸ知ってる度'. $allRate . '% やりました！';
        }
        require_once 'Bll/Restful.php';

        //activity sussess url
        $url = Zend_Registry::get('host') . "/mobile/quiz/transcript";
        //$joinchar = (stripos($url, '?') === false) ? '?' : '&';
        $joinchar = 'http://ma.mixi.net/'. $this->_APP_ID .'/?guid=ON&url=' . urlencode($url);
        //get restful object
        $restful = Bll_Restful::getInstance($CF_uid, $this->_APP_ID);
        //$restful = Bll_Restful::getInstance(23815092, 9477);
        $activity = array(
             'title' => $title,
             'mobileUrl' => $joinchar
        );

        $restful->createActivity($activity);
        if ($restful->hasError()) {
        	info_log($restful->getErrorMessage, 'quiz_activity_error');
        }

    	//add diary
        $this->newDiary();

        $this->view->rightRate = $rightRate;
        $this->view->quizSize = $this->_quizCount;
        $this->view->oldRightAnswer = $rightAnswer;
        $this->view->result = $result;
        $this->view->quiz = $quiz;
        $this->render();
    }

    public function transcriptAction()
    {
    	$CF_uid = $this->_user->getId();
        $status = (int)$this->getParam('CF_status');
        if ($status) {
        	require_once 'Mdal/Quiz/User.php';
            $mdalUser = Mdal_Quiz_User::getDefaultInstance();
            $mdalUser->updateStatus($CF_uid, 0);
        }

    	require_once 'Mdal/Quiz/Quiz.php';
        $mdalQuiz = Mdal_Quiz_Quiz::getDefaultInstance();
        //answered quiz id
        $answeredQuizidList = $mdalQuiz->getAnsweredQuizlistById($CF_uid, true);
        foreach ($answeredQuizidList as $key => $value) {
        	$countSize = $mdalQuiz->getAnsweredQuizCountByQid($value['quiz_id'], $CF_uid);
            $rightCount = $mdalQuiz->getQuizAnswerResultByQid($value['quiz_id'], 1, $CF_uid);
            $quizInfo = $mdalQuiz->getQuizById($value['quiz_id']);
            $answeredQuizidList[$key]['rightRate'] = round( $rightCount / $countSize, 3) * 100;
            $answeredQuizidList[$key]['quizName'] = $quizInfo['quiz_name'];
        }

        //current game's right result
        $right = number_format($mdalQuiz->getQuizResultById($CF_uid, 1, 0)) + number_format($mdalQuiz->getQuizResultById($CF_uid, 1, 1));
        $wrong = number_format($mdalQuiz->getQuizResultById($CF_uid, 0, 0)) + number_format($mdalQuiz->getQuizResultById($CF_uid, 0, 1));
        $pass = number_format($mdalQuiz->getQuizResultById($CF_uid, 2, 0)) + number_format($mdalQuiz->getQuizResultById($CF_uid, 2, 1));

        $this->view->wrong = $wrong;
        $this->view->right = $right;
        $this->view->pass = $pass;

        $allQuizCount = number_format($mdalQuiz->getAllQuizCountById($CF_uid));
        $rightRate = 0;
        if ($allQuizCount) {
        	$rightRate = round( $right / $allQuizCount, 3) * 100;
        }

        //said 'ﾄﾓﾀﾞﾁ失くすよ!/'
        $this->getTitle($rightRate);

        $newQuizList = $mdalQuiz->getNewAnsweredQuizById($CF_uid);
        $wrongQuizlist = $mdalQuiz->getRegardMyQuizById($CF_uid, 0);
        $rightQuizlist = $mdalQuiz->getRegardMyQuizById($CF_uid, 1);

        require_once 'Bll/User.php';
        Bll_User::appendPeople($newQuizList, 'ask_uid');
        Bll_User::appendPeople($wrongQuizlist, 'answer_uid');
        Bll_User::appendPeople($rightQuizlist, 'answer_uid');

        //add diary
        $this->newDiary();

        $this->view->nickName = $this->_user->getDisplayName();
        $this->view->newQuizList = $newQuizList;
        $this->view->wrongQuizlist = $wrongQuizlist;
        $this->view->rightQuizlist = $rightQuizlist;
        $this->view->answeredQuizidList = $answeredQuizidList;
        $this->view->rightRate = $rightRate;

        $this->render();
    }

    public function myquizlistAction()
    {
    	$CF_uid = $this->_user->getId();
        $pageIndex = (int)$this->getParam('CF_page', 1);
        $pageSize = 10;

        require_once 'Mdal/Quiz/Quiz.php';
        $mdalQuiz = Mdal_Quiz_Quiz::getDefaultInstance();

        $myQuizlist = $mdalQuiz->getNewAnsweredQuizById($CF_uid, $pageIndex, $pageSize);
        $count = $mdalQuiz->getAllQuizCountById($CF_uid);

        require_once 'Bll/User.php';
        Bll_User::appendPeople($myQuizlist, 'ask_uid');

        if ($count && !empty($myQuizlist)) {
                $startCount = ($pageIndex -1 ) * $pageSize + 1;
                if (count($myQuizlist) == '10') {
                    $endCount = $pageIndex * $pageSize;
                }
                else {
                    $endCount = $startCount + count($myQuizlist) - 1;
                }
                $listCount = array('startCount' => $startCount, 'endCount' => $endCount );
         }

        $this->view->pager = array('count' => $count,
                                   'pageIndex' => $pageIndex,
                                   'requestUrl' => 'mobile/quiz/myquizlist',
                                   'pageSize' => $pageSize,
                                   'maxPager' => ceil($count / $pageSize)
                                   );

        $this->view->uid = $CF_uid;
        $this->view->myQuizlist = $myQuizlist;
        $this->view->listCount = $listCount;
        $this->view->count = $count;
        $this->render();
    }

    public function resultlistAction()
    {
        $CF_uid = $this->_user->getId();
        $pageIndex = (int)$this->getParam('CF_page', 1);
        //$result : 0,wrong 1,right
        $result = (int)$this->getParam('CF_result', 0);
        $pageSize = 10;

        require_once 'Mdal/Quiz/Quiz.php';
        $mdalQuiz = Mdal_Quiz_Quiz::getDefaultInstance();

        $quizlist = $mdalQuiz->getRegardMyQuizById($CF_uid, $result , $pageIndex, $pageSize);
        $count = $mdalQuiz->getQuizResultCountById($CF_uid, $result);

        require_once 'Bll/User.php';
        Bll_User::appendPeople($quizlist, 'answer_uid');

        if ($count && !empty($quizlist)) {
                $startCount = ($pageIndex -1 ) * $pageSize + 1;
                if (count($quizlist) == '10') {
                    $endCount = $pageIndex * $pageSize;
                }
                else {
                    $endCount = $startCount + count($quizlist) - 1;
                }
                $listCount = array('startCount' => $startCount, 'endCount' => $endCount );
         }

        $this->view->pager = array('count' => $count,
                                   'pageIndex' => $pageIndex,
                                   'requestUrl' => 'mobile/quiz/resultlist',
                                   'pageSize' => $pageSize,
                                   'maxPager' => ceil($count / $pageSize),
                                   'pageParam' => '&CF_result=' . $result
                                   );

        $this->view->uid = $CF_uid;
        $this->view->result = $result;
        $this->view->quizlist = $quizlist;
        $this->view->listCount = $listCount;
        $this->view->count = $count;
        $this->render();
    }

    public function resulttypelistAction()
    {
        $CF_uid = $this->_user->getId();
        //$result : 0,wrong 1,right
        $result = (int)$this->getParam('CF_result', 0);

        $pageIndex = 1;
        $pageSize = 3;
        require_once 'Mdal/Quiz/Quiz.php';
        $mdalQuiz = Mdal_Quiz_Quiz::getDefaultInstance();

        $resultList = $mdalQuiz->getAnsweredQuizlistById($CF_uid, false);
        foreach ($resultList as $key=>$value) {
        	$quizlist = $mdalQuiz->getQuizResultTypeById($CF_uid, $result, $value['quiz_id'], $pageIndex , $pageSize, 1);
        	foreach ($quizlist as $k => $v) {
				$rightCount = number_format($mdalQuiz->getQuizResultById($v['answer_uid'], 1, 0) + $mdalQuiz->getQuizResultById($v['answer_uid'], 1, 1));
        		$allQuizCount = number_format($mdalQuiz->getAllQuizCountById($v['answer_uid']));
        		if ($allQuizCount) {
        			$quizlist[$k]['right_rate'] = round( $rightCount / $allQuizCount, 3) * 100;
        		}
        	}
        	if ($quizlist) {
        		require_once 'Bll/User.php';
	            Bll_User::appendPeople($quizlist, 'answer_uid');
	        	$resultList[$key]['quizlist'] = $quizlist;
        	}
        }

        $this->view->result = $result;
        $this->view->resultList = $resultList;
        $this->render();
    }

    public function resulttypeAction()
    {
        $CF_uid = $this->_user->getId();
        //$result : 0,wrong 1,right
        $result = (int)$this->getParam('CF_result', 0);
        $pageIndex = (int)$this->getParam('CF_page', 1);
        $quizId = (int)$this->getParam('CF_quizId', 0);
        $pageSize = 10;

        require_once 'Mdal/Quiz/Quiz.php';
        $mdalQuiz = Mdal_Quiz_Quiz::getDefaultInstance();

        $quizInfo = $mdalQuiz->getQuizById($quizId);

        $count = $mdalQuiz->getAskedQuizCountByQid($CF_uid, $result, $quizId);
        $quizlist = $mdalQuiz->getQuizResultTypeById($CF_uid, $result, $quizId, $pageIndex, $pageSize, 0);
        foreach ($quizlist as $k => $v) {
        	$rightCount = number_format($mdalQuiz->getQuizResultById($v['answer_uid'], 1, 0) + $mdalQuiz->getQuizResultById($v['answer_uid'], 1, 1));
        	$allQuizCount = number_format($mdalQuiz->getAllQuizCountById($v['answer_uid']));
        	if ($allQuizCount) {
        		$quizlist[$k]['right_rate'] = round( $rightCount / $allQuizCount, 3) * 100;
        	}
        }

        require_once 'Bll/User.php';
        Bll_User::appendPeople($quizlist, 'answer_uid');

        if ($count && !empty($quizlist)) {
                $startCount = ($pageIndex -1 ) * $pageSize + 1;
                if (count($quizlist) == '10') {
                    $endCount = $pageIndex * $pageSize;
                }
                else {
                    $endCount = $startCount + count($quizlist) - 1;
                }
                $listCount = array('startCount' => $startCount, 'endCount' => $endCount );
         }

        $this->view->pager = array('count' => $count,
                                   'pageIndex' => $pageIndex,
                                   'requestUrl' => 'mobile/quiz/resulttype',
                                   'pageSize' => $pageSize,
                                   'maxPager' => ceil($count / $pageSize),
                                   'pageParam' => '&CF_result=' . $result . '&CF_quizId=' . $quizId
                                   );

        $this->view->uid = $CF_uid;
        $this->view->quiz_name = $quizInfo['quiz_name'];
        $this->view->quizlist = $quizlist;
        $this->view->listCount = $listCount;
        $this->view->result = $result;
        $this->view->count = $count;
        $this->render();
    }

    /**
     * profile action
     *
     */
    public function profileAction()
    {
        $fid = $this->getParam('CF_fid');
        $uid = $this->_user->getId();

        require_once 'Mbll/Quiz/User.php';
        $mUserDal = Mdal_Quiz_User::getDefaultInstance();

        $fInfo = $mUserDal->getUser($fid);
        //get friend info
        require_once 'Bll/User.php';
        $friendInfo  = Bll_User::getPerson($fid);
        if ( !$friendInfo ) {
            $this->_redirect($this->_baseUrl . '/mobile/quiz/top?CF_pos=1');
            return;
        }
        $this->view->friendInfo = $friendInfo;

        require_once 'Mbll/Quiz/Quiz.php';
        $mbllQuiz = new Mbll_Quiz_Quiz();
        //get friend answer info
        $friendAnswer = $mbllQuiz->getQuizInfoUserToFriend($uid, $fid, 2);

        //get my answer info
        $myAnswer = $mbllQuiz->getQuizInfoUserToFriend($fid, $uid, 3);
        //
        $boardUrl = Zend_Registry::get('host') . '/mobile/board?CF_uid=' . $fid;

        $this->view->friendAnswer = $friendAnswer;
        $this->view->myAnswer = $myAnswer;
        $this->view->fInfo = $fInfo;
        $this->view->url = 'http://ma.mixi.net/4011/?guid=ON&url='. urlencode($boardUrl);
        $this->render();
    }

    private function getTitle($rightRate)
    {
        $tips = 'ﾄﾓﾀﾞﾁ失くすよ!';

        if ($rightRate > 0 && $rightRate < 21) {
            $tips = 'やる気ないでしょ?';
        }elseif ($rightRate > 20 && $rightRate < 41 ) {
            $tips = 'ﾄﾓﾀﾞﾁは大切にﾈ!';
        }elseif ($rightRate > 40 && $rightRate < 61 ) {
            $tips = 'まぁﾄﾓﾀﾞﾁだから当然だﾈ';
        }elseif ($rightRate > 60 && $rightRate < 81 ) {
            $tips = '結構ﾏﾒなんじゃない?';
        }elseif ($rightRate > 80 ) {
            $tips = '知り過ぎてて怖いよ!';
        }
        $this->view->tips = $tips;
    }

    private function newDiary()
    {
    	$CF_uid = $this->_user->getId();
    	require_once 'Mdal/Quiz/Quiz.php';
        $mdalQuiz = Mdal_Quiz_Quiz::getDefaultInstance();
    	//current game's right result
        $right = number_format($mdalQuiz->getQuizResultById($CF_uid, 1, 0)) + number_format($mdalQuiz->getQuizResultById($CF_uid, 1, 1));
        $wrong = number_format($mdalQuiz->getQuizResultById($CF_uid, 0, 0)) + number_format($mdalQuiz->getQuizResultById($CF_uid, 0, 1));
        $pass = number_format($mdalQuiz->getQuizResultById($CF_uid, 2, 0)) + number_format($mdalQuiz->getQuizResultById($CF_uid, 2, 1));

        $allQuizCount = number_format($mdalQuiz->getAllQuizCountById($CF_uid));
        $rightRate = 0;
        if ($allQuizCount) {
        	$rightRate = round( $right / $allQuizCount, 3) * 100;
        }

        $body = "「マイミククイズ」をやってみたよ！\n\n■今までのクイズ成績\n正解数:". $right
		        ."問\n不正解:". $wrong ."問\nパス　:".$pass."問\n\nマイミク知ってる度は". $rightRate
		        ."%でした。\n↓みんなもやってみて！\n\nマイミククイズ\nhttp://mixi.jp/view_appli.pl?id=" .$this->_APP_ID;
		$title = 'マイミク知ってる度は「'.$rightRate.'%」';

		$ua = Zend_Registry::get('ua');
		if ( $ua == 3 ){
			$diary_title = urlencode(mb_convert_encoding('マイミク知ってる度は「'.$rightRate.'%」', 'SJIS','UTF-8'));
			$diary_body  = urlencode(mb_convert_encoding($body, 'SJIS','UTF-8'));
			$this->view->diaryUrl = "http://m.mixi.jp/add_diary.pl?diary_title=" . $diary_title . "&diary_body=" . $diary_body . "&guid=ON";
    	}else {
			$this->view->diary_title = $title;
			$this->view->diary_body = $body;
		}
    }

    public function helpAction()
    {
        $this->render();
    }

    public function errortwoAction()
    {
        $this->render();
    }
}
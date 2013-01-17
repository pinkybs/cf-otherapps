<?php

require_once 'Mbll/Abstract.php';

/**
 * quiz
 *
 * @package    Mbll
 * @copyright  Copyright (c) 2009 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/09/17    xial
 */
class Mbll_Quiz_Quiz extends Mbll_Abstract
{
    public function insertQuiz($info)
    {
        $result = false;
        try {
            $this->_wdb->beginTransaction();

            require_once 'Mdal/Quiz/Quiz.php';
            $dalGame = Mdal_Quiz_Quiz::getDefaultInstance();

            $askUid = $info['ask_uid'];
            $answerUid = $info['answer_uid'];
            $quizId = $info['quiz_id'];

            if (empty($askUid) || empty($answerUid) || empty($quizId) ) {
                $this->_wdb->rollBack();
                return $result;
            }

            $quizCount = $dalGame->getQuizCountById($answerUid);
            if ($quizCount > 5) {
            	$this->_wdb->rollBack();
                return $result;
            }

            //check insert info whether exists
            /*$isExists = $dalGame->isExistsQuizById($askUid ,$answerUid, $quizId);
            if ($isExists) {
            	$this->_wdb->rollBack();
                return $result;
            }*/

            $dalGame->insert($info);

            $this->_wdb->commit();
            $result = true;
        }
        catch (Exception $e){
            $this->_wdb->rollBack();
            return $result;
        }

        $result = true;
    }


    /**
     * get quiz info user to friend
     *
     * @param integer $uid
     * @param integer $fid
     * @param integer $contentType
     * @return array
     */
    public function getQuizInfoUserToFriend($askId, $answerId, $contentType)
    {
        require_once 'Mdal/Quiz/Quiz.php';
        $mdalQuiz = Mdal_Quiz_Quiz::getDefaultInstance();
        //get friend answer list
        $answerList = $mdalQuiz->getQuizResultList($askId, $answerId);

        //get right count and rate
        $rightCount = $mdalQuiz->getRightCountOneToOne($askId, $answerId);

        $rightRate = $rightCount > 0 ? round( $rightCount / count($answerList), 1) * 100 : 0;

        //get rate level and content
        $rigthLevel = $this->getRateLevel($rightRate);

        require_once 'Bll/Cache/Quiz.php';
        $content = Bll_Cache_Quiz::getQuizContent($rigthLevel, $contentType);

        return array('answerList'=>$answerList, 'quizContent'=>$content);
    }

    /**
     * get rate level
     *
     * @param integer $rate
     * @param integer $type
     * @return integer
     */
    public function getRateLevel($rate)
    {
        switch ($rate) {
            case 0 < $rate && $rate < 21:
                $level = 2;
                break;
            case 20 < $rate && $rate < 41:
                $level = 3;
                break;
            case 40 < $rate && $rate < 61:
                $level = 4;
                break;
            case 60 < $rate && $rate < 81 :
                $level = 5;
                break;
            case 80 < $rate && $rate < 101:
                $level = 6;
                break;
            default:
                $level = 1;
        }

        return $level;
    }

    /**
     * get answer key choose
     *
     * @param array $friendInfo
     * @return array
     */
   /* public function getAnswerKeyChoose($friendInfo)
    {
    	$answerKeys = array();
    	switch (count($friendInfo)) {
            case 1:
                $answerKeys = array('A');
                break;

            case 2:
                $answerKeys = array('A', 'B');
                break;

            case 3:
                $answerKeys = array('A', 'B', 'C');
                break;

            case 4:
                $answerKeys = array('A', 'B', 'C', 'D');
                break;

            default:
                $answerKeys = array('A', 'B', 'C', 'D', 'E');
                break;
        }
        return $answerKeys;
    }*/

    /**
     * get tips information
     *
     * @param object array $friendPerson
     * @param string $quiz
     * @return string
     */
    public function getTips($friendPerson, $quiz)
    {
        $tips = '';
        if ($friendPerson == null) {
        	return $tips;
        }

    	if ( $quiz != 'displayName' &&  $friendPerson['displayName'] != null ) {
            $tips .= $friendPerson['displayName'];
        }

        if ( $quiz != 'address' && $friendPerson['address'] != null ) {
            $tips .= '・' . $friendPerson['address'] . '在住';
        }

        if ( $quiz != 'dateOfBirth' && $quiz != 'age' && $friendPerson['dateOfBirth'] != null ) {
           list($y, $m, $d) = explode('-', $friendPerson['dateOfBirth']);
           $tips .= '・' . $y . '年' . $m . '月' . $d . '日' . '生まれ';
        }

        if ( $quiz != 'dateOfBirth' && $quiz != 'age' && $friendPerson['age'] != null ) {
           $tips .= '・' . $friendPerson['age'] . '歳';
        }

        if ( $quiz != 'bloodType' && $friendPerson['bloodType'] != null ) {
            $tips .= '・' . $friendPerson['bloodType'] . '型';
        }

        if ( $quiz != 'gender' && $friendPerson['gender'] != null ) {
            if ($friendPerson['gender'] == 'MALE') {
                $tips .= '・男性';
            }
            else{
                $tips .= '・女性';
            }
        }
        return $tips;
    }

    /**
     * get rightAnswer
     *
     * @param object array $friendPerson
     * @param string $quiz
     * @return string
     */
    public function getRightAnswer($friendPerson, $quiz)
    {
        //right answer
        $rightAnswer = '「' . $friendPerson[$quiz] . '」';

        if ($quiz == 'gender') {
           $rightAnswer = $friendPerson['gender'] == 'MALE' ? '男性' : '女性';
           $rightAnswer = '「'. $rightAnswer .'」';
        }

        if ($quiz == 'age') {
            $rightAnswer = '「' . $friendPerson['age'] . '歳」';
        }

        if ($quiz == 'bloodType') {
           $rightAnswer = '「' . $friendPerson['bloodType'] . '型」';
        }

        if ($quiz == 'miniThumbnailUrl') {
        	$rightAnswer = '<img src="' . $friendPerson['miniThumbnailUrl'] . '" />';
        }

        if ( $quiz == 'dateOfBirth' ) {
           list($y, $m, $d) = explode('-', $friendPerson['dateOfBirth']);
           $rightAnswer = '「' . $y . '年' . $m . '月' . $d . '日」';
        }

        return $rightAnswer;
    }

    /**
     * get friend
     *
     * @param array $fidsStr
     * @param string $quiz
     * @return array
     */
    public function getFriendInfo($fidsStr, $quiz)
    {
        $friendInfo = array();
        $friendArray = array();
        $randCount = count($fidsStr) >= 5 ? 5 : count($fidsStr);

        // rand get 5 friend
        for ( $i = 0, $iCount = count($fidsStr); $i < $iCount; $i++ ) {
            $arraylist = array_diff($fidsStr, $friendArray);
            $index = array_rand($arraylist, 1);
            require_once 'Bll/User.php';
            $info['uid'] = $arraylist[$index];
            Bll_User::appendPerson($info, 'uid', true);

            //check answer null and repeated value
            if ($info != null && $info[$quiz] != null) {
                $canUse = '1';
                for ( $j = 0, $jCount = count($friendInfo); $j < $jCount; $j++ ) {
                	//repeated value
                    if ( $info[$quiz] == $friendInfo[$j][$quiz] ) {
                        $canUse = '0';
                        break;
                    }
                }
                if ( $canUse == '1' ) {
                    $friendInfo[] = $info;
                }
            }

            $friendArray[] = $arraylist[$index];

            if ($quiz == 'gender') {
               if ( count($friendInfo) == 2 ) {
                    break;
               }
            }

            if ( count($friendInfo) == $randCount ) {
                break;
            }
        }
        return $friendInfo;
    }
}
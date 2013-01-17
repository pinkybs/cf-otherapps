<?php
/** @see Bll_Abstract.php */
require_once 'Bll/Abstract.php';

/** @see Dal_Millionminds_Question.php */
require_once 'Dal/Millionminds/Question.php';

/**
 * millionminds question logic's Operation
 *
 * @package    Bll
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/07/27    Huch
 */
class Bll_Millionminds_Question extends Bll_Abstract
{
    /**
     * get question
     *
     * @param integer $uid
     * @param integer $num
     * @param integer $type  0:all  1,2,3,4,5
     * @param integer $field  1:answer 2:create_time
     * @param integer $order  1:desc 2:asc
     * @return array
     */
    public function getQuestion($uid, $num = 5, $type = 0, $field = 1, $order = 1, $pageIndex = 1)
    {
        $dalQuestion = Dal_Millionminds_Question::getDefaultInstance();                
        $question = $dalQuestion->getQuestion($type, $field, $order, $pageIndex, $num * 5);
        return $this->_checkQuestion($question, $uid, $num);
    }

    /**
     * get user question
     *
     * @param integer $uid
     * @param integer $num
     * @param integer $pageIndex
     * @param integer $visitor
     * @return array
     */
    public function getUserQuestion($uid, $num = 5, $pageIndex = 1, $visitor)
    {        
        $dalQuestion = Dal_Millionminds_Question::getDefaultInstance();
        
        $question = $dalQuestion->getUserQuestion($uid , $pageIndex, $num * 5);
        return $this->_checkQuestion($question, $visitor, $num, 'uid',1);
    }
    
    /**
     * get archive
     *
     * @param integer $uid
     * @param integer $type  0:all  1,2,3,4,5
     * @param integer $field  1:answer 2:create_time(id)
     * @param integer $order  1:desc 2:asc
     * @param integer $pageIndex
     * @param integer $pageSize
     * @return array
     */
    public function getArchive($uid, $type = 0, $field = 1, $order = 1, $pageIndex = 1, $pageSize = 30)
    {
        $dalQuestion = Dal_Millionminds_Question::getDefaultInstance();
        $question = $dalQuestion->getArchiveData($uid, $type, $field, $order, $pageIndex, $pageSize);
        return $this->_checkQuestion($question, $uid, $pageSize);
    }
    
    /**
     * insert a question
     *
     * @param string $cat
     * @param string $nickname
     * @param string $region
     * @param string $uid
     * @param string $title
     * @param array $answerArray
     * @return -1 or 1
     */
    public function insertQuestion($cat,$nickname,$region,$uid,$title,$answerArray)
    {
        $result = -1;
        switch ($cat) {
            case 'politics' :
                $type = 2;
                break;
            case 'life' :
                $type = 3;
                break;
            case 'entertainment' :
                $type = 4;
                break;    
            case 'hobby' :
                $type = 5;
                break;
            default :
                return $result;
        }
        
        switch ($nickname) {
            case 'open' :
                $nickname_auth = 1;
                break;
            case 'close' :
                $nickname_auth = 0;
                break;
            default :
                return $result;
        }
        
        switch ($region) {
            case 'all' :
                $public_type = 1;
                break;
            case 'friend' :
                $public_type = 2;
                break;
            case 'fof' :
                $public_type = 3;
                break;
            default :
                return $result;
        }
        
        $create_time = time();
        $array = array('uid'=>$uid,
                	   'question'=>$title,
                	   'type'=>$type,
                	   'nickname_auth'=>$nickname_auth,
                	   'public_type'=>$public_type,
                	   'create_time'=>$create_time);   
        $answerCnt = count($answerArray);
                	   
        try {
            $dalQuestion = Dal_Millionminds_Question::getDefaultInstance();
            
            $this->_wdb->beginTransaction();
            
            //insert unaudited question
            $lastId = $dalQuestion->insertQuestion($array);
            //insert unaudited question answer
            for ($i = 1; $i <= $answerCnt; $i++) {
                $answerArrayEdit = array('qid'=>$lastId,
                                    	 'answer'=>$answerArray[$i-1],
                                    	 'aid'=>$i);
                $dalQuestion->insertAnswer($answerArrayEdit);
            }
            $this->_wdb->commit();
            $result = 1;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return $result;
        }
        return $result;
    }
    
    /**
     * approve a question
     *
     * @param string $type
     * @param string $nickname_auth
     * @param string $public_type
     * @param string $uid
     * @param string $question
     * @param string $id
     * @param array $answerArray
     * @return -1 or 1
     */
    public function approveQuestion($type,$nickname_auth,$public_type,$question,$answerArray,$uid,$id)
    {
        $result = -1;
        switch ($type) {
            case 2 :
                $category = '政治・経済';
                break;
            case 3 :
                $category = '社会生活';
                break;
            case 4 :
                $category = '芸能・スポーツ';
                break;    
            case 5 :
                $category = '趣味・その他';
                break;
            default :
                return $result;  
        }
        
        $create_time = time();
        $array = array('uid'=>$uid,
                	   'question'=>$question,
                	   'type'=>$type,
                	   'nickname_auth'=>$nickname_auth,
                	   'public_type'=>$public_type,
                	   'category'=>$category,
                	   'create_time'=>$create_time);   
        $answerCnt = count($answerArray);
                	   
        try {
            $dalQuestion = Dal_Millionminds_Question::getDefaultInstance();
            
            $this->_wdb->beginTransaction();
            
            //lock a row 
            $selectLock = $dalQuestion->isUnQstIdLock($id);
            if ($selectLock == 1) {
                //insert approved question
                $lastId = $dalQuestion->approveQuestion($array);
                
                //insert approved question answer
                for ($i = 1; $i <= $answerCnt; $i++) {
                    $answerArrayEdit = array('qid'=>$lastId,
                                        	 'answer'=>$answerArray[$i-1],
                                        	 'aid'=>$i);
                    $dalQuestion->approveAnswer($answerArrayEdit);
                }
                
                //delete unapproved question
                $aaa = $dalQuestion->delUnQstById($id);
                //delete unapproved question answer
                $bbb = $dalQuestion->delUnAswByQid($id);
                $this->_wdb->commit();
                $result = 1;
            }
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return $result;
        }
        return $result;
    }
    
    /**
     * deny question
     *
     * @param string $id
     * @return 1 or -1
     */
    public function denyQuestion($id)
    {
        $result = -1;
        try {
            $dalQuestion = Dal_Millionminds_Question::getDefaultInstance();
            
            $this->_wdb->beginTransaction();
            
            $dalQuestion->denyQuestion($id);
            
            $this->_wdb->commit();
            $result = 1;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return $result;
        }
        return $result;
    }
    
    /**
     * check nickname_auth public_type and existence
     *
     * @param string  $uid
     * @param string $qid
     * @return -1 -2 -3 or array
     */
    public function questionCheck($uid,$qid)
    {
        $result = -1;
        require_once 'Bll/Friend.php';
        $dalQuestion = Dal_Millionminds_Question::getDefaultInstance();
        
        //check $qid existence
        $qidCheck = $dalQuestion->isQstId($qid);
        if (!$qidCheck) {
            return -2;
        }
        
        //get question info
        $qInfo = $dalQuestion->getOneQst($qid);
        //if this question can be read
        if ($qInfo['uid'] == $uid || $qInfo['public_type'] == 1 ||
           ($qInfo['public_type'] == 2 && Bll_Friend::isFriend($qInfo['uid'], $uid)) || 
           ($qInfo['public_type'] == 3 && (Bll_Friend::isFriendFriend($qInfo['uid'], $uid) || Bll_Friend::isFriend($qInfo['uid'], $uid)))) {
            $result = $qInfo;
        }
        else {
            return -3;
        }
        
        return $result;
    }
    
    
    /**
     * delete answer
     *
     * @param string $uid
     * @param string $qid
     * @return -1 or 1
     */
    public function delAnswer($uid,$qid)
    {
        $result = -1;
        try {
            require_once 'Dal/Millionminds/Useranswer.php';
            $dalUseranswer = Dal_Millionminds_Useranswer::getDefaultInstance();
            $dalQuestion = Dal_Millionminds_Question::getDefaultInstance();
            
            $this->_wdb->beginTransaction();
            
            //delete answer in millionmind_user_answer
            $dalUseranswer->delAnswer($uid,$qid);
            
            //update millionmind_question.answer
            $dalQuestion->updateQuestionAnswerCount($qid,-1);
            //$dalQuestion->updateAnswer($qid);
            
            //renew question info
            $qInfo = $dalQuestion->getOneQst($qid);
            
            //renew all millionmind_question.answer
            //$dalQuestion->updateAnswerAll();
            
            $this->_wdb->commit();
            $result = $qInfo;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return $result;
        }
        return $result;
    }
    
    /**
     * get user last answer(include answer this question's user)
     *
     * @param integer $uid
     * @return array
     */
    public function getUserRandomAnswer($uid)
    {
        require_once 'Bll/User.php'; 
        
        $dalQuestion = Dal_Millionminds_Question::getDefaultInstance();        
        $question = $dalQuestion->getUserRandomAnswer($uid); 
        Bll_User::appendPerson($question);
               
        $answer = $dalQuestion->getQuestionAnswerCount($question['qid']);
                
        //get this question answer's user
        $answerUser = array();
        $answerLength = count($answer);
        $percent = 0;
         
        for ($i = 0; $i < $answerLength; $i++) {
            if ($answer[$i]['answer_sum'] != 0) {
                $temp = $dalQuestion->getQuestionAnswerUser($answer[$i]['qid'], $answer[$i]['aid']);
                Bll_User::appendPeople($temp);
                $answerUser[$answer[$i]['aid']] = $temp;
            }
            else {
                $answerUser[$answer[$i]['aid']] = array();
            }
            
            //if ($i != $answerLength - 1) {
                $answer[$i]['per'] = round($answer[$i]['answer_sum'] / $question['answer'], 2) * 100;
                $percent += $answer[$i]['per'];
            //}
            //else {
                //$answer[$i]['per'] = 100 - $percent;
            //}
            
            $answer[$i]['len'] = round($answer[$i]['answer_sum'] / $question['answer'], 2) * 200;
            $answer[$i]['user'] = $answerUser;
        }
        
        $answerOrder = $this->_arrayMultisort($answer, "answer_sum", SORT_DESC);
        
        return array('question'=>$question, 'answer'=>$answer, 'answerOrder'=> $answerOrder);
    }
    
    /**
     * get question answer info
     *
     * @param integer $qid
     * @param integer $question
     * @return array
     */
    public function getQuestionAnswer($qid, $question)
    {
        $dalQuestion = Dal_Millionminds_Question::getDefaultInstance();
        //get question answer info
        $answer = $dalQuestion->getQuestionAnswerCount($qid);
        
        //get this question answer's user
        $answerUser = array();
        $answerLength = count($answer);
        $percent = 0;
         
        for ($i = 0; $i < $answerLength; $i++) {
            if ($answer[$i]['answer_sum'] != 0) {
                $temp = $dalQuestion->getQuestionAnswerUser($answer[$i]['qid'], $answer[$i]['aid']);
                Bll_User::appendPeople($temp);
                $answerUser[$answer[$i]['aid']] = $temp;
            }
            else {
                $answerUser[$answer[$i]['aid']] = array();
            }
            
            //if ($i != $answerLength - 1) {
                $answer[$i]['per'] = round($answer[$i]['answer_sum'] / $question['answer'], 2) * 100;
                $percent += $answer[$i]['per'];
            //}
            //else {
                //$answer[$i]['per'] = 100 - $percent;
            //}
            
            $answer[$i]['len'] = round($answer[$i]['answer_sum'] / $question['answer'], 2) * 200;
            $answer[$i]['user'] = $answerUser;
        }
        
        $answerOrder = $this->_arrayMultisort($answer, "answer_sum", SORT_DESC);

        return array('answer' => $answer, 'answerOrder' => $answerOrder);
    }

    /**
     * insert user answer about this question
     *
     * @param integer $uid
     * @param integer $qid
     * @param integer $aid
     * @return array
     */
    public function insertQuestionAnswer($uid, $qid, $aid)
    {
        $result = -1;
        
        $dalQuestion = Dal_Millionminds_Question::getDefaultInstance();
                
        //get question answer count
        $answerCount = $dalQuestion->getQstAnswerCount($qid);
        if ( $aid > $answerCount || $aid < 1 ) {
            return $result;
        }
                
        $this->_wdb->beginTransaction();
        try {
            $answer = array('uid' => $uid,
                            'qid' => $qid,
                            'aid' => $aid,
                            'create_time' => time());
            
            require_once 'Dal/Millionminds/Useranswer.php';
            $dalUseranswer = Dal_Millionminds_Useranswer::getDefaultInstance();
            //insert user answer about this question
            $dalUseranswer->insertUserAnswer($answer);
            
            //update question answer count
            $dalQuestion->updateQuestionAnswerCount($qid, 1);
            
            $this->_wdb->commit();
            $result = 1;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return $result;
        }
        return $result;
    }
    
    /**
     * multi array sort
     *
     * @return sort array
     */
    private function _arrayMultisort()
    {
        $arguments = func_get_args();
        $arrays    = $arguments[0];
        
        for ($c = (count($arguments)-1); $c > 0; $c--) {
            if (in_array($arguments[$c], array(SORT_ASC , SORT_DESC))) {
                continue;
            }
            
            $compare = create_function('$a,$b','return strcasecmp($a["'.$arguments[$c].'"], $b["'.$arguments[$c].'"]);');
            usort($arrays, $compare);
            
            if ($arguments[$c+1] == SORT_DESC) {
                $arrays = array_reverse($arrays);
            }
        }
        
        return $arrays ;
    }
    
    /**
     * check question is can show
     *
     * @param array $question
     * @param integer $visitor
     * @param integer $num
     * @param string $key
     * @return array
     */
    private function _checkQuestion($question, $visitor, $num, $key='uid', $nickauth=0) 
    {
        $result = array();
        $length = count($question);
        
        require_once 'Bll/Friend.php';
        
        for ($i = 0; $i < $length; $i++) {
            //self
            if ($question[$i][$key] == $visitor) {
                $result[] = $question[$i];
            }
            else if ($nickauth == 0 || $question[$i]['nickname_auth'] == 1) {
                //all open
                if ($question[$i]['public_type'] == 1) {
                    $result[] = $question[$i];
                }
                
                //friend open
                if ($question[$i]['public_type'] == 2) {
                    //check is friend
                    if (Bll_Friend::isFriend($question[$i][$key], $visitor)) {
                        $result[] = $question[$i];
                    }
                }
                
                //friend'friend open
                if ($question[$i]['public_type'] == 3) {
                    //check is friend's friend
                    if (Bll_Friend::isFriendFriend($question[$i][$key], $visitor) || Bll_Friend::isFriend($question[$i][$key], $visitor)) {
                        $result[] = $question[$i];
                    }
                }
            }
            if (count($result) == $num) {
                break;
            }
        }
        
        return $result;
    }
}
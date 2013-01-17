<?php

require_once 'Mdal/Abstract.php';

class Mdal_Quiz_Quiz extends Mdal_Abstract
{
    /**
     * user table name
     *
     * @var string
     */
    protected $table_user = 'quiz_list';

    protected static $_instance;

    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function getlistQuiz()
    {
        $sql = "SELECT * FROM quiz_list ORDER BY id";
        return $this->_rdb->fetchAll($sql);
    }

    /**
     * get quiz
     *
     * @param integer $qid
     * @return array
     */
    public function getQuizById($qid)
    {
        $sql = 'SELECT * FROM quiz_list WHERE id = :qid';
        return $this->_rdb->fetchRow($sql, array('qid' => $qid));
    }

    /**
     * insert answer result
     *
     * @param array $info
     * @return Integer
     */
    public function insert($info)
    {
        $this->_wdb->insert('quiz', $info);
        return $this->_wdb->lastInsertId();
    }

    public function updateStatus($uid)
    {
        $sql = "UPDATE quiz SET status = 0 WHERE answer_uid = :uid";
        return $this->_wdb->query($sql, array('uid' => $uid));
    }

    /**
     * get answer result
     *
     * @param integer $uid
     * @param integer $result
     * @param integer $status
     * @return integer
     */
    public function getQuizResultById($uid, $result, $status)
    {
        $sql = ' SELECT COUNT(1) as resultCount FROM quiz WHERE answer_uid = :uid AND status = :status AND result = :result
                 GROUP BY result';
        $count =  $this->_rdb->fetchOne($sql, array('uid' => $uid, 'result' => $result, 'status' => $status));

        return $count == null ? 0 : $count;
    }

    /**
     * get answer question's count
     * @param integer $uid
     * @param integer $fid
     * @return integer
     */
    public function getQuizCountById($uid)
    {
    	$sql = 'SELECT COUNT(1) FROM quiz WHERE answer_uid = :uid AND status = 1';
    	return $this->_rdb->fetchOne($sql, array('uid' => $uid));
    }

    /**
     * get answered all quiz_id
     *
     * @param unknown_type $uid
     */
    public function getQuizIdById($uid)
    {
        $sql = 'SELECT quiz_id FROM quiz WHERE answer_uid = :uid AND status = 1';
        return $this->_rdb->fetchAll($sql, array('uid' => $uid));
    }

    /**
     * check is exists
     *
     * @param unknown_type $uid
     * @param unknown_type $quid
     */
    public function isExistsQuizById($askUid, $answerUid, $quid)
    {
        $sql = " SELECT COUNT(1) FROM quiz WHERE ask_uid = :askUid AND answer_uid = :answerUid
                 AND quiz_id = :quid AND status = 1";

        $array = array('askUid' => $askUid, 'answerUid' => $answerUid, 'quid' => $quid);
        $result = $this->_rdb->fetchOne($sql, $array);

        return $result > 0 ? true : false;
    }

    /**
     * get answered all quiz count
     *
     * @param unknown_type $uid
     * @return unknown
     */
    public function getAllQuizCountById($uid)
    {
    	$sql = "SELECT COUNT(1) FROM quiz WHERE answer_uid = :uid";
        return $this->_rdb->fetchOne($sql, array('uid' => $uid));
    }

    /**
     * get asked all quiz count
     *
     * @param integer $askUid
     * @return integer
     */
    public function getAllAskedCountById($askUid)
    {
        $sql = "SELECT COUNT(1) FROM quiz WHERE ask_uid = :uid";
        return $this->_rdb->fetchOne($sql, array('uid' => $askUid));
    }

    /**
     * get answered quiz id
     *
     * @param integer $uid
     * @param boolean $isAsk
     * @return array
     */
    public function getAnsweredQuizlistById($uid, $isAsk)
    {
    	$columm = 'answer_uid';
        if (!$isAsk) {
        	$columm = 'ask_uid';
        }

        $sql = " SELECT q.quiz, q.quiz_name, u.quiz_id FROM quiz AS u, quiz_list AS q
                 WHERE u.$columm = :uid AND q.id = u.quiz_id GROUP BY u.quiz_id";
        return $this->_rdb->fetchAll($sql, array('uid' => $uid));
    }

    public function getQuizAnswerResultByQid($qid, $result, $uid)
    {
    	$sql = "SELECT COUNT(1) FROM quiz WHERE quiz_id = :qid AND result = :result AND answer_uid = :uid";

    	$array = array('uid' => $uid, 'qid' => $qid, 'result' => $result);

        return $this->_rdb->fetchOne($sql, $array);
    }

    public function getAnsweredQuizCountByQid($qid, $uid)
    {
        $sql = "SELECT COUNT(1) FROM quiz WHERE quiz_id = :qid AND answer_uid = :uid";
        return $this->_rdb->fetchOne($sql, array('qid' => $qid, 'uid' => $uid));
    }

    public function getAskedQuizCountByQid($uid, $result, $qid)
    {
        $sql = "SELECT COUNT(1) FROM quiz WHERE quiz_id = :qid AND ask_uid = :uid AND result = :result";
        return $this->_rdb->fetchOne($sql, array('qid' => $qid, 'uid' => $uid, 'result' => $result));
    }

    /**
     * get quiz answer result count
     *
     * @param integer $askId
     * @param integer $result
     */
    public function getQuizResultCountById($askId, $result)
    {
        $sql = "SELECT COUNT(1) FROM quiz WHERE ask_uid = :uid AND result = $result";
        return $this->_rdb->fetchOne($sql, array('uid' => $askId));
    }

    public function getNewAnsweredQuizById($uid, $pageIndex = 1, $pageSize = 5)
    {
    	$start = ($pageIndex - 1) * $pageSize;
    	$sql = " SELECT q.quiz,q.quiz_name,u.ask_uid,u.answer_uid,u.quiz_id,u.result,u.status,u.create_time
    	         FROM quiz AS u,quiz_list AS q
    	         WHERE u.answer_uid = :uid AND q.id = u.quiz_id
                 ORDER BY u.create_time DESC LIMIT $start, $pageSize";
    	return $this->_rdb->fetchAll($sql, array('uid' => $uid));
    }

    public function getRegardMyQuizById($uid, $result, $pageIndex = 1, $pageSize = 5)
    {
    	$start = ($pageIndex - 1) * $pageSize;
        $sql = " SELECT q.quiz,q.quiz_name,u.ask_uid,u.answer_uid,u.quiz_id,u.result,u.status,u.create_time
                 FROM quiz AS u,quiz_list AS q
                 WHERE u.ask_uid = :uid AND q.id = u.quiz_id AND u.result = $result
                 ORDER BY u.create_time DESC LIMIT $start, $pageSize";
        return $this->_rdb->fetchAll($sql, array('uid' => $uid));
    }

    /**
     * get quiz result list by ask id and answer id
     *
     * @param integer $askId
     * @param integer $answerId
     * @return array
     */
    public function getQuizResultList($askId, $answerId)
    {
        $sql = "SELECT q.*,l.quiz_name FROM quiz AS q,quiz_list AS l WHERE q.ask_uid =:ask_uid
                AND q.answer_uid=:answer_uid AND q.quiz_id=l.id ORDER BY q.quiz_id ";
        return $this->_rdb->fetchAll($sql, array('ask_uid' => $askId, 'answer_uid' => $answerId));
    }

    /**
     * get quiz result right count one to one
     *
     * @param integer $askId
     * @param integer $answerId
     * @return integer
     */
    public function getRightCountOneToOne($askId, $answerId)
    {
        $sql = "SELECT COUNT(1) FROM quiz WHERE ask_uid=:ask_uid AND answer_uid=:answer_uid AND result=1 ";
        return $this->_rdb->fetchOne($sql, array('ask_uid' => $askId, 'answer_uid' => $answerId));
    }

    /**
     * get quiz content
     *
     * @param integer $level
     * @param integer $type
     * @return string
     */
    public function getQuizContent($level, $type)
    {
        $sql = "SELECT content FROM quiz_content WHERE level=:level AND type=:type";
        return $this->_rdb->fetchOne($sql, array('level' => $level, 'type' => $type));
    }

    public function getQuizResultTypeById($uid, $result, $quizId, $pageIndex = 1, $pageSize = 3, $isGroup = 1)
    {
    	$start = ($pageIndex - 1) * $pageSize;
    	$group = "";
    	if ( $isGroup ) {
            $group = " GROUP BY answer_uid ";
        }

        $sql = "SELECT answer_uid FROM quiz WHERE quiz_id = :qid AND ask_uid = :uid AND result = :result
				$group ORDER BY create_time LIMIT $start, $pageSize";
		return $this->_rdb->fetchAll($sql, array('uid' => $uid, 'result' => $result, 'qid' => $quizId));
    }
}
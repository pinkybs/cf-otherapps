<?php

require_once 'Dal/Abstract.php';

class Dal_Millionminds_Question extends Dal_Abstract
{
    protected static $_instance;

    /**
     * get Dal_Millionminds_Question default
     *
     * @return Dal_Millionminds_Question
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }
    
    /**
     * update question answer count
     *
     * @param integer $qid
     * @param integer $change
     * @return void
     */
    public function updateQuestionAnswerCount($qid, $change)
    {
        $sql = "UPDATE millionmind_question SET answer = answer + :change WHERE qid=:qid ";
        $this->_wdb->query($sql, array('change'=>$change, 'qid'=>$qid));
    }
    
    /**
     * get popular question
     *
     * @param integer $type 0:all  1,2,3,4,5
     * @param integer $field  1:answer 2:create_time
     * @param integer $order  1:desc 2:asc
     * @param integer $pageIndex
     * @param integer $pageSize
     * @return array
     */
    public function getQuestion($type, $field, $order, $pageIndex = 1, $pageSize)
    {
        $start = ($pageIndex - 1) * $pageSize;
        
        $field = $field == 1 ? 'answer' : 'qid';
        $order = $order == 1 ? 'DESC' : 'ASC';
        
        if ($type == 0) {
            $sql = "SELECT qid,uid,public_type,question,answer,FROM_UNIXTIME(create_time,'%m月%d日 %H:%i') AS create_time 
                    FROM millionmind_question ORDER BY $field $order LIMIT $start,$pageSize";
            return $this->_rdb->fetchAll($sql);
        }
        else {
            $sql = "SELECT qid,uid,public_type,question,answer,FROM_UNIXTIME(create_time,'%m月%d日 %H:%i') AS create_time  
                    FROM millionmind_question WHERE type=:type ORDER BY $field $order LIMIT $start,$pageSize";
            return $this->_rdb->fetchAll($sql, array('type'=>$type));
        }
    }
    
    /**
     * get user create question
     *
     * @param integer $uid
     * @return array
     */
    public function getUserQuestion($uid, $pageIndex = 1, $pageSize)
    {
        $start = ($pageIndex - 1) * $pageSize;
        
        $sql = "SELECT qid,uid,nickname_auth,public_type,question,answer,FROM_UNIXTIME(create_time,'%m月%d日 %H:%i') AS create_time 
                FROM millionmind_question WHERE uid=:uid ORDER BY qid DESC LIMIT $start,$pageSize";
        return $this->_rdb->fetchAll($sql, array('uid'=>$uid));
    }
    
    /**
     * get archive data
     *
     * @param integer $uid
     * @param integer $type 0:all  1,2,3,4,5
     * @param integer $field  1:answer 2:create_time
     * @param integer $order  1:desc 2:asc
     * @param integer $pageIndex
     * @param integer $pageSize
     * @return array
     */
    public function getArchiveData($uid, $type, $field, $order, $pageIndex = 1, $pageSize)
    {
        $start = ($pageIndex - 1) * $pageSize;
        
        $field = $field == 1 ? 'answer' : 'qid';
        $order = $order == 1 ? 'DESC' : 'ASC';
        
        if ($type == 0) {
            $sql = "SELECT a.*,IFNULL(b.qid,0) AS hasAnswered FROM
                    (SELECT qid,uid,public_type,question,answer,FROM_UNIXTIME(create_time,'%m月%d日 %H:%i') AS create_time 
                    FROM millionmind_question ORDER BY $field $order LIMIT $start,$pageSize) AS a LEFT JOIN 
                    (SELECT qid FROM millionmind_user_answer WHERE uid=:uid) AS b ON a.qid=b.qid";
            return $this->_rdb->fetchAll($sql, array('uid'=>$uid));
        }
        else {
            $sql = "SELECT a.*,IFNULL(b.qid,0) AS hasAnswered FROM
                    (SELECT qid,uid,public_type,question,answer,FROM_UNIXTIME(create_time,'%m月%d日 %H:%i') AS create_time  
                    FROM millionmind_question WHERE type=:type ORDER BY $field $order LIMIT $start,$pageSize) AS a LEFT JOIN 
                    (SELECT qid FROM millionmind_user_answer WHERE uid=:uid) AS b ON a.qid=b.qid";
            return $this->_rdb->fetchAll($sql, array('type'=>$type, 'uid'=>$uid));
        }
    }
    
    /**
     * get question count
     *
     * @param integer $type 0:all  1,2,3,4,5
     * @param integer $field  1:answer 2:create_time
     * @return integer
     */
    public function getArchiveCount($type, $field)
    {
       if ($type == 0) {
            $sql = "SELECT COUNT(1) FROM millionmind_question";
            return $this->_rdb->fetchOne($sql);
        }
        else {
            $sql = "SELECT COUNT(1) FROM millionmind_question WHERE type=:type";
            return $this->_rdb->fetchOne($sql, array('type'=>$type));
        } 
    }
    
    /**
     * get user not answer nature
     *
     * @param integer $uid
     * @return array
     */
    public function getNotAnswerNature($uid)
    {
        $sql = "SELECT * FROM millionmind_question WHERE qid NOT IN
                (SELECT qid FROM millionmind_user_answer WHERE uid=:uid AND qid>=1 AND qid<=50)
                AND qid>=1 AND qid<=50 ORDER BY RAND() LIMIT 1";
        
        return $this->_rdb->fetchRow($sql, array('uid'=>$uid));
    }

    /**
     * get friend not answer nature
     *
     * @param integer $uid
     * @param integer $from_uid
     * @return array
     */
    public function getFriendNotAnswerNature($uid, $from_uid)
    {
        $sql = "SELECT * FROM millionmind_question WHERE qid NOT IN
                (SELECT qid FROM millionmind_friend_nature_answer WHERE uid=:uid AND from_uid=:from_uid AND qid>=1 AND qid<=50)
                AND qid>=1 AND qid<=50 ORDER BY RAND() LIMIT 1";
        
        return $this->_rdb->fetchRow($sql, array('uid'=>$uid, 'from_uid'=>$from_uid));
    }
    
    /**
     * get nature answer user
     *
     * @param string $qid
     * @param string $aid
     * @param string $fids
     * @return array
     */
    public function getNatureAnswerUser($qid, $fids, $aid)
    {
        $fids = $this->_rdb->quote($fids);
        
        $aidType = '';
        $array = array('qid'=>$qid);
        
        if ( $aid > 0 && $aid < 4 ) {
            $aidType = ' AND aid=:aid ';
            $array = array('qid'=>$qid, 'aid'=>$aid);
        }
        
        $sql = "SELECT uid FROM millionmind_user_answer WHERE qid=:qid " . $aidType . " AND uid IN ($fids) ORDER BY id DESC";
        $friends = $this->_rdb->fetchAll($sql, $array);
        
        $c = count($friends);
        
        if ($c < 7) {
            $limit = 7 - $c;
            $sql = "SELECT uid FROM millionmind_user_answer WHERE qid=:qid " . $aidType . " AND uid NOT IN ($fids) ORDER BY id DESC LIMIT $limit";
            $notFriends = $this->_rdb->fetchAll($sql, $array);
            
            return array_merge($friends, $notFriends);
        }
        
        return $friends;
    }

    /**
     * add a unapproved question
     *
     * @param array $info
     * @return number
     */
    public function insertQuestion($info)
    {
        $this->_wdb->insert('millionmind_question_unaudited', $info);
        return $this->_wdb->lastInsertId();
    }
    
    /**
     * add a approved question 
     *
     * @param unknown_type $info
     * @return unknown
     */
    public function approveQuestion($info)
    {
        $this->_wdb->insert('millionmind_question', $info);
        return $this->_wdb->lastInsertId();
    }
    
    /**
     * deny a unapproved question
     *
     * @param  $id
     * @return void  
     */
    public function denyQuestion($id)
    {
        $sql = "UPDATE millionmind_question_unaudited SET operated=1 WHERE id=:id";
		$this->_wdb->query($sql, array('id' => $id));
    }
    
    /**
     * add a unapproved answer
     *
     * @param array $info
     * @return void
     */
    public function insertAnswer($info)
    {
        $this->_wdb->insert('millionmind_question_answer_unaudited', $info);
        $this->_wdb->lastInsertId();
    }
    
    /**
     * add a approved answer
     *
     * @param array $info
     * @return void
     */
    public function approveAnswer($info)
    {
        $this->_wdb->insert('millionmind_question_answer', $info);
        $this->_wdb->lastInsertId();
    }
    
    /**
     * get unapproved question
     *
     * @param number $pageIndex
     * @param number $pageSize
     * @param number $type
     * @param string $order
     * @return array
     */
    public function getUnapprovedQuestion($pageIndex,$pageSize,$type,$order="ASC")
    {   
        $start = ($pageIndex-1)*$pageSize;
        if ($type == 0) {
            $type = "2 or `type`=3 or `type`=4 or `type`=5";
        }
        if ($order == 2){
            $order = "DESC";
        }
        else {
            $order = "ASC";
        }
        
        $sql = "SELECT question,FROM_UNIXTIME(create_time,'%m月%d日 %H:%i') AS create_time,id FROM millionmind_question_unaudited WHERE (`type`=$type) AND operated=0 ORDER BY create_time $order,id $order LIMIT $start,$pageSize";

        return $this->_rdb->fetchAll($sql);
    }    
    
    /**
     * get unaudited question count
     *
     * @param string $type
     * @return number
     */
    public function getUnapprovedQuestionCnt($type=0)
    {  
        if ($type == 0) {
            $type = "2 or `type`=3 or `type`=4 or `type`=5";
        }
        $sql = "SELECT COUNT(1) AS num FROM millionmind_question_unaudited WHERE (`type`=$type) AND operated=0";
        
        return $this->_rdb->fetchOne($sql);
    }
    
    /**
     * get unapproved question by id
     *
     * @param string $id
     * @return array
     */
    public function getUnQstById($id)
    {  
        $sql = "SELECT question,type,nickname_auth,public_type,uid,id FROM millionmind_question_unaudited WHERE id=:id";
        
        return $this->_rdb->fetchRow($sql,array('id'=>$id));
    }
    
    /**
     * delete unapproved question by id
     *
     * @param string $id
     * @return void
     */
    public function delUnQstById($id)
    {  
        $sql = "DELETE FROM millionmind_question_unaudited WHERE id=:id";
        
        return $this->_wdb->query($sql,array('id'=>$id));
    }
    
    /**
     * delete unapproved question answer by qid
     *
     * @param string $qid
     * @return void
     */
    public function delUnAswByQid($qid)
    {  
        $sql = "DELETE FROM millionmind_question_answer_unaudited WHERE qid=:qid";
        
        return $this->_wdb->query($sql,array('qid'=>$qid));
    }
    
    /**
     * check whether $id is exist
     *
     * @param string    $id
     * @return  boolean
     */
    public function isUnQstId($id)
    {  
        $sql = "SELECT COUNT(1) FROM millionmind_question_unaudited WHERE id=:id";
        
        $result = $this->_rdb->fetchOne($sql,array('id'=>$id));
        return $result > 0;
    }
    
    /**
     * check whether $id is exist and lock the table
     *
     * @param string $id
     * @return boolean 
     */
    public function isUnQstIdLock($id)
    {  
        $sql = "SELECT COUNT(1) FROM millionmind_question_unaudited WHERE id=:id FOR UPDATE";
        
        $result = $this->_rdb->fetchOne($sql,array('id'=>$id));
        return $result > 0;
    }
    
    /**
     * check whether $qid is exist
     *
     * @param string    $id
     * @return  boolean
     */
    public function isQstId($qid)
    {  
        $sql = "SELECT COUNT(1) FROM millionmind_question WHERE qid=:qid";
        
        $result = $this->_rdb->fetchOne($sql,array('qid'=>$qid));
        return $result > 0;
    }
    
    /**
     * get unapproved answer by qid
     *
     * @param string $qid
     * @return array
     */
    public function getUnAswByQid($qid)
    {  
        $sql = "SELECT aid,answer FROM millionmind_question_answer_unaudited WHERE qid=:qid";
        
        return $this->_rdb->fetchAll($sql,array('qid'=>$qid));
    }
    
    /**
     * get answer by qid
     *
     * @param string $qid
     * @return array
     */
    public function getAswByQid($qid)
    {  
        $sql = "SELECT aid,answer FROM millionmind_question_answer WHERE qid=:qid";
        
        return $this->_rdb->fetchAll($sql,array('qid'=>$qid));
    }
    
    /**
     * get a question data
     *
     * @param string $qid
     * @return array
     */
    public function getOneQst($qid)
    {  
        $sql = "SELECT qid,uid,nickname_auth,public_type,question,category,answer,category AS name FROM millionmind_question WHERE qid=:qid";
        
        return $this->_rdb->fetchRow($sql,array('qid'=>$qid));
    }
       
    /**
     * get friend answer question
     *
     * @param integer $uid
     * @param string $fids
     * @return array
     */
    public function getFriendAnswerQuestion($uid, $fids)
    {
        $fids = $this->_rdb->quote($fids);
        $sql = "SELECT a.qid FROM 
                (SELECT qid,create_time FROM millionmind_user_answer WHERE uid IN ($fids) ORDER BY id DESC) AS a WHERE NOT EXISTS 
                (SELECT qid FROM millionmind_user_answer AS b WHERE a.qid<>b.qid AND b.uid=:uid) LIMIT 10";
        
        $question1 = $this->_rdb->fetchAll($sql, array('uid'=>$uid));
        
        $c = count($question);
        
        if ($c < 10) {
            $limit = 10 - $c;
            $sql = "SELECT a.qid FROM 
                    (SELECT qid,create_time FROM millionmind_user_answer WHERE uid IN ($fids) ORDER BY id DESC) AS a,
                    (SELECT qid FROM millionmind_user_answer WHERE uid=:uid) AS b
                    WHERE a.qid=b.qid LIMIT $limit";
            $question2 = $this->_rdb->fetchAll($sql, array('uid'=>$uid));
            
            $question1 = array_merge($question1, $question2);
        }
        
        $qid = '';
        foreach ($question1 as $item) {
            $qid .= $item['qid'] . ',';
        }
        
        if (strlen($qid) > 0) {
            $qid = substr($qid, 0, strlen($qid) - 1);
            
            $sql = "SELECT qid,question FROM millionmind_question WHERE qid IN ($qid)";
            
            return $this->_rdb->fetchAll($sql);
        }
        else {
            return array();
        }
    }
    
    /**
     * get user last answer
     *
     * @param integer $uid
     * @return array
     */
    public function getUserRandomAnswer($uid)
    {
        $sql = "SELECT a.*,b.name FROM millionmind_question AS a,millionmind_question_type AS b WHERE qid=
                (SELECT qid FROM (SELECT qid FROM millionmind_user_answer WHERE uid=:uid ORDER BY RAND() LIMIT 1) AS x) AND a.type=b.id";
        
        return $this->_rdb->fetchRow($sql, array('uid'=>$uid));
    }
    
    /**
     * get question answer info
     *
     * @param integer $qid
     * @return array
     */
    public function getQuestionAnswerCount($qid)
    {
        $sql = "SELECT a.qid,a.aid,IFNULL(b.answer_sum,0) AS answer_sum,a.answer FROM millionmind_question_answer AS a LEFT JOIN
                (SELECT qid,aid,COUNT(aid) AS answer_sum FROM millionmind_user_answer WHERE qid=:qid GROUP BY aid) AS b
                ON a.qid=b.qid AND a.aid=b.aid WHERE a.qid=:qid";
        
        return $this->_rdb->fetchAll($sql, array('qid'=>$qid));
    }

    /**
     * get question answer count
     *
     * @param integer $qid
     * @return integer
     */
    public function getQstAnswerCount($qid)
    {
        $sql = "SELECT count(1) FROM millionmind_question_answer WHERE qid=:qid";
        
        return $this->_rdb->fetchOne($sql, array('qid'=>$qid));
    }
    
    /**
     * get question answer user
     *
     * @param integer $qid
     * @param integer $aid
     * @return array
     */
    public function getQuestionAnswerUser($qid, $aid)
    {
        $sql = "SELECT uid FROM millionmind_user_answer WHERE qid=:qid AND aid=:aid ORDER BY create_time DESC limit 14";
        
        return $this->_rdb->fetchAll($sql, array('qid'=>$qid, 'aid'=>$aid));
    }
    
    /**
     * update answer in millionmind_question
     *
     * @param string $qid
     * @return void
     */
    public function updateAnswer($qid)
    {     
        $sql = "UPDATE millionmind_question SET answer=(SELECT COUNT(1) FROM millionmind_user_answer WHERE qid=$qid) WHERE qid=$qid";
    
        $this->_wdb->query($sql);  
    }
    
    /**
     * update all answer in millionmind_question
     *
     * @return  void
     */
    public function updateAnswerAll()
    {     
        for($i = 0; $i < 200; $i++){            
            $sql1 = "SELECT COUNT(1) FROM millionmind_user_answer WHERE qid=$i";
            $num = $this->_rdb->fetchOne($sql1);
            
            $sql = "UPDATE millionmind_question SET answer=$num WHERE qid=$i";        
            $this->_wdb->query($sql);  
        }
    }
}
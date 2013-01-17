<?php

require_once 'Dal/Abstract.php';

/**
 * Dal Millionminds Muser
 * MixiApp Millionminds user Data Access Layer
 *
 * @package    Dal/Millionminds
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/07/27    Liz
 */
class Dal_Millionminds_Muser extends Dal_Abstract
{
    /**
     * user table name
     *
     * @var string
     */
    protected $table_user = 'millionmind_user';
    
    protected static $_instance;
    
    /**
     * get Dal_Millionminds_Question default
     *
     * @return Dal_Millionminds_Muser
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        
        return self::$_instance;
    }
    
    /**
     * insert millionminds user
     *
     * @param array $userInfo
     * @return void
     */
    public function insertMillionmindsUser($userInfo)
    {
        $this->_wdb->insert($this->table_user, $userInfo);
    }
    
    /**
     * update user nature result
     *
     * @param integer $uid
     * @param integer $nature
     * @return void
     */
    public function updateUserNature($uid, $nature)
    {
        $sql = "UPDATE $this->table_user SET nature_result=:nature WHERE uid=:uid ";
        $this->_wdb->query($sql, array('uid'=>$uid, 'nature'=>$nature));
    }

    /**
     * delete friend evaluation
     *
     * @param integer $uid
     * @param integer $from_uid
     * @return void
     */
    public function deleteFriendEvaluation($uid, $from_uid)
    {
        $sql = "DELETE FROM millionmind_friend_evaluation WHERE uid=:uid AND from_uid=:from_uid";
        $this->_wdb->query($sql, array('uid'=>$uid, 'from_uid'=>$from_uid));
    }
    
    /**
     * insert friend evaluation
     *
     * @param array $uid
     * @return void
     */
    public function insertFriendEvaluation($info)
    {
        $this->_wdb->insert("millionmind_friend_evaluation", $info);
    }
    
    /**
     * check the user is join millionmind
     *
     * @param integer $uid
     */
    public function isInMillionminds($uid)
    {
        $sql = "SELECT COUNT(1) FROM $this->table_user WHERE uid=:uid";

        $result = $this->_rdb->fetchOne($sql,array('uid'=>$uid));

        return $result > 0;
    }

    /**
     * get user info
     *
     * @param integer $uid
     * @return array
     */
    public function getUser($uid)
    {
        $sql = "SELECT u.*,r.* FROM $this->table_user AS u LEFT JOIN millionmind_nature_result AS r ON u.nature_result=r.id WHERE u.uid=:uid";

        return $this->_rdb->fetchRow($sql,array('uid'=>$uid));
    }

    /**
     * get friend evaluation nature result
     *
     * @param integer $uid
     * @param integer $from_uid
     * @return integer
     */
    public function getFriendEvaluation($uid, $from_uid)
    {
        $sql = "SELECT nature_result FROM millionmind_friend_evaluation WHERE uid=:uid AND from_uid=:from_uid";

        return $this->_rdb->fetchOne($sql,array('uid'=>$uid, 'from_uid'=>$from_uid));
    }
    
    /**
     * get user near prople array
     *
     * @param integer $uid
     * @return array
     */
    public function getUserNearPeople($uid)
    {
        $sql = "SELECT uid FROM $this->table_user WHERE nature_result in(SELECT r.group_id2 FROM 
                millionmind_complare_result AS r,$this->table_user AS u WHERE r.group_id1=u.nature_result AND r.type<=2 
                AND u.uid=:uid) AND uid<>:uid ORDER BY rand() LIMIT 0,7";

        return $this->_rdb->fetchAll($sql,array('uid'=>$uid));
    }
    
    /**
     * get user answer info
     *
     * @param integer $uid
     * @param integer $questionType
     * @param integer $pageIndex
     * @param integer $pageSize
     * @return array
     */
    public function getUserAnswer($uid, $questionType, $pageIndex, $pageSize=6)
    {
        $start = ($pageIndex-1) * $pageSize;
        
        $sql = "SELECT a.*,q.question,q.uid AS question_uid,q.type,qa.answer,q.public_type,q.nickname_auth 
                FROM millionmind_user_answer AS a,millionmind_question AS q,millionmind_question_answer AS qa
                WHERE a.aid=qa.aid AND a.qid=qa.qid AND a.qid=q.qid AND a.uid=:uid ";
    
        //question type, '0'->all 
        if ( $questionType != 0 ) {
            $sql .= " AND q.type=:type ";
            $array = array('uid' => $uid, 'type' => $questionType);
        }
        else {
            $array = array('uid' => $uid);
        }
        
        $sql .= " LIMIT $start,$pageSize";
        
        return $this->_rdb->fetchAll($sql, $array);
    }

    /**
     * get user answer count
     *
     * @param integer $uid
     * @param integer $questionType
     * @return array
     */
    public function getUserAnswerCount($uid, $questionType)
    {        
        $sql = "SELECT count(1) FROM millionmind_user_answer AS a,millionmind_question AS q WHERE a.qid=q.qid AND a.uid=:uid ";
        
        //question type, '0'->all 
        if ( $questionType != 0 ) {
            $sql .= " AND q.type=:type ";
            $array = array('uid' => $uid, 'type' => $questionType);
        }
        else {
            $array = array('uid' => $uid);
        }
        
        return $this->_rdb->fetchOne($sql, $array);
    }

    /**
     * get user nature question point
     *
     * @param integer $uid
     * @param integer $questionType
     * @return integer
     */
    public function getUserQuestionPoint($uid, $questionType)
    {        
        switch ($questionType) {
            case 1 :    //morality
                $typeSql = " qid<11 ";
                break;
            case 2 :    //love
                $typeSql = " qid>10 AND qid<21 ";
                break;
            case 3 :    //count
                $typeSql = " qid>20 AND qid<31 ";
                break;
            case 4 :    //instinct
                $typeSql = " qid>30 AND qid<41 ";
                break;
            case 5 :    //harmony
                $typeSql = " qid>40 AND qid<51 ";
                break;
        }
        
        $sql = "SELECT (SELECT count(1)*2 FROM millionmind_user_answer WHERE aid=1 AND " . $typeSql . " AND uid=:uid) + 
                (SELECT count(1)*1 FROM millionmind_user_answer WHERE aid=3 AND " . $typeSql . " AND uid=:uid) AS point";

        $point = $this->_rdb->fetchOne($sql,array('uid'=>$uid));    

        return round($point/4);
    }

    /**
     * get friend nature question point
     *
     * @param integer $uid
     * @param integer $from_uid
     * @param integer $questionType
     * @return integer
     */
    public function getFriendQuestionPoint($uid, $from_uid, $questionType)
    {        
        switch ($questionType) {
            case 1 :    //morality
                $typeSql = " qid<11 ";
                break;
            case 2 :    //love
                $typeSql = " qid>10 AND qid<21 ";
                break;
            case 3 :    //count
                $typeSql = " qid>20 AND qid<31 ";
                break;
            case 4 :    //instinct
                $typeSql = " qid>30 AND qid<41 ";
                break;
            case 5 :    //harmony
                $typeSql = " qid>40 AND qid<51 ";
                break;
        }
        
        $sql = "SELECT (SELECT count(1)*2 FROM millionmind_friend_nature_answer WHERE aid=1 AND " . $typeSql . " AND uid=:uid AND from_uid=:from_uid) + 
                (SELECT count(1)*1 FROM millionmind_friend_nature_answer WHERE aid=3 AND " . $typeSql . " AND uid=:uid AND from_uid=:from_uid) AS point";

        $point = $this->_rdb->fetchOne($sql,array('uid'=>$uid, 'from_uid'=>$from_uid));    

        return round($point/4);
    }
    
    /**
     * get nature result by type
     * 
     * @param string $morality
     * @param string $love
     * @param string $count
     * @param string $instinct
     * @param string $harmony
     * @return array
     */
    public function getNatureByType($morality, $love, $count, $instinct, $harmony)
    {
        $sql = "SELECT * FROM millionmind_nature_result_type 
                WHERE morality=:morality AND love=:love AND count=:count AND instinct=:instinct AND harmony=:harmony";
        
        $array = array('morality' => $morality,
                       'love' => $love,
                       'count' => $count,
                       'instinct' => $instinct,
                       'harmony' => $harmony);
        
        return $this->_rdb->fetchRow($sql, $array);
    }

    /**
     * get nature result by group id
     *
     * @param integer $id
     * @return array
     */
    public function getNatureByGroupId($id)
    {
        $sql = "SELECT * FROM millionmind_nature_result WHERE id=:id";
        
        return $this->_rdb->fetchRow($sql, array('id'=>$id));
    }
    
    /**
     * get evaluated fid
     *
     * @param integer $uid
     * @return array
     */
    public function getEvaluatedFid($uid)
    {
        $sql = "SELECT from_uid FROM millionmind_friend_evaluation WHERE uid=:uid";
        
        return $this->_rdb->fetchAll($sql, array('uid'=>$uid));
    }

    /**
     * get user near prople array
     *
     * @param integer $uid
     * @return array
     */
    public function getFriendAveQuestionPoint($uid, $questionType, $fids, $fidCount)
    {        
        switch ($questionType) {
            case 1 :    //morality
                $typeSql = " qid<11 ";
                break;
            case 2 :    //love
                $typeSql = " qid>10 AND qid<21 ";
                break;
            case 3 :    //count
                $typeSql = " qid>20 AND qid<31 ";
                break;
            case 4 :    //instinct
                $typeSql = " qid>30 AND qid<41 ";
                break;
            case 5 :    //harmony
                $typeSql = " qid>40 AND qid<51 ";
                break;
        }
        
        $fids = $this->_rdb->quote($fids);
        
        $sql = "SELECT (SELECT count(1)*2 FROM millionmind_friend_nature_answer 
                WHERE aid=1 AND " . $typeSql . " AND from_uid in($fids) AND uid=:uid) + 
                (SELECT count(1)*1 FROM millionmind_friend_nature_answer 
                WHERE aid=3 AND " . $typeSql . " AND from_uid in($fids) AND uid=:uid) AS point";

        $point = $this->_rdb->fetchOne($sql, array('uid'=>$uid));    

        if ( $point ) {
            $aveFidPoint = $point/$fidCount;
            $result = round($aveFidPoint/4);
        }
        else {
            $result = 0;
        }
        
        return $result;
    }
    
    /**
     * check user begin nature
     *
     * @param integer $uid
     * @return boolean
     */
    public function checkUserBeginNature($uid)
    {
        $sql = "SELECT COUNT(1) FROM millionmind_user_answer WHERE uid=:uid AND qid BETWEEN 1 AND 50";
        
        $result = $this->_rdb->fetchOne($sql, array('uid'=>$uid));
        
        return $result == 0;
    }
    
    /**
     * get all of the user count
     *
     * @return integer
     */
    public function getAllAppUserCount()
    {
        $sql = "SELECT COUNT(1) FROM $this->table_user";
        
        return $this->_rdb->fetchOne($sql);
    }
    
    /**
     * get user rank number ,all of the app user
     *
     * @param integer $uid
     * @return integer
     */
    public function getUserRankNmAllAppUser($uid)
    {
        $sql1 = "SET @pos=0";
        $this->_rdb->query($sql1);
        
        $sql = "SELECT b.rank,a.uid FROM $this->table_user AS a,
                (SELECT @pos:=@pos+1 AS rank,uid FROM $this->table_user ORDER BY create_time DESC,uid DESC) AS b 
                WHERE a.uid=b.uid AND a.uid=:uid";
        
        $reuslt = $this->_rdb->fetchRow($sql, array('uid' => $uid));
        
        return $reuslt['rank'];
    }
    
    /*
     * get ranking user
     *
     * @param integer $start
     * @param integer $pageSize
     * @return array
     */
    public function getRankingUserAllAppUser($start ,$pageSize)
    {
        $sql = "SELECT uid FROM $this->table_user ORDER BY create_time ASC,uid ASC LIMIT $start,$pageSize";
                        
        return $this->_rdb->fetchAll($sql);
    }

    /**
     * get the count of my mixi in the app
     *
     * @param integer $uid
     * @param string $fids
     * @return integer
     */
    public function getAppMyMixiCount($uid, $fids)
    {
        $fids = $this->_rdb->quote($fids);
        
        $sql = "SELECT COUNT(1) FROM $this->table_user WHERE uid IN ($fids,:uid)";
        
        return $this->_rdb->fetchOne($sql, array('uid' => $uid));
    }
    
    /*
     * get ranking user
     *
     * @param integer $uid
     * @param string $fids
     * @param integer $start
     * @param integer $pageSize
     * @return array
     */
    public function getRankingUserMyMixi($uid, $fids, $start ,$pageSize)
    {
        $fids = $this->_rdb->quote($fids);
        
        $sql = "SELECT uid FROM $this->table_user WHERE uid IN ($fids,:uid) ORDER BY create_time ASC,uid ASC LIMIT $start,$pageSize";
        
        return $this->_rdb->fetchAll($sql, array('uid' => $uid));
    }

    /**
     * get user rank number ,all of the app user
     *
     * @param integer $rankUid
     * @param integer $uid
     * @param string $fids
     * @return integer
     */
    public function getUserRankNmMyMixi($rankUid, $uid, $fids)
    {
        $fids = $this->_rdb->quote($fids);
        
        $sql1 = "SET @pos=0";
        $this->_rdb->query($sql1);
        
        $sql = "SELECT b.rank,a.uid FROM $this->table_user AS a,
                (SELECT @pos:=@pos+1 AS rank,uid FROM $this->table_user WHERE uid IN ($fids, :uid) ORDER BY create_time DESC,uid DESC) AS b 
                WHERE a.uid=b.uid AND a.uid=:rankUid";
        
        $reuslt = $this->_rdb->fetchRow($sql, array('rankUid' => $rankUid, 'uid' => $uid));
        
        return $reuslt['rank'];
    }
}
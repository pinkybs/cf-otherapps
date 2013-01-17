<?php

require_once 'Dal/Abstract.php';

/**
 * Dal Millionminds Useranswer
 * MixiApp Millionminds User answer Data Access Layer
 *
 * @package    Dal/Millionminds
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/07/27    Liz
 */
class Dal_Millionminds_Useranswer extends Dal_Abstract
{
    /**
     * table name
     *
     * @var string
     */
    protected $table_user_answer = 'millionmind_user_answer';
    
    /**
     * table name
     *
     * @var string
     */ 
    protected $table_friend_nature_answer = 'millionmind_friend_nature_answer';
    
    protected static $_instance;
    
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        
        return self::$_instance;
    }

    /**
     * insert user question answer
     *
     * @param array $info
     * @return number
     */
    public function insertUserAnswer($info)
    {
        $this->_wdb->insert($this->table_user_answer, $info);
        return $this->_wdb->lastInsertId();
    }

    /**
     * insert user question answer
     *
     * @param array $info
     * @return number
     */
    public function insertFriendAnswer($info)
    {
        $this->_wdb->insert($this->table_friend_nature_answer, $info);
        return $this->_wdb->lastInsertId();
    }

    /**
     * update nature question answer count
     *
     * @param integer $change
     * @return void
     */
    public function updateNatureAnswerCount($change)
    {
        $sql = "UPDATE millionmind_question SET answer = answer + :change WHERE qid<51";
        $this->_wdb->query($sql, array('change' => $change));
    }
    
    /**
     * delete user nature answer
     *
     * @param integer $uid
     * @return void
     */
    public function deleteUserNatureAnswer($uid)
    {
        $sql = "DELETE FROM $this->table_user_answer WHERE qid < 51 AND uid=:uid";
        $this->_wdb->query($sql, array('uid' => $uid));
    }

    /**
     * delete friend evaluation answer
     *
     * @param integer $uid
     * @param integer $from_uid
     * @return void
     */
    public function deleteFriendEvaluationAnswer($uid, $from_uid)
    {
        $sql = "DELETE FROM $this->table_friend_nature_answer WHERE qid < 51 AND uid=:uid AND from_uid=:from_uid";
        $this->_wdb->query($sql, array('uid' => $uid, 'from_uid' => $from_uid));
    }
    
    /**
     * get user all answer count
     *
     * @param integer $uid
     * @return integer
     */
    public function getUserAnswerCount($uid)
    {
        $sql = "SELECT count(1) FROM $this->table_user_answer WHERE uid=:uid";

        return $this->_rdb->fetchOne($sql, array('uid'=>$uid));
    }
        
    /**
     * get userlist qid and uid
     * 
     * @param string $uid
     * @param string $qid
     * @return array
     */
    public function getUidList($uid, $qid ,$fids)
    {
        $fids = $this->_rdb->quote($fids);
        $sql = "SELECT uid FROM millionmind_user_answer WHERE qid=:qid AND uid IN ($fids)
                UNION SELECT uid FROM millionmind_user_answer WHERE qid=:qid AND uid NOT IN 
                ($fids) limit 0,7";

        return $this->_rdb->fetchAll($sql,array('qid'=>$qid));
    }

    /**
     * get user answer by question id
     *
     * @param integer $uid
     * @param integer $qid
     * @return integer
     */
    public function getUserAnswer($uid, $qid)
    {
        $sql = "SELECT aid FROM $this->table_user_answer WHERE uid=:uid AND qid=:qid";

        return $this->_rdb->fetchOne($sql, array('uid'=>$uid, 'qid'=>$qid));
    }

    /**
     * get friend answer by question id
     *
     * @param integer $uid
     * @param integer $fromUid
     * @param integer $qid
     * @return integer
     */
    public function getFriendAnswer($uid, $fromUid, $qid)
    {
        $sql = "SELECT aid FROM $this->table_friend_nature_answer WHERE uid=:uid AND from_uid=:from_uid AND qid=:qid";

        return $this->_rdb->fetchOne($sql, array('uid'=>$uid,'from_uid'=>$fromUid, 'qid'=>$qid));
    }
    
    /**
     * return whether the user has answered the question
     *
     * @param string $uid
     * @param string $qid
     * @return boolean
     */
    public function hasAnswered($uid,$qid)
    {
        $sql = "SELECT COUNT(1) FROM $this->table_user_answer WHERE uid=:uid AND qid=:qid";

        $result = $this->_rdb->fetchOne($sql, array('uid'=>$uid,'qid'=>$qid));
        return $result > 0;
    }
    
    /**
     * delete user answer by uid and qid
     *
     * @param string $uid
     * @param string $qid
     * @return void
     */
    public function delAnswer($uid,$qid)
    {
        $sql = "DELETE FROM $this->table_user_answer WHERE qid=:qid AND uid=:uid";
        
        $this->_wdb->query($sql,array('qid'=>$qid,'uid'=>$uid));
    }
}
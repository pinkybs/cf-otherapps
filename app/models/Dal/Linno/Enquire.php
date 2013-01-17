<?php

require_once 'Dal/Abstract.php';

class Dal_Linno_Enquire extends Dal_Abstract
{
    /**
     * user table name
     *
     * @var string
     */
    protected $table_enquire = 'linno_enquire';
    
    protected static $_instance;
    
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        
        return self::$_instance;
    }
  
    /**
     * insert linno enquire
     *
     * @param array $enquire
     * @return integer
     */
    public function insertEnquire($enquire)
    {
        $this->_wdb->insert($this->table_enquire, $enquire);
        return $this->_wdb->lastInsertId();
    }

    /**
     * get enquire info by enquire id
     *
     * @param integer $eid
     * @return array
     */
    public function getEnquire($eid)
    {
        $sql = "SELECT * FROM linno_enquire_question WHERE qqid=:eid";

        return $this->_rdb->fetchRow($sql,array('eid'=>$eid));
    }

    /**
     * get enquire answer by enquire id
     *
     * @param integer $eid
     * @return array
     */
    public function getEnquireAnswer($eid)
    {
        $sql = "SELECT * FROM linno_enquire_answer WHERE qqid=:eid";

        return $this->_rdb->fetchAll($sql,array('eid'=>$eid));
    }
    
    /**
     * check is user have answered enquire
     *
     * @param integer $uid
     * @param integer $eid
     * @return boolean
     */
    public function isAnsweredEnquire($uid, $eid)
    {
        $sql = "SELECT count(1) FROM linno_enquire WHERE actor=:uid AND qid =:eid";

        $result = $this->_rdb->fetchOne($sql,array('uid'=>$uid, 'eid'=>$eid));
        
        return $result ? true : false;
    }
    
    /**
     * select user not answer enquires
     *
     * @param integer $uid
     * @param integer $pageIndex
     * @param integer $pageIndex
     * @return array
     */
    public function getNotAnswerEnquire($uid, $pageIndex = 1, $pageSize = 5)
    {
        $start = ($pageIndex - 1) * $pageSize;
        
        $sql = "SELECT * FROM linno_enquire_question AS q WHERE NOT EXISTS(SELECT * FROM linno_enquire AS e 
                WHERE e.qid=q.qqid AND e.actor=:uid) ORDER BY q.update_time DESC LIMIT $start,$pageSize";

        return $this->_rdb->fetchAll($sql,array('uid'=>$uid));
    }

    /**
     * select user not answer enquires count
     *
     * @param integer $uid
     * @return integer
     */
    public function getNotAnswerEnquireCount($uid)
    {
        $sql = "SELECT count(1) FROM linno_enquire_question AS q WHERE NOT EXISTS(SELECT * 
                FROM linno_enquire AS e WHERE e.qid=q.qqid AND e.actor=:uid) ";

        return $this->_rdb->fetchOne($sql,array('uid'=>$uid));
    }

    /**
     * select rand user not answer enquire
     *
     * @param integer $uid
     * @param integer $maxNum
     * @return array
     */
    public function getRandNotAnswerEnquire($uid, $maxNum)
    {        
        $sql = "SELECT * FROM linno_enquire_question AS q WHERE NOT EXISTS(SELECT * FROM linno_enquire AS e 
                WHERE e.qid=q.qqid AND e.actor=:uid) ORDER BY rand() LIMIT 0,$maxNum";

        return $this->_rdb->fetchAll($sql,array('uid'=>$uid));
    }

    /**
     * select popular enquires
     *
     * @param integer $pageIndex
     * @param integer $pageIndex
     * @return array
     */
    public function getPopularEnquire($pageIndex = 1, $pageSize = 5)
    {
        $start = ($pageIndex - 1) * $pageSize;
        
        $sql = "SELECT * FROM linno_enquire_question ORDER BY count DESC,update_time DESC LIMIT $start,$pageSize ";

        return $this->_rdb->fetchAll($sql);
    }

    /**
     * select popular enquires
     *
     * @param integer $pageIndex
     * @param integer $pageIndex
     * @return array
     */
    public function getAboutUserEnquire($uid, $network, $pageIndex = 1, $pageSize = 5)
    {
        $start = ($pageIndex - 1) * $pageSize;
        
        $sql = "SELECT q.* FROM linno_enquire AS e,linno_enquire_question AS q WHERE (actor IN 
                (SELECT fid FROM linno_friend WHERE uid=:uid) OR actor IN 
                (SELECT uid FROM linno_user WHERE network_id=:network AND uid<>:uid)) 
                AND e.qid=q.qqid ORDER BY count DESC,update_time DESC LIMIT $start,$pageSize";

        return $this->_rdb->fetchAll($sql,array('uid'=>$uid, 'network'=>$network));
    }
    
    /**
     * select answer info by enquire id
     *
     * @param integer $eid
     * @return array
     */
    public function getAnswerByEid($eid)
    {        
        $sql = "SELECT * FROM linno_enquire_answer WHERE qqid=:qqid";

        return $this->_rdb->fetchAll($sql,array('qqid'=>$eid));
    }
    
    
    
    
    
}
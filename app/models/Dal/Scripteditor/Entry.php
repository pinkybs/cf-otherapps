<?php

require_once 'Dal/Abstract.php';

class Dal_Scripteditor_Entry extends Dal_Abstract
{
    /**
     * user table name
     *
     * @var string
     */
    protected $table_entry = 'scripteditor_entry';
    
    protected static $_instance;
    
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        
        return self::$_instance;
    }
  
    /**
     * insert scripteditor entry
     *
     * @param array $entry
     * @return integer
     */
    public function insertEntry($entry)
    {
        $this->_wdb->insert($this->table_entry, $entry);
        return $this->_wdb->lastInsertId();
    }

    /**
     * update entry info
     *
     * @param integer $eid
     * @param array $info
     * @return void
     */
    public function updateEntry($eid, $info)
    {
        $where = $this->_wdb->quoteInto('eid = ?', $eid);
        $this->_wdb->update($this->table_entry, $info, $where);
    }

    /**
     * update entry follow count
     *
     * @param integer $eid
     * @param integer $count
     * @return void
     */
    public function updateFollowCount($eid, $count)
    {
        $sql = "UPDATE $this->table_entry SET follow_count=follow_count+:count WHERE eid=:eid ";
        $this->_wdb->query($sql,array('eid'=>$eid, 'count'=>$count));
    }
    
    /**
     * get entry info by id
     *
     * @param integer $eid
     * @return array
     */
    public function getEntryInfo($eid)
    {
        $sql = "SELECT e.*,u.nickname FROM $this->table_entry AS e,scripteditor_user AS u WHERE e.eid=:eid AND e.uid=u.uid ";

        return $this->_rdb->fetchRow($sql,array('eid'=>$eid));
    }

    /**
     * get follow entry list by entry id
     *
     * @param integer $eid
     * @return array
     */
    public function getFollowEntryList($eid)
    {
        $sql = "SELECT e.*,u.nickname 
                FROM $this->table_entry AS e 
                INNER JOIN scripteditor_user AS u 
                WHERE e.follow_id=:eid AND e.status=1 AND e.uid=u.uid ";

        return $this->_rdb->fetchAll($sql,array('eid'=>$eid));
    }
    
    /**
     * get user entry info by language type
     *
     * @param string $uid
     * @param integer $language
     * @param integer $pageIndex
     * @param integer $pageSize
     * @param integer $status
     * @return array
     */
    public function getUserEntry($uid, $language, $pageIndex = 1, $pageSize = 5, $status)
    {
        $start = ($pageIndex-1)*$pageSize;

        $sql = "SELECT e.*,u.nickname,l.language_name 
                FROM scripteditor_entry AS e 
                INNER JOIN scripteditor_language AS l 
                INNER JOIN scripteditor_user AS u 
                WHERE e.language=l.id AND e.uid=u.uid AND e.uid=:uid ";
        
        //the finished entry
        if ( $status == 1 ) {
            $sql .= " AND e.language=:language AND e.status=1 ";
            
            $array = array('uid'=>$uid, 'language'=>$language);
        }
        else {//saved entry and create_time is not more than 24 hours
            $sql .= " AND e.status=0 AND (unix_timestamp(now())-unix_timestamp(e.create_time))<86400 ";
            
            $array = array('uid'=>$uid);
        }
        
        $sql .= "ORDER BY e.create_time DESC LIMIT $start,$pageSize"; 

        return $this->_rdb->fetchAll($sql, $array);
    }

    /**
     * get entry count by language type
     *
     * @param string $uid
     * @param integer $language
     * @param integer $status
     * @return integer
     */
    public function getUserEntryCount($uid, $language, $status)
    {
        $sql = "SELECT count(1) FROM $this->table_entry WHERE uid=:uid ";

        //the finished entry
        if ( $status == 1 ) {
            $sql .= " AND language=:language AND status=1 ";
            
            $array = array('uid'=>$uid, 'language'=>$language);
        }
        else {//saved entry and create_time is not more than 24 hours
            $sql .= " AND status=0 AND (unix_timestamp(now())-unix_timestamp(create_time))<86400 ";
            
            $array = array('uid'=>$uid);
        }
        
        return $this->_rdb->fetchOne($sql, $array);
    }
    
    /**
     * get new entry info by language type
     *
     * @param integer $language
     * @param integer $pageIndex
     * @param integer $pageSize
     * @return array
     */
    public function getNewEntry($language, $pageIndex = 1, $pageSize = 5)
    {
        $start = ($pageIndex-1)*$pageSize;
        
        $sql = "SELECT e.*,u.nickname,l.language_name 
                FROM scripteditor_entry AS e 
                INNER JOIN scripteditor_language AS l 
                INNER JOIN scripteditor_user AS u 
                WHERE e.language=:language AND e.language=l.id AND e.uid=u.uid AND e.status=1 
                ORDER BY e.create_time DESC LIMIT $start,$pageSize";

        return $this->_rdb->fetchAll($sql,array('language'=>$language));
    }

    /**
     * get entry count by language type
     *
     * @param integer $language
     * @return integer
     */
    public function getEntryCount($language)
    {
        $sql = "SELECT count(1) FROM $this->table_entry WHERE language=:language AND status=1 ";

        return $this->_rdb->fetchOne($sql,array('language'=>$language));
    }

    /**
     * get the follow list by user id 
     *
     * @param string $uid
     * @return array
     */
    public function getFollowList($uid)
    {
        $sql = "SELECT e.uid,u.nickname,u.pic 
                FROM scripteditor_entry AS e,scripteditor_user AS u, 
                (SELECT eid FROM scripteditor_entry WHERE uid=:uid) AS b 
                WHERE e.follow_id = b.eid AND e.uid=u.uid AND e.uid!=:uid GROUP BY e.uid 
                ORDER BY e.create_time DESC ";

        return $this->_rdb->fetchAll($sql,array('uid'=>$uid));
    }

    /**
     * get one entry 
     *
     * @return array
     */
    public function getOneNewEntry()
    {
        $sql = "SELECT * FROM $this->table_entry ORDER BY create_time DESC LIMIT 0,1  ";

        return $this->_rdb->fetchRow($sql);
    }
    
    
}
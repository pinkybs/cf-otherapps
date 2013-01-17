<?php

require_once 'Dal/Abstract.php';

/**
 * Dal Afrac User
 * App Afrac user Data Access Layer
 *
 * @package    Dal/Afrac
 * @copyright  Copyright (c) 2009 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/08/05    Zhaoxh
 */
class Dal_Afrac_User extends Dal_Abstract
{
    /**
     * user table name
     *
     * @var string
     */
    protected $table_user = 'afrac_user';
    
    protected static $_instance;
    
    /**
     * get Dal_Afrac_User default
     *
     * @return Dal_Afrac_User
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
    public function insertUser($userInfo)
    {
        $this->_wdb->insert($this->table_user, $userInfo);
    }
    
    /**
     * update user score
     *
     * @param string $uid
     * @param string $score
     * @return void
     */
    public function updateScore($uid, $score)
    {
        $sql = "UPDATE $this->table_user SET score=:score WHERE uid=:uid AND score<:score";
        $this->_wdb->query($sql, array('uid'=>$uid, 'score'=>$score));
    }
    
    /**
     * check the user is join afrac
     *
     * @param string $uid
     * @return  boolean
     */
    public function isInAfrac($uid)
    {
        $sql = "SELECT COUNT(1) FROM $this->table_user WHERE uid=:uid";

        $result = $this->_rdb->fetchOne($sql,array('uid'=>$uid));

        return $result > 0;
    }
    
    /**
     * get the score of user
     *
     * @param string $uid
     * @return  integer
     */
    public function getScore($uid)
    {
        $sql = "SELECT score FROM $this->table_user WHERE uid=:uid";

        $result = $this->_rdb->fetchOne($sql,array('uid'=>$uid));

        return $result;
    }
    
    /**
     * get the rank and score of user
     *
     * @param string $uid
     * @param string $fids
     * @param string $type
     * @return  array
     */
    public function getRankScore($uid,$type=1,$fids='',$cache=0)
    {
        $sql1 = "SET @pos=0";
        $this->_rdb->query($sql1);
        if ($type == 1) {
        	if ($cache == 0) { //no cache 20091105 modify by zhaoxh
	            $sql = "SELECT b.rank,b.score FROM (SELECT @pos:=@pos+1 AS rank,score,uid FROM afrac_user ORDER BY score DESC) AS b WHERE uid=:uid";
	        }
	        else {//has cache 20091105 modify by zhaoxh
	            $sql = "SELECT id AS rank,uid,score FROM afrac_rank_tmp WHERE uid=:uid";
	        }
        }
    	else {
            $sql = "SELECT b.rank,b.score FROM (SELECT @pos:=@pos+1 AS rank,u.score,u.uid FROM 
            (SELECT uid,score FROM afrac_user WHERE uid IN ($fids)) AS u ORDER BY score DESC) AS b WHERE uid=:uid";
        }
        $result = $this->_rdb->fetchRow($sql,array('uid'=>$uid));

        return $result;
    }
    
    /**
     * get ranking user data
     *
     * @param  integer $pageStart
     * @param  inetger $pageSize
     * @param string $fids
     * @param string $type
     * @return array
     */
    public function getUserList($pageStart,$pageSize,$type=1,$fids='',$cache=0)
    {   
        $sql1 = "SET @pos=0";
        $this->_rdb->query($sql1);
        $pageStart -= 1;
        if ($type == 1) {
        	if ($cache == 0) {
            	$sql = "SELECT b.rank,b.score,uid FROM (SELECT @pos:=@pos+1 AS rank,score,uid FROM afrac_user ORDER BY score DESC) AS b LIMIT $pageStart,$pageSize";
        	}
        	else {  //20091105 modify by zhaoxh
        		$sql = "SELECT id AS rank,uid,score FROM afrac_rank_tmp LIMIT $pageStart,$pageSize";
        	}
        }
        else {
            $sql = "SELECT b.rank,b.score,uid FROM (SELECT @pos:=@pos+1 AS rank,u.score,u.uid FROM 
                    (SELECT uid,score FROM afrac_user WHERE uid IN ($fids)) AS u ORDER BY score DESC) 
                    AS b limit $pageStart,$pageSize";
        }
        $result = $this->_rdb->fetchAll($sql);

        return $result;
    }
    
    /**
     * count user
     *
     * @param string $type
     * @param string $fids
     * @return integer
     */
    public function countUser($type=1,$fids='')
    {
        if ($type == 1){
            $sql = "SELECT COUNT(1) FROM afrac_rank_tmp";
        }
        else {
            $sql = "SELECT COUNT(1) FROM afrac_user WHERE uid IN ($fids)";
        }
        return $this->_rdb->fetchOne($sql);
    }

    public function doBatch() 
    {	
        $sql = "TRUNCATE Table afrac_rank_tmp;";
        $this->_wdb->query($sql);

        $sql = "SET @pos=0;";

        $this->_wdb->query($sql);

        $sql = "INSERT INTO afrac_rank_tmp
         SELECT @pos:=@pos+1 AS id, uid, score FROM afrac_user  ORDER BY score DESC;";
        $this->_wdb->query($sql);
    }


}
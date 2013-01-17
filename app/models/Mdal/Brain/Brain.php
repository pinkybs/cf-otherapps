<?php

require_once 'Mdal/Abstract.php';

class Mdal_Brain_Brain extends Mdal_Abstract
{
    /**
     * user table name
     *
     * @var string
     */
    protected $table_user = 'brain_user';
    
    protected static $_instance;

    /**
     * Mdal_Brain_Brain
     *
     * @return Mdal_Brain_Brain
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }
    /**
     * get game info
     * @param integer $gid
     * @return array
     */
    public function getGameInfoById($gid)
    {
        $sql = "SELECT * FROM brain_description WHERE gid = :gid";

        return $this->_rdb->fetchRow($sql, array('gid' => $gid));
    }

    /**
     * get all game info
     *
     * @return array
     */
    public function getlistGameInfoById()
    {
        $sql = "SELECT * FROM brain_description";

        return $this->_rdb->fetchAll($sql);
    }
    /**
     * insert a score
     *
     * @param array $info
     * @return integer
     */
    public function insertGameScore($info)
    {
        $this->_wdb->insert('brain_score', $info);
        return $this->_wdb->lastInsertId();
    }
    
    /**
     * insert into user table
     *
     * @param array $info
     * @return integer
     */
    public function insertUser($info)
    {
        $this->_wdb->insert('brain_user', $info);
        return $this->_wdb->lastInsertId();
    }
    
    /**
     * in brain or not
     *
     * @param string $uid
     * @return boolean
     */
    public function isInBrain($uid)
    {
        $sql = "SELECT COUNT(1) FROM $this->table_user WHERE uid=:uid";

        $result = $this->_rdb->fetchOne($sql,array('uid'=>$uid));

        return $result == 0;
    }

    /**
     * update game score
     *
     * @param integer $uid
     * @param integer $gid
     * @param integer $newScore
     * @param integer $lastTime
     * @return integer
     */
    public function updateGameScore($uid, $gid, $newScore)
    {
        $sql = ' UPDATE brain_score SET score = :newScore WHERE gid = :gid AND uid = :uid';

        $arrInfo = array('uid' => $uid, 'gid' => $gid, 'newScore' =>$newScore);
        
        return $this->_wdb->query($sql, $arrInfo);
    }
    
    /**
     * update last oprate time
     *
     * @param string $uid
     * @param string $lastTime
     * @return pdo_obj
     */
    public function updateLastTime($uid,$lastTime)
    {
        $sql = ' UPDATE brain_user SET last_update_time = :lastTime WHERE uid = :uid ';

        $arrInfo = array('uid' => $uid, 'lastTime' => $lastTime);
        
        return $this->_wdb->query($sql, $arrInfo);
    }

    /**
     * get my game info
     *
     * @param integer $uid
     * @return array
     */
    public function getGameById($uid)
    {
        $sql = "SELECT d.gid, d.gname, u.uid, u.score FROM brain_description AS d,brain_score AS u
                 WHERE u.score > 0 AND u.gid = d.gid AND u.uid = :uid";

        return $this->_rdb->fetchAll($sql, array('uid' => $uid));
    }
    
    /**
     * get game description
     *
     * @param string $gid
     * @return array
     */
    public function getGameInfo($gid) {
         $sql = "SELECT gid,gname FROM brain_description WHERE gid = :gid";

        return $this->_rdb->fetchRow($sql, array('gid' => $gid));
    }
    
    /**
     * get my ranking (all)
     *
     * @param integer $uid
     * @param integer $gid
     * @return integer
     */
    //20091201 cached
    public function getRankById($uid, $gid)
    {
        //$sql = 'SELECT count(1) + 1 FROM brain_score
        //          WHERE score >(SELECT score FROM brain_score WHERE gid = :gid AND uid = :uid) AND gid = :gid';

        //return $this->_rdb->fetchOne($sql, array('gid' => $gid, 'uid' => $uid));
        
        $sql1 = "SET @pos=0;";
        $this->_rdb->query($sql1);

        $sql = "SELECT a.rank FROM (SELECT @pos:=@pos+1 AS rank,s.uid,s.score FROM brain_score AS s WHERE s.gid=:gid ORDER BY s.score DESC,s.id ASC) AS a WHERE a.uid =:uid;";
        

        $reuslt = $this->_rdb->fetchOne($sql, array('uid' => $uid,'gid' => $gid));

        return $reuslt;
    }
    
    /**
     * CNT total score rank  
     *
     * @param string $uid
     * @return integer
     */
    public function cntRankByIdTotal()
    {
        $sql = "SELECT COUNT(1) FROM brain_user";        
        return $this->_rdb->fetchOne($sql);
    }
    
    /**
     * CNT one game score rank
     *
     * @param string $uid
     * @param string $gid
     * @return string
     */
    public function cntRankById($gid)
    {
        $sql = 'SELECT count(1) FROM brain_score WHERE gid = :gid';

        return $this->_rdb->fetchOne($sql, array('gid' => $gid));
    }
    
    /**
     * get one score by uid and gid
     *
     * @param string $uid
     * @param string $gid
     * @return string
     */
    public function getScore($uid, $gid)
    {
        $sql = "SELECT id,score FROM brain_score
                WHERE gid=:gid AND uid=:uid";

        return $this->_rdb->fetchRow($sql, array('gid' => $gid, 'uid' => $uid));
    }
    
    /**
     * get my ranking (Friend)
     *
     * @param integer $uid
     * @param integer $gid
     * @param string $gid
     * @return integer
     */
    public function getFriendGameRankById($uid, $gid, $fids)
    {
    	$sqlll = "SELECT score FROM brain_score WHERE gid=:gid AND uid=:uid";
    	$re = $this->_rdb->fetchOne($sqlll, array('gid' => $gid, 'uid' => $uid));
        if ($re === false) {
            return 0;
        }
    	
        $fids = $this->_rdb->quote($fids);
        
    	$sql1 = "SET @pos=0;";
        $this->_rdb->query($sql1);

        $sql = "SELECT a.rank FROM (SELECT @pos:=@pos+1 AS rank,s.uid,s.score FROM brain_score AS s WHERE s.gid=:gid AND s.uid IN ($fids,:uid) ORDER BY s.score DESC,s.id ASC) AS a WHERE a.uid =:uid;";
        

        $reuslt = $this->_rdb->fetchOne($sql, array('uid' => $uid,'gid' => $gid));

        return $reuslt;
        /*
        $sql = " SELECT count(1) + 1 FROM brain_score
                 WHERE score >(SELECT score FROM brain_score WHERE gid = :gid AND uid = :uid)
                 AND gid = :gid AND uid IN ($fids)";

        return $this->_rdb->fetchOne($sql, array('gid' => $gid, 'uid' => $uid));
        */
    }
    
    /**
     * CNT one game friend rank 
     *
     * @param string $uid
     * @param string $gid
     * @param string $fids
     * @return string
     */
    public function cntFriendRankById($gid, $fids)
    {
    	$fids = $this->_rdb->quote($fids);

        $sql = " SELECT count(1) FROM brain_score WHERE gid = :gid AND uid IN ($fids)";

        return $this->_rdb->fetchOne($sql, array('gid' => $gid));
    }
    
    /**
     * get a array contains AT MOST 5 PEOPLE`S uid gid score...one game
     *
     * @param string $gid
     * @param string $start
     * @param integer $size
     * @param string $pos
     * @param array $fids
     * @return array
     */
    public function getUserInfo($gid,$start,$size,$fids=null)
    {   
        if ($fids == null) {
            $sql = "SELECT uid,gid,score FROM brain_score WHERE gid=:gid ORDER BY score DESC,id ASC LIMIT $start,$size";
        }
        else {
    	    $fids = $this->_rdb->quote($fids);
            $sql = "SELECT uid,gid,score FROM brain_score WHERE uid IN ($fids) AND gid=:gid ORDER BY score DESC,id ASC LIMIT $start,$size";
        }
        return $this->_rdb->fetchAll($sql,array('gid' => $gid));
    }
    
    /**
     * total score Array contains AT MOST 5 PEOPLE`S uid gid score...
     *
     * @param string $start
     * @param integer $size
     * @param string $pos
     * @param array $fids
     * @return array
     */
    //20091201 cached
    public function getUserInfoTotal($start,$size,$fids)
    {   
        if ($fids == null) {
            $sql = "SELECT uid,total_score AS totalScore FROM brain_user ORDER BY totalScore DESC,id ASC LIMIT $start,$size";
        }
        else {
    	    $fids = $this->_rdb->quote($fids);
            $sql = "SELECT uid,total_score AS totalScore FROM brain_user where uid IN ($fids) ORDER BY totalScore DESC,id ASC LIMIT $start,$size";
        }
        return $this->_rdb->fetchAll($sql);
    }
    
    /**
     * get User count By gid
     *
     * @param integer $gid
     * @return integer
     */
    public function getAllGameCountByGid($gid)
    {
        $sql = "SELECT count(1) FROM brain_score WHERE gid = :gid";

        return $this->_rdb->fetchOne($sql, array('gid' => $gid));
    }
    
    /**
     * get friend count
     *
     * @param string $fids
     * @param integer $gid
     * @return integer
     */
    public function getFriendGameCount($fids, $gid)
    {
    	$fids = $this->_rdb->quote($fids);
    	$sql = "SELECT COUNT(1) + 1 FROM brain_score WHERE gid = :gid AND uid IN ($fids)";
    	return $this->_rdb->fetchOne($sql, array('gid' => $gid));
    }

    public function isExistsGameScore($gid, $uid)
    {
        $sql = "SELECT COUNT(1) FROM brain_score WHERE uid = :uid AND gid = :gid";

        $result = $this->_rdb->fetchOne($sql, array('uid' => $uid, 'gid' => $gid));

        return $result>0;
    }

    /**
     * get totalscore
     *
     * @param integer $uid
     * @param integer $specialGameId
     * @return integer
     */
    public function getTotalScoreById($uid)
    {
    	$sql = "SELECT total_score FROM brain_user WHERE uid=:uid";

        return $this->_rdb->fetchOne($sql, array('uid' => $uid));
    }
    
    /**
     * get total score rank by uid
     *
     * @param integer $uid
     * 
     * @return integer
     */
    //20091201 cached
    public function getTotalScoreRank($uid)
    {
        $sql1 = "SET @pos=0;";
        $this->_rdb->query($sql1);
        
        $sql = "SELECT r.rank FROM
               (SELECT @pos:=@pos+1 AS rank,uid FROM brain_user ORDER BY total_score DESC,id ASC)
                AS r WHERE r.uid = :uid";
        return $this->_rdb->fetchOne($sql, array('uid' => $uid));
    }
 
	public function updateTotalScore($uid, $totalScoreAdd)
    {
        /*
        $sql = ' UPDATE brain_user SET total_score = total_score + :totalScoreAdd WHERE uid = :uid';

        $arrInfo = array('totalScoreAdd' =>$totalScoreAdd,'uid' => $uid);*/
        
        $sql = "SELECT SUM(score) FROM (SELECT score FROM brain_score WHERE uid=:uid) as a";
        $score = $this->_rdb->fetchOne($sql, array('uid'=>$uid));
        
        $sql = "UPDATE brain_user SET total_score = $score WHERE uid=:uid";
        return $this->_wdb->query($sql, array('uid'=>$uid));
    }

    /**
     * get total score in friend
     *
     * @param array $fids
     * @param integer $uid
     * @param integer $specialGameId
     * @return integer
     */
    public function getTotalScoreFriendRank($fids, $uid)
    {
    	$sql1 = "SET @pos=0";
        $this->_rdb->query($sql1);
        $fids = $this->_rdb->quote($fids);

        $sql = "SELECT r.rank FROM (
	                SELECT @pos:=@pos+1 AS rank,uid FROM brain_user 
	                WHERE uid IN ($fids, :uid) ORDER BY total_score DESC,id ASC)
                AS r WHERE r.uid = :uid";

        $reuslt = $this->_rdb->fetchOne($sql, array('uid' => $uid));

        return $reuslt;
    }

    /**
     * get total rank friend count
     * @param array $fids
     * @param integer $uid
     * @param integer $specialGameId
     * @return integer
     */
    public function getTotalScoreFriendCountById($fids, $uid, $specialGameId)
    {
    	$fids = $this->_rdb->quote($fids);
    	
    	$sql = " SELECT COUNT(1) FROM brain_user WHERE uid IN ($fids, :uid)";

        return $this->_rdb->fetchOne($sql, array('uid' => $uid));
    }

    public function getAvgScoreById($fids, $uid, $gid)
    {
    	$fids = $this->_rdb->quote($fids);

    	$sql = "SELECT AVG(score) FROM brain_score WHERE score > 0 AND gid = :gid AND uid IN ($fids, :uid)";

    	return $this->_rdb->fetchOne($sql, array('uid' => $uid, 'gid' => $gid));
    }

    public function getGameAndUserInfoById($uid, $gid)
    {
    	$sql = " SELECT d.gid, d.gname, u.uid, u.score FROM brain_description AS d,brain_score AS u
    	         WHERE u.gid = d.gid AND u.gid = :gid AND u.uid = :uid";

    	return $this->_rdb->fetchRow($sql, array('uid' => $uid, 'gid' => $gid));
    }
    
    public function insertInvite($uid,$fid)
    {
    	$info = array('uid' => $uid,'fid' => $fid);
    	$this->_wdb->insert('brain_invite', $info);
        return $this->_wdb->lastInsertId();
    }
    
    /**
     * set all inviters` bsyou_on to 1
     *
     * @param string $uid
     * @return pdo_obj?
     */
	public function setBsyouOn($uid)
    {
    	//$sql = "UPDATE brain_user SET bsyou_on=1 WHERE uid IN (SELECT uid FROM brain_invite WHERE fid=:uid) AND bsyou_on=0";
        $sql = "UPDATE brain_user SET bsyou_on=1 WHERE exists (SELECT uid FROM brain_invite WHERE fid=:uid AND uid=brain_user.uid) AND bsyou_on=0";
    	return $this->_wdb->query($sql, array('uid' => $uid));
    }
    
	public function getBsyouResult($uid)
    {
    	$sql = "SELECT bsyou_on FROM brain_user WHERE uid=:uid";

    	return $this->_rdb->fetchOne($sql, array('uid' => $uid));
    }
    
    
    //set bsyou_on from 1 to 2
    public function bsyouOneTwo($uid)
    {
    	$sql = "UPDATE brain_user SET bsyou_on=2 WHERE bsyou_on=1 AND uid=:uid";

    	return $this->_wdb->query($sql, array('uid' => $uid));
    }
    
    public function getInviteUser($uid)
    {
        $sql = "SELECT fid FROM brain_invite AS b WHERE b.uid=:uid";
        return $this->_rdb->fetchAll($sql, array('uid' => $uid));
    }
    
    public function checkBsyou($fid)
    {
        $sql = "SELECT count(1) from brain_user AS a WHERE uid IN ($fid) AND total_score>0";
        $result = $this->_rdb->fetchOne($sql);
        
        return $result > 0;
    }
    
    public function updateUserBsyou($uid)
    {
        $sql = "UPDATE brain_user SET bsyou_on=1 WHERE bsyou_on=0 AND uid=:uid";

    	$this->_wdb->query($sql, array('uid' => $uid));
    }    
}
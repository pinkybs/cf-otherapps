<?php

require_once 'Dal/Abstract.php';

/**
 * Johnson Dal
 * @package    Dal/Johnson
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/10/22    lp
 */
class Dal_Johnson_Johnson extends Dal_Abstract
{
    protected static $_instance;

    public static function getDefaultInstance()
    {
    	if (self::$_instance == null) {
    		self::$_instance = new self();
    	}

    	return self::$_instance;
    }

    /**
     * firest join game, insert user
     *@param array $insertInfo
     */
    public function insertUser($insertInfo)
    {
    	$this->_wdb->insert('johnson_user', $insertInfo);
    }

    /**
     * check user is in app
     *
     * @param string $uid
     * @return string
     */
    public function isInApp($uid)
    {
    	$sql = "SELECT uid FROM johnson_user WHERE uid=:uid";

    	return $this->_rdb->fetchOne($sql, array('uid' => $uid));
    }

    /**
     * get user last last login time
     *
     * @param string $uid
     * @return string
     */
    public function getLastLoginTime($uid)
    {
        $sql = "SELECT last_login_time FROM johnson_user WHERE uid=:uid";

        return $this->_rdb->fetchOne($sql,array('uid'=>$uid));
    }

    /**
     * update user infomation
     *
     * @param string $uid
     * @param array $updateInfo
     */
    public function updateUserInfo($uid, $updateInfo)
    {
    	$where = $this->_wdb->quoteInto('uid = ?', $uid);
    	$this->_wdb->update('johnson_user', $updateInfo, $where);
    }

    /**
     * ger ids invited by user
     *
     * @param string $uid
     * @param string $appId
     * @return array
     */
    public function getUserInviteUser($uid)
    {
    	$sql = "SELECT target FROM johnson_invite WHERE actor = :actor";

    	return $this->_wdb->fetchAll($sql, array('actor' => $uid));
    }

    /**
     * ger user infomation
     *
     * @param string $uid
     * @return array
     */
    public function getUser($uid)
    {
    	$sql = "SELECT * FROM johnson_user WHERE uid = :uid";

    	return $this->_rdb->fetchRow($sql, array('uid' => $uid));
    }

    /**
     * ger user all item
     *
     * @param string $uid
     * @return array
     */
    public function getUserItem($uid)
    {
    	$sql = "SELECT * FROM johnson_user_item WHERE uid = :uid";

    	return $this->_rdb->fetchAll($sql, array('uid' => $uid));
    }

    /**
     * delete user item
     *
     * @param string $uid
     * @param array $itemIdArray
     */
    public function deleteUserItem($uid, $itemIdArray = null)
    {
    	$sql = "DELETE FROM johnson_user_item WHERE uid = :uid ";

    	if ($itemIdArray != null) {
    		$itemIdString = $this->_rdb->quote($itemIdArray);
    		$sql = $sql . " AND item_id NOT IN($itemIdString)";
    	}

    	$this->_wdb->query($sql, array('uid' => $uid));
    }

    /**
     * get max score user
     *
     * @param array $friendIdArray
     * @return array
     */
    public function getMaxScoreUidInFriend($friendIdArray)
    {
    	$friendIdString = $this->_rdb->quote($friendIdArray);

    	$sql = "SELECT uid,score FROM johnson_user WHERE uid IN($friendIdString) ORDER BY score DESC LIMIT 1";

    	return $this->_rdb->fetchRow($sql);
    }

    /**
     * get rank user count
     *
     * @param string $fids
     * @param integer $rankType
     * @return integer
     */
    public function getRankUserCount($fids, $rankType)
    {
    	if ($rankType == 1){
            //$sql = "SELECT COUNT(1) FROM johnson_rank_tmp";
            $sql = "SELECT COUNT(1) FROM johnson_user";
        }
        else {
        	$fids = $this->_rdb->quote($fids);
            $sql = "SELECT COUNT(1) FROM johnson_user WHERE uid IN ($fids)";
        }

        return $this->_rdb->fetchOne($sql);
    }

    /**
     * get the rank and score of user
     *
     * @param string $uid
     * @param string $fids
     * @param string $type
     * @return  array
     */
    public function getRankScore($uid, $type = 1 , $fids = null)
    {
        $sql1 = "SET @pos=0";

        $this->_rdb->query($sql1);

        if ($type == 1) {
            $sql = "SELECT b.rank,b.score,honor_id FROM (SELECT @pos:=@pos+1 AS rank,score,uid,honor_id FROM johnson_user ORDER BY score DESC) AS b WHERE uid=:uid";
            //$sql = "SELECT *,id AS rank FROM johnson_rank_tmp WHERE uid=:uid";
        }
        else {
        	$fids = $this->_rdb->quote($fids);
            $sql = "SELECT b.rank,b.score, honor_id FROM (SELECT @pos:=@pos+1 AS rank,score,uid,honor_id FROM johnson_user WHERE uid IN($fids) ORDER BY score DESC) AS b WHERE uid=:uid";
        }

        return $this->_rdb->fetchRow($sql, array('uid' => $uid));

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
    public function getUserList($pageStart, $pageSize, $type = 1, $fids = null)
    {
        $sql1 = "SET @pos=0";

        $this->_rdb->query($sql1);

        $pageStart -= 1;
        //$pageEnd = $pageStart + 5;

        if ($type == 1) {
            $sql = "SELECT b.rank,b.score,uid,honor_id FROM (SELECT @pos:=@pos+1 AS rank,score,uid,honor_id FROM johnson_user ORDER BY score DESC) AS b
                    LIMIT $pageStart,$pageSize";
            //$sql = "SELECT *,id AS rank FROM johnson_rank_tmp WHERE id > $pageStart AND id <= $pageEnd";
        }
        else {
        	$fids = $this->_rdb->quote($fids);
            $sql = "SELECT b.rank,b.score,uid, honor_id FROM (SELECT @pos:=@pos+1 AS rank,score,uid,honor_id FROM johnson_user WHERE uid IN($fids) ORDER BY score DESC) AS b
                    LIMIT $pageStart,$pageSize";
        }

        return $this->_rdb->fetchAll($sql);
    }

    /**
     * insert invite table
     * @param  string $actor
     * @param  string $target
     *
     */
    public function insertInvite($actor, $target)
    {
    	$now = time();

    	$sql = "INSERT INTO johnson_invite (actor, target, create_time) VALUES (:actor, :target, $now) ON DUPLICATE KEY UPDATE create_time = $now";

    	$this->_wdb->query($sql, array('actor' => $actor, 'target' => $target));
    }

    public function doBatchUpdateRankTemTable()
    {

        $sql = "TRUNCATE Table johnson_rank_tmp_1;";

        $this->_wdb->query($sql);

        $sql = "SET @pos=0;";
        $this->_wdb->query($sql);

        $sql = "INSERT INTO johnson_rank_tmp_1
                SELECT @pos:=@pos+1 AS id,uid,score,honor_id FROM johnson_user ORDER BY score DESC;";

        $this->_wdb->query($sql);

    }

    public function isTableEmpty()
    {
        $sql = "SELECT COUNT(1) FROM johnson_rank_tmp_1";
        return $this->_rdb->fetchOne($sql);
    }

    public function insertNewRankTable()
    {
        $sql = "TRUNCATE Table johnson_rank_tmp;";
        $this->_wdb->query($sql);

        $sql = "INSERT INTO johnson_rank_tmp
                SELECT id, uid, score, honor_id FROM johnson_rank_tmp_1";

        $this->_wdb->query($sql);

    }
}
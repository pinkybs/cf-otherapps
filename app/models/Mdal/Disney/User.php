<?php

require_once 'Mdal/Abstract.php';

/**
 * Mdal Disney User
 * MixiApp Disney User Data Access Layer
 *
 * @package    Mdal/Disney
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/10/12    Liz
 */
class Mdal_Disney_User extends Mdal_Abstract
{
    /**
     * table name
     *
     * @var string
     */
    protected $table_user = 'disney_user';

    /**
     * table name
     *
     * @var string
     */
    protected $table_user_award = 'disney_user_award';
    
    /**
     * table name
     *
     * @var string
     */
    protected $table_user_cup = 'disney_user_cup';
    
    protected static $_instance;

    /**
     * getDefaultInstance
     *
     * @return Mdal_Disney_User
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * insert disney user
     *
     * @param array $user
     * @return integer
     */
    public function insertUser($user)
    {
        $this->_wdb->insert($this->table_user, $user);
        return $this->_wdb->lastInsertId();
    }

    /**
     * insert into my mixi
     *
     * @param array $info
     * @return integer
     */
    public function insertMymixi($info)
    {
        $this->_wdb->insert('disney_mymixi', $info);
        return $this->_wdb->lastInsertId();
    }
    
    /**
     * insert disney trade apply
     *
     * @param array $tradeInfo
     * @return integer
     */
    public function insertTradeApply($tradeInfo)
    {
        $this->_wdb->insert('disney_trade', $tradeInfo);
        return $this->_wdb->lastInsertId();
    }
    
    /**
     * delete user award
     *
     * @param integer $uid
     * @param integer $pid
     * @return void
     */
    public function deleteUserAward($uid, $pid)
    {
        $sql = "UPDATE $this->table_user_award SET count=count-1 WHERE uid=:uid AND pid=:pid ";
        $this->_wdb->query($sql, array('uid'=>$uid, 'pid'=>$pid));
    }

    /**
     * delete trade apply
     *
     * @param integer $nid
     * @return void
     */
    public function deleteTradeApply($nid)
    {
        $where = $this->_wdb->quoteinto('nid = ?', $nid);
        $this->_wdb->delete('disney_trade', $where);
    }
    
    /**
     * add user award
     *
     * @param array $award
     * @return viod
     */
    public function addUserAward($uid, $pid)
    {
        $sql = "SELECT id,count FROM $this->table_user_award WHERE uid=:uid AND pid=:pid ";
        $result = $this->_wdb->fetchRow($sql, array('uid'=>$uid, 'pid'=>$pid));
        
        $time = time();
        if ( !$result ) {
            $award = array('uid' => $uid, 'pid' => $pid, 'count' => 1, 'create_time' => $time);
            $this->_wdb->insert($this->table_user_award, $award);
        }
        else if ( $result['count'] < 3 ) {
            $sql = "UPDATE $this->table_user_award SET count=count+1, create_time=$time WHERE uid=:uid AND pid=:pid ";
            $this->_wdb->query($sql, array('uid'=>$uid, 'pid'=>$pid));
        }
    }

    /**
     * add user game ticket
     *
     * @param integer $uid
     * @param integer $ticketCount
     * @return viod
     */
    public function updateUserGameTicket($uid, $ticketCount)
    {
        $sql = "UPDATE $this->table_user SET game_ticket = game_ticket + :ticketCount WHERE uid=:uid ";
        $this->_wdb->query($sql, array('uid'=>$uid, 'ticketCount'=>$ticketCount));
    }
    
    /**
     * update user info
     *
     * @param integer $uid
     * @param array $info
     * @return integer
     */
    public function updateUser($uid, $info)
    {
        $where = $this->_wdb->quoteInto('uid = ?', $uid);
        return $this->_wdb->update($this->table_user, $info, $where);
    }
    
    /**
     * update user game point
     *
     * @param integer $uid
     * @param integer $point
     * @return void
     */
    public function updateUserPoint($uid, $point)
    {
        $sql = "UPDATE $this->table_user SET game_point=game_point + :point WHERE uid=:uid ";
        $this->_wdb->query($sql, array('uid'=>$uid, 'point'=>$point));
    }

    /**
     * update user flash distance
     *
     * @param integer $uid
     * @param integer $distance
     * @return void
     */
    public function updateUserFlashDistance($uid, $distance)
    {
        $sql = "UPDATE $this->table_user SET flash_distance = flash_distance + :distance WHERE uid=:uid ";
        $this->_wdb->query($sql, array('uid'=>$uid, 'distance'=>$distance));
    }
    
    /**
     * is in app
     *
     * @param integer $uid
     * @return boolean
     */
    public function isInApp($uid)
    {
        $sql = "SELECT COUNT(1) FROM $this->table_user WHERE uid=:uid ";

        $result = $this->_wdb->fetchOne($sql, array('uid'=>$uid));
        
        return $result>0 ? true : false;
    }
    
    /**
     * get user app info
     *
     * @param integer $uid
     * @return array
     */
    public function getUser($uid)
    {
        $sql = "SELECT u.*,c.aid,c.name AS current_name,t.name AS target_name,c.award_name AS c_award_name, 
                c.award_icon AS c_award_icon,t.award_name AS t_award_name,t.award_icon AS t_award_icon,t.mixi_name AS t_mixi_name 
                FROM $this->table_user AS u 
                LEFT JOIN disney_place AS c ON u.current_place=c.pid 
                LEFT JOIN disney_place AS t ON u.target_place=t.pid 
                WHERE u.uid=:uid ";

        return $this->_wdb->fetchRow($sql, array('uid'=>$uid));
    }
    
    /**
     * get user trade count
     *
     * @param integer $uid
     * @return integr
     */
    public function getUserTradeCount($uid)
    {
        $sql = "SELECT today_trade_times FROM $this->table_user WHERE uid=:uid ";

        return $this->_wdb->fetchOne($sql, array('uid'=>$uid));
    }
    
    /**
     * get user app friend id
     *
     * @param array $fids
     * @param integer $pageIndex
     * @param integer $pageSize
     * @return array
     */
    public function getAppFids($fids, $pageIndex = null, $pageSize = null)
    {
        $fids = $this->_rdb->quote($fids);
        
        $sql = "SELECT uid,0 AS c FROM $this->table_user WHERE uid IN ($fids) AND current_place>0 AND target_place>0 ";
        
        if ( $pageIndex && $pageSize ) {
            $start = ($pageIndex - 1) * $pageSize;
            $sql .= " LIMIT $start,$pageSize ";
        }

        return $this->_rdb->fetchAll($sql);
    }
    
    /**
     * get user app friend count
     *
     * @param array $fids
     * @return integer
     */
    public function getAppFidCount($fids)
    {
        $fids = $this->_rdb->quote($fids);
        
        $sql = "SELECT COUNT(1) FROM $this->table_user WHERE uid IN ($fids) AND current_place>0 AND target_place>0 ";
        
        return $this->_rdb->fetchOne($sql);
    }

    /**
     * get user award count by place id
     *
     * @param integer $uid
     * @param integer $pid
     * @return integer
     */
    public function getUserAwardCount($uid, $pid)
    {        
        $sql = "SELECT count FROM $this->table_user_award WHERE uid=:uid AND pid=:pid ";
        
        return $this->_wdb->fetchOne($sql, array('uid'=>$uid, 'pid'=>$pid));
    }

    /**
     * get user cup list
     *
     * @param integer $uid
     * @param integer $pageIndex
     * @param integer $pageSize
     * @return array
     */
    public function getUserCupList($uid, $pageIndex, $pageSize)
    {        
        $start = ($pageIndex - 1) * $pageSize;
        
        $sql = "SELECT u.*,c.name,c.icon,c.point FROM $this->table_user_cup AS u,disney_cup AS c 
                WHERE u.uid=:uid AND u.cid=c.cid ORDER BY u.create_time DESC LIMIT $start, $pageSize";
        
        return $this->_rdb->fetchAll($sql, array('uid'=>$uid));
    }

    /**
     * get user cup count
     *
     * @param integer $uid
     * @return integer
     */
    public function getUserCupCount($uid)
    {        
        $sql = "SELECT COUNT(1) FROM $this->table_user_cup WHERE uid=:uid ";
        
        return $this->_rdb->fetchOne($sql, array('uid'=>$uid));
    }

    /**
     * get cup info by cup id
     *
     * @param integer $cid
     * @return array
     */
    public function getCupByCid($cid)
    {
        $sql = "SELECT * FROM disney_cup WHERE cid=:cid ";
        
        return $this->_rdb->fetchRow($sql, array('cid'=>$cid));
    }

    /**
     * get game point ranking list 
     *
     * @param integer $uid
     * @param array $fids
     * @param integer $pageIndex
     * @param integer $pageSize
     * @return array
     */
    public function getRankingList($isFriendRank, $uid = null, $fids = null, $pageIndex = 1, $pageSize = 5)
    {
        $start = ($pageIndex - 1) * $pageSize;
        
        if ( $isFriendRank == 1 ) {
            $fids = $this->_rdb->quote($fids);
            $where = " uid IN ($fids, :uid) AND ";
            $array = array('uid'=>$uid);
        }
        else {
            $where = "";
            $array = array();
        }
        
        $sql = "SELECT * FROM disney_user WHERE " . $where . " current_place>0 AND target_place>0 
                ORDER BY game_point DESC,create_time ASC LIMIT $start,$pageSize";
        
        return $this->_rdb->fetchAll($sql, $array);
    }

    /**
     * get user game point ranking number
     *
     * @param integer $uid
     * @param array $fids
     * @return array
     */
    public function getUserRankNm($uid, $fids)
    {
        $sql1 = "SET @pos=0";
        $this->_rdb->query($sql1);
    
        if ( $fids ) {
            $fids = $this->_rdb->quote($fids);
            $where = " uid IN ($fids, :uid) AND ";
        }
        else {
            $where = " uid IN (:uid) AND ";
        }
        
        $sql = "SELECT * FROM (SELECT @pos:=@pos+1 AS rank,uid,game_point 
                FROM disney_user WHERE " . $where . " current_place>0 AND target_place>0 
                ORDER BY game_point DESC,create_time ASC ) AS r WHERE r.uid=:uid ";
        
        return $this->_rdb->fetchRow($sql, array('uid'=>$uid));
    }
    
    /**
     * get user all award count
     *
     * @param integer $uid
     * @return integer
     */
    public function getUserAllAwardCount($uid)
    {
        $sql = "SELECT sum(count) FROM disney_user_award WHERE uid=:uid ";
        
        return $this->_rdb->fetchOne($sql, array('uid'=>$uid));
    }
    
    /**
     * get trade apply info
     *
     * @param integer $uid
     * @param integer $fid
     * @param integer $userPid
     * @param integer $friendPid
     * @return array
     */
    public function getTradeApply($uid, $fid, $userPid, $friendPid)
    {
        $sql = "SELECT * FROM disney_trade WHERE uid=:uid AND fid=:fid AND user_pid=:userPid AND friend_pid=:friendPid ";

        return $this->_wdb->fetchRow($sql, array('uid'=>$uid, 'fid'=>$fid, 'userPid'=>$userPid, 'friendPid'=>$friendPid));
    }

    /**
     * get trade apply info by friend uid 
     *
     * @param integer $fid
     * @return array
     */
    public function getTradeApplyByFid($fid)
    {
        $sql = "SELECT * FROM disney_trade WHERE fid=:fid 
        		UNION
        		SELECT * FROM disney_trade WHERE uid=:uid ";

        return $this->_wdb->fetchAll($sql, array('fid'=>$fid, 'uid'=>$fid));
    }

    /**
     * get trade apply info by uid and user pid
     *
     * @param integer $uid
     * @param integer $userPid
     * @return array
     */
    public function getTradeApplyByUidAndUserPid($uid, $userPid)
    {
        $sql = "SELECT * FROM disney_trade WHERE uid=:uid AND user_pid=:userPid ";

        return $this->_wdb->fetchAll($sql, array('uid'=>$uid, 'userPid'=>$userPid));
    }
    
    /**
     * get trade apply info by friend uid and friend pid
     *
     * @param integer $fid
     * @param integer $friendPid
     * @return array
     */
    public function getTradeApplyByFidAndFriendPid($fid, $friendPid)
    {
        $sql = "SELECT * FROM disney_trade WHERE fid=:fid AND friend_pid=:friendPid ";

        return $this->_wdb->fetchAll($sql, array('fid'=>$fid, 'friendPid'=>$friendPid));
    }
    
    /**
     * get trade apply info by nid
     *
     * @param integer $nid
     * @return array
     */
    public function getTradeApplyByNid($nid)
    {
        $sql = "SELECT * FROM disney_trade WHERE nid=:nid ";

        return $this->_wdb->fetchRow($sql, array('nid'=>$nid));
    }
    
    /**
     * get user award count by area id
     *
     * @param integer $uid
     * @param integer $aid
     * @return integer
     */
    public function getUserAwardCountByAid($uid, $aid)
    {        
        $sql = "SELECT COUNT(1) FROM disney_user_award AS a,disney_place AS p 
                WHERE a.pid=p.pid AND a.uid=:uid AND a.count>0 AND p.aid=:aid ";
        
        return $this->_wdb->fetchOne($sql, array('uid'=>$uid, 'aid'=>$aid));
    }

    /**
     * get user award count all area
     *
     * @param integer $uid
     * @return integer
     */
    public function getUserAwardCountAllArea($uid)
    {        
        $sql = "SELECT COUNT(1) FROM disney_user_award WHERE uid=:uid AND count>0 ";
        
        return $this->_wdb->fetchOne($sql, array('uid'=>$uid));
    }
    
    /**
     * update disney member
     *
     * @param integer $uid
     */
    public function updateDisneyMember($uid)
    {
        $sql = "UPDATE disney_user SET disney_member=2 WHERE uid=:uid";
        
        $this->_wdb->query($sql, array('uid'=>$uid));
    }
    
    /**
     * update my mixi
     *
     * @param string $fids
     */
    public function updateMymixi($fids)
    {
    	if (empty($fids)) {
    		return;
    	}
    	
        $fids = $this->_rdb->quote($fids);
        $sql = "UPDATE disney_mymixi SET mymixi=1 WHERE mymixi=0 AND uid IN ($fids)";
        
        $this->_wdb->query($sql);
    }
    
    /**
     * update mymixi has read
     *
     * @param integer $uid
     */
    public function updateMymixiHasRead($uid)
    {
        $sql = "UPDATE disney_mymixi SET mymixi=0 WHERE uid=:uid";
        
        $this->_wdb->query($sql, array('uid'=>$uid));
    }
    
    /**
     * get my mixi
     *
     * @param integer $uid
     * @return boolean
     */
    public function getMymixi($uid)
    {
        $sql = "SELECT mymixi FROM disney_mymixi WHERE uid=:uid";
        return $this->_rdb->fetchOne($sql, array('uid'=>$uid));
    }
    
    /**
     * getAwardCountByPid
     *
     * @param array $user
     * @param integer $pid
     * @return array
     */
    public function getAwardCountByPid($user, $pid)
    {
    	if (empty($user)) {
    		return array();
    	}
    	
        $uids = $this->_rdb->quote($user);
        $sql = "SELECT uid,`count` AS c FROM disney_user_award WHERE uid IN ($uids) AND pid=:pid";
        return $this->_wdb->fetchAll($sql, array('pid'=>$pid));
    }
    
    /**
     * delete user 
     *
     * @param string $table
     * @param string $name
     * @param string $value
     * @return integer  effect row count
     */
    public function deleteUser($table, $name, $value) 
    {
        $where = $this->_wdb->quoteInto("$name = ?", $value);
        return $this->_wdb->delete($table, $where);
    }
    
    /**
     * check invite success
     *
     * @param integer $uid
     * @param integer $invite_uid
     * @return boolean
     */
    public function checkInviteSuccess($uid, $invite_uid)
    {
        $sql = "SELECT COUNT(1) FROM disney_invite_success WHERE uid=:uid AND fid=:fid";
        $result = $this->_rdb->fetchOne($sql, array('uid'=>$uid, 'fid'=>$invite_uid));
        return $result > 0 ? true : false;
    }
    
    /**
     * insert into invite success
     *
     * @param array $invite
     */
    public function insertInviteSuccess($invite)
    {
        $this->_wdb->insert('disney_invite_success', $invite);
    }
}
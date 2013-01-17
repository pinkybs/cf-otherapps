<?php

require_once 'Dal/Abstract.php';

/**
 * Dal Dynamite User
 * MixiApp Dynamite User Data Access Layer
 *
 * @package    Dal/Dynamite
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/07/06    Liz
 */
class Dal_Dynamite_User extends Dal_Abstract
{
    /**
     * user table name
     *
     * @var string
     */
    protected $table_user_basic = 'dynamite_user_basic';
    protected $table_user_more = 'dynamite_user_more';

    protected static $_instance;

    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * insert dynamite user
     *
     * @param array $user
     * @return integer
     */
    public function insertUserMore($user)
    {
        $this->_wdb->insert($this->table_user_more, $user);
        return $this->_wdb->lastInsertId();
    }

    /**
     * insert dynamite user basic infomation
     *
     * @param array $user
     * @return integer
     */
    public function insertUserBasic($user)
    {
        $this->_wdb->insert($this->table_user_basic, $user);
        return $this->_wdb->lastInsertId();
    }

    /**
     * update user basic info
     *
     * @param integer $uid
     * @param array $info
     * @return void
     */
    public function updateUserBasicInfo($uid, $info)
    {
        $where = $this->_wdb->quoteInto('uid = ?', $uid);
        return $this->_wdb->update($this->table_user_basic, $info, $where);
    }

    /**
     * update user more info
     *
     * @param integer $uid
     * @param array $info
     * @return void
     */
    public function updateUserMoreInfo($uid, $info)
    {
        $where = $this->_wdb->quoteInto('uid = ?', $uid);
        return $this->_wdb->update($this->table_user_more, $info, $where);
    }

    /**
     * get user basic info
     *
     * @param integer $uid
     * @return array
     */
    public function getUserBasicInfo($uid)
    {
        $sql = "SELECT * FROM dynamite_user_basic WHERE uid=:uid";
        return $this->_rdb->fetchRow($sql, array('uid' => $uid));
    }

    /**
     * get user more info
     *
     * @param integer $uid
     * @return array
     */
    public function getUserMoreInfoForUpdate($uid)
    {
        $sql = "SELECT * FROM dynamite_user_more WHERE uid=:uid FOR UPDATE";
        return $this->_wdb->fetchRow($sql, array('uid' => $uid));
    }

    /**
     * update user bonus
     *
     * @param integer $uid
     * @param integer $change
     * @return void
     */
    public function updateUserBonus($uid, $change)
    {
        $sql = "UPDATE $this->table_user_more SET bonus=bonus + :change WHERE uid=:uid ";
        $this->_wdb->query($sql, array('uid'=>$uid, 'change'=>$change));
    }

    /**
     * update user bomb count
     *
     * @param integer $uid
     * @param integer $change
     * @return void
     */
    public function updateUserBombCount($uid, $change)
    {
        $sql = "UPDATE $this->table_user_more SET bomb_count=bomb_count + :change WHERE uid=:uid ";
        $this->_wdb->query($sql, array('uid'=>$uid, 'change'=>$change));
    }

    /**
     * update user remainder bomb count
     *
     * @param integer $uid
     * @param integer $change
     * @return void
     */
    public function updateUserRemainderBombCount($uid, $change)
    {
        $sql = "UPDATE $this->table_user_more SET remainder_bomb_count=remainder_bomb_count + :change WHERE uid=:uid ";
        $this->_wdb->query($sql, array('uid'=>$uid, 'change'=>$change));
    }

    /**
     * update user remainder bomb count
     *
     * @param integer $uid
     * @param integer $change
     * @return void
     */
    public function updateUserHitmanCount($uid, $change)
    {
        $sql = "UPDATE $this->table_user_more SET hitman_count=hitman_count + :change WHERE uid=:uid ";
        $this->_wdb->query($sql, array('uid'=>$uid, 'change'=>$change));
    }

    /**
     * update user hitman bomb count
     *
     * @param integer $uid
     * @param integer $hitmanId
     * @param integer $change
     * @return void
     */
    public function updateUserHitmanBombCount($uid, $hitmanId, $change)
    {
        $hitman = 'hitman_bomb_count' . $hitmanId;

        $sql = "UPDATE dynamite_user_more SET $hitman = $hitman + :change WHERE uid=:uid";
        $this->_wdb->query($sql, array('uid'=>$uid, 'change'=>$change));
    }

    /**
     * update hitman life
     *
     * @param integer $hitmanId
     * @param integer $uid
     * @param integer $maxLife
     * @return void
     */
    public function updateHitmanLife($hitmanId, $uid, $maxLife)
    {
        $hitman = 'hitman_life' . $hitmanId;

        $sql = "UPDATE dynamite_user_more SET $hitman = $hitman + 1 WHERE uid=$uid AND $hitman < $maxLife AND $hitman > 0";
        $this->_wdb->query($sql);
    }

    /**
     * get need update life uid list
     *
     * @return array
     */
    public function getUpdateLifeUid($uid)
    {
        $now = time();

        $sql = "SELECT u.uid, hitman_type FROM dynamite_user_basic AS u,dynamite_hitman_type AS l, dynamite_user_more AS m
                WHERE u.uid = m.uid AND u.hitman_type = l.id AND ($now - u.last_update_life_time)/(l.life_time*60)>1 AND hitman_count > 0 AND u.uid=:uid";

        return $this->_rdb->fetchRow($sql, array('uid' => $uid));
    }

    /**
     * get user info for update
     *
     * @param integer $uid
     * @return array
     */
    public function getUserForUpdate($uid)
    {

    	$sql = "SELECT m.*, b.* FROM dynamite_user_more AS m, dynamite_user_basic AS b WHERE m.uid = b.uid AND m.uid=:uid FOR UPDATE";

        return $this->_wdb->fetchRow($sql, array('uid'=>$uid));
    }

    /**
     * get user info
     *
     * @param integer $uid
     * @return array
     */
    public function getUser($uid)
    {

        $sql = "SELECT m.*, b.* FROM dynamite_user_more AS m, dynamite_user_basic AS b WHERE m.uid = b.uid AND m.uid=:uid";

        return $this->_rdb->fetchRow($sql, array('uid'=>$uid));
    }


    /**
     * get all app user
     *
     * @return array
     */
    public function getAllAppUser()
    {
        $sql = "SELECT uid FROM $this->table_user WHERE hitman_count>0 ORDER BY create_time DESC";

        return $this->_rdb->fetchAll($sql);
    }

    /**
     * get uid from fids
     *
     * @param array $fids
     * @param integer $pageIndex
     * @param integer $pageSize
     * @return array
     */
    public function getUidInFids($fids, $type = 0, $pageIndex = 0, $pageSize = 0)
    {
        $fids = $this->_rdb->quote($fids);

        $field = $type == 1 ? ' hitman_count>0 AND ' : '';

        $sql = "SELECT uid,bonus FROM $this->table_user_more WHERE " . $field . " uid IN ($fids) ";

        if ( $pageIndex > 0 && $pageSize > 0 ) {
            $start = ($pageIndex-1)*$pageSize;
            $sql .= " ORDER BY bonus DESC LIMIT $start,$pageSize";
        }
        else {
            $sql .= " ORDER BY id DESC";
        }
        return $this->_rdb->fetchAll($sql);
    }

    /**
     * get count of fids in app
     *
     * @param array $fids
     * @return integer
     */
    public function getCountInFids($fids)
    {
        $fids = $this->_rdb->quote($fids);

        $sql = "SELECT count(1) FROM $this->table_user_basic WHERE uid IN ($fids) ";

        return $this->_rdb->fetchOne($sql);
    }

    /**
     * get enemy list
     *
     * @param integer $uid
     * @param integer $pageIndex
     * @param integer $pageSize
     * @return array
     */
    public function getEnemyList($uid, $pageIndex, $pageSize)
    {
        $start = ($pageIndex-1)*$pageSize;

        $sql = "SELECT e.uid,u.bonus FROM dynamite_enemy AS e,dynamite_user_more AS u WHERE e.uid=u.uid AND bomb_uid=:bomb_uid
                ORDER BY e.update_time DESC LIMIT $start,$pageSize ";

        return $this->_rdb->fetchAll($sql,array('bomb_uid'=>$uid));
    }

    /**
     * get enemy count
     *
     * @param integer $uid
     * @return integer
     */
    public function getEnemyCount($uid)
    {
        $sql = "SELECT count(1) FROM dynamite_enemy WHERE bomb_uid=:bomb_uid ";

        return $this->_rdb->fetchOne($sql,array('bomb_uid'=>$uid));
    }

    /**
     * check user is in dynamite
     *
     * @return array
     */

    public function isInDynamite($uid)
    {
        $sql = "SELECT COUNT(1) FROM $this->table_user_basic WHERE uid=:uid";

        $result = $this->_rdb->fetchOne($sql,array('uid'=>$uid));

        return $result > 0;
    }

    /**
     * update user last login time
     *
     * @param integer $uid
     */
    public function updateLastLoginTime($uid)
    {
        $date = time();

        $sql = "UPDATE $this->table_user_basic SET last_login_time=$date WHERE uid=:uid";

        $this->_wdb->query($sql, array('uid' => $uid));
    }

    /**
     * is joined
     *
     * @param integer $uid
     * @return array
     */
    public function getUser2($uid)
    {
        $sql = "SELECT * FROM $this->table_user_basic WHERE uid=:uid ";

        return $this->_rdb->fetchRow($sql, array('uid'=>$uid));
    }

    /**
     * update user last_login_time
     * @param integer $uid
     * @param integer $time
     * @return array
     */
    public function updateUser($uid, $time)
    {
        $sql = "UPDATE $this->table_user_basic SET last_login_time = :last_login_time WHERE uid = :uid";

        return $this->_wdb->query($sql, array('last_login_time' => $time, 'uid' => $uid));
    }

    /**
     * get user invite people
     *
     * @param integer $uid
     * @return array
     */
    public function getUserInvitePeople($uid, $appId)
    {
    	$sql = "SELECT * FROM mixi_app_invite WHERE actor=:actor AND app_id=:appId ORDER BY time ASC";
    	return $this->_rdb->fetchAll($sql, array('actor' => $uid, 'appId' => $appId));
    }

    /**
     * check if user is invited
     * @param integer $uid
     * @param integer $appId
     * @return array
     */
    public function isInInvite($uid, $appId)
    {
    	$sql = "SELECT * FROM mixi_app_invite WHERE target=:target AND app_id=:appid ORDER BY time ASC";
        return $this->_rdb->fetchAll($sql, array('target' => $uid, 'appid' => $appId));
    }


    /**
     * get all of the hitman type info
     *
     * @param integer $number-id < number
     * @return array
     */
    public function getAllHitmanInfo($number)
    {
        $sql = "SELECT * FROM dynamite_hitman_type WHERE id < :number ORDER BY price ASC";

        return $this->_rdb->fetchAll($sql, array('number' => $number));
    }

    /**
     * get hitman type info by type id
     *
     * @param integer $id
     * @return array
     */
    public function getHitmanLevelInfoById($id)
    {
        $sql = "SELECT * FROM dynamite_hitman_type WHERE id=:id ";

        return $this->_rdb->fetchRow($sql, array('id' => $id));
    }


    /**
     * get hitman type info
     *
     * @return array
     */
    public function getHitmanTypeInfo()
    {
        $sql = "SELECT * FROM dynamite_hitman_type";

        return $this->_rdb->fetchAll($sql);
    }

    /**
     * get item info
     *
     * @return array
     */
    public function getItemInfo()
    {
        $sql = "SELECT * FROM dynamite_card ORDER BY sortId";

        return $this->_rdb->fetchAll($sql);
    }


    /**
     * get user bonus
     *
     * @param integer $uid
     * @return integer
     */
    public function getUserBonus($uid)
    {
    	$sql = "SELECT bonus FROM dynamite_user_more WHERE uid=:uid";
    	return $this->_rdb->fetchOne($sql, array('uid' => $uid));
    }

    /**
     * get users who need auto resurrect
     * @param integer $uid
     * @return integer
     */
    public function getNeedResurrectUids($hitmanId, $hour)
    {
        $hitmanDeadTime = 'du.hitman_dead_time' . $hitmanId;
        $hitmanSelf = 'du.hitman_life' . $hitmanId;

        $now = time();

        $sql = "SELECT du.uid,du.hitman_count,dt.max_life FROM dynamite_user_more AS du,dynamite_user_basic AS db,dynamite_hitman_type AS dt
                WHERE  db.hitman_type=dt.id AND du.uid=db.uid AND $hitmanDeadTime>0
                AND ($now - $hitmanDeadTime) > $hour*3600 AND $hitmanSelf=0";

        return $this->_rdb->fetchAll($sql);
    }

    /**
     * get friend count in app
     * @param integer $uid
     * @return integer
     */
    public function getFriendCountInApp($fids)
    {
        $fids = $this->_rdb->quote($fids);

        $sql = "SELECT COUNT(1) FROM dynamite_user_basic WHERE uid IN ($fids)";

        return $this->_rdb->fetchOne($sql);
    }

    /**
     * get neighber uid
     * @param integer $uid
     * @param string $nextOrPrev[prev/next/first/last]
     * @return integer
     */
    public function getNeighberUid($uid, $nextOrPrev)
    {
        $aryParm = array();
        if ('prev' == $nextOrPrev) {
            $sql = "SELECT uid FROM dynamite_user_more WHERE uid<:uid AND hitman_count > 0 ORDER BY uid DESC LIMIT 0,1 ";
            $aryParm['uid'] = $uid;
        }
        else if ('next' == $nextOrPrev){
            $sql = "SELECT uid FROM dynamite_user_more WHERE uid>:uid AND hitman_count > 0 ORDER BY uid LIMIT 0,1 ";
            $aryParm['uid'] = $uid;
        }
        else if ('first' == $nextOrPrev) {
            $sql = "SELECT uid FROM dynamite_user_more WHERE hitman_count > 0 ORDER BY uid LIMIT 0,1 ";
        }
        else if ('last' == $nextOrPrev) {
            $sql = "SELECT uid FROM dynamite_user_more WHERE hitman_count > 0 ORDER BY uid DESC LIMIT 0,1 ";
        }
        return $this->_rdb->fetchOne($sql, $aryParm);
    }


	/**
     * get neighber friend uid
     * @param integer $uid
     * @param integer $profileUid
     * @param string $nextOrPrev[prev/next/first/last]
     * @return integer
     */
    public function getNeighberFriendUid($uid, $profileUid, $nextOrPrev, $fids)
    {
        $fids = $this->_rdb->quote($fids);

        $aryParm = array();
        $aryParm['uid'] = $uid;
        if ('prev' == $nextOrPrev) {
            $sql = "SELECT a.uid FROM
                           (SELECT uid FROM dynamite_user_more WHERE hitman_count > 0 AND uid IN ($fids,:uid)) a
                    WHERE a.uid<:profile_uid ORDER BY a.uid DESC LIMIT 0,1 ";
            $aryParm['profile_uid'] = $profileUid;
        }
        else if ('next' == $nextOrPrev){
            $sql = "SELECT a.uid FROM
                           (SELECT uid FROM dynamite_user_more WHERE hitman_count > 0 AND uid IN ($fids,:uid)) a
                    WHERE a.uid>:profile_uid ORDER BY a.uid LIMIT 0,1 ";
            $aryParm['profile_uid'] = $profileUid;
        }
        else if ('first' == $nextOrPrev) {
            $sql = "SELECT a.uid FROM
                           (SELECT uid FROM dynamite_user_more WHERE hitman_count > 0 AND uid IN ($fids,:uid)) a
                    ORDER BY a.uid LIMIT 0,1 ";
        }
        else if ('last' == $nextOrPrev) {

            $sql = "SELECT a.uid FROM
                           (SELECT uid FROM dynamite_user_more WHERE hitman_count > 0 AND uid IN ($fids,:uid)) a
                    ORDER BY a.uid DESC LIMIT 0,1 ";
        }
        return $this->_rdb->fetchOne($sql, $aryParm);
    }


    /**
     * get users need be auto trigger
     * @param integer $hitmanId
     * @return array
     */
    public function getAutoTriggerBombUser($hitmanId)
    {
    	$bombCount = 'hitman_bomb_count' . $hitmanId;

    	$sql = "SELECT uid,bonus,hitman_count FROM dynamite_user_more WHERE $bombCount=4 AND hitman_count>0";

    	return $this->_rdb->fetchAll($sql);
    }

    /**
     * get bomb power->set in a hitman and power >= 5
     * @param integer $uid
     * @param integer $bombHiman
     * @return array
     */
    public function getHitmanBombPower($uid, $bombHiman)
    {
    	$now = time();

    	$sql = "SELECT * ,CASE WHEN floor(($now-set_time)/(power_time*60))+bomb_power>5 THEN 5 ELSE floor(($now-set_time)/(power_time*60))+bomb_power END AS power
    	        FROM dynamite_bomb WHERE bomb_uid=:uid AND bomb_hitman=:bomb_hitman AND floor(($now-set_time)/(power_time*60))+bomb_power>=5
                ORDER BY set_time DESC";

    	return $this->_rdb->fetchAll($sql, array('uid' => $uid, 'bomb_hitman' => $bombHiman));
    }

    /**
     * update user bomb count and remian bomb count
     * @param integer $uid
     * @param integer $bombHiman
     * @return array
     */
    public function updateUserBombCountAndRemainBombCount($uid, $change)
    {
        $sql = "UPDATE $this->table_user_more SET bomb_count=bomb_count + :change, remainder_bomb_count=remainder_bomb_count + :change WHERE uid=:uid ";
        $this->_wdb->query($sql, array('uid'=>$uid, 'change'=>$change));
    }

   /**
     * check if user need select a new hitman
     * @param String $uids
     * @return void
     */
    public function isNeedSelectHitman($uid)
    {
        $sql = "SELECT b.hitman_type,m.bonus FROM dynamite_user_basic AS b, dynamite_user_more AS m WHERE b.uid=m.uid AND b.uid=:uid";
        return $this->_rdb->fetchRow($sql, array('uid' => $uid));
    }

    /**
     * move to next user or goback
     * @param String $myselfUid
     * @param Interger $id
     * @param array $friendIdArray
     * @param String $moveDirection
     * @param String $orderType
     * @return void
     */
    public function getNextUser($myselfUid, $id, $friendIdArray, $moveDirection, $orderType)
    {

    	//game_mode is friend mode
    	if (!empty($friendIdArray)) {
            $idString = $this->_rdb->quote($friendIdArray);

            $sql = "SELECT uid FROM $this->table_user_more WHERE uid IN($idString) AND hitman_count>0 AND id";

            if ($moveDirection == 'next') {
                $sql = $sql . " > ";
            }
            else if ($moveDirection == 'back') {
                $sql = $sql . " < ";
            }

            $sql = $sql . "$id ORDER BY id $orderType LIMIT 1";
        }
        else {
            $sql = "SELECT uid FROM $this->table_user_more WHERE hitman_count>0 AND uid <> $myselfUid AND id";

            if ($moveDirection == 'next') {
                $sql = $sql . " > ";
            }
            else if ($moveDirection == 'back') {
                $sql = $sql . " < ";
            }

            $sql = $sql . "$id ORDER BY id $orderType LIMIT 1";
        }

        return $this->_rdb->fetchOne($sql);

    }

    /**
     * get user game mode and id
     * @param interger $uid
     * @return array
     */
    public function getUserGameModeAndId($uid)
    {
    	$sql = "SELECT game_mode,id FROM $this->table_user_basic WHERE uid=:uid";
    	return $this->_rdb->fetchRow($sql, array('uid' => $uid));
    }

    /**
     * check users is in app
     * @param array $uidArray
     * @return interger
     */
    public function isInApp($uidArray)
    {
        $uidString = $this->_rdb->quote($uidArray);

        $sql = "SELECT COUNT(uid) FROM dynamite_user_basic WHERE uid IN($uidString)";

        return $this->_rdb->fetchOne($sql);

    }

    /**
     * get user more infomation
     * @param interger $uid
     * @return array
     */
    public function getUserMoreInfo($uid)
    {
        $sql = "SELECT * FROM dynamite_user_more WHERE uid=:uid";
        return $this->_rdb->fetchRow($sql, array('uid' => $uid));
    }

    /**
     * insert payment
     * @param array $payment
     * @return array
     */
    public function insertPayment($payment)
    {
        $this->_wdb->insert('dynamite_payment', $payment);
    }

    /**
     * delete dynamite_payment
     *
     * @param varchar $paycode
     */
    public function deletePayment($paycode)
    {
        $sql = "DELETE FROM dynamite_payment WHERE point_code=:code";
        $this->_wdb->query($sql, array('code' => $paycode));
    }

    /**
     * update dynamite_payment
     * @param string $point_code
     * @param integer $status
     * @param integer $finishTime
     *
     */
    public function updatePaymentStatus($point_code, $status, $finishTime)
    {
        $sql = "UPDATE dynamite_payment SET status=:status,finish_time=:finish_time WHERE point_code=:point_code";

        $this->_wdb->query($sql, array('status' => $status, 'finish_time' => $finishTime, 'point_code' => $point_code));
    }

    /**
     * get payment by point_code
     *
     * @param string $code
     * @return array
     */
    public function getPaymentByCode($code, $status = 0)
    {
        $sql = "SELECT * FROM dynamite_payment WHERE point_code=:code AND status=:status";

        return $this->_rdb->fetchRow($sql, array('code' => $code, 'status' => $status));
    }
}
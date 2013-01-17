<?php
/**
 * item Operation
 * @package    Dal
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/07/07    lp
 */
class Dal_Dynamite_Item extends Dal_Abstract
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
     * insert item
     *
     * @param array $item
     * @return integer
     */
    public function insertItem($item)
    {
        $this->_wdb->insert('dynamite_user_card', $item);
        return $this->_wdb->lastInsertId();
    }

    /**
     * get item info
     *
     * @param integer $uid
     * @param integer $cid
     * @return array
     */
    public function getUserItemInfo($uid, $cid)
    {
        $sql = "SELECT c.*,u.count FROM dynamite_card AS c,dynamite_user_card AS u WHERE c.cid=u.cid AND c.cid=:cid AND u.uid=:uid ";
        return $this->_rdb->fetchRow($sql, array('uid' => $uid, 'cid' => $cid));
    }

	/**
     * get item info
     * @param integer $cid
     * @return array
     */
	public function getItemInfo($cid)
	{
		$sql = "SELECT * FROM dynamite_card WHERE cid=:cid ";
		return $this->_rdb->fetchRow($sql, array('cid' => $cid));
	}

    /**
     * get item list
     * @author lp
     * @param integer $uid
     * @return array
     */
	public function getItemList($uid)
	{
		$sql = "SELECT duc.cid, duc.count, dc.name, dc.introduce
		        FROM dynamite_user_card AS duc, dynamite_card AS dc
		        WHERE duc.cid=dc.cid AND duc.uid=:uid ORDER BY dc.sortId ASC";

		return $this->_rdb->fetchAll($sql, array('uid' => $uid));
	}

    /**
     * check if hitman dead
     * @author lp
     * @param integer $hitmanId
     * @param integer $uid
     * @return integer
     */
    public function getHitmanBloodCount($hitmanId, $uid)
    {
        $sql = "SELECT hitman_life$hitmanId From dynamite_user_more WHERE uid=:uid";
        return $this->_rdb->fetchOne($sql, array('uid' => $uid));
    }

    /**
     * check if user have relevant card
     * @author lp
     * @param integer $uid
     * @param integer $cid
     * @return integer
     */
    public function haveThisCard($uid, $cid)
    {
        $sql = "SELECT count FROM dynamite_user_card WHERE uid=:uid AND cid=:cid";
        return $this->_rdb->fetchOne($sql, array('uid' => $uid, 'cid' => $cid));
    }

    /**
     * updata hitman blood
     * @author lp
     * @param integer $uid
     * @param integer $hitmanId
     * @param integer $hitmanBloodCount
     */
    public function updataHitmanBlood($uid, $hitmanId, $hitmanBloodCount)
    {
    	$sql = "UPDATE dynamite_user_more SET hitman_life$hitmanId=:bloodcount WHERE uid=:uid";
    	$this->_wdb->query($sql, array('bloodcount' => $hitmanBloodCount, 'uid' => $uid));
    }

    /**
     * change hitman blood
     * @author lp
     * @param integer $uid
     * @param integer $cid
     */
    public function updateUserCard($uid, $cid, $change)
    {
        $sql = "UPDATE dynamite_user_card SET count=count+$change WHERE uid=:uid AND cid=:cid";
        $this->_wdb->query($sql, array('uid' => $uid, 'cid' => $cid));
    }

    /**
     * get all hitman blood
     * @author lp
     * @param integer $uid
     * @return array
     */
    public function getAllHitmanBlood($uid)
    {
        $sql = "SELECT hitman_life1, hitman_life2, hitman_life3, hitman_life4 From dynamite_user_more WHERE uid=:uid";
        return $this->_rdb->fetchRow($sql, array('uid' => $uid));
    }

    /**
     * get user bomb count
     * @author lp
     * @param integer $uid
     * @return integer
     */
    public function getUserBombCountForUpdate($uid)
    {
    	$sql = "SELECT bomb_count FROM dynamite_user_more WHERE uid=:uid FOR UPDATE";
    	return $this->_wdb->fetchOne($sql, array('uid' => $uid));
    }

    /**
     * get user have install bomb
     * @author lp
     * @param integer $uid
     * @return array
     */
    public function getInstallBombUserForUpdate($uid)
    {
        $sql = "SELECT uid,count(uid) AS count FROM dynamite_bomb WHERE bomb_uid=:uid GROUP BY uid FOR UPDATE";
        return $this->_wdb->fetchAll($sql, array('uid' => $uid));
    }


    /**
     * update user bomb count
     * @author lp
     * @param integer $uid
     * @param integer $count
     */
    public function updateEnemyBombCount($uid, $count)
    {
    	$sql = "UPDATE dynamite_user_more SET bomb_count=bomb_count-$count WHERE uid=:uid";
        $this->_wdb->query($sql, array('uid' => $uid));
    }

    /**
     * get install bomb user's id and installed bomb count
     * @param string $id
     * @author lp
     * @return array
     */
    public function whoInstallBombInUserAndAlliance($id)
    {
    	$sql = "SELECT uid,count(uid) AS count FROM dynamite_bomb WHERE bomb_uid in($id) GROUP BY uid FOR UPDATE";
    	return $this->_wdb->fetchAll($sql);
    }

    /**
     * send bomb to user
     *
     * @param integer $cid
     * @param integer $uid
     * @return void
     */
    public function sendBombToUser($uid, $count, $remianBomb)
    {
        $sql = "UPDATE dynamite_user_more SET bomb_count=:count, remainder_bomb_count=:remainCount WHERE uid=:uid ";
        $this->_wdb->query($sql, array('count' => $count, 'remainCount' => $remianBomb, 'uid' => $uid));
    }

    /**
     * clean user card
     * @param integer $uid
     * @return void
     */
    public function cleanUserInfo($uid, $batchwork, $hitmanType)
    {
        if ($batchwork) {
            $sql1 = "UPDATE dynamite_user_basic SET isalive=1,hitman_type=$hitmanType WHERE uid=:uid";
        }
        else {
            $sql1 = "UPDATE dynamite_user_basic SET isalive=0,show_set_bomb=0 WHERE uid=:uid";
        }
        $this->_wdb->query($sql1, array('uid' => $uid));

        $sql2 = "DELETE FROM dynamite_bomb WHERE uid=:uid OR bomb_uid=:uid";
        $this->_wdb->query($sql2, array('uid' => $uid));
    }

    /**
     * user set bomb to others information
     * @param integer $uid
     * @return array
     */
    public function getUserSetBombInfoForUpdate($uid)
    {
    	$sql = "SELECT bomb_uid,bomb_hitman FROM dynamite_bomb WHERE uid=:uid FOR UPDATE";
    	return $this->_wdb->fetchAll($sql, array('uid' => $uid));
    }

    /**
     * update user's hitman bomb
     * @param integer $uid
     * @return array
     */
    public function updateUserHitmanBomb($uid, $hitmanId)
    {
    	$sql = "UPDATE dynamite_user_more SET hitman_bomb_count$hitmanId=hitman_bomb_count$hitmanId-1 WHERE uid=:uid";
    	$this->_wdb->query($sql, array('uid' => $uid));
    }


    /**
     * get user live hitman count
     * @param integer $uid
     * @return integer
     */
    public function getHitmanCount($uid)
    {
    	$sql = "SELECT hitman_count FROM dynamite_user_more WHERE uid=:uid";
        return $this->_rdb->fetchOne($sql, array('uid' => $uid));
    }

    /**
     * delete user set bombs and bombs which be set in user
     * @param integer $uid
     * @return integer
     */
    public function deleteBombAboutUser($uid)
    {
    	$sql = "DELETE FROM dynamite_bomb WHERE uid=:uid OR bomb_uid=:uid";
        $this->_wdb->query($sql, array('uid' => $uid));
    }

    /**
     * get user refuse bomb time
     * @author lp
     * @param integer $uid
     * @return integer
     */
    public function getUserRefuseBombTime($uid)
    {
    	$sql = "SELECT refuse_bomb_time FROM dynamite_user_basic WHERE uid=:uid";
    	return $this->_rdb->fetchOne($sql, array('uid' => $uid));
    }

    /**
     * get user max life by user's hitmanType
     * @author lp
     * @param integer $hitmanType
     * @return integer
     */
    public function getMaxLifeByHitmanType($hitmanType)
    {
    	$sql = "SELECT max_life FROM dynamite_hitman_type WHERE id=:id";
    	return $this->_rdb->fetchOne($sql, array('id' => $hitmanType));
    }

    /**
     * get user max life by uid
     * @author lp
     * @param integer $uid
     * @return integer
     */
    public function getMaxLifeByUid($uid)
    {
    	$sql = "SELECT t.max_life FROM dynamite_hitman_type AS t,dynamite_user_basic AS b
    	        WHERE b.uid=:uid AND b.hitman_type=t.id";
    	return $this->_rdb->fetchOne($sql, array('uid' => $uid));
    }

    /**
     * get all of the item count
     *
     * @param integer $uid
     * @return integer
     */
    public function getItemCountAll($uid)
    {
        $sql = "SELECT sum(count) FROM dynamite_user_card WHERE uid=:uid AND count>0 ";
        return $this->_rdb->fetchOne($sql, array('uid' => $uid));
    }

    /**
     * get user bonus by uid
     * @author lp
     * @param integer $uid
     * @return integer
     */
    public function getUserBonusByUid($uid)
    {
    	$sql = "SELECT bonus FROM dynamite_user_more WHERE uid=:uid";
    	return $this->_rdb->fetchOne($sql, array('uid' => $uid));
    }

    /**
     * get user game mode
     * @author lp
     * @param integer $uid
     * @return integer
     */
    public function getUserGameMode($uid)
    {
    	$sql = "SELECT game_mode FROM dynamite_user_basic WHERE uid=:uid";
    	return $this->_rdb->fetchOne($sql, array('uid' => $uid));
    }

    /**
     * update angry card count
     * @author lp
     * @param integer $uid
     * @return integer
     */
    public function updateAngryCard($uid)
    {
    	$sql = "UPDATE dynamite_user_card SET count=1 WHERE cid=10 AND uid=:uid";
    	$this->_wdb->query($sql, array('uid' => $uid));
    }

    /**
     * get add app friend count
     * @author lp
     * @param array $fids
     * @return integer
     */
    public function getFriendCountInApp($fids)
    {
    	$idString = $this->_rdb->quote($fids);

    	$sql = "SELECT count(uid) AS count FROM dynamite_user_basic WHERE uid IN($idString)";

    	return $this->_rdb->fetchOne($sql);
    }

    /**
     * get item list in item shop
     * @author lp
     * @return array
     */
    public function getItemShopList()
    {
        $sql = "SELECT * FROM dynamite_card_shop";
        return $this->_rdb->fetchAll($sql);
    }

    /**
     * get user some item count
     * @author lp
     * @param  integer $uid
     * @return array
     */
    public function getUserSomeItemCount($uid)
    {
        $sql = "SELECT cid,count FROM dynamite_user_card WHERE uid=:uid AND cid < 10";
        return $this->_rdb->fetchAll($sql, array('uid' => $uid));
    }

    /**
     * get one item count
     * @author lp
     * @param  integer $uid
     * @param integer $itemId
     * @return array
     */
    public function getOneItemCount($uid, $itemId)
    {
        $sql = "SELECT count FROM dynamite_user_card WHERE uid=:uid AND cid=:cid";
        return $this->_rdb->fetchOne($sql, array('uid' => $uid, 'cid' => $itemId));
    }

    /**
     * insert buy item log
     * @author lp
     * @return array
     */
    public function insertLog($buyItemLog)
    {
        $this->_wdb->insert('dynamite_buy_item_log', $buyItemLog);
    }

}
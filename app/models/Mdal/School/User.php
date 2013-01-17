<?php

require_once 'Mdal/Abstract.php';

class Mdal_School_User extends Mdal_Abstract
{
    /**
     * user table name
     *
     * @var string
     */
    protected $table_user = 'school_user';

    protected static $_instance;

    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function getUser($uid)
    {
        $sql = "SELECT * FROM $this->table_user WHERE uid=:uid";
        return $this->_rdb->fetchRow($sql, array('uid' => $uid));
    }

    public function getUserLock($uid)
    {
        $sql = "SELECT * FROM $this->table_user WHERE uid=:uid FOR UPDATE";
        return $this->_rdb->fetchRow($sql, array('uid' => $uid));
    }

    public function getUsers($uids, $contactNum)
    {
        $sql = "SELECT uid, rand() AS r
                FROM $this->table_user where uid in ($uids)
                    AND status = 0
                ORDER BY r LIMIT 0, $contactNum";

        return $this->_rdb->fetchAll($sql);
    }

 	/**
     * insert user
     *
     * @param array $user
     * @return integer
     */
    public function insertUser($user)
    {
        return $this->_wdb->insert($this->table_user, $user);
    }

    /**
     * update user
     *
     * @param array $info
     * @param string $uid
     * @return integer
     */
    public function updateUser($info, $uid)
    {
        $where = $this->_wdb->quoteInto('uid=?', $uid);
        return $this->_wdb->update($this->table_user, $info, $where);
    }

	/**
     * delete user
     *
     * @param integer $uid
     * @return integer
     */
    public function deleteUser($uid)
    {
        $sql = "DELETE FROM $this->table_user WHERE uid=:uid ";
        return $this->_wdb->query($sql, array('uid' => $uid));
    }

	/**
     * update user lottery chance
     *
     * @param array $info
     * @param string $uid
     * @return integer
     */
    public function addUserLotteryChance($uid)
    {
        $sql = "UPDATE school_user SET lottery_chance=lottery_chance+1 WHERE uid=:uid ";
        return $this->_wdb->query($sql, array('uid' => $uid));
    }

    /**
     * list app friends and same school users
     *
     * @param string $s_code
     * @param integer $uid
     * @param array $fids
     * @param integer $pageindex
     * @param integer $pagesize
     * @param string $orderBy
     * @return integer
     */
    public function listSchoolFriendIds($s_code, $uid, $fids, $pageindex = 1, $pagesize = 10, $orderBy='')
    {
        $ids = $this->_rdb->quote($fids);
        $start = ($pageindex - 1) * $pagesize;
        $sql = "SELECT uid FROM $this->table_user WHERE uid IN ($ids)";
        $sql .= " OR (school_code=:school_code AND uid<>:uid) ";
        if ($orderBy) {
            $sql .= " ORDER BY $orderBy ";
        }
        $sql .= " LIMIT $start, $pagesize ";

        return $this->_rdb->fetchAll($sql, array('school_code' => $s_code, 'uid' => $uid));
    }

    public function getLstAllSchoolFriendIds($s_code)
    {
        $sql = "SELECT uid FROM $this->table_user WHERE school_code=:school_code";

        return $this->_rdb->fetchAll($sql, array('school_code' => $s_code));
    }

    /**
     * get app uids count
     *
     * @param string $s_code
     * @param integer $uid
     * @param array $fids
     * @return integer
     */
    public function getSchoolFriendIdsCount($s_code, $uid, $fids)
    {
        $ids = $this->_rdb->quote($fids);
        $sql = "SELECT COUNT(uid) FROM $this->table_user WHERE uid IN ($ids)";
        $sql .= " OR (school_code=:school_code AND uid<>:uid) ";
        return $this->_rdb->fetchOne($sql, array('school_code' => $s_code, 'uid' => $uid));
    }

	/**
     * list app uids
     *
     * @param Integer $idxStart
     * @param Integer $pagesize
     * @return array
     */
    public function listAppUids($idxStart = 1, $pagesize = 10)
    {
        $start = $idxStart > 0 ? ($idxStart - 1) : 0;
        $sql = "SELECT uid AS fid FROM $this->table_user
                ORDER BY uid LIMIT $start, $pagesize ";

        return $this->_rdb->fetchAll($sql);
    }


    /***********************************************************************************/

/**
 * xial
 * ***********************************************************************************
 */
//****************************school_user_star_change_history*************************
	public function insertUserStarChangeHistory($info)
	{
		$this->_wdb->insert('school_user_star_change_history', $info);
    	return $this->_wdb->lastInsertId();
	}
}
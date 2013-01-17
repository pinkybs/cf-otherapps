<?php

require_once 'Dal/Abstract.php';

/**
 * Dal Dynamite Bomb
 * MixiApp Dynamite Bomb Data Access Layer
 *
 * @package    Dal/Dynamite
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/07/06    Liz
 */
class Dal_Dynamite_Bomb extends Dal_Abstract
{
    /**
     * bomb table name
     *
     * @var string
     */
    protected $table_bomb = 'dynamite_bomb';

    protected static $_instance;

    protected static $_nowTime;

    public static function getDefaultInstance()
    {
        self::$_nowTime = time();

        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * insert dynamite bomb info
     *
     * @param array $bomb
     * @return integer
     */
    public function insertBomb($bomb)
    {
        $this->_wdb->insert($this->table_bomb, $bomb);
        return $this->_wdb->lastInsertId();
    }

    /**
     * update bomb info
     *
     * @param array $info
     * @param integer $id
     * @return void
     */
    public function updateBomb($info, $id)
    {
        $where = $this->_wdb->quoteInto('id = ?', $id);
        return $this->_wdb->update($this->table_bomb, $info, $where);
    }

    /**
     * update user bomb power time
     *
     * @param integer $uid
     * @param integer $powerTime
     * @return void
     */
    public function updateBombPowerTime($uid, $powerTime, $setTime)
    {
        $sql = "UPDATE $this->table_bomb SET power_time=:power_time,set_time=:set_time,bomb_power=0 WHERE uid=:uid ";
        $this->_wdb->query($sql, array('uid'=>$uid, 'power_time'=>$powerTime, 'set_time'=>$setTime));
    }

    /**
     * get bomb info by uid, bomb_uid and bomb_hitman
     *
     * @param integer $uid
     * @param integer $bomb_uid
     * @param integer $bomb_hitman
     * @return array
     */
    public function getBombByUidAndHitman($uid, $bomb_uid, $bomb_hitman, $maxPower = 5)
    {
        $nowTime = self::$_nowTime;

        $sql = "SELECT *,CASE WHEN floor(($nowTime-set_time)/(power_time*60))+bomb_power>:maxPower THEN :maxPower ELSE
                floor(($nowTime-set_time)/(power_time*60))+bomb_power END AS bomb_power,
                IF (($nowTime - set_time)-(power_time*60)>0, 0, 1) AS needWait
                FROM $this->table_bomb WHERE uid=:uid AND bomb_uid=:bomb_uid AND bomb_hitman=:bomb_hitman";

        return $this->_rdb->fetchRow($sql, array('uid'=>$uid, 'bomb_uid'=>$bomb_uid, 'bomb_hitman'=>$bomb_hitman, 'maxPower'=>$maxPower));
    }

    /**
     * get user bomb info
     *
     * @param integer $uid
     * @param integer $maxPower
     * @return array
     */
    public function getUserBomb($uid, $pageIndex = 0, $pageSize = 0, $maxPower = 5)
    {
        $nowTime = self::$_nowTime;

        $sql = "SELECT *,CASE WHEN floor(($nowTime-set_time)/(power_time*60))+bomb_power>:maxPower THEN :maxPower ELSE
                floor(($nowTime-set_time)/(power_time*60))+bomb_power END AS bomb_power,
                IF (($nowTime - set_time)-(power_time*60)>0, 0, 1) AS needWait,
                IF ((power_time*60-($nowTime - set_time))>0,
                ceil((power_time*60-($nowTime - set_time))/60), 0) AS waitTime
                FROM $this->table_bomb WHERE uid=:uid ORDER BY bomb_power DESC ";

        if ( $pageIndex > 0 && $pageSize > 0 ) {
            $start = ($pageIndex-1)*$pageSize;

            $sql .= " LIMIT $start,$pageSize";
        }

        return $this->_rdb->fetchAll($sql, array('uid'=>$uid, 'maxPower'=>$maxPower));
    }

    /**
     * get user bomb count
     *
     * @param integer $uid
     * @return integer
     */
    public function getUserBombCount($uid)
    {
        $sql = "SELECT count(1) FROM $this->table_bomb WHERE uid=:uid ";

        return $this->_rdb->fetchOne($sql, array('uid'=>$uid));
    }

    /**
     * get user hitman bomb info for update
     *
     * @param integer $uid
     * @return array
     */
    public function getUserHitmanBombForUpdate($uid, $maxPower = 5)
    {
        $nowTime = self::$_nowTime;

        $sql = "SELECT *,id AS bomb_id,IF (floor(($nowTime-set_time)/(power_time*60)) + bomb_power>:maxPower, :maxPower,
                floor(($nowTime-set_time)/(power_time*60))+bomb_power)
                AS bomb_power,IF (($nowTime - set_time)-(power_time*60)>0, 0, 1) AS needWait
                FROM $this->table_bomb WHERE bomb_uid=:uid ORDER BY bomb_power DESC, uid ASC FOR UPDATE";

        return $this->_wdb->fetchAll($sql, array('uid'=>$uid, 'maxPower'=>$maxPower));
    }

    /**
     * get user hitman bomb info
     *
     * @param integer $uid
     * @return array
     */
    public function getUserHitmanBomb($uid, $maxPower = 5)
    {
        $nowTime = self::$_nowTime;

        $sql = "SELECT *,id AS bomb_id,IF (floor(($nowTime-set_time)/(power_time*60)) + bomb_power>:maxPower, :maxPower,
                floor(($nowTime-set_time)/(power_time*60))+bomb_power)
                AS bomb_power,IF (($nowTime - set_time)-(power_time*60)>0, 0, 1) AS needWait
                FROM $this->table_bomb WHERE bomb_uid=:uid ORDER BY bomb_power DESC, uid ASC";

        return $this->_rdb->fetchAll($sql, array('uid'=>$uid, 'maxPower'=>$maxPower));
    }

    /**
     * get bomb user hitman bomb info
     *
     * @param integer $uid
     * @param integer $bombHitman
     * @return array
     */
    public function getBombUserHitmanBomb($uid, $bombHitman, $maxPower = 5)
    {
        $nowTime = self::$_nowTime;

        $sql = "SELECT *,id AS bomb_id,IF (floor(($nowTime-set_time)/(power_time*60))+bomb_power>:maxPower, :maxPower,
                floor(($nowTime-set_time)/(power_time*60))+bomb_power) AS bomb_power,
                IF (($nowTime - set_time)-(power_time*60)>0, 0, 1) AS needWait,
                IF ((power_time*60-($nowTime - set_time))>0,
                ceil((power_time*60-($nowTime - set_time))/60), 0) AS waitTime
                FROM $this->table_bomb WHERE bomb_uid=:uid AND bomb_hitman=:bomb_hitman ORDER BY set_time ASC ";

        return $this->_rdb->fetchAll($sql, array('uid'=>$uid, 'bomb_hitman'=>$bombHitman, 'maxPower'=>$maxPower));
    }

    /**
     * get user had set bomb to some body
     *
     * @param integer $uid
     * @param integer $bomb_uid
     * @return array
     */
    public function getUsesSetBombToOne($uid, $bomb_uid)
    {
        $nowTime = self::$_nowTime;

        $sql = "SELECT *,IF (($nowTime - set_time)-(power_time*60)>0, 1, 0) AS canBomb,
                IF (($nowTime - set_time)-(power_time*60)>0, 0, 1) AS needWait
                FROM $this->table_bomb WHERE uid=:uid AND bomb_uid=:bomb_uid ";

        return $this->_rdb->fetchAll($sql, array('uid'=>$uid, 'bomb_uid'=>$bomb_uid));
    }

    /**
     * delete bomb info by bomb hitman
     *
     * @param integer $bomb_uid
     * @param integer $bomb_hitman
     * @return void
     */
    public function deleteBombByBombhitman($bomb_uid, $bomb_hitman)
    {
        $sql = "DELETE FROM $this->table_bomb WHERE bomb_uid=:bomb_uid AND bomb_hitman=:bomb_hitman ";
        $this->_wdb->query($sql, array('bomb_uid'=>$bomb_uid, 'bomb_hitman'=>$bomb_hitman));
    }

    /**
     * delete bomb info by bomb id
     *
     * @param integer $id
     * @return void
     */
    public function deleteBombById($id)
    {
        $sql = "DELETE FROM $this->table_bomb WHERE id=:id ";
        return $this->_wdb->query($sql, array('id'=>$id));
    }

    /**
     * is user'hitman under attack
     *
     * @param integer $uid
     * @param integer $hitman
     * @return void
     */
    public function isUnderAttack($uid, $hitman)
    {
        $sql = "SELECT COUNT(id) FROM $this->table_bomb WHERE bomb_uid=:bomb_uid AND bomb_hitman=:bomb_hitman ";
        $result = $this->_rdb->fetchOne($sql, array('bomb_uid'=>$uid, 'bomb_hitman'=>$hitman));
        return $result > 0 ? true : false;
    }

    /**
     * get user hitman bomb info
     *
     * @param integer $uid
     * @return array
     */
    public function getTheHitmanBomb($uid, $bombHitman, $maxPower = 5)
    {
        $nowTime = self::$_nowTime;

        $sql = "SELECT *,id AS bomb_id,IF (floor(($nowTime-set_time)/(power_time*60)) + bomb_power>:maxPower, :maxPower,
                floor(($nowTime-set_time)/(power_time*60))+bomb_power)
                AS bomb_power,IF (($nowTime - set_time)-(power_time*60)>0, 0, 1) AS needWait
                FROM $this->table_bomb WHERE bomb_uid=:uid AND bomb_hitman=:bomb_hitman ORDER BY bomb_power DESC, uid ASC";

        return $this->_rdb->fetchAll($sql, array('uid' => $uid, 'bomb_hitman' => $bombHitman, 'maxPower' => $maxPower));
    }

    /**
     * update power bomb xount
     *
     * @param integer $uid
     * @param integer $power
     * @param integer $change
     *
     */
    public function updatePowerBombCount($uid, $power, $change)
    {
        $sql = "UPDATE dynamite_user_more SET bomb_power$power = bomb_power$power + :change WHERE uid=:uid";

        $this->_wdb->query($sql, array('change' => $change, 'uid' => $uid));
    }

}
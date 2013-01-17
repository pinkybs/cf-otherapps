<?php

/**
 * rank Operation
 * @package    Dal
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/07/10    lp
 */
class Dal_Dynamite_Rank extends Dal_Abstract
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
     * get alliance rank top
     * @author lp
     * @param integer $uid
     * @param integer $type
     * @return array
     */
    public function getRankUser($uid, $idArray = null, $start = 0, $size = 5, $type, $orderType)
    {

        $end = $start + $size + 1;

        //$type:1->mixifriend, 2->all user
        if ($type == 1) {
            $idString = $this->_rdb->quote($idArray);

            if (empty($idString)) {
                $sql = "SELECT uid, bonus FROM dynamite_user_more WHERE uid=:uid";
            }
            else {
                $sql = "SELECT uid, bonus FROM dynamite_user_more WHERE uid IN('$uid',$idString)
                        ORDER BY bonus $orderType, id ASC
                        LIMIT $start,$size";
            }
        }
        else if ($type == 2) {
            $rankTableName = $this->getBonusRankTableName();

            $sql = "SELECT * FROM $rankTableName WHERE id > $start AND id < $end";

        }

        return $this->_rdb->fetchAll($sql, array('uid' => $uid));
    }

    /**
     * get rank user count
     * @author lp
     * @param integer $uid
     * @param array $idArray
     * @param integer $type
     * @return array
     */
    public function getRankCount($uid, $idArray, $type, $orderType = 1)
    {
        //$type:1->mixifriend, 2->all user
        if ($type == 1) {
            $idString = $this->_rdb->quote($idArray);

            if (empty($idString)) {
                return 1;
            }
            else {
                $sql = "SELECT COUNT(uid) AS count FROM dynamite_user_basic WHERE uid IN('$uid',$idString)";
            }
        }
        else if ($type == 2) {
            //check order type
            $tableName = $orderType == 1 ? $this->getBonusRankTableName() : $this->getDeadNumRankTableName();

            $sql = "SELECT MAX(id) FROM " . $tableName;
        }

        return $this->_rdb->fetchOne($sql, array('uid' => $uid));
    }

    /**
     * get user rank number
     * @author lp
     * @param integer $uid
     * @param string $idString
     * @param integer $type
     * @return array
     */
    public function getUserRankNm($uid, $lastUserId, $idArray, $type, $orderType)
    {

        $sql1 = "SET @pos=0";
        $this->_rdb->query($sql1);
        //$type 1->mixifriend, 2->all
        if ($type == 1) {

            $idString = $this->_rdb->quote($idArray);

            if (empty($idString)) {
                return array('rank' => 1);
            }
            else {
                $sql = "SELECT b.rank, b.uid, b.bonus FROM
                        (SELECT @pos:=@pos+1 AS rank, uid, bonus FROM dynamite_user_more where uid IN('$uid','$lastUserId',$idString)
                        ORDER BY bonus $orderType, id ASC ) as b
                        WHERE uid=:uid";
            }
        }
        else if ($type == 2) {
            $rankTableName = $this->getBonusRankTableName();

            $sql = "SELECT *,id AS rank FROM $rankTableName WHERE uid=:uid";
        }

        return $this->_rdb->fetchRow($sql, array('uid' => $lastUserId));
    }

    /**
     * get rank user 1-6 or the last 6
     * @author lp
     * @param integer $uid
     * @param string $idString
     * @param integer $type
     * @return array
     */
    public function getMaxRankUser($uid, $idArray, $start = 0, $size = 6, $type)
    {
        //$now = time() - 15 * 60;
        $end = $start + $size;
        $idString = $this->_rdb->quote($idArray);

        //$type 1->mixifriend, 2->all
        if ($type == 1) {
            $sql = "SELECT uid, bonus FROM dynamite_user_more WHERE uid IN('$uid',$idString) ORDER BY bonus DESC,
                    id ASC LIMIT $start,$size";

        }
        else if ($type == 2) {
            $rankTableName = $this->getBonusRankTableName();

            $sql = "SELECT * FROM $rankTableName WHERE id >= $start AND id < $end";
        }

        return $this->_rdb->fetchAll($sql, array('uid' => $uid));
    }

    /**
     * get game over rank user
     * @author lp
     * @param integer $uid
     * @param array $idString
     * @param integer $type
     * @return array
     */
    public function getGameOverRankUser($uid, $idArray, $start, $size, $type, $orderType)
    {
        if ($start > 0) {
            $start = ($start - 1) * $size;
        }

        $end = $start + $size;

        $sql1 = "SET @pos=" . $start;
        $this->_rdb->query($sql1);

        //$type 1->mixifriend, 2->all
        if ($type == 1) {
            $idString = $this->_rdb->quote($idArray);
            if (empty($idString)) {
                $sql = "SELECT @pos:=@pos+1 AS rank,uid,dead_number FROM dynamite_user_more WHERE uid=:uid";
            }
            else {
                $sql = "SELECT @pos:=@pos+1 AS rank,uid,dead_number FROM dynamite_user_more WHERE uid IN('$uid',$idString) ORDER BY dead_number $orderType, id ASC
                        LIMIT $start,$size";
            }

            $result = $this->_rdb->fetchAll($sql, array('uid' => $uid));
        }
        else if ($type == 2) {

            $rankTableName = $this->getDeadNumRankTableName();

            $sql = "SELECT id AS rank,uid,dead_number FROM $rankTableName WHERE id > $start AND id <= $end";

            $result = $this->_rdb->fetchAll($sql);
        }

        return $result;
    }

    /**
     * get max reward rank user
     * @author lp
     * @param integer $uid
     * @param array $idString
     * @param integer $start
     * @param integer $size
     * @param integer $type
     * @param string $orderType
     * @return array
     */
    public function getMaxRewardRankUser($uid, $idArray, $start, $size, $type, $orderType)
    {
        if ($start > 0) {
            $start = ($start - 1) * $size;
        }

        $end = $start + $size;

        $sql1 = "SET @pos=" . $start;
        $this->_rdb->query($sql1);

        //$type 1->mixifriend, 2->all
        if ($type == 1) {
            $idString = $this->_rdb->quote($idArray);

            if (empty($idString)) {
                $sql = "SELECT @pos:=@pos+1 AS rank,uid,bonus FROM dynamite_user_more WHERE uid=:uid";
            }
            else {
                $sql = "SELECT @pos:=@pos+1 AS rank,uid,bonus FROM dynamite_user_more WHERE uid IN('$uid',$idString) ORDER BY bonus $orderType, id ASC
                        LIMIT $start,$size";
            }

            $result = $this->_rdb->fetchAll($sql, array('uid' => $uid));
        }
        else if ($type == 2) {
            $rankTableName = $this->getBonusRankTableName();

            $sql = "SELECT id AS rank,uid,bonus FROM $rankTableName WHERE id > $start AND id <= $end";

            $result = $this->_rdb->fetchAll($sql);
        }

        return $result;
    }

    /**
     * get user ranking number
     *
     * @param integer $uid
     * @param array $fids
     * @param integer $type
     * @param integer $orderType
     * @return integer
     */
    public function getMobileUserRankNm($uid, $fids, $type, $orderType)
    {
        //$type:1->mixifriend, 2->all user
        if ($type == 1) {
            //check order type
            $order = $orderType == 1 ? " bonus DESC " : " dead_number DESC ";

            $sql1 = "SET @pos=0";
            $this->_rdb->query($sql1);

            $fids = $this->_rdb->quote($fids);

            $sql = "SELECT rank FROM (SELECT @pos:=@pos+1 AS rank,uid FROM dynamite_user_more WHERE uid IN ($fids, :uid)
                    ORDER BY " . $order . ", id ASC) AS r WHERE r.uid=:uid";
            $result = $this->_rdb->fetchOne($sql, array('uid' => $uid));
        }
        else {
            //check order type
            $tableName = $orderType == 1 ? $this->getBonusRankTableName() : $this->getDeadNumRankTableName();

            $sql = "SELECT id AS rank FROM " . $tableName . " WHERE uid=:uid";
            $result = $this->_rdb->fetchOne($sql, array('uid' => $uid));
        }
        return $result;
    }

    /**
     * get top rank user
     * @param integer $uid
     * @param array $idArray
     * @param integer $type
     * @return integer
     */
    public function getTopRankUser($uid, $idArray, $type)
    {
        //$type 1->mixifriend, 2->all
        if ($type == 1) {
            $idArray[] = $uid;
            $idString = $this->_rdb->quote($idArray);

            $sql = "SELECT uid, bonus FROM dynamite_user_more WHERE uid IN($idString) ORDER BY bonus DESC,id ASC LIMIT 1";
        }
        else if ($type == 2) {
            $rankTableName = $this->getBonusRankTableName();

            $sql = "SELECT * FROM $rankTableName WHERE id=1";
        }

        return $this->_rdb->fetchAll($sql);
    }

    /**
     * get rank for friend game mode
     * @param integer $start
     * @param integer $size
     * @return array
     */
    public function friendGameModeRank($start, $size)
    {
        $end = $start + $size;

        $rankTableName = $this->getBonusRankTableName();

        $sql = "SELECT * FROM $rankTableName WHERE id > $start AND id <= $end";

        return $this->_rdb->fetchAll($sql);
    }

    /**
     * get user rank num after trigger bomb, only used in mobile
     * @param integer $start
     * @param integer $size
     * @return array
     */
    public function getUserRankNmAfterTriggerBomb($uid)
    {
        $TableName = $this->getAllUserRankTableName();

        $sql = "SELECT *, id AS rank FROM $TableName WHERE uid=:uid";

        return $this->_rdb->fetchRow($sql, array('uid' => $uid));
    }
    /************ do batch about dynamite_rank_tmp_1 *************/
    public function doBatchUpdateRankTemTable()
    {

        //$sql = "TRUNCATE Table dynamite_rank_tmp_1;";
        //$this->_wdb->query($sql);

        $sql = "SET @pos=0;";
        $this->_wdb->query($sql);

        $sql = "INSERT INTO dynamite_rank_tmp_1
                SELECT @pos:=@pos+1 AS id, m.uid, m.bonus FROM dynamite_user_more AS m, dynamite_user_basic AS b
                WHERE b.game_mode = 0 AND m.uid = b.uid ORDER BY m.bonus DESC, m.id ASC;";

        $this->_wdb->query($sql);

    }

    public function insertNewRankTable()
    {
        $sql = "TRUNCATE Table dynamite_rank_tmp;";
        $this->_wdb->query($sql);

        $sql = "INSERT INTO dynamite_rank_tmp
                SELECT id, uid, bonus FROM dynamite_rank_tmp_1;";
        $this->_wdb->query($sql);

    }
    /************ do batch about dynamite_rank_tmp_1 end*************/


    /************ do batch about dynamite_rank_deadnumber_tmp_1 *************/
    public function doBatchUpdateDeadNumTable()
    {
        //$sql = "TRUNCATE Table dynamite_rank_deadnumber_tmp_1;";
        //$this->_wdb->query($sql);

        $sql = "SET @pos=0;";

        $this->_wdb->query($sql);

        $sql = "INSERT INTO dynamite_rank_deadnumber_tmp_1
                SELECT @pos:=@pos+1 AS id, m.uid, m.dead_number FROM dynamite_user_more AS m, dynamite_user_basic AS b
                WHERE b.game_mode = 0 AND m.uid = b.uid ORDER BY dead_number DESC, m.id ASC;";
        $this->_wdb->query($sql);
    }

    public function insertNewDeadNumTable()
    {
        $sql = "TRUNCATE Table dynamite_rank_deadnumber_tmp;";
        $this->_wdb->query($sql);

        $sql = "INSERT INTO dynamite_rank_deadnumber_tmp
                SELECT id, uid, dead_number FROM dynamite_rank_deadnumber_tmp_1;";
        $this->_wdb->query($sql);

    }
    /************ do batch about dynamite_rank_deadnumber_tmp_1 end*************/

    /************ do batch about dynamite_rank_all_tmp_1 *************/
    public function doBatchUpdateAllUserRankTable()
    {
        //$sql = "TRUNCATE Table dynamite_rank_all_tmp_1;";
        //$this->_wdb->query($sql);

        $sql = "SET @pos=0;";

        $this->_wdb->query($sql);

        $sql = "INSERT INTO dynamite_rank_all_tmp_1
                SELECT b.rank, b.uid, b.bonus FROM
                (SELECT @pos:=@pos+1 AS rank, uid, bonus FROM dynamite_user_more ORDER BY bonus DESC, id ASC ) as b
                ";
        $this->_wdb->query($sql);
    }

    public function insertNewRankAllTable()
    {
        $sql = "TRUNCATE Table dynamite_rank_all_tmp;";
        $this->_wdb->query($sql);

        $sql = "INSERT INTO dynamite_rank_all_tmp
                SELECT id, uid, bonus FROM dynamite_rank_all_tmp_1;";
        $this->_wdb->query($sql);

    }
     /************ do batch about dynamite_rank_all_tmp_1 end*************/

    public function getBonusRankTableName()
    {
        require_once 'Bll/Cache/Dynamite.php';
        $result = Bll_Cache_Dynamite::getRankTempTable();

        $tableName = '';

        if ($result == 1) {
            $tableName = 'dynamite_rank_tmp';
        }
        else if ($result == 2) {
            $tableName = 'dynamite_rank_tmp_1';
        }

        return $tableName;
    }

    public function getDeadNumRankTableName()
    {
        require_once 'Bll/Cache/Dynamite.php';
        $result = Bll_Cache_Dynamite::getDeadNumTempTable();

        $tableName = '';

        if ($result == 1) {
            $tableName = 'dynamite_rank_deadnumber_tmp';
        }
        else if ($result == 2) {
            $tableName = 'dynamite_rank_deadnumber_tmp_1';
        }

        return $tableName;
    }

    public function getAllUserRankTableName()
    {
        require_once 'Bll/Cache/Dynamite.php';
        $result = Bll_Cache_Dynamite::getAllUserRankTable();

        $tableName = '';

        if ($result == 1) {
            $tableName = 'dynamite_rank_all_tmp';
        }
        else if ($result == 2) {
            $tableName = 'dynamite_rank_all_tmp_1';
        }

        return $tableName;
    }


    public function trancateTempTable($tablename)
    {
        if ($tablename == 0) {
            $tablename = 'dynamite_rank_tmp_1';
        }
        else if ($tablename == 1) {
            $tablename = 'dynamite_rank_deadnumber_tmp_1';
        }
        else if ($tablename == 2) {
            $tablename = 'dynamite_rank_all_tmp_1';
        }

        $sql = "TRUNCATE Table $tablename;";

        $this->_wdb->query($sql);
    }

    public function isTableEmpty($tableName)
    {
        $sql = "SELECT id FROM $tableName WHERE id = 1";
        return $this->_rdb->fetchOne($sql);
    }
}
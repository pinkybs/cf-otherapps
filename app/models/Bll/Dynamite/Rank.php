<?php

require_once 'Bll/Abstract.php';

/**
 * dynamite rank logic's Operation
 *
 * @package    Bll
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/07/07    lp
 */
class Bll_Dynamite_Rank extends Bll_Abstract
{

    /**
     * user rank
     * @param string $uid
     * @param integer $type  1->my mixi friends, 2->all user
     * @return array
     */
    public function rank($uid, $type = 1)
    {
        $idArray = null;

        if ($type == 1) {
            $idArray = $this->getIdArray($uid, $type);
        }

        require_once 'Dal/Dynamite/Rank.php';
        $dalRank = Dal_Dynamite_Rank::getDefaultInstance();

        //get top rank user
        $topRankUser = $dalRank->getTopRankUser($uid, $idArray, $type);

        require_once 'Bll/User.php';
        Bll_User::appendPeople($topRankUser, 'uid');

        $topRankUser = $this->truncateResult($topRankUser);

        //get rank user count
        $rankCount = $dalRank->getRankCount($uid, $idArray, $type);

        //get user rank number
        $userRankNumArr = $dalRank->getUserRankNm($uid, $uid, $idArray, $type, 'DESC');
        if (empty($userRankNumArr)) {
        	$userRankNum = 2;
        }
        else {
	        $userRankNum = $userRankNumArr['rank'];
	    }

	    //get rank user
        $size = 5;

        if ($rankCount <= 6) {
            $start = 1;
            $size = $rankCount - 1;
        }
        else if ($userRankNum == $rankCount || ($userRankNum + 2) >= $rankCount) {
            $start = $rankCount - 5;
        }
        else if ($userRankNum < 5) {
			$start = 1;
		}
        else {
            $start = $userRankNum - 3;
        }

        $rankUser = array();

        if ($rankCount != 1) {
            $rankUser = $dalRank->getRankUser($uid, $idArray, $start, $size, $type, 'DESC');
            $rankUser = array_reverse($rankUser);
        }

        //if rank user <= 6,  add invite user
        $inviteUser = array();
        if ($rankCount <= 6) {
            $count = 5 - count($rankUser);

            for ($i = 0; $i < $count; $i++) {
                $inviteUser[$i] = array('displayName' => '?????', 'bonus' => '?????', 'thumbnailUrl' => Zend_Registry::get('static') . '/apps/dynamite/img/default.png');
            }
        }

        //the last user's order of cast in this search
        if (!empty($rankUser)) {
            $lastUserRankNumArr = $dalRank->getUserRankNm($uid, $rankUser[0]['uid'], $idArray, $type, 'DESC');

            $lastUserRankNum = $lastUserRankNumArr['rank'];

            Bll_User::appendPeople($rankUser, 'uid');
            $rankUser = $this->truncateResult($rankUser);
        }
        else {
            $lastUserRankNum = 6;
        }

        //get rank pre
        if ($lastUserRankNum <= 6) {
            $rankPre = 2;
        }
        else {
            $rankPre = $lastUserRankNum - 4;
        }

        $response = array('rankCount' => $rankCount, 'rankUser' => $rankUser, 'userRankNum' => $userRankNum, 'topRankUser' => $topRankUser, 'inviteUser' => $inviteUser, 'start' => $start, 'lastUserRankNum' => $lastUserRankNum, 'rankPre' => $rankPre);

        return $response;
    }

    /**
     * move to left or right
     * @return array
     */
    public function getNextRankUser($uid, $rankCount, $lastUserRankNum, $rankPrev, $rankType, $direction)
    {
        $idArray = null;

        if ($rankType == 1) {
            $idArray = $this->getIdArray($uid, $rankType);
        }

        require_once 'Dal/Dynamite/Rank.php';
        $dalRank = Dal_Dynamite_Rank::getDefaultInstance();
        $rankCount = $dalRank->getRankCount($uid, $idArray, $rankType);

        if ($direction == 'left') {
            $start = $lastUserRankNum;
            $size = 5;
        }
        else if ($direction == 'right') {
            if ($rankPrev <= 7) {
                $start = 1;
                $size = $rankPrev - 2;
            }
            else {
                $start = $rankPrev - 6;
                $size = 5;
            }
        }

        $rankUser = $dalRank->getRankUser($uid, $idArray, $start, $size, $rankType, 'DESC');
        $rankUser = array_reverse($rankUser);

        require_once 'Bll/User.php';
        Bll_User::appendPeople($rankUser, 'uid');

        if ($direction == 'left') {
            $lastRankNumbr = $lastUserRankNum + count($rankUser);
        }
        else if ($direction == 'right') {
            $rankPrev = $rankPrev - count($rankUser);
        }

        if (!empty($rankUser)) {
            $rankUser = $this->truncateResult($rankUser);
        }

        $nextRankUser = array('info' => $rankUser, 'lastRankNum' => $lastRankNumbr, 'rankPrev' => $rankPrev, 'rankCount' => $rankCount);

        return $nextRankUser;
    }

    /**
     * move to left or right, move 10 users once
     * @return array
     */
    public function getNextTenRankUser($uid, $type, $direction, $currentRight)
    {
        //type == 1, move in my friends, type==2, move in all user
        $idArray = null;

        if ($type == 1) {
            $idArray = $this->getIdArray($uid, $type);
        }

        require_once 'Dal/Dynamite/Item.php';
        $dalItem = Dal_Dynamite_Item::getDefaultInstance();
        //gameMode==1, friend mode,  gameMode==0, all user mode
        $gameMode = $dalItem->getUserGameMode($uid);

        require_once 'Dal/Dynamite/Rank.php';
        $dalRank = Dal_Dynamite_Rank::getDefaultInstance();

        //move to right
        if ($direction == 'right') {
            //move in all user
            if ($type == 2) {
                //friend game mode
                if ($gameMode == 1) {
                    if ($currentRight <= 12) {
                        $start = 1;
                        $currentRight = 2;
                    }
                    else {
                        $start = $currentRight - 10;
                        $currentRight = $start;
                    }
                    $rankUser = $dalRank->friendGameModeRank($start, 5);
                }
                //all user game mode
                else {
                    if ($currentRight <= 12) {
                        $start = 2;
                        $currentRight = 2;
                    }
                    else {
                        $start = $currentRight - 10;
                        $currentRight = $start;
                    }
                    $rankUser = $dalRank->getMaxRankUser($uid, $idArray, $start, 5, $type);
                }
            }
            //move in my friends
            else if ($type == 1) {
                if ($currentRight <= 12) {
                    $start = 1;
                    $currentRight = 2;
                }
                else {
                    $start = $currentRight - 11;
                    $currentRight = $start + 1;
                }

                $rankUser = $dalRank->getMaxRankUser($uid, $idArray, $start, 5, $type);
            }
        }
        //move to left
        else {

            $rankCount = $dalRank->getRankCount($uid, $idArray, $type);
            //move in all user
            if ($type == 2) {
                //friend game mode
                if ($gameMode == 1) {
            		if ($currentRight + 14 >= $rankCount) {
                        $start = $rankCount - 5;
                    }
                    else {
                        $start = $currentRight + 9;
                    }

                    $currentRight = $start + 1;
            		$rankUser = $dalRank->friendGameModeRank($start, 5);
            	}
            	//all user game mode
            	else {
            		if ($currentRight + 14 >= $rankCount) {
                        $start = $rankCount - 4;
                    }
                    else {
                        $start = $currentRight + 10;
                    }

                    $currentRight = $start;
            		$rankUser = $dalRank->getMaxRankUser($uid, $idArray, $start, 5, $type);
            	}
            }
            //move in my friends
            else if ($type == 1) {
            	if ($currentRight + 14 >= $rankCount) {
                    $start = $rankCount - 5;
                }
                else {
                    $start = $currentRight + 9;
                }

                $currentRight = $start + 1;

            	$rankUser = $dalRank->getMaxRankUser($uid, $idArray, $start, 5, $type);
            }

        }

        $rankUser = array_reverse($rankUser);

        require_once 'Bll/User.php';
        Bll_User::appendPeople($rankUser, 'uid');

        if (!empty($rankUser)) {
            $rankUser = $this->truncateResult($rankUser);
        }

        $response = array('rankUser' => $rankUser, 'rankCount' => $rankCount, 'currentRight' => $currentRight);

        return $response;
    }

    /**
     * if user's game_mode = 1, use this rank method
     * @return array
     */
    public function friendGameModeRank($uid)
    {
        $start = 1;
        $size = 5;

        require_once 'Dal/Dynamite/Rank.php';
        $dalRank = Dal_Dynamite_Rank::getDefaultInstance();
        //get rank count
        $rankCount = $dalRank->getRankCount($uid, null, 2);
        //get top rank user
        $topRankUser = $dalRank->friendGameModeRank(0, 1);

        require_once 'Bll/User.php';
        Bll_User::appendPeople($topRankUser, 'uid');
        $topRankUser = $this->truncateResult($topRankUser);
        //get 2-5 rank user
        $rankUser = $dalRank->friendGameModeRank($start, $size, 'DESC');
        $rankUser = array_reverse($rankUser);
        if (!empty($rankUser)) {
            Bll_User::appendPeople($rankUser, 'uid');
            $rankUser = $this->truncateResult($rankUser);
        }

        //rank user != 6,  add invite user
        $inviteUser = array();
        if ($rankCount <= 6) {
            $count = 5 - count($rankUser);

            for ($i = 0; $i < $count; $i++) {
                $inviteUser[$i] = array('displayName' => '?????', 'bonus' => '?????', 'thumbnailUrl' => Zend_Registry::get('static') . '/apps/dynamite/img/default.png');
            }
        }

        $rankInfo = array('rankCount' => $rankCount, 'rankUser' => $rankUser, 'userRankNum' => 0, 'topRankUser' => $topRankUser, 'inviteUser' => $inviteUser, 'lastUserRankNum' => count($rankUser) + 1);
        return $rankInfo;
    }

    /**
     * move to left or right
     * @return array
     */
    public function getFriendModeNextRankUser($uid, $lastUserRankNum, $rankPrev, $direction)
    {
        require_once 'Dal/Dynamite/Rank.php';
        $dalRank = Dal_Dynamite_Rank::getDefaultInstance();
        $rankCount = $dalRank->getRankCount($uid, null, 2);

        $size = 5;

        if ($direction == 'left') {
            $start = $lastUserRankNum;
        }
        else if ($direction == 'right') {
            if ($rankPrev <= 7) {
                $start = 1;
                $size = $rankPrev - 2;
            }
            else {
                $start = $rankPrev - 6;
            }
        }

        $rankUser = $dalRank->friendGameModeRank($start, $size, 'DESC');

        if ($direction == 'left') {
            $lastRankNumbr = $lastUserRankNum + count($rankUser);
        }
        else if ($direction == 'right') {
            $rankPrev = $rankPrev - count($rankUser);
        }

        if (!empty($rankUser)) {
            $rankUser = array_reverse($rankUser);
            require_once 'Bll/User.php';
            Bll_User::appendPeople($rankUser, 'uid');
            $rankUser = $this->truncateResult($rankUser);
        }

        $nextRankUser = array('info' => $rankUser, 'lastRankNum' => $lastRankNumbr, 'rankPrev' => $rankPrev, 'rankCount' => $rankCount);

        return $nextRankUser;
    }

    /**
     * mymixi reward rank or dead rank
     * @param string $uid
     * @param int $rankType
     * @return array
     */
    public function specialRank($uid, $rankType = 1)
    {
        $idArray = null;

        if ($rankType == 1) {
            $idArray = $this->getIdArray($uid, $rankType);
        }

        require_once 'Bll/Cache/Dynamite.php';
        //get reward rank user
        $rewardRankUser = Bll_Cache_Dynamite::getMaxRewardRankUser($uid, $idArray, 0, 10, $rankType, 'DESC');

        //get game over rank user
        $gameOverRankUser = Bll_Cache_Dynamite::getGameOverRankUser($uid, $idArray, 0, 10, $rankType, 'DESC');

        require_once 'Bll/User.php';

        if (!empty($rewardRankUser)) {
            Bll_User::appendPeople($rewardRankUser, 'uid');
            $rewardRankUser = $this->truncateResult($rewardRankUser);
        }

        if (!empty($gameOverRankUser)) {
            Bll_User::appendPeople($gameOverRankUser, 'uid');
            $gameOverRankUser = $this->truncateResult($gameOverRankUser);
        }

        $rankUser = array('rewardRankUser' => $rewardRankUser, 'gameOverRankUser' => $gameOverRankUser);
        return $rankUser;
    }

    /**
     * other type reward rank or dead rank, alliance,emeny,all,mymixi
     * @param string $uid
     * @param int $rankName, 1->reward rank, 2->dead rank
     * @param int $rankType
     * @return array
     */
    public function otherSpecialRank($uid, $rankName, $rankType)
    {
        $idArray = null;

        if ($rankType == 1) {
            $idArray = $this->getIdArray($uid, $rankType);
        }

        require_once 'Bll/Cache/Dynamite.php';

        if ($rankName == 1) {
            //get reward rank user
            $rewardRankUser = Bll_Cache_Dynamite::getMaxRewardRankUser($uid, $idArray, 0, 10, $rankType, 'DESC');
        }
        else if ($rankName == 2) {
            //get game over rank user
            $gameOverRankUser = Bll_Cache_Dynamite::getGameOverRankUser($uid, $idArray, 0, 10, $rankType, 'DESC');
        }

        require_once 'Bll/User.php';

        if (!empty($rewardRankUser)) {
            Bll_User::appendPeople($rewardRankUser, 'uid');
            $rankUser = $this->truncateResult($rewardRankUser);
        }

        if (!empty($gameOverRankUser)) {
            Bll_User::appendPeople($gameOverRankUser, 'uid');
            $rankUser = $this->truncateResult($gameOverRankUser);
        }

        return $rankUser;
    }

    /***********************    begin batch work ******************************/

    /**
     * used in batch, update rank temp table
     */
    public function refreshRankTempTable()
    {
        require_once 'Bll/Cache/Dynamite.php';

        require_once 'Dal/Dynamite/Rank.php';
        $dalRank = Dal_Dynamite_Rank::getDefaultInstance();

        try {
            $tableFlag = Bll_Cache_Dynamite::getRankTempTable();
            info_log("begin fresh rank temp table, table flag=========" . $tableFlag, "lp_rank");

            $dalRank->trancateTempTable(0);
        }
        catch (Exception $e1) {
            info_log("Firstly TRUNCATE Table dynamite_rank_tmp_1 error happend : " . $e1->getMessage(), "lp_rank");
        }

        try {
            if (!$dalRank->isTableEmpty('dynamite_rank_tmp_1')) {
                $dalRank->doBatchUpdateRankTemTable();
            }
        }
        catch (Exception $e4) {
            info_log("insert into dynamite_rank_tmp_1 error" . $e4->getMessage(), "lp_rank");
        }

        $result = $dalRank->isTableEmpty('dynamite_rank_tmp_1');

        if ($result) {
            try {
                Bll_Cache_Dynamite::freshRankTempTable(2);

                $resultAAAAAA = Bll_Cache_Dynamite::getRankTempTable();
                info_log("refreshRankTempTable resultAAAAAA==========" . $resultAAAAAA, "lp_rank_tablename");

                sleep(3);

                $dalRank->insertNewRankTable();
            }
            catch (Exception $e2) {
                info_log("TRUNCATE Table dynamite_rank_tmp error happend : " . $e2->getMessage(), "lp_rank");
            }

            try {
                $result1 = $dalRank->isTableEmpty('dynamite_rank_tmp');

                if ($result1) {

                    Bll_Cache_Dynamite::freshRankTempTable(1);

                    $resultBBBBBBBBB = Bll_Cache_Dynamite::getRankTempTable();
                    info_log("refreshRankTempTable resultBBBBBBBBB==========" . $resultBBBBBBBBB, "lp_rank_tablename");

                    sleep(3);

                    $dalRank->trancateTempTable(0);
                }
            }
            catch (Exception $e3) {
                info_log("Secondly TRUNCATE Table dynamite_rank_tmp_1 error happend : " . $e3->getMessage(), "lp_rank");
            }
        }
        else {
            Bll_Cache_Dynamite::freshRankTempTable(1);
            info_log("dynamite_rank_tmp_1 is null", "lp_rank");
        }
    }

    /**
     * used in batch, update dead number rank temp table
     */
    public function refreshDeadNumTempTable()
    {
        require_once 'Bll/Cache/Dynamite.php';

        require_once 'Dal/Dynamite/Rank.php';
        $dalRank = Dal_Dynamite_Rank::getDefaultInstance();

        try {

            $tableFlag = Bll_Cache_Dynamite::getDeadNumTempTable();
            info_log("begin fresh dead number table, table flag=======" . $tableFlag, "lp_rank");

            $dalRank->trancateTempTable(1);
        }
        catch (Exception $e1) {
            info_log("Firstly TRUNCATE Table dynamite_rank_deadnumber_tmp_1 error happend : " . $e1->getMessage(), "lp_rank");
        }

        try {
            if (!$dalRank->isTableEmpty('dynamite_rank_deadnumber_tmp_1')) {
                $dalRank->doBatchUpdateDeadNumTable();
            }
        }
        catch (Exception $e4) {
            info_log("insert into table dynamite_rank_deadnumber_tmp_1 error," . $e4->getMessage(), "lp_rank");
        }

        $result = $dalRank->isTableEmpty('dynamite_rank_deadnumber_tmp_1');

        if ($result) {
            try {
                Bll_Cache_Dynamite::freshDeadNumTempTable(2);

                $resultAAAAAAAA = Bll_Cache_Dynamite::getDeadNumTempTable();
                info_log("refreshDeadNumTempTable resultAAAAAAAA==========" . $resultAAAAAAAA, "lp_rank_tablename");

                sleep(3);

                $dalRank->insertNewDeadNumTable();
            }
            catch (Exception $e2) {
                info_log("TRUNCATE Table dynamite_rank_deadnumber_tmp error happend : " . $e2->getMessage(), "lp_rank");
            }

            try {
                $result1 = $dalRank->isTableEmpty('dynamite_rank_deadnumber_tmp');
                if ($result1) {

                    Bll_Cache_Dynamite::freshDeadNumTempTable(1);

                    $resultbbbbbbbbb = Bll_Cache_Dynamite::getDeadNumTempTable();
                    info_log("refreshDeadNumTempTable resultbbbbbbbbb==========" . $resultbbbbbbbbb, "lp_rank_tablename");

                    sleep(3);

                    $dalRank->trancateTempTable(1);
                }
            }
            catch (Exception $e3) {
                info_log("Secondly TRUNCATE Table dynamite_rank_deadnumber_tmp_1 error happend : " . $e3->getMessage(), "lp_rank");
            }
        }
        else {
            Bll_Cache_Dynamite::freshDeadNumTempTable(1);
            info_log("dynamite_rank_deadnumber_tmp_1 is null", "lp_rank");
        }
    }

    /**
     * used in batch, update all user rank temp table
     */
    public function refreshAllUserRankTempTable()
    {
        require_once 'Bll/Cache/Dynamite.php';

        require_once 'Dal/Dynamite/Rank.php';
        $dalRank = Dal_Dynamite_Rank::getDefaultInstance();

        try {

            $tableFlag = Bll_Cache_Dynamite::getAllUserRankTable();
            info_log("begin fresh all user rank table, table flag======" . $tableFlag, "lp_rank");

            $dalRank->trancateTempTable(2);

        }
        catch (Exception $e1) {
            info_log("Firstly TRUNCATE Table dynamite_rank_all_tmp_1 error happend : " . $e1->getMessage(), "lp_rank");
        }

        try {
            if (!$dalRank->isTableEmpty('dynamite_rank_all_tmp_1')) {
                $dalRank->doBatchUpdateAllUserRankTable();
            }
        }
        catch (Exception $e4) {
            info_log("insert into dynamite_rank_all_tmp_1  error, " . $e4->getMessage(), "lp_rank");
        }

        $result = $dalRank->isTableEmpty('dynamite_rank_all_tmp_1');

        if ($result) {
            try {
                Bll_Cache_Dynamite::freshAllUserRankTempTable(2);

                $resultAAAAAAAA = Bll_Cache_Dynamite::getAllUserRankTable();
                info_log("refreshAllUserRankTempTable resultAAAAAAAA==========" . $resultAAAAAAAA, "lp_rank_tablename");

                sleep(3);

                $dalRank->insertNewRankAllTable();
            }
            catch (Exception $e2) {
                info_log("TRUNCATE Table dynamite_rank_all_tmp error happend : " . $e2->getMessage(), "lp_rank");
            }

            try {
                $result1 = $dalRank->isTableEmpty('dynamite_rank_all_tmp');
                if ($result1) {

                    Bll_Cache_Dynamite::freshAllUserRankTempTable(1);

                    $resultBBBBBBBBBB = Bll_Cache_Dynamite::getAllUserRankTable();
                    info_log("refreshAllUserRankTempTable resultBBBBBBBBBB==========" . $resultBBBBBBBBBB, "lp_rank_tablename");

                    sleep(3);

                    $dalRank->trancateTempTable(2);
                }
            }
            catch (Exception $e3) {
                info_log("Secondly TRUNCATE Table dynamite_rank_all_tmp_1 error happend : " . $e3->getMessage(), "lp_rank");
            }
        }
        else {
            Bll_Cache_Dynamite::freshAllUserRankTempTable(1);
            info_log("dynamite_rank_all_tmp_1 is null", "lp_rank");
        }
    }

    /**
     * get user friends id array or alliance id array
     * @param string $uid
     * @param int $rankType
     * @return array
     */
    public function getIdArray($uid, $rankType)
    {
        //user friends id array or alliance id array
        $idArray = null;
        if ($rankType == 1) {
            require_once 'Bll/Friend.php';
            $friendIds = Bll_Friend::getFriends($uid);
            $idArray = $friendIds;
        }

        return $idArray;
    }

    /**
     * truncate user name and reward
     * @param array $result
     * @return array
     */
    public function truncateResult($result)
    {
        $now = time() - 15 * 60;

        require_once 'Bll/Cache/Dynamite.php';

        foreach ($result as $key => $value) {
            //truncate user bonus
            if ($value['bonus'] != null) {
                if ($value['bonus'] >= 100000000000000000000) {
                    $result[$key]['bonus'] = round($value['bonus'] / 100000000000000000000, 2) . '垓＄';
                }
                else if ($value['bonus'] >= 10000000000000000) {
                    $result[$key]['bonus'] = round($value['bonus'] / 10000000000000000, 2) . '京＄';
                }
                else if ($value['bonus'] >= 1000000000000) {
                    $result[$key]['bonus'] = round($value['bonus'] / 1000000000000, 2) . '兆＄';
                }
                else if ($value['bonus'] >= 100000000) {
                    $result[$key]['bonus'] = round($value['bonus'] / 100000000, 2) . '億＄';
                }
                else if ($value['bonus'] >= 10000) {
                    $result[$key]['bonus'] = round($value['bonus'] / 10000) . '万＄';
                }
                else {
                    $result[$key]['bonus'] = $value['bonus'] . '＄';
                }
            }
            //truncate user name
            $result[$key]['displayName'] = html_entity_decode($value['displayName'], ENT_QUOTES);

            if (mb_strlen($result[$key]['displayName'], 'UTF-8') > 10) {
                $result[$key]['displayName'] = mb_substr($result[$key]['displayName'], 0, 10, 'UTF-8') . '…';
            }

            $result[$key]['displayName'] = htmlspecialchars($result[$key]['displayName'], ENT_QUOTES);

            //add user online status
            $userBasicInfo = Bll_Cache_Dynamite::getUserBasicInfo($value['uid']);

            $result[$key]['online'] = $userBasicInfo['last_login_time'] > $now ? 1 : 0;
        }

        return $result;
    }

}
<?php
/** @see Zend_Json */
require_once 'Zend/Json.php';
/** @see Bll_Abstract.php */
require_once 'Bll/Abstract.php';

/** @see Dal_Johnson_Johnson.php */
require_once 'Dal/Johnson/Johnson.php';

/**
 * johnson Operation
 * @package    Bll
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/10/22    lp
 */
class Bll_Johnson_Johnson extends Bll_Abstract
{

    /**
     * today first login
     *
     * @param string $uid
     * @return integer
     */
    public function isTodayFirstLogin($uid, $appId)
    {
        //if today first login,$firstLogin = 0
        $result = array('firstLogin' => 1);

        $dalJohnson = Dal_Johnson_Johnson::getDefaultInstance();

        $isIn = $dalJohnson->isInApp($uid);
        //user have join app
        if ($isIn) {
            $lastLoginTime = $dalJohnson->getLastLoginTime($uid);
            $todayDate = date("Y-m-d");
            $todayTime = strtotime($todayDate);

            if ($lastLoginTime < $todayTime) {
                $result['firstLogin'] = 0;

                $dalJohnson->deleteUserItem($uid);
            }
            else {
                $itemIdArray = $dalJohnson->getUserItem($uid);

                if (!empty($itemIdArray)) {
                    $newItemIdArray = array();
                    foreach ($itemIdArray as $value) {
                        $newItemIdArray[] = $value['item_id'];
                    }

                    $itemIdString = implode(',', $newItemIdArray);

                    $result['itemIdString'] = $itemIdString;
                }
                else {
                	$result['itemIdString'] = "";
                }
            }
        }//user first join in app
        else {
            $result['firstLogin'] = 0;

            $insertInfo = array('uid' => $uid, 'create_time' => time());
            $dalJohnson->insertUser($insertInfo);

            //check if user is be invited
            require_once 'Bll/Invite.php';
            $inviterArray = Bll_Invite::get($appId, $uid);

            if (!empty($inviterArray)) {

                foreach ($inviterArray as $value) {
                    $dalJohnson->insertInvite($value, $uid);
                }
            }

        }

        $updateInfo = array('last_login_time' => time());
        $dalJohnson->updateUserInfo($uid, $updateInfo);

        return $result;
    }

    /**
     * get incentive ids by invite user count
     *
     * @param string $appId
     * @param string $uid
     * @return string
     */
    public function getIncentiveId($appId, $uid)
    {

        $dalJohnson = Dal_Johnson_Johnson::getDefaultInstance();

        //get friends invited by user
        $inviteUser = $dalJohnson->getUserInviteUser($uid);

        //get incentive ids
        $incentiveId = $this->getIncentiveIdByInviteUserCount(count($inviteUser));

        if (!$incentiveId) {
            $incentiveId = '';
        }

        return $incentiveId;
    }

    /**
     * after game over, operate user infomation, used by flash
     *
     * @param string $uid
     * @param string $encrypt
     * @param integer $honorId
     * @param string $restItemId
     * @param integer $score
     * @return array
     */
    public function afterGameOver($uid, $encrypt, $honorId, $restItemId, $score)
    {
        $result = array('result' => -1);

        //check data
        $key = 'jajappli' . $uid . $score . $honorId . $restItemId;

        debug_log('$uid:'.$uid . '  $encrypt:'.$encrypt.'   $honorId:'.$honorId.'  $restItemId:'.$restItemId.'  $score:'.$score.'  md5(key):'.md5($key));
        
        if (md5($key) != $encrypt) {
            return $result;
        }

        require_once 'Bll/Friend.php';
        $friendIdArray = Bll_Friend::getFriends($uid);

        $dalJohnson = Dal_Johnson_Johnson::getDefaultInstance();
        $userInfo = $dalJohnson->getUser($uid);

        $userLastScore = $userInfo['score'];
        //get old max score user
        $maxScoreUser = $dalJohnson->getMaxScoreUidInFriend($friendIdArray);

        try {
            $this->_wdb->beginTransaction();

            //delete have used item
            if ($restItemId) {
                $itemIdArray = explode(',', $restItemId);
                $dalJohnson->deleteUserItem($uid, $itemIdArray);
            }

            //update user score
            if ($score > $userInfo['score']) {
                $dalJohnson->updateUserInfo($uid, array('score' => $score));
            }

            //update user honor
            if ($honorId > $userInfo['honor_id']) {
                $dalJohnson->updateUserInfo($uid, array('honor_id' => $honorId));
            }
            $this->_wdb->commit();

            $result['result'] = 0;
            //send activity
            if ($score > $maxScoreUser['score']) {
                require_once 'Bll/User.php';

                Bll_User::appendPerson($userInfo, 'uid');
                Bll_User::appendPerson($maxScoreUser, 'uid');

                $result['activity'] = $userInfo['displayName'] . 'さんが' . $maxScoreUser['displayName'] . 'さんの最高得点を更新しました';
                $result['reciptents'] = $maxScoreUser['uid'];

                $result['result'] = 1;
            }
            else if ($score > $userLastScore) {
            	$result['activity'] = '';
            	$result['reciptents'] = '';
            	$result['result'] = 2;
            }
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return array('result' => -1);
        }

        return $result;
    }

    /**
     * rank
     *
     * @param string $uid
     * @return array
     */
    public function rank($uid)
    {
        $response = array();
        //get fids
        require_once 'Bll/Friend.php';
        $fids = Bll_Friend::getFriends($uid);
        $fids[] = $uid;

        $dalJohnson = Dal_Johnson_Johnson::getDefaultInstance();
        //get rank user count
        $userCnt = $dalJohnson->getRankUserCount($fids, 2);
        //get user`s rank and score
        $userRankScore = $dalJohnson->getRankScore($uid, 2, $fids);

        //get user list data
        if ($userCnt < 6) {

            //real data
            $userList = $dalJohnson->getUserList(1, 5, 2, $fids);
            Bll_User::appendPeople($userList);

            //make false data
            for ($i = count($userList); $i < 5; $i++) {
                $userList[$i]['uid'] = "??????さん";
                $userList[$i]['rank'] = "?";
                $userList[$i]['score'] = "????";
                $userList[$i]['displayName'] = "????さん";
                $userList[$i]['miniThumbnailUrl'] = Zend_Registry::get('static') . "/apps/johnson/img/default_pic.gif";
            }

            $response['rankBegin'] = 1;
            $response['rankEnd'] = $userCnt;

        }
        else {
            if ($userCnt >= 6 && ($userRankScore['rank'] + 2) <= $userCnt) {
                $userList = $dalJohnson->getUserList(max($userRankScore['rank'] - 2, 1), 5, 2, $fids);
            }
            else if ($userCnt >= 6 && ($userRankScore['rank'] + 2) > $userCnt) {
                $userList = $dalJohnson->getUserList($userCnt - 4, 5, 2, $fids);
            }

            Bll_User::appendPeople($userList);

            $response['rankBegin'] = $userList[0]['rank'];
            $response['rankEnd'] = $userList[count($userList) - 1]['rank'];

        }
        $response['userCnt'] = $userCnt;
        $response['rankInfo'] = $userList;

        return $response;

    }

    /**
     * otherTypeRank
     *
     * @param string $uid
     * @param string $rankType
     * @return array
     */
    public function otherTypeRank($uid, $rankType)
    {
        require_once 'Bll/User.php';

        $dalJohnson = Dal_Johnson_Johnson::getDefaultInstance();

        //get friend list
        if ($rankType == 2) {
            require_once 'Bll/Friend.php';
            $fids = Bll_Friend::getFriends($uid);
            $fids[] = $uid;
        }

        //get user count
        $userCnt = $dalJohnson->getRankUserCount($fids, $rankType);

        //get user`s rank and score
        $userRankScore = $dalJohnson->getRankScore($uid, $rankType, $fids);

        //get user list data
        if ($userCnt < 6) {
            $userList = $dalJohnson->getUserList(1, 5, $rankType, $fids);
            Bll_User::appendPeople($userList);

            $rankStart = 1;
            $rankEnd = $userList[count($userList) - 1]['rank'];

            for ($i = count($userList); $i < 5; $i++) {
                $userList[$i]['uid'] = "??????さん";
                $userList[$i]['rank'] = "?";
                $userList[$i]['score'] = "????";
                $userList[$i]['displayName'] = "????さん";
                $userList[$i]['miniThumbnailUrl'] = Zend_Registry::get('static') . "/apps/johnson/img/default_pic.gif";
            }
        }
        else {
            if ($userCnt >= 6 && $userRankScore['rank'] + 2 <= $userCnt) {
                $userList = $dalJohnson->getUserList(max($userRankScore['rank'] - 2, 1), 5, $rankType, $fids);
            }
            else if ($userCnt >= 6 && $userRankScore['rank'] + 2 > $userCnt) {
                $userList = $dalJohnson->getUserList($userCnt - 4, 5, $rankType, $fids);
            }
            Bll_User::appendPeople($userList);

            $rankStart = $userList[0]['rank'];
            $rankEnd = $userList[4]['rank'];
        }

        //set long name to with '..'
        for ($i = 0; $i < 5; $i++) {
            $userList[$i]['displayName'] = $this->_stringCut($userList[$i]['displayName']);
        }

        $response = array('info' => $userList, 'begin' => $rankStart, 'end' => $rankEnd, 'userCnt' => $userCnt);

        return $response;
    }

    /**
     * rank move to up or down
     *
     * @param string $uid
     * @param string $rankStart
     * @param string $rankEnd
     * @param string $direction
     * @param string $userCnt
     * @param string $type
     * @return array
     */
    public function goNext($uid, $rankStart, $rankEnd, $direction, $type)
    {
        require_once 'Bll/User.php';

        $dalJohnson = Dal_Johnson_Johnson::getDefaultInstance();

        //get friend list
        if ($type == 2) {
            require_once 'Bll/Friend.php';
            $fids = Bll_Friend::getFriends($uid);
            $fids[] = $uid;
        }

        if ($direction == 'down') {
            $userList = $dalJohnson->getUserList($rankEnd + 1, 5, $type, $fids);
            Bll_User::appendPeople($userList);

            $rankStart = $userList[0]['rank'];
            $rankEnd = $userList[count($userList) - 1]['rank'];
            for ($i = count($userList); $i < 5; $i++) {
                $userList[$i]['uid'] = "??????さん";
                $userList[$i]['rank'] = "?";
                $userList[$i]['score'] = "????";
                $userList[$i]['displayName'] = "????さん";
                $userList[$i]['miniThumbnailUrl'] = Zend_Registry::get('static') . "/apps/johnson/img/default_pic.gif";
            }
        }
        else if ($direction == 'up') {
            $userList = $dalJohnson->getUserList(max(1, $rankStart - 5), 5, $type, $fids);
            Bll_User::appendPeople($userList);

            $rankStart = $userList[0]['rank'];
            $rankEnd = $userList[4]['rank'];
        }

        //get user count
        $userCnt = $dalJohnson->getRankUserCount($fids, $type);

        $response = array('info' => $userList, 'begin' => $rankStart, 'end' => $rankEnd, 'rankCount' => $userCnt);

        return $response;
    }

    public function getIncentiveIdByInviteUserCount($count)
    {
        $incentiveIdArray = array('1' => '1', '2' => '1,2', '3' => '1,2,3', '4' => '1,2,3', '5' => '1,2,3,4', '6' => '1,2,3,4', '7' => '1,2,3,4,5', '8' => '1,2,3,4,5,6', '9' => '1,2,3,4,5,6,7', '10' => '1,2,3,4,5,6,7,8');

        if ($count > 10) {
            return $incentiveIdArray['10'];
        }

        return $incentiveIdArray[$count];
    }

    /**
     * string cut
     *
     * @param string $string
     * @return string
     */
    function _stringCut($string)
    {
        $maxLength = 20;
        $num = 0;
        $charI = $string;
        for ($i = 0; $i < mb_strlen($string, 'utf8'); $i++) {
            if (ord($charI) > 128) {
                $num += 2;
            }
            else {
                $num += 1;
            }
            $charI = mb_substr($string, $i + 1, mb_strlen($string, 'utf8'), 'utf8');
            if ($num > $maxLength) {
                $string = mb_substr($string, 0, $i, 'utf8');
                $string .= '..';
                break;
            }
        }
        return $string;
    }

    public function refreshRankTempTable()
    {

        try {
            require_once 'Dal/Johnson/Johnson.php';
            $dalJohnson = Dal_Johnson_Johnson::getDefaultInstance();

            $dalJohnson->doBatchUpdateRankTemTable();

            $result = $dalJohnson->isTableEmpty();

            if ($result) {
                $dalJohnson->insertNewRankTable();
            }
            else {
                info_log("johnson_rank_tmp_1 is null", "lp_johnson");
            }
        }
        catch (Exception $e) {
            info_log('johnson refreshRankTempTable Error Happened!' . $e->getMessage(), "lp_johnson");
        }
    }

}
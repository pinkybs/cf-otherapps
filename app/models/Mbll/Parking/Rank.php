<?php

require_once 'Mbll/Abstract.php';

/**
 * parking ranking Operation
 *
 * @package    Mbll
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/05/19    lp
 */
class Mbll_Parking_Rank extends Mbll_Abstract
{
    /**
     * get rank info
     *
     * @param string $uid
     * @param integer $type1
     * @param integer $type2
     * @return array
     */
    public function getRankInfo($uid, $type1, $type2)
    {
        require_once 'Bll/Parking/Friend.php';
        $friendIds = Bll_Parking_Friend::getFriendIds($uid);
        $friendIds = explode(',', $friendIds);

        require_once 'Dal/Parking/Puser.php';
        $dalParkPuser = new Dal_Parking_Puser();
        $count = $dalParkPuser->getRankingCount($uid, $type1, $friendIds);

        require_once 'Mdal/Parking/Puser.php';
        $mdalParkPuser = new Mdal_Parking_Puser();
        //get rank info about user,get user's rank number
        $userRankNm = $mdalParkPuser->getUserRankNm($uid, $friendIds, $type1, $type2);

        $allCount = 5;

        //rank users count>5
        if ($count > 5) {
            //user's rank number <= 3
            if ($userRankNm <= 3) {
                $start = 0;
            }
            //user's rank number > 3
            else {
                if ($userRankNm + 2 < $count) {
                    $start = ($userRankNm + 2) - 5;
                }
            	else {
            	    $start = $count - 5;
            	}
            }
        }
        //rank users count<5
        else {
        	$start = 0;
        }

        require_once 'Mdal/Parking/Rank.php';
        $mdalRank = new Mdal_Parking_Rank();
        //get rank info
        $rankInfo = $mdalRank->getRankingUser($uid, $friendIds, $type1, $type2, $allCount, 'DESC', $start);

        require_once 'Bll/User.php';
        Bll_User::appendPeople($rankInfo, 'uid');

        $response = array('rankInfo' => $rankInfo, 'userRankNm' => $userRankNm, 'start' => $start);

        return $response;
    }
    /**
     * append neighbor top rank
     *
     * @param array $rank
     * @param integer $type
     * @return array
     */
    public function appendNeighborRank($rank, $type)
    {
    	require_once 'Bll/Parking/Index.php';
    	$bllIndex = new Bll_Parking_Index();

        if ($type == 1) {
            $neighbor1 = array('uid' => -1, 'ass' => '500000', 'online' => 0, 'type' => 2, 'displayName' => '駐車太郎', 'thumbnailUrl' => Zend_Registry::get('static') . '/apps/parking/img/neighbor/taro.gif');
            $neighbor2 = array('uid' => -2, 'ass' => '700000', 'online' => 0, 'type' => 2, 'displayName' => '駐車花子', 'thumbnailUrl' => Zend_Registry::get('static') . '/apps/parking/img/neighbor/hanako.gif');

            $rank = $bllIndex->msort(array_merge(array($neighbor1), array($neighbor2), $rank));
        }
        else {
            $neighbor1 = array('uid' => -1, 'ass' => '400000', 'online' => 0, 'type' => 2, 'displayName' => '駐車太郎', 'thumbnailUrl' => Zend_Registry::get('static') . '/apps/parking/img/neighbor/taro.gif');
            $neighbor2 = array('uid' => -2, 'ass' => '500000', 'online' => 0, 'type' => 2, 'displayName' => '駐車花子', 'thumbnailUrl' => Zend_Registry::get('static') . '/apps/parking/img/neighbor/hanako.gif');

            $rank = $bllIndex->msort(array_merge(array($neighbor1), array($neighbor2), $rank));
       }
       return $rank;
   }
    /**
     * a page turning method
     *
     */
    public function newPage($startNum, $uid, $type1, $type2)
    {
        require_once 'Bll/Parking/Friend.php';
        $friendIds = Bll_Parking_Friend::getFriendIds($uid);
        $friendIds = explode(',', $friendIds);

        require_once 'Dal/Parking/Puser.php';
        $dalParkPuser = new Dal_Parking_Puser();
        $count = $dalParkPuser->getRankingCount($uid, $type1, $friendIds);

        require_once 'Mdal/Parking/Rank.php';
        $mdalRank = new Mdal_Parking_Rank();
        //get rank info
        $rankInfo = $mdalRank->getRankingUser($uid, $friendIds, $type1, $type2, $allCount=5, 'DESC', $startNum-1);

        require_once 'Bll/User.php';
        Bll_User::appendPeople($rankInfo, 'uid');

        $response = array('rankInfo' => $rankInfo, 'rankCount' => $count);

        return $response;
    }

}
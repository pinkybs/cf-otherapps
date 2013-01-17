<?php

/**
 * Bll BatchWork
 * DB Auto Statistic Batch Work Logic Layer
 *
 * @package    Bll/Dynamite
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/07/20    Liz
 */
class Bll_Dynamite_BatchWork
{
    /**
     * db config
     * @var array
     */
    protected $_config;

    /**
     * db read adapter
     * @var Zend_Db_Abstract
     */
    protected $_rdb;

    /**
     * db write adapter
     * @var Zend_Db_Abstract
     */
    protected $_wdb;

    /**
     * construct
     *
     * @param array $config ( db config )
     */
    public function __construct($dbConfig = null)
    {
        if (is_null($dbConfig)) {
            $dbConfig = getDBConfig();
        }
        $this->_rdb = $dbConfig['readDB'];
        $this->_wdb = $dbConfig['writeDB'];
        $this->_config = $dbConfig;
    }

    /**
     * do batch resurrect hitman
     *
     * @param integer $runingDate
     * @return boolean
     */
    public function doBatchResurrectHitman($runingDate)
    {
        require_once 'Dal/Dynamite/User.php';
        $dalDynamiteUser = Dal_Dynamite_User::getDefaultInstance();

        require_once 'Bll/Dynamite/Index.php';
        $bllDynamiteIndex = new Bll_Dynamite_Index();

        try {
            for ($i = 1; $i < 5; $i++) {
                //get need resurrect uid list
                $uids = $dalDynamiteUser->getNeedResurrectUids($i, 72);

                if ($uids) {
                    for ($j = 0, $jCount = count($uids); $j < $jCount; $j++) {
                        $result = '0';
                        //user game over
                        if ($uids[$j]['hitman_count'] == 0) {
                            $result = $bllDynamiteIndex->needRestartGame($uids[$j]['uid'], true);
                        }
                        //user not game over, but hitman dead
                        else {
                            $info = array('hitman_life' . $i => $uids[$j]['max_life'], 'hitman_dead_time' . $i => 0, 'hitman_count' => $uids[$j]['hitman_count'] + 1);
                            $dalDynamiteUser->updateUserMoreInfo($uids[$j]['uid'], $info);
                        }
                    }
                }
            }
        }
        catch (Exception $e) {
            info_log("doBatchResurrectHitman error happend", "dynamite_batch");
            info_log($e->getMessage(), "dynamite_batch");
        }
    }

    /**
     * do batch auto trigger bomb
     * @param integer $runingDate
     * @return boolean
     */
    public function doBatchAutoTriggerBomb($runingDate)
    {

        require_once 'Dal/Dynamite/User.php';
        $dalDynamiteUser = Dal_Dynamite_User::getDefaultInstance();

        require_once 'Dal/Dynamite/Bomb.php';
        $dalDynamiteBomb = Dal_Dynamite_Bomb::getDefaultInstance();

        require_once 'Bll/Dynamite/Index.php';
        $bllIndex = new Bll_Dynamite_Index();

        $feedUser = array();

        $now = time();

        for ($i = 1; $i <= 4; $i++) {
            //user's bomb_count$i == 4
            $uids = $dalDynamiteUser->getAutoTriggerBombUser($i);

            if ($uids) {

                $foreachCount = ceil(count($uids) / 10);

                for ($j = 0; $j < $foreachCount; $j++) {

                    $this->_wdb->beginTransaction();
                    try {
                        $arrayStart = $j * 10;
                        $array = array_slice($uids, $arrayStart, 10);

                        for ($k = 0; $k < count($array); $k++) {
                            //who set bomb in user's hitman$i and bomb power=5
                            $hitmanBombPower = $dalDynamiteUser->getHitmanBombPower($array[$k]['uid'], $i);

                            if (!empty($hitmanBombPower)) {
                                $allBombPower = 0;
                                foreach ($hitmanBombPower as $value) {
                                    $allBombPower = $allBombPower + $value['power'];
                                }
                                //if the last bomb's set_time distance now >= 1 hour, then can auto trigger
                                $startPower = $hitmanBombPower[0]['bomb_power'];
                                if ($startPower == 0) {
                                    $lastSetTime = $hitmanBombPower[0]['set_time'] + $hitmanBombPower[0]['power_time'] * 60 * 5;
                                }
                                else {
                                    $lastSetTime = $hitmanBombPower[0]['set_time'] + $hitmanBombPower[0]['power_time'] * 60 * (5 - $startPower);
                                }


                                $canBomb = $now - $lastSetTime >= (60 * 60) ? 1 : 0;

                                if ($allBombPower == 20 && $canBomb) {

                                    //update target user hitman count
                                    $dalDynamiteUser->updateUserHitmanCount($array[$k]['uid'], -1);

                                    $info = array('hitman_life' . $i => 0, 'hitman_dead_time' . $i => time(), 'hitman_bomb_count' . $i => 0);
                                    $dalDynamiteUser->updateUserMoreInfo($array[$k]['uid'], $info);

                                    foreach ($hitmanBombPower as $value) {
                                        //delete set bomb user's bomb by bomb id
                                        $deleteResult = $dalDynamiteBomb->deleteBombById($value['id']);

                                        if ($deleteResult) {
                                            //update uid bomb count
                                            $dalDynamiteUser->updateUserBombCount($value['uid'], -1);
                                        }
                                    }

                                    $feedUser[] = $array[$k]['uid'];

                                    //if target user game over
                                    if ($array[$k]['hitman_count'] == 1) {

                                        //clean target user set bomb
                                        require_once 'Dal/Dynamite/Item.php';
                                        $dalItem = Dal_Dynamite_Item::getDefaultInstance();

                                        //target user set bomb to other
                                        $userSetBombInfo = $dalItem->getUserSetBombInfoForUpdate($array[$k]['uid']);

                                        foreach ($userSetBombInfo as $value) {
                                            $dalItem->updateUserHitmanBomb($value['bomb_uid'], $value['bomb_hitman']);
                                        }

                                        $dalItem->deleteBombAboutUser($array[$k]['uid']);
                                        //update target user bonus
                                        $dalDynamiteUser->updateUserBonus($array[$k]['uid'], (-$array[$k]['bonus'] * 0.5));

                                        $enemyUpdateInfo = array('isgameover' => 1);
                                        $dalDynamiteUser->updateUserBasicInfo($array[$k]['uid'], $enemyUpdateInfo);
                                    }
                                }
                            }
                        }
                        $this->_wdb->commit();
                    }
                    catch (Exception $e) {
                        $this->_wdb->rollBack();
                        info_log('doBatchAutoTriggerBomb Error Happened!', "dynamite_batch");
                        info_log($e->getMessage(), "dynamite_batch");
                        info_log($e->__toString(), "dynamite_batch");
                    }
                }
            }

        }

        try {
            if (!empty($feedUser)) {
                //insert feed
                $create_time = date('Y-m-d H:i:s');

                for ($i = 0; $i < count($feedUser); $i++) {

                    $minifeed = array('uid' => $feedUser[$i],
                                      'template_id' => 59,
                                      'actor' => $feedUser[$i],
                                      'target' => '',
                                      'feed_type' => 'ダイナマイト暴発',
                                      'icon' => Zend_Registry::get('static') . "/apps/dynamite/img/icon/hitman.gif",
                                      'title' => '',
                                      'create_time' => $create_time);

                    require_once 'Dal/Dynamite/Feed.php';
                    $dalDynamiteFeed = Dal_Dynamite_Feed::getDefaultInstance();

                    $feedTable = $bllIndex->getFeedTable($minifeed['uid']);
                    $dalDynamiteFeed->insertFeed($minifeed, $feedTable);
                }
            }
        }
        catch (Exception $e1) {

        }
    }

    /**
     * do batch auto update rank temp table, used in rank
     */
    public function doBatchUpdateRankTemTable()
    {

        try {

            require_once 'Bll/Dynamite/Rank.php';
            $bllRank = new Bll_Dynamite_Rank();

            $bllRank->refreshRankTempTable();
            $bllRank->refreshDeadNumTempTable();
            $bllRank->refreshAllUserRankTempTable();

        }
        catch (Exception $e) {
            info_log('doBatchUpdateRankTemTable Error Happened!' . $e->getMessage(), "dynamite_batch");
        }
    }

    /**
     * do batch clean unused feed, 7 days towards
     */
    public function doBatchCleanFeed()
    {
        try {
            require_once 'Dal/Dynamite/Feed.php';
            $dalFeed = Dal_Dynamite_Feed::getDefaultInstance();

            $startDate = $this->dateTowards(date("Y-m-d"), -7);

            for ($i = 0; $i <= 9; $i++) {
                $feedTable = "dynamite_feed_" . $i;

                $startId = $dalFeed->getStartId($feedTable, $startDate);

                if (!empty($startId)) {
                    $dalFeed->cleanFeed($feedTable, $startId);
                }
            }
        }
        catch (Exception $e) {
            info_log('do batch clean feed Error Happened!' . $e->getMessage(), "dynamite_batch");
        }
    }

    public function dateTowards($date, $num, $format = 'Y-m-d')
    {
        return date($format, strtotime($date) + $num * 3600 * 24);
    }
}
<?php

/**
 * Board logic's Operation
 *
 *
 * @package    Bll
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/02/10     Liz
 */
class Bll_Chomeboard_Chomeboard
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
    
    protected static $_instance;

    /**
     * init the Vote's variables
     *
     * @param array $config ( config info )
     * @return void
     */
    public function __construct($config = null)
    {
        if (is_null($config)) {
            $config = getDBConfig();
        }
        
        $this->_config = $config;
        $this->_rdb = $config['readDB'];
        $this->_wdb = $config['writeDB'];
    
    }

    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * create new chome board
     *
     * @param int $bid
     * @param array $info
     * @param steam $image
     * @param string $url
     * @return integer
     */
    public function newChomeBoard($bid, $info, $image, $url)
    {
        $result = false;
        try {
            require_once 'Dal/Chomeboard/Chomeboard.php';
            $dalChomeboard = Dal_Chomeboard_Chomeboard::getDefaultInstance();
            
            require_once 'Dal/Chomeboard/User.php';
            $dalChomeboardUser = Dal_Chomeboard_User::getDefaultInstance();
            
            $this->_wdb->beginTransaction();
            
            //get currecnt chome board's sort id
            $sortId = $dalChomeboard->getSortIdByBid($bid);
            
            //new chome board's sort id
            $info['sort_id'] = $sortId + 1;
            
            //update sort id plus 1 which greater than currecnt chome board's sort id
            $dalChomeboard->updateSortId($info['uid'], $sortId, 1);
            
            //insert chome board
            $bid = $dalChomeboard->insertChomeBoard($info);
            
            //update board owner's be_commented count
            $dalChomeboardUser->updateBecommentedCount($info['uid'], 1);
            
            //update comment user's commented count
            $dalChomeboardUser->updateCommentedCount($info['comment_uid'], 1);
            
            // バイナリ書き込みモード（wb）で、ファイル名"$name"のファイルを作成
            $fp = fopen($url, 'wb');
            fwrite($fp, $image);
            fclose($fp);
            
            $this->_wdb->commit();
            
            require_once 'Bll/Chomeboard/Activity.php';
            if ($info['uid'] != $info['comment_uid']) {
                $result['activity'] = Bll_Chomeboard_Activity::getActivity($info['comment_uid'], $info['uid'],1);
            } else {
                $result['activity'] = "";
            }
            
            $result['status'] = 1;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return false;
        }
        
        return $result;
    }

    /**
     * get dirs
     * @param string $dir_name
     * @return string
     */
    public function getSaveFolder($photoBasePath)
    {
        $maxFile = 100;
        $dirs = $this->getDirs($photoBasePath);
        $maxPhotoFolder = count($dirs);
        $maxPhotoFolderPath = $photoBasePath . '/' . $maxPhotoFolder;
        $fileCount = $this->getFileCount($maxPhotoFolderPath);
        
        $saveFolder = $maxPhotoFolder;
        if ($fileCount >= $maxFile) {
            $saveFolder = $saveFolder + 1;
            mkdir($photoBasePath . $saveFolder, 0755);
        }
        
        return $saveFolder;
    }
    
    /**
     * get dirs
     * @param string $dir_name
     * @return string
     */
    public function getDirs($dir_name)
    {
        global $dirs;
        $od = opendir($dir_name);

        while ($name = readdir($od))
        {
            $dir_path = $dir_name.'/'.$name;
            if (is_dir($dir_path) && ($name !='.') && ($name !='..') && ($name !='.svn'))
              $dirs[] = $name;
        }
        return $dirs;
    }
    
    /**
     * get file count
     * @param string $dir_name
     * @return string
     */
    public function getFileCount($dir_name)
    {
      global $files;
      $od = opendir($dir_name);
    
      while ($name = readdir($od))
      {
        $file_path = $dir_name.'/'.$name;
        if (is_file($file_path))
          $files[] = $file_path;
      }
      return count($files);
    }
    
    /**
     * create new chome board for friend don't have app
     *
     * @param string $uid
     * @return string
     */
    public function newChomeBoard4Friends($uid)
    {
        $result = "";
        try {
            $this->_wdb->beginTransaction();
            
            require_once 'Bll/Friend.php';
            $bllFriend = new Bll_Friend();
            
            //get chome board friends
            $aryChomeboardFriends = array();
            $aryChomeboardFriends = $bllFriend->getFriends($uid);
            $friendsCount = count($aryChomeboardFriends);
            
            for ($i = 0; $i < $friendsCount; $i++) {
                require_once 'Dal/Chomeboard/Chomeboard.php';
                $dalChomeboard = Dal_Chomeboard_Chomeboard::getDefaultInstance();

                require_once 'Dal/Chomeboard/User.php';
                $dalChomeboardUser = Dal_Chomeboard_User::getDefaultInstance();

                $lastChomeBoard = $dalChomeboard->getLastChomeBoard($aryChomeboardFriends[$i]);
                $count = count($lastChomeBoard);
                
                if (0 == $count) {
                    //pick random key
                    $aryDefaultPic = array(1,2,3,4,5,6,7);
                    $key = array_rand($aryDefaultPic);
                    $filename = "default" . ($key + 1);
                    
                    $info = array('uid' => $aryChomeboardFriends[$i], 'comment_uid' => $aryChomeboardFriends[$i], 'content' => '1/' . $filename . ".png", 'create_time' => date('Y-m-d H:i:s'), 'sort_id' => 0);
                    
                    //insert friend into chome_board_user
                    $dalChomeboardUser->updateUser($aryChomeboardFriends[$i]);
                    
                    //update friends
                    //$friendIds = Bll_Friend::getFriendIds($aryChomeboardFriends[$i]);
                    //if ($friendIds) {
                    //    $bllChomeboardFriend->updateFriendIds($aryChomeboardFriends[$i], $friendIds);
                    //}

                    //insert chome board
                    $dalChomeboard->insertChomeBoard($info);

                    //update board owner's be_commented count
                    $dalChomeboardUser->updateBecommentedCount($aryChomeboardFriends[$i], 1);
                    
                    //update comment user's commented count
                    $dalChomeboardUser->updateCommentedCount($aryChomeboardFriends[$i], 1);
                }
            }
            
            $this->_wdb->commit();
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return false;
        }
        
        return $result;
    }
    
    /**
     * delete chome board
     *
     * @param int $bid
     * @param string $uid
     * @param string $comment_uid
     * @return integer
     */
    public function deleteChomeBoard($bid, $uid, $comment_uid)
    {
        try {
            require_once 'Dal/Chomeboard/Chomeboard.php';
            $dalChomeboard = Dal_Chomeboard_Chomeboard::getDefaultInstance();
            
            require_once 'Dal/Chomeboard/User.php';
            $dalChomeboardUser = Dal_Chomeboard_User::getDefaultInstance();
            
            $this->_wdb->beginTransaction();
            
            //get currecnt chome board's sort id
            $sortId = $dalChomeboard->getSortIdByBid($bid);
            
            //update sort id reduce 1 which greater than currecnt chome board's sort id
            $dalChomeboard->updateSortId($uid, $sortId, -1);
            
            //delete chome board
            $bid = $dalChomeboard->deleteChomeBoard($bid);
            
            //update board owner's be_commented count
            $dalChomeboardUser->updateBecommentedCount($uid, -1);
            
            //update comment user's commented count
            $dalChomeboardUser->updateCommentedCount($comment_uid, -1);
            
            $this->_wdb->commit();
            
            $result = true;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return false;
        }
        
        return $result;
    }
    /**
     * create new message board
     *
     * @param array $info
     * @return integer
     */
    public function getLastChomeBoard($uid)
    {
        require_once 'Dal/Chomeboard/Chomeboard.php';
        $dalChomeboard = Dal_Chomeboard_Chomeboard::getDefaultInstance();
        
        $aryLastChomeBoard = array();
        
        $aryLastChomeBoard = $dalChomeboard->getLastChomeBoard($uid);
        
        Bll_User::appendPeople($aryLastChomeBoard, 'comment_uid');
        
        return $aryLastChomeBoard;
    }

    /**
     * create new message board
     *
     * @param array $info
     * @return integer
     */
    public function getBoardHistory($uid)
    {
        require_once 'Dal/Chomeboard/Chomeboard.php';
        $dalChomeboard = Dal_Chomeboard_Chomeboard::getDefaultInstance();
        
        $aryBoardHistory = array();
        
        $aryBoardHistory = $dalChomeboard->getBoardHistory($uid);
        
        Bll_User::appendPeople($aryBoardHistory, 'comment_uid');
        
        return $aryBoardHistory;
    }

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
        $friendIds = Bll_Friend::getFriends($uid);

        require_once 'Dal/Chomeboard/User.php';
        $dalCbUser = new Dal_Chomeboard_User();
        $count = $dalCbUser->getRankingCount($uid, $type1, $friendIds);

        if ($count>2) {
            //$count = $count-2;
            $rightFirstCount = $count-2;
            //get rank info about user
            $userRankNm = $dalCbUser->getUserRankNm($uid, $friendIds, $type1, $type2);

            //get start number
            $start = $userRankNm>7 ? ($userRankNm-7) : 0;

            //get array count
            $allCount = 24;
            if ( $userRankNm > 7 ) {
                $userRightCount = $rightFirstCount-$userRankNm;

                if ( $userRightCount < 5 ) {
                    $start = $start - (5-$userRightCount);
                    if ( $start > 0 ){
                        $allCount = 12;
                        $rightFirstCount = 12;
                    }
                    else {
                        $start = 0;
                        $allCount = $rightFirstCount;
                    }
                }
                else if ( ($rightFirstCount-$start) <= 24 ) {
                    $allCount = $rightFirstCount-$start;
                }
            }
            else if ( ($rightFirstCount-$start) > 24 ) {
                $allCount = 22;
            }//init count < 8
            else {
                $allCount = $rightFirstCount;
            }

            //get rank info
            $rankInfo = $dalCbUser->getRankingUser($uid, $friendIds, $type1, $type2, $allCount, 'ASC', $start);

            require_once 'Bll/User.php';
            Bll_User::appendPeople($rankInfo, 'uid');

            $uesrRankNm = ($count-$start);
            $response = array('rankInfo' => $rankInfo, 'userRankNm' => $uesrRankNm, 'rankStatus'=>1);
        }
        else {
            $response = array('rankStatus'=>2);
        }
        return $response;
    }

    /**
     * get more rank info
     *
     * @param string $uid
     * @param integer $type1
     * @param integer $type2
     * @param integer $rankId
     * @param integer $allCount
     * @param integer $isRight
     * @return array
     */
    public function getMoreRank($uid, $type1, $type2, $rankId, $allCount, $isRight)
    {
        require_once 'Dal/Chomeboard/User.php';
        $dalCbUser = new Dal_Chomeboard_User();

        //get friend info
        $friendIds = Bll_Friend::getFriends($uid);

        //$allCount = $dalCbUser->getRankingCount($uid, $type1, $friendIds);
        //get start number and array count
        $pageSize = 12;

        if ( $isRight == 1 ) {
            $start = $allCount - $rankId + 1;
            $rankCount = $rankId-1;
            if ( $rankCount < 14 ) {
                $pageSize = $rankCount-2;
            }
        }
        //move left
        else {
            $otherCount = $allCount - $rankId;
            if ( $otherCount > 12) {
                $start = $otherCount - 12;
                $rankCount = $rankId + 12;
            }
            else {
                $start = $start > 0 ? $start : 0;
                $rankCount = $rankId + $otherCount;
                $pageSize = $otherCount;
            }
        }

        //get rank info
        $rankInfo = $dalCbUser->getRankingUser($uid, $friendIds, $type1, $type2, $pageSize, 'ASC', $start);
        $allCount = $allCount;

        if ( $rankInfo ) {
            Bll_User::appendPeople($rankInfo, 'uid');
        }

        $result = array('rankInfo' => $rankInfo, 'count' => $rankCount, 'allCount' => $allCount, 'isRight' => $isRight);

        return $result;

    }

    /**
     * get last rank info
     *
     * @param string $uid
     * @param integer $type1
     * @param integer $type2
     * @param integer $isRight
     * @return array
     */
    public function getLastRank($uid, $type1, $type2, $isRight)
    {
        //get friend info
        $friendIds = Bll_Friend::getFriends($uid);

        //get rank count
        require_once 'Dal/Chomeboard/User.php';
        $dalCbUser = new Dal_Chomeboard_User();
        $count = $dalCbUser->getRankingCount($uid, $type1, $friendIds);
        $allOfCount = $count;
        $count = $count-2;
        $rankNm = $allOfCount;
        //move right last
        if ($isRight == 1) {
            $i = $count - 12;
            if ($i > 0) {
                $start = $i;
                $allCount = 12;
                $rankNm = 14;
            }
            else {
                $start = 0;
                $allCount = $count;
                $rankNm = $allOfCount;
            }
        }//move left first
        else {
            $i = $count - 24;
            if ( $i > 0 ) {
                $start = 0;
                $allCount = 24;
            }
            else {
                $start = 0;
                $allCount = $count;
            }
        }
        //get rank info
        $rankInfo = $dalCbUser->getRankingUser($uid, $friendIds, $type1, $type2, $allCount, 'ASC', $start);
        if ( $rankInfo ) {
            Bll_User::appendPeople($rankInfo, 'uid');
        }

        $rankCount = count($rankInfo);
        $rightCount = $rankCount > 12 ? ($rankCount-12) : 0;

        $countArr = array('rankCount' => count($rankInfo),
                          'rightCount' => $rightCount,
                          'allCount' => $allOfCount);

        $result = array('rankInfo' => $rankInfo,'rankNm' => $rankNm, 'countArr' => $countArr);
        return $result;
    }
}
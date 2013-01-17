<?php

/**
 * Board logic's Operation
 *
 *
 * @package    Bll
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/02/10     Liz
 */
class Bll_Board_Board
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
     * create new message board
     *
     * @param array $info
     * @return integer
     */
    public function newBoard($info)
    {
        $result = false;

        try {
            $dalBoard = Dal_Board_Board::getDefaultInstance();

            $commentStatus = $this->checkForbidWord($dalBoard, $info['content']);

            $this->_wdb->beginTransaction();

            //insert message board
            $bid = $dalBoard->insertBoard($info);

            //insert board watch comment status
            $infoForbid = array('bid' => $bid, 'comment_status' => $commentStatus);
            $dalBoard->insertBoardWatchCommentStatus($infoForbid);

            $this->_wdb->commit();

            require_once 'Bll/Board/Activity.php';
            if ($info['uid'] != $info['comment_uid']) {
                $result['activity'] = Bll_Board_Activity::getActivity($info['comment_uid'], $info['uid'], 1);
            }
            else {
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
     * create new message board by flash
     *
     * @param array $info
     * @param steam $image
     * @param string $url
     * @return integer
     */
    public function newImage($info, $image, $url, $mPicUrl)
    {

        $result = false;
        try {
            $dalBoard = Dal_Board_Board::getDefaultInstance();

            $this->_wdb->beginTransaction();

            //insert message board
            $bid = $dalBoard->insertBoard($info);

            //insert board watch comment status
            $infoForbid = array('bid' => $bid, 'comment_status' => 1);
            $dalBoard->insertBoardWatchCommentStatus($infoForbid);

            $this->_wdb->commit();

            // バイナリ書き込みモード（wb）で、ファイル名"$name"のファイルを作成
            $fp = fopen($url, 'wb');
            fwrite($fp, $image);
            fclose($fp);

            //gif for mobile
            // Create Imagick object
            $im = new Imagick();

            // Convert image into Imagick
            $im->readImageBlob($image);
            $im->setImageFormat('gif');
            $im->adaptiveResizeImage(228, 54);

            // Output the image
            $im->writeImage($mPicUrl);
            //$format = $this->_checkFormat($mPicUrl);
            $im->destroy();

            require_once 'Bll/Board/Activity.php';
            if ($info['uid'] != $info['comment_uid']) {
                $result['activity'] = Bll_Board_Activity::getActivity($info['comment_uid'], $info['uid'], 1);
            }
            else {
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

    protected function _checkFormat($imagefile)
    {
        list($width, $height, $type, $attr) = @getimagesize($imagefile);

        $format = false;
        switch ($type) {
            case IMAGETYPE_GIF :
                if (@imagecreatefromgif($imagefile))
                    $format = 'gif';
                break;

            case IMAGETYPE_JPEG :
                if (@imagecreatefromjpeg($imagefile))
                    $format = 'jpg';
                break;

            case IMAGETYPE_PNG :
                if (@imagecreatefrompng($imagefile))
                    $format = 'png';
                break;

            case IMAGETYPE_BMP :
                require_once ('MyLib/Image/bmp.php');
                if (@imagecreatefrombmp($imagefile))
                    $format = 'bmp';
                break;

            default :
                break;
        }

        return $format;
    }

    /**
     * get comments
     *
     * @param Integer $bid
     * @return array
     */
    public function getCommentInfo($bid)
    {
        $dalBoard = Dal_Board_Board::getDefaultInstance();

        return $dalBoard->getCommentInfo($bid);
    }

    /**
     * create new message board
     *
     * @param array $info
     * @return integer
     */
    public function editSetting($uid, $setting)
    {
        $result = false;
        try {
            $date = date('Y-m-d H:i:s');
            $setting['create_time'] = $date;

            $this->_wdb->beginTransaction();

            $dalBoard = Dal_Board_Board::getDefaultInstance();

            require_once 'Dal/Board/Set.php';
            $dalBoardSet = Dal_Board_Set::getDefaultInstance();

            //get setting data
            $settingData = $dalBoardSet->getUserSetting($uid);

            //not exist in db
            if (empty($settingData)) {
                $setting['uid'] = $uid;
                $dalBoardSet->insertSetting($setting);
            }
            else {
                //update message board
                $dalBoardSet->updateSetting($uid, $setting);
            }

            $titleWatch = $dalBoard->getBoardWatchTitleStatusByUid($uid);
            $watchTitle = array();
            //not exist in db
            if (empty($titleWatch)) {
                $watchTitle['uid'] = $uid;
                $watchTitle['title_status'] = $this->checkForbidWord($dalBoard, $setting['title']);
                $watchTitle['des_status'] = $this->checkForbidWord($dalBoard, $setting['introduce']);

                //insert board_watch_title_status
                $dalBoard->insertBoardWatchTitleStatus($watchTitle);
            }
            else {
                //has modified title
                if ($setting['title'] != $settingData['title']) {
                    $watchTitle['title_status'] = $this->checkForbidWord($dalBoard, $setting['title']);
                }

                //has modified introduce
                if ($setting['introduce'] != $settingData['introduce']) {
                    $watchTitle['des_status'] = $this->checkForbidWord($dalBoard, $setting['introduce']);
                }

                //need to update board_watch_title_status
                if ($watchTitle) {
                    $watchTitle['t_admin_id'] = null;
                    $watchTitle['d_admin_id'] = null;
                    $watchTitle['t_update_time'] = null;
                    $watchTitle['d_update_time'] = null;

                    //update board_watch_title_status
                    $dalBoard->updateBoardWatchTitleStatus($uid, $watchTitle);
                }
            }

            $this->_wdb->commit();
            $result = true;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return false;
        }

        return $result;
    }

    public static function setting($data)
    {
        try {
            require_once 'Dal/Board/Board.php';
            $dalBoard = Dal_Board_Board::getDefaultInstance();
            $dalBoard->set($data);
        }
        catch (Exception $e) {

        }
    }

    public static function initSetting($uid)
    {
        $rand = rand(1, 6);
        $data = array('uid' => $uid, 'title' => '', 'introduce' => '', 'openflag' => 0, 'allowComment' => 0, 'image_url' => '/apps/board/img/skin' . $rand . '.png', 'mobile_image_url' => '/apps/board/mobile/img/template/' . $rand . '/thumb.gif ', 'create_time' => date("Y-m-d H:i:s"));

        self::setting($data);
    }

    public function checkForbidWord($dalBoard, $content)
    {
        $forbidWords = $dalBoard->getForbidWordList();

        //check forbid work
        $commentStatus = 1; //1-未処理 2-容疑
        foreach ($forbidWords as $word) {
            $pos = strpos($content, $word['word']);
            if ($pos !== false) {
                $commentStatus = 2;
                break;
            }
        }
        return $commentStatus;
    }

    public function getMiniContactList($viewerid, $ownerid, $contactNumPerPage)
    {
        $aryFids = Bll_Friend::getFriends($viewerid);
        $maxCount = count($aryFids);
        //$contactCount3Page = $contactNumPerPage * 3;
        $pageindex = 1;
        $maxPage = 1;
        $aryContactUids = array();

        if ($aryFids) {
            $maxPage = ceil($maxCount / $contactNumPerPage);
            if ($viewerid == $ownerid) {
                if ($contactNumPerPage > $maxCount) {
                    $contactNumPerPage = $maxCount;
                }
                for ($i = 0; $i < $contactNumPerPage; $i++) {
                    $aryContactUids[] = array('uid' => $aryFids[$i]);
                }
                $pageindex = 1;
            }
            else {
                $friendKey = array_search($ownerid, $aryFids) + 1;
                $friendInPage = ceil($friendKey / $contactNumPerPage);
                if (1 == $friendInPage) {
                    $start = 0;
                    $end = ($contactNumPerPage > $maxCount) ? $maxCount : $contactNumPerPage;
                }
                else if (1 < $friendInPage && $friendInPage < $maxPage) {
                    $start = ($friendInPage - 1) * $contactNumPerPage;
                    $end = ($contactNumPerPage * $friendInPage > $maxCount) ? $maxCount : $contactNumPerPage * $friendInPage;
                }
                else if ($friendInPage == $maxPage) {
                    $start = ($friendInPage - 1) * $contactNumPerPage;
                    $end = $maxCount;
                }

                for ($i = $start; $i < $end; $i++) {
                    $aryContactUids[] = array('uid' => $aryFids[$i]);
                }
                $pageindex = $friendInPage;

            }
            Bll_User::appendPeople($aryContactUids, 'uid');
        }

        $contactList['pageindex'] = $pageindex;
        $contactList['maxPage'] = $maxPage;
        $contactList['maxCount'] = $maxCount;
        $contactList['contactUids'] = $aryContactUids;

        return $contactList;
    }

    public function getMoreContactList($viewerid, $type, $pageindex, $contactNumPerPage)
    {
        $aryFids = Bll_Friend::getFriends($viewerid);
        $maxCount = count($aryFids);
        $aryContactUids = array();
        $rightCount = 0;
        $leftCount = 0;

        if ($aryFids) {
            //move to right
            if (1 == $type) {
                $start = ($pageindex) * $contactNumPerPage;
                $end = (($start + $contactNumPerPage * 3) > $maxCount) ? $maxCount : $start + $contactNumPerPage * 3;
                $rightCount = ceil(($end - $start) / $contactNumPerPage);
            }
            //move to right
            else if (-1 == $type) {
                $start = ($pageindex - 3 - 1) < 0 ? 0 : ($pageindex - 3 - 1) * $contactNumPerPage;
                $end = ($pageindex - 1) * $contactNumPerPage;
                $leftCount = ceil(($end - $start) / $contactNumPerPage);
            }

            for ($i = $start; $i < $end; $i++) {
                $aryContactUids[] = array('uid' => $aryFids[$i]);
            }

            Bll_User::appendPeople($aryContactUids, 'uid');
        }

        $contactList['rightCount'] = $rightCount;
        $contactList['leftCount'] = $leftCount;
        $contactList['contactUids'] = $aryContactUids;

        return $contactList;
    }

    /**
     * get friend list
     *
     * @param array $aryFriendIds
     * @param int $viewerid
     * @param int $pageindex
     * @param int $contactNumPerPage
     * @return array
     */

    public function getFriendList($aryFriendIds, $uid, $pageindex, $contactNumPerPage)
    {
        $aryFids = $aryFriendIds;
        $maxCount = count($aryFids);
        $aryContactUids = array();

        if ($aryFids) {
            $start = ($pageindex - 1) * $contactNumPerPage;
            $end = (($start + $contactNumPerPage) > $maxCount) ? $maxCount : $start + $contactNumPerPage;

            require_once 'Dal/Board/Board.php';
            $dalBoard = Dal_Board_Board::getDefaultInstance();

            for ($i = $start; $i < $end; $i++) {
                require_once 'Bll/Board/User.php';
                $bllBoardUser = new Bll_Board_User();
                $userInfo = $bllBoardUser->getUser($aryFids[$i]);
                //user add board app
                if ($userInfo) {
                    $isloginapp = true;
                    $commentcount = $dalBoard->getCommentsCount($aryFids[$i]);
                }
                else {
                    $isloginapp = false;
                    $commentcount = 0;
                }

                $aryContactUids[] = array('uid' => $aryFids[$i], 'isLoginApp' => $isloginapp, 'commentCount' => $commentcount);
            }

            Bll_User::appendPeople($aryContactUids, 'uid');
        }

        return $aryContactUids;
    }

    /**
     * get dirs
     * @param string $dir_name
     * @return string
     */
    public function getSaveFolder($photoBasePath, $mobileBasePhotoUrl)
    {
        try {
            $maxFile = 100;
            $dirs = $this->getDirs($photoBasePath);
            $maxPhotoFolder = count($dirs);

            $fileCount = 0;
            if (0 != $maxPhotoFolder) {
                $maxPhotoFolderPath = $photoBasePath . $maxPhotoFolder;
                $fileCount = $this->getFileCount($maxPhotoFolderPath);
            }

            $saveFolder = $maxPhotoFolder;
            if ($fileCount >= $maxFile || 0 == $maxPhotoFolder) {
                $saveFolder = $saveFolder + 1;
                mkdir($photoBasePath . $saveFolder, 0755);
                mkdir($mobileBasePhotoUrl . $saveFolder, 0755);
            }

            return $saveFolder;
        }
        catch (Exception $e) {
        }
    }

    /**
     * get dirs
     * @param string $dir_name
     * @return string
     */
    public function getDirs($dir_name)
    {
        try {
            $dirs = array();
            $od = opendir($dir_name);

            while ($name = readdir($od)) {
                $dir_path = $dir_name . '/' . $name;
                if (is_dir($dir_path) && ($name != '.') && ($name != '..') && ($name != '.svn'))
                    $dirs[] = $name;
            }
            return $dirs;
        }
        catch (Exception $e) {
        }
    }

    /**
     * get file count
     * @param string $dir_name
     * @return string
     */
    public function getFileCount($dir_name)
    {
        try {
            $files = array();
            $od = opendir($dir_name);

            while ($name = readdir($od)) {
                $file_path = $dir_name . '/' . $name;

                if (is_file($file_path))
                    $files[] = $file_path;
            }
            return count($files);
        }
        catch (Exception $e) {
        }
    }

    /**
     * change skin, use for mibile
     * @param string $uid
     * @param string $selectSkin
     * @return string
     */
    public function changeSkin($uid, $selectSkin)
    {
        try {
            $this->_wdb->beginTransaction();

            require_once 'Dal/Board/Set.php';
            $dalBoardSet = Dal_Board_Set::getDefaultInstance();

            $settingInfo = array('image_url' => '/apps/board/img/skin' . $selectSkin . '.png', 'mobile_image_url' => '/apps/board/mobile/img/template/' . $selectSkin . '/thumb.gif');

            $dalBoardSet->updateSetting($uid, $settingInfo);

            $this->_wdb->commit();
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
        }

    }

    /**
     * change skin, use for mibile
     * @param string $uid
     * @param string $selectSkin
     * @return string
     */
    public function settingTitleAndIntroduce($uid, $postTitle, $postDescription)
    {
        try {
            $this->_wdb->beginTransaction();

            require_once 'Dal/Board/Set.php';
            $dalBoardSet = Dal_Board_Set::getDefaultInstance();

            $settingInfo = array('title' => $postTitle, 'introduce' => $postDescription);

            $dalBoardSet->updateSetting($uid, $settingInfo);

            $this->_wdb->commit();
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
        }

    }

    /**
     * get skin id by skin url
     * @param string $skinUrl
     * @return string
     */
    public function getSkinIdByUrl($skinUrl, $isMobile = 0)
    {
        if ($isMobile) {
            $skinId = substr($skinUrl, 32, 1);
        }
        else {
            $skinId = substr($skinUrl, 20, 1);
        }
        return $skinId;
    }

    /**
     * remove emoji
     * @param string $emojiString
     * @return string
     */
    public function removeEmoji($emojiString)
    {

        $moji_pattern = '/\[([ies]:[0-9]{1,3})\]/';

        $matches = array();
        preg_match_all($moji_pattern, $emojiString, $matches);

        if (!empty($matches[0])) {
            foreach ($matches[0] as $value) {
                $emojiString = str_replace($value, '　', $emojiString);
            }
        }
        return $emojiString;
    }

}

<?php

/**
 * Admin Board logic's Operation
 *
 * @package    Admin/Bll
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/03/10    zhangxin
 */
final class Admin_Bll_Board
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
     * class default instance
     * @var self instance
     */
    protected static $_instance;

    /**
     * init the user's variables
     *
     * @param array $config ( config info )
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

    /**
     * return self's default instance
     *
     * @return self instance
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * deal watch comment
     *
     * @param array $aryNewStatus
     * @param integer $adminId
     * @param boolean $isViewer
     * @return integer
     */
    public function dealWatchComment($aryNewStatus, $adminId, $isWatcher = false)
    {
        try {
            require_once 'Admin/Dal/Board.php';
            $dalBoard = new Admin_Dal_Board($this->_config);
            $result = 0;

            $this->_wdb->beginTransaction();

            foreach ($aryNewStatus as $key => $value) {
                //get curren status
                $oldStatus = $dalBoard->getBoardWatchCommentStatusById($key);

                //watcher not allow to deal already dealed data
                if ($isWatcher && (3 <= (int)$oldStatus['comment_status'] || !empty($oldStatus['admin_id']))) {
                    continue;
                }
                //unchanged or no deal
                if (2 >= (int)$value || (int)$oldStatus['comment_status'] == (int)$value) {
                    continue;
                }

                //save status change logs
                $aryLog = array();
                $aryLog['admin_id'] = $adminId;
                $aryLog['watch_bid'] = $key;
                $aryLog['watch_type'] = 1; //1-comments 2-title 3-description
                $aryLog['from_status'] = (int)$oldStatus['comment_status'];
                $aryLog['to_status'] = (int)$value;
                $aryLog['create_time'] = time();
                $dalBoard->insertBoardWatchChangestatusLog($aryLog);

                //set new status
                $newInfo['comment_status'] = $value;
                $newInfo['admin_id'] = $adminId;
                $newInfo['update_time'] = date('Y-m-d H:i:s');
                $dalBoard->updateBoardWatchCommentStatus($newInfo, $key);

                /*
                //違反の時　コメント⇒非表示
                if (5 == (int)$value) {
                    require_once 'Dal/Board.php';
                    $delBoard = new Dal_Board($this->_config);
                    $delBoard->deleteBoard($key);
                }
                */
                $result++;
            }

            $this->_wdb->commit();
            return $result;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return 0;
        }
    }

    /**
     * deal watch title
     *
     * @param array $aryNewStatus
     * @param integer $adminId
     * @param boolean $isViewer
     * @return integer
     */
    public function dealWatchTitle($aryNewStatus, $adminId, $isWatcher = false)
    {
        try {
            require_once 'Admin/Dal/Board.php';
            $dalBoard = new Admin_Dal_Board($this->_config);
            $result = 0;

            $this->_wdb->beginTransaction();

            foreach ($aryNewStatus as $key => $value) {
                //get curren status
                $oldStatus = $dalBoard->getBoardWatchTitleStatusById($key);

                //watcher not allow to deal already dealed data
                if ($isWatcher && (3 <= (int)$oldStatus['title_status'] || !empty($oldStatus['t_admin_id']))) {
                    continue;
                }
                //unchanged or no deal
                if (2 >= (int)$value || (int)$oldStatus['title_status'] == (int)$value) {
                    continue;
                }

                //save status change logs
                $aryLog = array();
                $aryLog['admin_id'] = $adminId;
                $aryLog['watch_uid'] = $key;
                $aryLog['watch_type'] = 2; //1-comments 2-title 3-description
                $aryLog['from_status'] = (int)$oldStatus['title_status'];
                $aryLog['to_status'] = (int)$value;
                $aryLog['create_time'] = time();
                $dalBoard->insertBoardWatchChangestatusLog($aryLog);

                //set new status
                $newInfo['title_status'] = $value;
                $newInfo['t_admin_id'] = $adminId;
                $newInfo['t_update_time'] = date('Y-m-d H:i:s');
                $dalBoard->updateBoardWatchTitleStatus($newInfo, $key);

                /*
                //違反の時　「タイトル」＝デフォルト値
                if (5 == (int)$value) {
                    require_once 'Dal/Board.php';
                    $delBoard = new Dal_Board($this->_config);
                    $delBoard->updateBoardSet(array('title' => ''), $key);
                }
				*/
                $result++;
            }

            $this->_wdb->commit();
            return $result;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return 0;
        }
    }

    /**
     * deal watch des
     *
     * @param array $aryNewStatus
     * @param integer $adminId
     * @param boolean $isViewer
     * @return integer
     */
    public function dealWatchDes($aryNewStatus, $adminId, $isWatcher = false)
    {
        try {
            require_once 'Admin/Dal/Board.php';
            $dalBoard = new Admin_Dal_Board($this->_config);
            $result = 0;

            $this->_wdb->beginTransaction();

            foreach ($aryNewStatus as $key => $value) {
                //get curren status
                $oldStatus = $dalBoard->getBoardWatchTitleStatusById($key);

                //watcher not allow to deal already dealed data
                if ($isWatcher && (3 <= (int)$oldStatus['des_status'] || !empty($oldStatus['d_admin_id']))) {
                    continue;
                }
                //unchanged or no deal
                if (2 >= (int)$value || (int)$oldStatus['des_status'] == (int)$value) {
                    continue;
                }

                //save status change logs
                $aryLog = array();
                $aryLog['admin_id'] = $adminId;
                $aryLog['watch_uid'] = $key;
                $aryLog['watch_type'] = 3; //1-comments 2-title 3-description
                $aryLog['from_status'] = (int)$oldStatus['des_status'];
                $aryLog['to_status'] = (int)$value;
                $aryLog['create_time'] = time();
                $dalBoard->insertBoardWatchChangestatusLog($aryLog);

                //set new status
                $newInfo['des_status'] = $value;
                $newInfo['d_admin_id'] = $adminId;
                $newInfo['d_update_time'] = date('Y-m-d H:i:s');
                $dalBoard->updateBoardWatchTitleStatus($newInfo, $key);

                /*
                //違反の時　「掲示板の説明」＝デフォルト値
                if (5 == (int)$value) {
                    require_once 'Dal/Board.php';
                    $delBoard = new Dal_Board($this->_config);
                    $delBoard->updateBoardSet(array('introduce' => ''), $key);
                }
				*/
                $result++;
            }

            $this->_wdb->commit();
            return $result;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return 0;
        }
    }
}
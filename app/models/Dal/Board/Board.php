<?php

require_once 'OpenSocial/Collection.php';
require_once 'OpenSocial/Person.php';

/**
 * Board datebase's Operation
 *
 *
 * @package    Dal
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/02/10    Liz
 */
class Dal_Board_Board extends Dal_Abstract
{

    /**
     * user table name
     *
     * @var string
     */
    protected $table_board = 'board';

    protected static $_instance;

    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * insert a new board
     *
     * @param array $info
     * @return integer
     */
    public function insertBoard($info)
    {
        $this->_wdb->insert($this->table_board, $info);
        return $this->_wdb->lastInsertId();
    }

    /**
     * delete board by id
     *
     * @param integer $id
     * @return void
     */
    public function deleteBoard($bid)
    {
        $sql = "UPDATE $this->table_board SET isdelete = '1'  WHERE bid=:bid";

        return $this->_wdb->query($sql, array('bid' => $bid));
    }

    /**
     * get comments
     *
     * @param Integer $uid
     * @param Integer $pageindex
     * @param Integer $pagesize
     * @return array
     */
    public function getFriendsComments($uids, $pageindex = 1, $pagesize = 10)
    {
        $start = ($pageindex - 1) * $pagesize;
        $sql = "SELECT b.*, bwcs.comment_status, mu1.displayName AS uid_name, mu2.displayName AS comment_uid_name
                FROM $this->table_board AS b, board_watch_comment_status AS bwcs, mixi_user AS mu1, mixi_user AS mu2
                WHERE b.uid in ($uids) AND b.isdelete = 0 AND b.bid = bwcs.bid AND bwcs.comment_status != 5
                      AND b.uid = mu1.id AND b.comment_uid = mu2.id
                ORDER BY b.create_time DESC LIMIT $start, $pagesize";

        return $this->_rdb->fetchAll($sql);
    }

    /**
     * get comments
     *
     * @param Integer $bid
     * @return array
     */
    public function getCommentInfo($bid)
    {
        $sql = "SELECT * FROM $this->table_board WHERE bid = :bid";

        $array = array('bid' => $bid);

        return $this->_rdb->fetchRow($sql, $array);
    }

    /**
     * get comments
     *
     * @param Integer $uid
     * @param Integer $pageindex
     * @param Integer $pagesize
     * @return array
     */
    public function getComments($uid, $pageindex = 1, $pagesize = 10, $sort = 'DESC')
    {
        $start = ($pageindex - 1) * $pagesize;
        $sql = "SELECT b.*, bwcs.comment_status
                FROM $this->table_board AS b, board_watch_comment_status AS bwcs
                WHERE b.uid = :uid AND b.isdelete = 0 AND b.bid = bwcs.bid AND bwcs.comment_status != 5
                ORDER BY b.create_time $sort LIMIT $start, $pagesize";

        $array = array('uid' => $uid);

        return $this->_rdb->fetchAll($sql, $array);
    }

    /**
     * get comments count
     *
     * @param Integer $uid
     * @return integer
     */
    public function getCommentsCount($uid)
    {
        $sql = "SELECT count(1)
                FROM $this->table_board AS b, board_watch_comment_status AS bwcs
                WHERE b.uid = :uid AND b.isdelete = 0 AND b.bid = bwcs.bid AND bwcs.comment_status != 5";

        $array = array('uid' => $uid);

        return $this->_rdb->fetchOne($sql, $array);
    }

    /**
     * get user setting
     *
     * @param Integer $uid
     * @return integer
     */
    public function getUserSetting($uid)
    {
        $sql = "SELECT bs.*, bwts.title_status, bwts.des_status FROM board_set as bs
                LEFT JOIN board_watch_title_status AS bwts ON bs.uid = bwts.uid
                WHERE bs.uid = :uid";

        $array = array('uid' => $uid);

        return $this->_rdb->fetchRow($sql, $array);
    }


    /**
     * get history list info by user id
     *
     * @param Integer $uid
     * @param Integer $pageindex
     * @param Integer $pagesize
     * @return array
     */
    public function getHistoryList($uid, $pageindex = 1, $pagesize = 10, $sort = 'DESC')
    {
        $start = ($pageindex - 1) * $pagesize;
        $sql = "SELECT b.*, bwcs.comment_status
                FROM $this->table_board AS b, board_watch_comment_status AS bwcs
                WHERE b.comment_uid = :uid AND b.isdelete = 0 AND b.bid = bwcs.bid AND bwcs.comment_status != 5
                ORDER BY b.create_time $sort LIMIT $start, $pagesize";

        $array = array('uid' => $uid);

        return $this->_rdb->fetchAll($sql, $array);
    }

    /**
     * get history count
     *
     * @param Integer $uid
     * @return integer
     */
    public function getHistoryCount($uid)
    {
        $sql = "SELECT count(1)
                FROM $this->table_board AS b, board_watch_comment_status AS bwcs
                WHERE b.comment_uid = :uid AND b.isdelete = 0 AND b.bid = bwcs.bid AND bwcs.comment_status != 5";

        $array = array('uid' => $uid);

        return $this->_rdb->fetchOne($sql, $array);
    }

    /**
     * get contact list info by user id
     *
     * @param Integer $uid
     * @param Integer $pageindex
     * @param Integer $pagesize
     * @return array
     */
    public function getMiniContactList($uid, $pageindex = 1, $pagesize = 10)
    {
        $start = ($pageindex - 1) * $pagesize;
        $sql = "SELECT c.comment_uid, rand() AS r
                FROM (SELECT DISTINCT comment_uid FROM $this->table_board WHERE uid = :uid AND isdelete = 0 AND comment_uid <> uid) AS c
                ORDER BY r DESC LIMIT $start, $pagesize";

        $array = array('uid' => $uid);

        return $this->_rdb->fetchAll($sql, $array);
    }

    /**
     * get contact list info by user id
     *
     * @param Integer $uid
     * @param Integer $pageindex
     * @param Integer $pagesize
     * @return array
     */
    public function getContactList($uid, $pageindex = 1, $pagesize = 10)
    {
        $start = ($pageindex - 1) * $pagesize;
        $sql = "SELECT c.*
                FROM (SELECT * FROM $this->table_board WHERE uid = :uid AND isdelete = 0 AND comment_uid <> uid ORDER BY create_time DESC) AS c
                GROUP BY c.comment_uid LIMIT $start, $pagesize";

        $array = array('uid' => $uid);

        return $this->_rdb->fetchAll($sql, $array);
    }

	/**
     * get contact count
     *
     * @param Integer $uid
     * @return integer
     */
    public function getContactCount($uid)
    {
        $sql = "SELECT count(DISTINCT comment_uid) AS count FROM $this->table_board WHERE uid = :uid AND isdelete = 0 AND comment_uid <> uid";

        $array = array('uid' => $uid);

        return $this->_rdb->fetchOne($sql, $array);
    }

    //ADD BY LP
    public function  getInfo($uid){
        $sql="SELECT * FROM board_set WHERE uid=:uid";
        $result = $this->_rdb->fetchAll($sql, array('uid' => $uid));
        return $result;
    }

    public function set($setMessage){
    	$sql = "INSERT INTO board_set(uid, title, introduce, openflag, allowComment,image_url,mobile_image_url,create_time) "
             . "VALUES (:uid, :title, :introduce, :openflag, :allowComment,:image_url,:mobile_image_url,:create_time) ON DUPLICATE KEY UPDATE "
             . "title = :title, introduce = :introduce, openflag = :openflag, allowComment = :allowComment,image_url=:image_url,mobile_image_url=:mobile_image_url";
        return $this->_wdb->query($sql, array('title' => $setMessage['title'],
                                       'introduce'=>$setMessage['introduce'],
                                       'openflag'=>$setMessage['openflag'],
                                       'allowComment'=>$setMessage['allowComment'],
                                       'image_url'=>$setMessage['image_url'],
                                       'mobile_image_url' => $setMessage['mobile_image_url'],
                                       'uid'=>$setMessage['uid'],
                                       'create_time'=>$setMessage['create_time']
                                       ));
     //  $result = $stmt->rowCount();
     //  return $result;
    }
    //LP ADD END


	/**
     * update boardset
     *
     * @param array $info
     * @param integer $id
     * @return integer
     */
    public function updateBoardSet($info, $id)
    {
        $where = $this->_wdb->quoteInto('uid = ?', $id);
        return $this->_wdb->update('board_set', $info, $where);
    }
    /**
     * list Forbid Word
     *
     * @param Integer $pageindex
     * @param Integer $pagesize
     * @return array
     */
    public function getForbidWordList()
    {
        $sql = "SELECT word FROM board_forbid_word ";

        return $this->_rdb->fetchAll($sql);
    }

    /**
     * insert board watch comment status
     *
     * @param array $info
     * @return integer
     */
    public function insertBoardWatchCommentStatus($info)
    {
        $this->_wdb->insert('board_watch_comment_status', $info);
        return $this->_wdb->lastInsertId();
    }

    /**
     * get board watch title status
     *
     * @param string $id
     * @return array
     */
    public function getBoardWatchTitleStatusByUid($uid)
    {
        $sql = "SELECT * FROM board_watch_title_status WHERE uid=:uid ";
        return $this->_rdb->fetchRow($sql, array('uid' => $uid));
    }

    /**
     * insert board watch title status
     *
     * @param array $info
     * @return integer
     */
    public function insertBoardWatchTitleStatus($info)
    {
        $this->_wdb->insert('board_watch_title_status', $info);
        return $this->_wdb->lastInsertId();
    }

    /**
     * update board watch title status
     *
     * @param array $info
     * @param string $id
     * @return integer
     */
    public function updateBoardWatchTitleStatus($uid, $info)
    {
        $where = $this->_wdb->quoteInto('uid = ?', $uid);
        return $this->_wdb->update('board_watch_title_status', $info, $where);
    }

    /**
     * get all skin infomation
     * @return array
     */
    public function getSkinBasicInfo()
    {
    	$sql = "SELECT * FROM board_skin";
    	return $this->_rdb->fetchAll($sql);
    }

    /**
     * get one skin infomation
     * @param string $skinId
     * @return array
     */
    public function getOneSkinInfo($skinId)
    {
    	$sql = "SELECT * FROM board_skin WHERE skin_id = :skinId";
    	return $this->_rdb->fetchRow($sql, array("skinId" => $skinId));
    }

    /**
     * get all app user
     * @return array
     */
    public function getAllAppUser()
    {
    	$sql = "SELECT uid FROM board_user";
        return $this->_rdb->fetchAll($sql);
    }

    public function getUserSkinUrl($uid)
    {
        $sql = "SELECT image_url FROM board_set WHERE uid=:uid";
        return $this->_rdb->fetchOne($sql, array('uid' => $uid));
    }

}
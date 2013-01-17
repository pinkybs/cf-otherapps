<?php

require_once 'Admin/Dal/Abstract.php';

/**
 * Admin Dal Board
 * LinNo Admin Board Data Access Layer
 *
 * @package    Admin/Dal
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/03/06    zhangxin
 */
class Admin_Dal_Board extends Admin_Dal_Abstract
{

    /**
     * class default instance
     * @var self instance
     */
    protected static $_instance;

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

    /******************* board_forbid_word, board_forbid_word_type *******************/

    /**
     * list Forbid Word Type
     *
     * @return array
     */
    public function listForbidWordType()
    {
        $sql = "SELECT * FROM board_forbid_word_type ORDER BY tid ";
        return $this->_rdb->fetchAll($sql);
    }

    /**
     * list Forbid Word
     *
     * @param Integer $pageindex
     * @param Integer $pagesize
     * @return array
     */
    public function listForbidWord($pageindex = 1, $pagesize = 10)
    {
        $start = ($pageindex - 1) * $pagesize;
        $sql = "SELECT f.*,t.name AS type_name,u.name AS admin_name FROM board_forbid_word f, board_forbid_word_type t, admin_user u
                WHERE f.tid = t.tid AND u.uid = f.admin_id
                ORDER BY f.create_time LIMIT $start, $pagesize";

        return $this->_rdb->fetchAll($sql);
    }

    /**
     * get Forbid Word by id
     *
     * @param Integer $id
     * @return array
     */
    public function getForbidWordById($id)
    {
        $sql = "SELECT f.*,t.name AS type_name FROM board_forbid_word f, board_forbid_word_type t WHERE f.tid = t.tid AND f.id=:id ";
        return $this->_rdb->fetchRow($sql, array('id' => $id));
    }

    /**
     * insert Forbid Word
     *
     * @param array $info
     * @return integer
     */
    public function insertForbidWord($info)
    {
        $this->_wdb->insert('board_forbid_word', $info);
        return $this->_wdb->lastInsertId();
    }

    /**
     * update Forbid Word
     *
     * @param array $info
     * @param integer $id
     * @return integer
     */
    public function updateForbidWord($info, $id)
    {
        $where = $this->_wdb->quoteInto('id = ?', $id);
        return $this->_wdb->update('board_forbid_word', $info, $where);
    }

    /**
     * delete Forbid Word
     *
     * @param integer $id
     * @return integer
     */
    public function deleteForbidWord($id)
    {
        $sql = "DELETE FROM board_forbid_word WHERE id=:id ";
        return $this->_wdb->query($sql, array('id' => $id));
    }

    /******************* board_watch_changestatus_log *******************/

    /**
     * insert board watch changestatus log
     *
     * @param array $info
     * @return integer
     */
    public function insertBoardWatchChangestatusLog($info)
    {
        $this->_wdb->insert('board_watch_changestatus_log', $info);
        return $this->_wdb->lastInsertId();
    }

    /**
     * get user daily deal contents count list by month
     *
     * @param Integer $uid
     * @param Integer $year
     * @param Integer $month
     * @return array
     */
    public function getUserDailyDealtCountByMonth($uid, $year, $month)
    {
        $sql = "SELECT DATE(FROM_UNIXTIME(create_time)) AS sel_year_month, COUNT(*) AS deal_count
                FROM board_watch_changestatus_log
                WHERE admin_id=:uid AND MONTH(DATE(FROM_UNIXTIME(create_time)))=:month AND YEAR(DATE(FROM_UNIXTIME(create_time)))=:year
                GROUP BY DATE(FROM_UNIXTIME(create_time)) ORDER BY sel_year_month ";
        return $this->_rdb->fetchAll($sql, array('uid' => $uid, 'month' => $month, 'year' => $year));
    }

    /******************* board_watch_comment_status *******************/
    /**
     * get board watch comment status
     *
     * @param Integer $status
     * @return Integer
     */
    public function getBoardWatchCommentCountByStatus($status)
    {
        $sql = "SELECT COUNT(*) FROM board_watch_comment_status WHERE comment_status=:comment_status ";
        return $this->_rdb->fetchOne($sql, array('comment_status' => $status));
    }

    /**
     * get board watch comment list
     *
     * @param Integer $pageindex
     * @param Integer $pagesize
     * @param Integer $status 1-未処理 2-容疑 3-問題なし 4-保留 5-違反 0-All
     * @param string $keyword ''=All
     * @return array
     */
    public function getBoardWatchCommentList($pageindex = 1, $pagesize = 10, $status = 0, $keyword = '')
    {
        $start = ($pageindex - 1) * $pagesize;

        $aryParm = array();
        $sql = "SELECT b.*,s.comment_status,s.admin_id FROM board_watch_comment_status s, board b
                WHERE s.bid=b.bid ";

        if (!empty($status)) {
            $sql .= " AND s.comment_status=:comment_status ";
            $aryParm['comment_status'] = $status;
        }

        if (!empty($keyword)) {
            $sql .= " AND b.content LIKE :content ";
            $aryParm['content'] = '%' . $keyword . '%';
        }

        $sql .= " ORDER BY b.create_time LIMIT $start, $pagesize ";

        return $this->_rdb->fetchAll($sql, $aryParm);
    }

    /**
     * get board watch comment list count
     *
     * @param Integer $status 1-未処理 2-容疑 3-問題なし 4-保留 5-違反 0-All
     * @param string $keyword ''=All
     * @return Integer
     */
    public function getBoardWatchCommentListCount($status = 0, $keyword = '')
    {
        $aryParm = array();
        $sql = "SELECT COUNT(*) FROM board_watch_comment_status s, board b
                WHERE s.bid=b.bid ";

        if (!empty($status)) {
            $sql .= " AND s.comment_status=:comment_status ";
            $aryParm['comment_status'] = $status;
        }

        if (!empty($keyword)) {
            $sql .= " AND b.content LIKE :content ";
            $aryParm['content'] = '%' . $keyword . '%';
        }

        return $this->_rdb->fetchOne($sql, $aryParm);
    }

    /**
     * get board watch comment list by id
     *
     * @param Integer $pageindex
     * @param Integer $pagesize
     * @param Integer/string $id
     * @param string $column
     * @return array
     */
    public function getBoardWatchCommentListById($pageindex = 1, $pagesize = 10, $id, $column)
    {
        $start = ($pageindex - 1) * $pagesize;

        $sql = "SELECT b.*,s.comment_status,s.admin_id FROM board_watch_comment_status s, board b
                WHERE s.bid=b.bid AND $column=:id
                ORDER BY b.create_time LIMIT $start, $pagesize ";

        return $this->_rdb->fetchAll($sql, array('id' => $id));
    }

    /**
     * get board watch comment list count by id
     *
     * @param Integer/string $id
     * @param string $column
     * @return array
     */
    public function getBoardWatchCommentListCountById($id, $column)
    {
        $sql = "SELECT COUNT(*) FROM board_watch_comment_status s, board b
                WHERE s.bid=b.bid AND $column=:id ";

        return $this->_rdb->fetchOne($sql, array('id' => $id));
    }

    /**
     * get board watch comment status
     *
     * @param Integer $id
     * @return array
     */
    public function getBoardWatchCommentStatusById($id)
    {
        $sql = "SELECT * FROM board_watch_comment_status WHERE bid=:id ";
        return $this->_rdb->fetchRow($sql, array('id' => $id));
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
     * update board watch comment status
     *
     * @param array $info
     * @param integer $id
     * @return integer
     */
    public function updateBoardWatchCommentStatus($info, $id)
    {
        $where = $this->_wdb->quoteInto('bid = ?', $id);
        return $this->_wdb->update('board_watch_comment_status', $info, $where);
    }

    /**
     * delete board watch comment status
     *
     * @param integer $id
     * @return integer
     */
    public function deleteBoardWatchCommentStatus($id)
    {
        $sql = "DELETE FROM board_watch_comment_status WHERE bid=:id ";
        return $this->_wdb->query($sql, array('id' => $id));
    }

    /******************* board_watch_title_status *******************/

    /**
     * get board watch title status
     *
     * @param string $column
     * @param Integer $status
     * @return Integer
     */
    public function getBoardWatchTitleCountByStatus($column, $status)
    {
        $sql = "SELECT COUNT(*) FROM board_watch_title_status WHERE $column=:status ";
        return $this->_rdb->fetchOne($sql, array('status' => $status));
    }

    /**
     * get board watch title list
     *
     * @param Integer $pageindex
     * @param Integer $pagesize
     * @param Integer $status 1-未処理 2-容疑 3-問題なし 4-保留 5-違反 0-All
     * @param string $keyword ''=All
     * @param string $column1 - title_status/des_status
     * @param string $column2 - title/introduce
     * @return array
     */
    public function getBoardWatchTitleList($pageindex = 1, $pagesize = 10, $status = 0, $keyword = '', $column1, $column2)
    {
        $start = ($pageindex - 1) * $pagesize;

        $aryParm = array();
        $sql = "SELECT b.*,s.title_status,s.t_admin_id,s.des_status,s.d_admin_id,s.t_update_time,s.d_update_time
                FROM board_watch_title_status s, board_set b WHERE s.uid=b.uid ";

        if (!empty($status)) {
            $sql .= " AND s.$column1=:status ";
            $aryParm['status'] = $status;
        }

        if (!empty($keyword)) {
            $sql .= " AND b.$column2 LIKE :content ";
            $aryParm['content'] = '%' . $keyword . '%';
        }

        $sql .= " ORDER BY b.create_time LIMIT $start, $pagesize ";

        return $this->_rdb->fetchAll($sql, $aryParm);
    }

    /**
     * get board watch title list count
     *
     * @param Integer $status 1-未処理 2-容疑 3-問題なし 4-保留 5-違反 0-All
     * @param string $keyword ''=All
     * @param string $column1 - title_status/des_status
     * @param string $column2 - title/introduce
     * @return Integer
     */
    public function getBoardWatchTitleListCount($status = 0, $keyword = '', $column1, $column2)
    {
        $aryParm = array();
        $sql = "SELECT COUNT(*) FROM board_watch_title_status s, board_set b
                WHERE s.uid=b.uid ";

        if (!empty($status)) {
            $sql .= " AND s.$column1=:status ";
            $aryParm['status'] = $status;
        }

        if (!empty($keyword)) {
            $sql .= " AND b.$column2 LIKE :content ";
            $aryParm['content'] = '%' . $keyword . '%';
        }

        return $this->_rdb->fetchOne($sql, $aryParm);
    }

    /**
     * get board watch title list by id
     *
     * @param Integer $pageindex
     * @param Integer $pagesize
     * @param Integer/string $id
     * @param string $column
     * @return array
     */
    public function getBoardWatchTitleListById($pageindex = 1, $pagesize = 10, $id, $column)
    {
        $start = ($pageindex - 1) * $pagesize;

        $sql = "SELECT b.*,s.title_status,s.t_admin_id,s.des_status,s.d_admin_id,s.t_update_time,s.d_update_time
                FROM board_watch_title_status s, board_set b
                WHERE s.uid=b.uid AND s.$column=:id
                ORDER BY b.create_time LIMIT $start, $pagesize ";

        return $this->_rdb->fetchAll($sql, array('id' => $id));
    }

    /**
     * get board watch title list count by id
     *
     * @param Integer/string $id
     * @param string $column
     * @return array
     */
    public function getBoardWatchTitleListCountById($id, $column)
    {
        $sql = "SELECT COUNT(*) FROM board_watch_title_status s, board_set b
                WHERE s.uid=b.uid AND s.$column=:id ";

        return $this->_rdb->fetchOne($sql, array('id' => $id));
    }

    /**
     * get board watch title status
     *
     * @param string $id
     * @return array
     */
    public function getBoardWatchTitleStatusById($id)
    {
        $sql = "SELECT * FROM board_watch_title_status WHERE uid=:id ";
        return $this->_rdb->fetchRow($sql, array('id' => $id));
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
    public function updateBoardWatchTitleStatus($info, $id)
    {
        $where = $this->_wdb->quoteInto('uid = ?', $id);
        return $this->_wdb->update('board_watch_title_status', $info, $where);
    }

    /**
     * delete board watch title status
     *
     * @param string $id
     * @return integer
     */
    public function deleteBoardWatchTitleStatus($id)
    {
        $sql = "DELETE FROM board_watch_title_status WHERE uid=:id ";
        return $this->_wdb->query($sql, array('id' => $id));
    }

}
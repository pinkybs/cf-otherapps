<?php

require_once 'OpenSocial/Collection.php';
require_once 'OpenSocial/Person.php';

/**
 * Board datebase's Operation
 *
 *
 * @package    Dal
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/05/11    SHW
 */
class Dal_Chomeboard_Chomeboard extends Dal_Abstract
{

    /**
     * user table name
     *
     * @var string
     */
    protected $table_board = 'chome_board';

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
    public function insertChomeBoard($info)
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
    public function deleteChomeBoard($bid)
    {
        $sql = "UPDATE $this->table_board SET isdelete = 1 WHERE bid=:bid";

        return $this->_wdb->query($sql, array('bid' => $bid));
    }

    /**
     * get last chome board by uid
     *
     * @param integer $uid
     * @return array
     */
    public function getLastChomeBoard($uid)
    {
        $sql = "SELECT * FROM $this->table_board WHERE uid=:uid AND isdelete = 0 ORDER BY bid DESC LIMIT 0,1";
        
        $array = array('uid' => $uid);
        
        return $this->_rdb->fetchAll($sql, $array);
    }

    /**
     * get chome board info
     *
     * @param integer $bid
     * @return array
     */
    public function getChomeBoardInfo($bid)
    {
        $sql = "SELECT * FROM $this->table_board WHERE bid=:bid";
        
        $array = array('bid' => $bid);
        
        return $this->_rdb->fetchRow($sql, $array);
    }
    
    /**
     * get board history
     *
     * @param integer $uid
     * @return array
     */
    public function getBoardHistory($uid)
    {
        $sql = "SELECT * FROM $this->table_board WHERE uid=:uid AND isdelete = 0 ORDER BY sort_id DESC";
        
        $array = array('uid' => $uid);
        
        return $this->_rdb->fetchAll($sql, $array);
    }

    /**
     * get sort id by bid
     *
     * @param integer $bid
     * @return int
     */
    public function getSortIdByBid($bid)
    {
        $sql = "SELECT sort_id FROM $this->table_board WHERE bid=:bid";
        
        $array = array('bid' => $bid);
        
        return $this->_rdb->fetchOne($sql, $array);
    }

    /**
     * update sort id
     *
     * @param integer $uid
     * @param integer $sortId
     * @param integer $num
     * @return void
     */
    public function updateSortId($uid, $sortId, $num)
    {
        $sql = "UPDATE $this->table_board SET sort_id = sort_id + $num WHERE uid=:uid AND sort_id > :sort_id";

        $this->_wdb->query($sql, array('uid' => $uid, 'sort_id' => $sortId));
    }
    
}
<?php

require_once 'Dal/Abstract.php';

class Dal_Board_Set extends Dal_Abstract
{
    /**
     * user table name
     *
     * @var string
     */
    protected $table_card = 'board_set';
    
    protected static $_instance;
    
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        
        return self::$_instance;
    }

    /**
     * get user setting
     *
     * @param string $uid
     * @return array
     */
    public function getUserSetting($uid)
    {
        $sql = "SELECT * FROM $this->table_card WHERE uid = :uid";

        return $this->_rdb->fetchRow($sql, array('uid' => $uid));
    }

    /**
     * insert board watch comment status
     *
     * @param array $info
     * @return integer
     */
    public function insertSetting($info)
    {
        $this->_wdb->insert('board_set', $info);
        return $this->_wdb->lastInsertId();
    }
    
    /**
     * update user setting
     *
     * @param string $uid
     * @param array $setting
     * @return array
     */
    public function updateSetting($uid, $setting)
    {
        $where = $this->_wdb->quoteinto('uid = ?', $uid);
        
        $this->_wdb->update($this->table_card, $setting, $where);
    }



}
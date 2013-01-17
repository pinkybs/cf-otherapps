<?php

require_once 'Dal/Abstract.php';

class Dal_Parking_Card extends Dal_Abstract
{
    /**
     * user table name
     *
     * @var string
     */
    protected $table_card = 'parking_user_card';
    
    protected static $_instance;
    
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        
        return self::$_instance;
    }

    /**
     * buy new card
     * 
     * @param array $info
     * @return integer
     */
    public function insertUserCard($info)
    {
        $this->_wdb->insert($this->table_card, $info);
        return $this->_wdb->lastInsertId();
    }

    /**
     * update card count
     * 
     * @param integer $cid
     * @param integer $uid
     * @return void
     */
    public function updateUserCardCoutByCid($cid, $uid, $change)
    {
        $sql = "UPDATE $this->table_card SET count=count+$change,buy_time=now() WHERE uid=:uid AND sid=:sid";
        $this->_wdb->query($sql, array('sid'=>$cid, 'uid' => $uid));
    }

    /**
     * get user card count where card id = cid
     *
     * @param integer $cid
     * @param integer $uid
     * @return integer
     */
    public function getUserCardCoutByCid($cid, $uid)
    {
        $sql = "SELECT `count` FROM parking_user_card WHERE sid=:sid AND uid=:uid";
        return $this->_rdb->fetchOne($sql,array('sid'=>$cid, 'uid'=>$uid));
    }


}
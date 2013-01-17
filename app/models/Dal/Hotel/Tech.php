<?php

require_once 'Dal/Abstract.php';

/**
 * Dal Hotel Tech
 * MixiApp hotel Tech Data Access Layer
 *
 * @package    Dal/Hotel
 * @copyright  Copyright (c) 2009 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/09/16    Zhaoxh
 */
class Dal_Hotel_Tech extends Dal_Abstract
{
    /**
     * tech table name
     *
     * @var string
     */
    protected $table_user = 'hotel_user_technology';

    protected static $_instance;

    /**
     * get Dal_Hotel_Tech default
     *
     * @return Dal_Hotel_Tech
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * get One Data of hotel_user_technology table by uid
     *
     * @param string $uid
     * @param string $colName
     * @return string
     */
    public function getOneData($uid,$colName)
    {
        $sql = "SELECT $colName FROM $this->table_user WHERE uid=:uid";

        $result = $this->_rdb->fetchOne($sql,array('uid'=>$uid));

        return $result;
    }

    /**
     * is in update process
     *
     * @param string $uid
     * @param string $type
     * @return boolean
     */
    public function inUpProcess($uid,$type)
    {
        $nowTime = time();
        $sql = "SELECT max(over_time) FROM hotel_update WHERE uid=$uid and `type`=$type";
        $overTime = $this->_rdb->fetchOne($sql);
        return $nowTime < $overTime;
    }

    /**
     * insert a update process
     *
     * @param array $info
     */
    public function insertUpProcess($info)
    {
        $this->_wdb->insert('hotel_update', $info);
    }

    /**
     * get update_money by given arg
     *
     * @param string $table
     * @param string $currentLv
     * @return string
     */
    public function getTechnology($currentLv,$name)
    {
        $sql = "SELECT update_money,update_time,effect FROM hotel_technology_type WHERE `level`=:lv AND `name`=:name";
        $result = $this->_rdb->fetchRow($sql,array('lv' => $currentLv,'name' => $name));
        return $result;
    }


    /**
     * update Huser by uid with array $set
     *
     * @param string $uid
     * @param array $set
     * @return boolean
     */
    public function upTech($uid,$set)
    {
        $db = buildAdapter();
        $where = $db->quoteInto('uid = ?', $uid);
        $rows_affected = $db->update($this->table_user, $set, $where);
        return $rows_affected == 1 ;
    }

    public function updateTech($uid, $type, $time)
    {
        //select last room_update info
        $sql = "SELECT id,`name`,currentLv FROM hotel_update WHERE uid=:uid AND `type`=:type AND operated=0 AND over_time<$time";
        $arr = $this->_rdb->fetchRow($sql,array('uid' => $uid,'type' => $type));
        $name = $arr['name'];

        //update that room
        $sqll = "UPDATE hotel_user_technology SET $name=:currentLv WHERE uid=:uid ";
        $this->_wdb->query($sqll,array('uid' => $uid, 'currentLv' => $arr['currentLv'] + 1));

        return $arr;
    }

    public function getTechInfo($uid)
    { 
        $sql = "SELECT desk,cook,service,learn FROM hotel_user_technology WHERE uid=:uid";
        $arr = $this->_rdb->fetchRow($sql,array('uid' => $uid));

        $sqll = "SELECT `level`,update_money,effect FROM hotel_technology_type WHERE `name`='desk' AND `level`=:deskLv
       UNION ALL SELECT `level`,update_money,effect FROM hotel_technology_type WHERE `name`='cook' AND `level`=:cookLv
       UNION ALL SELECT `level`,update_money,effect FROM hotel_technology_type WHERE `name`='service' AND `level`=:serviceLv
       UNION ALL SELECT `level`,update_money,effect FROM hotel_technology_type WHERE `name`='learn' AND `level`=:learnLv
       UNION ALL SELECT `level`,update_money,effect FROM hotel_technology_type WHERE `name`='desk' AND `level`=:deskLv + 1
       UNION ALL SELECT `level`,update_money,effect FROM hotel_technology_type WHERE `name`='cook' AND `level`=:cookLv + 1
       UNION ALL SELECT `level`,update_money,effect FROM hotel_technology_type WHERE `name`='service' AND `level`=:serviceLv + 1
       UNION ALL SELECT `level`,update_money,effect FROM hotel_technology_type WHERE `name`='learn' AND `level`=:learnLv + 1";

        $re = $this->_rdb->fetchAll($sqll,array('deskLv' => $arr['desk'],
                                               'cookLv' => $arr['cook'],
                                               'serviceLv' => $arr['service'],
                                               'learnLv' => $arr['learn']));
        return $re;
    }
}
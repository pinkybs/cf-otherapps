<?php

require_once 'Mdal/Abstract.php';

class Mdal_Parking_House extends Mdal_Abstract
{

    protected static $_instance;

    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }


    /**
     * get house infomation
     * @author lp
     * @return array
     */
    public function getHouseInfoById($hid)
    {
        $sql = "SELECT * FROM parking_background WHERE id=:id";
        return $this->_rdb->fetchRow($sql, array('id' => $hid));
    }
}
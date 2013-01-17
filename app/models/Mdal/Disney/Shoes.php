<?php

require_once 'Mdal/Abstract.php';

/**
 * Mdal Disney Shoes
 *
 * @package    Mdal/Disney
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2010/03/18    hch
 */
class Mdal_Disney_Shoes extends Mdal_Abstract
{
    protected static $_instance;

    /**
     * getDefaultInstance
     *
     * @return Mdal_Disney_Shoes
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }
    
    public function update($uid, $sid)
    {
        $result = $this->hasShoes($uid);
        
        if ($result) {
            $sql = "UPDATE disney_user_shoes SET times=times+2 WHERE uid=:uid";
            $this->_wdb->query($sql, array('uid'=>$uid));
        }
        else {
            $info = array('uid'=>$uid, 'shoes'=>$sid, 'times'=>'2');
            $this->_wdb->insert('disney_user_shoes', $info);
        }
    }
    
    public function updateShoesCount($uid)
    {
        $shoes = $this->getUserShoes($uid);
        
        if (!empty($shoes)) {
            if ($shoes['times'] > 1) {
                $sql = "UPDATE disney_user_shoes SET times=times-1 WHERE uid=:uid";        
            }
            else {
                $sql = "DELETE FROM disney_user_shoes WHERE uid=:uid";
            }
            
            $this->_wdb->query($sql, array('uid'=>$uid));
        }
        
    }
    
    /**
     * check user has shoes
     *
     * @param integer $uid
     * @return boolean
     */
    public function hasShoes($uid)
    {        
        $sql = "SELECT COUNT(1) FROM disney_user_shoes WHERE uid=:uid";
        $result = $this->_wdb->fetchOne($sql, array('uid'=>$uid));
        return $result > 0 ? true : false;
    }
    
    /**
     * get user shoes
     *
     * @param integer $uid
     * @return array
     */
    public function getUserShoes($uid)
    {
        $sql = "SELECT * FROM disney_user_shoes WHERE uid=:uid";
        $result = $this->_rdb->fetchRow($sql, array('uid'=>$uid));
        
        if (!empty($result)) {
            $this->getShoesName($result);
        }
        return $result;
    }
    
    /**
     * get shoes magni
     *
     * @param shoes' id $sid
     */
    public function getShoesMagni($sid)
    {
        $magni = 1;
        $rand = rand(0,101);
        
        if ($sid == 7){
            if ($rand <= 30) {
                $magni = 10;
            }
            elseif ($rand <= 30) {
                $magni = 20;
            }
            elseif ($rand <= 20){
                $magni = 50;
            }
            else {
                $magni = 100;
            }
        }
        elseif ($sid == 8) {
            if ($rand <= 40) {
                $magni = 5;
            }
            elseif ($rand <= 70) {
                $magni = 10;
            }
            elseif ($rand <= 80){
                $magni = 20;
            }
            elseif ($rand <= 90){
                $magni = 50;
            }
            else {
                $magni = 100;
            }
        }
        else {
            if ($rand <= 40) {
                $magni = 2;
            }
            elseif ($rand <= 80) {
                $magni = 5;
            }
            elseif ($rand <= 98) {
                $magni = 10;
            }
            elseif ($rand <= 99) {
                $magni = 20;
            }
            else {
                $magni = 50;
            }
        }
        
        return $magni;
    }
    
    public function getShoesName(&$shoes)
    {
        if ($shoes['shoes'] == 7) {
            $shoes['shoes_name'] = 'ｶﾞﾗｽの靴';
        }
        else if ($shoes['shoes'] == 8) {
            $shoes['shoes_name'] = 'ｺﾞｰﾙﾄﾞ･ｼｭｰｽﾞ';
        }
        else {
            $shoes['shoes_name'] = '快適ｽﾆｰｶｰ';
        }
    }
}
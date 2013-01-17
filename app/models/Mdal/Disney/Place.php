<?php

require_once 'Mdal/Abstract.php';

/**
 * Mdal Disney Place
 * MixiApp Disney Place Data Access Layer
 *
 * @package    Mdal/Disney
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/10/12    Liz
 */
class Mdal_Disney_Place extends Mdal_Abstract
{
    /**
     * table name
     *
     * @var string
     */
    protected $table_place = 'disney_place';

    /**
     * table name
     *
     * @var string
     */
    protected $table_area = 'disney_area';
    
    /**
     * table name
     *
     * @var string
     */
    protected $table_place_award = 'disney_place_award';
    
    protected static $_instance;

    /**
     * getDefaultInstance
     *
     * @return Mdal_Disney_Place
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * get place list
     *
     * @return array
     */
    public function getPlaceList()
    {
        $sql = "SELECT pid,name FROM $this->table_place ";

        return $this->_rdb->fetchPairs($sql);
    }

    /**
     * get area list
     *
     * @return array
     */
    public function getAreaList()
    {
        $sql = "SELECT * FROM $this->table_area ";

        return $this->_rdb->fetchAll($sql);
    }
    
    /**
     * get user place list info by area id
     *
     * @param integer $uid
     * @param integer $aid
     * @return array
     */
    public function getUserPlaceListByAid($uid, $aid)
    {
        $sql = "SELECT p.*,a.uid,a.count FROM $this->table_place AS p 
                LEFT JOIN disney_user_award AS a ON a.uid=:uid AND a.pid=p.pid 
                WHERE p.aid=:aid ORDER BY y,x";

        return $this->_rdb->fetchAll($sql, array('uid'=>$uid, 'aid'=>$aid));
    }
    
    /**
     * get place info by id
     *
     * @param integer $pid
     * @return array
     */
    public function getPlaceById($pid)
    {
        $sql = "SELECT * FROM $this->table_place WHERE pid=:pid ";

        return $this->_rdb->fetchRow($sql, array('pid'=>$pid));
    }

    /**
     * get place info by name
     *
     * @param string $name
     * @return array
     */
    public function getPlaceByName($name)
    {
        $sql = "SELECT pid,name FROM $this->table_place WHERE name=:name ";

        return $this->_rdb->fetchRow($sql, array('name'=>$name));
    }

    /**
     * get place info by mixi name
     *
     * @param string $name
     * @return array
     */
    public function getPlaceByMixiName($name)
    {
        $sql = "SELECT pid,name FROM $this->table_place WHERE mixi_name=:name ";

        return $this->_rdb->fetchRow($sql, array('name'=>$name));
    }
    
    /**
     * get place info by IAreaCode
     *
     * @param integer $iAreaCode
     * @return array
     */
    public function getPlaceByIAreaCode($iAreaCode)
    {
        $sql = "SELECT p.*,l.area_name FROM disney_place_list AS l,$this->table_place AS p WHERE CONCAT(l.area_id,l.sub_area_id)=:iAreaCode AND l.pid=p.pid ";

        return $this->_rdb->fetchRow($sql, array('iAreaCode'=>$iAreaCode));
    }
    
    /**
     * get place award info by id
     *
     * @param integer $pid
     * @return array
     */
    /*public function getPlaceAwardById($pid)
    {
        $sql = "SELECT * FROM $this->table_place_award WHERE pid=:pid ";

        return $this->_wdb->fetchRow($sql, array('pid'=>$pid));
    }*/

    /**
     * get area info by area id
     *
     * @param integer $aid
     * @return array
     */
    public function getAreaByAid($aid)
    {
        $sql = "SELECT * FROM $this->table_area WHERE aid=:aid ";

        return $this->_rdb->fetchRow($sql, array('aid'=>$aid));
    }
    
    /**
     * get neighber area
     * 
     * @param integer $aid
     * @param string $nextOrPrev[prev/next/first/last]
     * @return integer
     */
    public function getNeighberArea($aid, $nextOrPrev)
    {
        $aryParm = array();
        if ('prev' == $nextOrPrev) {
            $sql = "SELECT aid FROM $this->table_area WHERE aid<:aid ORDER BY aid DESC LIMIT 0,1 ";
            $aryParm['aid'] = $aid;
        }
        else if ('next' == $nextOrPrev){
            $sql = "SELECT aid FROM $this->table_area WHERE aid>:aid ORDER BY aid LIMIT 0,1 ";
            $aryParm['aid'] = $aid;
        }
        else if ('first' == $nextOrPrev) {
            $sql = "SELECT aid FROM $this->table_area ORDER BY aid LIMIT 0,1 ";
        }
        else if ('last' == $nextOrPrev) {
            $sql = "SELECT aid FROM $this->table_area ORDER BY aid DESC LIMIT 0,1 ";
        }
        return $this->_rdb->fetchOne($sql, $aryParm);
    }
    
    /**
     * get pet image url
     *
     * @return array
     */
    public function getPetImageUrl()
    {
        $sql = "SELECT pid,aid,award_name,award_icon,x,y FROM $this->table_place";
        
        return $this->_rdb->fetchAll($sql);
    }
    
    /**
     * get pid list by area id
     *
     * @param integer $aid
     * @return array
     */
    public function getPlaceListByAid($aid)
    {
        $sql = "SELECT pid FROM disney_place WHERE aid=:aid ";
        
        return $this->_rdb->fetchAll($sql, array('aid'=>$aid));
    }

    /**
     * get all place count
     *
     * @return integer
     */
    public function getAllPlaceCount()
    {
        $sql = "SELECT COUNT(1) FROM disney_place ";
        
        return $this->_rdb->fetchOne($sql);
    }

    /**
     * get download award info
     *
     * @param integer $uid
     * @param integer $pid
     * @return array
     */
    public function getDownloadAwardInfo($uid, $pid)
    {
        $sql = "SELECT * FROM disney_download_award WHERE uid=:uid AND award=:pid ";
        
        return $this->_rdb->fetchRow($sql, array('uid'=>$uid, 'pid'=>$pid));
    }
    
    /**
     * get desktop award info
     *
     * @param integer $uid
     * @param integer $pid
     * @return array
     */
    public function getDesktopAwardInfo($uid, $pid)
    {
        $sql = "SELECT * FROM disney_desktop_award WHERE uid=:uid AND award=:pid ";
        
        return $this->_rdb->fetchRow($sql, array('uid'=>$uid, 'pid'=>$pid));
    }
    
    /**
     * get all award by user
     *
     * @param integer $uid
     * @return array
     */
    public function getAllAwardByUser($uid, $area)
    {
    	$sql = "SELECT p.pid,p.aid,p.award_name,p.`name`,IFNULL(a.pid,0) AS has,IFNULL(d.id,0) AS download,IFNULL(k.id,0) AS desktop FROM disney_place AS p
				LEFT JOIN disney_user_award AS a ON a.pid=p.pid AND a.uid=:uid AND a.count>0
				LEFT JOIN disney_download_award AS d ON p.pid=d.award AND d.uid=:uid
				LEFT JOIN disney_desktop_award AS k ON p.pid=k.award AND k.uid=:uid
				WHERE aid=:aid ORDER BY aid DESC";
    	
    	return $this->_rdb->fetchAll($sql, array('uid'=>$uid, 'aid'=>$area));
    }
}
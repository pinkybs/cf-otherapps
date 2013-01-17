<?php

require_once 'Mdal/Abstract.php';

/**
 * Mdal Ship Rank
 * MixiApp Ship Rank Data Access Layer
 *
 * @package    Mdal/Ship
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/11/30    Liz
 */
class Mdal_Ship_Rank extends Mdal_Abstract
{        
    protected static $_instance;

    /**
     * getDefaultInstance
     *
     * @return Mdal_Ship_Rank
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }
    
    /**
     * get user ranking number in friends
     *
     * @param integer $uid
     * @param array $fids
     * @return integer
     */
    public function getUserFriendRankNm($uid, $fids)
    {
        $fids = $this->_rdb->quote($fids);
        
        $sql2 = "SET @pos=0;";
        $this->_wdb->query($sql2);
        
        $sql = "SELECT rank FROM (SELECT @pos:=@pos+1 AS rank,uid FROM ship_user 
                WHERE uid IN ($fids, :uid) ORDER BY asset+ship_price DESC,id ASC) AS r WHERE r.uid=:uid ";
        return $this->_rdb->fetchOne($sql, array('uid'=>$uid));
    }
    
    /**
     * get user ranking count in friend
     *
     * @param ingeger $uid
     * @param array $fids
     * @return integer
     */
    public function getRankFriendCount($uid, $fids)
    {
        $fids = $this->_rdb->quote($fids);
        
        $sql = "SELECT count(1) FROM ship_user WHERE uid IN ($fids, :uid) ";
        return $this->_rdb->fetchOne($sql, array('uid'=>$uid));
    }
    
    /**
     * get user ranking info in all
     *
     * @param ingeger $pageIndex
     * @param ingeger $pageSize
     * @return array
     */
    public function getAssetRankAllUser($pageIndex, $pageSize)
    {
        $start = ($pageIndex - 1) * $pageSize;
        $sql = "SELECT uid,asset+ship_price AS asset FROM ship_user ORDER BY asset+ship_price DESC,id ASC LIMIT $start,$pageSize ";
        return $this->_rdb->fetchAll($sql);
    }

    /**
     * get user ranking info in friends
     *
     * @param ingeger $uid
     * @param array $fids
     * @param ingeger $pageIndex
     * @param ingeger $pageSize
     * @return array
     */
    public function getAssetRankFriendUser($uid, $fids, $pageIndex, $pageSize)
    {
        $fids = $this->_rdb->quote($fids);
        
        $sql2 = "SET @pos=0;";
        $this->_wdb->query($sql2);
        
        $start = ($pageIndex - 1) * $pageSize;
        $sql = "SELECT * FROM (SELECT @pos:=@pos+1 AS rank,uid,asset+ship_price AS asset 
                FROM ship_user WHERE uid IN ($fids,:uid) ORDER BY asset+ship_price DESC,id ASC) AS a LIMIT $start,$pageSize ";
        return $this->_rdb->fetchAll($sql, array('uid'=>$uid));
    }
    
}
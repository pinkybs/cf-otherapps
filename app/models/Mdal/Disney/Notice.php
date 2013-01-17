<?php

require_once 'Mdal/Abstract.php';

/**
 * Mdal Disney Notice
 * MixiApp Disney Notice Data Access Layer
 *
 * @package    Mdal/Disney
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/10/12    Liz
 */
class Mdal_Disney_Notice extends Mdal_Abstract
{
    /**
     * table name
     *
     * @var string
     */
    protected $table_notice = 'disney_notice';
    
    protected static $_instance;

    /**
     * getDefaultInstance
     *
     * @return Mdal_Disney_Notice
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * insert notice
     *
     * @param array $notice
     * @return integer
     */
    public function insertNotice($notice)
    {
        $this->_wdb->insert($this->table_notice, $notice);
        return $this->_wdb->lastInsertId();
    }
    
    /**
     * delete notice by notice id
     *
     * @param integer $nid
     * @return void
     */
    public function deleteNotice($nid)
    {
        $sql = "UPDATE $this->table_notice SET status=0 WHERE id=:nid ";
        $this->_wdb->query($sql, array('nid'=>$nid));
    }

    /**
     * get notice by id
     *
     * @param integer $nid
     * @return array
     */
    public function getNoticeById($nid)
    {
        $sql = "SELECT * FROM $this->table_notice WHERE status=1 AND id=:nid";

        return $this->_rdb->fetchRow($sql, array('nid'=>$nid));
    }
    
    /**
     * get notice list
     *
     * @param integer $uid
     * @param integer $pageIndex
     * @param integer $pageSize
     * @return array
     */
    public function getNoticeList($uid, $pageIndex, $pageSize)
    {
        $sql = "SELECT * FROM $this->table_notice WHERE uid=:uid AND `status`=1 AND `type`<>9 ORDER BY id DESC ";
        
        if ( $pageIndex && $pageSize ) {
            $start = ($pageIndex - 1) * $pageSize;
            $sql .= " LIMIT $start,$pageSize";
        }

        return $this->_rdb->fetchAll($sql, array('uid'=>$uid));
    }

    /**
     * get notice count
     *
     * @param integer $uid
     * @return integer
     */
    public function getNoticeCount($uid)
    {
        //$sql = "SELECT COUNT(1) FROM $this->table_notice WHERE uid=:uid AND status=1 ";
        //modify by hide ticket notice type=9
        $sql = "SELECT COUNT(1) FROM $this->table_notice WHERE `status`=1 AND `type`<>9 AND uid=:uid";

        return $this->_rdb->fetchOne($sql, array('uid'=>$uid));
    }

    /**
     * get friend feed list
     *
     * @param array $fids
     * @param integer $pageIndex
     * @param integer $pageSize
     * @return array
     */
    public function getFriendFeedList($fids, $pageIndex, $pageSize)
    {
        $start = ($pageIndex - 1) * $pageSize;
        
        $fids = $this->_rdb->quote($fids);
        
        $sql = "SELECT u.uid,ap.name,ap.award_name,ap.award_icon,ap.create_time FROM disney_user AS u
                LEFT JOIN (SELECT a.*,p.name,p.award_name,p.award_icon FROM (SELECT * FROM disney_user_award WHERE uid IN ($fids) 
                ORDER BY id DESC) AS a,disney_place AS p WHERE p.pid=a.pid GROUP BY uid ) as ap ON ap.uid=u.uid
                WHERE u.uid IN ($fids) LIMIT $start,$pageSize ";

        return $this->_rdb->fetchAll($sql);
    }

    /**
     * get my mixi feed
     *
     * @param array $fids
     * @param integer $pageIndex
     * @param integer $pageSize
     * @return array
     */
    public function getMymixiFeed($fids, $pageIndex, $pageSize)
    {
        $start = ($pageIndex - 1) * $pageSize;
        
        $fids = $this->_rdb->quote($fids);
        
        $sql = "SELECT * FROM (SELECT * FROM 
                (SELECT a.id,a.uid,a.create_time,p.award_name AS name,p.award_icon AS icon,1 AS type FROM 
                ( SELECT * FROM disney_user_award WHERE uid IN ($fids) ORDER BY id DESC ) AS a, 
                disney_place AS p WHERE p.pid=a.pid GROUP BY uid 
                UNION 
                SELECT u.id,u.uid,u.create_time,c.name,icon,2 AS type FROM 
                ( SELECT * FROM disney_user_cup WHERE uid IN ($fids) ORDER BY id DESC ) AS u, 
                disney_cup AS c WHERE c.cid=u.cid GROUP BY uid ) AS ac ORDER BY create_time DESC) AS cs 
                GROUP BY uid LIMIT $start,$pageSize ";

        return $this->_rdb->fetchAll($sql);
    }
    
    public function getMymixiFeedCount($fids, $pageIndex, $pageSize)
    {
        $fids = $this->_rdb->quote($fids);
        
        $sql = "SELECT COUNT(1) FROM (
                SELECT * FROM (SELECT * FROM 
                (SELECT a.id,a.uid,a.create_time,p.award_name AS name,p.award_icon AS icon,1 AS type FROM 
                ( SELECT * FROM disney_user_award WHERE uid IN ($fids) ORDER BY id DESC ) AS a, 
                disney_place AS p WHERE p.pid=a.pid GROUP BY uid 
                UNION 
                SELECT u.id,u.uid,u.create_time,c.name,icon,2 AS type FROM 
                ( SELECT * FROM disney_user_cup WHERE uid IN ($fids) ORDER BY id DESC ) AS u, 
                disney_cup AS c WHERE c.cid=u.cid GROUP BY uid ) AS ac ORDER BY create_time DESC) AS cs 
                GROUP BY uid ) AS temp";

        return $this->_rdb->fetchOne($sql);
    }
}
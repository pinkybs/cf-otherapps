<?php

require_once 'Mdal/Abstract.php';

/**
 * Mdal School
 * MixiApp School AssistNewestTopic Data Access Layer
 *
 * @package    Mdal/School
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/11/11    zhangxin
 */
class Mdal_School_AssistNewestTopic extends Mdal_Abstract
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

    /**
     * list NewestTopic
     *
     * @param array $aryCid
     * @param integer $pageindex
     * @param integer $pagesize
     * @return array
     */
    public function listNewestTopic($aryCid, $pageindex = 1, $pagesize = 10)
    {
        $start = ($pageindex - 1) * $pagesize;
        $ids = $this->_rdb->quote($aryCid);
        $sql = "SELECT * FROM school_assist_newest_topic WHERE cid IN ($ids)
                ORDER BY update_time DESC LIMIT $start, $pagesize";

        return $this->_rdb->fetchAll($sql);
    }

    /**
     * get NewestTopic count
     *
     * @param array $aryCid
     * @return integer
     */
    public function getNewestTopicCount($aryCid)
    {
        $ids = $this->_rdb->quote($aryCid);
        $sql = "SELECT COUNT(cid) FROM school_assist_newest_topic WHERE cid IN ($ids) ";
        return $this->_rdb->fetchOne($sql);
    }

    /**
     * get NewestTopic
     *
     * @param integer $cid
     * @return integer
     */
    public function getNewestTopic($cid)
    {
        $sql = "SELECT * FROM school_assist_newest_topic WHERE cid=:cid ";
        return $this->_rdb->fetchRow($sql, array('cid' => $cid));
    }

    /**
     * insert NewestTopic
     *
     * @param array $info
     * @return integer
     */
    public function insertNewestTopic($info)
    {
        return $this->_wdb->insert('school_assist_newest_topic', $info);
    }

    /**
     * update NewestTopic
     *
     * @param integer $cid
     * @param integer $tid
     * @return integer
     */
    public function updateNewestTopic($info, $cid)
    {
        $where = $this->_wdb->quoteInto('cid=?', $cid);
        return $this->_wdb->update('school_assist_newest_topic', $info, $where);
    }

}
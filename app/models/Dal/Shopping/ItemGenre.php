<?php

require_once 'Dal/Abstract.php';

/**
 * Dal Shopping ItemGenre
 * MixiApp Shopping ItemGenre Data Access Layer
 *
 * @package    Dal/Shopping
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/08/12    zhangxin
 */
class Dal_Shopping_ItemGenre extends Dal_Abstract
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
     * get ItemGenre parent
     *
     * @param integer $gid
     * @return array
     */
    public function getItemGenreParent($gid)
    {
        $sql = 'SELECT * FROM shopping_nb_genre_parent WHERE gid=:gid ';
        return $this->_rdb->fetchRow($sql, array('gid' => $gid));
    }

    /**
     * insert ItemGenre parent
     *
     * @param array $info
     * @return integer
     */
    public function insertItemGenreParent($info)
    {
        return $this->_wdb->insert('shopping_nb_genre_parent', $info);
    }

    /**
     * update ItemGenre parent
     *
     * @param array $info
     * @param integer $gid
     * @return integer
     */
    public function updateItemGenreParent($info, $gid)
    {
        $where = $this->_wdb->quoteInto('gid=?', $gid);
        return $this->_wdb->update('shopping_nb_genre_parent', $info, $where);
    }

	/**
     * list ItemGenre parent
     *
     * @param Integer $pageindex
     * @param Integer $pagesize
     * @return array
     */
    public function listItemGenreParent($pageindex = 1, $pagesize = 10)
    {
        $start = ($pageindex - 1) * $pagesize;
        $sql = "SELECT * FROM shopping_nb_genre_parent ORDER BY gid LIMIT $start, $pagesize";
        return $this->_rdb->fetchAll($sql);
    }

	/**
     * get ItemGenre parent count
     *
     * @return integer
     */
    public function getItemGenreParentCount()
    {
        $sql = 'SELECT COUNT(gid) FROM shopping_nb_genre_parent ';
        return $this->_rdb->fetchOne($sql);
    }

	/**
     * list ItemGenre child
     *
     * @param Integer $gid
     * @param Integer $pageindex
     * @param Integer $pagesize
     * @return array
     */
    public function listItemGenreChildByParent($gid, $pageindex = 1, $pagesize = 10)
    {
        $start = ($pageindex - 1) * $pagesize;
        $sql = "SELECT * FROM shopping_nb_genre_child WHERE parent_gid=:parent_gid ORDER BY gid LIMIT $start, $pagesize";
        return $this->_rdb->fetchAll($sql, array('parent_gid' => $gid));
    }

	/**
     * get ItemGenre parent count
     *
     * @param Integer $gid
     * @return integer
     */
    public function getItemGenreChildByParentCount($gid)
    {
        $sql = 'SELECT COUNT(gid) FROM shopping_nb_genre_child WHERE parent_gid=:parent_gid ';
        return $this->_rdb->fetchOne($sql, array('parent_gid' => $gid));
    }

    /**
     * get ItemGenre Child
     *
     * @param integer $gid
     * @param integer $parentGid
     * @return array
     */
    public function getItemGenreChild($gid, $parentGid)
    {
        $sql = 'SELECT * FROM shopping_nb_genre_child WHERE gid=:gid AND parent_gid=:parent_gid';
        return $this->_rdb->fetchRow($sql, array('gid' => $gid, 'parent_gid' => $parentGid));
    }

    /**
     * insert ItemGenre Child
     *
     * @param array $info
     * @return integer
     */
    public function insertItemGenreChild($info)
    {
        return $this->_wdb->insert('shopping_nb_genre_child', $info);
    }

    /**
     * update ItemGenre Child
     *
     * @param array $info
     * @param integer $gid
     * @param integer $parentGid
     * @return integer
     */
    public function updateItemGenreChild($info, $gid, $parentGid)
    {
        $where = array($this->_wdb->quoteInto('gid=?', $gid),
                       $this->_wdb->quoteInto('parent_gid=?', $parentGid));
        return $this->_wdb->update('shopping_nb_genre_child', $info, $where);
    }

    /******************************************************/



}
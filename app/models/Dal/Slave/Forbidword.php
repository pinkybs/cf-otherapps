<?php

require_once 'Dal/Abstract.php';

/**
 * Dal Slave
 * MixiApp Slave Forbidword Data Access Layer
 *
 * @package    Dal/Slave
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/06/26    zhangxin
 */
class Dal_Slave_Forbidword extends Dal_Abstract
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
     * list Forbidword
     *
     * @return array
     */
    public function listForbidword()
    {
        $sql = 'SELECT * FROM slave_forbidword ';
        return $this->_rdb->fetchAll($sql);
    }

    /**
     * insert Forbidword
     *
     * @param array $info
     * @return integer
     */
    public function insertForbidword($info)
    {
        $this->_wdb->insert('slave_forbidword', $info);
        return $this->_wdb->lastInsertId();
    }

    /**
     * update Forbidword
     *
     * @param array $info
     * @param integer $id
     * @return integer
     */
    public function updateForbidword($info, $id)
    {
        $where = $this->_wdb->quoteInto('id = ?', $id);
        return $this->_wdb->update('slave_forbidword', $info, $where);
    }

	/**
     * delete Forbidword
     *
     * @param integer $id
     * @return integer
     */
    public function deleteForbidword($id)
    {
        $sql = "DELETE FROM slave_forbidword WHERE id=:id ";
        return $this->_wdb->query($sql, array('id' => $id));
    }

}
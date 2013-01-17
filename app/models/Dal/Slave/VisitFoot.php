<?php

require_once 'Dal/Abstract.php';

/**
 * Dal Slave
 * MixiApp Slave VisitFoot Data Access Layer
 *
 * @package    Dal/Slave
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/06/25    zhangxin
 */
class Dal_Slave_VisitFoot extends Dal_Abstract
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
     * list visit foot by uid
     *
     * @param string $uid
     * @return array
     */
    public function listVisitFootByUid($uid)
    {
        $sql = 'SELECT DISTINCT(v.uid) AS uid,u.nickname,FORMAT(u.price,0) AS price FROM slave_visit_foot v,slave_user u
				WHERE v.uid=u.uid AND v.visit_uid=:visit_uid ORDER BY v.create_time DESC LIMIT 0,5';
        return $this->_rdb->fetchAll($sql, array('visit_uid' => $uid));
    }

    /**
     * insert visit foot
     *
     * @param array $info
     * @return integer
     */
    public function insertVisitFoot($info)
    {
        $this->_wdb->insert('slave_visit_foot', $info);
        return $this->_wdb->lastInsertId();
    }

}
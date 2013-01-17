<?php

require_once 'Dal/Abstract.php';

/**
 * Dal Slave
 * MixiApp Slave CashPriceChangeLog Data Access Layer
 *
 * @package    Dal/Slave
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/06/26    zhangxin
 */
class Dal_Slave_CashPriceChangeLog extends Dal_Abstract
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
     * listCashPriceChangeLog by actor
     *
     * @param string $uid
     * @return array
     */
    public function listCashPriceChangeLogByActor($uid)
    {
        $sql = 'SELECT * FROM slave_cash_price_change_log WHERE actor_uid=:actor_uid
        		ORDER BY create_time DESC ';
        return $this->_rdb->fetchAll($sql, array('actor_uid' => $uid));
    }

	/**
     * listCashPriceChangeLog by target
     *
     * @param string $uid
     * @return array
     */
    public function listCashPriceChangeLogByTarget($uid)
    {
        $sql = 'SELECT * FROM slave_cash_price_change_log WHERE target_uid=:target_uid
        		ORDER BY create_time DESC ';
        return $this->_rdb->fetchAll($sql, array('target_uid' => $uid));
    }

    /**
     * insert CashPriceChangeLog
     *
     * @param array $info
     * @return integer
     */
    public function insertCashPriceChangeLog($info)
    {
        $this->_wdb->insert('slave_cash_price_change_log', $info);
        return $this->_wdb->lastInsertId();
    }
}
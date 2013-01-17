<?php

require_once 'Bll/Abstract.php';

/**
 * Mixi App Shopping logic Operation
 *
 * @package    Bll/Shopping
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/08/10    zhangxin
 */
final class Bll_Shopping_Shopping extends Bll_Abstract
{
    /**
     * new shopping user
     *
     * @param string $uid
     * @return boolean
     */
    public function newShoppingUser($uid)
    {
        try {
            require_once 'Dal/Shopping/Shopping.php';
            $dalShopping = Dal_Shopping_Shopping::getDefaultInstance();

            $this->_wdb->beginTransaction();

            $aryInfo = array();
            $aryInfo['uid'] = $uid;
            $aryInfo['last_login_time'] = time();
            $aryInfo['create_time'] = time();
            $dalShopping->insertShopping($aryInfo);

            $this->_wdb->commit();

            return true;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            debug_log('Bll/Shopping/Shopping/newShoppingUser:' . $e->getMessage());
            return false;
        }
    }


/******************************************************/
}
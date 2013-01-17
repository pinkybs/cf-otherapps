<?php

require_once 'Bll/Abstract.php';

/**
 * Mixi App Shopping logic Operation
 *
 * @package    Bll/wishItem
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/08/14    xiali
 */
final class Bll_Shopping_WishItem extends Bll_Abstract
{
    /**
     * add wishitem
     *
     * @param array $info
     * @return boolean
     */
    public function addWishItem($uid, $iid)
    {
        try {
            require_once 'Dal/Shopping/WishItem.php';
            $dalWishItem = Dal_Shopping_WishItem::getDefaultInstance();

            $this->_wdb->beginTransaction();
            $isExist = $dalWishItem->isWishItemExist($uid, $iid);

            if ($isExist) {
                $this->_wdb->rollBack();
                return false;
            }

            $aryInfo = array();
            $aryInfo['uid'] = $uid;
            $aryInfo['iid'] = $iid;
            $aryInfo['create_time'] = time();

            $dalWishItem->insertWishItem($aryInfo);

            $this->_wdb->commit();

            return true;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            debug_log('Bll/Shopping/WishItem/addWishItem:' . $e->getMessage());
            return false;
        }
    }

    /**
     * remove wishitem
     *
     * @param string $uid
     * @param integer $iid
     * @return boolean
     */
    public function removeWishItem($uid, $iid)
    {
        try {
            require_once 'Dal/Shopping/WishItem.php';
            $dalWishItem = Dal_Shopping_WishItem::getDefaultInstance();

            $this->_wdb->beginTransaction();

            $dalWishItem->deleteWishItem($uid, $iid);

            $this->_wdb->commit();

            return true;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            debug_log('Bll/Shopping/WishItem/removeWishItem:' . $e->getMessage());
            return false;
        }
    }


    /** insert system item
     *
     * @param array $info
     * @return boolean
     */
    public function addNbItem($info)
    {
        try {
            require_once 'Dal/Shopping/WishItem.php';
            $dalWishItem = Dal_Shopping_WishItem::getDefaultInstance();

            $this->_wdb->beginTransaction();

            $lastId = $dalWishItem->insertNbItem($info);

            $this->_wdb->commit();
            if ($lastId != 0) {
            	return true;
            }
            return false;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            debug_log('Bll/Shopping/WishItem/addNbItem:' . $e->getMessage());
            return false;
        }
    }


    /******************************************************/
}
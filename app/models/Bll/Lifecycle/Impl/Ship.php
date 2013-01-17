<?php

/**
 * application lifecycle event callback implementation for app ship
 *
 * @copyright  Copyright (c) 2009 Community Factory Inc. (http://communityfactory.com)
 * @create    2010/01/06    Liz
 */
class Bll_Lifecycle_Impl_Ship implements Bll_Lifecycle_Interface
{
    /**
     * user add app event callback
     *
     * @param int $app_id
     * @param int $uid
     * @param int $mixi_invite_from
     */
    public function add($app_id, $uid, $mixi_invite_from = null)
    {
        if ( $mixi_invite_from ) {
            require_once 'Mbll/Ship/User.php';
            $mbllShipUser = new Mbll_Ship_User();
            $mbllShipUser->inviteComplete($uid, $mixi_invite_from);
        }
    }

    /**
     * user remove app event callback
     *
     * @param int $app_id
     * @param int $uid
     */
    public function remove($app_id, $uid)
    {
        /*require_once 'Mbll/Ship/User.php';
        $mbllShipUser = new Mbll_Ship_User();
        return $mbllShipUser->removeShipUser($uid);*/
    }
}

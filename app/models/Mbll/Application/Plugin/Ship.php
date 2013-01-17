<?php

/** Bll_Application_Plugin_Interface */
require_once 'Bll/Application/Plugin/Interface.php';

/** Bll_Ship_User */
require_once 'Mbll/Ship/User.php';

class Mbll_Application_Plugin_Ship implements Bll_Application_Plugin_Interface
{
    public function postUpdatePerson($uid)
    {
        //check whether the user has joined this app
        $mbllShipUser = new Mbll_Ship_User();
        
        $isJoined = $mbllShipUser->isJoined($uid);
        if (!$isJoined) {
            $app = Mbll_Application::getInstance();
            $mbllShipUser->join($uid, $app->getAppId());
        }
    }

    public function postUpdateFriend($fid)
    {
        //TODO:
    }

    public function postUpdateFriendship($uid, array $fids)
    {
        //TODO:
    }

    public function updateAppFriendship($uid, array $fids)
    {
        //TODO:
    }

    public function postRun(Bll_Application_Interface $application)
    {
        $url = '/mobile/ship/top/cf_ts/' . time();
        $application->redirect($url);
    }
}

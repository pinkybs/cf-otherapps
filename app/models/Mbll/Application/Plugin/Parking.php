<?php

/** Bll_Application_Plugin_Interface */
require_once 'Bll/Application/Plugin/Interface.php';

class Mbll_Application_Plugin_Parking implements Bll_Application_Plugin_Interface
{
    public function postUpdatePerson($uid)
    {
        
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
        $url = '/mobile/parking/help/cf_ts/' . time();
        $application->redirect($url);
    }
}

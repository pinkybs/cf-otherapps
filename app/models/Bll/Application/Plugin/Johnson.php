<?php

/** Bll_Application_Plugin_Interface */
require_once 'Bll/Application/Plugin/Interface.php';

class Bll_Application_Plugin_Johnson implements Bll_Application_Plugin_Interface
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

    public function updateAppFriendship($uid, array $fidsHasApp)
    {
        //TODO:
    }

    public function postRun(Bll_Application_Interface $application)
    {
        $request = $application->getRequest();

        $url = '/johnson';
        $application->redirect($url);
    }
}

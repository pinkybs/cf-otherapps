<?php

/** Bll_Application_Plugin_Interface */
require_once 'Bll/Application/Plugin/Interface.php';

/** Bll_Dynamite_User */
require_once 'Bll/Dynamite/User.php';

class Mbll_Application_Plugin_Dynamite implements Bll_Application_Plugin_Interface
{
    public function postUpdatePerson($uid)
    {
        // check whether the user has joined this app
        //$bllDynamiteUser = new Bll_Dynamite_User();

        //$isJoined = $bllDynamiteUser->isJoined($uid);

        //if (!$isJoined) {

        	//if user is invited, auto change user and inviter to alliance
            //$application = Bll_Application::getInstance();

            //inviter id
        	//$inviterId = $application->getInvite();

            //$bllDynamiteUser->join($uid);
        //}
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
        $url = '/mobile/dynamite/index/cf_ts/' . time();
        $application->redirect($url);
    }
}

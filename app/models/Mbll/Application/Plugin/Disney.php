<?php

/** Bll_Application_Plugin_Interface */
require_once 'Bll/Application/Plugin/Interface.php';

/** Bll_Disney_User */
require_once 'Mbll/Disney/User.php';

class Mbll_Application_Plugin_Disney implements Bll_Application_Plugin_Interface
{
    public function postUpdatePerson($uid)
    {
        // check whether the user has joined this app
        $mbllDisneyUser = new Mbll_Disney_User();
        
        $isJoined = $mbllDisneyUser->isJoined($uid);

        if (!$isJoined) {
        	//if user is invited, auto change user and inviter to alliance
            //$application = Bll_Application::getInstance();

            //inviter id
        	//$inviterId = $application->getInvite();
        	
            $app = Mbll_Application::getInstance();
            
            $mbllDisneyUser->join($uid, $app->getAppId());
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
        $url = '/mobile/disney/index/cf_ts/' . time();
        $application->redirect($url);
    }
}

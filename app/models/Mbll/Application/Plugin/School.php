<?php

/** Bll_Application_Plugin_Interface */
require_once 'Bll/Application/Plugin/Interface.php';

/** Mbll_School_User */
require_once 'Mbll/School/User.php';

class Mbll_Application_Plugin_School implements Bll_Application_Plugin_Interface
{
    public function postUpdatePerson($uid)
    {
        // check whether the user has joined this app
        $mbllUser = new Mbll_School_User();
        $isJoined = $mbllUser->isJoined($uid);
        if (!$isJoined) {
        	$application = Mbll_Application::getInstance();
            $mbllUser->newSchoolUser($uid, $application->getAppId());
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
        $url = '/mobile/school/index/cf_ts/' . time();
        $application->redirect($url);
    }
}

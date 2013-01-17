<?php

/** Bll_Application_Plugin_Interface */
require_once 'Bll/Application/Plugin/Interface.php';

/** Bll_Chomeboard_User */
require_once 'Bll/Chomeboard/User.php';

/** Bll_Chomeboard_Chomeboard */
require_once 'Bll/Chomeboard/Chomeboard.php';

class Bll_Application_Plugin_Chomeboard implements Bll_Application_Plugin_Interface
{
    public function postUpdatePerson($uid)
    {
        // check whether the user has joined this app
        if (! Bll_Chomeboard_User::isJoined($uid)) {
            Bll_Chomeboard_User::join($uid);
        }
        
        $bllChomeboard = new Bll_Chomeboard_Chomeboard();
        $bllChomeboard->newChomeBoard4Friends($uid);
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
        $request = $application->getRequest();
        // get other params
        $uid = $request->getParam('uid');

        // get viewerId
        $viewerId = $application->getViewerId();
        //
        if (empty($uid) || $uid == $viewerId) {
            // show the user owner top page
            $url = '/chomeboard';
            $application->redirect($url);
        }
        else {
            $target = Bll_User::getPerson($uid);
            if ($target) {
                // show the target user top page
                $url = '/chomeboard?uid=' . $uid;
                $application->redirect($url);
            }
            else {
                $application->redirect404();
            }
        }
    }
}

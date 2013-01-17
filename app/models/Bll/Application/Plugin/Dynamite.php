<?php

/** Bll_Application_Plugin_Interface */
require_once 'Bll/Application/Plugin/Interface.php';

/** Bll_Dynamite_User */
require_once 'Bll/Dynamite/User.php';

class Bll_Application_Plugin_Dynamite implements Bll_Application_Plugin_Interface
{
    public function postUpdatePerson($uid)
    {
        // check whether the user has joined this app
        $bllDynamiteUser = new Bll_Dynamite_User();

        $isJoined = $bllDynamiteUser->isJoined($uid);

        if (!$isJoined) {
            $bllDynamiteUser->join($uid);
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
        $request = $application->getRequest();
        // get other params
        $uid = $request->getParam('uid');

        // get viewerId
        $viewerId = $application->getViewerId();
        //
        if (empty($uid) || $uid == $viewerId) {
            // show the user owner top page
            $url = '/dynamite/start';
            $application->redirect($url);
        }
        else {
            $target = Bll_User::getPerson($uid);
            if ($target) {
                // show the target user top page
                $url = '/dynamite/start?uid=' . $uid;
                $application->redirect($url);
            }
            else {
                $application->redirect404();
            }
        }
    }
}

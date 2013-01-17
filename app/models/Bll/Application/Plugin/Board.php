<?php

/** Bll_Application_Plugin_Interface */
require_once 'Bll/Application/Plugin/Interface.php';

/** Bll_Board_User */
require_once 'Bll/Board/User.php';

class Bll_Application_Plugin_Board implements Bll_Application_Plugin_Interface
{
    public function postUpdatePerson($uid)
    {
        // check whether the user has joined this app
        if (! Bll_Board_User::isJoined($uid)) {
            Bll_Board_User::join($uid);
        }
    }
    
    public function postUpdateFriend($fid)
    {
        // check whether the user has joined this app
        if (! Bll_Board_User::isJoined($fid)) {
            Bll_Board_User::join($fid);
        }
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
        // get other params
        $uid = $request->getParam('uid');

        $op = $request->getParam('op');

        if ($op == 'setting') {
            // show the user owner setting page
            $url = '/board/getinfo';
            $application->redirect($url);
        }

        // get viewerId
        $viewerId = $application->getViewerId();
        //
        if (empty($uid) || $uid == $viewerId) {
            // show the user owner top page
            $url = '/board/list';
            $application->redirect($url);
        }
        else {
            $target = Bll_User::getPerson($uid);
            if ($target) {
                // show the target user top page
                $url = '/board/list?uid=' . $uid;
                $application->redirect($url);
            }
            else {
                $application->redirect404();
            }
        }
    }
}

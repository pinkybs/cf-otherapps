<?php

/** Bll_Application_Plugin_Interface */
require_once 'Bll/Application/Plugin/Interface.php';

/** Bll_Dynamite_User */
require_once 'Bll/Board/User.php';

class Mbll_Application_Plugin_Board implements Bll_Application_Plugin_Interface
{

    public function postUpdatePerson($uid)
    {
        // check whether the user has joined this app
        $bllBoardUser = new Bll_Board_User();

        $isJoined = $bllBoardUser->isJoined($uid);

        if (!$isJoined) {
            $bllBoardUser->join($uid);
        }
    }

    public function postUpdateFriend($fid)
    {
        // check whether user's friend has joined this app
        $bllBoardUser = new Bll_Board_User();

        $isJoined = $bllBoardUser->isJoined($fid);

        if (!$isJoined) {
            $bllBoardUser->join($fid);
        }
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
        $url = '/mobile/board/list/cf_ts/' . time();
        $application->redirect($url);
    }
}

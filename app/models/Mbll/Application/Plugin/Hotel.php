<?php

/** Bll_Application_Plugin_Interface */
require_once 'Bll/Application/Plugin/Interface.php';


class Mbll_Application_Plugin_Hotel implements Bll_Application_Plugin_Interface
{
    public function postUpdatePerson($uid)
    {
        // check whether the user has joined this app
        //$bllHotelUser = new Bll_Hotel_User();

        //$isJoined = $bllHotelUser->isJoined($uid);

        //if (!$isJoined) {

        	//if user is invited, auto change user and inviter to alliance
            //$application = Bll_Application::getInstance();

            //inviter id
        	//$inviterId = $application->getInvite();

            //$bllHotelUser->join($uid);
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
        $url = '/mobile/hotel/index/cf_ts/' . time();
        $application->redirect($url);
    }
}

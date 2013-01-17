<?php

/** Bll_Application_Plugin_Interface */
require_once 'Bll/Application/Plugin/Interface.php';

/** Bll_Parking_Friend */
require_once 'Bll/Parking/Friend.php';

class Bll_Application_Plugin_Parking implements Bll_Application_Plugin_Interface
{
    public function postUpdatePerson($uid)
    {
        //TODO:
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
        // update user friends relationship that has joined app
        Bll_Parking_Friend::updateFriends($uid, $fidsHasApp);
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
            $url = '/parking/index';
            $application->redirect($url);
        }
        else {
            $target = Bll_User::getPerson($uid);
            if ($target) {
                // show the target user top page
                $url = '/parking/index?uid=' . $uid;
                $application->redirect($url);
            }
            else {
                $application->redirect404();
            }
        }
    }
}

<?php

/** Bll_Application_Plugin_Interface */
require_once 'Bll/Application/Plugin/Interface.php';

/** Bll_Scripteditor_Friend */
require_once 'Bll/Scripteditor/Friend.php';

class Bll_Application_Plugin_Scripteditor implements Bll_Application_Plugin_Interface
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
        Bll_Scripteditor_Friend::updateFriends($uid, $fidsHasApp);
    }
    
    public function postRun(Bll_Application_Interface $application)
    {
        $request = $application->getRequest();
        // get other params
        //$uid = $request->getParam('uid');

        $eid = $request->getParam('eid');
        if ($eid) {
            $url = '/scripteditor/entry/eid/' . $eid;
        }
        else {
            //go to scripteditor app
            $url = '/scripteditor/index';
        }

        $application->redirect($url);
    }
}

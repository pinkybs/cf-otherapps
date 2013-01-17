<?php

/** Bll_Application_Plugin_Interface */
require_once 'Bll/Application/Plugin/Interface.php';

/** Bll_Chat_Friend */
require_once 'Bll/Chat/Friend.php';

class Bll_Application_Plugin_Chat implements Bll_Application_Plugin_Interface
{
    public function postUpdatePerson($uid)
    {
        $application = Bll_Application::getInstance();

        if ($application->isViewer($uid)) {
            $isAlreadyAppUser = Bll_Chat_User::isAppUser($uid);
            $application->setData('isAlreadyAppUser', $isAlreadyAppUser);
        }

        Bll_Chat_User::updateUser($uid);
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
        Bll_Chat_Friend::updateFriends($uid, $fidsHasApp);
    }

    public function postRun(Bll_Application_Interface $application)
    {
        $request = $application->getRequest();
        // get other params
        //$uid = $request->getParam('uid');

        //go to chat app
        try {
            $isAlreadyAppUser = $application->getData('isAlreadyAppUser');
        }
        catch (Exception $e) {
            $isAlreadyAppUser = true;
        }

        if ($isAlreadyAppUser) {
            $url = '/chat';
        }
        else {
            $url = '/chat/guide';
        }

        //$application->redirect404();
        $application->redirect($url);
    }
}

<?php

/** Bll_Application_Plugin_Interface */
require_once 'Bll/Application/Plugin/Interface.php';

/** Bll_Slave_Friend */
require_once 'Bll/Slave/Friend.php';

class Bll_Application_Plugin_Slave implements Bll_Application_Plugin_Interface
{
    public function postUpdatePerson($uid)
    {
        $application = Bll_Application::getInstance();
        if ($application->isViewer($uid)) {
            $rowAppUser = Bll_Slave_User::isAppUser($uid);
            if (!$rowAppUser) {
                require_once 'Bll/Slave/Slave.php';
                $bllSlave = new Bll_Slave_Slave();
                $bllSlave->newSlaveUser($uid, true);
            }
            else {
                if (1 == $rowAppUser['status']) {
                    require_once 'Dal/Slave/Slave.php';
                    $dalUser = Dal_Slave_Slave::getDefaultInstance();
                    $dalUser->updateSlave(array('status' => 0), $uid);
                }
            }
        }
        //Bll_Slave_User::updateUser($uid);
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
        Bll_Slave_Friend::updateFriends($uid, $fidsHasApp);
    }
    
    public function postRun(Bll_Application_Interface $application)
    {
        $request = $application->getRequest();
        // get other params
        $uid = $request->getParam('uid');

        // get viewerId
        $viewerId = $application->getViewerId();
        if (empty($uid) || $uid == $viewerId) {
            // show the user owner top page
            $url = '/slave/home';
        }
        else {
            $url = '/slave/profile?uid=' . $uid;
        }
        $application->redirect($url);
    }
}

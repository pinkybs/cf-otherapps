<?php

/** Bll_Application_Plugin_Interface */
require_once 'Bll/Application/Plugin/Interface.php';

/** Bll_Shopping_Friend */
require_once 'Bll/Shopping/Friend.php';

class Bll_Application_Plugin_Shopping implements Bll_Application_Plugin_Interface
{
    public function postUpdatePerson($uid)
    {
        $application = Bll_Application::getInstance();
        if ($application->isViewer($uid)) {
            $rowAppUser = Bll_Shopping_User::isAppUser($uid);
            if (!$rowAppUser) {
                require_once 'Bll/Shopping/Shopping.php';
                $bllShopping = new Bll_Shopping_Shopping();
                $bllShopping->newShoppingUser($uid, true);
            }
            else {
                if (1 == $rowAppUser['status']) {
                    require_once 'Dal/Shopping/Shopping.php';
                    $dalUser = Dal_Shopping_Shopping::getDefaultInstance();
                    $dalUser->updateShopping(array('status' => 0), $uid);
                }
            }
        }
        //Bll_Shopping_User::updateUser($uid);
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
        Bll_Shopping_Friend::updateFriends($uid, $fidsHasApp);
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
            $url = '/shopping/home';
        }
        else {
            $url = '/shopping/wish?uid=' . $uid;
        }
        $application->redirect($url);
    }
}

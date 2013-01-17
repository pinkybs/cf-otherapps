<?php

/** Bll_Application_Plugin_Interface */
require_once 'Bll/Application/Plugin/Interface.php';

class Bll_Application_Plugin_Millionminds implements Bll_Application_Plugin_Interface
{
    private $_isIn = true;
    
    public function postUpdatePerson($uid)
    {
        //TODO:
        require_once 'Dal/Millionminds/Muser.php';
        $dalMillionmindsMuser = Dal_Millionminds_Muser::getDefaultInstance();

        $this->_isIn = $dalMillionmindsMuser->isInMillionminds($uid);
        
        if (!$this->_isIn) {
            //add user to millionminds
            require_once 'Bll/Millionminds/Nature.php';
            $bllNature = new Bll_Millionminds_Nature();
            $userInfo = array('uid'=>$uid,
                              'nature_result'=>0,
                              'create_time'=>time());
            $bllNature->insertMillionmindsUser($userInfo);
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
    
    public function updateAppFriendship($uid, array $fidsHasApp)
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
        
        //first login
        if (!$this->_isIn) {
            $url = '/millionminds/welcome';
            $application->redirect($url);
        }
        
        //
        if (empty($uid) || $uid == $viewerId) {
            // show the user owner top page
            $url = '/millionminds/index';
            $application->redirect($url);
        }
        else {
            $target = Bll_User::getPerson($uid);
            if ($target) {
                // show the target user top page
                $url = '/millionminds/' . $uid . '/profile';
                $application->redirect($url);
            }
            else {
                $application->redirect404();
            }
        }
    }
}

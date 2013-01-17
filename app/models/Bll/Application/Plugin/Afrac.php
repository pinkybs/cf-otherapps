<?php

/** Bll_Application_Plugin_Interface */
require_once 'Bll/Application/Plugin/Interface.php';

class Bll_Application_Plugin_Afrac implements Bll_Application_Plugin_Interface
{
    private $_isIn = true;
    
    public function postUpdatePerson($uid)
    {
        //TODO:
        //if not in,insert afrac_user
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
        
        $url = '/afrac';
        $application->redirect($url);
    }
}

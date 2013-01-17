<?php

/* Bll_Application */
require_once 'Bll/Application.php';

interface Bll_Application_Plugin_Interface
{
    public function postUpdatePerson($uid);
    
    public function postUpdateFriend($fid);
    
    public function postUpdateFriendship($uid, array $fids);
    
    public function updateAppFriendship($uid, array $fidsHasApp);
    
    public function postRun(Bll_Application_Interface $application);
}

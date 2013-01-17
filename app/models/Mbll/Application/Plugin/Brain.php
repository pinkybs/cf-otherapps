<?php

/** Bll_Application_Plugin_Interface */
require_once 'Bll/Application/Plugin/Interface.php';


class Mbll_Application_Plugin_Brain implements Bll_Application_Plugin_Interface
{
    public function postUpdatePerson($uid)
    {        
        //insert or update user info
        require_once 'Mdal/Brain/Brain.php';
        $mdalBrain = Mdal_Brain_Brain::getDefaultInstance();        
        $isInBrain = $mdalBrain->isInBrain($uid);        
        
        if ($isInBrain) {
            require_once 'Mbll/Brain/Brain.php';
            $mbllBrain = new Mbll_Brain_Brain();            
            $mbllBrain->insertUser($uid);
            
            
            
            //require_once 'Mbll/Brain/Activity.php';
            //$title = Mbll_Brain_Activity::getActivity(1,0);
            
            //$app = Mbll_Application::getInstance();
            
            //require_once 'Bll/Restful.php';
            //get restful object
            //$restful = Bll_Restful::getInstance($uid, $app->getAppId());
            //$restful->createActivity(array('title'=>$title));
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

    public function updateAppFriendship($uid, array $fids)
    {
        //TODO:
    }

    public function postRun(Bll_Application_Interface $application)
    {
        $url = '/mobile/brain/top/cf_ts/' . time();
        $application->redirect($url);
    }
}

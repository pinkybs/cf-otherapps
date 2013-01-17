<?php

require_once 'Dal/Invite.php';

class Bll_Application_Log
{ 
    public static function invite($app_id, $actor, $target, $count, $type = 'pc')
    {
        try {
            $dalLogInvite = Dal_Log_Invite::getDefaultInstance();
            $time = time();
            return $dalLogInvite->add($app_id, $actor, $target, $count, $type, $time);
        }
        catch (Exception $e) {
            return null;
        }
    }
    
    public static function activity($app_id, $actor, $target, $count, $type = 'pc')
    {
        try {
            $dalLogActivity = Dal_Log_Activity::getDefaultInstance();
            $time = time();
            return $dalLogActivity->add($app_id, $actor, $target, $count, $type, $time);
        }
        catch (Exception $e) {
            return false;
        }
    }
    
    public static function view($app_id, $owner_id, $viewer_id, $view)
    {
        try {
            $dalLogView = Dal_Log_View::getDefaultInstance();
            $time = time();
            return $dalLogView->add($app_id, $owner_id, $viewer_id, $view, $time);
        }
        catch (Exception $e) {
            return false;
        }
    }
}
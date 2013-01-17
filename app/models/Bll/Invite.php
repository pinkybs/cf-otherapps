<?php

require_once 'Dal/Invite.php';

class Bll_Invite
{
    public static function get($app_id, $target)
    {
        $ids = array();
        try {
            $dalInvite = Dal_Invite::getDefaultInstance();
            $rows = $dalInvite->getInvite($app_id, $target);
            if (!empty($rows)) {
                foreach($rows as $actor) {
                    $ids[] = $actor['actor'];
                }

                //update invite status
                $dalInvite->updateInvite($app_id, $target);
            }
        }
        catch (Exception $e) {

        }

        return $ids;
    }

    public static function add($app_id, $actor, $target)
    {
        try {
            $dalInvite = Dal_Invite::getDefaultInstance();
            $time = time();
            return $dalInvite->addInvite($app_id, $actor, $target, $time);
        }
        catch (Exception $e) {
            return false;
        }
    }

    public static function addMultiple($app_id, $actor, $targets)
    {
        if(is_string($targets)) {
            $targets = explode(',', $targets);
        }

        $dalInvite = Dal_Invite::getDefaultInstance();
        $db = $dalInvite->getWriter();
        $time = time();
        try {
            $db->beginTransaction();
            foreach ($targets as $target) {
                $dalInvite->addInvite($app_id, $actor, $target, $time);
            }
            $db->commit();
            return true;
        }
        catch (Exception $e) {
            $db->rollBack();
            err_log($e->getMessage());
            return false;
        }
    }
    
    public static function update($app_id, $target, $actor)
    {
        try {
            $dalInvite = Dal_Invite::getDefaultInstance();
            return $dalInvite->updateInviteOne($app_id, $target, $actor);
        }
        catch (Exception $e) {
            return false;
        }
    }
    
    public static function updateMultiple($app_id, $targets)
    {
        if(is_string($targets)) {
            $targets = explode(',', $targets);
        }

        $dalInvite = Dal_Invite::getDefaultInstance();
        $db = $dalInvite->getWriter();
        try {
            $db->beginTransaction();
            foreach ($targets as $target) {
                $dalInvite->updateInvite($app_id, $target);
            }
            $db->commit();
            return true;
        }
        catch (Exception $e) {
            $db->rollBack();
            err_log($e->getMessage());
            return false;
        }
    }

    public static function delete($app_id, $target)
    {
        try {
            $dalInvite = Dal_Invite::getDefaultInstance();
            return $dalInvite->deleteInvite($app_id, $target);
        }
        catch (Exception $e) {
            return false;
        }
    }

}
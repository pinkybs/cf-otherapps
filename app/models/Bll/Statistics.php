<?php

class Bll_Statistics
{
    public static function addLogin($app_id, $uid, $invite_from)
    {
        $dalStatistics = Dal_Statistics::getDefaultInstance();
        $mdalUser = Mdal_Kitchen_User::getDefaultInstance();
        $db = $dalStatistics->getWriter();
        $time = time();

        try {
            if($invite_from) {
                $log = $dalStatistics->getLoginLog($app_id, $uid);
                if (!$log) {
                    $user = $mdalUser->getUser($uid);
                    if(!$user) {
                        $info = array('app_id' => $app_id,
                                'uid' => $uid,
                                'isInvite' => 1,
                                'login_time' => $time
                            );
                        
                        $db->beginTransaction();
                        $dalStatistics->insertLoginLog($info);
                        $db->commit();
                    }
                }
                
                return true;
            } else {
                if ($uid) {
                    
                    $db->beginTransaction();

                    foreach ($uid as $id) {
                        $log = $dalStatistics->getLoginLog($app_id, $id);
                        if(!$log) {
                            $user = $mdalUser->getUser($id);
                            if (!$user) {
                                $info = array('app_id' => $app_id,
                                    'uid' => $id,
                                    'isInvite' => 0,
                                    'login_time' => $time
                                );
                                $dalStatistics->insertLoginLog($info);
                            }
                        }
                    }
                    
                    $db->commit();
                    return true;
                }
            }
        }
        catch (Exception $e) {
            $db->rollBack();
            err_log($e->getMessage());
            return false;
        }
    }
    
    public static function addRemove($app_id, $uid)
    {
        $dalStatistics = Dal_Statistics::getDefaultInstance();
        $db = $dalStatistics->getWriter();
        $time = time();
        
        try {
            if(!is_array($uid)) {
                $log = $dalStatistics->getRemoveLog($app_id, $id);
                if(!$log) {
                    $info = array('app_id' => $app_id,
                        'uid' => $uid,
                        'remove_time' => $time
                    );
                    $dalStatistics->insertRemoveLog($info);
                }

                return true;
            } else {
                $db->beginTransaction();
        
                foreach ($uid as $id) {
                    $log = $dalStatistics->getRemoveLog($app_id, $id);
                    if(!$log) {
                        $info = array('app_id' => $app_id,
                            'uid' => $id,
                            'remove_time' => $time
                        );
                        $dalStatistics->insertRemoveLog($info);
                    }
                }
                
                $db->commit();
                return true;
            }
        }
        catch (Exception $e) {
            $db->rollBack();
            err_log($e->getMessage());
            return false;
        }
        
    }
}
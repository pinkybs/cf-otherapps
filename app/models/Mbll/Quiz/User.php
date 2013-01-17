<?php

require_once 'Mbll/Abstract.php';

class Mbll_Quiz_User extends Mbll_Abstract
{

    public function isJoined($uid)
    {
        $mDalUser = Mdal_Quiz_User::getDefaultInstance();
        $user = $mDalUser->getUser($uid);

        if ($user) {
            return true;
        }
        else {
            return false;
        }
    }

    public function join($uid)
    {
        $result = false;

        try {
            $this->_wdb->beginTransaction();

            $mDalUser = Mdal_Quiz_User::getDefaultInstance();
            $time = time();
            $userInfo = array(
                'uid' => $uid,
                'create_time' => $time
            );
            $mDalUser->insertUser($userInfo);
            $this->_wdb->commit();
            $result = true;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return false;
        }

        return $result;
    }
}
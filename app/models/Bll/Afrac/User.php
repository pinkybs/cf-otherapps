<?php
/** @see Bll_Abstract.php */
require_once 'Bll/Abstract.php';

/**
 * afrac user logic's Operation
 *
 * @package    Bll
 * @copyright  Copyright (c) 2009 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/08/05    Zhaoxh
 */
class Bll_Afrac_User extends Bll_Abstract
{
    /**
     * update user score
     *
     * @param string $uid
     * @param  string $score
     * @return boolean
     */
    public function updateScore($uid,$score)
    {
        $result = 0;

        require_once 'Dal/Afrac/User.php';
        $dalUser = Dal_Afrac_User::getDefaultInstance();
        
        try {
            $this->_wdb->beginTransaction();
            
            //update user score
            $dalUser->updateScore($uid,$score);
            
            $this->_wdb->commit();
            
            $result = 1;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return $result;
        }
        return $result;
    }

    /**
     * insert a user 
     *
     * @param array $userInfo
     * @return boolean
     */
    public function insertUser($userInfo)
    {
        $result = false;

        $this->_wdb->beginTransaction();
        
        try {
            require_once 'Dal/Afrac/User.php';
            $dalUser = Dal_Afrac_User::getDefaultInstance();
            //insert user 
            $dalUser->insertUser($userInfo);
            
            $this->_wdb->commit();
            
            $result = true;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return $result;
        }
        return $result;
    }
    
	public function refreshAfracTmp()
    {

        try {
            require_once 'Dal/Afrac/User.php';
            $dalRank = Dal_Afrac_User::getDefaultInstance();

            $dalRank->doBatch();

        }
        catch (Exception $e) {
            debug_log('refreshAfracTmp Error Happened!' . $e->getMessage());
        }
    }
}
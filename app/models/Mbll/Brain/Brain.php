<?php

require_once 'Mbll/Abstract.php';

/**
 * game
 *
 * @package    Mbll
 * @copyright  Copyright (c) 2009 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/09/11    xial
 */
class Mbll_Brain_Brain extends Mbll_Abstract
{
    public function updateGameScore($info)
    {        
    	$result = false;
        try {
            $this->_wdb->beginTransaction();

            require_once 'Mdal/Brain/Brain.php';
            $dalGame = Mdal_Brain_Brain::getDefaultInstance();
            $CF_Uid = $info['uid'];
            $CF_Gid = $info['gid'];
            //check game score isexists
            $result = $dalGame->isExistsGameScore($CF_Gid, $CF_Uid);
                        
            if ($result){
            	$oldScore = $dalGame->getScore($CF_Uid,$CF_Gid);
            	
                $dalGame->updateGameScore($CF_Uid, $CF_Gid, $info['newScore']);
                
                $dalGame->updateTotalScore($CF_Uid,$info['newScore']-$oldScore['score']);
            }
            else {
                $set = array('uid' => $CF_Uid,
                             'gid' => $CF_Gid,
                             'score' => $info['newScore']);
                $dalGame->insertGameScore($set);
                
                //$dalGame->updateLastTime($CF_Uid,time());
                $dalGame->updateTotalScore($CF_Uid,$info['newScore']);
                
                
                //set inviter`s game5 to open
            	//$dalGame->setBsyouOn($CF_Uid);
            }
            
            $this->_wdb->commit();
            $result = true;
        }
        catch (Exception $e){
            $this->_wdb->rollBack();
            return $result;
        }

        return $result;
    }
    
    /**
     * insert user
     *
     * @param integer $uid
     * @return boolean
     */
    public function insertUser($uid)
    {
    	$result = false;
        try {
            $this->_wdb->beginTransaction();

            require_once 'Mdal/Brain/Brain.php';
            $dalGame = Mdal_Brain_Brain::getDefaultInstance();
            
            $info = array('uid' => $uid,
                          'create_time' => time(),
            	          'last_update_time' => time());
            	          
            $id = $dalGame->insertUser($info);            
            
            //set inviter`s game5 to open
            //$dalGame->setBsyouOn($uid);
            
            $this->_wdb->commit();
            $result = true;
        }
        catch (Exception $e){
            debug_log('Mbll_Brain_Brain e: ' . $e->getMessage());
            $this->_wdb->rollBack();
            return $result;
        }

        return $result;
    }
    
	/**
     * invite user
     *
     * @param integer $uid
     * @param string $recipientIds
     * @return string
     */
    public function invite($uid, $recipientIds)
    {        
        $result = false;
    	$inviteArray = explode(',', $recipientIds);
        
        if ( !$inviteArray ) {
            return $result;
        }
        
        require_once 'Mdal/Brain/Brain.php';
        $mdalBrain = Mdal_Brain_Brain::getDefaultInstance();
                
        try {
            $this->_wdb->beginTransaction();
	        //insert invite info
	    	for ( $i = 0, $iCount = count($inviteArray); $i < $iCount; $i++ ) {
	            $mdalBrain->insertInvite($uid, $inviteArray[$i]);
	        }
	        $this->_wdb->commit();
	        $result = true;
        }
    	catch (Exception $e){
            debug_log('Mbll_Brain_Brain e: ' . $e->getMessage());
            $this->_wdb->rollBack();
        }
        return $result; 
    }
    
    public function updateUserBsyou($uid)
    {
        require_once 'Mdal/Brain/Brain.php';
        $dalGame = Mdal_Brain_Brain::getDefaultInstance();
        
        $friend = $dalGame->getInviteUser($uid);
        
        $fid = "1";
        foreach ($friend as $item) {
            $fid .= "," . $item['fid'];
        }
        
        if ($fid != "1") {
            $result = $dalGame->checkBsyou($fid);
            
            if ($result) {
                $dalGame->updateUserBsyou($uid);
            }
        }
    }
}
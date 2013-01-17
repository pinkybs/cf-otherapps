<?php

require_once 'Bll/Abstract.php';

/**
 * Mixi App Hotel Competition action logic Operation
 *
 * @package    Bll/Hotel
 * @copyright  Copyright (c) 2009 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/11/25   zhaoxh
 */
class Bll_Hotel_Competition extends Bll_Abstract
{

   
    public function joinCompetition($uid,$numA,$numB,$numC)
    {
        $resultArray = array('result' => 0);
        try {

            $this->_wdb->beginTransaction();

            require_once 'Dal/Hotel/Competition.php';
            $dalCompetition = Dal_Hotel_Competition::getDefaultInstance();
            $joined = $dalCompetition->isJoined($uid);
            
            //validate data 
            if ($numA + $numB + $numC != 100) {
            	$resultArray = array('result' => -1);
            	return $resultArray;
            }
            
            if ($joined) {
            	$resultArray = array('result' => -2);
            	return $resultArray;
            }
            //end validate
            
            
            $isIn = $dalCompetition->isInCompetition($uid);
           
            if ($isIn) {
            	$set = array(
            	             'numA' => $numA,
            	             'numB' => $numB,
            	             'numC' => $numC,
            	             'joined' => 1,
            	             'join_time' => time());
            	$dalCompetition->updateCompetition($uid,$set);
            }
            else {
            	$set = array('uid' => $uid,
            	             'numA' => $numA,
            	             'numB' => $numB,
            	             'numC' => $numC,
            	             'joined' => 1,
            	             'join_time' => time());
            	$dalCompetition->insertCompetition($set);
            }
            
            $this->_wdb->commit();

            $resultArray = array('result' => 1);
            
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return $resultArray;
        }
        return $resultArray;
    }
    
	public function gainCompetition($uid)
    {
        $resultArray = array('result' => 0);
        try {
            require_once 'Dal/Hotel/Competition.php';
            $dalCompetition = Dal_Hotel_Competition::getDefaultInstance();

            $lastResult = $dalCompetition->getResult($uid);
       		if ($lastResult > 0) {
        		$moneyGain = 10000 * pow(2,$lastResult-1);
        		$expGain = pow(2,$lastResult-1);
        	}
        	else if ($lastResult == 0){
        		$moneyGain = 0;
        		$expGain = 0;
        		$lastResult = 12; //-12 instead of "-0" value
        	}
       	    else {
            	return $resultArray;
            }
            
            require_once 'Dal/Hotel/Huser.php';
        	$dalHuser = Dal_Hotel_Huser::getDefaultInstance();
            
        	$money = $dalHuser->getOneData($uid,'money');
        	$exp = $dalHuser->getOneData($uid,'experience');
        	
        	
        	$set = array('money' => $money + $moneyGain,
            			 'experience' => $exp + $expGain,
        	             'level' => $dalHuser->getUserLvByExp($exp + $expGain));
        	$set2 = array('last_result' => abs($lastResult) * (-1),
        	              'joined' => 0);
        	
        	$this->_wdb->beginTransaction();
        	
        	$dalHuser->upHuser($uid,$set);
        	$dalCompetition->updateCompetition($uid,$set2);
            
            $this->_wdb->commit();

            $resultArray = array('exp' => $expGain,
                                 'money' => $moneyGain,
                                 'date' => date(),
                                 'last_result' => $lastResult,
                                 'result' => 1);
            
            
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return $resultArray;
        }
        return $resultArray;
    }
    
	public function batchCompetition()
    {
        $resultArray = array('result' => 0);
        try {
			require_once 'Dal/Hotel/Competition.php';
            $dalCompetition = Dal_Hotel_Competition::getDefaultInstance();

            $enemyArr = $dalCompetition->getRanTen();
        	if (count($enemyArr) < 10) {
            	$dalCompetition->defaultHandler();
        		$resultArray = array('result' => 1);           //no enough user end 
            	return $resultArray;
            }
            
            
            $userNow = $dalCompetition->getRanOne();

            
            while ($userNow) {
	            
	            $winFailRe = 0;
	            for ($i = 0;$i < count($enemyArr); $i++ ) {
	            	$winFailTmp = 0;
	            	if ( $userNow['numA'] == $enemyArr[$i]['numA']
	            	  || $userNow['numB'] == $enemyArr[$i]['numB']
	            	  || $userNow['numC'] == $enemyArr[$i]['numC']) {
	            		if (rand(0,100) > 50) {
	            			$winFailRe += 1;
	            			continue;
	            		}
	            		else {
	            			break;
	            		}
	            		
	            	}
	            	if ($userNow['numA'] > $enemyArr[$i]['numA']) {
	            		$winFailTmp += 1;
	            	}
	            	if ($userNow['numB'] > $enemyArr[$i]['numB']) {
	            		$winFailTmp += 1;
	            	}
	            	if ($userNow['numC'] > $enemyArr[$i]['numC']) {
	            		$winFailTmp += 1;
	            	}
	            	if ($winFailTmp == 2) {
	            		$winFailRe += 1;
            			continue;
	            	}
	            	else {
	            		break;
	            	}
	            }
	            $this->_wdb->beginTransaction();
                
	            $set = array('last_result' => $winFailRe);
	            $dalCompetition->updateCompetition($userNow['uid'],$set);
                
                $this->_wdb->commit();
                
                $userNow = $dalCompetition->getRanOne();
                $enemyArr = $dalCompetition->getRanTen();
            }
            $resultArray = array('result' => 1);
            
            
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return $resultArray;
        }
        return $resultArray;
    }
    
    
}
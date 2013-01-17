<?php

require_once 'Bll/Abstract.php';

/**
 * parking batch logic's Operation
 *
 * @package    Bll
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2008/2/27    Huch
 */
class Bll_Parking_Batch extends Bll_Abstract
{
	public function patrolCar() 
	{		
        $runTime = date('Y-m-d H:i:s');
        //get parking car before one hour
        require_once 'Dal/Parking/Batch.php';
        $dalBatch = new Dal_Parking_Batch();
        $strtime = strtotime($runTime);
        $parkings = $dalBatch->getParkingCar($strtime);
        $reportedParkings = Array();
        $count = count($parkings);
        
        //set the deleted array
        for($i = 0; $i < $count; $i++) {
            $parkingUserInfo = $dalBatch->getParkingUser($parkings[$i]['parking_uid'],(int)$parkings[$i]['type']);
            
            //if not parking in free park location
            if ($parkingUserInfo['free_park'] != $parkings[$i]['location']) {
            	 //get parking user 
                 $parkedUserInfo = $dalBatch->getParkingUser($parkings[$i]['uid']);
                 
                 //if parking time > 3 days
                 if( ($strtime - 72 * 3600) > $parkings[$i]['parked_time']) {
                     //is parked 72 hour
                     $parkings[$i]['deletetype'] = '1';
                     $reportedParkings[] = $parkings[$i];
                 }
                 //if parking time < 3 days
                 else {
                 	$endTime = $parkedUserInfo['last_bribery_time'] + 259200;
                    //check bribery time
                    if($strtime < $endTime) {
                        continue;
                    }
                 	
                    //if not reported , compute the probability
                    if (!$parkings[$i]['is_reported']) {
                    	
                        //get probability by the car count 
                        $patrolCarProbability = $parkings[$i]['car_count'];
                        
                        //if probability > 9 ,set max as 9 
                        if($patrolCarProbability > 9) {
                            $patrolCarProbability = 9;
                        }
                        
                        //if not neighbor 
                        if($parkings[$i]['type'] ==1) {
                           //get probability by friend login
                           $patrolLoginProbability = floor(($strtime - $parkingUserInfo['last_login_time']) / 3600 );
                           
                           //if probability > 8 ,set max as 8 
                            if($patrolLoginProbability > 8) {
                                $patrolLoginProbability = 8;
                            } 
                            else if($patrolLoginProbability <= 0) {
                                $patrolLoginProbability = 1;
                            }
                        } 
                        //if neighbor , set probability as default 8
                        else {
                            $patrolLoginProbability = 8;
                        }
                        
                        $start = (int)mktime(22, 0, 0, date("m", $runTime), date("d" , $runTime), date("Y" , $runTime)); 
                        $end = (int)mktime(8, 0, 0, date("m", $runTime), date("d",$runTime) + 1, date("Y" , $runTime)); 
                        
                        //now time is in 22:00-8:00,set probability as half
                       if ($strtime < $end && $strtime >= $start) {
                           $patrolProbability = (int)ceil(($patrolLoginProbability + $patrolCarProbability) / 2);
                       } 
                       else {
                           $patrolProbability = $patrolLoginProbability + $patrolCarProbability ;
                       }
                
                       //get random number
                       $randNumber = rand(1,100);
                       
                       //if rand number is in patrol probability,set the line to deleted array
                       if ($randNumber <= $patrolProbability) {
                           //is police partol
                           $parkings[$i]['deletetype'] = '2';
                           $reportedParkings[] = $parkings[$i];
                       }
                    }
                }
            }
            //if parked in free parked location
            else {
                if( $strtime - 12 * 3600 > $parkings[$i]['parked_time']) {
                     //is free parked 12 hour
                    $parkings[$i]['deletetype'] = '3';
                    $reportedParkings[] = $parkings[$i];
                }
            }
        }
                
        $this->deleteParkedCar($reportedParkings);
        
        debug_log($runTime . " --> current patrol cost " . (time() - $strtime) . " seconds");
	}
	
	public function policePatrolReportedCar()
	{
        $runTime = date('Y-m-d H:i:s');
        
        //get parking car before one hour
        require_once 'Dal/Parking/Batch.php';
        $dalBatch = new Dal_Parking_Batch();
        $strtime = strtotime($runTime);
        $parkings = $dalBatch->getReportedParkingCar($strtime);
        $reportedParkings = Array();
        $count = count($parkings);
        
        //delete pid not in parking
        $dalBatch->deleteNotInParking();        
        
        //set the deleted array
        for($i = 0; $i < $count; $i++) {        	
            $parkingUserInfo = $dalBatch->getParkingUser($parkings[$i]['parking_uid'],$parkings[$i]['type']);
            
            //if not parking in free park location
            if ($parkingUserInfo['free_park'] != $parkings[$i]['location']) {
            	
                 //get parking user 
                 $parkedUserInfo = $dalBatch->getParkingUser($parkings[$i]['uid']);
                 
                //if this line is reported ,set the line to deleted array
                if ($parkings[$i]['is_reported']) {
                    $endTime = $parkedUserInfo['last_bribery_time'] + 259200;                    
                    
                    //check bribery time
                    if($strtime > $endTime) {
                        $reportedParkings[] = $parkings[$i];
                    }
                }
            }
        }
                
        $this->deleteParkedCar($reportedParkings);
        
        debug_log($runTime . " --> current police patrol report cost " . (time() - $strtime) . " seconds");
	}
	
	private function deleteParkedCar($reportedParkings)
	{
		require_once 'Dal/Parking/Batch.php';
        $dalBatch = new Dal_Parking_Batch();
        
        //delete parking car and insert into feed
        for($i = 0 , $countParking = count($reportedParkings); $i < $countParking; $i++) {

            $parkInfo = $dalBatch -> getParkInfoByPid($reportedParkings[$i]['pid']);
            if (!empty($parkInfo)) {
                $parkingUserInfo = $dalBatch->getParkingUser($reportedParkings[$i]['parking_uid'],(int)$reportedParkings[$i]['type']);
                $this->_wdb->beginTransaction();
                try {
                    $carName = $dalBatch->getCarName($reportedParkings[$i]['car_id']);
                    $dalBatch->deleteParkingCar($reportedParkings[$i]['pid']);
                    
                    $info = array(
                        'uid' => $reportedParkings[$i]['uid'],
                        'car_id' => $reportedParkings[$i]['car_id'],
                        'car_color' => $reportedParkings[$i]['car_color'],
                        'create_time' => time()
                    );
                    
                    $dalBatch->insertNoPark($info);
                    
                    $create_time = date('Y-m-d H:i:s');
                    
                    $roadsFeed = array(
                        'uid' => $reportedParkings[$i]['uid'],
                        'icon' => Zend_Registry::get('static') . "/apps/parking/img/icon/money.gif",
                        'create_time' => $create_time
                    );
                    
                    //get user info
                    $userInfo = $dalBatch->getParkingUser($reportedParkings[$i]['parking_uid'],$reportedParkings[$i]['type']);
                    
                    $minifeedForSelf = array(
                        'uid' => $reportedParkings[$i]['uid'],
                        'icon' => Zend_Registry::get('static') . "/apps/parking/img/icon/loss.gif",
                        'create_time' => $create_time
                    );
                    
                    //get parking money
                    require_once 'Dal/Parking/Car.php';
                    $dalCar = Dal_Parking_Car::getDefaultInstance();
                    $carInfo = $dalCar->getParkingCarInfo($parkInfo['car_id']);
                    
                    $time = floor((time()-$reportedParkings[$i]['parked_time'])/900);
                    $time = $time > 32 ? 32 : $time;
                    $addMoney = $time * $parkingUserInfo['fee'] * $carInfo['times'];
                    
                    //if the car is reported
                    if ($reportedParkings[$i]['is_reported']) {
                    	$parkind_uid = $reportedParkings[$i]['parking_uid'];
                    	require_once 'Bll/User.php';
				        $parkind_user = Bll_User::getPerson($parkind_uid);				
				        
				        if ($parkind_user == null) {
				        	$name = "____";
				        }
				        else {
				        	$name = $parkind_user->getDisplayName();
				        }
                    	
                        $addMoney = $addMoney/2;
                        $roadsFeed['target'] = $reportedParkings[$i]['uid'];
                        $minifeedForSelf['target'] = $reportedParkings[$i]['uid'];
                        $roadsFeed['title'] = '{"name":"'. $name .'","car_name":"'. $carName . '","money":"' . $addMoney . '"}';
                        $minifeedForSelf['title'] =  '{"name":"'. $name .'","car_name":"'. $carName . '","money":"' . $addMoney . '"}';
                        //if anonymous
                        if ($reportedParkings[$i]['anonymous']) {
                            $roadsFeed['template_id'] = 9;
                            $roadsFeed['actor'] = $reportedParkings[$i]['reported_uid'];
                            $minifeedForSelf['template_id'] = 8;
                            $minifeedForSelf['actor'] = $reportedParkings[$i]['reported_uid'];
                        } 
                        else {
                            $roadsFeed['template_id'] = 6;
                            $minifeedForSelf['template_id'] = 5;
                            $minifeedForSelf['actor'] = $reportedParkings[$i]['reported_uid'];
                            $roadsFeed['actor'] = $reportedParkings[$i]['reported_uid'];
                        }

                        if($addMoney > 0) {
                            $dalBatch ->updateParkingUserAsset($reportedParkings[$i]['reported_uid'],$addMoney);
                        }
                        
                        $minifeedForReported = array(
                            'uid' => $reportedParkings[$i]['reported_uid'],
                            'template_id' =>4,
                            'actor' => $reportedParkings[$i]['reported_uid'],
                            'target' => $reportedParkings[$i]['uid'],
                            'title' => '{"car_name":"'. $carName . '","money":"' . $addMoney . '"}',
                            'icon' => Zend_Registry::get('static') . "/apps/parking/img/icon/money.gif",
                            'create_time' => $create_time
                        );
                        
                        $dalBatch->insertMinifeed($minifeedForReported);
                        
                        $dalBatch->insertNewsfeed($roadsFeed);
                        $roadsFeed['uid'] =  $reportedParkings[$i]['parking_uid'];
                        $dalBatch->insertNewsfeed($roadsFeed);
                    } 
                    //if not reported
                    else {
                        if ($reportedParkings[$i]['deletetype'] == '1') {
                            $minifeedForSelf['template_id'] = 22;
                            $minifeedForSelf['title'] = '{"car_name":"'. $carName . '"}';
                            $minifeedForSelf['actor'] = $minifeedForSelf['uid'];
                            $minifeedForSelf['target'] = $reportedParkings[$i]['parking_uid'];
                            $minifeedForSelf['icon'] =  Zend_Registry::get('static') . "/apps/parking/img/icon/free.gif";
                            
                            /*now don't pay asset
                            //update  asset
                            if($addMoney > 0) {
                                $dalBatch ->updateParkingUserAsset($reportedParkings[$i]['parking_uid'],$addMoney);
                            }*/
                        } 
                        else if($reportedParkings[$i]['deletetype'] == '3') {
                            $minifeedForSelf['template_id'] = 23;
                            $minifeedForSelf['title'] = '{"car_name":"'. $carName . '"}';
                            $minifeedForSelf['actor'] = $minifeedForSelf['uid'];
                            $minifeedForSelf['target'] = $reportedParkings[$i]['parking_uid'];
                            $minifeedForSelf['icon'] =  Zend_Registry::get('static') . "/apps/parking/img/icon/free.gif";
                        } 
                        else {
                            $roadsFeed['template_id'] = 17;
                            $roadsFeed['actor'] = $reportedParkings[$i]['uid'];
                            $roadsFeed['title'] =  '{"car_name":"'. $carName . '","money":"' . $addMoney . '"}';
                            $roadsFeed['target'] =  $reportedParkings[$i]['parking_uid'];
                            $roadsFeed['icon'] =  Zend_Registry::get('static') . "/apps/parking/img/icon/police.gif";
                            
                            $minifeedForSelf['template_id'] = 16;
                            $minifeedForSelf['title'] = '{"car_name":"'. $carName . '","money":"' . $addMoney . '"}';
                            $minifeedForSelf['target'] =  $reportedParkings[$i]['parking_uid'];
                            $minifeedForSelf['actor'] =  $reportedParkings[$i]['uid'];                            
                            $minifeedForSelf['icon'] =  Zend_Registry::get('static') . "/apps/parking/img/icon/police.gif";
                            
                            $dalBatch->insertNewsfeed($roadsFeed);
                            $roadsFeed['uid'] =  $reportedParkings[$i]['parking_uid'];
                            $dalBatch->insertNewsfeed($roadsFeed);
                        }
                    }
                    
                    $dalBatch->insertMinifeed($minifeedForSelf);
                    $this->_wdb->commit();
                } 
                catch (Exception $e) {
                	err_log($e->getMessage());
                    $this->_wdb->rollBack();
                }
            }
        }
	}
}
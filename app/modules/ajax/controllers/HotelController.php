<?php
/** @see Zend_Json */
require_once 'Zend/Json.php';

/** @see MyLib_Zend_Controller_Action_Ajax */
require_once 'MyLib/Zend/Controller/Action/Ajax.php';

/**
 * Hotel Ajax Controllers
 *
 * @copyright  Copyright (c) 2009 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/09/16   Zhaoxh
 */
class Ajax_HotelController extends MyLib_Zend_Controller_Action_Ajax
{    
    /**
     * update building action
     *
     */
    public function upbuildAction()
	{
	    $uid = $this->_user->getId();
        
        $colName = $this->_request->getPost('buildName');
        $uidFlash = $this->_request->getPost('uidflash');
	    $re['result'] = 0;
	    info_log("11aa","aaa");
        if ($uid == $uidFlash && $colName) {
	        require_once 'Bll/Hotel/Huser.php';
            $bllHuser = new Bll_Hotel_Huser();
            $re = $bllHuser->upBuild($uid,$colName);
	    }
	    
	    require_once 'Bll/Hotel/ArrayToXml.php';
        $bllArrayToXml = new Bll_Hotel_ArrayToXml();
	    $xml = $bllArrayToXml->toXml($re);
        
	    echo $xml;
	}
	
	/**
	 * up building over building lv+1
	 *
	 */
	public function upbuildoverAction()
	{
	    $uid = $this->_user->getId();
        $uidFlash = $this->_request->getPost('uidflash');
	    
        $re = array('result' => 0);
	    
        if ($uid == $uidFlash) {
	        require_once 'Bll/Hotel/Huser.php';
            $bllHuser = new Bll_Hotel_Huser();
            $re = $bllHuser->upBuildOver($uid);
	    }
	    
	    require_once 'Bll/Hotel/ArrayToXml.php';
        $bllArrayToXml = new Bll_Hotel_ArrayToXml();
	    $xml = $bllArrayToXml->toXml($re);
        
	    echo $xml;
	}
	
	/**
	 * update technology action
	 *
	 */
	public function uptechAction()
	{
	    $uid = $this->_user->getId();
        
        $colName = $this->_request->getPost('techName');
        $uidFlash = $this->_request->getPost('uidflash');
        info_log("11aa","bbb");
	    $re['result'] = 0;
	    
        if ($uid == $uidFlash && $colName) {
	        require_once 'Bll/Hotel/Tech.php';
            $bllTech = new Bll_Hotel_Tech();
            $re = $bllTech->upTech($uid,$colName);
	    }
	    
	    require_once 'Bll/Hotel/ArrayToXml.php';
        $bllArrayToXml = new Bll_Hotel_ArrayToXml();
	    $xml = $bllArrayToXml->toXml($re);
        
	    echo $xml;
	}
	
	public function uptechoverAction()
	{
	    $uid = $this->_user->getId();
        
        $uidFlash = $this->_request->getPost('uidflash');
	    
        $re['result'] = 0;
	    
        if ($uid == $uidFlash) {
	        require_once 'Bll/Hotel/Tech.php';
            $bllTech = new Bll_Hotel_Tech();
            $re = $bllTech->upTechOver($uid);
	    }
	    
	    require_once 'Bll/Hotel/ArrayToXml.php';
        $bllArrayToXml = new Bll_Hotel_ArrayToXml();
	    $xml = $bllArrayToXml->toXml($re);
        
	    echo $xml;
	}
	
	/**
	 * @param set 1 :  'direction' == 'self'  
	 *  @return   self full data  array
	 *  (if 'direction'=='self',ignore all other param sets)  
	 * 
	 * @param set 2 :  'fid' == string(number) 
	 *  @return  friend full data   array
	 *  (if fid exist,ignore 'direction' AND 'position')
	 * 
	 * @param set 3 :  'direction' == 'left' or 'right','position' == string(number) 
	 *  @return  friend full data array
	 * 
	 */
	public function profileAction()
	{
	    $uid = $this->_user->getId();
	    $direction = $this->_request->getPost('direction');
	    $position = $this->_request->getPost('position');
	    
	    //$uidFlash = $this->_request->getPost('uidflash');
        //$fidflash = $this->_request->getPost('fidflash');
	    $fid = $this->_request->getPost('fid','0');
	    //info_log("11aa","ccc");
        //index--visit self
        if ($direction == 'self' && ($fid == "0" || $fid == $uid)) {
            require_once 'Bll/Hotel/Huser.php';
            $bllHuser = new Bll_Hotel_Huser();
            $bllHuser->handleUpdate($uid);         //update all over_time process
            
            require_once 'Dal/Hotel/Huser.php';
            $dalHuser = Dal_Hotel_Huser::getDefaultInstance();
            $re = $dalHuser->getFullData($uid,1);
            $re['result'] = 1;
            $re['position'] = 0;
            $re['fid'] = $uid;
            $re['userid'] = $uid;
            
            Bll_User::appendPerson($re, 'userid');
            
        }
        //visit friend
	    else {
            require_once 'Bll/Hotel/Huser.php';
            $bllHuser = new Bll_Hotel_Huser();
        
            require_once 'Bll/Friend.php';
            $fids = Bll_Friend::getFriendIds($uid);
            
	        $re = $bllHuser->profileFriend($uid,$direction,$position,$fids,$fid);
	        $re['userid'] = $uid;
	        
	        Bll_User::appendPerson($re, 'fid');
	    }
	    require_once 'Bll/Hotel/Config.php';
        $bllConfig = new Bll_Hotel_Config();
        $datehch = $bllConfig->getGameCurrentDate();
	    $re['date'] = $datehch;
	    $re['monthCnt'] = intval((time() - $re['join_time'])/(24 * 3600 * 30));
	    
	    require_once 'Dal/Hotel/Friend.php';
        $dalFriend = Dal_Hotel_Friend::getDefaultInstance();
	    //get idle learnCnt
        $re['learnCnt'] = $dalFriend->learnCnt($uid);
        $re['exchangeCnt'] = $dalFriend->exchangeCnt($uid);
	    
	    
        require_once 'Bll/Hotel/ArrayToXml.php';
        $bllArrayToXml = new Bll_Hotel_ArrayToXml();
	    $xml = $bllArrayToXml->toXml($re);
       
	    echo $xml;
	}
	
	/**
	 * get user tech update info
	 *
	 */
	public function gettechinfoAction()
	{
	    $uid = $this->_user->getId();
	    
        require_once 'Dal/Hotel/Tech.php';
        $dalTech = Dal_Hotel_Tech::getDefaultInstance();
        
        $re = $dalTech->getTechInfo($uid);
        
        require_once 'Bll/Hotel/ArrayToXml.php';
        $bllArrayToXml = new Bll_Hotel_ArrayToXml();
	    $xml = $bllArrayToXml->toXml($re);
        
	    echo $xml;
	}
	
	/**
	 * see bag item
	 *
	 */
	public function bagAction()
	{
	    $uid = $this->_user->getId();
	    $page = $this->_request->getPost('page');
	    
	    $re = array('result' => 0);
	    
	    if ($page) {
    	    require_once 'Dal/Hotel/Item.php';
            $dalItem = Dal_Hotel_Item::getDefaultInstance();
            
            $re = $dalItem->getItemList($uid,$page);
            $re['result'] = 1;
	    }
	    
        require_once 'Bll/Hotel/ArrayToXml.php';
        $bllArrayToXml = new Bll_Hotel_ArrayToXml();
	    $xml = $bllArrayToXml->toXml($re);
        
	    echo $xml;
	}
	
	/**
	 * buy a item
	 *
	 */
	public function buyitemAction()
	{
	    $uid = $this->_user->getId();
	    $sid = $this->_request->getPost('sid');
	    $buyNum = $this->_request->getPost('num');
	    $re = array('result' => 0);
	    
	    if ($sid) {
    	    require_once 'Bll/Hotel/Item.php';
            $bllItem = new Bll_Hotel_Item();
            
            $re = $bllItem->buyItem($uid,$sid,$buyNum);
	    }
	    
        require_once 'Bll/Hotel/ArrayToXml.php';
        $bllArrayToXml = new Bll_Hotel_ArrayToXml();
	    $xml = $bllArrayToXml->toXml($re);
        
	    echo $xml;
	}
	
	/**
	 * use item and get item effect
	 * some effect must be handle by FLEX SIDE
	 * @return  $sid
	 */
	public function useitemAction()
	{
	    $uid = $this->_user->getId();
	    $sid = $this->_request->getPost('sid');
	    $fid = $this->_request->getPost('fid','0');
	    $re = array('result' => 0);
	    
	    if ($sid) {
    	    require_once 'Bll/Hotel/Item.php';
            $bllItem = new Bll_Hotel_Item();
            
            $re = $bllItem->useItem($uid,$sid,$fid);
          
	    }
	    
        require_once 'Bll/Hotel/ArrayToXml.php';
        $bllArrayToXml = new Bll_Hotel_ArrayToXml();
	    $xml = $bllArrayToXml->toXml($re);
        
	    echo $xml;
	}
	
	/**
	 * show shop items
	 *
	 */
	public function shopAction() 
	{
	    $type = $this->_request->getPost('type','1');
	    $page = $this->_request->getPost('page','1');
	    
	    require_once 'Dal/Hotel/Item.php';
        $dalItem = Dal_Hotel_Item::getDefaultInstance();
        
        $re = $dalItem->shopShow($type,$page,12);
        $re['result'] = $dalItem->cntStore();
	    
        require_once 'Bll/Hotel/ArrayToXml.php';
        $bllArrayToXml = new Bll_Hotel_ArrayToXml();
	    $xml = $bllArrayToXml->toXml($re);
        
	    echo $xml;
	}
	
	/**  
	 * get ranking info
	 * 
	 * @param 'type' = 'money' || 'experience' || [other colname in hotel_user] 
	 */
	public function rankAction()
	{
	    $uid = $this->_user->getId();
		$colname = $this->_request->getPost('type','experience');
	    $page = $this->_request->getPost('page','1');
	    
	    require_once 'Dal/Hotel/Huser.php';
        $dalHuser = Dal_Hotel_Huser::getDefaultInstance();
        require_once 'Bll/Friend.php';
        $fids = Bll_Friend::getFriends($uid);
        $fids[count($fids)] = $uid;
        
        
        $re = $dalHuser->rank($colname,$page,$fids,1);
        
        require_once 'Bll/User.php';
        Bll_User::appendPeople($re);
        
		$aaa = count($re);
        for ($i = 0; $i <= 20; $i ++) {
        	$re[$aaa+$i] = $re[$i];
        }
        
        
        $re['result'] = 1;
	   
	    
	    require_once 'Bll/Hotel/ArrayToXml.php';
        $bllArrayToXml = new Bll_Hotel_ArrayToXml();
	    $xml = $bllArrayToXml->toXml($re);
        
	    echo $xml;
	}
	
	/**
	 * send a learner to friend
	 *
	 * @return array contains  uid,displayName,index,result
	 */
	public function sendlearnerAction()
	{
        $uid = $this->_user->getId();
	    $fid = $this->_request->getPost('fid');  
	    $re = array('result' => 0);
	    
	    if ($fid) {
    	    require_once 'Bll/Hotel/Friend.php';
            $bllFriend = new Bll_Hotel_Friend();
            
            $re = $bllFriend->sendLearner($uid,$fid);
	    }  
	    
	    require_once 'Bll/Hotel/ArrayToXml.php';
        $bllArrayToXml = new Bll_Hotel_ArrayToXml();
	    $xml = $bllArrayToXml->toXml($re);
        
	    echo $xml;
	}
	
	/**
	 * call back learner from friend 
	 * 
	 */
	public function cbklearnerAction()
	{
	    $uid = $this->_user->getId();
	    $index = $this->_request->getPost('index');
	    $re = array('result' => 0);
	    
	    if ($index) {
    	    require_once 'Bll/Hotel/Friend.php';
            $bllFriend = new Bll_Hotel_Friend();
            
            $re = $bllFriend->cbkLearner($uid,$index);
	    }  
	    
	    require_once 'Bll/Hotel/ArrayToXml.php';
        $bllArrayToXml = new Bll_Hotel_ArrayToXml();
	    $xml = $bllArrayToXml->toXml($re);
        
	    echo $xml;
	}
	
	/**
	 * banish friend learner
	 *
	 */
	public function banishlearnerAction()
	{
	    $uid = $this->_user->getId();
	    $learnUid = $this->_request->getPost('learnuid');     //fid means sender of learner,=uid in db
	    $index = $this->_request->getPost('index');
	    $place = $this->_request->getPost('place');         // 1-3
	    $re = array('result' => 0);
	    
	    if ($index && $learnUid) {
    	    require_once 'Bll/Hotel/Friend.php';
            $bllFriend = new Bll_Hotel_Friend();
            
            $re = $bllFriend->banishLearner($uid,$learnUid,$index,$place);
	    }  
	    
	    require_once 'Bll/Hotel/ArrayToXml.php';
        $bllArrayToXml = new Bll_Hotel_ArrayToXml();
	    $xml = $bllArrayToXml->toXml($re);
        
	    echo $xml;
	}
	
	public function learninfoAction()
	{
		$uid = $this->_user->getId();
		
		require_once 'Dal/Hotel/Friend.php';
        $dalFriend = Dal_Hotel_Friend::getDefaultInstance();
        $re = $dalFriend->getLearnInfo($uid);
        
        for($i = 0; $i < count($re); $i++) {
        	if ($re[$i]['fid'] != 0) {
        		$re[$i]['earn'] = $dalFriend->earnLearner($re[$i]['uid'],$re[$i]['index']);
        	}
        	else {
        		$re[$i]['earn'] = 0;
        	}
        }
        
        $re['result'] = 1;
		
		require_once 'Bll/Hotel/ArrayToXml.php';
        $bllArrayToXml = new Bll_Hotel_ArrayToXml();
	    $xml = $bllArrayToXml->toXml($re);
        
	    echo $xml;
	}
	
	
	/**
	 * clean fid's hotel
	 * 
	 * 
	 *
	 */
	public  function cleanAction()
	{
        $uid = $this->_user->getId();
	    $fid = $this->_request->getPost('fid');  
	    $re = array('result' => 0);
	    
	    if ($fid) {
    	    require_once 'Bll/Hotel/Friend.php';
            $bllFriend = new Bll_Hotel_Friend();
            
            $re = $bllFriend->clean($uid,$fid);
	    }  
	    
	    require_once 'Bll/Hotel/ArrayToXml.php';
        $bllArrayToXml = new Bll_Hotel_ArrayToXml();
	    $xml = $bllArrayToXml->toXml($re);
        
	    echo $xml;
	}
	
	/**
	 * update Huser action
	 *
	 */
	public function updatehuserAction()
	{
	    $uid = $this->_user->getId();
	    $value = $this->_request->getPost('value');
	    $colname = 'location';//$this->_request->getPost('colname');      //for security,only for location
	    
        require_once 'Dal/Hotel/Huser.php';
        $dalHuser = Dal_Hotel_Huser::getDefaultInstance();
        $location = $dalHuser->getOneData($uid,'location');
	    
	    if ($location != 0) {
		    require_once 'Bll/Hotel/Item.php';
	        $bllItem = new Bll_Hotel_Item();
	            
	        $itemUser = $bllItem->useItem($uid,1);
	        if ($itemUser['result'] <= 0){
	        	$re['result'] = -1; 
	        }
	        else {
	        	require_once 'Bll/Hotel/Huser.php';
		        $bllHuser = new Bll_Hotel_Huser();
		        
		        $re = $bllHuser->updateHuser($uid,$colname,$value);
		        $re['value'] = $value;
		        $re['colname'] = $colname;
	        }
	    }
	    else {
	    	require_once 'Bll/Hotel/Huser.php';
	        $bllHuser = new Bll_Hotel_Huser();
	        
	        $re = $bllHuser->updateHuser($uid,$colname,$value);
	        $re['value'] = $value;
	        $re['colname'] = $colname;
	    }
	    
        require_once 'Bll/Hotel/ArrayToXml.php';
        $bllArrayToXml = new Bll_Hotel_ArrayToXml();
	    $xml = $bllArrayToXml->toXml($re);
        
	    echo $xml;
	    
	}
	
	/**
	 * customer change action
	 *
	 */
	public function exchangeAction()
	{
		$uid = $this->_user->getId();
	    $fid = $this->_request->getPost('fid');
	    
	    
	    require_once 'Bll/Hotel/Friend.php';
        $bllFriend = new Bll_Hotel_Friend();
            
        $re = $bllFriend->exchange($uid,$fid);
        
        require_once 'Bll/Hotel/ArrayToXml.php';
        $bllArrayToXml = new Bll_Hotel_ArrayToXml();
	    $xml = $bllArrayToXml->toXml($re);
        
	    echo $xml;
	}
	
	/**
	 * cus click action(add occupancy 2%, one day max 5 times)
	 *
	 */
	public function cusclickAction()
	{
		$uid = $this->_user->getId();
		$opresult = $this->_request->getPost('qstResult');
	    require_once 'Bll/Hotel/Cus.php';
        $bllCus = new Bll_Hotel_Cus();
            
        $re = $bllCus->cusClick($uid,$opresult);
        
        require_once 'Bll/Hotel/ArrayToXml.php';
        $bllArrayToXml = new Bll_Hotel_ArrayToXml();
	    $xml = $bllArrayToXml->toXml($re);
        
	    echo $xml;
	}
	
	/**
	 * every day Competition join action
	 *
	 */
	public function joincompetitionAction()
	{
		$uid = $this->_user->getId();
		$numA = intval($this->_request->getPost('numA'));
		$numB = intval($this->_request->getPost('numB'));
		$numC = intval($this->_request->getPost('numC'));
		$batch = intval($this->_request->getPost('batchAction',0));
	    require_once 'Bll/Hotel/Competition.php';
        $bllCompetition = new Bll_Hotel_Competition();
        
        if ($batch == 0){
            $re = $bllCompetition->joinCompetition($uid,$numA,$numB,$numC);
        }
        else {
            $re = $bllCompetition->batchCompetition(); 
        }
        require_once 'Bll/Hotel/ArrayToXml.php';
        $bllArrayToXml = new Bll_Hotel_ArrayToXml();
	    $xml = $bllArrayToXml->toXml($re);
        
	    echo $xml;
	}
	
	//gain
	public function gaincompetitionAction()
	{
		$uid = $this->_user->getId();
		//$numA = intval($this->_request->getPost('numA'));
		//$numB = intval($this->_request->getPost('numB'));
		//$numC = intval($this->_request->getPost('numC'));
		//$batch = intval($this->_request->getPost('batchAction',0));
	    require_once 'Bll/Hotel/Competition.php';
        $bllCompetition = new Bll_Hotel_Competition();
        
       
        $re = $bllCompetition->gainCompetition($uid); 
       
        require_once 'Bll/Hotel/ArrayToXml.php';
        $bllArrayToXml = new Bll_Hotel_ArrayToXml();
	    $xml = $bllArrayToXml->toXml($re);
        
	    echo $xml;
	}
	
	
	public function indexcompetitionAction()
	{
		$uid = $this->_user->getId();
		//$numA = intval($this->_request->getPost('numA'));
		//$numB = intval($this->_request->getPost('numB'));
		//$numC = intval($this->_request->getPost('numC'));
		//$batch = intval($this->_request->getPost('batchAction',0));
	    require_once 'Bll/Hotel/Competition.php';
        $dalCompetition = Dal_Hotel_Competition::getDefaultInstance();
        
        $isIn = $dalCompetition->isInCompetition($uid);
        if (!$isIn) {
        	$set = array('uid' => $uid,
                         'joined' => 0,
                         'join_time' => time());
            $dalCompetition->insertCompetition($set);
        }
        
        $re['result'] = $dalCompetition->getResult($uid); 
        $re['joined'] = $dalCompetition->isJoined($uid);
        
        require_once 'Bll/Hotel/ArrayToXml.php';
        $bllArrayToXml = new Bll_Hotel_ArrayToXml();
	    $xml = $bllArrayToXml->toXml($re);
        
	    echo $xml;
	}
}
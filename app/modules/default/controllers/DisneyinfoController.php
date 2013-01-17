<?php

class DisneyinfoController extends Zend_Controller_Action
{
	private $_uid;
	
	function preDispatch()
    {        
    	if ($this->_request->getActionName() == 'index' || $this->_request->getActionName() == 'login') {
    	}
    	else {
    		$auth = Zend_Auth::getInstance();
    		$adminStorage = new Zend_Auth_Storage_Session('Zend_Auth_Disney');
        	$auth->setStorage($adminStorage);
    		if (!$auth->hasIdentity()) {
    			$this->_redirect('/default/disneyinfo/index');
    		}
    	}
    	
    	$this->view->baseUrl = Zend_Registry::get('host');
    }
    
	public function indexAction()
	{
		$this->render();
	}
	
	public function loginAction()
	{
		$id = $this->_request->getPost('txtId');
		$pass = $this->_request->getPost('txtPassword');
		
		if ($id == 'admin' && $pass == "123") {
			try {
			$auth = Zend_Auth::getInstance();
			$adminStorage = new Zend_Auth_Storage_Session('Zend_Auth_Disney');
            $auth->setStorage($adminStorage);
            $auth->getStorage()->write('1');
            $lifeTime = 24 * 3600; 
			setcookie(session_name(), session_id(), time() + $lifeTime, "/");
			}
			catch (Exception $e) {
				echo $e->getMessage();exit;
			}
            $this->_redirect('/default/disneyinfo/info');
		}
		else {
			$this->_redirect('/default/disneyinfo/index');
		}
	}	
	
	public function searchmoreAction()
	{
		
		$this->render('info');
	}	
	
	public function infoAction()
	{		
		$type = $this->_request->getParam('ddlSearch', 0);
		$startDate = $this->_request->getParam('txtDateStart', date('Y-m-d'));
		$endDate = $this->_request->getParam('txtDateEnd', date('Y-m-d'));
		$this->_uid = $this->_request->getParam('userID');
		
		$startTime = strtotime($startDate);
		$endTime = strtotime($endDate)+86400;
		
		if (!empty($this->_uid)) {
			require_once 'Mdal/Disney/Log.php';
			$mdalLog = Mdal_Disney_Log::getDefaultInstance();
			$userInfo = $mdalLog->getUserBaseInfo($this->_uid);
				
			if (empty($userInfo)) {
				$this->view->errorId = 1;
			}
			else {
				if ($type == 0) {				
					require_once 'Bll/User.php';
					Bll_User::appendPerson($userInfo);
					
					//get friends
					require_once 'Bll/Friend.php';
		        	$fids = Bll_Friend::getFriends($this->_uid);
		        	
		        	if (!empty($fids)) {
			        	$userInfo['friends'] = $mdalLog->getFriends($fids);
			        	$userInfo['friend_count'] = count($userInfo['friends']);
		        	}
		        	
		        	//get award
		        	$userInfo['award'] = $mdalLog->getAward($this->_uid);	        	
		        	//get cup
		        	$userInfo['cup'] = $mdalLog->getCup($this->_uid);	        	
		        	
					$this->view->userInfo = $userInfo;
				}
				else if ($type == 1) {
					$logGetCurrent = $mdalLog->getGetCurrent($this->_uid, $startTime, $endTime);
					
					if (empty($logGetCurrent)) {
						$this->view->errorId = 2;
						$this->view->error = 'すみません、ご当地GET履歴がありません。';
					}
					$this->view->logGetCurrent = $logGetCurrent;				
				}
				else if ($type == 2) {
					$logCheck = $mdalLog->getCheck($this->_uid, $startTime, $endTime);
					
					if (empty($logCheck)) {
						$this->view->errorId = 2;
						$this->view->error = 'すみません、チェック履歴がありません。';
					}
					$this->view->logCheck = $logCheck;	
				}
				else if ($type == 3) {
					$logInvite = $mdalLog->getInvite($this->_uid, $startTime, $endTime);
					
					if (empty($logInvite)) {
						$this->view->errorId = 2;
						$this->view->error = 'すみません、招待履歴がありません。';
					}
					$this->view->logInvite = $logInvite;	
				}
				else if ($type == 4) {
					$logTrade = $mdalLog->getTrade($this->_uid, $startTime, $endTime);
					
					if (empty($logTrade)) {
						$this->view->errorId = 2;
						$this->view->error = 'すみません、トレード履歴がありません。';
					}
					$this->view->logTrade = $logTrade;
				}
				else if ($type == 5) {
					$payment = array();
					$payment['ticket'] = $mdalLog->getPayTicket($this->_uid, $startTime, $endTime);
			        $payment['useticket'] = $mdalLog->getUseTicket($this->_uid, $startTime, $endTime);
			        
					if (empty($payment['ticket']) && empty($payment['useticket'])) {
						$this->view->errorId = 2;
						$this->view->error = 'すみません、チケット購入|消費履歴がありません。';
					}
					
					$this->view->payment = $payment;	  	
				}
				else if ($type == 6) {
					$payment = array();
					$payment['download'] = $mdalLog->getPayDownload($this->_uid, $startTime, $endTime);
					
					if (empty($payment['download'])) {
						$this->view->errorId = 2;
						$this->view->error = 'すみません、デコメ購入履歴がありません。';
					}
					$this->view->payment = $payment;	
				}
				else if ($type == 7) {
					$payment = array();
					$payment['send'] = $mdalLog->getPaySend($this->_uid, $startTime, $endTime);
					
					if (empty($payment['send'])) {
						$this->view->errorId = 2;
						$this->view->error = 'すみません、プレゼント購入履歴がありません。';
					}
					$this->view->payment = $payment;	
				}
				else {
					$payment = array();
					$payment['desk'] = $mdalLog->getPayDesk($this->_uid, $startTime, $endTime);
					
					if (empty($payment['desk'])) {
						$this->view->errorId = 2;
						$this->view->error = 'すみません、壁紙購入履歴がありません。';
					}
					$this->view->payment = $payment;
				}
			}
		}
		
		$this->view->type = $type;
		$this->view->uid = $this->_uid;
		$this->view->startDate = $startDate;
		$this->view->endDate = $endDate;		
		$this->render();
	}
}
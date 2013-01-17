<?php

class HotelController extends MyLib_Zend_Controller_Action_Default
{
	public function indexAction()
	{
		
		$this->render();
		
	}
	
	
	/**
	 * predispath
	 *
	 */
	function preDispatch()
    {    
        $uid = $this->_user->getId();
        require_once 'Dal/Hotel/Huser.php';
        $dalHuser = Dal_Hotel_Huser::getDefaultInstance();
        
        $isIn = $dalHuser->isInHotel($uid);
        
        if (!$isIn) {
            
            //add user to Hotel
            require_once 'Bll/Hotel/Huser.php';
            $bllHuser = new Bll_Hotel_Huser();
            
            $userInfo = array('uid' => $uid,
                              'money' => "1000000",
                              'clean' => "50",
                              'location' => '0',
                              'business_time' => time(),
                              'join_time' => time());
            
            $re = $bllHuser->insertHuser($userInfo);
            
            
            $this->_redirect($this->_baseUrl . '/Hotel');
        }
        
    }
	
    public function choosemapAction()
    {
        
        $this->render();
    }
	 /**
     * magic function
     * if call the function is undefined,then forward to not found
     *
     * @param string $methodName
     * @param array $args
     * @return void
     */
    function __call($methodName, $args)
    {
        return $this->_redirect('index','hotel','default');
    }
}
<?php

/** @see MyLib_Zend_Controller_Action_Mobile.php */
require_once 'MyLib/Zend/Controller/Action/Mobile.php';

/**
 * Mobile kitchenshop Controller(modules/mobile/controllers/KitchenshopController.php)
 *
 * @copyright  Copyright (c) 2009 Community Factory Inc. (http://communityfactory.com)
 * @create  zhaoxh  2010-1-4
 */
class Mobile_KitchenshopController extends MyLib_Zend_Controller_Action_Mobile
{
    protected $_pageSize = 10;

    /**
     * initialize object
     * override
     * @return void
     */
    public function init()
    {
        parent::init();
    }

    /**
     * dispatch
     *
     */
    function preDispatch()
    {
        $uid = $this->_user->getId();
        $userName = $this->_user->getDisplayName();
        $this->view->app_name = 'kitchen';
        $this->view->uid = $uid;
        $this->view->userName = $userName;

        $this->view->ua = Zend_Registry::get('ua');
        $this->view->rand = time();
        $this->view->boardAppId = BOARD_APP_ID;
    }

    /**
     * index action -- welcome page
     *
     */
    public function indexAction()
    {
        $uid = $this->_user->getId();
        $this->_redirect($this->_baseUrl . '/mobile/kitchen/home');
        return;
        //$this->render();
    }

    /**
     * to shopping index page
     *
     */
    public function shoppingAction()
    {
    	$uid = $this->_user->getId();
    	
    	require_once 'Mdal/Kitchen/User.php';
        $dalUser = Mdal_Kitchen_User::getDefaultInstance();
    	$userInfo = $dalUser->getUser($uid);
    	
    	$this->view->gold = $userInfo['gold'];
    	$this->view->point = $userInfo['point'];
    	
    	require_once 'Mdal/Kitchen/Restaurant.php';
        $dalRest = Mdal_Kitchen_Restaurant::getDefaultInstance();
    	
    	$levelForEnter = $dalRest->getMaxGenreLevel($uid);
    	$this->view->level = $levelForEnter;
    	
    	$this->render();
    }

    public function foodlistAction()
    {
    	$uid = $this->_user->getId();
    	
        require_once 'Mdal/Kitchen/User.php';
        $dalUser = Mdal_Kitchen_User::getDefaultInstance();
    	$userInfo = $dalUser->getUser($uid);
    	
    	$this->view->gold = $userInfo['gold'];
    	$this->view->point = $userInfo['point'];
    	
    	require_once 'Mdal/Kitchen/Restaurant.php';
        $dalRest = Mdal_Kitchen_Restaurant::getDefaultInstance();
    	$restInfo = $dalRest->getActiveRestaurant($uid);
        	
        $category = $this->_request->getParam('category',0);
        $pageStartEdit = $this->_request->getParam('start',1);
        $pageStart = $pageStartEdit - 1;
        $pageSize = 5;
        $genre = $restInfo['genre'];
        $level = $restInfo['level'];
        
        require_once 'Mdal/Kitchen/Food.php';
        $dalFood = Mdal_Kitchen_Food::getDefaultInstance();
        $foodlist = $dalFood->listFood($category, $pageStart, $pageSize, $genre, $level);
        $count = $dalFood->cntListFood($category, $genre, $level);
        
    	$this->view->foodlist = $foodlist;
        $this->view->count = $count;
    	
        $this->view->category = $category;
        $this->view->start = $pageStartEdit;
    	$this->view->startPrev = max(1,$pageStartEdit - 5);
    	$this->view->startNext = $pageStartEdit + 5;
    	$this->render();
    }   
    
    public function foodconfirmgoldAction()
    {
    	$uid = $this->_user->getId();
    	$foodId = $this->_request->getParam('food_id');
    	
    	require_once 'Mdal/Kitchen/User.php';
        $dalUser = Mdal_Kitchen_User::getDefaultInstance();
    	$userInfo = $dalUser->getUser($uid);
    	
    	$this->view->gold = $userInfo['gold'];
    	
    	require_once 'Mdal/Kitchen/Restaurant.php';
        $dalRest = Mdal_Kitchen_Restaurant::getDefaultInstance(); 
        $restInfo = $dalRest->getActiveRestaurant($uid);
    	
    	require_once 'Mdal/Kitchen/Food.php';
        $dalFood = Mdal_Kitchen_Food::getDefaultInstance();
        $foodInfo = $dalFood->getFood($foodId);
        
        
    	$canBuy =  $restInfo['level'] >= $foodInfo['level'];
        $foodId = $this->_request->getParam('food_id');
        
        if (!$foodId || !$canBuy) {
        	$this->_redirect($this->_baseUrl . '/mobile/kitchen/error');
        }
        
        if ($foodInfo['food_price_gold'] <= $userInfo['gold']) {
        	$this->_redirect($this->_baseUrl . '/mobile/kitchenshop/foodconfirmgoldok?food_id=' . $foodId);
        }
        else {
        	$this->_redirect($this->_baseUrl . '/mobile/kitchenshop/foodconfirmgoldfail?food_id=' . $foodId);
        }
        
    	$this->render();
    }
    
    public function foodconfirmgoldokAction()
    {
    	$this->_confirmData();
        
    	$this->render();
    }
    
    public function foodconfirmgoldfailAction()
    {
    	$this->_confirmData();
        
    	$this->render();
    } 

    function _confirmData()
    {
    	$uid = $this->_user->getId();
    	$foodId = $this->_request->getParam('food_id');
    	
    	require_once 'Mdal/Kitchen/User.php';
        $dalUser = Mdal_Kitchen_User::getDefaultInstance();
    	$userInfo = $dalUser->getUser($uid);
    	
    	$this->view->gold = $userInfo['gold'];
    	$this->view->point = $userInfo['point'];
    	
    	require_once 'Mdal/Kitchen/Food.php';
        $dalFood = Mdal_Kitchen_Food::getDefaultInstance();
        $foodInfo = $dalFood->getFood($foodId);
        
        $this->view->foodInfo = $foodInfo;
        
        $categoryName = $this->_getFoodCategoryName($foodInfo['food_category']);
        
        $this->view->categoryName = $categoryName;
    }
    
    public function foodconfirmpointAction()
    {
    	$uid = $this->_user->getId();
    	$foodId = $this->_request->getParam('food_id');
    	
    	require_once 'Mdal/Kitchen/User.php';
        $dalUser = Mdal_Kitchen_User::getDefaultInstance();
    	$userInfo = $dalUser->getUser($uid);
    	
    	$this->view->point = $userInfo['point'];
    	
    	require_once 'Mdal/Kitchen/Restaurant.php';
        $dalRest = Mdal_Kitchen_Restaurant::getDefaultInstance(); 
        $restInfo = $dalRest->getActiveRestaurant($uid);
    	
    	require_once 'Mdal/Kitchen/Food.php';
        $dalFood = Mdal_Kitchen_Food::getDefaultInstance();
        $foodInfo = $dalFood->getFood($foodId);
        
        
    	$canBuy =  $restInfo['level'] >= $foodInfo['level'];
        $foodId = $this->_request->getParam('food_id');
        
        if (!$foodId || !$canBuy) {
        	$this->_redirect($this->_baseUrl . '/mobile/kitchen/error');
        }
        
        if ($foodInfo['food_price_point'] <= $userInfo['point']) {
        	$this->_redirect($this->_baseUrl . '/mobile/kitchenshop/foodconfirmpointok?food_id=' . $foodId);
        }
        else {
        	$this->_redirect($this->_baseUrl . '/mobile/kitchenshop/foodconfirmpointfail?food_id=' . $foodId);
        }
        
    	$this->render();
    }
    
    public function foodconfirmpointokAction()
    {
    	$this->_confirmData();
        
    	$this->render();
    }
    
    public function foodconfirmpointfailAction()
    {
    	$this->_confirmData();
        
    	$this->render();
    }
    
    function _getFoodCategoryName($category)
    {
    	switch ($category) {
    		case 1:
    			$categoryName = '魚介類';
    			break;
    		case 2:
    			$categoryName = '穀類';
    			break;
			case 3:
    			$categoryName = '調味料';
    			break;
    		case 4:
    			$categoryName = '肉類';
    			break;
    		case 5:
    			$categoryName = '野菜類';
    			break;
    		case 6:
    			$categoryName = '乳卵豆';
    			break;
    		case 7:
    			$categoryName = 'フルーツ';
    			break;
    		default:
    			break;
    	}
    	return $categoryName;
    }
    
    public function foodfinishAction()
    {
    	$uid = $this->_user->getId();
    	$foodId = $this->_request->getParam('food_id');
    	$payType = $this->_request->getParam('pay');
    	$payTypeStr = 'food_price_' . $payType;
    	
    	require_once 'Mdal/Kitchen/User.php';
        $dalUser = Mdal_Kitchen_User::getDefaultInstance();
    	$userInfo = $dalUser->getUser($uid);
    	
    	$this->view->gold = $userInfo['gold'];
    	$this->view->point = $userInfo['point'];
    	
    	require_once 'Mdal/Kitchen/Restaurant.php';
        $dalRest = Mdal_Kitchen_Restaurant::getDefaultInstance(); 
        $restInfo = $dalRest->getActiveRestaurant($uid);
    	
    	require_once 'Mdal/Kitchen/Food.php';
        $dalFood = Mdal_Kitchen_Food::getDefaultInstance();
        $foodInfo = $dalFood->getFood($foodId);
        
        
    	$canBuy =  $restInfo['level'] >= $foodInfo['level'];
        $foodId = $this->_request->getParam('food_id');
        
        if (!$foodId || !$canBuy) {
        	$this->_redirect($this->_baseUrl . '/mobile/kitchen/error');
        }
    	
        if ($foodInfo[$payTypeStr] <= $userInfo[$payType]) {
    		require_once 'Mbll/Kitchen/Food.php';
        	$bllFood = new Mbll_Kitchen_Food();
        	
        	$bllFood->buyFood($uid, $payType, $foodInfo);
        	//$dalUser->updateUserBy($uid, $payType, -$foodInfo[$payTypeStr]);
    		
        }
        
    	$this->render();
    }    
    
    
	/**
     * magic function
     *   if call the function is undefined,then forward to not found
     *
     * @param string $methodName
     * @param array $args
     * @return void
     */
    function __call($methodName, $args)
    {
        return $this->_redirect($this->_baseUrl . '/mobile/kitchen/error');
    }
}
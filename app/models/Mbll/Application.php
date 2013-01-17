<?php

/** Bll_Application_Interface */
require_once 'Bll/Application/Interface.php';

/** Zend_Controller_Action */
require_once 'Zend/Controller/Action.php';

/** Bll_Application_Plugin_Broker */
require_once 'Bll/Application/Plugin/Broker.php';

class Mbll_Application implements Bll_Application_Interface
{
    /**
     * $_actionController - ActionController reference
     *
     * @var Zend_Controller_Action
     */
    protected $_actionController;
    
    /**
     * host url
     * @var string
     */
    protected $_host;
    
    /**
     * application id
     * @var string
     */
    protected $_appId;
    
    /**
     * application name
     * @var string
     */
    protected $_appName;
    
    /**
     * application owner id
     * @var string
     */
    protected $_ownerId;
    
    /**
     * application viewer id
     * @var string
     */
    protected $_viewerId;
    
    /**
     * Instance of Bll_Application_Plugin_Broker
     * @var Bll_Application_Plugin_Broker
     */
    protected $_plugins = null;
    
    /**
     * other data
     * @var array
     */
    protected $_data = null;
    
    /**
     * Singleton instance
     *
     * Marked only as protected to allow extension of the class. To extend,
     * simply override {@link getInstance()}.
     *
     * @var Bll_Application
     */
    protected static $_instance = null;
    
    const OWNER  = 'OWNER';
    const VIEWER = 'VIEWER';
    
    /**
     * __construct() -
     *
     * @param Zend_Controller_Action $actionController
     * @return void
     */
    protected function __construct(Zend_Controller_Action $actionController)
    {
        $this->_actionController = $actionController;
        $this->_init();
    }
    
    /**
     * _init()
     *
     * @return void
     */
    private function _init()
    {   
        $request = $this->getRequest();
        
        $this->_host = Zend_Registry::get('host');
        
        $app_id = $request->getParam('opensocial_app_id');
        $owner_id = $request->getParam('opensocial_owner_id');

        if (empty($app_id) || empty($owner_id)) {
            $this->redirect404();
            exit;
        }
     
        $app_name = $this->_getAppName($app_id);
     
        if (empty($app_name)) {
            $this->redirect404();
            exit;
        }
        
        $this->_plugins = new Bll_Application_Plugin_Broker();
        $this->_data = array();
        
        $this->_appId = $app_id;
        $this->_ownerId = $owner_id;
        //now, viewer = owner
        $this->_viewerId = $owner_id;
        $this->_appName = $app_name;
    }
    
    private function _getAppName($appId)
    {
        require_once  'Bll/Restful/Consumer.php';
        $consumer = Bll_Restful_Consumer::getConsumerData($appId);
        
        if ($consumer != null) {
            return $consumer['app_name'];
        }
        
        return '';
    }
    
    /**
     * Singleton instance, if null create an new one instance.
     *
     * @param Zend_Controller_Action $actionController
     * @return Bll_Application
     */
    public static function newInstance(Zend_Controller_Action $actionController)
    {
        if (null === self::$_instance) {
            self::$_instance = new self($actionController);
        }

        return self::$_instance;
    }
    
    /**
     * get singleton instance
     *
     * @return Bll_Application
     */
    public static function getInstance()
    {
        if (null === self::$_instance) {
            throw new Exception('Application instance has not been created! Please use "newInstance" to create one.');
        }

        return self::$_instance;
    }
    
    /**
     * get application id
     *
     * @return string
     */
    public function getAppId()
    {
        return $this->_appId;
    }
    
    /**
     * get owner id
     *
     * @return string
     */
    public function getOwnerId()
    {
        return $this->_ownerId;
    }
    
    /**
     * get viewer id
     *
     * @return string
     */
    public function getViewerId()
    {
        return $this->_viewerId;
    }
    
    /**
     * check is owner
     * 
     * @param string $uid
     * @return bool
     */
    public function isOwner($uid)
    {
        return $uid == $this->_ownerId;
    }
    
    /**
     * check is viewer
     * 
     * @param string $uid
     * @return bool
     */
    public function isViewer($uid)
    {
        return $uid == $this->_viewerId;
    }
    
    /**
     * check viewer and owner is same person
     * 
     * @return bool
     */
    public function isSamePerson()
    {
        return $this->_ownerId == $this->_viewerId;
    }
        
    
    /**
     * get stored data
     * 
     * @param string $name
     * @return object
     */
    public function getData($name)
    {
        if(isset($this->_data[$name])) {
            return $this->_data[$name];
        }
        
        throw new Exception('The data of name "' . $name . '" is not set.');
    }
    
    /**
     * store data
     * 
     * @param string $name
     * @param object $value
     * @return void
     */
    public function setData($name, $value)
    {
        $this->_data[$name] = $value;
    }
    
    /**
     * Get request object
     *
     * @return Zend_Controller_Request_Abstract $request
     */
    public function getRequest()
    {
        return $this->_actionController->getRequest();
    }
    
    /**
     * Register a plugin.
     *
     * @param  Bll_Application_Plugin_Interface $plugin
     * @param  int $stackIndex Optional; stack index for plugin
     * @return Bll_Application
     */
    public function registerPlugin(Bll_Application_Plugin_Interface $plugin, $stackIndex = null)
    {
        $this->_plugins->registerPlugin($plugin, $stackIndex);
        return $this;
    }
    
    public function autoRegisterPlugin()
    {
        if (!empty($this->_appName)) {
            $name = ucfirst($this->_appName);
            $pluginFile = 'Mbll/Application/Plugin/' . $name . '.php';
            if (file_exists(MODELS_DIR . '/' . $pluginFile)) {
                require_once $pluginFile;
                $pluginClassName = 'Mbll_Application_Plugin_' . $name;
                $plugin = new $pluginClassName();
                $this->_plugins->registerPlugin($plugin, null);
                return true;
            }
        }
        return false;
    }

    /**
     * Unregister a plugin.
     *
     * @param  string|Bll_Application_Plugin_Interface $plugin Plugin class or object to unregister
     * @return Bll_Application
     */
    public function unregisterPlugin($plugin)
    {
        $this->_plugins->unregisterPlugin($plugin);
        return $this;
    }
    
    /**
     * Redirect to another URL
     *
     * Proxies to {@link Zend_Controller_Action_Helper_Redirector::gotoUrl()}.
     *
     * @param string $url
     * @param array $options Options to be used when redirecting
     * @return void
     */
    public function redirect($url, array $options = array())
    {
        if (!preg_match('|^[a-z]+://|', $url)) {
            $url = $this->_host . $url;
        }
        $redirector = $this->_actionController->getHelper('redirector');
        $redirector->gotoUrl($url, $options);
    }
    
    /**
     * Redirect to "/error/notfound"
     * 
     * @return void
     */
    public function redirect404()
    {
        $this->redirect('/mobile/error/notfound');
        exit;
    }
    
    public function redirectStop()
    {
        $this->redirect('/mobile/error/stop');
        exit;        
    }
    
    /**
     * update user info
     *
     * @param  array $userInfo = array('user' => xxxx,'friends' => yyyy)
     * @param  string $priority
     * @return void
     */
    protected function _updateInfo()
    {        
        $uid = $this->_ownerId;
        
        if (Bll_Cache_User::isUpdated($uid)) {
            return;
        }
        
        require_once 'Bll/Restful.php';
        $restful = Bll_Restful::getInstance($uid, $this->_appId);
        
        if ($restful == null) {
            $this->redirect404();
            exit;
        }
        
        $userInfo = $restful->getUserAndFriends();
                
        if ($restful->hasError()) {
            debug_log($restful->getErrorMessage());
            $this->redirect404();
            exit;
        }
        
        require_once "OpenSocial/Collection.php";
        require_once "OpenSocial/Person.php";
        require_once 'Zend/Json.php';
        
        //update user and friends info
        $person = $restful->parsePerson($userInfo['user']);
        Bll_User::updatePerson($person);
        
        //
        $this->_plugins->postUpdatePerson($uid);
        
        $fids = array();
        $fidsHasApp = array();
        $friends = $userInfo['friends'];
        if ($friends instanceof osapiPerson) {
            $friendsList = array($friends);
        } else {
            $friendsList = $userInfo['friends']->getList();
        }

        foreach ($friendsList as $op) {
            $p = $restful->parsePerson($op);
            $fids[] = $p->getId();
            $hasApp = $p->getField('hasApp', false);
            if ($hasApp == 'true' || $hasApp == 1) {
                $fidsHasApp[] = $p->getId();
            }
            //update user friends info
            Bll_User::updatePerson($p);
            
            //
            $this->_plugins->postUpdateFriend($p->getId());
        }
        if (count($fids) > 0) {
            // update user friends relationship
            Bll_Friend::updateFriends($uid, $fids);
            //
            $this->_plugins->postUpdateFriendship($uid, $fids);
        }
        if (count($fidsHasApp) > 0) {
            // update user friends relationship that has joined app
            $this->_plugins->updateAppFriendship($uid, $fidsHasApp);
        }
        
        Bll_Cache_User::setUpdated($uid);
    }
    
    /**
     * run() - main mothed
     * 
     * @return void
     */
    public function run()
    {        
        $this->_updateInfo();
        //
        $this->_plugins->postRun($this);
    }
}
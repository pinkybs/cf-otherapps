<?php

/** Bll_Application_Interface */
require_once 'Bll/Application/Plugin/Interface.php';

/** Zend_Controller_Action */
require_once 'Zend/Controller/Action.php';

/** Bll_Application_Plugin_Broker */
require_once 'Bll/Application/Plugin/Broker.php';

class Bll_Application implements Bll_Application_Interface
{
    /**
     * $_actionController - ActionController reference
     *
     * @var Zend_Controller_Action
     */
    protected $_actionController;
    
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
     * mixi top url
     * @var string
     */
    protected $_topUrl;
    
    /**
     * mixi top url params
     * @var array
     */
    protected $_topParams;
    
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
        $this->_plugins = new Bll_Application_Plugin_Broker();
        $this->_data = array();
        $this->_topParams = array();
        
        $request = $this->getRequest();
        if (! $request->isPost()) {
            debug_log(__LINE__ . ': redirect404()');
            $this->redirect404();
        }
        
        $nonce = $request->getPost('nonce');
        if (!$nonce) {
            debug_log(__LINE__ . ': redirect404()');
            $this->redirect404();
        }
        
        require_once 'Bll/Nonce.php';
        $valid = Bll_Nonce::isValid($nonce, $data);
        if (!$valid) {
            debug_log(__LINE__ . ': redirect404()');
            $this->redirect404();
        }
        
        $this->_appId = $data['app_id'];
        $this->_ownerId = $data['owner_id'];
        $this->_viewerId = $data['viewer_id'];
        $this->_appName = $data['app_name'];

        $this->_topUrl = $request->getPost('top_url');
        $this->_parseTopParams();
        
        $request->setParam('uid', $this->_ownerId);
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
    
    private function _parseTopParams()
    {
        if (!empty($this->_topUrl)) {
            $info = parse_url($this->_topUrl);
            if ($info['query']) {
                parse_str($info['query'], $this->_topParams);
            }
        }
    }
    
    public function getTopParam($name)
    {
        if(isset($this->_topParams[$name])) {
            return $this->_topParams[$name];
        }
        
        return '';
    }
    
    public function getInvite()
    {
        return $this->getTopParam('invite');
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
            $pluginFile = 'Bll/Application/Plugin/' . $name . '.php';            
            if (file_exists(MODELS_DIR . '/' . $pluginFile)) {
                require_once $pluginFile;
                $pluginClassName = 'Bll_Application_Plugin_' . $name;
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
        $this->redirect('/error/notfound');
        exit;
    }
    
    /**
     * update user info
     *
     * @param  array $userInfo = array('user' => xxxx,'friends' => yyyy)
     * @param  string $priority
     * @return void
     */
    protected function _updateInfo($userInfo, $priority)
    {
        $user = $userInfo['user'];
        if (!$user) {
            debug_log(__LINE__ . ': redirect404()');
            $this->redirect404();
        }
        
        // update owner info
        $person = OpenSocial_Person::parseJson($user);
        
        $uid = $person->getId();
        if ($priority == self::OWNER) {
            if (!$this->isOwner($uid)) {
                debug_log(__LINE__ . ': redirect404()');
                $this->redirect404();
            }
        }
        
        if ($priority == self::VIEWER) {
            if (!$this->isViewer($uid)) {
                debug_log(__LINE__ . ': redirect404()');
                $this->redirect404();
            }
        }
        
        if (Bll_Cache_User::isUpdated($uid)) {
            return;
        } 
        
        Bll_User::updatePerson($person);
        
        //
        $this->_plugins->postUpdatePerson($uid);
                
        $friends = $userInfo['friends'];
        if ($friends) {            
            $people = OpenSocial_Person::parseJsonCollection(0, count($friends), $friends);
        
            $fids = array();
            $fidsHasApp = array();
            foreach ($people as $p) {
                $fids[] = $p->getId();
                $hasApp = $p->getField('hasApp', false);
                if ($hasApp == 'true' || $hasApp == 1) {
                    $fidsHasApp[] = $p->getId();
                }
                // update user friends info
                Bll_User::updatePerson($p);
                
                //
                $this->_plugins->postUpdateFriend($p->getId());
            }
            
            //Bll_User::updatePeople($people);
            
            // update user friends relationship
            Bll_Friend::updateFriends($uid, $fids);
            
            //
            $this->_plugins->postUpdateFriendship($uid, $fids);
                        
            //
            if ($fidsHasApp) {
                $this->_plugins->updateAppFriendship($uid, $fidsHasApp);
            }
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
        require_once "OpenSocial/Collection.php";
        require_once "OpenSocial/Person.php";
        require_once 'Zend/Json.php';

        $request = $this->getRequest();
        
        $viewerInfo = Zend_Json::decode($request->getPost('viewer_info'));
                
        if ($viewerInfo) {
            $this->_updateInfo($viewerInfo, self::VIEWER);

            if (!$this->isSamePerson()) {
                $ownerInfo = Zend_Json::decode($request->getPost('owner_info'));

                if ($ownerInfo) {
                    $this->_updateInfo($ownerInfo, self::OWNER);
                }
            }
        }
        
        // set cookie
        $expries = time() + 3*24*60*60;
        $path = '/';
        $params = session_get_cookie_params();

        //P3P privacy policy to use for the iframe document
        //for IE
        header('P3P: CP=CAO PSA OUR');
        
         // start session
        $auth = Zend_Auth::getInstance();
        $auth->getStorage()->write($this->_viewerId); 
        
        setcookie('app_mixi_uid', $this->_viewerId, $expries, $path, $params['domain']);
        require_once 'Bll/Secret.php';
        $sig = Bll_Secret::getSecretResult($this->_viewerId);
        setcookie('app_mixi_sig', $sig, $expries, $path, $params['domain']);

        setcookie('app_top_url_' . $this->_appName, $this->_topUrl, $expries, $path, $params['domain']);
        setcookie('app_top_url', $this->_topUrl, $expries, $path, $params['domain']);
        
        //$mixi_platform_api_url = $request->getParam('mixi_platform_api_url') . '&rpc_mode=1';
        //we found on rpc_replay.html on mixi api platform
        //url like: http://5b405adcd06c95e57e81f1bd7758e9826d123267.app0.mixi-platform.com/gadgets/files/container/rpc_relay.html
        
        $mixi_platform_api_url = $request->getParam('mixi_platform_api_url', '');
        if ($mixi_platform_api_url) {
            $url_ifo = parse_url($mixi_platform_api_url);
            $mixi_platform_api_url = $url_ifo['scheme'] . '://' . $url_ifo['host'] . '/gadgets/files/container/rpc_relay.html';
            setcookie('mixi_platform_api_url_' . $this->_appName, $mixi_platform_api_url, $expries, $path, $params['domain']);
        }
        
        //
        $this->_plugins->postRun($this);
    }
        
    /**
     * check signature is valid
     * 
     * @param output array $parameters
     * @return bool
     * 
     *  opensocial_owner_id         eg: 13915816
     *  opensocial_viewer_id        eg: 13915816
     *  opensocial_app_id           eg: 1325
     *  opensocial_app_url          eg: http://mixitest.linno.jp/static/apps/parking/mixitest.xml
     *
     *  oauth_token                 ''[empty]
     *  oauth_consumer_key          mixi.jp
     *  xoauth_signature_publickey  mixi.jp
     *  oauth_signature_method      RSA-SHA1
     *  oauth_nonce                 eg: e1b0d08891eb95c0
     *  oauth_timestamp             eg: 1245164539
     *  oauth_signature             eg: rr40jvcsPjJ+bI0cFh+eKlVgEj+iXCMihUBTPEPcDoO+IkUwA4YSjFHlpWRloYHMigc1prH1YHFDm3TmeTXojQtjZi+P6PEbyzronrocPxrEb2S6Hsmb+g262c1EjhMyEzcRZAXscKuFkIUsuOVI/fzMRPM1HBQ7arBW8jGv8rg=
     *
     */
    public static function isValidSignature(&$parameters)
    {
        require_once 'osapi/external/OAuth.php';
        //Build a request object from the current request
        $request = OAuthRequest::from_request(null, null, array_merge($_GET, $_POST));
        $xoauth_signature_publickey = $request->get_parameter('xoauth_signature_publickey');
        //Initialize the new signature method
        if ($xoauth_signature_publickey == 'mixi.jp') {
            require_once 'osapi/external/MixiSignatureMethodOld.php';
            $signature_method = new MixiSignatureMethodOld();
            $ip = false;
            if(!empty($_SERVER["HTTP_CLIENT_IP"])){
                $ip = $_SERVER["HTTP_CLIENT_IP"];
            }
            if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $ips = explode (", ", $_SERVER['HTTP_X_FORWARDED_FOR']);
                if ($ip) { 
                    array_unshift($ips, $ip); 
                    $ip = false; 
                }
                for ($i = 0, $n = count($ips); $i < $n; $i++) {
                    if (!eregi ("^(10|172\.16|192\.168)\.", $ips[$i])) {
                        $ip = $ips[$i];
                        break;
                    }
                }
            }
            if (!$ip) {
                $ip = $_SERVER['REMOTE_ADDR'];
            }
            info_log('xoauth_signature_publickey is old, ip: ' . $ip, 'xoauth_signature_publickey');
        } else {
            require_once 'osapi/external/MixiSignatureMethod.php';
            $signature_method = new MixiSignatureMethod();
        }
        
        //Check the request signature
        $signature = rawurldecode($request->get_parameter('oauth_signature'));
        @$signature_valid = $signature_method->check_signature($request, null, null, $signature);
        //$signature_valid = true;
        
        if ($signature_valid) {
            $parameters = array(
                'app_id'    => $request->get_parameter('opensocial_app_id'),
                'owner_id'  => $request->get_parameter('opensocial_owner_id'),
                'viewer_id' => $request->get_parameter('opensocial_viewer_id')
            );
        }
        else {
            $parameters = array();
        }
        
        return $signature_valid;
    }

}
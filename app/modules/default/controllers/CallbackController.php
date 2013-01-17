<?php

/* Bll_Application */
require_once 'Bll/Application.php';

/* Bll_Application_Log */
require_once 'Bll/Application/Log.php';

/**
 * application callback controller
 *
 * @copyright  Copyright (c) 2009 Community Factory Inc. (http://communityfactory.com)
 * @create    2009/08/07    HLJ
 */
class CallbackController extends Zend_Controller_Action
{
    /**
     * web site base url
     *
     * @var string
     */
    protected $_baseUrl;

    /**
     * init
     *  init the data
     */
    function init()
    {

    }

    /**
     * index Action
     *
     */
    public function indexAction()
    {
    	echo 'callback';
    	exit;
    }

    public function checkstatusAction()
    {
        $status = array();
        //OK,  code = 1
        //Stop,code = 0
        $status['code'] = 1;

        $app_name = $this->_request->getParam('app_name', '');
        $view = $this->_request->getParam('view');

        if ($status['code'] == 0) {
            if ($view == 'canvas') {
                $status['html'] = '<img src="http://static.mixi.communityfactory.net/apps/warning.png" width="420" height="420" border="0" />';
            }
            else {
                $status['html'] = '<img src="http://static.mixi.communityfactory.net/apps/warning.png" width="210" height="210" border="0" />';
            }
        }
        else {
            $signature_valid = Bll_Application::isValidSignature($parameters);
            if ($signature_valid == true) {
                $requestNonce = $this->_request->getParam('request_nonce');
                if ($requestNonce) {
                    require_once 'Bll/Nonce.php';
                    $nonce = Bll_Nonce::createNonce($parameters['app_id'], $parameters['owner_id'], $parameters['viewer_id'], $app_name);
                    $status['nonce'] = $nonce;
                    $status['html'] = $nonce;
                    $isUpdated = Bll_Cache_User::isUpdated($parameters['viewer_id']);
                    if ($isUpdated) {
                        $status['isUpdated'] = 'true';
                    }
                }
                else {
                    $status['html'] = 'HTTP 200, OK';
                }
                $status['parameters'] = $parameters;

                //
                Bll_Application_Log::view($parameters['app_id'], $parameters['owner_id'], $parameters['viewer_id'],  $view);
            } else {
                $status['code'] = -1;
                $status['html'] = "This request was spoofed";
            }
        }

        echo Zend_Json::encode($status);
        exit;
    }

    public function inviteAction()
    {
        $recipientIds = $this->_request->getParam('recipientIds');
        $result = false;
        if ($recipientIds) {
            $signature_valid = Bll_Application::isValidSignature($parameters);

            if ($signature_valid == true) {
                $count = count(explode(',', $recipientIds));

                require_once 'Bll/Invite.php';
                $result = Bll_Invite::addMultiple($parameters['app_id'], $parameters['viewer_id'], $recipientIds);

                require_once 'Bll/Application/Log.php';
                $result = Bll_Application_Log::invite($parameters['app_id'], $parameters['viewer_id'], $recipientIds, $count);

            }
        }

        echo $result ? 'true' : 'false';
        exit;
    }

    public function activityAction()
    {
        $recipientIds = $this->_request->getParam('recipientIds', '');
        $result = false;
        if ($recipientIds) {
            $signature_valid = Bll_Application::isValidSignature($parameters);

            if ($signature_valid == true) {
                $count = count(explode(',', $recipientIds));
                require_once 'Bll/Application/Log.php';
                $result = Bll_Application_Log::activity($parameters['app_id'], $parameters['viewer_id'], $recipientIds, $count);
            }
        }

        echo $result ? 'true' : 'false';
        exit;
    }
    
    //http://developer.mixi.co.jp/appli/pc/lets_enjoy_making_mixiapp/lifecycle_event
    private function checkSignature(&$parameters)
    {
        require_once 'osapi/external/MixiSignatureMethod.php';
        //Build a request object from the current request
        $request = OAuthRequest::from_request(null, null, null, true);
                
        //Initialize the new signature method
        $signature_method = new MixiSignatureMethod();
        //Check the request signature
        $signature = rawurldecode($request->get_parameter('oauth_signature'));
                
        @$signature_valid = $signature_method->check_signature($request, null, null, $signature);
                
        if ($signature_valid) {
            $parameters = $request->get_parameters();
        }
        else {
            $parameters = array();
        }
        
        return $signature_valid;
    }
    
    
    public function addappAction()
    {
        $signature_valid = $this->checkSignature($parameters);
        if ($signature_valid == true) {
            $eventtype = $parameters['eventtype'];
            $opensocial_app_id = $parameters['opensocial_app_id'];
            $id = $parameters['id'];
            $mixi_invite_from = $parameters['mixi_invite_from'];
            

            if ($eventtype == 'event.addapp') {                
                $impl = Bll_Lifecycle_Factory::getImplByAppId($opensocial_app_id);
                if ($impl) {
                    $impl->add($opensocial_app_id, $id, $mixi_invite_from);
                }
                //add addapp log
                require_once 'Bll/Statistics.php';
                $result = Bll_Statistics::addLogin($opensocial_app_id, $id, $mixi_invite_from);
                //update successed invited  stats
                require_once 'Bll/Invite.php';
                $result = Bll_Invite::update($opensocial_app_id, $id, $mixi_invite_from);
            }
        }
        
        exit;        
    }
    
    public function removeappAction()
    {
        $signature_valid = $this->checkSignature($parameters);
        if ($signature_valid == true) {
            $eventtype = $parameters['eventtype'];
            $opensocial_app_id = $parameters['opensocial_app_id'];
            $id = $parameters['id'];
            if ($eventtype == 'event.removeapp') {
                $impl = Bll_Lifecycle_Factory::getImplByAppId($opensocial_app_id);
                if ($impl) {
                    $impl->remove($opensocial_app_id, $id);
                }
                //remove app
                require_once 'Bll/Statistics.php';
                Bll_Statistics::addRemove($opensocial_app_id, $id, $mixi_invite_from);
            }
        }
        
        exit;
    }

    /**
     * magic function
     *   if call the function is undefined,then echo undefined
     *
     * @param string $methodName
     * @param array $args
     * @return void
     */
    function __call($methodName, $args)
    {
        echo 'undefined method name: ' . $methodName;
        exit;
    }

 }

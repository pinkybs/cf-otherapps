<?php

/**
 * application lifecycle controller
 *
 * @copyright  Copyright (c) 2009 Community Factory Inc. (http://communityfactory.com)
 * @create    2009/08/07    HLJ
 */
class LifecycleController extends Zend_Controller_Action
{

    /**
     * index Action
     *
     */
    public function indexAction()
    {
    	echo 'lifecycle';
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
            if ($eventtype == 'event.addapp' && !empty($id) && !isset($parameters['opensocial_owner_id'])) {
                $impl = Bll_Lifecycle_Factory::getImplByAppId($opensocial_app_id);
                if ($impl) {
                    $impl->add($opensocial_app_id, $id, $mixi_invite_from);
                }
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
            if ($eventtype == 'event.removeapp' && !empty($id) && !isset($parameters['opensocial_owner_id'])) {
                $impl = Bll_Lifecycle_Factory::getImplByAppId($opensocial_app_id);
                if ($impl) {
                    $impl->remove($opensocial_app_id, $id);
                }
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

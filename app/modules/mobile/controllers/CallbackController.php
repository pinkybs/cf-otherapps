<?php

/** @see MyLib_Zend_Controller_Action_Mobile.php */
require_once 'MyLib/Zend/Controller/Action/Mobile.php';

/**
 * application callback controller
 *
 * @copyright  Copyright (c) 2009 Community Factory Inc. (http://communityfactory.com)
 * @create    2009/09/23    HLJ
 */
class Mobile_CallbackController extends MyLib_Zend_Controller_Action_Mobile
{
    public function inviteAction()
    {
        $recipientIds = $this->_request->getParam('invite_member');
        $forward = $this->_request->getParam('forward');

        $app_id = $this->_request->getParam('opensocial_app_id');
        $user_id = $this->_request->getParam('opensocial_owner_id');

        if ($recipientIds) {
            $count = count(explode(',', $recipientIds));

            require_once 'Bll/Invite.php';
            $result = Bll_Invite::addMultiple($app_id, $user_id, $recipientIds);

            require_once 'Bll/Application/Log.php';
            $result = Bll_Application_Log::invite($app_id, $user_id, $recipientIds, $count, 'mobile');

            if ($app_id == 10487) {
                require_once 'Mbll/Disney/Index.php';
                $mbllIndex = new Mbll_Disney_Index();
                $mbllIndex->invite($user_id, $recipientIds, $this->_APP_ID);
            }
        	else if ($app_id == 8976) {
        		//info_log($recipientIds,'brainInvite');
                require_once 'Mbll/Brain/Brain.php';
                $mbllBrain = new Mbll_Brain_Brain();
                $mbllBrain->invite($user_id, $recipientIds);
            }
        	else if ($app_id == 9461) {
                require_once 'Mbll/Brain/Brain.php';
                $mbllBrain = new Mbll_Brain_Brain();
                $mbllBrain->invite($user_id, $recipientIds);
            }
            else if ($app_id == 13522) {
                require_once 'Mbll/Ship/User.php';
                $mbllUser = new Mbll_Ship_User();
                $mbllUser->invite($user_id, $recipientIds);
            }
            else if ($app_id == 13651) {
                require_once 'Mbll/Ship/User.php';
                $mbllUser = new Mbll_Ship_User();
                $mbllUser->invite($user_id, $recipientIds);
            }
        }

        if ($forward) {
            $this->_redirect($forward);
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

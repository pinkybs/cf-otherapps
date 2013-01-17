<?php

/**
 * linno ap controller
 * init each index page
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/06/25	Shenhw
 */
class LinnoController extends MyLib_Zend_Controller_Action_Default
{
    public function indexAction()
    {
        //get linno user information
        require_once 'Dal/Linno/Luser.php';
        $dalLuser = Dal_Linno_Luser::getDefaultInstance();
        $currentUser = $dalLuser->getLinnoUser($this->_user->getId());
        
        $this->view->user = $currentUser;
        $this->view->user_pic = $this->_user->getThumbnailUrl();
        $this->view->profile_url = $this->_user->getProfileUrl();
        
        //get user common network user
        $networkUser = $dalLuser->getCommonNetworkUser($currentUser['uid'], $currentUser['network_id']);
        require_once 'Bll/User.php';
        Bll_User::appendPeople($networkUser, 'uid');
        $this->view->networkUser = $networkUser;
        
        //get popular enquire
        
        //get popular evaluate
        
        
        $this->render();
    }

    public function enquirelistAction()
    {
        $pageIndex = $this->_request->getParam('page', 1);
        $this->view->pageIndex = $pageIndex;
        
        $uid = $this->_user->getId();
        
        require_once 'Dal/Linno/Luser.php';
        $dalLinnoUser = new Dal_Linno_Luser();
        //get user linno info
        $userInfo = $dalLinnoUser->getLinnoUser($uid);
        
        require_once 'Bll/Linno/Enquire.php';
        $bllLinnoEnquire = new Bll_Linno_Enquire();
        
        //get rand Enquire info
        $randEnquire = $bllLinnoEnquire->getRandNotAnswerEnquire($uid, 2);
        $this->view->randEnquire = $randEnquire;
                
        require_once 'Dal/Linno/Enquire.php';
        $dalLinnoEnquire = new Dal_Linno_Enquire();
        
        //get popular Enquire info
        $popularEnquire = $dalLinnoEnquire->getPopularEnquire(1, 5);
        $this->view->popularEnquire = $popularEnquire;
        
        //get about user Enquire info
        $aboutUserEnquire = $dalLinnoEnquire->getAboutUserEnquire($uid, $userInfo['network_id'], 1, 5);
        $this->view->aboutEnquire = $aboutUserEnquire;
        
        //get user not answer Enquire info
        $notAnswerEnquire = $dalLinnoEnquire->getNotAnswerEnquire($uid, $pageIndex, 5);
        $notAnswerEnquireCount = $dalLinnoEnquire->getNotAnswerEnquireCount($uid);
        $this->view->notAnswerEnquire = $notAnswerEnquire;
        $this->view->notAnswerEnquireCount = $notAnswerEnquireCount;
        
        $count = $notAnswerEnquireCount > 15 ? 15 : $notAnswerEnquireCount;
        $this->view->count = $count;
        
        $this->render();
    }
    
    public function enquireAction()
    {
        $qid = $this->_request->getParam('qid');
        
        $uid = $this->_user->getId();
        
        require_once 'Dal/Linno/Enquire.php';
        $dalLinnoEnquire = new Dal_Linno_Enquire();
        //get enquire info
        $enquireInfo = $dalLinnoEnquire->getEnquire($qid);
        
        if ( !$enquireInfo ) {
            $this->_redirect($this->_baseUrl . 'linno/enquirelist');
        }
        
        //check is have answered enquire
        $isAnsweredEnquire = $dalLinnoEnquire->isAnsweredEnquire($uid, $qid);
        
        if ( $isAnsweredEnquire ) {
            $this->_redirect($this->_baseUrl . 'linno/enquireresult');
        }
        
        $enquireInfo['answer'] = $dalLinnoEnquire->getEnquireAnswer($qid);
        $this->view->enquireInfo = $enquireInfo;
        
        //get popular Enquire info
        $popularEnquire = $dalLinnoEnquire->getPopularEnquire(1, 5);
        $this->view->popularEnquire = $popularEnquire;
        
        $this->render();
    }
    
    /**
     * dispatch
     *
     */
    function preDispatch()
    {
        $this->view->csstype = 'linno';
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
        return $this->_forward('notfound','error','default');
    }
}
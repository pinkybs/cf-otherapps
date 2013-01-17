<?php

/**
 * board controller
 * init each index page
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/02/10    Liz
 */
class ChomeboardController extends MyLib_Zend_Controller_Action_Default
{

    /**
     * index Action
     *
     */
    public function indexAction()
    {
        $viewerId = $this->_user->getId();
        $bownerId = $this->_request->getParam('uid', $viewerId);
        $this->view->viewerId = $viewerId;
        $this->view->bownerId = $bownerId;
        
        require_once 'Bll/Chomeboard/Chomeboard.php';
        $bllChomeboard = new Bll_Chomeboard_Chomeboard();
        
        //$bllChomeboard->newChomeBoard4Friends($viewerId);
        //$this->view->friendsFeed = $friendsFeed;
        
        $type1 = 2;
        $type2 = 1;
        $this->view->type1 = $type1;
        $this->view->type2 = $type2;
        
        //get rank info about user
        $response = $bllChomeboard->getRankInfo($viewerId, $type1, $type2);
        
        $this->view->userRankNm = $response['userRankNm'];
        $this->view->rankInfo = $response['rankInfo'];
        $rankCount = count($response['rankInfo']);
        $this->view->rankCount = $rankCount;

        if ( $rankCount < 12 ) {
            //$this->view->rankingLeft = (8 - $rankCount)*58;
            $arrInvite = array();
            for ( $i=0, $icount = (12 - $rankCount); $i<$icount; $i++ ) {
                $arrInvite[$i] = $i;
            }
            $this->view->arrInvite = $arrInvite;
            $this->view->allInvite = 14;
        }

        //get can move right count
        $rightCount = $rankCount > 12 ? ($rankCount-12) : 0;
        $this->view->rightCount = $rightCount;
        $this->view->allCount = $count;
        
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
        return $this->_forward('notfound', 'error', 'default');
    }

}

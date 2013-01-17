<?php

/**
 * afrac controller
 * init each index page
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/08/05	zhaoxh
 */
class AfracController extends MyLib_Zend_Controller_Action_Default
{
    /**
     * index action
     *
     */
    public function indexAction()
    {
        $uid = $this->_user->getId();
        require_once 'Bll/User.php';
        require_once 'Dal/Afrac/User.php';
        $dalUser = Dal_Afrac_User::getDefaultInstance();
        
        //get fids
        require_once 'Bll/Friend.php';
        $fids = Bll_Friend::getFriendIds($uid);
        if ($fids == null) {
            $fids = $uid;
        }
        else {
            $fids = $fids . ',' . $uid;
        }
        
        //get user count
        $userCnt = $dalUser->countUser(2,$fids);
        //get user`s rank and score
        $userRankScore = $dalUser->getRankScore($uid,2,$fids);
        $this->view->best = $userRankScore['score'];
        
        //get user list data
        if ($userCnt < 6) {
            
            //real data
            $userList = $dalUser->getUserList(1,6,2,$fids);
            Bll_User::appendPeople($userList);
            
            //make false data
            for ($i = count($userList); $i < 6; $i++) {
                $userList[$i]['uid'] = "??????さん";
                $userList[$i]['rank'] = "?";
                $userList[$i]['score'] = "????";
                $userList[$i]['displayName'] = "????さん";
                $userList[$i]['miniThumbnailUrl'] = $this->_staticUrl . "/apps/afrac/img/default_pic.gif";
            }
            
            $this->view->rankBegin = 1;
            $this->view->rankEnd = $userCnt;
        }
        else { 
            if ($userCnt >= 6 && $userRankScore['rank']+3 <= $userCnt) {
                $userList = $dalUser->getUserList(max($userRankScore['rank']-2,1),6,2,$fids);
            }
            else if ($userCnt >= 6 && $userRankScore['rank']+3 > $userCnt) {
                $userList = $dalUser->getUserList($userCnt-5,6,2,$fids);
            }
            
            Bll_User::appendPeople($userList);
            
            $this->view->rankBegin = $userList[0]['rank'];
            $this->view->rankEnd = $userList[count($userList)-1]['rank'];           
        }
        
        /*
        append long-displayName to with '..'
        for ($i = 0; $i < 6; $i++) {
            $userList[$i]['displayName'] = $this->_stringCut($userList[$i]['displayName']);
        }
        */
        
        $this->view->rankInfo = $userList;
        $this->view->userCnt = $userCnt;
        $this->view->uid = $uid;
        $this->render();
    }       
    
    /**
     * predispatch
     *
     */
    function preDispatch()
    {
        $uid = $this->_user->getId();
        /*
        require_once 'Bll/Afrac/User.php';
        $batchAfrac = new Bll_Afrac_User();
    	$batchAfrac->refreshAfracTmp();
        */
        require_once 'Dal/Afrac/User.php';
        $dalUser = Dal_Afrac_User::getDefaultInstance();

        $isIn = $dalUser->isInAfrac($uid);
        
        //if first come
        if (!$isIn) {
            $this->view->first = 1;
            //add user to Afrac
            require_once 'Bll/Afrac/User.php';
            $bllUser = new Bll_Afrac_User();
            $userInfo = array('uid'=>$uid,
                              'score'=>0,
                              'create_time'=>time());
            $bllUser->insertUser($userInfo);
        }
        else {
            $this->view->first = 0;
        }
    }
    
    function _stringCut($string) {
        $maxLength = 20;
        $num = 0;
        $charI = $string;
        for ($i = 0; $i < mb_strlen($string,'utf8'); $i++) {
            if (ord($charI) > 128) {
                $num += 2;
            }
            else {
                $num += 1;
            }
            $charI = mb_substr($string,$i+1,mb_strlen($string,'utf8'),'utf8');
            if ($num > $maxLength) {
                $string = mb_substr($string,0,$i,'utf8');
                $string .= '..';
                break;
            }
        }
        return $string;
    }
    
    /**
     * error action
     *
     */
    public function errorAction()
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
        return $this->_forward('notfound','error','default');
    }
}
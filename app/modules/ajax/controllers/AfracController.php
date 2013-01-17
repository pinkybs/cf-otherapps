<?php
/** @see Zend_Json */
require_once 'Zend/Json.php';

/** @see MyLib_Zend_Controller_Action_Ajax */
require_once 'MyLib/Zend/Controller/Action/Ajax.php';

/**
 * Afrac Ajax Controllers
 *
 * @copyright  Copyright (c) 2009 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/08/06   Zhaoxh
 */
class Ajax_AfracController extends MyLib_Zend_Controller_Action_Ajax
{     
    /**
     * get more rank info
     *
     */
    public function getrankinfoAction() 
    {
        if ($this->_request->isPost()) {
            $rankStart = $this->_request->getPost('rankStart');
        	$rankEnd = $this->_request->getPost('rankEnd');
            $direction = $this->_request->getPost('direction');
        	$userCnt = $this->_request->getPost('userCnt');
        	$type = $this->_request->getPost('type');
        	$uid = $this->_user->getId();
        	
        	require_once 'Bll/User.php';
        	require_once 'Dal/Afrac/User.php';
            $dalAfracUser = Dal_Afrac_User::getDefaultInstance();
            
            //get friend list
            if ($type == 2) {
                require_once 'Bll/Friend.php';
                $fids = Bll_Friend::getFriendIds($uid);
                if ($fids == null) {
                    $fids = $uid;
                }
                else {
                    $fids = $fids . ',' . $uid;
                }
            }
            
            if ($direction == 'down') {
                $userList = $dalAfracUser->getUserList($rankEnd+1,6,$type,$fids,1);   //20091105 modify by zhaoxh
                Bll_User::appendPeople($userList);
                
                $rankStart = $userList[0]['rank'];
                $rankEnd = $userList[count($userList)-1]['rank'];
                for ($i = count($userList); $i < 6; $i++) {
                    $userList[$i]['uid'] = "??????さん";
                    $userList[$i]['rank'] = "?";
                    $userList[$i]['score'] = "????";
                    $userList[$i]['displayName'] = "????さん";
                    $userList[$i]['miniThumbnailUrl'] = Zend_Registry::get('static') . "/apps/afrac/img/default_pic.gif";
                }
            }
            else if ($direction == 'up') {
                $userList = $dalAfracUser->getUserList(max(1,$rankStart-6),6,$type,$fids,1);  //20091105 modify by zhaoxh
                Bll_User::appendPeople($userList);
                
                $rankStart = $userList[0]['rank'];
                $rankEnd = $userList[5]['rank'];
            }
        	
            /*
            set long name to with '..'
            for ($i = 0; $i < 6; $i++) {
                $userList[$i]['displayName'] = $this->_stringCut($userList[$i]['displayName']);
            }
            */
            
        	$response = array('info' => $userList, 'begin' => $rankStart,'end' => $rankEnd);
        	$response = Zend_Json::encode($response);
            echo $response;
        }
    }
    
    /**
     * change type
     *
     */
    public function changetypeAction() 
    {
        if ($this->_request->isPost()) {
            $type = $this->_request->getPost('type');
            $uid = $this->_user->getId();
            
            require_once 'Bll/User.php';
            require_once 'Dal/Afrac/User.php';
            $dalUser = Dal_Afrac_User::getDefaultInstance();
            
            //get friend list
            if ($type == 2) {
                require_once 'Bll/Friend.php';
                $fids = Bll_Friend::getFriendIds($uid);
                if ($fids == null) {
                    $fids = $uid;
                }
                else {
                    $fids = $fids . ',' . $uid;
                }
            }
            
            //get user count
            $userCnt = $dalUser->countUser($type,$fids);  
            
            //get user`s rank and score
            $userRankScore = $dalUser->getRankScore($uid,$type,$fids,1); //20091105 modify by zhaoxh
            
            //get user list data
            if ($userCnt < 6) {
                $userList = $dalUser->getUserList(1,6,$type,$fids,1);  //20091105 modify by zhaoxh 
                Bll_User::appendPeople($userList);
                
                $rankStart = 1;
                $rankEnd = $userList[count($userList)-1]['rank'];
                
                for ($i = count($userList); $i < 6; $i++) {
                    $userList[$i]['uid'] = "??????さん";
                    $userList[$i]['rank'] = "?";
                    $userList[$i]['score'] = "????";
                    $userList[$i]['displayName'] = "????さん";
                    $userList[$i]['miniThumbnailUrl'] = Zend_Registry::get('static') . "/apps/afrac/img/default_pic.gif";
                }
            }
            else { 
                if ($userCnt >= 6 && $userRankScore['rank']+3 <= $userCnt) {
                    $userList = $dalUser->getUserList(max($userRankScore['rank']-2,1),6,$type,$fids,1); //20091105 modify by zhaoxh
                }
                else if ($userCnt >= 6 && $userRankScore['rank']+3 > $userCnt) {
                    $userList = $dalUser->getUserList($userCnt-5,6,$type,$fids,1); //20091105 modify by zhaoxh
                } 
                Bll_User::appendPeople($userList);
                
                $rankStart = $userList[0]['rank'];
                $rankEnd = $userList[5]['rank'];
            }
            
            //set long name to with '..'
            for ($i = 0; $i < 6; $i++) {
                $userList[$i]['displayName'] = $this->_stringCut($userList[$i]['displayName']);
            }
            
        	$response = array('info' => $userList, 'begin' => $rankStart,'end' => $rankEnd,'userCnt' => $userCnt);
        	$response = Zend_Json::encode($response);
            echo $response;
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
     * update score
     *
     */
    public function updatescoreAction() 
    {
        if ($this->_request->isPost()) {
            $score = $this->_request->getPost('score');
            $secret = $this->_request->getPost('secret');
                   
            $uid = $this->_user->getId();            
            $key = 'aflacappli';
            
            if ( md5('aflacappli'.$uid.$score) == $secret){
                require_once 'Bll/Afrac/User.php';
                $bllUser = new Bll_Afrac_User();
                
                $response = $bllUser->updateScore($uid,$score);
            	echo $response;
            }
            else {
                echo 0;
            }
        }
    }
}
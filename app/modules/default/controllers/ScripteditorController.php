<?php

include_once CONFIG_DIR . DIRECTORY_SEPARATOR . 'scripteditor-config.php';

/**
 * script editor controller
 * 
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/05/20    Liz
 */
class ScripteditorController extends MyLib_Zend_Controller_Action_Default
{

    /**
     * index Action
     *
     */
    public function indexAction()
    {
        $this->_redirect($this->_baseUrl . '/scripteditor/toppage');
        
    }

    /**
     * toppage Action
     *
     */
    public function toppageAction()
    {
        $uid = $this->__getUserInfo($this->_user->getId());
        $this->view->uid = $uid;
        
        require_once 'Bll/Scripteditor/Toppage.php';
        $bllSterToppage = new Bll_Scripteditor_Toppage();
        //get new entry info
        $newEntry = $bllSterToppage->getNewEntry();
        $this->view->newEntry = $newEntry;
        
        $this->render();
    }

    /**
     * profile Action
     *
     */
    public function profileAction()
    {
        //is from user config
        if ( $this->_request->getParam('submit') ) {
            $this->view->isSubmit = $this->_request->getParam('submit');
        }
        
        //get user id
        if ( $this->_request->getParam('uid') && $this->_request->getParam('uid') != $this->_user->getId() ) {
            $uid = $this->_request->getParam('uid');
            $this->view->isOwner = 0;
            
            require_once 'Dal/Scripteditor/User.php';
            $dalSterUser = new Dal_Scripteditor_User();
            //check the user is in game
            $isIn = $dalSterUser->isInScripteditor($uid);
            
            if ( !$isIn ) {
                $uid = $this->_user->getId();
                $this->view->isOwner = 1;
            }
        }
        else {
            $uid = $this->_user->getId();
            $this->view->isOwner = 1;
        }
        
        require_once 'Dal/Scripteditor/User.php';
        $dalSterUser = new Dal_Scripteditor_User();
        
        //get user info
        $userInfo = $dalSterUser->getUserInfo($uid);
        Bll_User::appendPerson($userInfo, 'uid');
        
        if ( !$userInfo['nickname'] ) {
            $userInfo['nickname'] = $userInfo['displayName'];
        }
        if ( !$userInfo['pic'] ) {
            $userInfo['pic_s'] = $userInfo['thumbnailUrl'];
            $userInfo['pic'] = $userInfo['largeThumbnailUrl'];
            $userInfo['mixiPic'] = 1;
        }
        
        
        require_once 'Dal/Scripteditor/Job.php';
        $dalSterJob = new Dal_Scripteditor_Job();
        //get user job
        $userJob = $dalSterJob->getJob($userInfo['job']);
        
        require_once 'Bll/Scripteditor/Config.php';
        $bllSterConfig = new Bll_Scripteditor_Config();
        //get user feature info
        $featureResult = $bllSterConfig->getFeatureInfo($userInfo['features'], 5);
        
        $this->view->uid = $uid;
        $this->view->userInfo = $userInfo;
        $this->view->userJob = $userJob;
        $this->view->userFeature = $featureResult;
                
        require_once 'Bll/Scripteditor/Toppage.php';
        $bllSterToppage = new Bll_Scripteditor_Toppage();
        //get user entry info
        $userEntry = $bllSterToppage->getUserEntry($uid);
        $this->view->userEntry = $userEntry;
        
        require_once 'Dal/Scripteditor/Entry.php';
        $dalSterEntry = new Dal_Scripteditor_Entry();
        //get user follow info
        $followList = $dalSterEntry->getFollowList($uid);
        require_once 'Bll/User.php';
        Bll_User::appendPeople($followList, 'uid');
        
        if ( count($followList) > '1' ) {
            $followList = $this->__randArray($followList, 25);
        }
        
        $this->view->followList = $followList;
        
        $this->render();
    }
    
    /**
     * archives Action
     *
     */
    public function archivesAction()
    {
        $uid = $this->__getUserInfo($this->_user->getId());
        $this->view->uid = $uid;
        
        $language = $this->_request->getParam('lang', 1);
        $this->view->language = $language;
        
        //is from toppage page
        if ( $this->_request->getParam('type') ) {
            $type = $this->_request->getParam('type');
            $this->view->type = $type;
        }
        
        require_once 'Dal/Scripteditor/Entry.php';
        $dalSterEntry = new Dal_Scripteditor_Entry();
        
        if ( $type == '1' ) {
            $array = $dalSterEntry->getNewEntry($language, 1, 5);
            $count = $dalSterEntry->getEntryCount($language);
        }
        else {
            $array = $dalSterEntry->getUserEntry($uid, $language, 1, 5, 1);
            $count = $dalSterEntry->getUserEntryCount($uid, $language, 1);    
        }
        
        require_once 'Bll/User.php';
        Bll_User::appendPeople($array, 'uid');
        
        require_once 'Dal/Scripteditor/Language.php';
        $dalSterLanguage = new Dal_Scripteditor_Language();
        $langInfo = $dalSterLanguage->getLanguage($language);
        
        $this->view->archivesInfo = $array;
        $this->view->arrayCount = count($array);
        $this->view->archivesCount = $count;
        $this->view->langInfo = $langInfo;
        
        $this->render();
    }
    
    /**
     * editor Action
     *
     */
    public function editorAction()
    {
        $eid = $this->_request->getParam('eid');
        $entryInfo = null;
        if ($eid) {
            require_once 'Dal/Scripteditor/Entry.php';
            $dalSterEntry = new Dal_Scripteditor_Entry();
            //get entry info
            $entryInfo = $dalSterEntry->getEntryInfo($eid);
            require_once 'Bll/User.php';
            Bll_User::appendPerson($entryInfo, 'uid');
        }

        $this->view->entryInfo = $entryInfo;
        $this->view->isEdit = $entryInfo ? true : false;
        
        $uid = $this->__getUserInfo($this->_user->getId());
        $this->view->uid = $uid;
        
        $this->render();
    }

    /**
     * entry Action
     *
     */
    public function entryAction()
    {
        $eid = $this->_request->getParam('eid');
        
        require_once 'Dal/Scripteditor/Entry.php';
        $dalSterEntry = new Dal_Scripteditor_Entry();
        //get entry info
        $entryInfo = $dalSterEntry->getEntryInfo($eid);
            
        if ( !$entryInfo ) {
            $this->_redirect($this->_baseUrl . '/scripteditor/profile');
        }
        if ( $entryInfo['status'] == '0' ) {
            $this->_redirect($this->_baseUrl . '/scripteditor/editor/eid/' . $entryInfo['eid']);
        }
        require_once 'Bll/User.php';
        Bll_User::appendPerson($entryInfo, 'uid');
        
        //get entry follow list
        $entryFollowList = $dalSterEntry->getFollowEntryList($eid);
        Bll_User::appendPeople($entryFollowList, 'uid');
        
        $this->view->entryInfo = $entryInfo;
        $this->view->entryFollowList = $entryFollowList;
        $this->view->entryFollowCount = count($entryFollowList);
        
        //get array tag
        $entryTag = str_replace(' ', ',', $entryInfo['tag']);
        $arrTag = explode(',', $entryTag);
        $this->view->arrTag = $arrTag;
        
        //get user info
        $uid = $this->__getUserInfo($entryInfo['uid']);
        $this->view->uid = $uid;
        
        $showScript = '<script id="script_container_' . $eid . '" type="text/javascript" src="' . $this->_baseUrl . '/scripteditor/showiframe/eid/' . $eid . '?height=215&width=650"></script>';
        
        $this->view->showEntry = $showScript;
        
        $this->render();
    }

    /**
     * editor Action
     *
     */
    public function followAction()
    {
        $eid = $this->_request->getParam('eid');
        
        require_once 'Dal/Scripteditor/Entry.php';
        $dalSterEntry = new Dal_Scripteditor_Entry();
        //get entry info
        $entryInfo = $dalSterEntry->getEntryInfo($eid);
        require_once 'Bll/User.php';
        Bll_User::appendPerson($entryInfo, 'uid');
        
        
        $this->view->followId = $eid;
        $this->view->entryInfo = $entryInfo;
        $this->view->entryTime = date("y/m/d H:i");
        
        $uid = $this->__getUserInfo($this->_user->getId());
        $this->view->uid = $uid;
        
        $this->render();
    }

    /**
     * tag Action
     *
     */
    public function tagAction()
    {
        //get user info
        $uid = $this->__getUserInfo($this->_user->getId());
        $this->view->uid = $uid;
        
        //get tag list, "1" -- cache
        $tagList = Bll_Cache_Scripteditor::getTagList(1);
        
        //rand array
        if ( count($tagList) > 1 ) {
            $tagList = $this->__randArray($tagList, count($tagList));
        }
        $this->view->tagList = $tagList;
        
        $this->render();
    }
    
    /**
     * config Action
     *
     */
    public function configAction()
    {
        //get user info
        $uid = $this->__getUserInfo($this->_user->getId());
        $this->view->uid = $uid;
        
        require_once 'Dal/Scripteditor/Job.php';
        $dalSterJob = new Dal_Scripteditor_Job();
        //get job list
        $jobList = $dalSterJob->getJobList();
        $this->view->jobList = $jobList;
        
        require_once 'Dal/Scripteditor/Feature.php';
        $dalSterFeature = new Dal_Scripteditor_Feature();
        //get feature list info by type
        $featureList1 = $dalSterFeature->getFeatureList(1);
        $featureList2 = $dalSterFeature->getFeatureList(2);
        $this->view->featureList1 = $featureList1;
        $this->view->featureList2 = $featureList2;
        
        $this->view->jsonFeature1 = Zend_Json::encode($featureList1);
        $this->view->jsonFeature2 = Zend_Json::encode($featureList2);
        
        $this->render();
    }

    /**
     * delete pic Action
     *
     */
    public function deletepicAction()
    {
        $uid = $this->_user->getId();
        
        $userConfig = array('pic' => '',
                            'pic_s' => '');
        
        require_once 'Bll/Scripteditor/Config.php';
        $BllSterConfig = new Bll_Scripteditor_Config();
        
        //update user info
        $BllSterConfig->updateUserInfo($uid, $userConfig);
        
        $this->_redirect($this->_baseUrl . '/scripteditor/config');
    }
    
    /**
     * search Action
     *
     */
    public function searchAction()
    {
        $uid = $this->__getUserInfo($this->_user->getId());
        $this->view->uid = $uid;
        
        if ( $this->_request->getParam('langType') || $this->_request->getParam('search') ) {
            $search = $this->_request->getParam('search');
            $langType = $this->_request->getParam('langType');
            $this->view->search = $search;
            $this->view->langType = $langType;
            $this->view->isOnFocus = 1;
        }
        
        $this->render();
    }

    /**
     * help Action
     *
     */
    public function helpAction()
    {
        $uid = $this->__getUserInfo($this->_user->getId());
        $this->view->uid = $uid;
        
        $this->render();
    }

    /**
     * show entry Action
     *
     */
    public function showentryAction()
    {
        $eid = $this->_request->getParam('eid');
        $height = $this->_request->getParam('height', 150);
        $width = $this->_request->getParam('width', 400);

        require_once 'Dal/Scripteditor/Entry.php';
        $dalSterEntry = new Dal_Scripteditor_Entry();
        
        $entry = $dalSterEntry->getEntryInfo($eid);
        
        if ( !$entry ) {
            $this->_forward('notfound', 'error', 'default');
            return;
        }
        
        require_once 'Bll/User.php';
        Bll_User::appendPerson($entry, 'uid');
        
        $this->view->entry = $entry;
        $this->view->height = $height;
        $this->view->width = $width;
        
        $this->render();
    }
    
    /**
     * show entry Action
     *
     */
    public function showiframeAction()
    {
       $eid = $this->_request->getParam('eid');
       $height = $this->_request->getParam('height', 215);
       $width = $this->_request->getParam('width', 650);
       
       //create div
       echo 'document.write("<div id=\'iframe_container_' . $eid . '\'></div>");';
       
       //echo js code
       echo 'var frameElem = document.createElement("iframe");
             frameElem.setAttribute("id", "content_iframe");
             frameElem.setAttribute("name", "content_iframe");
             frameElem.frameBorder = 0;
             frameElem.setAttribute("scrolling", "no");
             frameElem.setAttribute("align", "middle");
             frameElem.style.height = "' . $height . 'px";
             frameElem.style.width = "' . $width . 'px";
             frameElem.setAttribute("src", "' . $this->_baseUrl .'/scripteditor/showentry/eid/' . $eid . '");
             
             var frameContainer = document.getElementById("iframe_container_' . $eid . '");
             frameContainer.innerHTML = "";
             frameContainer.appendChild(frameElem);';
             
       exit;
    }
    
    public function runAction()
    {
        $this->view->nickname = $this->_request->getParam('nickname', '');
        $this->view->title = $this->_request->getParam('title', '');
        $language = $this->_request->getParam('language');
        $content = $this->_request->getParam('content');
        $this->view->time = $this->_request->getParam('time', '');
        
        require_once 'Bll/Scripteditor/Run.php';
        require_once CONFIG_DIR . DIRECTORY_SEPARATOR . 'scripteditor-config.php';
        
        //php
        if ($language == 1) {
            $file = Bll_Scripteditor_Run::createPHPFile($content);
            $this->view->language = $language;
            $this->view->file = $file;
            require_once 'Bll/Secret.php';
            $this->view->sig = Bll_Secret::getSecretResult($file, $language);
            $html = 'running.html';
        }
        else {
            $html = Bll_Scripteditor_Run::createHTMLFile($content);
        }
        
        $this->view->result = CODE_RUN_HOST . '/' . $html;
        
        $this->render();
    }
    
    /**
     * deipatch
     *
     */
    function preDispatch()
    {        
        $this->_baseUrl = Zend_Registry::get('host');
        
        $uid = $this->_user->getId();
        require_once 'Dal/Scripteditor/User.php';
        $dalSterUser = new Dal_Scripteditor_User();

        $isIn = $dalSterUser->isInScripteditor($uid);
        
        //check is in user
        if ( !$isIn ) {
            //new a user
            $newUser = array('uid' => $uid);
            $dalSterUser->insertScripteditorUser($newUser);
        }
        
        $this->view->csstype = 'scripteditor';
    }

    /**
     * get user info
     *
     * @param string $methodName
     * @param array $args
     * @return void
     */
    function __getUserInfo($uid)
    {
        //get user id
        if ( $this->_request->getParam('uid') && $this->_request->getParam('uid') != $this->_user->getId() ) {
            $uid = $this->_request->getParam('uid');
            $this->view->isOwner = 0;
            
            require_once 'Dal/Scripteditor/User.php';
            $dalSterUser = new Dal_Scripteditor_User();
            //check the user is in game
            $isIn = $dalSterUser->isInScripteditor($uid);
            
            if ( !$isIn ) {
                $uid = $this->_user->getId();
                $this->view->isOwner = 1;
            }
        }
        else {
            if ( $uid == $this->_user->getId() ) {
                $this->view->isOwner = 1;
            }
        }
        
        require_once 'Dal/Scripteditor/User.php';
        $dalSterUser = new Dal_Scripteditor_User();
        
        //get user info
        $userInfo = $dalSterUser->getUserInfo($uid);
        Bll_User::appendPerson($userInfo, 'uid');
        
        if ( !$userInfo['nickname'] ) {
            $userInfo['nickname'] = $userInfo['displayName'];
        }
        if ( !$userInfo['pic'] ) {
            $userInfo['pic_s'] = $userInfo['thumbnailUrl'];
            $userInfo['pic'] = $userInfo['largeThumbnailUrl'];
            $userInfo['mixiPic'] = 1;
        }
        
        require_once 'Dal/Scripteditor/Job.php';
        $dalSterJob = new Dal_Scripteditor_Job();
        //get user job
        $userJob = $dalSterJob->getJob($userInfo['job']);
        
        require_once 'Bll/Scripteditor/Config.php';
        $bllSterConfig = new Bll_Scripteditor_Config();
        //get user feature info
        $featureResult = $bllSterConfig->getFeatureInfo($userInfo['features'], 8);
        
        $this->view->uid = $uid;
        $this->view->userInfo = $userInfo;
        $this->view->userJob = $userJob;
        $this->view->userFeature = $featureResult;
        
        return $uid;
    }
    
    /**
     * rand array
     * 
     * @return array
     */
    function __randArray($arrays, $num="") {
        $num = empty($num) ? count($arrays) : $num;
        
        $num = $num > count($arrays) ? count($arrays) : $num;
        
        $rand_array = array_rand($arrays, $num);
        $len = count($rand_array);
        for ($i=0; $i<$len; $i++) {
            {
                $new_array[$i] = $arrays[$rand_array[$i]];
            }
        }
        return $new_array;
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

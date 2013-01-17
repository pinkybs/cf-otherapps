<?php
/** @see Zend_Json */
require_once 'Zend/Json.php';
/** @see MyLib_Zend_Controller_Action_Ajax */
require_once 'MyLib/Zend/Controller/Action/Ajax.php';

/**
 * Scripteditor Ajax Controllers
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/05/20    Liz
 */
class Ajax_ScripteditorController extends MyLib_Zend_Controller_Action_Ajax
{

    /**
     * get user entry archives
     * 
     */
    public function getarchivesAction() {
        if ($this->_request->isPost()) {
            $pageIndex = (int)$this->_request->getPost('pageIndex', 1);
            $language = $this->_request->getPost('language', 1);
            $uid = $this->_request->getPost('uid');
            $type = $this->_request->getPost('type');
            
            
            require_once 'Dal/Scripteditor/Entry.php';
            $dalSterEntry = new Dal_Scripteditor_Entry();
            
            if ( $type == '1' ) {
                $array = $dalSterEntry->getNewEntry($language, $pageIndex, 5);
                $count = $dalSterEntry->getEntryCount($language);
            }
            else {
                $array = $dalSterEntry->getUserEntry($uid, $language, $pageIndex, 5, 1);
                $count = $dalSterEntry->getUserEntryCount($uid, $language, 1);    
            }
            require_once 'Bll/User.php';
            Bll_User::appendPeople($array, 'uid');
            
            require_once 'Dal/Scripteditor/Language.php';
            $dalSterLanguage = new Dal_Scripteditor_Language();
            $langInfo = $dalSterLanguage->getLanguage($language);
            
            $response = array('info' => $array, 'count' => $count, 'langInfo' => $langInfo);
            $response = Zend_Json::encode($response);

            echo $response;
        }
    }

    /**
     * new entry
     * 
     */
    public function newentryAction() {
        if ($this->_request->isPost()) {
            $title = $this->_request->getPost('title');
            $tag = $this->_request->getPost('tag');
            $language = $this->_request->getPost('language');
            $content = $this->_request->getPost('content');
            $followId = $this->_request->getPost('followId');
            $eid = $this->_request->getPost('eid');
            
            $uid = $this->_user->getId();
            
            if ( !$title || !$language || !$content ) {
                return ;
            }
            
            $entry = array('uid' => $uid,
                           'title' => $title,
                           'tag' => $tag,
                           'language' => $language,
                           'content' => $content,
                           'create_time' => date('Y-m-d H:i:s'),
                           'follow_id' => $followId,
                           'status' => '1');
            
            require_once 'Bll/Scripteditor/Entry.php';
            $bllSterEntry = new Bll_Scripteditor_Entry();
            
            $entryId = $bllSterEntry->newEntry($eid, $entry);
            
            $response = array('entryId' => $entryId);
            $response = Zend_Json::encode($response);

            echo $response;
        }
    }

    /**
     * save entry
     * 
     */
    public function saveentryAction() {
        if ($this->_request->isPost()) {
            $title = $this->_request->getPost('title');
            $tag = $this->_request->getPost('tag');
            $language = $this->_request->getPost('language');
            $content = $this->_request->getPost('content');
            $followId = $this->_request->getPost('followId');
            $eid = $this->_request->getPost('eid');
            
            $uid = $this->_user->getId();
            
            $entry = array('uid' => $uid,
                           'title' => $title,
                           'tag' => $tag,
                           'language' => $language,
                           'content' => $content,
                           'create_time' => date('Y-m-d H:i:s'),
                           'status' => '0',
                           'follow_id' => $followId);
            
            require_once 'Bll/Scripteditor/Entry.php';
            $bllSterEntry = new Bll_Scripteditor_Entry();
            
            $entryId = $bllSterEntry->newEntry($eid, $entry);
            
            $response = array('entryId' => $entryId);
            $response = Zend_Json::encode($response);

            echo $response;
        }
    }
    
    public function runAction()
    {
        if ($this->_request->isPost()) {
            $language = (int)$this->_request->getPost('language', 0);
            $file = $this->_request->getPost('file');
            $sig = $this->_request->getPost('sig');
            
            //debug_log($language . ' ' . $file . ' ' . $sig);
            
            require_once 'Bll/Secret.php';
            if (!Bll_Secret::isTrueSecret($file, $sig, $language)) {
                echo 'false';
                exit;
            }
            
            require_once 'Bll/Scripteditor/Run.php';
            
            //php
            if ($language == 1) {
                $htmlname = CODE_RUN_HOST . '/' . Bll_Scripteditor_Run::phpToHTML(TEMP_PHP_DIR . DIRECTORY_SEPARATOR . $file);
                echo $htmlname;
                exit;
            }
            
            echo 'false';
            exit;
        }
    }

    /**
     * config
     * 
     */
    public function configAction() {
        if ($this->_request->isPost()) {
            $nickname = $this->_request->getPost('txtNickname');
            $job = $this->_request->getPost('txtJob');
            $level = $this->_request->getPost('txtLevel');
            $introduce = $this->_request->getPost('txtIntroduce');
            $blogurl = $this->_request->getPost('blogUrl');
            if ($blogurl == 'http://') {
                $blogurl = '';
            }
            if ($blogurl != '') {
                require_once 'MyLib/Network.php';
                $valid = MyLib_Network::validateUrl($blogurl);
                if (!$valid) {
                    $blogurl = '';
                }
            }
            
            $language = $this->_request->getPost('defaultLang');
            $mixiprof = $this->_request->getPost('mixiProf');
            $feature = $this->_request->getPost('txtFeature');
            
            $uid = $this->_user->getId();
            
            $userConfig = array('nickname' => $nickname,
                                'job' => $job,
                                'level' => $level,
                                'introduce' => $introduce,
                                'blogUrl' => $blogurl,
                                'default_language' => $language,
                                'public_type' => $mixiprof,
                                'features' => $feature);
            
            require_once 'Bll/Scripteditor/Config.php';
            $BllSterConfig = new Bll_Scripteditor_Config();
            
            //update user info
            $result = $BllSterConfig->updateUserInfo($uid, $userConfig, 'upPhoto');
            
            $message = $result ? 'true' : 'false';
            
            echo $message;
        }
    }
    
    /**
     * search
     * 
     */
    public function searchAction() {
        if ($this->_request->isPost()) {
            $search = $this->_request->getPost('search');
            $langType = $this->_request->getPost('langType');
            $pageIndex = $this->_request->getPost('page', 1);
            $pageSize = $this->_request->getPost('pageSize', 5);
            
            //get job content by job id
            require_once 'Bll/Scripteditor/Search.php';
            $bllSterSearch = new Bll_Scripteditor_Search();
            $result = $bllSterSearch->searchEntry($search, $langType, $pageIndex, $pageSize);
            
            $response = Zend_Json::encode($result);

            echo $response;
        }
    }
    
    /**
     * get job content by job id
     * 
     */
    public function getjobcontentAction(){
        if ($this->_request->isPost()) {
            $id = $this->_request->getPost('id');
            
            //get job content by job id
            require_once 'Dal/Scripteditor/Job.php';
            $dalSterJob = new Dal_Scripteditor_Job();
            $jobContent = $dalSterJob->getJob($id);
            
            $response = array('content' => $jobContent);
            $response = Zend_Json::encode($response);

            echo $response;
        }
    }

    /**
     * download entry
     * 
     */
    public function downloadAction(){
        $id = $this->_request->getParam('id');

        require_once 'Dal/Scripteditor/Entry.php';
        $dalSterEntry = new Dal_Scripteditor_Entry();
        //get entry info
        $entry = $dalSterEntry->getEntryInfo($id);
        require_once 'Bll/User.php';
        Bll_User::appendPerson($entry, 'uid');
            
        if ( !$entry ) {
            return;
        }
        
        require_once 'Dal/Scripteditor/Language.php';
        $dalSterLanguage = new Dal_Scripteditor_Language();
        //get language info
        $lang = $dalSterLanguage->getLanguage($entry['language']);
        
        $name = date('YmdHis', strtotime($entry['create_time']));
        
        $filename = $name . '.' . strtolower($lang['language_name']);
        
        header("Content-Encoding: none");
        header("Content-type: application/octet-stream");
        header("Cache-Control: private");
        header("Accept-Ranges: bytes");
        header("Accept-Length: ".filesize($filename));
        header('Content-Disposition: attachment; filename="'.$filename.'"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        echo $entry['content'];
        exit();
        
    }
    
}


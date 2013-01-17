<?php

/** @see MyLib_Zend_Controller_Action_Mobile.php */
require_once 'MyLib/Zend/Controller/Action/Mobile.php';
require_once 'Zend/Http/Client.php';

/**
 * Mobile School Controller(modules/mobile/controllers/SchoolController.php)
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/11/09
 */
class Mobile_SchoolController extends MyLib_Zend_Controller_Action_Mobile
{
    protected $_pageSize = 10;

    //array _schoolUser
    protected $_schoolUser;


    /**
     * initialize object
     * override
     * @return void
     */
    public function init()
    {
        parent::init();
    }

    /**
     * deipatch
     *
     */
    function preDispatch()
    {
        $uid = $this->_user->getId();
        $userName = $this->_user->getDisplayName();
        $this->view->app_name = 'school';
        $this->view->uid = $uid;
        $this->view->userName = $userName;

        //school user info
        require_once 'Mbll/School/User.php';
        $mbllUser = new Mbll_School_User();
        require_once 'Mdal/School/User.php';
        $mdalUser = Mdal_School_User::getDefaultInstance();
        $rowSchoolUser = $mdalUser->getUser($uid);
        if (empty($rowSchoolUser) || 1 == $rowSchoolUser['status']) {
            $result = $mbllUser->newSchoolUser($uid, $this->_APP_ID);
            if ($result) {
                $rowSchoolUser = $mdalUser->getUser($uid);
            }
        }
        if (empty($rowSchoolUser)) {
            return $this->_forward('notfound', 'error');
        }

        require_once 'Bll/User.php';
        Bll_User::appendPerson($rowSchoolUser, 'uid');

        //every day login in check
        $todayTime = strtotime(date("Y-m-d"));
        $lastLoginTime = $rowSchoolUser['last_login_time'];
        //is today first login
        if (empty($lastLoginTime) || $lastLoginTime < $todayTime) {
            $mbllUser->updateLoginCount($uid);
            $rowSchoolUser['login_day_count'] = $rowSchoolUser['login_day_count'] + 1;
        }

        //redirect logic
        $aryAction = array('help', 'error', 'callbackschool');
        if (!in_array($this->_request->getActionName(), $aryAction)) {
            //学校APIチェック
            require_once 'Mbll/School/SchoolsApiCache.php';
            $intRst = Mbll_School_SchoolsApiCache::checkSchool($uid, $this->_APP_ID, $rowSchoolUser);
            if (1 == $intRst) {
                //学校情報登録なし
                $this->_redirect($this->_baseUrl . '/mobile/school/error?CF_error=nosignup');
                return;
            }
            else if (2 == $intRst) {
                //複数の学校情報を取得
                $this->_redirect($this->_baseUrl . '/mobile/school/error?CF_error=nosignuptwo');
                return;
            }
            else if (3 == $intRst && 'schedulereset' != $this->_request->getActionName()) {
                //学校情報が不一致
                $this->_redirect($this->_baseUrl . '/mobile/school/error?CF_error=profilechange');
                return;
            }
            else if (4 == $intRst) {
                //高校生以下
                $this->_redirect($this->_baseUrl . '/mobile/school/error?CF_error=nopermission');
                return;
            }

            //2回目以降の訪問 and 公開設定を一度も変更していない
            if (empty($rowSchoolUser['is_privacy_showed']) && $rowSchoolUser['login_day_count']>=2 && empty($rowSchoolUser['last_mode_change_date'])) {
                if ('privacy' != $this->_request->getActionName()
                 && 'sflash' != $this->_request->getActionName()) {
                    $this->_redirect($this->_baseUrl . '/mobile/school/privacy');
                    return;
                }
            }
            //3回目以降の訪問 and あしあと帳アプリ未登録
            if (empty($rowSchoolUser['is_ashiato_showed']) && $rowSchoolUser['login_day_count'] >=3 ) {
                if ('ashiato' != $this->_request->getActionName()
                 && 'sflash' != $this->_request->getActionName()) {
                    require_once 'Mbll/School/RemoteServiceApi.php';
                    $mbllRemoteApi = new Mbll_School_RemoteServiceApi();
                    $isAshiatoUser = $mbllRemoteApi->isAshiatoUser($uid);
                    if (!$isAshiatoUser) {
                        $this->_redirect($this->_baseUrl . '/mobile/school/ashiato');
                        return;
                    }
                }
            }
        }

        $this->_schoolUser = $rowSchoolUser;
        $this->view->mineInfo = $rowSchoolUser;
        $this->view->ua = Zend_Registry::get('ua');
        $this->view->rand = time();
        $this->view->boardAppId = BOARD_APP_ID;
    }

    /**
     * index action -- welcome page
     *
     */
    public function indexAction()
    {
        $uid = $this->_user->getId();
        $this->_redirect($this->_baseUrl . '/mobile/school/home');
        return;
        //$this->render();
    }

	/**
     * mixi school api call back action
     *
     */
    public function callbackschoolAction()
    {
        //$uid = $this->getParam('opensocial_owner_id');
        //$app_id = $this->getParam('opensocial_app_id');
        $uid = $this->_user->getId();

        $schoolToken = $this->getParam('school_token');
        $schoolDivision = $this->getParam('school_division');
        $schoolId = $this->getParam('school_id');
        $forward = $this->getParam('forward');

        if (!empty($schoolToken) && !empty($schoolDivision)) {
            $rowSchoolUser = $this->_schoolUser;
            if ($rowSchoolUser['school_code'] != $schoolToken) {
                require_once 'Mbll/School/Timepart.php';
                $mbllTimepart = new Mbll_School_Timepart();
                $rst = $mbllTimepart->scheduleReset($uid, $schoolToken, $schoolDivision);
                if ($rst) {
                    //clear api cache
                    require_once 'Mbll/School/SchoolsApiCache.php';
                    Mbll_School_SchoolsApiCache::clearCache($uid);
                }
            }
        }

        if ($forward) {
        	require_once 'Mbll/School/SchoolsApiCache.php';
            $intRst = Mbll_School_SchoolsApiCache::checkSchool($uid, $this->_APP_ID, $this->_schoolUser);
        	if (4 == $intRst) {
                //高校生以下
                $this->_redirect($this->_baseUrl . '/mobile/school/error?CF_error=nopermission');
                return;
            }
            $this->_redirect($forward);
        }
        else {
            $this->_redirect($this->_baseUrl . '/mobile/school/home');
        }
        return;
    }

	/**
     * invite finish action
     *
     */
    public function invitefinishAction()
    {
    	$uid = $this->_user->getId();
    	$this->render();
    }

    /**
     * school flash action
     *
     */
    public function sflashAction()
    {
        $uid = $this->_user->getId();
        $profileUid = $this->getParam('CF_uid');
        $selWday = $this->getParam('CF_wday');

        if ($uid != $profileUid) {
            //update visit foot logic
            require_once 'Mbll/School/User.php';
            $mbllUser = new Mbll_School_User();
            $mbllUser->updateVisitFoot($uid, $profileUid);
        }

        // get swf
        $mixiUrl = $this->_mixiMobileUrl . $this->_APP_ID . ((Zend_Registry::get('ua') == 1) ? '/?guid=ON&amp;url=' : '/?url=');
        require_once 'Mbll/School/FlashCache.php';
        $swf = Mbll_School_FlashCache::getNewFlash($this->_schoolUser, $profileUid, $mixiUrl, $selWday);

        //$this->render();
        ob_end_clean();
        ob_start();
        header("Accept-Ranges: bytes");
        header("Cache-Control: no-cache, must-revalidate");
        header("Content-Type: application/x-shockwave-flash");
        header("Content-Encoding: gzip");
        echo $swf;
        exit(0);
    }

 	/**
     * school flash call back action
     *
     */
    public function flashfwdAction()
    {
        $uid = $this->_user->getId();
        $parameter = $this->getParam('CF_fwd');
        if (empty($parameter) || strlen($parameter) < 4) {
            $this->_redirect($this->_baseUrl . '/mobile/school/home');
            return;
        }
        $aryWeekDay = array(1 => 'mon', 2 => 'tue', 3 => 'wed', 4 => 'thu', 5 => 'fri', 6 => 'sat');
        $strwday = substr($parameter, 0, 3);
        $wday = array_search($strwday, $aryWeekDay);
        $part = (int)(substr($parameter, 3));
        if (empty($wday) || empty($part)) {
            $this->_redirect($this->_baseUrl . '/mobile/school/home');
            return;
        }

        require_once 'Mdal/School/Timepart.php';
        $mdalTimepart = Mdal_School_Timepart::getDefaultInstance();
        $rowNowClass = $mdalTimepart->getTimepartScheduleByPk($uid, $wday, $part);
        if (!empty($rowNowClass)) {
            $this->_redirect($this->_baseUrl . '/mobile/school/class?CF_cid=' . $rowNowClass['cid']);
        }
        else {
            $this->_redirect($this->_baseUrl . '/mobile/school/classnameadd?CF_wday=' . $wday . '&CF_part=' . $part);
        }
        return;
    }

    /**
     * home action
     *
     */
    public function homeAction()
    {
        $uid = $this->_user->getId();
        $rowSchoolUser = $this->_schoolUser;

        require_once 'Bll/Friend.php';
        require_once 'Bll/User.php';
        require_once 'Mdal/School/Message.php';
        require_once 'Mdal/School/User.php';
        require_once 'Mdal/School/VisitFoot.php';
        require_once 'Mdal/School/Timepart.php';
        require_once 'Mdal/School/Class.php';
        require_once 'Mdal/School/Topic.php';

        //get school user info
        $mdalUser = Mdal_School_User::getDefaultInstance();
        $mdalTopic = Mdal_School_Topic::getDefaultInstance();
        $rowSchoolUser['my_topic_count'] = $mdalTopic->getMyNewestTopicCount($uid);//$mdalTopic->getMyNewestTopicGroupByCidCount($uid);
        $this->view->mineInfo = $rowSchoolUser;
        $aryFids = Bll_Friend::getFriends($uid);

        //message info
        $mdalMessage = Mdal_School_Message::getDefaultInstance();
        $lstMessage = $mdalMessage->listMessage($uid, 1, 100);
        $this->view->listMessage = $lstMessage;

        //schedule info
        $now = getdate();
        $this->view->scheduleTitle = $this->_getScheduleName($now);
        $mdalTimepart = Mdal_School_Timepart::getDefaultInstance();
        $mdalClass = Mdal_School_Class::getDefaultInstance();
        $lstTimepart = $mdalTimepart->listUserTimepartUsed($uid);
        foreach ($lstTimepart as $key=>$timeData) {
            $strBeginTime = $timeData['start_h'] . ':' . $timeData['start_m'];
            $lstTimepart[$key]['end_h'] = strftime('%H', strtotime($strBeginTime) + $timeData['part_minutes'] * 60);
            $lstTimepart[$key]['end_m'] = strftime('%M', strtotime($strBeginTime) + $timeData['part_minutes'] * 60);

            $lstTimepart[$key]['c_name'] = '未登録';
            $lstTimepart[$key]['cid'] = 0;
            $rowTimepartClass = $mdalTimepart->getTimepartScheduleByPk($uid, $now['wday'], $timeData['part']);
            if (!empty($rowTimepartClass)) {
                $rowClass = $mdalClass->getClassInfo($rowTimepartClass['cid']);
                $lstTimepart[$key]['c_name'] =  empty($rowClass) ? '未登録' : $rowClass['name'];
                $lstTimepart[$key]['member_count'] =  empty($rowClass) ? 0 : $rowClass['member_count'];
                $lstTimepart[$key]['cid'] = empty($rowClass) ? 0 : $rowClass['cid'];
                //get class forecast status
                $lstTimepart[$key]['c_forecast'] = empty($rowClass) ? 0 : $this->_getClassForecastStatus($uid, $rowClass['cid'], $aryFids);
                //has new emoji flag
                $lstTimepart[$key]['is_new'] = empty($rowClass) ? 0 : ((time() - (int)$rowClass['last_new_update_time'] <= 60*60*72)? 1 : 0);
            }
        }
        $this->view->mineSchedule = $lstTimepart;
        $this->view->hasTimePartS = $mdalTimepart->hasTimepartSchedule($uid);
        $this->view->nextWday = ($now['wday'] + 1) > 6 ? 1 : ($now['wday'] + 1);
        $this->view->prevWday = ($now['wday'] - 1) < 1 ? 6 : ($now['wday'] - 1);
        $this->view->wday = $now['wday'];

        //my friend's list
        $lstFriend = null;
        $cntFriend = 0;
        if (!empty($aryFids)) {
            $lstFriend = $mdalUser->listSchoolFriendIds($rowSchoolUser['school_code'], $uid, $aryFids, 1, 3, 'rand()');
            $cntFriend = $mdalUser->getSchoolFriendIdsCount($rowSchoolUser['school_code'], $uid, $aryFids);
            Bll_User::appendPeople($lstFriend, 'uid');

            //get currrent day parttime's class
            foreach ($lstFriend as $key=>$fdata) {
                $lstFriend[$key]['class_name'] = $this->_getCurrentTimepartClassNameByUid($fdata['uid']);
            }
        }
        $this->view->cntFriend = $cntFriend;
        $this->view->listFriend = $lstFriend;

        //newest class topic list
        require_once 'Mdal/School/AssistNewestTopic.php';
        $mdalAssistTopic = Mdal_School_AssistNewestTopic::getDefaultInstance();
        $lstTopic = null;
        $cntTopic = 0;
        $aryJoinedCids = $mdalClass->listJoinedClassIds($uid);
        $cids = array();
        if (!empty($aryJoinedCids)) {
            foreach ($aryJoinedCids as $cdata) {
            	$cids[] = $cdata['cid'];
            }
            $lstTopic = $mdalTopic->listNewestTopic($cids, 1, 3);
            $cntTopic = $mdalTopic->getNewestTopicCount($cids);
            foreach ($lstTopic as $key=>$tdata) {
            	$rowClass = $mdalClass->getClassInfo($tdata['cid']);
            	$lstTopic[$key]['c_name'] = empty($rowClass) ? '--' : $rowClass['name'];
            	$lstTopic[$key]['t_name'] = $tdata['title'];
            	$lstTopic[$key]['t_comment_count'] = $tdata['comment_count'];
            	$aryTime = getdate($tdata['create_time']);
            	$lstTopic[$key]['format_time'] = $aryTime['mon'] . '/' . $aryTime['mday'];
            	if ($now['mon'] == $aryTime['mon'] && $now['mday'] == $aryTime['mday']) {
            	    $lstTopic[$key]['format_time'] = $aryTime['hours'] . ':' . (strlen($aryTime['minutes'])<2 ? ('0' . $aryTime['minutes']) : $aryTime['minutes']);
            	}
            }
        }
        /*
        if (!empty($aryJoinedCids)) {
            foreach ($aryJoinedCids as $cdata) {
            	$cids[] = $cdata['cid'];
            }
            $lstTopic = $mdalAssistTopic->listNewestTopic($cids, 1, 3);
            $cntTopic = $mdalAssistTopic->getNewestTopicCount($cids);
            foreach ($lstTopic as $key=>$tdata) {
            	$rowClass = $mdalClass->getClassInfo($tdata['cid']);
            	$rowTopic = $mdalTopic->getClassTopic($tdata['tid']);
            	$lstTopic[$key]['c_name'] = empty($rowClass) ? '--' : $rowClass['name'];
            	$lstTopic[$key]['t_name'] = empty($rowTopic) ? '--' : $rowTopic['title'];
            	$lstTopic[$key]['t_comment_count'] = empty($rowTopic) ? 0 : $rowTopic['comment_count'];
            	$aryTime = getdate($lstTopic[$key]['update_time']);
            	$lstTopic[$key]['format_time'] = $aryTime['mon'] . '/' . $aryTime['mday'];
            	if ($now['mon'] == $aryTime['mon'] && $now['mday'] == $aryTime['mday']) {
            	    $lstTopic[$key]['format_time'] = $aryTime['hours'] . ':' . (strlen($aryTime['minutes'])<2 ? ('0' . $aryTime['minutes']) : $aryTime['minutes']);
            	}
            }
        }
		*/
        $this->view->cntTopic = $cntTopic;
        $this->view->listTopic = $lstTopic;

        //question list
        $lstEnquiry = null;
        $cntEnquiry = 0;
        require_once 'Mdal/School/Enquiry.php';
        $mdalEnquiry = Mdal_School_Enquiry::getDefaultInstance();
        $lstEnquiry = $mdalEnquiry->getLstEnquiryCategoryById('', 1, 3, 'update_time DESC');
        $cntEnquiry = $mdalEnquiry->getCntEnquiryCategoryById('');
        if (!empty($lstEnquiry) && count($lstEnquiry) > 0) {
            foreach ($lstEnquiry as $key => $edata) {
                $aryTime = getdate($edata['update_time']);
                $lstEnquiry[$key]['format_time'] = $aryTime['mon'] . '/' . $aryTime['mday'];
                if ($now['mon'] == $aryTime['mon'] && $now['mday'] == $aryTime['mday']) {
            	    $lstEnquiry[$key]['format_time'] = $aryTime['hours'] . ':' . (strlen($aryTime['minutes'])<2 ? ('0' . $aryTime['minutes']) : $aryTime['minutes']);
            	}
            }
        }
        $this->view->cntEnquiry = $cntEnquiry;
        $this->view->listEnquiry = $lstEnquiry;
        $this->view->cntMyEnquiry = $mdalEnquiry->getMyNewCntEnquiryById($uid);
        $qidLst = $mdalEnquiry->getLstQidById($uid);
        if ($qidLst) {
            $randQidAry = array_rand($qidLst, 1);
            $lstMessage[] = array('type' => 99, 'randQid' => $qidLst[$randQidAry]['qid']);
            $this->view->listMessage = $lstMessage;
        }

        //visit foot list
        $mdalVisitFoot = Mdal_School_VisitFoot::getDefaultInstance();
        $cntFootAccess = $mdalVisitFoot->getVisitFootCountAll($uid);
        $lstFoot = $mdalVisitFoot->listVisitFoot($uid, 1, 3);
        if (!empty($lstFoot) && count($lstFoot) > 0) {
            foreach ($lstFoot as $key => $pdata) {
                $lstFoot[$key]['is_friend'] = Bll_Friend::isFriend($uid, $pdata['uid']) ? '1' : '0';
            }
            Bll_User::appendPeople($lstFoot, 'uid');
        }
        $this->view->cntVisitFootAccess = $cntFootAccess;
        $this->view->listVisitFoot = $lstFoot;

        //ashiato list
        require_once 'Mbll/School/RemoteServiceApi.php';
        $mbllRemoteApi = new Mbll_School_RemoteServiceApi();
        $isAshiatoUser = $mbllRemoteApi->isAshiatoUser($uid);
        $lstAshiato = $mbllRemoteApi->listAshiato($uid, 3);
        $cntAshiato = $mbllRemoteApi->getAshiatoCount($uid);
        if (!empty($lstAshiato) && count($lstAshiato) > 0) {
            foreach ($lstAshiato as $key=>$adata) {
                $aryTime = getdate(strtotime($adata['create_time']));
                $lstAshiato[$key]['pic_url'] = SCHOOL_REMOTE_SERVER_PHOTO_DIR . $adata['mobile_pic_url'];
                $lstAshiato[$key]['format_time'] = $aryTime['mon'] . '/' . $aryTime['mday'];
                if ($now['mon'] == $aryTime['mon'] && $now['mday'] == $aryTime['mday']) {
            	    $lstAshiato[$key]['format_time'] = $aryTime['hours'] . ':' . (strlen($aryTime['minutes'])<2 ? ('0' . $aryTime['minutes']) : $aryTime['minutes']);
            	}
            }
            Bll_User::appendPeople($lstAshiato, 'comment_uid');
        }
        $this->view->listAshiato = $lstAshiato;
        $this->view->cntAshiato = $cntAshiato;
        $this->view->isAshiatoUser = $isAshiatoUser;

        $this->render();
    }

    /**
     * profile action
     *
     */
    public function profileAction()
    {
        $uid = $this->_user->getId();
        $profileUid = $this->getParam('CF_uid');
        if (empty($profileUid) || $uid == $profileUid) {
            $this->_redirect($this->_baseUrl . '/mobile/school/home');
            return;
        }

        //update visit foot logic
        require_once 'Mbll/School/User.php';
        $mbllUser = new Mbll_School_User();
        $mbllUser->updateVisitFoot($uid, $profileUid);

        $rowMyinfo = $this->_schoolUser;
        require_once 'Bll/Friend.php';
        require_once 'Bll/User.php';
        require_once 'Mdal/School/User.php';
        require_once 'Mdal/School/Timepart.php';
        require_once 'Mdal/School/Class.php';
        //profile info
        $mdalUser = Mdal_School_User::getDefaultInstance();
        $rowSchoolUser = $mdalUser->getUser($profileUid);
        if (empty($rowSchoolUser)) {
            $this->_forward('notfound', 'error');
            return;
        }
        Bll_User::appendPerson($rowSchoolUser, 'uid');
        $this->view->profileInfo = $rowSchoolUser;

        //schedule info
        $now = getdate();
        $this->view->scheduleTitle = $this->_getScheduleName($now);
        $mdalTimepart = Mdal_School_Timepart::getDefaultInstance();
        $lstTimepart = null;
        //public type
        $isAllowView = false;
        $this->view->isSameSchool = ($rowSchoolUser['school_code'] == $rowMyinfo['school_code']);
        $this->view->isFriend = Bll_Friend::isFriend($uid, $profileUid);
        if (0 == $rowSchoolUser['mode'] && ($this->view->isSameSchool || $this->view->isFriend)) {
            $isAllowView = true;
        }
        else if (1 == $rowSchoolUser['mode'] && $this->view->isSameSchool && $this->view->isFriend) {
            $isAllowView = true;
        }
        $this->view->isAllowView = $isAllowView;
        if ($isAllowView && $mdalTimepart->hasTimepartSchedule($profileUid)) {
            $mdalClass = Mdal_School_Class::getDefaultInstance();
            $lstTimepart = $mdalTimepart->listUserTimepartUsed($profileUid);
            foreach ($lstTimepart as $key=>$timeData) {
                $strBeginTime = $timeData['start_h'] . ':' . $timeData['start_m'];
                $lstTimepart[$key]['end_h'] = strftime('%H', strtotime($strBeginTime) + $timeData['part_minutes'] * 60);
                $lstTimepart[$key]['end_m'] = strftime('%M', strtotime($strBeginTime) + $timeData['part_minutes'] * 60);

                $lstTimepart[$key]['c_name'] = '未登録';
                $lstTimepart[$key]['cid'] = 0;
                $rowTimepartClass = $mdalTimepart->getTimepartScheduleByPk($profileUid, $now['wday'], $timeData['part']);
                if (!empty($rowTimepartClass)) {
                    $rowClass = $mdalClass->getClassInfo($rowTimepartClass['cid']);
                    $lstTimepart[$key]['c_name'] =  empty($rowClass) ? '未登録' : $rowClass['name'];
                    $lstTimepart[$key]['member_count'] =  empty($rowClass) ? 0 : $rowClass['member_count'];
                    $lstTimepart[$key]['cid'] = empty($rowClass) ? 0 : $rowClass['cid'];
                    $lstTimepart[$key]['wday'] = $now['wday'];
                    $myTimepartClass = $mdalTimepart->getTimepartScheduleByPk($uid, $now['wday'], $timeData['part']);
                    $lstTimepart[$key]['is_added'] = empty($myTimepartClass) ? 0 : 1;
                }
            }
        }

        //enquiry count
        require_once 'Mdal/School/Enquiry.php';
        $mdalEnquiry = Mdal_School_Enquiry::getDefaultInstance();
        $this->view->cntProfileEnquiry = $mdalEnquiry->getCntEnquiryByUid($profileUid);

        //ashiato list
        $lstAshiato = null;
        $cntAshiato = 0;
        require_once 'Mbll/School/RemoteServiceApi.php';
        $mbllRemoteApi = new Mbll_School_RemoteServiceApi();
        $isAshiatoUser = $mbllRemoteApi->isAshiatoUser($profileUid);
        if ($isAshiatoUser) {
            $cntAshiato = $mbllRemoteApi->getAshiatoCount($profileUid);
            if ($cntAshiato > 0) {
                $lstAshiato = $mbllRemoteApi->listAshiato($profileUid, 5);
                if (!empty($lstAshiato) && count($lstAshiato) > 0) {
                    foreach ($lstAshiato as $key=>$adata) {
                        $aryTime = getdate(strtotime($adata['create_time']));
                        $lstAshiato[$key]['pic_url'] = SCHOOL_REMOTE_SERVER_PHOTO_DIR . $adata['mobile_pic_url'];
                        $lstAshiato[$key]['format_time'] = $aryTime['mon'] . '/' . $aryTime['mday'];
                        if ($now['mon'] == $aryTime['mon'] && $now['mday'] == $aryTime['mday']) {
                    	    $lstAshiato[$key]['format_time'] = $aryTime['hours'] . ':' . (strlen($aryTime['minutes'])<2 ? ('0' . $aryTime['minutes']) : $aryTime['minutes']);
                    	}
                    }
                    Bll_User::appendPeople($lstAshiato, 'comment_uid');
                }
            }
        }

        $this->view->isAshiatoUser = $isAshiatoUser;
        $this->view->listAshiato = $lstAshiato;
        $this->view->cntAshiato = $cntAshiato;

        $this->view->profileSchedule = $lstTimepart;
        $this->view->boardAppTopUrl = urlencode(SCHOOL_REMOTE_SERVER_HOST . '/mobile/board/list' . '?CF_uid=' . $profileUid);

        $this->render();
    }

    /**
     * friendlist action
     *
     */
    public function friendlistAction()
    {
        $uid = $this->_user->getId();
        $pageIndex = $this->getParam('CF_page', 1);

        require_once 'Bll/Friend.php';
        require_once 'Bll/User.php';
        require_once 'Mdal/School/User.php';
        require_once 'Mdal/School/Timepart.php';
        require_once 'Mdal/School/Class.php';
        $lstFriend = null;
        $cntFriend = 0;
        $aryFids = Bll_Friend::getFriends($uid);
        $mdalUser = Mdal_School_User::getDefaultInstance();
        $rowSchoolUser = $this->_schoolUser;
        if (!empty($aryFids)) {
            $lstFriend = $mdalUser->listSchoolFriendIds($rowSchoolUser['school_code'], $uid, $aryFids, $pageIndex, $this->_pageSize);
            $cntFriend = $mdalUser->getSchoolFriendIdsCount($rowSchoolUser['school_code'], $uid, $aryFids);
            Bll_User::appendPeople($lstFriend, 'uid');

            //get currrent class
            $mdalTimepart = Mdal_School_Timepart::getDefaultInstance();
            foreach ($lstFriend as $key=>$fdata) {
                $lstFriend[$key]['class_name'] = $this->_getCurrentTimepartClassNameByUid($fdata['uid']);
            }
        }
        $this->view->cntFriend = $cntFriend;
        $this->view->listFriend = $lstFriend;

        //get start number and end number
        $start = ($pageIndex - 1) * $this->_pageSize;
        $this->view->startNm = $start + 1;
        $this->view->endNm = ($start + $this->_pageSize) > $cntFriend ? $cntFriend : ($start + $this->_pageSize);

        //get pager info
        $this->view->pager = array('count' => $cntFriend,
                                   'pageIndex' => $pageIndex,
                                   'requestUrl' => 'mobile/school/friendlist',
                                   'pageSize' => $this->_pageSize,
                                   'maxPager' => ceil($cntFriend / $this->_pageSize),
                                   'pageParam' => '',
                                   'lineColor' => '#ffdb33'
                                   );

        $this->render();
    }

	/**
     * newesttopiclist action
     *
     */
    public function newesttopiclistAction()
    {
        $uid = $this->_user->getId();
        $pageIndex = $this->getParam('CF_page', 1);

        require_once 'Mdal/School/Class.php';
        require_once 'Mdal/School/Topic.php';
        require_once 'Mdal/School/AssistNewestTopic.php';
        $mdalAssistTopic = Mdal_School_AssistNewestTopic::getDefaultInstance();
        $mdalClass = Mdal_School_Class::getDefaultInstance();
        $mdalTopic = Mdal_School_Topic::getDefaultInstance();
        $lstTopic = null;
        $cntTopic = 0;
        $aryJoinedCids = $mdalClass->listJoinedClassIds($uid);
        $cids = array();
        $now = getdate();
        if (!empty($aryJoinedCids)) {
            foreach ($aryJoinedCids as $cdata) {
            	$cids[] = $cdata['cid'];
            }
            $lstTopic = $mdalTopic->listNewestTopic($cids, $pageIndex, $this->_pageSize);
            $cntTopic = $mdalTopic->getNewestTopicCount($cids);
            foreach ($lstTopic as $key=>$tdata) {
            	$rowClass = $mdalClass->getClassInfo($tdata['cid']);
            	$lstTopic[$key]['c_name'] = empty($rowClass) ? '--' : $rowClass['name'];
            	$lstTopic[$key]['t_name'] = $tdata['title'];
            	$lstTopic[$key]['t_comment_count'] = $tdata['comment_count'];
            	$aryTime = getdate($tdata['create_time']);
            	$lstTopic[$key]['format_time'] = $aryTime['mon'] . '/' . $aryTime['mday'];
            	if ($now['mon'] == $aryTime['mon'] && $now['mday'] == $aryTime['mday']) {
            	    $lstTopic[$key]['format_time'] = $aryTime['hours'] . ':' . (strlen($aryTime['minutes'])<2 ? ('0' . $aryTime['minutes']) : $aryTime['minutes']);
            	}
            }
        }
        /*
        if (!empty($aryJoinedCids)) {
            foreach ($aryJoinedCids as $cdata) {
            	$cids[] = $cdata['cid'];
            }
            $lstTopic = $mdalAssistTopic->listNewestTopic($cids, $pageIndex, $this->_pageSize);
            $cntTopic = $mdalAssistTopic->getNewestTopicCount($cids);
            foreach ($lstTopic as $key=>$tdata) {
            	$rowClass = $mdalClass->getClassInfo($tdata['cid']);
            	$rowTopic = $mdalTopic->getClassTopic($tdata['tid']);
            	$lstTopic[$key]['c_name'] = empty($rowClass) ? '--' : $rowClass['name'];
            	$lstTopic[$key]['t_name'] = empty($rowTopic) ? '--' : $rowTopic['title'];
            	$lstTopic[$key]['t_comment_count'] = empty($rowTopic) ? 0 : $rowTopic['comment_count'];
            	$aryTime = getdate($rowTopic['create_time']);
            	$lstTopic[$key]['format_time'] = $aryTime['mon'] . '/' . $aryTime['mday'];
            	if ($now['mon'] == $aryTime['mon'] && $now['mday'] == $aryTime['mday']) {
            	    $lstTopic[$key]['format_time'] = $aryTime['hours'] . ':' . $aryTime['minutes'];
            	}
            }
        }
		*/
        $this->view->cntTopic = $cntTopic;
        $this->view->listTopic = $lstTopic;

        //get start number and end number
        $start = ($pageIndex - 1) * $this->_pageSize;
        $this->view->startNm = $start + 1;
        $this->view->endNm = ($start + $this->_pageSize) > $cntTopic ? $cntTopic : ($start + $this->_pageSize);

        //get pager info
        $this->view->pager = array('count' => $cntTopic,
                                   'pageIndex' => $pageIndex,
                                   'requestUrl' => 'mobile/school/newesttopiclist',
                                   'pageSize' => $this->_pageSize,
                                   'maxPager' => ceil($cntTopic / $this->_pageSize),
                                   'pageParam' => '',
                                   'lineColor' => '#b7cb6d'
                                   );

        $this->render();
    }

	/**
     * mynewesttopiclist action
     *
     */
    public function mynewesttopiclistAction()
    {
        $uid = $this->_user->getId();
        $pageIndex = $this->getParam('CF_page', 1);
        $msgid = (int)$this->getParam('CF_mid');
        //is from msg link
        if (!empty($msgid)) {
            require_once 'Mdal/School/Message.php';
            $mdalMsg = Mdal_School_Message::getDefaultInstance();
            $mdalMsg->deleteMessage($msgid);
        }

        require_once 'Mdal/School/Class.php';
        require_once 'Mdal/School/Topic.php';

        $mdalClass = Mdal_School_Class::getDefaultInstance();
        $mdalTopic = Mdal_School_Topic::getDefaultInstance();
        $lstTopic = null;
        $cntTopic = 0;
        $now = getdate();
        $lstTopic = $mdalTopic->listMyNewestTopic($uid, $pageIndex, $this->_pageSize);
        $cntTopic = $mdalTopic->getMyNewestTopicCount($uid);
        foreach ($lstTopic as $key=>$tdata) {
        	$rowClass = $mdalClass->getClassInfo($tdata['cid']);
        	$lstTopic[$key]['c_name'] = empty($rowClass) ? '--' : $rowClass['name'];
        	$lstTopic[$key]['t_name'] = $tdata['title'];
        	$lstTopic[$key]['t_comment_count'] = $tdata['comment_count'];
        	$aryTime = getdate($tdata['create_time']);
        	$lstTopic[$key]['format_time'] = $aryTime['mon'] . '/' . $aryTime['mday'];
        	if ($now['mon'] == $aryTime['mon'] && $now['mday'] == $aryTime['mday']) {
        	    $lstTopic[$key]['format_time'] = $aryTime['hours'] . ':' . (strlen($aryTime['minutes'])<2 ? ('0' . $aryTime['minutes']) : $aryTime['minutes']);
        	}
        }
        /*
        $lstTopic = $mdalTopic->listMyNewestTopicGroupByCid($uid, $pageIndex, $this->_pageSize);
        $cntTopic = $mdalTopic->getMyNewestTopicGroupByCidCount($uid);
        foreach ($lstTopic as $key=>$tdata) {
        	$rowClass = $mdalClass->getClassInfo($tdata['cid']);
        	$rowTopic = $mdalTopic->getClassTopic($tdata['tid']);
        	$lstTopic[$key]['c_name'] = empty($rowClass) ? '--' : $rowClass['name'];
        	$lstTopic[$key]['t_name'] = empty($rowTopic) ? '--' : $rowTopic['title'];
        	$lstTopic[$key]['t_comment_count'] = empty($rowTopic) ? 0 : $rowTopic['comment_count'];
        	$aryTime = getdate($rowTopic['create_time']);
        	$lstTopic[$key]['format_time'] = $aryTime['mon'] . '/' . $aryTime['mday'];
        	if ($now['mon'] == $aryTime['mon'] && $now['mday'] == $aryTime['mday']) {
        	    $lstTopic[$key]['format_time'] = $aryTime['hours'] . ':' . $aryTime['minutes'];
        	}
        }
		*/
        $this->view->cntTopic = $cntTopic;
        $this->view->listTopic = $lstTopic;

        //get start number and end number
        $start = ($pageIndex - 1) * $this->_pageSize;
        $this->view->startNm = $start + 1;
        $this->view->endNm = ($start + $this->_pageSize) > $cntTopic ? $cntTopic : ($start + $this->_pageSize);

        //get pager info
        $this->view->pager = array('count' => $cntTopic,
                                   'pageIndex' => $pageIndex,
                                   'requestUrl' => 'mobile/school/mynewesttopiclist',
                                   'pageSize' => $this->_pageSize,
                                   'maxPager' => ceil($cntTopic / $this->_pageSize),
                                   'pageParam' => '',
                                   'lineColor' => '#b7cb6d'
                                   );

        $this->render();
    }

    /**
     * visitfoot action
     *
     */
    public function visitfootAction()
    {
        $uid = $this->_user->getId();
        $pageIndex = $this->getParam('CF_page', 1);

        require_once 'Bll/Friend.php';
        require_once 'Bll/User.php';
        require_once 'Mdal/School/VisitFoot.php';
        $mdalVisitFoot = Mdal_School_VisitFoot::getDefaultInstance();
        $cntFootAccess = $mdalVisitFoot->getVisitFootCountAll($uid);
        $lstFoot = $mdalVisitFoot->listVisitFoot($uid, $pageIndex, $this->_pageSize);
        $cntFoot = $mdalVisitFoot->getVisitFootCount($uid);
        if (!empty($lstFoot) && count($lstFoot) > 0) {
            foreach ($lstFoot as $key => $pdata) {
                $lstFoot[$key]['is_friend'] = Bll_Friend::isFriend($uid, $pdata['uid']) ? '1' : '0';
            }
            Bll_User::appendPeople($lstFoot, 'uid');
        }
        $this->view->cntVisitFootAccess = $cntFootAccess;
        $this->view->listVisitFoot = $lstFoot;

        //get start number and end number
        $start = ($pageIndex - 1) * $this->_pageSize;
        $this->view->startNm = $start + 1;
        $this->view->endNm = ($start + $this->_pageSize) > $cntFoot ? $cntFoot : ($start + $this->_pageSize);

        //get pager info
        $this->view->pager = array('count' => $cntFoot,
                                   'pageIndex' => $pageIndex,
                                   'requestUrl' => 'mobile/school/visitfoot',
                                   'pageSize' => $this->_pageSize,
                                   'maxPager' => ceil($cntFoot / $this->_pageSize),
                                   'pageParam' => '',
                                   'lineColor' => '#ffa0fe'
                                   );

        $this->render();
    }

	/**
     * class action
     *
     */
    public function classAction()
    {
        $uid = $this->_user->getId();
        $cid = $this->getParam('CF_cid');
        $rowSchoolUser = $this->_schoolUser;
        require_once 'Mbll/School/ClassCommonNote.php';
        $mbllCommonNote = new Mbll_School_ClassCommonNote();
        $mbllCommonNote->releaseLockClassCommonNote($cid, $uid);

        require_once 'Mbll/School/Class.php';
        require_once 'Mdal/School/Class.php';
        require_once 'Mdal/School/Topic.php';
        $mdalClass = Mdal_School_Class::getDefaultInstance();
        //class member check
        if (!$mdalClass->isClassMember($cid, $uid)) {
            $this->_redirect($this->_baseUrl . '/mobile/school/home');
            return;
        }

        //class info
        $rowClass = $mdalClass->getClassInfo($cid);
        if (empty($rowClass)) {
            $this->_redirect($this->_baseUrl . '/mobile/school/error');
            return;
        }
        require_once 'Bll/Friend.php';
        $aryFids = Bll_Friend::getFriends($uid);
        $rowClass['c_forecast'] = $this->_getClassForecastStatus($uid, $cid, $aryFids);

        //next class schedule
        $this->view->nextSchedule = $this->_getNextClassDateArray($uid, $cid);

        //list class topic
        $mdalTopic = Mdal_School_Topic::getDefaultInstance();
        $lstTopic = $mdalTopic->listClassTopic($cid, 1, 3);
        $cntTopic = $mdalTopic->getClassTopicCount($cid);
        $this->view->cntTopic = $cntTopic;
        $this->view->listTopic = $lstTopic;

        $rowMyVote = $mdalClass->getVotedInfo($cid, $uid);
        //have not voted
        if (empty($rowMyVote)) {
            $this->view->aryContent = $this->_getClassVoteItemArray('vote_content', true);
            $this->view->aryDifficult = $this->_getClassVoteItemArray('vote_difficult', true);
            $this->view->aryWork = $this->_getClassVoteItemArray('vote_work', true);
            $this->view->aryTest = $this->_getClassVoteItemArray('vote_test', true);
            $this->view->aryAttend = $this->_getClassVoteItemArray('vote_attend', true);
        }
        //get vote result
        else {
            $rowVoteResult = $mdalClass->getAvgVoteResult($cid);
            foreach ($rowVoteResult as $key=>$val) {
                if ($val>=1 && $val<=1.5) {
                    $rowVoteResult[$key . '_stars'] = 1;
                }
                else if ($val>1.5 && $val<=2.5) {
                    $rowVoteResult[$key . '_stars'] = 2;
                }
                else if ($val>2.5 && $val<=3.5) {
                    $rowVoteResult[$key . '_stars'] = 3;
                }
                else if ($val>3.5 && $val<=4.5) {
                    $rowVoteResult[$key . '_stars'] = 4;
                }
                else if ($val>4.5 && $val<=5) {
                    $rowVoteResult[$key . '_stars'] = 5;
                }
                else {
                    $rowVoteResult[$key . '_stars'] = 0;
                }
                $rowVoteResult[$key . '_text'] = $this->_getClassVoteItemText($key, $rowVoteResult[$key . '_stars']);

                //get stars array for smarty
                for($i=1; $i<=5; $i++) {
                    if ($rowVoteResult[$key . '_stars'] >= $i) {
                        $rowVoteResult['ary_' . $key][] = 1;
                    }
                    else {
                        $rowVoteResult['arynull_' . $key][] = 1;
                    }
                }
            }
            $this->view->voteResult = $rowVoteResult;
        }

        //list class member
        $mbllClass = new Mbll_School_Class();
        $lstMember = $mbllClass->lstClassMember($cid, $uid, 1, 5);
        $cntMember = $mdalClass->getCountClassMemberByCid($cid);
        $this->view->cntMember = $cntMember;
        $this->view->listMember = $lstMember;

        $this->view->classInfo = $rowClass;
        $this->render();
    }

	/**
     * class topic list action
     *
     */
    public function topiclistAction()
    {
        $uid =  $this->_user->getId();
        $pageIndex = $this->getParam('CF_page', 1);
        $cid = $this->getParam('CF_cid');

        require_once 'Mbll/School/Class.php';
        require_once 'Mdal/School/Class.php';
        require_once 'Mdal/School/Topic.php';
        $mdalClass = Mdal_School_Class::getDefaultInstance();

        //class member check
        if (!$mdalClass->isClassMember($cid, $uid)) {
            $this->_redirect($this->_baseUrl . '/mobile/school/home');
            return;
        }

        //class info
        $rowClass = $mdalClass->getClassInfo($cid);
        if (empty($rowClass)) {
            $this->_redirect($this->_baseUrl . '/mobile/school/error');
            return;
        }
        $this->view->classInfo = $rowClass;

        //get class topic list
        $dalTopic = Mdal_School_Topic::getDefaultInstance();
        $classTopicList = $dalTopic->listClassTopic($cid, $pageIndex, $this->_pageSize);
        $cntTopic = $dalTopic->getClassTopicCount($cid);

         //get start number and end number
        $start = ($pageIndex - 1) * $this->_pageSize;
        $this->view->startNm = $start + 1;
        $this->view->endNm = ($start + $this->_pageSize) > $cntTopic ? $cntTopic : ($start + $this->_pageSize);

        //next class schedule
        $this->view->nextSchedule = $this->_getNextClassDateArray($uid, $cid);

    	$this->view->pager = array('count' => $cntTopic,
                                   'pageIndex' => $pageIndex,
                                   'requestUrl' => 'mobile/school/topiclist',
                                   'pageSize' => $this->_pageSize,
                                   'maxPager' => ceil($cntTopic / $this->_pageSize),
    	                           'pageParam' => '&CF_cid=' . $cid);

        $this->view->topicList = $classTopicList;
        $this->view->topicCount = $cntTopic;
        $this->render();
    }

    /**
     * add class topic action
     *
     */
    public function topicaddAction()
    {
    	$uid = $this->_user->getId();
    	$cid = $this->getParam('CF_cid');
		$step = $this->getParam('CF_step', 'start');

        require_once 'Mdal/School/Class.php';
        $mdalClass = Mdal_School_Class::getDefaultInstance();

        //class member check
        if (!$mdalClass->isClassMember($cid, $uid)) {
            $this->_redirect($this->_baseUrl . '/mobile/school/home');
            return;
        }

        //class info
        $rowClass = $mdalClass->getClassInfo($cid);
        if (empty($rowClass)) {
            $this->_redirect($this->_baseUrl . '/mobile/school/error');
            return;
        }
        $this->view->classInfo = $rowClass;
        //next class schedule
        $this->view->nextSchedule = $this->_getNextClassDateArray($uid, $cid);

        //edit mode
    	if ($step == "start") {
    	    //from edit mode
            if (isset($_SESSION['school_class_topicadd']) && $_SESSION['school_class_topicadd'] != null) {
                //load from session
                $rowTopic = $_SESSION['school_class_topicadd'];
                $this->view->errMsg = $rowTopic['CF_error'];
                $_SESSION['school_class_topicadd'] = null;
                unset($_SESSION['school_class_topicadd']);
            }
            //init
            else {
                //$rowTopic = $mdalTopic->getClassTopic($cid, $uid);
                $rowTopic = array('title' => '', 'introduce' => '');
            }
            $this->view->topicInfo = $rowTopic;
    	}
    	else if ($step == 'confirm') {
    		$txtTitle = trim($this->getParam('txtTitle'));
            $txtIntroduce = trim($this->getParam('txtIntroduce'));

            require_once 'Mbll/Emoji.php';
            $bllEmoji = new Bll_Emoji();
            $tmpTitle = $bllEmoji->escapeEmoji($txtTitle, true);

            //check validate
            $strMsg = '';
            if (empty($txtTitle)) {
                $strMsg .= "･ﾀｲﾄﾙが未入力です｡";
            }
            else if ($tmpTitle != $txtTitle) {
                $strMsg .= (empty($strMsg)?'':"\n") . "･ﾀｲﾄﾙに絵文字は使用できません｡";
            }
    	    else if (mb_strlen($txtTitle, 'UTF-8') > 30) {
                $strMsg .= (empty($strMsg)?'':"\n") . "･ﾀｲﾄﾙは30文字以内で入力してください｡";
            }
            if (empty($txtIntroduce)) {
                $strMsg .= (empty($strMsg)?'':"\n") . "･質問内容が未入力です｡";
            }
            else if (mb_strlen($txtIntroduce, 'UTF-8') > 300) {
                $strMsg .= (empty($strMsg)?'':"\n") . "･質問内容は300文字以内で入力してください｡";
            }

            //convert emoji to the format like [i/e/s:x]
            $txtIntroduce = $bllEmoji->escapeEmoji($txtIntroduce);

            //save to session
            $_SESSION['school_class_topicadd'] = array('title' => $txtTitle, 'introduce' => $txtIntroduce);
            if (!empty($strMsg)) {
            	$_SESSION['school_class_topicadd'] = array('title' => $txtTitle, 'introduce' => $txtIntroduce, 'CF_error' => $strMsg);
                $this->_redirect($this->_baseUrl . '/mobile/school/topicadd?CF_cid=' . $cid);
                return;
            }
            //show confirm info
            $this->view->topicInfo = $_SESSION['school_class_topicadd'];
    	}
    	else if ($step == 'complete') {
    	    if (isset($_SESSION['school_class_topicadd']) && $_SESSION['school_class_topicadd'] != null) {
                //load from session
                $rowTopic = $_SESSION['school_class_topicadd'];
                //add topic
                require_once 'Mbll/School/Topic.php';
                $mbllTopic= new Mbll_School_Topic();
                $result = $mbllTopic->addTopic($rowTopic, $cid, $uid);
                $rowTopic['tid'] = $result;
                $_SESSION['school_class_topicadd'] = null;
                unset($_SESSION['school_class_topicadd']);
                $this->view->topicInfo = $rowTopic;
            }
    	}

    	$this->view->step = $step;
    	$this->render();
    }


	/**
     * class vote add/edit action
     *
     */
    public function voteaddAction()
    {
    	$uid = $this->_user->getId();
    	$cid = $this->getParam('CF_cid');
        $step = $this->getParam('CF_step', 'start');

        $rowSchoolUser = $this->_schoolUser;
        require_once 'Mdal/School/Class.php';
        $mdalClass = Mdal_School_Class::getDefaultInstance();
        //class member check
        if (!$mdalClass->isClassMember($cid, $uid)) {
            $this->_redirect($this->_baseUrl . '/mobile/school/home');
            return;
        }

        //class info
        $rowClass = $mdalClass->getClassInfo($cid);
        if (empty($rowClass)) {
            $this->_redirect($this->_baseUrl . '/mobile/school/error');
            return;
        }
        $this->view->classInfo = $rowClass;
        //next class schedule
        $this->view->nextSchedule = $this->_getNextClassDateArray($uid, $cid);

        //edit mode
        if ($step == 'start') {
            //from edit mode
            if (isset($_SESSION['school_class_voteadd']) && $_SESSION['school_class_voteadd'] != null) {
                //load from session
                $rowMyVote = $_SESSION['school_class_voteadd'];
                $this->view->errMsg = $rowMyVote['CF_error'];
                $_SESSION['school_class_voteadd'] = null;
                unset($_SESSION['school_class_voteadd']);
            }
            //init
            else {
                $rowMyVote = $mdalClass->getVotedInfo($cid, $uid);
            }

            $this->view->myVote = $rowMyVote;
            $this->view->aryContent = $this->_getClassVoteItemArray('vote_content', true);
            $this->view->aryDifficult = $this->_getClassVoteItemArray('vote_difficult', true);
            $this->view->aryWork = $this->_getClassVoteItemArray('vote_work', true);
            $this->view->aryTest = $this->_getClassVoteItemArray('vote_test', true);
            $this->view->aryAttend = $this->_getClassVoteItemArray('vote_attend', true);
        }

        //confirm mode
        else if ($step == 'confirm') {
            $voteContent = (int)$this->getParam('selContent');
            $voteDifficult = (int)$this->getParam('selDifficult');
            $voteWork = (int)$this->getParam('selWork');
            $voteTest = (int)$this->getParam('selTest');
            $voteAttend = (int)$this->getParam('selAttend');

            //check validate
            $strMsg = '';
            if (empty($voteContent) || $voteContent > 5) {
                $strMsg .= "･内容を選択してください｡";
            }
            if (empty($voteDifficult) || $voteDifficult > 5) {
                $strMsg .= (empty($strMsg)?'':"\n") . "･難易度を選択してください｡";
            }
            if (empty($voteWork) || $voteWork > 5) {
                $strMsg .= (empty($strMsg)?'':"\n") . "･宿題を選択してください｡";
            }
            if (empty($voteTest) || $voteTest > 5) {
                $strMsg .= (empty($strMsg)?'':"\n") . "･試験を選択してください｡";
            }
            if (empty($voteAttend) || $voteAttend > 5) {
                $strMsg .= (empty($strMsg)?'':"\n") . "･出席を選択してください｡";
            }

            //save to session
            $arySession = array();
            $arySession['vote_content'] = $voteContent;
            $arySession['vote_difficult'] = $voteDifficult;
            $arySession['vote_work'] = $voteWork;
            $arySession['vote_test'] = $voteTest;
            $arySession['vote_attend'] = $voteAttend;

            if (!empty($strMsg)) {
            	$arySession['CF_error'] = $strMsg;
            	$_SESSION['school_class_voteadd'] = $arySession;
                $this->_redirect($this->_baseUrl . '/mobile/school/voteadd?CF_cid=' . $cid);
                return;
            }
			$_SESSION['school_class_voteadd'] = $arySession;

            //show confirm info
            $this->view->voteContent = $this->_getClassVoteItemText('vote_content', $voteContent);
            $this->view->voteDifficult = $this->_getClassVoteItemText('vote_difficult', $voteDifficult);
            $this->view->voteWork = $this->_getClassVoteItemText('vote_work', $voteWork);
            $this->view->voteTest = $this->_getClassVoteItemText('vote_test', $voteTest);
            $this->view->voteAttend = $this->_getClassVoteItemText('vote_attend', $voteAttend);
        }

        //complete mode
        else if ($step == 'complete') {
        	if (isset($_SESSION['school_class_voteadd']) && $_SESSION['school_class_voteadd'] != null) {
                //load from session
                $rowMyVote = $_SESSION['school_class_voteadd'];
                //vote updated
                require_once 'Mbll/School/Class.php';
                $mbllClass = new Mbll_School_Class();
                $result = $mbllClass->addVote($rowMyVote, $cid, $uid);
                $_SESSION['school_class_voteadd'] = null;
                unset($_SESSION['school_class_voteadd']);
            }
        }

        $this->view->step = $step;
    	$this->render();
    }

	/**
     * class forecast finish action
     *
     */
    public function forecastAction()
    {
    	$uid = $this->_user->getId();
    	$cid = $this->getParam('CF_cid');
        $forecast = (int)$this->getParam('CF_forecast');//[0-晴れ/1-曇り/2-雨]
        $agree = (int)$this->getParam('CF_agree');//[0-not agree/1-agree]

        $rowSchoolUser = $this->_schoolUser;
        require_once 'Mdal/School/Class.php';
        $mdalClass = Mdal_School_Class::getDefaultInstance();
        //class member check
        if (!$mdalClass->isClassMember($cid, $uid)) {
            $this->_redirect($this->_baseUrl . '/mobile/school/home');
            return;
        }

        //class info
        $rowClass = $mdalClass->getClassInfo($cid);
        if (empty($rowClass)) {
            $this->_redirect($this->_baseUrl . '/mobile/school/error');
            return;
        }
        $this->view->classInfo = $rowClass;
        //next class schedule
        $this->view->nextSchedule = $this->_getNextClassDateArray($uid, $cid);

        //forecast
        $isBad = 0;
        if (1 == $forecast) {
            $isBad = $agree;
        }
        else if (2 == $forecast) {
            $isBad = $agree;
        }
        else {
            $isBad = ($agree==1 ? 0 : 1);
        }
        require_once 'Mbll/School/Class.php';
        $mbllClass = new Mbll_School_Class();
        $result = $mbllClass->forecastClass($isBad, $cid, $uid);

        $this->view->agree = $agree;
    	$this->render();
    }

	/**
     * class common note action
     *
     */
    public function classnoteAction()
    {
        $uid = $this->_user->getId();
        $cid = $this->getParam('CF_cid');
        $rowSchoolUser = $this->_schoolUser;
        require_once 'Mbll/School/ClassCommonNote.php';
        $mbllCommonNote = new Mbll_School_ClassCommonNote();
        $mbllCommonNote->releaseLockClassCommonNote($cid, $uid);

        require_once 'Mdal/School/Class.php';
        $mdalClass = Mdal_School_Class::getDefaultInstance();
        //class member check
        if (!$mdalClass->isClassMember($cid, $uid)) {
            $this->_redirect($this->_baseUrl . '/mobile/school/home');
            return;
        }
        //class info
        $rowClass = $mdalClass->getClassInfo($cid);
        if (empty($rowClass)) {
            $this->_redirect($this->_baseUrl . '/mobile/school/error');
            return;
        }

        $this->view->classInfo = $rowClass;
        //next class schedule
        $this->view->nextSchedule = $this->_getNextClassDateArray($uid, $cid);

        //modify member list
        require_once 'Mdal/School/ClassCommonNote.php';
        $mdalCommonNote = Mdal_School_ClassCommonNote::getDefaultInstance();
        $listEditor = $mdalCommonNote->listCommonNoteUser($cid, 1, 5);
        require_once 'Bll/User.php';
        Bll_User::appendPeople($listEditor, 'uid');
        $this->view->listEditor = $listEditor;
        $this->render();
    }

    /**
     * class common note add action
     *
     */
    public function classnoteaddAction()
    {
        $uid = $this->_user->getId();
    	$cid = $this->getParam('CF_cid');
		$step = $this->getParam('CF_step', 'start');
        $rowSchoolUser = $this->_schoolUser;

        require_once 'Mbll/School/ClassCommonNote.php';
        $mbllCommonNote = new Mbll_School_ClassCommonNote();
        //check lock is time out default:15 minutes
        $drst = $mbllCommonNote->dealCommonNoteTimeoutLock($cid, 15);

        require_once 'Mdal/School/Class.php';
        $mdalClass = Mdal_School_Class::getDefaultInstance();
        //class member check
        if (!$mdalClass->isClassMember($cid, $uid)) {
            $this->_redirect($this->_baseUrl . '/mobile/school/home');
            return;
        }
        //class info
        $rowClass = $mdalClass->getClassInfo($cid);
        if (empty($rowClass)) {
            $this->_redirect($this->_baseUrl . '/mobile/school/error');
            return;
        }
        $this->view->classInfo = $rowClass;
        //next class schedule
        $this->view->nextSchedule = $this->_getNextClassDateArray($uid, $cid);

        //check if common note is locked
        if (!$mbllCommonNote->addLockClassCommonNote($cid, $uid)) {
            $step = "lockFailed";
        }

        //edit mode
    	if ($step == "start") {
    	    //from edit mode
            if (isset($_SESSION['school_class_common_noteadd']) && $_SESSION['school_class_common_noteadd'] != null) {
                //load from session
                $rowNote = $_SESSION['school_class_common_noteadd'];
                $this->view->errMsg = $rowNote['CF_error'];
                $_SESSION['school_class_common_noteadd'] = null;
                unset($_SESSION['school_class_common_noteadd']);
            }
            //init
            else {
                $rowNote = array('introduce' => $rowClass['introduce']);
            }
            $this->view->noteInfo = $rowNote;
    	}
    	else if ($step == 'confirm') {
    		$txtNote = trim($this->getParam('txtNote'));

            //check validate
            $strMsg = '';
            if (empty($txtNote)) {
                $strMsg .= "･ﾉｰﾄが未入力です｡";
            }
    	    else if (mb_strlen($txtNote, 'UTF-8') > 30000) {
                $strMsg .= (empty($strMsg)?'':"\n") . "･30000文字以内で入力してください｡";
            }
            require_once 'Mbll/Emoji.php';
            $bllEmoji = new Bll_Emoji();
            $txtNote = $bllEmoji->escapeEmoji($txtNote);

            //save to session
            $_SESSION['school_class_common_noteadd'] = array('introduce' => $txtNote);
            if (!empty($strMsg)) {
            	$_SESSION['school_class_common_noteadd'] = array('introduce' => $txtNote, 'CF_error' => $strMsg);
                $this->_redirect($this->_baseUrl . '/mobile/school/classnoteadd?CF_cid=' . $cid);
                return;
            }
            //show confirm info
            $this->view->noteInfo = $_SESSION['school_class_common_noteadd'];
    	}
    	else if ($step == 'complete') {
    	    if (isset($_SESSION['school_class_common_noteadd']) && $_SESSION['school_class_common_noteadd'] != null) {
                //load from session
                $rowNote = $_SESSION['school_class_common_noteadd'];
                //edit note
                $result = $mbllCommonNote->editClassCommonNote($rowNote, $cid, $uid);
                $_SESSION['school_class_common_noteadd'] = null;
                unset($_SESSION['school_class_common_noteadd']);
            }
    	}

    	$this->view->step = $step;
    	$this->render();
    }

	/**
     * timepart set action
     *
     */
    public function timepartsetAction()
    {
    	$uid = $this->_user->getId();
    	$step = $this->getParam('CF_step', 'start');
        $rowSchoolUser = $this->_schoolUser;
        require_once 'Mdal/School/Timepart.php';
        $mdalTimepart = Mdal_School_Timepart::getDefaultInstance();

        //edit mode
        if ($step == 'start') {
            //from edit mode
            if (isset($_SESSION['school_user_timepartset'])) {
                //load from session
                $lstTimepart = $_SESSION['school_user_timepartset'];
                $this->view->errMsg = $_SESSION['school_user_timepartset_err'];
                $_SESSION['school_user_timepartset'] = null;
                unset($_SESSION['school_user_timepartset']);
                $_SESSION['school_user_timepartset_err'] = null;
                unset($_SESSION['school_user_timepartset_err']);
            }
            //init
            else {
                $lstTimepart = $mdalTimepart->listUserTimepart($uid);
            }
            //auto calc
            $maxShowPart = 0;
            foreach ($lstTimepart as $timepart) {
                if (0 == $timepart['is_hide'] && $timepart['part'] > $maxShowPart) {
            	    $maxShowPart = $timepart['part'];
            	}
            }
            foreach ($lstTimepart as $keyNo=>$timepart) {
                $lstTimepart[$keyNo]['has_class'] = 0;
                $lstTimepartClass = $mdalTimepart->lstUserScheduleByPart($uid, $timepart['part']);
                if (!empty($lstTimepartClass)) {
                    $lstTimepart[$keyNo]['has_class'] = 1;
                }
                if ($maxShowPart >= $timepart['part'] && 1 == $timepart['is_hide']) {
                    $lstTimepart[$keyNo]['ary_fix_hour_option'] = array($timepart['start_h'] => $timepart['start_h'] . '時');
                    $lstTimepart[$keyNo]['ary_fix_minute_option'] = array($timepart['start_m'] => $timepart['start_m'] . '分');
                }
            }

            $aryHour = array();
            for ($i=7; $i<24; $i++) {
                $aryHour[(strlen($i)<2 ? '0': '') . $i] = $i . '時';
            }
            $aryMinute = array();
            for ($i=0; $i<12; $i++) {
                $keyM = $i*5;
                $aryMinute[(strlen($keyM)<2 ? '0': '') . $keyM] = (strlen($keyM)<2 ? '0': '') . $keyM . '分';
            }
            $this->view->aryPartMinutes = array('30'=>'30分','35'=>'35分','40'=>'40分','45'=>'45分','50'=>'50分','55'=>'55分',
                                                '60'=>'60分','65'=>'65分','70'=>'70分','75'=>'75分','80'=>'80分','85'=>'85分',
                                                '90'=>'90分','95'=>'95分','100'=>'100分','105'=>'105分','110'=>'110分',
                                                '115'=>'115分','120'=>'120分');
            $this->view->aryHour = $aryHour;
            $this->view->aryHourNull = array('' => '--') + $aryHour;
            $this->view->aryMinute = $aryMinute;
            $this->view->aryMinuteNull = array('' => '--') + $aryMinute;
            $this->view->lstTimepart = $lstTimepart;
        }

        //confirm mode
        else if ($step == 'confirm') {
            $partMinutes = (int)$this->getParam('selPartMinutes');
            $aryStartHour = array();
            $aryStarMinute = array();
            $aryChkPart = array();
            for($i=1; $i<=14; $i++) {
                $aryStartHour[] = $this->getParam('selStartHour'.$i);
                $aryStarMinute[] = $this->getParam('selStartMinute'.$i);
                $tmpChk = $this->getParam('chkHide'.$i);
                if (!empty($tmpChk)) {
                    $aryChkPart[] = $tmpChk;
                }
            }

            //check validate
            $aryTimepart = array();
            $strMsg = '';
            foreach ($aryStartHour as $keyNo=>$hour) {
                $part = $keyNo + 1;
                $aryTimepart[$keyNo] = array();
                $aryTimepart[$keyNo]['part_minutes'] = $partMinutes;
                $aryTimepart[$keyNo]['part'] = $part;
                $aryTimepart[$keyNo]['start_h'] = $aryStartHour[$keyNo];
                $aryTimepart[$keyNo]['start_m'] = $aryStarMinute[$keyNo];
                $aryTimepart[$keyNo]['is_hide'] = 0;
                if ($aryChkPart && in_array($part, $aryChkPart)) {
                    $aryTimepart[$keyNo]['is_hide'] = 1;
                    //auto calc space time
                    $strSpaceTime = strftime('%H:%M', strtotime($lastStartHour . ':' . $lastStartMinute) + $partMinutes*60);
                    $arySpaceTime = explode(':', $strSpaceTime);
                    $aryStartHour[$keyNo] = $arySpaceTime[0];
                    $aryStarMinute[$keyNo] = $arySpaceTime[1];
                    $aryTimepart[$keyNo]['start_h'] = $aryStartHour[$keyNo];
                    $aryTimepart[$keyNo]['start_m'] = $aryStarMinute[$keyNo];
                }
                else if (empty($aryStartHour[$keyNo]) || empty($aryStarMinute[$keyNo])) {
                    $strMsg .= (empty($strMsg)?'':"\n") . "･" . $part . "時限の開始時刻に誤りがあります｡";
                }
                //【n時限の開始時刻】－【n-1時限の開始時刻】＞＝【授業1回の時間】 check
                else if ($part > 1) {
                    if (empty($lastStartHour)) {
                        $lastStartHour = $aryStartHour[0];
                        $lastStartMinute = $aryStarMinute[0];
                    }
                    $strBeginTime = $lastStartHour . ':' . $lastStartMinute;
                    $strBeginTimeN = $aryStartHour[$keyNo] . ':' . $aryStarMinute[$keyNo];
                    if (strtotime($strBeginTimeN) - strtotime($strBeginTime) < $partMinutes*60) {
                        $strMsg .= (empty($strMsg)?'':"\n") . "･" . $part . "時限の開始時刻に誤りがあります｡";
                    }
                }
                $lastStartHour = $aryStartHour[$keyNo];
                $lastStartMinute = $aryStarMinute[$keyNo];
            }

            //save to session
            $_SESSION['school_user_timepartset'] = $aryTimepart;
            if (!empty($strMsg)) {
            	$_SESSION['school_user_timepartset_err'] = $strMsg;
                $this->_redirect($this->_baseUrl . '/mobile/school/timepartset?step=start');
                return;
            }

            //show confirm info
            $this->view->cfmTimepart = $aryTimepart;
            $this->view->lstChkPart = $aryChkPart;
        }

        //complete mode
        else if ($step == 'complete') {
            if (isset($_SESSION['school_user_timepartset'])) {
                //load from session
                $aryInfo = $_SESSION['school_user_timepartset'];
                //timepart updated
                require_once 'Mbll/School/Timepart.php';
                $mbllTimepart = new Mbll_School_Timepart();
                $result = $mbllTimepart->setTimepart($uid, $aryInfo);
                $_SESSION['school_user_timepartset'] = null;
                unset($_SESSION['school_user_timepartset']);
                $_SESSION['school_user_timepartset_err'] = null;
                unset($_SESSION['school_user_timepartset_err']);
            }
        }

        $this->view->step = $step;
    	$this->render();
    }

	/**
     * privacy info action
     *
     */
    public function privacyAction()
    {
    	$uid = $this->_user->getId();
    	$rowSchoolUser = $this->_schoolUser;
    	//2回目以降の訪問 and 公開設定を一度も変更していない
    	if (!empty($rowSchoolUser['last_mode_change_date']) || !empty($rowSchoolUser['is_privacy_showed'])) {
            $this->_redirect($this->_baseUrl . '/mobile/school/home');
            return;
    	}

    	require_once 'Mdal/School/User.php';
    	$mdalUser = Mdal_School_User::getDefaultInstance();
    	$mdalUser->updateUser(array('is_privacy_showed' => 1), $uid);
    	$this->render();
    }

	/**
     * privacy set action
     *
     */
    public function privacysetAction()
    {
    	$uid = $this->_user->getId();
    	$step = $this->getParam('CF_step', 'start');
        $rowSchoolUser = $this->_schoolUser;

        //edit mode
        if ($step == 'start') {
            //from edit mode
            if (isset($_SESSION['school_user_privacyset'])) {
                //load from session
                $intMode = $_SESSION['school_user_privacyset'];
                $_SESSION['school_user_privacyset'] = null;
                unset($_SESSION['school_user_privacyset']);
            }
            //init
            else {
                $intMode = $rowSchoolUser['mode'];
            }
            $this->view->myMode = $intMode;
        }

        //confirm mode
        else if ($step == 'confirm') {
            $intMode = (int)$this->getParam('rdoMode');
            //save to session
            $_SESSION['school_user_privacyset'] = $intMode;
            //show confirm info
            $this->view->myModeName = $intMode ? '同級生のﾏｲﾐｸのみ公開' : '同級生全員に公開';
        }

        //complete mode
        else if ($step == 'complete') {
            if (isset($_SESSION['school_user_privacyset'])) {
                //load from session
                $intMode = $_SESSION['school_user_privacyset'];
                //privacy updated
                require_once 'Mdal/School/User.php';
                $mdalUser = Mdal_School_User::getDefaultInstance();
                $result = $mdalUser->updateUser(array('mode' => $intMode, 'last_mode_change_date' => time()), $uid);
                $_SESSION['school_user_privacyset'] = null;
                unset($_SESSION['school_user_privacyset']);
            }
        }

        $this->view->step = $step;
    	$this->render();
    }

	/**
     * ashiato invite receive action
     *
     */
    public function ashiatoinvitereceiveAction()
    {
    	$uid = $this->_user->getId();
        $msgid = (int)$this->getParam('CF_mid');
        //is from msg link
        if (!empty($msgid)) {
            require_once 'Mdal/School/Message.php';
            $mdalMsg = Mdal_School_Message::getDefaultInstance();
            $mdalMsg->deleteMessage($msgid);
        }
        $this->_redirect($this->_mixiMobileUrl . BOARD_APP_ID .'/?guid=ON');
        return;
    }

	/**
     * ashiato info action
     *
     */
    public function ashiatoAction()
    {
    	$uid = $this->_user->getId();
    	$rowSchoolUser = $this->_schoolUser;
    	//3回目以降の訪問 and あしあと帳アプリ未登録
    	if (!empty($rowSchoolUser['is_ashiato_showed'])) {
            $this->_redirect($this->_baseUrl . '/mobile/school/home');
            return;
    	}

    	require_once 'Mdal/School/User.php';
    	$mdalUser = Mdal_School_User::getDefaultInstance();
    	$mdalUser->updateUser(array('is_ashiato_showed' => 1), $uid);
    	$this->render();
    }

	/**
     * invite ashiato action
     *
     */
    public function ashiatoinviteAction()
    {
    	$uid = $this->_user->getId();
    	$profileUid = $this->getParam('CF_uid');

        if (empty($profileUid) || $uid == $profileUid) {
            $this->_redirect($this->_baseUrl . '/mobile/school/home');
            return;
        }

        //invite ashiato
        require_once 'Mdal/School/Message.php';
        $mdalMessage = Mdal_School_Message::getDefaultInstance();
        $rowMsg = $mdalMessage->isMessageExites($profileUid, 2);
        if ($rowMsg) {
            //$mdalMessage->updateMessage(array('uid' => $uid), $rowMsg['id']);
        }
        else {
            $mdalMessage->insertMessage(array('uid'=>$uid, 'target_uid'=>$profileUid, 'type'=>2, 'create_time'=>time()));
        }

        require_once 'Bll/User.php';
        $profileInfo = array('uid' => $profileUid);
        Bll_User::appendPerson($profileInfo, 'uid');
    	$this->view->profileInfo = $profileInfo;
    	$this->render();
    }

	/**
     * add ashiato comment action
     *
     */
    public function ashiatoaddAction()
    {
    	$uid = $this->_user->getId();
    	$profileUid = $this->getParam('CF_uid');
		$step = $this->getParam('CF_step', 'start');
		$rowSchoolUser = $this->_schoolUser;
		if (empty($profileUid) || $profileUid == $uid) {
		    $this->_redirect($this->_baseUrl . '/mobile/school/home');
            return;
		}

        require_once 'Bll/User.php';
        $profileInfo = array('uid' => $profileUid);
        Bll_User::appendPerson($profileInfo, 'uid');
    	$this->view->profileInfo = $profileInfo;

    	require_once 'Mbll/School/RemoteServiceApi.php';
        $mbllRemoteApi = new Mbll_School_RemoteServiceApi();
        //check is ashiato app added
        if (!$mbllRemoteApi->isAshiatoUser($uid)) {
            $step = "error";
        }

        //edit mode
    	if ($step == "start") {
    	    //from edit mode
            if (isset($_SESSION['school_ashiato_commentadd']) && $_SESSION['school_ashiato_commentadd'] != null) {
                //load from session
                $rowComment = $_SESSION['school_ashiato_commentadd'];
                $this->view->errMsg = $rowComment['CF_error'];
                $_SESSION['school_ashiato_commentadd'] = null;
                unset($_SESSION['school_ashiato_commentadd']);
            }
            //init
            else {
                $rowComment = array('content' => '');
            }
            $this->view->commentInfo = $rowComment;
    	}
    	else if ($step == 'confirm') {
            $txtComment = trim($this->getParam('txtComment'));
            //check validate
            $strMsg = '';
            if (empty($txtComment)) {
                $strMsg .= (empty($strMsg)?'':"\n") . "･ｺﾒﾝﾄが未入力です｡";
            }
            else if (mb_strlen($txtComment, 'UTF-8') > 100) {
                $strMsg .= (empty($strMsg)?'':"\n") . "･100文字以内で入力してください｡";
            }

            require_once 'Mbll/Emoji.php';
            $bllEmoji = new Bll_Emoji();
            $txtComment = $bllEmoji->escapeEmoji($txtComment);
            //save to session
            $_SESSION['school_ashiato_commentadd'] = array('content' => $txtComment);
            if (!empty($strMsg)) {
            	$_SESSION['school_ashiato_commentadd'] = array('content' => $txtComment, 'CF_error' => $strMsg);
                $this->_redirect($this->_baseUrl . '/mobile/school/ashiatoadd?CF_uid=' . $profileUid);
                return;
            }
            //show confirm info
            $this->view->commentInfo = $_SESSION['school_ashiato_commentadd'];
    	}
    	else if ($step == 'complete') {
    	    if (isset($_SESSION['school_ashiato_commentadd']) && $_SESSION['school_ashiato_commentadd'] != null) {
                //load from session
                $rowComment = $_SESSION['school_ashiato_commentadd'];
                //add comment
                $rst = $mbllRemoteApi->addAshiato($uid, $profileUid, $rowComment['content']);
                $_SESSION['school_ashiato_commentadd'] = null;
                unset($_SESSION['school_ashiato_commentadd']);
            }
    	}

    	$this->view->step = $step;
    	$this->render();
    }

	/**
     * fet day name
     *
     */
    private function _getDayName($did)
    {
        $dayArray = array(0 => '日', 1 => '月', 2 => '火', 3 => '水', 4 => '木', 5 => '金', 6 => '土');

        return $dayArray[$did];
    }

	/**
     * get class vote item arrays
     *
     * @param string $category
     * @param boolean $addSel
     * @return array
     */
    private function _getClassVoteItemArray($category, $addSel = false)
    {
    	$result = array();
        if ($addSel) {
            $result[0] = '選択してください';
        }

        $aryContent =   array(1 => 'もの足りない', 2 => '少し物足りない', 3 => '普通', 4 => '充実している', 5 => 'かなり充実');
        $aryDifficult = array(1 => 'かなり難しい', 2 => '難しい', 3 => '普通', 4 => '余裕', 5 => 'かなり余裕');
        $aryWork =      array(1 => 'ほぼ無し', 2 => '少なめ', 3 => '普通', 4 => 'ちょっと多め', 5 => '宿題多すぎ');
        $aryTest =      array(1 => '超難関', 2 => '厳しい', 3 => '普通', 4 => '優しい', 5 => '超簡単');
        $aryAttend =    array(1 => 'かなり厳しい', 2 => '厳しい', 3 => '普通', 4 => '優しい', 5 => 'ほぼ出欠なし');

        if ('vote_content' == $category) {
            $result = $result + $aryContent;
        }
        else if ('vote_difficult' == $category) {
            $result = $result + $aryDifficult;
        }
        else if ('vote_work' == $category) {
            $result = $result + $aryWork;
        }
        else if ('vote_test' == $category) {
            $result = $result + $aryTest;
        }
        else if ('vote_attend' == $category) {
            $result = $result + $aryAttend;
        }

        return $result;
    }

    /**
     * get class vote item text by item category and star
     *
     * @param string $category
     * @param integer $star
     * @return string
     */
    private function _getClassVoteItemText($category, $star)
    {
    	$result = '';

        $aryContent =   array(1 => 'もの足りない', 2 => '少し物足りない', 3 => '普通', 4 => '充実している', 5 => 'かなり充実');
        $aryDifficult = array(1 => 'かなり難しい', 2 => '難しい', 3 => '普通', 4 => '余裕', 5 => 'かなり余裕');
        $aryWork =      array(1 => 'ほぼ無し', 2 => '少なめ', 3 => '普通', 4 => 'ちょっと多め', 5 => '宿題多すぎ');
        $aryTest =      array(1 => '超難関', 2 => '厳しい', 3 => '普通', 4 => '優しい', 5 => '超簡単');
        $aryAttend =    array(1 => 'かなり厳しい', 2 => '厳しい', 3 => '普通', 4 => '優しい', 5 => 'ほぼ出欠なし');

        if ('vote_content' == $category) {
            $result = $aryContent[$star];
        }
        else if ('vote_difficult' == $category) {
            $result = $aryDifficult[$star];
        }
        else if ('vote_work' == $category) {
            $result = $aryWork[$star];
        }
        else if ('vote_test' == $category) {
            $result = $aryTest[$star];
        }
        else if ('vote_attend' == $category) {
            $result = $aryAttend[$star];
        }

        return $result;
    }

	/**
     * get class forecast status by user and class
     *
     * @param integer uid
     * @param integer $cid
     * @param array $aryFids
     * @return integer [0-晴れ/1-曇り/2-雨]
     */
    private function _getClassForecastStatus($uid, $cid, $aryFids)
    {
        require_once 'Mdal/School/Timepart.php';
        require_once 'Mdal/School/Class.php';
        $mdalTimepart = Mdal_School_Timepart::getDefaultInstance();
        $mdalClass = Mdal_School_Class::getDefaultInstance();

        $rtnStatus = 0;
        $badCount = $mdalClass->getClassForecastCount($cid, true);
        $goodCount = $mdalClass->getClassForecastCount($cid, false);
        if ($badCount == 0) {
            $rtnStatus = 0;
        }
        else if ($badCount >= $goodCount) {
            $rtnStatus = 2;
        }
        else {
            $aryFids[] = $uid;
            if ($mdalClass->getClassFriendBadForecastCount($cid, $aryFids) >= 1
                || $mdalClass->getClassNotFriendBadForecastCount($cid, $aryFids) >= 2) {
                $rtnStatus = 1;
            }
        }
        return $rtnStatus;
    }

	/**
     * get class schedule name
     *
     * @param array $date
     * @return string
     */
    private function _getScheduleName($date)
    {
        $weekdays = array(0 => '日', 1 => '月', 2 => '火', 3 => '水', 4 => '木', 5 => '金', 6 => '土');
        $wday = (int)$date['wday'];
        return $date['mon'] . '月' . $date['mday'] . '日(' . $weekdays[$wday] . ')の時間割';
    }

    /**
     * get current timepart's class name by user
     *
     * @param integer $uid
     * @return string
     */
    private function _getCurrentTimepartClassNameByUid($uid)
    {
        require_once 'Mdal/School/Timepart.php';
        require_once 'Mdal/School/Class.php';

        $className = '授業なし';
        //find current part
        $mdalTimepart = Mdal_School_Timepart::getDefaultInstance();
        if ($mdalTimepart->hasTimepartSchedule($uid)) {
            $timepart = 0;
            $lstTimepart = $mdalTimepart->listUserTimepartUsed($uid);
            foreach ($lstTimepart as $timeData) {
                $strBeginTime = $timeData['start_h'] . ':' . $timeData['start_m'];
                $strEndTime = strftime('%H:%M', strtotime($strBeginTime) + $timeData['part_minutes'] * 60);
                $strNow = strftime('%H:%M', time());
                //in now time's class
                if ($strNow >= $strBeginTime && $strNow <= $strEndTime) {
                    $timepart = $timeData['part'];
                    break;
                }
            }//end find current part
            if (!empty($timepart)) {
                $now = getdate();
                $rowNowClass = $mdalTimepart->getTimepartScheduleByPk($uid, $now['wday'], $timepart);
                if (!empty($rowNowClass)) {
                    $mdalClass = Mdal_School_Class::getDefaultInstance();
                    $rowClass = $mdalClass->getClassInfo($rowNowClass['cid']);
                    $className =  empty($rowClass) ? '授業なし' : $rowClass['name'];
                }
            }
        }
        else {
            $className = '時間割未編集';
        }
        return $className;
    }

	/**
     * get next class schedule data array
     *
     * @param integer $uid
     * @param integer $cid
     * @return array
     */
    private function _getNextClassDateArray($uid, $cid)
    {
    	$timePart = array();

        require_once 'Mdal/School/Timepart.php';
        $mdalTimepart = Mdal_School_Timepart::getDefaultInstance();
        $lstSchedule = $mdalTimepart->lstUserScheduleByCid($uid, $cid);
        if (!empty($lstSchedule) && count($lstSchedule) > 0) {
            $now = getdate();
            $nextDay = 0;
            $nextPart = 0;
            foreach ($lstSchedule as $schedule) {
                if ($schedule['wday'] >= $now['wday']) {
                    $nextDay = $schedule['wday'];
                    $nextPart = $schedule['part'];
                    break;
                }
            }
            if (empty($nextDay)) {
                $nextDay = $lstSchedule[0]['wday'];
                $nextPart = $lstSchedule[0]['part'];
            }
            //next date and time
            if ($nextDay == $now['wday']) {
                $nextDate = $now['mday'];
                $nextMonth = $now['mon'];
            }
            else {
                $nextDate = 0;
                $basTime = $newTime = $now[0];
                while ($newNow['wday'] != $nextDay) {
                    $newTime += 60*60*24;
                    $newNow = getdate($newTime);
                }
                $nextDate = $newNow['mday'];
                $nextMonth = $newNow['mon'];
            }
            $timePart= $mdalTimepart->getUserTimepart($uid, $nextPart);
            $strBeginTime = $timePart['start_h'] . ':' . $timePart['start_m'];
            $timePart['end_h'] = strftime('%H', strtotime($strBeginTime) + $timePart['part_minutes'] * 60);
            $timePart['end_m'] = strftime('%M', strtotime($strBeginTime) + $timePart['part_minutes'] * 60);
            $timePart['wday'] = $this->_getDayName($nextDay);
            $timePart['wdayInt'] = $nextDay;
            $timePart['day'] = $nextDate;
            $timePart['month'] = $nextMonth;
        }

        return $timePart;
    }


/** xial
 * ***************************************************************
 */
    /**
     * select class member
     *
     * @return unknown
     */
    public function classmemberAction()
    {
		$uid = $this->_user->getId();
        $cid = $this->getParam('CF_cid', 1);
        $pageIndex = $this->getParam('CF_page', 1);

        require_once 'Mdal/School/Class.php';
        $mdalClass = Mdal_School_Class::getDefaultInstance();

        $classInfo = $mdalClass->getClassInfo($cid);
    	if (empty($classInfo)) {
        	return $this->_redirect($this->_baseUrl . '/mobile/school/error');
        }

    	//class member check
        if (!$mdalClass->isClassMember($cid, $uid)) {
            return $this->_redirect($this->_baseUrl . '/mobile/school/home');
        }

		require_once 'Mbll/School/Class.php';
        $mbllClass = new Mbll_School_Class();

        $memberLst = $mbllClass->lstClassMember($cid, $uid, $pageIndex, $this->_pageSize);
		$count = $classInfo['member_count'];

		 //get start number and end number
        $start = ($pageIndex - 1) * $this->_pageSize;
        $this->view->startNm = $start + 1;
        $this->view->endNm = ($start + $this->_pageSize) > $count ? $count : ($start + $this->_pageSize);

        //next class schedule
        $this->view->nextSchedule = $this->_getNextClassDateArray($uid, $cid);

        //get pager info
        $this->view->pager = array('count' => $count,
                                   'pageIndex' => $pageIndex,
                                   'requestUrl' => 'mobile/school/classmember',
                                   'pageSize' => $this->_pageSize,
                                   'maxPager' => ceil($count / $this->_pageSize),
                                   'pageParam' => '&CF_cid=' . $cid
                                   );

        $this->view->classInfo = $classInfo;
        $this->view->memberLst = $memberLst;
        $this->render();
    }

    /**
     * send class invite
     *
     * @return unknown
     */
    public function classinviteAction()
    {
		$uid = $this->_user->getId();
        $pageIndex = $this->getParam('CF_page', 1);
        $cid = $this->getParam('CF_cid');
		$step = $this->getParam('CF_step', 'start');

        require_once 'Bll/Friend.php';
        require_once 'Bll/User.php';
        require_once 'Mdal/School/User.php';
        require_once 'Mdal/School/Class.php';
        require_once 'Mdal/School/Message.php';

        $mdalUser = Mdal_School_User::getDefaultInstance();
		$mdalClass = Mdal_School_Class::getDefaultInstance();
		$mdalMessage = Mdal_School_Message::getDefaultInstance();

    	//class member check
        if (!$mdalClass->isClassMember($cid, $uid)) {
            return $this->_redirect($this->_baseUrl . '/mobile/school/home');
        }

        //next class schedule
		$nextSchedule = $this->_getNextClassDateArray($uid, $cid);
        $this->view->nextSchedule = $nextSchedule;

        if ($step == 'start') {
        	$lstFriend = null;
	        $cntFriend = 0;
	        $aryFids = Bll_Friend::getFriends($uid);
	        $rowSchoolUser = $this->_schoolUser;
	        $sort = 'create_time DESC';
	        if (!empty($aryFids)) {
	            $lstFriend = $mdalUser->listSchoolFriendIds($rowSchoolUser['school_code'], $uid, $aryFids, $pageIndex, $this->_pageSize, $sort);
	            $cntFriend = $mdalUser->getSchoolFriendIdsCount($rowSchoolUser['school_code'], $uid, $aryFids);
	            Bll_User::appendPeople($lstFriend, 'uid');
	            foreach ($lstFriend as $key => $fdata) {
	            	$is_In = 0;
	            	$row = $mdalClass->isClassMember($cid, $fdata['uid']);
	            	if ($row) {
	            		$is_In = 1;
	            	}
	            	$lstFriend[$key]['is_In'] = $is_In;
	            	$lstFriend[$key]['is_friend'] = Bll_Friend::isFriend($uid, $fdata['uid']) ? '1' : '0';
	            }
	        }
	        $this->view->listFriend = $lstFriend;

	        //get start number and end number
	        $start = ($pageIndex - 1) * $this->_pageSize;
	        $this->view->startNm = $start + 1;
	        $this->view->endNm = ($start + $this->_pageSize) > $cntFriend ? $cntFriend : ($start + $this->_pageSize);

	        //get pager info
	        $this->view->pager = array('count' => $cntFriend,
	                                   'pageIndex' => $pageIndex,
	                                   'requestUrl' => 'mobile/school/classinvite',
	                                   'pageSize' => $this->_pageSize,
	                                   'maxPager' => ceil($cntFriend / $this->_pageSize),
	                                   'pageParam' => '&CF_cid=' . $cid
	                                   );
        } else {
        	require_once 'Mbll/School/Class.php';
        	$mbllClass = new Mbll_School_Class();

			$target_uid= $this->getParam('CF_fid');

        	$message = '';
        	require_once 'Bll/User.php';
			$fInfo = Bll_User::getPerson($target_uid);

			require_once 'Mdal/School/Timepart.php';
			$mdalTimepart = Mdal_School_Timepart::getDefaultInstance();
			//check $target_uid is add timepart
        	$isExists = $mdalTimepart->isTimepartScheduleExists($target_uid, $nextSchedule['wdayInt'], $nextSchedule['part'], $cid);
			if ($isExists) {
				$message = $fInfo->getDisplayName() . 'さんを登録しました｡';
			} else {
				$messageAry = array('type' => 1,
								'uid' => $uid,
								'object_id' => $cid,
								'target_uid' =>$target_uid);

        		$isId = $mdalMessage->isInviteClassExites($messageAry);
	            if ($isId) {
	            	//is exists update create_time
					$result = $mdalMessage->updateMessage(array('create_time' => time()), $isId);
	            } else {
					$info = array(	'uid' => $uid,
									'target_uid' => $target_uid,
									'type' => 1,
									'object_id' => $cid,
									'wday' => $nextSchedule['wdayInt'],
									'part' => $nextSchedule['part'],
									'create_time' => time());

					$result = $mbllClass->inviteClass($info);
	            }

	        	if ($result == 1) {
					$message = $fInfo->getDisplayName() . 'さんを招待しました｡';
				} elseif (empty($result)) {
					$message = $fInfo->getDisplayName() . 'さんを失敗しました｡';
				}
			}

			$this->view->message = $message;
        }

        $classInfo = $mdalClass->getClassInfo($cid);
		$this->view->step = $step;
		$this->view->classInfo = $classInfo;
        $this->render();
    }

    /**
     * my schedule
     *
     */
    public function scheduleAction()
    {
    	$uid =  $this->_user->getId();
    	$now = getdate();
    	$wday = $this->getParam('CF_wday', $now['wday']);
    	$wday = empty($wday) ? 1 : $wday;
    	$weekdays = array(1 => 'mon', 2 => 'tue', 3 => 'wed', 4 => 'thu', 5 => 'fri', 6 => 'sat');
		$dy = $weekdays[$wday];

        require_once 'Mdal/School/Timepart.php';
        $mdalTimepart = Mdal_School_Timepart::getDefaultInstance();
		$this->view->wdayImage = $dy;
		$this->view->wday = $wday;

		//schedule info
        $lstTimepart = $mdalTimepart->listUserTimepart($uid);
        foreach ($lstTimepart as $key=>$timeData) {
            $lstTimepart[$key]['c_name'] =  $key + 1 .'限:未登録';
            $lstTimepart[$key]['cid'] = 0;
            $rowTimepartClass = $mdalTimepart->getTimepartScheduleByPk($uid, $wday, $timeData['part']);
            $lstTimepart[$key]['model'] = 1;
            if (!empty($rowTimepartClass)) {
                $mdalClass = Mdal_School_Class::getDefaultInstance();
                $rowClass = $mdalClass->getClassInfo($rowTimepartClass['cid']);
                $lstTimepart[$key]['c_name'] =  empty($rowClass) ? $key + 1 .'限:' . '未登録' : $key + 1 .'限:' . $rowClass['name'];
                $lstTimepart[$key]['cid'] = empty($rowClass) ? 0 : $rowClass['cid'];
                $lstTimepart[$key]['model'] = 2;
            }

            if ($timeData['is_hide'] == 1 && empty($rowTimepartClass)) {
            	$lstTimepart[$key]['c_name'] =  $key + 1 .'限:授業なし';
            	$lstTimepart[$key]['model'] = 3;
            }
        }
        $this->view->mineSchedule = $lstTimepart;
		$this->render();
    }

    /**
     * add new class (set class name)
     *
     * @return unknown
     */
	public function classnameaddAction()
    {
		$uid =  $this->_user->getId();
		$pageIndex = $this->getParam('CF_page', 1);
    	$now = getdate();
    	$wday = $this->getParam('CF_wday', $now['wday']);
    	$wday = empty($wday) ? 1 : $wday;
    	$part = $this->getParam('CF_part');

    	require_once 'Mdal/School/Timepart.php';
        $mdalTimepart = Mdal_School_Timepart::getDefaultInstance();
		require_once 'Mdal/School/Class.php';
        $mdalClass = Mdal_School_Class::getDefaultInstance();

    	$rowTimepart = $mdalTimepart->getUserTimepart($uid, $part);
		if ($rowTimepart['is_hide'] && empty($rowTimepart['start_h']) && empty($rowTimepart['start_m'])) {
			$timePartMax = $mdalTimepart->getMaxPartIshideById($uid);
			if ($timePartMax['part'] > $part) {
				return $this->_redirect($this->_baseUrl . '/mobile/school/error?CF_error=notadd');
			}
		}

    	if (empty($wday) && empty($part)) {
    		return $this->_redirect($this->_baseUrl . '/mobile/school/error');
    	}

    	//check session is null
    	if ($_SESSION['school_aryClassAdd'] != null) {
    		//get session array
    		$sessionAry = $_SESSION['school_aryClassAdd'];
			$this->view->cname = $sessionAry['classname'];
			$this->view->errorMsg = $sessionAry['CF_error'];
			//clear session
			$_SESSION['school_aryClassAdd'] = null;
			unset($_SESSION['school_aryClassAdd']);
		}

		$this->view->day = $this->_getDayName($wday);
		$this->view->part = $part;
		$this->view->wday = $wday;

        //get school user info
        $rowSchoolUser = $this->_schoolUser;
        $this->view->mineInfo = $rowSchoolUser;

        $lstCids = $mdalTimepart->getLstCidsById($rowSchoolUser['school_code'], $uid, $wday, $part, $pageIndex, $this->_pageSize);
        if (!empty($lstCids)) {
            $count = $mdalTimepart->getCntScheduleClassById($rowSchoolUser['school_code'], $uid, $wday, $part);
            //get currrent day parttime's class
            foreach ($lstCids as $key=>$cdata) {
            	//get class infomation
                $classInfo = $mdalClass->getClassInfo($cdata['cid']);
                if ($classInfo) {
			        $lstCids[$key]['c_name'] = $classInfo['name'];
			        $lstCids[$key]['c_teacher'] = $classInfo['teacher'];
            	}
			}
        }
        $this->view->lstCid = $lstCids;

        //get start number and end number
        $start = ($pageIndex - 1) * $this->_pageSize;
        $this->view->startNm = $start + 1;
        $this->view->endNm = ($start + $this->_pageSize) > $count ? $count : ($start + $this->_pageSize);

        //get pager info
        $this->view->pager = array('count' => $count,
                                   'pageIndex' => $pageIndex,
                                   'requestUrl' => 'mobile/school/classnameadd',
                                   'pageSize' => $this->_pageSize,
                                   'maxPager' => ceil($count / $this->_pageSize),
                                   'pageParam' => '&CF_wday=' . $wday . '&CF_part=' . $part
                                   );
        $this->render();
    }

    /**
     * add new class (set teacher name)
     *
     * @return unknown
     */
     public function classteacheraddAction()
    {
		$pageIndex = $this->getParam('CF_page', 1);
		$step = $this->getParam('CF_step', 'start');

    	$wday = $this->getParam('CF_wday');
    	$part = $this->getParam('CF_part');
    	$classname = trim($this->getParam('classNameText'), ' ');

    	require_once 'Mdal/School/Class.php';
        $mdalClass = Mdal_School_Class::getDefaultInstance();

    	if (empty($wday) && empty($part)) {
    		return $this->_redirect($this->_baseUrl . '/mobile/school/error');
    	}

		$rowSchoolUser = $this->_schoolUser;
    	if ($step == 'start') {
    		$model = $this->getParam('CF_model');
    		//save session array
	    	$sessionAry = array();
	    	$errorMsg = '';
	    	$url = '';
	    	$actionUrl = '';
	    	if ($model == 'add') {
	    		$url = '/mobile/school/classnameadd?CF_wday=' . $wday . '&CF_part=' . $part;
	    		$actionUrl = 'classnameadd';
	    		$sessionAry['actionUrl'] = 'classnameadd';
	    	} elseif ($model == 'eait') {
	    		$cid = $this->getParam('CF_cid');
	    		$url = '/mobile/school/scheduleedit?CF_cid=' . $cid . '&CF_wday=' . $wday . '&CF_part=' . $part;
	    		$this->view->cid = $cid;
	    		$actionUrl = 'scheduleedit';
	    		$sessionAry['actionUrl'] = 'scheduleedit';
	    	}

	    	if (empty($classname)) {
	    		$errorMsg = '･授業名が未入力です｡';
	    	}

    		if ($classname) {
	    		//半角カナに変換してください
				$classname = mb_convert_kana($classname, "k", "UTF-8");
	    		$isExistes = $mdalClass->getCidByName($classname, $rowSchoolUser['school_code']);
				if ($isExistes) {
					$sessionAry['classname'] = $classname;
					$sessionAry['wday'] = $wday;
					$sessionAry['part'] = $part;
					$sessionAry['action'] = $actionUrl;
					$sessionAry['cid'] = $cid;
					$_SESSION['school_aryClassSame'] = $sessionAry;
					//return $this->_redirect($this->_baseUrl . '/mobile/school/classsameconfirm?CF_cname=' . $classname . '&CF_wday=' . $wday . '&CF_part=' . $part . '&CF_action=' . $actionUrl . '&CF_cid=' . $cid);
					return $this->_redirect($this->_baseUrl . '/mobile/school/classsameconfirm');
				}
	    	}

	    	if (!empty($classname) && mb_strlen($classname, 'UTF-8') < 2) {
	    		$errorMsg .= (empty($errorMsg)?'':"\n") . '･授業名は２文字以上追加する必要あり｡';
	    	}

			//check classname length, classname length must < 30
    		$truncatePostClassName = MyLib_String::truncate($classname, 30);
			if (!empty($classname) && mb_strlen($truncatePostClassName, 'UTF-8') > 30) {
				$errorMsg .= (empty($errorMsg)?'':"\n") . '･全角30文字以内で入力してください｡';
	 		}

			require_once 'Mbll/Emoji.php';
	 		$bllEmoji = new Bll_Emoji();
	 		$emojiClassName = $bllEmoji->escapeEmoji($classname, true);
	   		if (!empty($classname) && ($emojiClassName != $classname)) {
				$errorMsg .= (empty($errorMsg)?'':"\n") . '･絵文字は入力できません｡';
	 		}

	 		if ($errorMsg) {
	 			$sessionAry['classname'] = $classname;
	 			$sessionAry['CF_error'] = $errorMsg;
				$_SESSION['school_aryClassAdd'] = $sessionAry;
	 			$this->_redirect($this->_redirect($this->_baseUrl . $url));
	 			return;
	 		}

	 		$this->view->actionUrl = $actionUrl;

			//半角カナに変換してください
			$classname = mb_convert_kana($classname, "k", "UTF-8");
			//「全角」英字を「半角」に変換します。
			$classname = mb_convert_kana($classname, "r", "UTF-8");
			//「全角」数字を「半角」に変換します。
			$classname = mb_convert_kana($classname, "n", "UTF-8");

			$classname = $bllEmoji->escapeEmoji($classname, true);
	 		//set value
			$sessionAry['classname'] = $classname;
	 		$_SESSION['school_aryClassAdd'] = $sessionAry;
    	}
    	//ｷｬﾝｾﾙ or have error tips
    	elseif ($step == 'update' || $step == 'error'){
    		//get session
        	if ($_SESSION['school_aryClassAdd'] != null) {
	    		$sessionAry = $_SESSION['school_aryClassAdd'];
	    		$classname = $sessionAry['classname'];
	    		$this->view->teachername = $sessionAry['teachername'];
	    		$this->view->actionUrl = $sessionAry['actionUrl'];
				$this->view->errorMsg = $sessionAry['CF_teacherError'];
	    	}
    	}

    	$this->view->day = $this->_getDayName($wday);
		$this->view->part = $part;
		$this->view->wday = $wday;

 		$likeName = '';
 		if (mb_strlen($classname, 'UTF-8') >= 2 && mb_strlen($classname, 'UTF-8') < 4) {
 			//２-３文字の場合: 「先頭1文字」
 			$likeName = mb_substr($classname, 0, 1, 'UTF-8');
 		} elseif (mb_strlen($classname, 'UTF-8') > 3){
 			//４文字以上の場合: 「先頭3文字」
			$likeName = mb_substr($classname, 0, 3, 'UTF-8');
 		}
		//$likeName = urlencode($likeName);

		//$allLstCids = $mdalTimepart->getLstAllCidsById($rowSchoolUser['school_code'], $uid, $wday, $part);
        $lstName = $mdalClass->getlstLikeNameById($rowSchoolUser['school_code'], $likeName, $pageIndex, $this->_pageSize);

        if (!empty($lstName)) {
            $count = $mdalClass->getLikeNameCountById($rowSchoolUser['school_code'], $likeName);
        }

        //get start number and end number
        $start = ($pageIndex - 1) * $this->_pageSize;
        $this->view->startNm = $start + 1;
        $this->view->endNm = ($start + $this->_pageSize) > $count ? $count : ($start + $this->_pageSize);
        //get pager info
        $this->view->pager = array('count' => $count,
                                   'pageIndex' => $pageIndex,
                                   'requestUrl' => 'mobile/school/classteacheradd',
                                   'pageSize' => $this->_pageSize,
                                   'maxPager' => ceil($count / $this->_pageSize),
                                   'pageParam' => '&CF_wday=' . $wday . '&CF_part=' . $part . '&classNameText=' . $classname
                                   );

		$this->view->classname = $classname;
        $this->view->lstName = $lstName;

		$this->render();
    }

    /**
     * schedule add confirm
     *
     * @return unknown
     */
    public function scheduleaddconfirmAction()
	{
		$uid =  $this->_user->getId();
		$wday = $this->getParam('CF_wday');
    	$part = $this->getParam('CF_part');
		$action = $this->getParam('CF_action');
		$cid = $this->getParam('CF_cid');
		//message id
    	$id = $this->getParam('CF_id');
    	$teachername = trim($this->getParam('classNameTeacher'), ' ');

		if ($action == 'classnameadd') {
			$model = 'add';
		} elseif ($action == 'scheduleedit') {
			$model = 'eait';
		} elseif ($action == 'inviteclass') {
			$model = 'invite';
		} elseif ($action == 'profile') {
			$model = 'profile';
		}

		if ($action == 'classnameadd' || $action == 'scheduleedit') {
	    	$sessionAry = array();
	    	$errorMsg = '';
	    	$url = '/mobile/school/classteacheradd?CF_cid=' . $cid . '&CF_model=' . $model . '&CF_wday=' . $wday . '&CF_part=' . $part;

	    	if (empty($teachername)) {
	    		$errorMsg = '･講師名が未入力です｡';
	    	}

			//check classname length, classname length must < 30
			$truncatePostTeacherName = MyLib_String::truncate($teachername, 30);
			if (!empty($teachername) && ($truncatePostTeacherName != $teachername)) {
				$errorMsg .= (empty($errorMsg)?'':"\n") . '･全角30文字以内で入力してください｡';
	 		}

			require_once 'Mbll/Emoji.php';
	 		$bllEmoji = new Bll_Emoji();
	 		$emojiTeacherName = $bllEmoji->escapeEmoji($teachername, true);

	   		if (!empty($teachername) && ($emojiTeacherName != $teachername)) {
				$errorMsg .= (empty($errorMsg)?'':"\n") . '･絵文字は入力できません｡';
	 		}

			if ($_SESSION['school_aryClassAdd'] != null) {
	    		//get session array
	    		$sessionAry = $_SESSION['school_aryClassAdd'];
	    		//set teachername
	    		$sessionAry['teachername'] = $teachername;
	    		//save session
	    		$_SESSION['school_aryClassAdd'] = $sessionAry;

	    		$this->view->teachername = $teachername;
		    	$this->view->classname = $sessionAry['classname'];
	    	}

	 		if ($errorMsg) {
	 			//set teachername
	    		$sessionAry['teachername'] = $teachername;
	    		$sessionAry['CF_teacherError'] = $errorMsg;
	    		$sessionAry['actionUrl'] = $action;
	    		//save session
	    		$_SESSION['school_aryClassAdd'] = $sessionAry;
	    		return $this->_redirect($this->_baseUrl . $url . '&CF_step=update');
	 		}
		}
		//inviteclass action
        elseif ($action == 'inviteclass') {
        	require_once 'Mdal/School/Timepart.php';
        	$mdalTimepart = Mdal_School_Timepart::getDefaultInstance();
        	$rowTimepartClass = $mdalTimepart->getTimepartScheduleByPk($uid, $wday, $part);
			//schedule is exists
			if ($rowTimepartClass) {
	        	require_once 'Mdal/School/Class.php';
	        	$mdalClass = Mdal_School_Class::getDefaultInstance();
				$newInfo = $mdalClass->getClassInfo($cid);
				$oldInfo = $mdalClass->getClassInfo($rowTimepartClass['cid']);
				$this->view->oldInfo = $oldInfo;
				$this->view->newInfo = $newInfo;
				$action = 'scheduleedit';
				$model = 'invite';
			}
			else {
				$url = $this->_baseUrl .'/mobile/school/scheduleaddfinish?CF_action=classnameadd';
				$url .= '&CF_id='. $id .'&CF_cid=' . $cid . '&CF_wday=' . $wday . '&CF_part=' . $part;
				return $this->_redirect($url);
			}
        }

        elseif ($action == 'profile') {
        	$fid = $this->getParam('CF_fid');

        	require_once 'Mdal/School/Class.php';
        	$mdalClass = Mdal_School_Class::getDefaultInstance();
			$newInfo = $mdalClass->getClassInfo($cid);
			$this->view->newInfo = $newInfo;
			$this->view->fid = $fid;
			$action = 'classnameadd';
        }

		$this->view->day = $this->_getDayName($wday);
		$this->view->part = $part;
		$this->view->wday = $wday;

    	$this->view->action = $action;
    	$this->view->model = $model;
    	$this->view->cid = $cid;
    	$this->view->id = $id;
		$this->render();
    }

    public function classsameconfirmAction()
    {
        $step = $this->getParam('CF_step', 'start');
        $pageIndex = $this->getParam('CF_page', 1);

        if($_SESSION['school_aryClassSame'] != null) {
			$sessionAry = $_SESSION['school_aryClassSame'];
			$wday = $sessionAry['wday'];
	    	$part = $sessionAry['part'];
	        $cname = $sessionAry['classname'];
	        $action = $sessionAry['action'];
	        $cid = $sessionAry['cid'];

	        //clear session
			$_SESSION['school_aryClassSame'] = null;
			unset($_SESSION['school_aryClassSame']);
		} else {
			$wday = $this->getParam('CF_wday');
	    	$part = $this->getParam('CF_part');
	        $cname = $this->getParam('CF_cname');
	        $action = $this->getParam('CF_action');
	        $cid = $this->getParam('CF_cid');
		}

		if ($step == 'start') {
			$rowSchoolUser = $this->_schoolUser;
			require_once 'Mdal/School/Class.php';

	        $mdalClass = Mdal_School_Class::getDefaultInstance();
	        $cnameLst = $mdalClass->getLstClassNameByName($rowSchoolUser['school_code'], $cname, $pageIndex, $this->_pageSize);
	        $count = $mdalClass->getCntClassNameByName($rowSchoolUser['school_code'], $cname);
			//get start number and end number
	        $start = ($pageIndex - 1) * $this->_pageSize;
	        $this->view->startNm = $start + 1;
	        $this->view->endNm = ($start + $this->_pageSize) > $count ? $count : ($start + $this->_pageSize);

	        $ua = Zend_Registry::get('ua');
			if ( $ua == 1 ){
	        	$tempName = urlencode(mb_convert_encoding($cname, 'SJIS','UTF-8'));
			} else {
				$tempName = urlencode($cname);
			}
	        //get pager info
	        $this->view->pager = array('count' => $count,
	                                   'pageIndex' => $pageIndex,
	                                   'requestUrl' => 'mobile/school/classsameconfirm',
	                                   'pageSize' => $this->_pageSize,
	                                   'maxPager' => ceil($count / $this->_pageSize),
	                                   'pageParam' => '&CF_cname=' . $tempName . '&CF_wday=' . $wday . '&CF_part=' . $part . '&CF_action=' . $action . '&CF_cid=' . $cid
	                                   );

			$this->view->cnameLst = $cnameLst;

			if ($_SESSION['school_aryClassAdd'] != null) {
	    		$sessionAry = $_SESSION['school_aryClassAdd'];
	    		$this->view->teachername = $sessionAry['teachername'];
				$this->view->errorMsg = $sessionAry['CF_teacherError'];
	    		//clear session
				$_SESSION['school_aryClassAdd'] = null;
				unset($_SESSION['school_aryClassAdd']);
	    	}

		} elseif ($step == 'confirm') {
			$teachername = $this->getParam('classNameTeacher');
			$url = '/mobile/school/classsameconfirm?CF_cname=' . $cname . '&CF_wday=' . $wday . '&CF_part=' . $part . '&CF_action=' . $action . '&CF_cid=' . $cid;
	    	if (empty($teachername)) {
	    		$errorMsg = '･講師名が未入力です｡';
	    	}

			//check classname length, classname length must < 30
			$truncatePostTeacherName = MyLib_String::truncate($teachername, 30);
			if (!empty($teachername) && ($truncatePostTeacherName != $teachername)) {
				$errorMsg .= (empty($errorMsg)?'':"\n") . '･全角30文字以内で入力してください｡';
	 		}

			require_once 'Mbll/Emoji.php';
	 		$bllEmoji = new Bll_Emoji();
	 		$emojiTeacherName = $bllEmoji->escapeEmoji($teachername, true);

	   		if (!empty($teachername) && ($emojiTeacherName != $teachername)) {
				$errorMsg .= (empty($errorMsg)?'':"\n") . '･絵文字は入力できません｡';
	 		}

	 		//set teachername
	    	$sessionAry['teachername'] = $teachername;
	 		if ($errorMsg) {
	    		$sessionAry['CF_teacherError'] = $errorMsg;
	    		//save session
	    		$_SESSION['school_aryClassAdd'] = $sessionAry;
	    		return $this->_redirect($this->_baseUrl . $url);
	 		}
	 		$sessionAry['classname'] = $cname;
	 		//save session
	    	$_SESSION['school_aryClassAdd'] = $sessionAry;
	 		$this->view->teachername = $teachername;
		}
		elseif ($step == 'rollback') {
			$sessionAry['classname'] = $cname;

    		//save session
    		$_SESSION['school_aryClassAdd'] = $sessionAry;

			$url = '';
			if ($action == 'classnameadd') {
				$url = '/mobile/school/classnameadd?CF_wday=' . $wday . '&CF_part=' . $part;
			} elseif ($action == 'scheduleedit') {
				$url = '/mobile/school/scheduleedit?CF_wday=' . $wday . '&CF_part=' . $part . '&CF_cid=' . $cid;
			}
			return $this->_redirect($this->_baseUrl . $url);
		}

		$this->view->day = $this->_getDayName($wday);
		$this->view->part = $part;
		$this->view->wday = $wday;
		$this->view->actionUrl = $action;

		$this->view->cid = $cid;
		$this->view->step = $step;
		$this->view->classname = $cname;
		$this->render();
    }

    /**
     * schedule add finish
     *
     * @return unknown
     */
    public function scheduleaddfinishAction()
    {
    	$uid =  $this->_user->getId();
    	$wday = $this->getParam('CF_wday');
    	$part = $this->getParam('CF_part');
		$action = $this->getParam('CF_action');
    	$cid = $this->getParam('CF_cid');
    	//message id
    	$id = $this->getParam('CF_id');

    	if (empty($wday) && empty($part)) {
    		return $this->_redirect($this->_baseUrl . '/mobile/school/error');
    	}

    	require_once 'Mdal/School/Timepart.php';
        $mdalTimepart = Mdal_School_Timepart::getDefaultInstance();
		require_once 'Mdal/School/Class.php';
        $mdalClass = Mdal_School_Class::getDefaultInstance();
        require_once 'Mbll/School/Timepart.php';
        $mbllTimepart = new Mbll_School_Timepart();

        //get school user info
        $rowSchoolUser = $this->_schoolUser;

        $classname = '';
        $teachername = '';
    	if ($_SESSION['school_aryClassAdd'] != null) {
    		$sessionAry = $_SESSION['school_aryClassAdd'];
    		$classname = $sessionAry['classname'];
    		$teachername = $sessionAry['teachername'];
    		//clear session
			$_SESSION['school_aryClassAdd'] = null;
			unset($_SESSION['school_aryClassAdd']);
    	}

        $message = '授業変更完了';
        $classDisplay = '';

        if ($action == 'classnameadd' || $action == 'scheduleedit') {
        	if ($action == 'classnameadd') {
				$rowTimepartClass = $mdalTimepart->getTimepartScheduleByPk($uid, $wday, $part);
				//schedule is exists
				if ($rowTimepartClass) {
					return $this->_redirect($this->_baseUrl . '/mobile/school/error');
				}
        	}

			$aryInfo = array('uid' =>  $uid,
							'wday' => $wday,
							'part' => $part,
							'cid' => $cid,
							'school_code' => $rowSchoolUser['school_code'],
							'tname' => $teachername,
							'cname' => $classname);

	        if ($action == 'classnameadd') {
				$cid = $mbllTimepart->insertTimepartSchedule($aryInfo);
				if ($cid == 'error') {
					return $this->_redirect($this->_baseUrl . '/mobile/school/error?CF_error=notadd');
				}
				$message = '授業追加完了';
	        } else {
				$cid = $mbllTimepart->updateTimePartScheaule($aryInfo);
	        }

			if (empty($cid)) {
				return $this->_redirect($this->_baseUrl . '/mobile/school/error');
			}
			//delete message
        	if ($id && $cid) {
				require_once 'Mdal/School/Message.php';
				$mdalMessage = Mdal_School_Message::getDefaultInstance();
				//delete message
    			$mdalMessage->deleteInvite(array('uid' =>  $uid, 'wday' => $wday, 'part' => $part));
			}
			$cInfo = $mdalClass->getClassInfo($cid);

			$classDisplay = $cInfo['name'] . '(' . $cInfo['teacher'] . ')';

        }
        //delete schedule
        elseif ($action == 'delschedule') {
        	$mbllTimepart->delSchedule($uid, $wday, $part, $cid);
        	$classDisplay = '授業なし';
        }

        elseif ($action == 'delscheduleIsHide') {
        	$aryTimePart = array('is_hide' => 1);
			//$mbllTimepart->updateTimePart($aryTimePart, $uid, $wday, $part);
			$classDisplay = '授業なし';
        }

        $this->view->day = $this->_getDayName($wday);
		$this->view->part = $part;
		$this->view->wday = $wday;

        $this->view->message = $message;
		$this->view->classDisplay = $classDisplay;
		$this->view->action = $action;
		$this->view->cInfo = $cInfo;
		$this->render();
    }

    /**
     * schedule edit action
     *
     * @return unknown
     */
    public function scheduleeditAction()
    {
    	$uid =  $this->_user->getId();
		$pageIndex = $this->getParam('CF_page', 1);
    	$wday = $this->getParam('CF_wday');
    	$part = $this->getParam('CF_part');
    	$cid = $this->getParam('CF_cid');

    	if (empty($wday) && empty($part)) {
    		return $this->_redirect($this->_baseUrl . '/mobile/school/error');
    	}

    	$this->view->day = $this->_getDayName($wday);
		$this->view->part = $part;
		$this->view->wday = $wday;
		$this->view->cid = $cid;

    	require_once 'Mdal/School/Timepart.php';
        $mdalTimepart = Mdal_School_Timepart::getDefaultInstance();
		require_once 'Mdal/School/Class.php';
		$mdalClass = Mdal_School_Class::getDefaultInstance();

		if ($cid) {
			//get class infomation
			$eaitInfo = $mdalClass->getClassInfo($cid);
			$this->view->eaitInfo = $eaitInfo;
		}

    	if ($_SESSION['school_aryClassAdd'] != null) {
    		$sessionAry = $_SESSION['school_aryClassAdd'];
    		$this->view->cname = $sessionAry['classname'];
			$this->view->errorMsg = $sessionAry['CF_error'];
    	}


        //get school user info
        $rowSchoolUser = $this->_schoolUser;
        $this->view->mineInfo = $rowSchoolUser;

        $lstCids = $mdalTimepart->getLstCidsById($rowSchoolUser['school_code'], $uid, $wday, $part, $pageIndex, $this->_pageSize);
        if (!empty($lstCids)) {
            $count = $mdalTimepart->getCntScheduleClassById($rowSchoolUser['school_code'], $uid, $wday, $part);
            //get currrent day parttime's class
            foreach ($lstCids as $key=>$cdata) {
            	//get class infomation
                $classInfo = $mdalClass->getClassInfo($cdata['cid']);
                if ($classInfo) {
			        $lstCids[$key]['c_name'] = $classInfo['name'];
			        $lstCids[$key]['c_teacher'] = $classInfo['teacher'];
            	}
			}
			$this->view->lstCid = $lstCids;
        }

        //get start number and end number
        $start = ($pageIndex - 1) * $this->_pageSize;
        $this->view->startNm = $start + 1;
        $this->view->endNm = ($start + $this->_pageSize) > $count ? $count : ($start + $this->_pageSize);

        //get pager info
        $this->view->pager = array('count' => $count,
                                   'pageIndex' => $pageIndex,
                                   'requestUrl' => 'mobile/school/scheduleedit',
                                   'pageSize' => $this->_pageSize,
                                   'maxPager' => ceil($count / $this->_pageSize),
                                   'pageParam' => '&CF_wday=' . $wday . '&CF_part=' . $part . '&CF_cid=' . $cid
                                   );
		$this->render();
    }

    /**
     * reset schedule action
     *
     */
    public function scheduleresetAction()
	{
    	$uid =  $this->_user->getId();
    	$step = $this->getParam('CF_step', 'start');

		//schedule reset
    	if ($step == 'finish') {
    		//delete all schedule
			require_once 'Mbll/School/Timepart.php';
        	$mbllTimepart = new Mbll_School_Timepart();
			$mbllTimepart->scheduleReset($uid);
    	}

		$this->view->step = $step;
    	$this->render();
    }

    /**
     * invite class action
     *
     */
    public function inviteclassAction()
    {
    	$uid =  $this->_user->getId();
    	$pageIndex = $this->getParam('CF_page', 1);
    	$step = $this->getParam('CF_step', 'start');
    	$mid = $this->getParam('CF_mid');

    	require_once 'Mdal/School/Class.php';
		$mdalClass = Mdal_School_Class::getDefaultInstance();
		require_once 'Mdal/School/Message.php';
		$mdalMessage = Mdal_School_Message::getDefaultInstance();

    	if ($step == 'start') {
	    	if ($mid) {
	    		$mdalMessage->deleteMessage($mid);
	    	}

	        $weekdays = array(1 => '月', 2 => '火', 3 => '水', 4 => '木', 5 => '金', 6 => '土');
			$lstMessage = $mdalMessage->lstInviteMessage($uid, 1, $pageIndex, $this->_pageSize);
			$count = $mdalMessage->getCntInviteMessageById($uid);
			foreach ($lstMessage as $key => $value) {
				$cInfo = $mdalClass->getClassInfo($value['object_id']);
				$lstMessage[$key]['tname'] = $cInfo['teacher'];
				$lstMessage[$key]['cname'] = $cInfo['name'];
				$lstMessage[$key]['day'] = $weekdays[$value['wday']];
			}

			$this->view->lstMessage = $lstMessage;
			//get start number and end number
	        $start = ($pageIndex - 1) * $this->_pageSize;
	        $this->view->startNm = $start + 1;
	        $this->view->endNm = ($start + $this->_pageSize) > $count ? $count : ($start + $this->_pageSize);

			 //get pager info
        	$this->view->pager = array('count' => $count,
                                   'pageIndex' => $pageIndex,
                                   'requestUrl' => 'mobile/school/inviteclass',
                                   'pageSize' => $this->_pageSize,
                                   'maxPager' => ceil($count / $this->_pageSize),
                                   'pageParam' => ''
                                   );
    	} elseif ($step == 'finish') {
			$wday = $this->getParam('CF_wday');
	    	$part = $this->getParam('CF_part');
	    	$cid = $this->getParam('CF_cid');
	    	$id = $this->getParam('CF_id');
			//delete message
	    	$mdalMessage->deleteMessage($id);
	    	$this->view->day = $this->_getDayName($wday);
			$this->view->part = $part;
			$this->view->wday = $wday;
		    $this->view->classInfo = $mdalClass->getClassInfo($cid);
    	}

		$this->view->step = $step;
    	$this->render();
    }

    /**
     * get design list action
     *
     */
    public function designlistAction()
    {
		$uid = $this->_user->getId();
		$pageIndex = $this->getParam('CF_page', 1);
		$pageSize = 9;
		//school design info
        require_once 'Mdal/School/Design.php';
        $mdalDesign = Mdal_School_Design::getDefaultInstance();

        $rowSchoolUser = $this->_schoolUser;
		$lstDesign = $mdalDesign->getlstDesign($uid, $pageIndex, $pageSize);
		$count = $mdalDesign->getCntDesign($uid);

		foreach ($lstDesign as $key => $value) {
			if ($value['did'] == $rowSchoolUser['design_type']) {
				$lstDesign[$key]['type'] = 's';
			} else {
				$lstDesign[$key]['type'] = 'f';
			}
		}

		for ($i = (count($lstDesign)) ; $i < 9; $i ++ ) {
			$lstDesign[$i]['type'] = 'no';
		}

		//get start number and end number
        $start = ($pageIndex - 1) * $pageSize;
        $this->view->startNm = $start + 1;
        $this->view->endNm = ($start + $pageSize) > $count ? $count : ($start + $pageSize);

		 //get pager info
        $this->view->pager = array('count' => $count,
                                   'pageIndex' => $pageIndex,
                                   'requestUrl' => 'mobile/school/designlist',
                                   'pageSize' => $pageSize,
                                   'maxPager' => ceil($count / $pageSize),
                                   'pageParam' => ''
                                   );
		//design db count
		$aryNum = $mdalDesign->getCntNbDesign();
		$this->view->aryNum = count($aryNum);
		$this->view->miniInfo = $rowSchoolUser;
		$this->view->lstDesign = $lstDesign;
		$this->render();
    }

    /**
     * design preview action
     */
	public function designviewAction()
    {
    	$uid = $this->_user->getId();
    	$step = $this->getParam('CF_step', 'view');
    	$did = $this->getParam('CF_did');

		require_once 'Mdal/School/Design.php';
        $mdalDesign = Mdal_School_Design::getDefaultInstance();

		$rowSchoolUser = $this->_schoolUser;

		//design db count
		$aryNum = $mdalDesign->getCntNbDesign();

        if ($step == 'change') {
				if (empty($rowSchoolUser['lottery_chance'])) {
					return $this->_redirect($this->_baseUrl . '/mobile/school/home');
				} else {
					foreach ($aryNum as $value) {
						$nums[] = $value['did'];
					}

					$lstDids = $mdalDesign->getlstDesign($uid, 1, count($aryNum));
					foreach ($lstDids as $value) {
						$nbDids[] = $value['did'];
					}

					$dids = array_diff($nums, $nbDids);
					$did = array_rand($dids, 1) + 1;
					$isDid = $mdalDesign->isDesignExists($uid, $did);
					if (empty($isDid)) {
						$mdalDesign->insertDesign(array('did' => $did, 'uid' => $uid, 'create_time' => time()));
					}

					require_once 'Mbll/School/User.php';
        			$mbllUser = new Mbll_School_User();
					$mbllUser->updateUserLotteryChance($uid);
				}
			$this->view->count = $mdalDesign->getCntDesign($uid);
        }
        elseif ($step == 'lottery') {
        	$mid = $this->getParam('CF_mid');
        	if ($mid) {
        		require_once 'Mdal/School/Message.php';
            	$mdalMessage = Mdal_School_Message::getDefaultInstance();
	    		$mdalMessage->deleteMessage($mid);
	    	}
			if (empty($rowSchoolUser['lottery_chance'])) {
				return $this->_redirect($this->_baseUrl . '/mobile/school/designfinish?CF_step=reset');
			} elseif ($rowSchoolUser['lottery_chance'] == count($aryNum)) {
				return $this->_redirect($this->_baseUrl . '/mobile/school/designfinish?CF_step=lottery');
			}

			$this->view->inviteCnt = $mdalDesign->getInviteCntById($this->_APP_ID, $uid);
        }

        $this->view->aryNum = count($aryNum);
		$this->view->miniInfo = $rowSchoolUser;
    	$this->view->step = $step;
    	$this->view->did = $did;
		$this->render();
    }

    /**
     * update design type finish action
     *
     */
	public function designfinishAction()
    {
    	$uid = $this->_user->getId();
    	$step = $this->getParam('CF_step');
    	$did = $this->getParam('CF_did');

    	//school user info
        require_once 'Mbll/School/User.php';
        $mbllUser = new Mbll_School_User();

    	if ($step == 'lottery') {
			$rowSchoolUser = $this->_schoolUser;
			$this->view->miniInfo = $rowSchoolUser;
    	}
    	elseif ($step == 'finish') {
    		$mbllUser->updateUserDesign($uid, $did);
    	}

    	$this->view->step = $step;
    	$this->view->did = $did;
		$this->render();
    }

    /**
     * topic list action
     *
     */
    public function topicAction()
    {
		$uid = $this->_user->getId();
    	$tid = $this->getParam('CF_tid');
    	$cid = $this->getParam('CF_cid');
		$orderBy = $this->getParam('CF_orderby');

    	$pageIndex = $this->getParam('CF_page', 1);
    	$pageSize = 5;

    	require_once 'Mdal/School/Topic.php';
        $mdalTopic = Mdal_School_Topic::getDefaultInstance();
        $topicInfo = $mdalTopic->getClassTopic($tid);

        require_once 'Mdal/School/Class.php';
        $mdalClass = Mdal_School_Class::getDefaultInstance();
        $classInfo = $mdalClass->getClassInfo($cid);

        if ($topicInfo && $classInfo) {
        	$mdalTopic->updateClassTopic(array('visit_count' => $topicInfo['visit_count'] + 1), $tid);
        	$topicInfo['visit_count'] = $topicInfo['visit_count'] + 1 ;
        } else {
			return $this->_redirect($this->_baseUrl . '/mobile/school/error');
        }

        if ($orderBy == 'good_count') {
        	$orderBy = 'good_count DESC';
        } elseif ($orderBy == 'create_time') {
        	$orderBy = 'create_time ASC';
        }

        $topicLst = $mdalTopic->getLstTopicCommentById($tid, $pageIndex, $pageSize, $orderBy);
		$count = $mdalTopic->getCntTopicCommentById($tid);

		require_once 'Mdal/School/User.php';
        $mdalUser = Mdal_School_User::getDefaultInstance();

		foreach ($topicLst as $key => $value) {
        	$topicLst[$key]['userInfo'] = $mdalUser->getUser($value['uid']);
        	$topicLst[$key]['format_data'] = date('y/m/d H:i', $value['create_time']);
        	$topicLst[$key]['isAssess'] = $mdalTopic->isUserHaveAssessed($value['comment_id'], $uid);
		}

        //get start number and end number
        $start = ($pageIndex - 1) * $pageSize;
        $this->view->startNm = $start + 1;
        $this->view->endNm = ($start + $pageSize) > $count ? $count : ($start + $pageSize);

		 //get pager info
        $this->view->pager = array('count' => $count,
                                   'pageIndex' => $pageIndex,
                                   'requestUrl' => 'mobile/school/topic',
                                   'pageSize' => $pageSize,
                                   'maxPager' => ceil($count / $pageSize),
                                   'pageParam' => '&CF_tid=' . $tid . '&CF_cid=' . $cid . '&CF_orderby=' . $orderBy
                                   );

		$rowSchoolUser = $this->_schoolUser;
		$this->view->miniInfo = $rowSchoolUser;
		//next class schedule
        $this->view->nextSchedule = $this->_getNextClassDateArray($uid, $cid);

		Bll_User::appendPeople($topicLst, 'uid');
        $this->view->topicLst = $topicLst;

    	$this->view->tid = $tid;
    	$this->view->classInfo = $classInfo;
    	$this->view->tInfo = $topicInfo;
    	$this->render();
    }

    /**
     * topic add comment action
     *
     */
    public function topiccommentaddAction()
    {
		$uid = $this->_user->getId();
    	$tid = $this->getParam('CF_tid');
    	$step = $this->getParam('CF_step', 'start');
    	$cid = $this->getParam('CF_cid', 1);

        require_once 'Mdal/School/Topic.php';
        $mdalTopic = Mdal_School_Topic::getDefaultInstance();
        $topicInfo = $mdalTopic->getClassTopic($tid);

    	require_once 'Mdal/School/Class.php';
        $mdalClass = Mdal_School_Class::getDefaultInstance();
        $classInfo = $mdalClass->getClassInfo($cid);

		//$uid == $topicInfo['uid'] ||
        if (empty($topicInfo) || empty($classInfo)) {
			return $this->_redirect($this->_baseUrl . '/mobile/school/error');
        }

    	if ($step == 'start') {
    	 	//from edit mode
            if (isset($_SESSION['school_topic_commentadd']) && $_SESSION['school_topic_commentadd'] != null) {
                //load from session
                $rowComment = $_SESSION['school_topic_commentadd'];
                $comment = $rowComment['comment'];
                $this->view->errMsg = $rowComment['CF_error'];
                $_SESSION['school_topic_commentadd'] = null;
                unset($_SESSION['school_topic_commentadd']);
            }
    	}
    	//add topic comment confrim
    	elseif ($step == 'confirm') {
    		$comment = $this->getParam('classTopicComment');
    		//check validate
            $strMsg = '';
            if (empty($comment)) {
                $strMsg .= "･回答ｺﾒﾝﾄが未入力です｡";
            }
    		else if (mb_strlen($comment, 'UTF-8') > 300) {
                $strMsg .= (empty($strMsg)?'':"\n") . "･300文字以内で入力してください｡";
            }

            require_once 'Mbll/Emoji.php';
            $bllEmoji = new Bll_Emoji();
            $comment = $bllEmoji->escapeEmoji($comment);
			 //save to session
            $arySession = array();
            $arySession['comment'] =  $comment;
            $arySession['tid'] = $tid;
            $arySession['uid'] = $uid;
            $arySession['create_time'] = time();

            if (!empty($strMsg)) {
            	$arySession['CF_error'] = $strMsg;
            	$_SESSION['school_topic_commentadd'] = $arySession;
                $this->_redirect($this->_baseUrl . '/mobile/school/topiccommentadd?CF_cid=' . $cid . '&CF_tid='. $tid);
                return;
            }
            $_SESSION['school_topic_commentadd'] = $arySession;
    	}
    	//add topic comment confrim
    	elseif ($step == 'finish') {
    		if (isset($_SESSION['school_topic_commentadd']) && $_SESSION['school_topic_commentadd'] != null) {
                //load from session
                $rowComment = $_SESSION['school_topic_commentadd'];
				//add to db
                require_once 'Mbll/School/Topic.php';
        		$mbllTopic = new Mbll_School_Topic();
				$result = $mbllTopic->insertTopicComment($rowComment);
				$_SESSION['school_topic_commentadd'] = null;
                unset($_SESSION['school_topic_commentadd']);
    			if ($result) {
    				if ($uid != $topicInfo['uid']) {
	    				require_once 'Mdal/School/Message.php';
	        			$mdalMessage = Mdal_School_Message::getDefaultInstance();
	        			$isExistsId = $mdalMessage->isMessageExites($topicInfo['uid'], 3);
	        			if ($isExistsId) {
	        				$mdalMessage->updateMessage(array('create_time' => time()), $isExistsId);
	        			} else {
		        			$messageAry = array('uid' => $uid,
		        								'target_uid' => $topicInfo['uid'],
		        								'type' => 3, 'object_id' => $tid,
		        								'create_time' => time());
		        			$mdalMessage->insertMessage($messageAry);
	        			}
    				}
				} else {
					return $this->_redirect($this->_baseUrl . '/mobile/school/error');
				}
            }
    	}

    	$rowSchoolUser = $this->_schoolUser;
		$this->view->miniInfo = $rowSchoolUser;
		//next class schedule
        $this->view->nextSchedule = $this->_getNextClassDateArray($uid, $cid);

    	$this->view->classInfo = $classInfo;
    	$this->view->tInfo = $topicInfo;
    	$this->view->comment = $comment;

    	$this->view->step = $step;
    	$this->view->tid = $tid;
    	$this->render();
    }

/**
     * topic delete comment action
     *
     */
    public function topiccommentdelAction()
    {
		$uid = $this->_user->getId();
    	$tid = $this->getParam('CF_tid');
    	$comment_id = $this->getParam('CF_commentid');
    	$step = $this->getParam('CF_step', 'start');
    	$cid = $this->getParam('CF_cid');

    	require_once 'Mdal/School/Topic.php';
        $mdalTopic = Mdal_School_Topic::getDefaultInstance();
        $topicInfo = $mdalTopic->getClassTopic($tid);
        $commentInfo = $mdalTopic->getTopicComment($comment_id);

        require_once 'Mdal/School/Class.php';
        $mdalClass = Mdal_School_Class::getDefaultInstance();
        $classInfo = $mdalClass->getClassInfo($cid);

    	if (empty($classInfo) || empty($commentInfo) || empty($topicInfo)) {
        	return $this->_redirect($this->_baseUrl . '/mobile/school/home');
        }

    	if ($step == 'finish') {
    		require_once 'Mbll/School/Topic.php';
        	$mbllTopic = new Mbll_School_Topic();
			$reseult = $mbllTopic->updateTopicComment(array('isDelete' => 1, 'create_time' =>time()), $comment_id, $tid);
			if (!$reseult) {
				$this->_redirect($this->_baseUrl . '/mobile/school/topiccommentdel?CF_cid=' . $cid . '&CF_tid='. $tid .'&CF_step=error');
                return;
			}
    	}

    	$rowSchoolUser = $this->_schoolUser;
		$this->view->miniInfo = $rowSchoolUser;
		//next class schedule
        $this->view->nextSchedule = $this->_getNextClassDateArray($uid, $cid);

        $this->view->commentInfo = $commentInfo;
    	$this->view->classInfo = $classInfo;
    	$this->view->tInfo = $topicInfo;

    	$this->view->step = $step;
    	$this->view->tid = $tid;
    	$this->render();
    }

    /**
     * help action
     *
     */
    public function helpAction()
    {
    	$parm = $this->getParam('CF_param', 'helpindex');
    	$uid = $this->_user->getId();

    	if ($this->_request->isPost() && $parm == 'helpfinish') {
			//delete all schedule
			require_once 'Mbll/School/Timepart.php';
        	$mbllTimepart = new Mbll_School_Timepart();
			$rst = $mbllTimepart->schoolClear($uid);
			if ($rst) {
			    $this->view->isClearComplete = 1;
			}
		}

        $this->view->parm = $parm;
		$this->render();
    }

    /**
     * cinfo static page
     *
     */
    public function cfinfoAction()
    {
		$this->render();
    }

    /********************************************************************************************/

    /************************************lp add ***********************************/

   /**
     * assess topic comment action
     *
     */
    public function classtopicgoodfinishAction()
    {
    	$uid = $this->_user->getId();
    	$tid = $this->getParam('CF_tid');
    	$cid = $this->getParam('CF_cid');
        $commentId = $this->getParam('CF_commentid');

        require_once 'Mdal/School/Topic.php';
        $dalTopic = Mdal_School_Topic::getDefaultInstance();

        require_once 'Mbll/School/Topic.php';
        $bllTopic = new Mbll_School_Topic();

        //if user have assessed this comment
        $alreadyAssess = $dalTopic->isUserHaveAssessed($commentId, $uid);

   		if ($alreadyAssess) {
        	$this->_redirect($this->_baseUrl . '/mobile/school/home');
            return;
        }
		$info = array('comment_id' => $commentId, 'uid' => $uid, 'create_time' => time());
        $bllTopic->insertTopicCommentGood($info);

        require_once 'Mdal/School/Class.php';
        $mdalClass = Mdal_School_Class::getDefaultInstance();
        $classInfo = $mdalClass->getClassInfo($cid);

        $rowSchoolUser = $this->_schoolUser;
		$this->view->miniInfo = $rowSchoolUser;
		//next class schedule
        $this->view->nextSchedule = $this->_getNextClassDateArray($uid, $cid);

        $this->view->classInfo = $classInfo;
        $this->view->tid = $tid;
        $this->render();
    }

    /**
     * error action
     *
     */
    public function errorAction()
    {
    	$parm = $this->getParam('CF_error', 'system');
        $this->view->parm = $parm;
        $this->render();
    }

//**********************************************part 2*****************************************************
//xiali
    /**
     * profile enquiry list
     *
     */
    public function enquiryprofilelistAction()
    {
    	$uid = $this->_user->getId();
    	$profileUid = $this->getParam('CF_uid');
    	$pageIndex = $this->getParam('CF_page', 1);
    	$pageSize = 30;

        if (empty($profileUid) || $uid == $profileUid) {
        	$profileUid = $uid;
        }

    	if ($uid != $profileUid) {
        	//update visit foot logic
	        require_once 'Mbll/School/User.php';
	        $mbllUser = new Mbll_School_User();
	        $mbllUser->updateVisitFoot($uid, $profileUid);
        }

		require_once 'Mdal/School/Enquiry.php';
        $mdalEnquiry = Mdal_School_Enquiry::getDefaultInstance();

        $enquiryLst = $mdalEnquiry->getLstEnquiryByUid($profileUid, $pageIndex, $pageSize);
		$count = $mdalEnquiry->getCntEnquiryByUid($profileUid);

		require_once 'Mdal/School/User.php';
        $mdalUser = Mdal_School_User::getDefaultInstance();
        $rowSchoolUser = $mdalUser->getUser($profileUid);

        require_once 'Bll/User.php';
        Bll_User::appendPerson($rowSchoolUser, 'uid');
		$this->view->miniInfo = $rowSchoolUser;

		if ($enquiryLst) {
			foreach ($enquiryLst as $key => $value) {
				//get enquiry
	        	$enquiryInfo = $mdalEnquiry->getEnquiry($value['qid']);
	        	if ($enquiryInfo) {
	        		$enquiryLst[$key]['cname'] = $enquiryInfo['name'];
	        		$enquiryLst[$key]['question'] = $enquiryInfo['question'];
	        		$enquiryLst[$key]['answer_count'] = $enquiryInfo['answer_count'];
	        		$enquiryLst[$key]['isAssess'] = $mdalEnquiry->isUserHaveAssessed($value['comment_id'], $uid);
	        	} else {
	        		unset($enquiryLst[$key]);
	        		$count = $count - 1;
	        	}
			}

			require_once 'Bll/User.php';
			Bll_User::appendPeople($enquiryLst, 'uid');

			//get start number and end number
	        $start = ($pageIndex - 1) * $pageSize;
	        $this->view->startNm = $start + 1;
	        $this->view->endNm = ($start + $pageSize) > $count ? $count : ($start + $pageSize);

			 //get pager info
	        $this->view->pager = array('count' => $count,
	                                   'pageIndex' => $pageIndex,
	                                   'requestUrl' => 'mobile/school/enquiryprofilelist',
	                                   'pageSize' => $pageSize,
	                                   'maxPager' => ceil($count / $pageSize),
	                                   'pageParam' => '&CF_uid=' . $profileUid);

			Bll_User::appendPeople($enquiryLst, 'uid');
	        $this->view->enquiryLst = $enquiryLst;
		}

		//20100112 add
		require_once 'Mbll/School/RemoteServiceApi.php';
        $mbllRemoteApi = new Mbll_School_RemoteServiceApi();
        $this->view->isAshiatoUser = $mbllRemoteApi->isAshiatoUser($profileUid);
        $this->view->profileUid1 = $profileUid;

		$this->view->cntMyEnquiry = $mdalEnquiry->getMyNewCntEnquiryById($profileUid);
		$this->view->uid = $uid;
        $this->render();
    }

    public function enquiryquestionaddAction()
	{
    	$uid = $this->_user->getId();
    	$step = $this->getParam('CF_step', 'start');

    	require_once 'Mdal/School/Enquiry.php';
        $mdalEnquiry = Mdal_School_Enquiry::getDefaultInstance();

    	if ($step == 'start') {
			//all nb caregory
    		$nbTypeLst = $mdalEnquiry->getNbLstEnquiryType();
    		$this->view->nbTypeLst = $nbTypeLst;

    		if ($_SESSION['school_enquiry_questionadd'] != null) {
    			$errorAry = $_SESSION['school_enquiry_questionadd'];
    			$this->view->questioncat = $errorAry['category'];
    			$this->view->question = $errorAry['question'];
    			$this->view->errorMsg = $errorAry['CF_error'];

    			//clear session
    			$_SESSION['school_enquiry_questionadd'] = null;
    			unset($_SESSION['school_enquiry_questionadd']);
    		}
    	}
		//add question confirm
    	elseif ($step == 'confirm') {
    		$questioncat = $this->getPost('enqueteQuestionCat');
    		$question = $this->getParam('enqueteQuestion');

    		$strMsg = '';
    		if (empty($question)) {
    			$strMsg = '･質問内容が未入力です｡';
    		}

    		elseif (mb_strlen($question, 'UTF-8') > 30) {
                $strMsg .= (empty($strMsg)?'':"\n") . "･質問内容は30文字以内で入力してください｡";
            }

    		require_once 'Mbll/Emoji.php';
	 		$bllEmoji = new Bll_Emoji();
	 		$emojiquestion = $bllEmoji->escapeEmoji($question, true);

	   		if ($emojiquestion != $question) {
				$strMsg .= (empty($strMsg)?'':"\n") . '･質問内容に絵文字は使用できません｡';
	 		}

	 		$sessionAry = array('category' => $questioncat, 'question' => $question);
            if (!empty($strMsg)) {
            	//save to session
				$sessionAry['CF_error'] = $strMsg;
				$_SESSION['school_enquiry_questionadd'] = $sessionAry;
                $this->_redirect($this->_baseUrl . '/mobile/school/enquiryquestionadd');
                return;
            }

            $sessionAry['uid'] = $uid;
            $sessionAry['create_time'] = time();
            $sessionAry['update_time'] = time();

			$_SESSION['school_enquiry_questionadd'] = $sessionAry;
			$this->view->question = $question;
			$this->view->rowCategory = $mdalEnquiry->getRowNbCategoryById($questioncat);
    	}
		//add question finish
    	elseif ($step == 'finish') {
    		if ($_SESSION['school_enquiry_questionadd'] != null) {
    			$rowAry = $_SESSION['school_enquiry_questionadd'];
    			//db add
				$result = $mdalEnquiry->insertEnquiry($rowAry);
				if (empty($result)) {
					return $this->_redirect($this->_baseUrl . '/mobile/school/error');
				}
				$this->view->qid = $result;
    			//clear session
    			$_SESSION['school_enquiry_questionadd'] = null;
    			unset($_SESSION['school_enquiry_questionadd']);
    		}
    	}

    	$this->view->step = $step;
        $this->render();
    }

	public function enquiryansweraddAction()
    {
    	$uid = $this->_user->getId();
    	$step = $this->getParam('CF_step', 'start');
    	$qid = $this->getParam('CF_qid');

    	require_once 'Mdal/School/Enquiry.php';
        $mdalEnquiry = Mdal_School_Enquiry::getDefaultInstance();
		$enquiryInfo = $mdalEnquiry->getEnquiry($qid);

		if (empty($enquiryInfo)) {
			return $this->_redirect($this->_baseUrl . '/mobile/school/error');
		}

		$qidLst = $mdalEnquiry->getLstQidById($uid);
		foreach ($qidLst as $key => $value) {
			if ($value['qid'] == $qid ) {
				unset($qidLst[$key]);
			}
		}

		$randQidAry = array_rand($qidLst, 1);
		$randQid = $qidLst[$randQidAry]['qid'];
		/*if ($randQid == $qid) {
			$randQid = 0;
		}*/
		$this->view->randQid = $randQid;

    	if ($step == 'start') {
			$this->view->enquiry = $enquiryInfo;
    		if ($_SESSION['school_enquiry_answeradd'] != null) {
    			$errorAry = $_SESSION['school_enquiry_answeradd'];
    			$this->view->topiccomment = $errorAry['comment'];
    			$this->view->errorMsg = $errorAry['CF_error'];

    			//clear session
    			$_SESSION['school_enquiry_answeradd'] = null;
    			unset($_SESSION['school_enquiry_answeradd']);
    		}
    	}
		//add answer confirm
    	elseif ($step == 'confirm') {
    		$txtTopiccomment = $this->getParam('classTopicComment');

    		$strMsg = '';
    		if (empty($txtTopiccomment)) {
    			$strMsg = '･回答ｺﾒﾝﾄが未入力です｡';
    		}

    		elseif (mb_strlen($txtTopiccomment, 'UTF-8') > 300) {
                $strMsg .= (empty($strMsg)?'':"\n") . "･300文字以内で入力してください｡";
            }

            require_once 'Mbll/Emoji.php';
	 		$bllEmoji = new Bll_Emoji();
			$txtTopiccomment = $bllEmoji->escapeEmoji($txtTopiccomment);

    		//save to session
            $_SESSION['school_enquiry_answeradd'] = array('comment' => $txtTopiccomment, 'qid' => $qid, 'uid' => $uid, 'create_time' => time());
            if (!empty($strMsg)) {
            	$_SESSION['school_enquiry_answeradd'] = array('comment' => $txtTopiccomment, 'CF_error' => $strMsg);
                $this->_redirect($this->_baseUrl . '/mobile/school/enquiryansweradd?CF_qid=' . $qid);
                return;
            }

			$this->view->topiccomment = $txtTopiccomment;
    	}
		//add answer finish
    	elseif ($step == 'finish') {
    		require_once 'Mbll/School/Enquiry.php';
        	$mbllEnquiry = new Mbll_School_Enquiry();

    		if ($_SESSION['school_enquiry_answeradd'] != null) {
    			$rowAry = $_SESSION['school_enquiry_answeradd'];
    			//db add
				$result = $mbllEnquiry->insertEnquiryComment($rowAry);
				if ($result) {
					if ($uid != $enquiryInfo['uid']) {
						require_once 'Mdal/School/Message.php';
	        			$mdalMessage = Mdal_School_Message::getDefaultInstance();
	        			$isExistsId = $mdalMessage->isMessageExites($enquiryInfo['uid'], 4);
	        			if ($isExistsId) {
	        				$mdalMessage->updateMessage(array('create_time' => time()), $isExistsId);
	        			} else {
		        			$messageAry = array('uid' => $uid,
		        								'target_uid' => $enquiryInfo['uid'],
		        								'type' => 4, 'object_id' => $qid,
		        								'create_time' => time());
		        			$mdalMessage->insertMessage($messageAry);
	        			}
					}

					//add by zx 2010/1/22
	                require_once 'Bll/Restful.php';
	                //get restful object
	                $restful = Bll_Restful::getInstance($uid, $this->_APP_ID);
	                $activityUrl = Zend_Registry::get('MIXI_APP_REQUEST_URL')
	                             . urlencode(Zend_Registry::get('host') . '/mobile/school/enquiryprofilelist?CF_uid=' . $uid);
	                $aryActivity = array('title' => ($this->_user->getDisplayName()).'さんが、学生限定ｲﾝﾀﾋﾞｭｰに回答しました',
	                                     'mobileUrl' => $activityUrl);
	                $restful->createActivity($aryActivity);
	                //$restful->createActivityWithPic(array('title'=>$title), $picUrl, 'image/gif');
				}

    			//clear session
    			$_SESSION['school_enquiry_answeradd'] = null;
    			unset($_SESSION['school_enquiry_answeradd']);
    		}
    	}

    	$rowSchoolUser = $this->_schoolUser;
		$this->view->miniInfo = $rowSchoolUser;
    	$this->view->qid = $qid;
    	$this->view->step = $step;
        $this->render();
    }

    /**
     * anquiry answer delete
     *
     * @return unknown
     */
    public function enquiryanswerdelAction()
    {
    	$step = $this->getParam('CF_step', 'confirm');
    	$commentId = $this->getParam('CF_commentid');

    	require_once 'Mdal/School/Enquiry.php';
        $mdalEnquiry = Mdal_School_Enquiry::getDefaultInstance();
		$commentInfo = $mdalEnquiry->getEnquiryComment($commentId);
		$qid = $commentInfo['qid'];

		//check whether have good_count
		if ($commentInfo['good_count'] > 0) {
			return $this->_redirect($this->_baseUrl . '/mobile/school/error');
		}

		//have good assessed
		if ($step != 'error') {
			if ($commentInfo['good_count'] || empty($commentInfo)) {
				return $this->_redirect($this->_baseUrl . '/mobile/school/enquiryanswerdel?CF_step=error&CF_commentid=' . $commentId);
			}
		}

        if ($step == 'confirm') {
        	//init
        	$this->view->enquiryInfo = $mdalEnquiry->getEnquiry($qid);
        	$this->view->commentInfo = $commentInfo;
        }
		elseif ($step == 'finish') {
			//send
			require_once 'Mbll/School/Enquiry.php';
			$mbllEnquiry = new Mbll_School_Enquiry();
			$reseult = $mbllEnquiry->updateEnquiryComment(array('isDelete' => 1, 'create_time' =>time()), $commentId, $qid);
			if (!$reseult) {
				return $this->_redirect($this->_baseUrl . '/mobile/school/error');
			}
    	}

    	$rowSchoolUser = $this->_schoolUser;
		$this->view->miniInfo = $rowSchoolUser;

    	$this->view->qid = $qid;
    	$this->view->commentid = $commentId;
    	$this->view->step = $step;
        $this->render();
    }

    /**
     * enquiry comment good
     *
     * @return unknown
     */
    public function enquirycommentgoodAction()
    {
    	$uid = $this->_user->getId();
        $commentId = $this->getParam('CF_commentid');

        require_once 'Mdal/School/Enquiry.php';
        $mdalEnquiry = Mdal_School_Enquiry::getDefaultInstance();

        require_once 'Mbll/School/Enquiry.php';
		$mbllEnquiry = new Mbll_School_Enquiry();

        //if user have assessed this comment
        $alreadyAssess = $mdalEnquiry->isUserHaveAssessed($commentId, $uid);
		//check is have assessed
        if ($alreadyAssess) {
        	return $this->_redirect($this->_baseUrl . '/mobile/school/home');
        }

        $rowComment = $mdalEnquiry->getEnquiryComment($commentId);
        //check assessed is mine
		if ($rowComment['uid'] == $uid) {
			return $this->_redirect($this->_baseUrl . '/mobile/school/home');
		}

		$info = array('comment_id' => $commentId, 'uid' => $uid, 'create_time' => time());
        $ownerId = $mbllEnquiry->insertEnquiryCommentGood($info);
    	if (empty($ownerId)) {
			return $this->_redirect($this->_baseUrl . '/mobile/school/error');
		}

        $rowSchoolUser = $this->_schoolUser;
		$this->view->miniInfo = $rowSchoolUser;

		$userInfo = Bll_User::getPerson($ownerId);
        $this->view->nickname = $userInfo->getField('displayName');
        $this->view->qid = $rowComment['qid'];
        $this->render();
    }

	public function enquiryanswereditAction()
    {
    	$uid = $this->_user->getId();
    	$step = $this->getParam('CF_step', 'start');
		$commentId = $this->getParam('CF_commentid');

    	require_once 'Mdal/School/Enquiry.php';
        $mdalEnquiry = Mdal_School_Enquiry::getDefaultInstance();

        //answer info
		$commentInfo = $mdalEnquiry->getEnquiryComment($commentId);
		$qid = $commentInfo['qid'];
    	if ($step == 'start') {
			$enquiryInfo = $mdalEnquiry->getEnquiry($qid);
			//check is mine answer
			if ($commentInfo['uid'] != $uid || empty($commentInfo) || empty($enquiryInfo)) {
				return $this->_redirect($this->_baseUrl . '/mobile/school/error');
			}
			//question info
        	$this->view->enquiryInfo = $enquiryInfo;
        	$this->view->commentInfo = $commentInfo;
			$this->view->topiccomment = $commentInfo['comment'];

			//get session' value
    		if ($_SESSION['school_enquiry_answeredit'] != null) {
    			$errorAry = $_SESSION['school_enquiry_answeredit'];
    			$this->view->topiccomment = $errorAry['comment'];
    			$this->view->errorMsg = $errorAry['CF_error'];

    			//clear session
    			$_SESSION['school_enquiry_answeredit'] = null;
    			unset($_SESSION['school_enquiry_answeredit']);
    		}
    	}
		//edit answer confirm
    	elseif ($step == 'confirm') {
    		$topiccomment = $this->getParam('classTopicComment');

    		$strMsg = '';
    		if (empty($topiccomment)) {
    			$strMsg = '･回答ｺﾒﾝﾄが未入力です｡';
    		}

    		elseif (mb_strlen($topiccomment, 'UTF-8') > 300) {
                $strMsg .= (empty($strMsg)?'':"\n") . "･300文字以内で入力してください｡";
            }

            require_once 'Mbll/Emoji.php';
	 		$bllEmoji = new Bll_Emoji();
			$topiccomment = $bllEmoji->escapeEmoji($topiccomment);

			//save to session
            $_SESSION['school_enquiry_answeredit'] = array('comment' => $topiccomment, 'create_time' => time());
            if (!empty($strMsg)) {
            	$_SESSION['school_enquiry_answeredit'] = array('comment' => $topiccomment, 'CF_error' => $strMsg);
                $this->_redirect($this->_baseUrl . '/mobile/school/enquiryansweredit?CF_commentid=' . $commentId);
                return;
            }

			$this->view->topiccomment = $topiccomment;
    	}
		//edit answer finish
    	elseif ($step == 'finish') {
    		require_once 'Mbll/School/Enquiry.php';
        	$mbllEnquiry = new Mbll_School_Enquiry();

    		if ($_SESSION['school_enquiry_answeredit'] != null) {
    			$rowAry = $_SESSION['school_enquiry_answeredit'];
    			//db update
				$mbllEnquiry->updateEnquiryComment($rowAry, $commentId, $qid);
    			//clear session
    			$_SESSION['school_enquiry_answeredit'] = null;
    			unset($_SESSION['school_enquiry_answeredit']);
    		}

    		$qidLst = $mdalEnquiry->getLstQidById($uid);
			$randQidAry = array_rand($qidLst, 1);
			$this->view->randQid = $qidLst[$randQidAry]['qid'];
    	}

    	$rowSchoolUser = $this->_schoolUser;
		$this->view->miniInfo = $rowSchoolUser;

    	$this->view->uid = $uid;
    	$this->view->commentid = $commentId;
    	$this->view->qid = $qid;
    	$this->view->step = $step;
        $this->render();
    }

    public function enquirylistmineAction()
    {
    	$uid = $this->_user->getId();
    	$pageIndex = $this->getParam('CF_page', 1);

    	$mid = $this->getParam('CF_mid');
    	if ($mid) {
        	require_once 'Mdal/School/Message.php';
            $mdalMessage = Mdal_School_Message::getDefaultInstance();
    		$mdalMessage->deleteMessage($mid);
    	}

		require_once 'Mdal/School/Enquiry.php';
        $mdalEnquiry = Mdal_School_Enquiry::getDefaultInstance();

        $rowSchoolUser = $this->_schoolUser;
		$this->view->miniInfo = $rowSchoolUser;

        $enquiryLst = $mdalEnquiry->getMyNewLstEnquiryById($uid, $pageIndex, $this->_pageSize);
		$count = $mdalEnquiry->getMyNewCntEnquiryById($uid);

    	$now = getdate();
		foreach ($enquiryLst as $key => $value) {
			$aryTime = getdate($value['update_time']);
            $enquiryLst[$key]['format_time'] = $aryTime['mon'] . '/' . $aryTime['mday'];
            if ($now['mon'] == $aryTime['mon'] && $now['mday'] == $aryTime['mday']) {
                $enquiryLst[$key]['format_time'] = $aryTime['hours'] . ':' . (strlen($aryTime['minutes'])<2 ? ('0' . $aryTime['minutes']) : $aryTime['minutes']);
            }
		}

        //get start number and end number
        $start = ($pageIndex - 1) * $this->_pageSize;
        $this->view->startNm = $start + 1;
        $this->view->endNm = ($start + $this->_pageSize) > $count ? $count : ($start + $this->_pageSize);

		 //get pager info
        $this->view->pager = array('count' => $count,
                                   'pageIndex' => $pageIndex,
                                   'requestUrl' => 'mobile/school/enquirylistmine',
                                   'pageSize' => $this->_pageSize,
                                   'maxPager' => ceil($count / $this->_pageSize));

        $this->view->enquiryLst = $enquiryLst;
        $this->render();
    }

    public function enquiryanswerlistAction()
    {
    	$uid = $this->_user->getId();
    	$pageIndex = $this->getParam('CF_page', 1);
    	$qid = $this->getParam('CF_qid');
    	$orderBy = $this->getParam('CF_orderBy', 'desc');
		$this->view->orderBy = $orderBy;

    	require_once 'Mdal/School/Enquiry.php';
        $mdalEnquiry = Mdal_School_Enquiry::getDefaultInstance();

    	$enquiryInfo = $mdalEnquiry->getEnquiry($qid);
    	if (empty($enquiryInfo)) {
    		return $this->_redirect($this->_baseUrl . '/mobile/school/home');
    	}

    	$mdalEnquiry->updateEnquiry(array('visit_count' => $enquiryInfo['visit_count'] + 1) , $qid);
		$enquiryInfo['visit_count'] = $enquiryInfo['visit_count'] + 1;
    	if ($orderBy == 'good_count') {
        	$orderBy = 'good_count DESC';
        } elseif ($orderBy == 'desc') {
        	$orderBy = 'create_time DESC';
        } elseif ($orderBy == 'asc') {
			$orderBy = 'create_time ASC';
        }

        require_once 'Mdal/School/User.php';
        $mdalUser = Mdal_School_User::getDefaultInstance();

        $enquiryanswerLst = $mdalEnquiry->getLstEnquiryAnswerById($qid, $pageIndex, $this->_pageSize, $orderBy);
		$count = $mdalEnquiry->getCntEnquiryAnswerById($qid);

		foreach ($enquiryanswerLst as $key => $value) {
        	$enquiryanswerLst[$key]['userInfo'] = $mdalUser->getUser($value['uid']);
        	$enquiryanswerLst[$key]['isAssess'] = $mdalEnquiry->isUserHaveAssessed($value['comment_id'], $uid);
		}

		Bll_User::appendPeople($enquiryanswerLst, 'uid');
        //get start number and end number
        $start = ($pageIndex - 1) * $this->_pageSize;
        $this->view->startNm = $start + 1;
        $this->view->endNm = ($start + $this->_pageSize) > $count ? $count : ($start + $this->_pageSize);

		 //get pager info
        $this->view->pager = array('count' => $count,
                                   'pageIndex' => $pageIndex,
                                   'requestUrl' => 'mobile/school/enquiryanswerlist',
                                   'pageSize' => $this->_pageSize,
                                   'maxPager' => ceil($count / $this->_pageSize),
                                   'pageParam' => '&CF_qid=' . $qid . '&CF_orderBy=' . $orderBy);

        $rowSchoolUser = $this->_schoolUser;
		$this->view->miniInfo = $rowSchoolUser;
		$this->view->commentId = $mdalEnquiry->isUserHaveComment($uid, $qid);
		$this->view->qid = $qid;
		$this->view->enquiryInfo = $enquiryInfo;
		$this->view->enquiryanswerLst = $enquiryanswerLst;
        $this->render();
    }

    public function enquirycategorylistAction()
    {
		$uid = $this->_user->getId();
    	$pageIndex = $this->getParam('CF_page', 1);
    	$categoryid = $this->getParam('CF_categoryid', rand(0,4));
		$orderBy = $this->getParam('CF_orderBy', 'update_time');

		require_once 'Mdal/School/Enquiry.php';
        $mdalEnquiry = Mdal_School_Enquiry::getDefaultInstance();

		$categoryLst = null;
		$count = 0;

    	if ($orderBy == 'answer_count' || $orderBy == 'answer_count DESC') {
        	$orderBy = 'answer_count DESC';
        } elseif ($orderBy == 'update_time' || $orderBy == 'create_time DESC') {
        	$orderBy = 'update_time DESC';
        }

   		if (empty($categoryid)) {
        	$categoryLst = $mdalEnquiry->getLstEnquiryCategoryById(0, $pageIndex, $this->_pageSize, $orderBy);
			$count = $mdalEnquiry->getCntEnquiryCategoryById(0);
   			$now = getdate();
			foreach ($categoryLst as $key => $value) {
				$aryTime = getdate($value['update_time']);
	            $categoryLst[$key]['format_time'] = $aryTime['mon'] . '/' . $aryTime['mday'];
	            if ($now['mon'] == $aryTime['mon'] && $now['mday'] == $aryTime['mday']) {
	                $categoryLst[$key]['format_time'] = $aryTime['hours'] . ':' . (strlen($aryTime['minutes'])<2 ? ('0' . $aryTime['minutes']) : $aryTime['minutes']);
	            }
			}
        } else {
        	$categoryLst = $mdalEnquiry->getLstEnquiryCategoryById($categoryid, $pageIndex, $this->_pageSize, $orderBy);
			$count = $mdalEnquiry->getCntEnquiryCategoryById($categoryid);
        }

        //all nb caregory
    	$nbTypeLst = $mdalEnquiry->getNbCategoryEnquiry();
    	$this->view->nbTypeLst = $nbTypeLst;

		Bll_User::appendPeople($categoryLst, 'uid');
        //get start number and end number
        $start = ($pageIndex - 1) * $this->_pageSize;
        $this->view->startNm = $start + 1;
        $this->view->endNm = ($start + $this->_pageSize) > $count ? $count : ($start + $this->_pageSize);

		 //get pager info
        $this->view->pager = array('count' => $count,
                                   'pageIndex' => $pageIndex,
                                   'requestUrl' => 'mobile/school/enquirycategorylist',
                                   'pageSize' => $this->_pageSize,
                                   'maxPager' => ceil($count / $this->_pageSize),
                                   'pageParam' => '&CF_categoryid=' . $categoryid . '&CF_orderBy=' . $orderBy);

		$this->view->sort = substr($orderBy, 0 , 1);
		$this->view->cid = $categoryid;
		$this->view->orderBy = $orderBy;
		$this->view->categoryLst = $categoryLst;
    	$this->render();
    }

    public function enquiryAction()
    {
    	$uid = $this->_user->getId();
		$pageIndex = 1;
		$pageSize = 5;

		require_once 'Mdal/School/Enquiry.php';
        $mdalEnquiry = Mdal_School_Enquiry::getDefaultInstance();
		//all nb caregory
    	$nbTypeLst = $mdalEnquiry->getNbCategoryEnquiry();
    	$this->view->nbTypeLst = $nbTypeLst;

		$myEnquiryLst = $mdalEnquiry->getMyNewLstEnquiryById($uid, $pageIndex, $pageSize);
		$this->view->myEnquiryLst = $myEnquiryLst;

		$cid1 = rand(0,4);
		$cid2 = rand(0,4);
		$this->view->cid1 = $cid1;
		$this->view->cid2 = $cid2;

		$now = getdate();
		foreach ($myEnquiryLst as $key => $value) {
			$aryTime = getdate($value['update_time']);
            $myEnquiryLst[$key]['format_time'] = $aryTime['mon'] . '/' . $aryTime['mday'];
            if ($now['mon'] == $aryTime['mon'] && $now['mday'] == $aryTime['mday']) {
                $myEnquiryLst[$key]['format_time'] = $aryTime['hours'] . ':' . (strlen($aryTime['minutes'])<2 ? ('0' . $aryTime['minutes']) : $aryTime['minutes']);
            }
		}

    	$enquiryCntByLst = $mdalEnquiry->getLstEnquiryCategoryById($cid1, $pageIndex, $pageSize, 'answer_count DESC');
		foreach ($enquiryCntByLst as $key => $value) {
			$aryTime = getdate($value['update_time']);
            $enquiryCntByLst[$key]['format_time'] = $aryTime['mon'] . '/' . $aryTime['mday'];
            if ($now['mon'] == $aryTime['mon'] && $now['mday'] == $aryTime['mday']) {
                $enquiryCntByLst[$key]['format_time'] = $aryTime['hours'] . ':' . (strlen($aryTime['minutes'])<2 ? ('0' . $aryTime['minutes']) : $aryTime['minutes']);
            }
		}

		$enquiryTimeByLst = $mdalEnquiry->getLstEnquiryCategoryById($cid2, $pageIndex, $pageSize, 'update_time DESC');
    	foreach ($enquiryTimeByLst as $key => $value) {
			$aryTime = getdate($value['update_time']);
            $enquiryTimeByLst[$key]['format_time'] = $aryTime['mon'] . '/' . $aryTime['mday'];
            if ($now['mon'] == $aryTime['mon'] && $now['mday'] == $aryTime['mday']) {
                $enquiryTimeByLst[$key]['format_time'] = $aryTime['hours'] . ':' . (strlen($aryTime['minutes'])<2 ? ('0' . $aryTime['minutes']) : $aryTime['minutes']);
            }
		}

		$qidLst = $mdalEnquiry->getLstQidById($uid);
		$randQidAry = array_rand($qidLst, 1);
		$randQid = $qidLst[$randQidAry]['qid'];
		$this->view->randQid = $randQid;
		$this->view->enquiryCntByLst = $enquiryCntByLst;
		$this->view->enquiryTimeByLst = $enquiryTimeByLst;
		$this->view->myEnquiryLst = $myEnquiryLst;
		$this->render();
    }
//*******************************************************************************************************

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
        return $this->_redirect($this->_baseUrl . '/mobile/school/error');
    }
}
<?php

/** @see Bll_Cache */
require_once 'Bll/Cache.php';

define('DYNAMITE_TPL_ROOT', ROOT_DIR . '/www/static/apps/school/mobile/swf');

/**
 * flash Cache
 *
 * @package    Mbll/School
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create
 */
class Mbll_School_FlashCache
{
    private static $_prefix = 'Mbll_School_FlashCache';

    /**
     * get cache key
     *
     * @param string $salt
     * @param mixi $params
     * @return string
     */
    private static function getCacheKey($salt, $params = null)
    {
        return Bll_Cache::getCacheKey(self::$_prefix, $salt, $params);
    }



/*
// config
baseUrl = "{%baseUrl%}";
owid = "{%owid%}";
vwid = "{%vwid%}";
topUrl = "{%topUrl%}";
subUrl = "{%subUrl%}";
if(owid eq vwid) {
	//subUrl = baseUrl add "?mode=changeDesign";
	menu.disp = 1;
} else {
	//subUrl = baseUrl add "?mode=profile";
}
// dayOfweek
sat = "{%sat%}";
dispDow = "{%dispDow%}";
// now
cnt = "{%cnt%}";
// region
minTime = "{%minTime%}";
maxTime = "{%maxTime%}";
//minTime = (minTime>9)  ? 9  : minTime;
//maxTime = (maxTime<6)  ? 6  : maxTime;
//maxTime = (maxTime>14) ? 14 : maxTime;

// layout
container._alpha = "{%alpha%}";
bitmap = "{%bitmap%}";

// ad
adtype = "{%adtype%}";
adtxt = "{%adtxt%}";
adUrl  = "{%adUrl%}";
clickable = "{%clickable%}";
fwdUrl = "{%fwdUrl%}";

// schedule
mon_cn_1 = "{%mon_cn_1%}";
mon_ct_1 = "{%mon_ct_1%}";
mon_wt_1 = "{%mon_wt_1%}";
mon_disp_1 = "{%mon_disp_1%}";
tue_cn_1 = "{%tue_cn_1%}";
tue_ct_1 = "{%tue_ct_1%}";
tue_wt_1 = "{%tue_wt_1%}";
tue_disp_1 = "{%tue_disp_1%}";
wed_cn_1 = "{%wed_cn_1%}";
wed_ct_1 = "{%wed_ct_1%}";
wed_wt_1 = "{%wed_wt_1%}";
wed_disp_1 = "{%wed_disp_1%}";
thu_cn_1 = "{%thu_cn_1%}";
thu_ct_1 = "{%thu_ct_1%}";
thu_wt_1 = "{%thu_wt_1%}";
thu_disp_1 = "{%thu_disp_1%}";
fri_cn_1 = "{%fri_cn_1%}";
fri_ct_1 = "{%fri_ct_1%}";
fri_wt_1 = "{%fri_wt_1%}";
fri_disp_1 = "{%fri_disp_1%}";
sat_cn_1 = "{%sat_cn_1%}";
sat_ct_1 = "{%sat_ct_1%}";
sat_wt_1 = "{%sat_wt_1%}";
sat_disp_1 = "{%sat_disp_1%}";
*/

    /**
     * get flash
     * this function has not use cache for the moment
     *
     * @param array $myInfo
     * @param integer $profileUid
     * @param string $mixiUrl
     * @param integer $selwday
     * @return stream flash
     */
    public static function getNewFlash($myInfo, $profileUid, $mixiUrl, $selwday)
    {
        $uid = $myInfo['uid'];
        //mine or profile user
        if (empty($profileUid) || $uid == $profileUid) {
            $profileUid = $uid;
            $profileInfo = $myInfo;
            $isSelf = 1;
        }
        else {
            require_once 'Mdal/School/User.php';
            $mdalUser = Mdal_School_User::getDefaultInstance();
            $profileInfo = $mdalUser->getUser($profileUid);
            $isSelf = 2;
        }
        if (empty($profileInfo)) {
            return false;
        }
        require_once 'Bll/Friend.php';
        $aryFids = Bll_Friend::getFriends($profileUid);

        $mixiMobileBaseUrl = $mixiUrl;
        $appUrl = Zend_Registry::get('host') . '/mobile/school/';
        $now = getdate();
        $aryWeekDay = array(1 => 'mon', 2 => 'tue', 3 => 'wed', 4 => 'thu', 5 => 'fri', 6 => 'sat');
        $wday = empty($selwday) ? $now['wday'] : $selwday;

        //get profile schedule info
        require_once 'Mdal/School/Class.php';
        require_once 'Mdal/School/Timepart.php';
        $mdalClass = Mdal_School_Class::getDefaultInstance();
        $mdalTimepart = Mdal_School_Timepart::getDefaultInstance();
        $lstTimepart = $mdalTimepart->listUserTimepart($profileUid);
        if (empty($lstTimepart) || count($lstTimepart) == 0) {
            return false;
        }

        //get design info
        require_once 'Mdal/School/Design.php';
        $mdalDesign = Mdal_School_Design::getDefaultInstance();
        $rowDesign = $mdalDesign->getNbDesign($profileInfo['design_type']);
        if (empty($rowDesign)) {
            return false;
        }

        //flash info combine and replace
        $aryVar = array();
        $aryLeftRight = explode(',', self::_getFlashSizeLeftRight());
        $aryVar['left'] = $aryLeftRight[0];
        $aryVar['right'] = $aryLeftRight[1];
        //design info
        $aryVar['SetBackgroundColor_r'] = $rowDesign['SetBackgroundColor_r'];
        $aryVar['SetBackgroundColor_g'] = $rowDesign['SetBackgroundColor_g'];
        $aryVar['SetBackgroundColor_b'] = $rowDesign['SetBackgroundColor_b'];
        $aryVar['base_r'] = $rowDesign['base_r'];
        $aryVar['base_g'] = $rowDesign['base_g'];
        $aryVar['base_b'] = $rowDesign['base_b'];
        $aryVar['color1_r'] = $rowDesign['color1_r'];
        $aryVar['color1_g'] = $rowDesign['color1_g'];
        $aryVar['color1_b'] = $rowDesign['color1_b'];
        $aryVar['color2_r'] = $rowDesign['color2_r'];
        $aryVar['color2_g'] = $rowDesign['color2_g'];
        $aryVar['color2_b'] = $rowDesign['color2_b'];
        $aryVar['color3_r'] = $rowDesign['color3_r'];
        $aryVar['color3_g'] = $rowDesign['color3_g'];
        $aryVar['color3_b'] = $rowDesign['color3_b'];
        $aryVar['color4_r'] = $rowDesign['color4_r'];
        $aryVar['color4_g'] = $rowDesign['color4_g'];
        $aryVar['color4_b'] = $rowDesign['color4_b'];
        $aryVar['color5_r'] = $rowDesign['color5_r'];
        $aryVar['color5_g'] = $rowDesign['color5_g'];
        $aryVar['color5_b'] = $rowDesign['color5_b'];
        $aryVar['color6_r'] = $rowDesign['color6_r'];
        $aryVar['color6_g'] = $rowDesign['color6_g'];
        $aryVar['color6_b'] = $rowDesign['color6_b'];
        $aryVar['text_r'] = $rowDesign['text_r'];
        $aryVar['text_g'] = $rowDesign['text_g'];
        $aryVar['text_b'] = $rowDesign['text_b'];
        $aryVar['bgObj1'] = $rowDesign['bgObj1'];
        $aryVar['bgObj2'] = $rowDesign['bgObj2'];
        $aryVar['offset_to_alpha'] = $rowDesign['offset_to_alpha'];
        $aryVar['alpha'] = $rowDesign['alpha'];
        $aryVar['bitmap'] = $rowDesign['bitmap'];

        $aryVar['baseUrl'] = $mixiMobileBaseUrl . urlencode($appUrl);
        $aryVar['owid'] = $uid;
        $aryVar['vwid'] = $profileUid;
        $aryVar['topUrl'] = $mixiMobileBaseUrl . urlencode($appUrl . 'home');
        if ($uid == $profileUid) {
            $aryVar['subUrl'] = $mixiMobileBaseUrl . urlencode($appUrl . 'designlist');
            $aryVar['clickable'] = 0;
        }
        else {
            $aryVar['subUrl'] = $mixiMobileBaseUrl . urlencode($appUrl . 'profile?CF_uid=' . $profileUid);
            $aryVar['clickable'] = 1;
        }

        $aryVar['adtype'] = 0; //0->none, 1->large, 2->small
        $aryVar['adtxt'] = "脳を鍛えるﾏｲﾐｸ頭脳くらべ";
        $aryVar['adUrl'] = "http://m.mixi.jp/view_appli.pl?id=9461&amp;guid=ON";
        $aryVar['fwdUrl'] = $mixiMobileBaseUrl . urlencode($appUrl . 'flashfwd?CF_fwd=');

        $maxShowPart = 6;
        foreach ($lstTimepart as $tdata) {
        	if (0 == $tdata['is_hide'] && $tdata['part'] > $maxShowPart) {
        	    $maxShowPart = $tdata['part'];
        	}
        }
        $aryVar['cnt'] = 1;
        $saturdayHasClass = 0;
        foreach ($lstTimepart as $idx => $timepart) {
            if (0 == $timepart['is_hide'] || $timepart['part'] <= $maxShowPart) {
                $part = $timepart['part'];
                $startTime = $timepart['is_hide'] ? '' : $timepart['start_h'] . ':' . $timepart['start_m'];
                $endTime = $timepart['is_hide'] ? '' : strftime('%H:%M', strtotime($startTime) + $timepart['part_minutes'] * 60);
                $status = $timepart['is_hide'] ? 2 : 1;//0授業あり/1未登録/2授業なし
                $strNowTime = strftime('%H:%M', $now[0]);
                if ($strNowTime >= $startTime && $strNowTime <= $endTime) {
                    $aryVar['cnt'] = $part;
                }
                //schedule data
                foreach ($aryWeekDay as $wdayIdx=>$wdayValue) {
                    $className = '授業なし';
                    $forecast = '';
                    if (2 != $status) {
                        $status = 1;
                        $rowNowClass = $mdalTimepart->getTimepartScheduleByPk($profileUid, $wdayIdx, $part);
                        if (!empty($rowNowClass)) {
                            $rowClass = $mdalClass->getClassInfo($rowNowClass['cid']);
                            $className =  empty($rowClass) ? '' : $rowClass['name'];
                            $forecast = empty($rowClass) ? 0 : self::_getClassForecastStatus($profileUid, $rowNowClass['cid'], $aryFids);
                            $status = 0;
                        }
                        else {
                            $className = '未登録';
                        }
                    }
                    //
                    $aryVar[$wdayValue . '_cn_' . $part] = empty($className) ? '' : htmlspecialchars($className, ENT_QUOTES, 'UTF-8');
                    $aryVar[$wdayValue . '_ct_' . $part] = $startTime . '-' . $endTime;
                    //0晴れor1曇りor2雨  emoji
                    $aryVar[$wdayValue . '_wt_' . $part] = self::_getEmojiInFlash($forecast);
                    //is not self
                    if (2 == $isSelf || '' === $forecast) {
                        $aryVar[$wdayValue . '_wt_' . $part] = '';
                    }
                    $aryVar[$wdayValue . '_disp_' . $part] = $status;
                    //is saturday has class
                    if (6 == $wdayIdx && 0 == $status) {
                        $saturdayHasClass = 1;
                    }
                }
                $maxIdx = $idx;
            }
        }
        $aryVar['sat'] = $saturdayHasClass;
        if ((!$saturdayHasClass && $wday == 6) || $wday==0) {
            $aryVar['dispDow'] = 'mon';
        }
        else {
            $aryVar['dispDow'] = $aryWeekDay[$wday];
        }
        $aryVar['minTime'] = $lstTimepart[0]['part'] > 9 ? 9 : $lstTimepart[0]['part'];
        $aryVar['maxTime'] = (($maxIdx+1) < 6) ? 6 : ($maxIdx+1);

        //$aryVar['url_set'] = $mixiMobileBaseUrl . urlencode($appUrl . 'dynamiteset?CF_uid=' . $profileUid . '&CF_flashlite=1');
        //$aryVar['url_left'] = ($appUrl . 'agit?CF_uid=' . $prevId . '&amp;opensocial_app_id=' . $appid . '&amp;opensocial_owner_id='. $mydynamiteInfo['uid'] . '&amp;rand=' . time()) . '&amp;guid=ON';

        //flash cache deal
        $aryTmp = array();
        $aryTmp['design_type'] = $profileInfo['design_type'];
        foreach ($aryVar as $varKey=>$vardata) {
            if ('bgObj1' != $varKey && 'bgObj2' != $varKey) {
                $aryTmp[$varKey] = $vardata;
            }
        }
        $strTmp = http_build_query($aryTmp);
        $cacheVal = md5($profileUid . $strTmp);
        $cacheKey = self::getCacheKey('getSchoolFlash', $profileUid . $isSelf);
        $savedCacheInfo = Bll_Cache::get($cacheKey);

        //load from cache
        if ($savedCacheInfo && $savedCacheInfo == $cacheVal) {
            $cacheFile = TEMP_DIR . '/school' . self::_getSavedDir($profileUid) . $profileUid . '_' . $isSelf . '.swf.gz';
            if (file_exists($cacheFile)) {
                $swfOutput = @file_get_contents($cacheFile);
                return $swfOutput;
            }
        }

        //reset cache
        //get xml and replace values
        $xmlString = file_get_contents(DYNAMITE_TPL_ROOT . '/school_flash_tpl.xml');
        foreach ($aryVar as $key=>$value) {
            $xmlString = str_replace("{%" . $key . "%}", $value, $xmlString);
        }
//file_put_contents(DYNAMITE_TPL_ROOT . '/school' . $rowDesign['did'] . '.xml', $xmlString);

        //set process param
        $descriptorspec = array(
           0 => array("pipe", "r"),
           1 => array("pipe", "w"),
           2 => array("file", "school_xml2swf_error.txt", "a")
        );

        $pipes = array();
        //XML to SWF
        // run swfmill and get $process
        $process = proc_open(SWFMILL_DIR . ' -e cp932 xml2swf stdin stdout', $descriptorspec, $pipes);

        if (is_resource($process)) {
            // set param $xmlString
            fwrite($pipes[0], $xmlString);
            fclose($pipes[0]);

            // get $swfOutput
            $swfOutput = stream_get_contents($pipes[1]);
            fclose($pipes[1]);

            // close $process
            proc_close($process);
        }

        if ($swfOutput) {
            $saveFile = TEMP_DIR . '/school' . self::_getSavedDir($profileUid);
            if (!file_exists($saveFile)) {
                mkdir($saveFile, 0777, true);
            }
            $saveFile .= $profileUid . '_' . $isSelf . '.swf.gz';
            $swfOutput = gzencode($swfOutput, 9);
            Bll_Cache::set($cacheKey, $cacheVal, Bll_Cache::LIFE_TIME_ONE_DAY);
            //save to file
            @file_put_contents($saveFile, $swfOutput);
 //info_log('cache reseted:' . $profileUid . '_' . $isSelf, 'school_swf_cache');
        }

        return $swfOutput;
    }


    /**
     * get school swf gz saved directory
     *
     * @param integer uid
     * @return string
     */
    private static function _getSavedDir($uid)
    {
        $strMd5 = md5($uid);
        $dir0 = substr($strMd5, 0, 1);
        $dir1 = substr($strMd5, 1, 1);
        $dir2 = substr($strMd5, 2, 1);
        $dir3 = substr($strMd5, 3, 1);
        $dir4 = substr($strMd5, 4, 1);
        return '/' . $dir0 . '/' . $dir1 . '/' . $dir2 . '/' . $dir3 . '/' . $dir4 . '/';
    }

	/**
     * get class forecast status by user and class
     *
     * @param integer uid
     * @param integer $cid
     * @param array $aryFids
     * @return integer [0-晴れ/1-曇り/2-雨]
     */
    private static function _getClassForecastStatus($uid, $cid, $aryFids)
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
     * get flash size left right
     *
     * @param
     * @return string [left,right]
     */
    private static function _getFlashSizeLeftRight()
    {
        $aryDeviceAu = array('A5403CA'=>'100,4700',
                             'A5406CA'=>'100,4700',
                             'A5407CA'=>'100,4700',
                             'A5512CA'=>'100,4700',
                             'A5513CA'=>'100,4700',
                             'E02SA'=>'100,4700',
                             'W21CA'=>'100,4700',
                             'W21CAII'=>'100,4700',
                             'W22H'=>'100,4700',
                             'W22SA'=>'100,4700',
                             'W31CA'=>'100,4700',
                             'W31H'=>'100,4700',
                             'W31SA'=>'100,4700',
                             'W31SAII'=>'100,4700',
                             'W32H'=>'100,4700',
                             'W41CA'=>'100,4700',
                             'W41H'=>'100,4700',
                             'W41SA'=>'100,4700',
                             'W42SA'=>'100,4700',
                             'W43SA'=>'100,4700',
                             'W51SA'=>'100,4700',
                             'W52SA'=>'100,4700',
                             'W53SA'=>'100,4700',
                             'W55SA'=>'100,4700',
                             'W62SA'=>'100,4700',
                             'A5526K'=>'80,4720',
                             'A5528K'=>'80,4720',
                             'E03CA'=>'80,4720',
                             'K001'=>'80,4720',
                             'K003'=>'80,4720',
                             'KYX02'=>'80,4720',
                             'NS01'=>'80,4720',
                             'W41K'=>'80,4720',
                             'W42CA'=>'80,4720',
                             'W42H'=>'80,4720',
                             'W42K'=>'80,4720',
                             'W43CA'=>'80,4720',
                             'W43H'=>'80,4720',
                             'W43HII'=>'80,4720',
                             'W43K'=>'80,4720',
        					 'W44K'=>'80,4720',
        					 'W44KII'=>'80,4720',
                             'W44KIIカメラなし'=>'80,4720',
                             'W51CA'=>'80,4720',
                             'W51H'=>'80,4720',
                             'W51K'=>'80,4720',
                             'W51P'=>'80,4720',
                             'W52CA'=>'80,4720',
                             'W52H'=>'80,4720',
                             'W52K'=>'80,4720',
                             'W52P'=>'80,4720',
                             'W53CA'=>'80,4720',
                             'W53H'=>'80,4720',
                             'W53K'=>'80,4720',
                             'W61CA'=>'80,4720',
                             'W61H'=>'80,4720',
        					 'W61K'=>'80,4720',
        					 'W61P'=>'80,4720',
                             'W62K'=>'80,4720',
                             'W62P'=>'80,4720',
                             'W63K'=>'80,4720',
                             'W63Kカメラ無し'=>'80,4720',
                             'W64K'=>'80,4720',
                             'W65K'=>'80,4720',
                             'W11H'=>'70,4730',
                             'W11K'=>'70,4730',
                             'A1403K'=>'60,4740',
        					 'A5502K'=>'60,4740',
                             'A5515K'=>'60,4740',
        					 'A5521K'=>'60,4740',
                             'B01K'=>'60,4740',
        					 'CA001'=>'60,4740',
                             'CA002'=>'60,4740',
        					 'E05SH'=>'60,4740',
                             'E06SH'=>'60,4740',
        					 'H001'=>'60,4740',
                             'HIY01'=>'60,4740',
        					 'K002'=>'60,4740',
                             'P001'=>'60,4740',
        					 'S001'=>'60,4740',
                             'SH001'=>'60,4740',
        					 'SH002'=>'60,4740',
        					 'SHY01'=>'60,4740',
        					 'SOX01'=>'60,4740',
                             'SOY01'=>'60,4740',
        					 'T001'=>'60,4740',
                             'T002'=>'60,4740',
        					 'TSX04'=>'60,4740',
                             'TSY01'=>'60,4740',
        					 'W21K'=>'60,4740',
                             'W31K'=>'60,4740',
        					 'W31KII'=>'60,4740',
                             'W32K'=>'60,4740',
        					 'W54S'=>'60,4740',
                             'W54SA'=>'60,4740',
        					 'W56T'=>'60,4740',
                             'W61T'=>'60,4740',
        					 'W62CA'=>'60,4740',
        					 'W62H'=>'60,4740',
        					 'W62SH'=>'60,4740',
                             'W62T'=>'60,4740',
        					 'W63CA'=>'60,4740',
                             'W63H'=>'60,4740',
        					 'W63S'=>'60,4740',
                             'W63T'=>'60,4740',
        					 'W64SA'=>'60,4740',
                             'W64SH'=>'60,4740',
        					 'W64T'=>'60,4740',
                             'W65S'=>'60,4740',
        					 'W65T'=>'60,4740',
                             'A5507SA'=>'50,4750',
        					 'A5508SA'=>'50,4750',
                             'A5510SA'=>'50,4750',
        					 'A5514SA'=>'50,4750',
                             'A5518SA'=>'50,4750',
                             'A5519SA'=>'50,4750',
        					 'A5520SA'=>'50,4750',
                             'A5520SAII'=>'50,4750',
        					 'A5522SA'=>'50,4750',
                             'A5527SA'=>'50,4750',
        					 'W63SA'=>'50,4750');

        $aryDeviceDoCoMo = array('D701i'=>'100,4700',
                             'D701iWM'=>'100,4700',
                             'D702i'=>'100,4700',
                             'D702iBCL'=>'100,4700',
                             'D702iF'=>'100,4700',
                             'D703i'=>'100,4700',
                             'D704i'=>'100,4700',
                             'D800iDS'=>'100,4700',
                             'D851iWM'=>'100,4700',
                             'D901i'=>'100,4700',
                             'D901iS'=>'100,4700',
                             'D902i'=>'100,4700',
                             'D902iS'=>'100,4700',
                             'D903i'=>'100,4700',
                             'D903iTV'=>'100,4700',
                             'F700i'=>'100,4700',
                             'F700iS'=>'100,4700',
                             'F702iD'=>'100,4700',
                             'F703i'=>'100,4700',
                             'F704i'=>'100,4700',
                             'F900i'=>'100,4700',
                             'F900iC'=>'100,4700',
                             'F900iT'=>'100,4700',
                             'F901iC'=>'100,4700',
                             'F901iS'=>'100,4700',
                             'F902i'=>'100,4700',
                             'F902iS'=>'100,4700',
                             'F903i'=>'100,4700',
                             'F903iBSC'=>'100,4700',
                             'F903iX'=>'100,4700',
                             'NM705i'=>'90,4710',
                             'NM706i'=>'90,4710');

        $aryDeviceSoftBank = array('705SC'=>'100,4700',
                             '705T'=>'100,4700',
                             '706SC'=>'100,4700',
                             '707SC'=>'100,4700',
                             '707SCII'=>'100,4700',
                             '709SC'=>'100,4700',
                             '804NK'=>'100,4700',
                             '832P'=>'90,4710',
                             '705N'=>'80,4720',
                             '706N'=>'80,4720',
                             '804N'=>'80,4720',
                             '804SS'=>'80,4720',
                             '805SC'=>'80,4720',
                             '824P'=>'80,4720',
                             '920SC'=>'80,4720',
                             '705SH'=>'60,4740',
                             '731SC'=>'60,4740',
                             '810T'=>'60,4740',
                             '811T'=>'60,4740',
                             '812SH'=>'60,4740',
                             '812SHs'=>'60,4740',
                             '812SHsII'=>'60,4740',
                             '812T'=>'60,4740',
                             '813SH'=>'60,4740',
                             '813SH for Biz'=>'60,4740',
                             '813T'=>'60,4740',
                             '814T'=>'60,4740',
                             '815T'=>'60,4740',
                             '816SH'=>'60,4740',
                             '820SH'=>'60,4740',
                             '820T'=>'60,4740',
                             '821SC'=>'60,4740',
                             '821SH'=>'60,4740',
                             '821T'=>'60,4740',
                             '822SH'=>'60,4740',
                             '823P'=>'60,4740',
                             '823SH'=>'60,4740',
                             '830SC'=>'60,4740',
                             '832T'=>'60,4740',
                             '905SH'=>'60,4740',
                             '910T'=>'60,4740',
                             '911SH'=>'60,4740',
                             '911T'=>'60,4740',
                             '912T'=>'60,4740',
                             '913SH'=>'60,4740',
                             '913SH G'=>'60,4740',
                             '920T'=>'60,4740',
                             '921T'=>'60,4740',
                             'DM001SH'=>'60,4740',
                             'DM002SH'=>'60,4740',
                             '820P'=>'50,4760',
                             '821P'=>'50,4760',
                             '822P'=>'50,4760',
                             '823T'=>'50,4760',
                             '824T'=>'50,4760',
                             '830T'=>'50,4760',
                             '831T'=>'50,4760',
                             '810P'=>'40,4780',
                             '822T'=>'40,4780',
                             '831P'=>'40,4780');

        $retValue = '0,4800';
        //mobile device name
        require_once 'MyLib/Mobile/Japan/Device.php';
        $mDevice = new MyLib_Mobile_Japan_Device();
        $deviceName = $mDevice->getDevice();
        $aryFind = array();
        //docomo
        if (1 == Zend_Registry::get('ua')) {
            $aryFind = $aryDeviceDoCoMo;
        }
        //softbank
        else if (2 == Zend_Registry::get('ua')) {
            $aryFind = $aryDeviceSoftBank;
        }
        //au
        else if (3 == Zend_Registry::get('ua')) {
            $aryFind = $aryDeviceAu;
        }
        if (array_key_exists($deviceName, $aryFind)) {
            $retValue = $aryFind[$deviceName];
        }

        return $retValue;
    }

    /**
     * get emoji in flash (for flash lite1.1)
     *
     * @param integer $mobileUa [1 docomo / 2 softbank / 3 au]
     * @param integer $forecast [0晴れ or 1曇り or 2雨]
     * @param string
     */
    private static function _getEmojiInFlash($forecast)
    {
        //docomo 1晴れ 2曇り 3雨
        //softbank 74晴れ 73曇り 75雨
        //au 44晴れ 107曇り 95雨
        require_once 'MyLib/Emoji/MobileForFlashLite.php';
        $mobileEmoji = new Emoji_MobileForFlashLite();
        $retValue = '';

        //docomo
        if (1 == Zend_Registry::get('ua')) {
            switch ($forecast) {
            	case 1:
            	    $emojiCode = 2;
                    break;
            	case 2:
            	    $emojiCode = 3;
            	    break;
            	default:
            	    $emojiCode = 1;
            	    break;
            }
            $retValue = $mobileEmoji->getEmoji('docomo', $emojiCode);
        }
        //softbank
        else if (2 == Zend_Registry::get('ua')) {
            switch ($forecast) {
            	case 1:
            	    $emojiCode = 73;
                    break;
            	case 2:
            	    $emojiCode = 75;
            	    break;
            	default:
            	    $emojiCode = 74;
            	    break;
            }
            $retValue = $mobileEmoji->getEmoji('softbank', $emojiCode);
        }
        //au
        else if (3 == Zend_Registry::get('ua')) {
            switch ($forecast) {
            	case 1:
            	    $emojiCode = 107;
                    break;
            	case 2:
            	    $emojiCode = 95;
            	    break;
            	default:
            	    $emojiCode = 44;
            	    break;
            }
            $retValue = $mobileEmoji->getEmoji('au', $emojiCode);
        }

        return $retValue;
    }

    /**
     * clear feed template cache info
     *
     * @param integer $uid
     * @param integer $isSelf [1-self 2-not self]
     */
    public static function clearFlash($uid, $isSelf)
    {
        Bll_Cache::delete(self::getCacheKey('getSchoolFlash', $uid . $isSelf));
    }
}
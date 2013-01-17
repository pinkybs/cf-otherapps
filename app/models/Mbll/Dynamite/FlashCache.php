<?php

/** @see Bll_Cache */
require_once 'Bll/Cache.php';
require_once 'Bll/Friend.php';

define('DYNAMITE_FLASH_ROOT', ROOT_DIR . '/www/static/apps/dynamite/mobile/swf');

/**
 * flash Cache
 *
 * @package    Bll/Cache
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create
 */
class Mbll_Dynamite_FlashCache
{
    private static $_prefix = 'Mbll_Dynamite_FlashCache';

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
// 基本情報
baseUrl = "{%baseurl%}";
fst     = "{%show_help%}";
ksid    = "{%ksid%}";
mm      = "{%money%}";
md      = "{%remain_bomb%}";
maxlife = "{%max_life%}";

h       = "{%url_home%}";
l       = "{%url_left%}";
r       = "{%url_right%}";

own     = "{%name%}";
ownid   = "{%profile_uid%}";
own_m   = "{%owner_game_mode%}"

viewid  = "{%uid%}";
view_m  = "{%viewer_game_mode%}"

rel     = "{%isFriend%}"

setUrl  = "{%url_set%}";
rmvUrl  = "{%url_remove%}";
bmbUrl  = "{%url_bomb%}";

// base.box1
main.b1.btn   = "{%hitman1_button_type%}";
main.b1.life  = "{%hitman1_health%}";
main.b1.price = "{%hitman1__bonus%}";
main.b1.d1.cnt = "{%hitman1_bomb1%}";
main.b1.d2.cnt = "{%hitman1_bomb2%}";
main.b1.d3.cnt = "{%hitman1_bomb3%}";
main.b1.d4.cnt = "{%hitman1_bomb4%}";

// base.box2
main.b2.btn   = "{%hitman2_button_type%}";
main.b2.life  = "{%hitman2_health%}";
main.b2.price = "{%hitman2__bonus%}";
main.b2.d1.cnt = "{%hitman2_bomb1%}";
main.b2.d2.cnt = "{%hitman2_bomb2%}";
main.b2.d3.cnt = "{%hitman2_bomb3%}";
main.b2.d4.cnt = "{%hitman2_bomb4%}";

// base.box3
main.b3.btn   = "{%hitman3_button_type%}";
main.b3.life  = "{%hitman3_health%}";
main.b3.price = "{%hitman3__bonus%}";
main.b3.d1.cnt = "{%hitman3_bomb1%}";
main.b3.d2.cnt = "{%hitman3_bomb2%}";
main.b3.d3.cnt = "{%hitman3_bomb3%}";
main.b3.d4.cnt = "{%hitman3_bomb4%}";

// base.box4
main.b4.btn   = "{%hitman4_button_type%}";
main.b4.life  = "{%hitman4_health%}";
main.b4.price = "{%hitman4__bonus%}";
main.b4.d1.cnt = "{%hitman4_bomb1%}";
main.b4.d2.cnt = "{%hitman4_bomb2%}";
main.b4.d3.cnt = "{%hitman4_bomb3%}";
main.b4.d4.cnt = "{%hitman4_bomb4%}";
*/

    /**
     * get flash size 230 or 240
     *
     * @param string $deviceName
     * @return boolean
     */
    private static function _checkFlashSize($deviceName)
    {
        $isFind = false;
        $aryFixDevice = array('F700i','F700iS','F702iD','F703i','F704i','F901iC','F901iS','F902i','F902iS','F903i',
        'F903iX','D701i','D701iWM','D702i','D702iBCL','D702iF','D703i','D704i','D800iDS',
        'D901i','D702iG','D901iS','M702iS','D851iWM','D902i','D902iS','D903i','D903iTV');
        foreach ($aryFixDevice as $dData) {
            if (!(strpos($deviceName, $dData) === false)) {
                $isFind = true;
                break;
            }
        }
        return $isFind;
    }

    /**
     * get agit flash
     * this function has not use cache for the moment
     *
     * @param array $mydynamiteInfo, viewer
     * @param integer $profileUid, owner
     * @param string $mixiUrl
     * @param string $appid
     * @return stream flash
     */
    public static function getNewFlash($mydynamiteInfo, $profileUid, $mixiUrl, $appid)
    {

        $mixiMobileBaseUrl = $mixiUrl;
        $appUrl = Zend_Registry::get('host') . '/mobile/dynamite/';

        require_once 'Dal/Dynamite/User.php';
        $dalDynamiteUser = Dal_Dynamite_User::getDefaultInstance();

        require_once 'Bll/Friend.php';
        $isFriend = Bll_Friend::isFriend($mydynamiteInfo['uid'], $profileUid);

        //agit info
        if (empty($profileUid) || $mydynamiteInfo['uid'] == $profileUid) {
            $agitInfo = $mydynamiteInfo;
            $profileUid = $mydynamiteInfo['uid'];
        }
        else {
            $agitInfo = $dalDynamiteUser->getUser($profileUid);

            require_once 'Bll/Cache/Dynamite.php';
            $hitmanInfo = Bll_Cache_Dynamite::getHitmanType();
            $agitInfo = array_merge($agitInfo, $hitmanInfo[$agitInfo['hitman_type'] - 1]);

            require_once 'Bll/User.php';
            Bll_User::appendPerson($agitInfo, 'uid');
        }
        if (empty($agitInfo)) {
            return false;
        }

        $flashName = ($agitInfo['pic_id'] < 10 ? ('0' . $agitInfo['pic_id']) : $agitInfo['pic_id']) . '.swf';

        //Mbll_Dynamite_FlashCache::clearFlash($flashName);

        //get xml
        $xmlString = self::_getFlashXml($flashName);

        //get neighber uids
        //all mode
        if (0 == $mydynamiteInfo['game_mode']) {
            $prevId = $dalDynamiteUser->getNeighberUid($profileUid, 'prev');
            if (empty($prevId)) {
                $prevId = $dalDynamiteUser->getNeighberUid($profileUid, 'last');
            }
            $nextId = $dalDynamiteUser->getNeighberUid($profileUid, 'next');
            if (empty($nextId)) {
                $nextId = $dalDynamiteUser->getNeighberUid($profileUid, 'first');
            }
        }
        //friend mode
        else {

            $fids = Bll_Friend::getFriends($mydynamiteInfo['uid']);

            $prevId = $dalDynamiteUser->getNeighberFriendUid($mydynamiteInfo['uid'], $profileUid, 'prev', $fids);
            if (empty($prevId)) {
                $prevId = $dalDynamiteUser->getNeighberFriendUid($mydynamiteInfo['uid'], $profileUid, 'last', $fids);
            }
            $nextId = $dalDynamiteUser->getNeighberFriendUid($mydynamiteInfo['uid'], $profileUid, 'next', $fids);
            if (empty($nextId)) {
                $nextId = $dalDynamiteUser->getNeighberFriendUid($mydynamiteInfo['uid'], $profileUid, 'first', $fids);
            }
        }

        //flash info combine and replace
        $aryAgit = array();

        $aryAgit['baseurl'] = $mixiMobileBaseUrl . urlencode($appUrl);
        $aryAgit['show_help'] = $mydynamiteInfo['show_mobile_flash_help'];
        $aryAgit['money'] = number_format($mydynamiteInfo['bonus']);
        $aryAgit['remain_bomb'] = $mydynamiteInfo['remainder_bomb_count'];
        $aryAgit['max_life'] = $agitInfo['max_life'];

        $aryAgit['url_home'] = $mixiMobileBaseUrl . urlencode($appUrl . 'home');
        $aryAgit['url_left'] = ($appUrl . 'agit?CF_uid=' . $prevId . '&amp;opensocial_app_id=' . $appid . '&amp;opensocial_owner_id='. $mydynamiteInfo['uid'] . '&amp;rand=' . time()) . '&amp;guid=ON';
        $aryAgit['url_right'] = ($appUrl . 'agit?CF_uid=' . $nextId . '&amp;opensocial_app_id=' . $appid . '&amp;opensocial_owner_id='. $mydynamiteInfo['uid'] . '&amp;rand=' . time()) . '&amp;guid=ON';

        $aryAgit['name'] = htmlspecialchars($agitInfo['unescapeDisplayName'], ENT_QUOTES, 'UTF-8');
        $aryAgit['profile_uid'] = $profileUid;
        $aryAgit['owner_game_mode'] = $agitInfo['game_mode'];

        $aryAgit['uid'] = $agitInfo['uid'];
        $aryAgit['viewer_game_mode'] = $mydynamiteInfo['game_mode'];

        $aryAgit['isFriend'] = $isFriend ? 1 : 0;

        $aryAgit['url_set'] = $mixiMobileBaseUrl . urlencode($appUrl . 'dynamiteset?CF_uid=' . $profileUid . '&CF_flashlite=1');
        $aryAgit['url_bomb'] = $mixiMobileBaseUrl . urlencode($appUrl . 'dynamitebomb?CF_uid=' . $profileUid . '&CF_flashlite=1');
        $aryAgit['url_remove'] = $mixiMobileBaseUrl . urlencode($appUrl . 'dynamiteremove?CF_flashlite=1');

        require_once 'Dal/Dynamite/Bomb.php';
        $dalDynamiteBomb = Dal_Dynamite_Bomb::getDefaultInstance();

        //bomb info
        for ($i=1; $i<=4; $i++) {
            $buttonType = '';
            $aryAgit['hitman' . $i . '_bomb1'] = '';
            $aryAgit['hitman' . $i . '_bomb2'] = '';
            $aryAgit['hitman' . $i . '_bomb3'] = '';
            $aryAgit['hitman' . $i . '_bomb4'] = '';
            $aryAgit['hitman' . $i . '_bonus'] = '0';
            $aryAgit['hitman' . $i . '_health'] = '';
            //$aryAgit['hitman' . $i . '_status'] = '';
            $aryAgit['hitman' . $i . '_button_type'] = '';

            if ( $agitInfo['hitman_life' . $i] > 0 ) {
                //get user hitman bomb info
                $hitmanBombAry = $dalDynamiteBomb->getBombUserHitmanBomb($agitInfo['uid'], $i);

                $canRemoveBomb = '0';
                $hadSetBomb = '0';
                $canBomb = '0';
                $canSetBomb = '1';

                $aryCount = count($hitmanBombAry);
                //show hitman bomb info
                for ( $k = 0, $kCount = $aryCount; $k < $kCount; $k++ ) {
                    //get bomb color
                    if ( $hitmanBombAry[$k]['uid'] == $mydynamiteInfo['uid'] ) {
                        $bombColor = $hitmanBombAry[$k]['bomb_power'] > 0 && $hitmanBombAry[$k]['needWait'] != 1 ? 'r' : 'p';
                    }
                    else {
                        $bombColor = 'b';
                    }

                    //get bomb power and color info
                    $aryAgit['hitman' . $i . '_bomb' . ($k + 1)] = $bombColor . $hitmanBombAry[$k]['bomb_power'];
                }

                if ( $mydynamiteInfo['uid'] != $agitInfo['uid'] ) {
                    //check game mode
                    if ( $mydynamiteInfo['game_mode'] == 0 && $agitInfo['game_mode'] == 1 && !$isFriend ) {
                        $canSetBomb = '0';
                    }
                    if ( $mydynamiteInfo['game_mode'] == 1 && $agitInfo['game_mode'] == 0 && !$isFriend ) {
                        $canSetBomb = '0';
                    }
                    if ( $mydynamiteInfo['game_mode'] == 1 && $agitInfo['game_mode'] == 1 && !$isFriend ) {
                        $canSetBomb = '0';
                    }
                }

                if ( $canSetBomb == '1' ) {
                    //get hitman button info
                    for ( $j = 0, $jCount = $aryCount; $j < $jCount; $j++ ) {
                        //check is my agit
                        if ( $agitInfo['uid'] == $mydynamiteInfo['uid'] ) {
                            //check can remove
                            if ( $hitmanBombAry[$j]['bomb_power'] > 0 ) {
                                $canRemoveBomb = '1';
                                break;
                            }
                            else {
                                $canRemoveBomb = '2';
                            }
                        }
                        else {
                            //check can bomb
                            if ( $hitmanBombAry[$j]['uid'] == $mydynamiteInfo['uid'] ) {
                                $hadSetBomb = '1';

                                if ( $hitmanBombAry[$j]['bomb_power'] > 0 && $hitmanBombAry[$j]['needWait'] != 1 ) {
                                    $canBomb = '1';
                                }
                                break;
                            }

                            //check can set
                            if ( $hadSetBomb || $agitInfo['hitman_bomb_count' . $i] >= 4 ) {
                                $canSetBomb = '0';
                            }
                        }
                    }

                    if ( $mydynamiteInfo['remainder_bomb_count'] < 1 ) {
                        $canSetBomb = '0';
                    }
                }

                //get button type info
                if ( $agitInfo['uid'] == $mydynamiteInfo['uid'] ) {
                    if ( $canRemoveBomb == '1' ) {
                        $buttonType = '3';
                    }
                    else if ( $canRemoveBomb == '2' ) {
                        $buttonType = '6';
                    }
                    else {
                        $buttonType = '';
                    }
                }
                else {
                    if ( $hadSetBomb == '1' ) {
                        $buttonType = $canBomb == '1' ? '2' : '5';
                    }
                    else {
                        $buttonType = $canSetBomb == '1' ? '1' : '4';
                    }
                }

                //get hitman reward
                $hitmanReward = number_format(round($agitInfo['bonus'] * 0.1 > 10000 ? 10000 : $agitInfo['bonus'] * 0.1));
                $aryAgit['hitman' . $i . '_bonus'] = $hitmanReward;
                $aryAgit['hitman' . $i . '_health'] = $agitInfo['hitman_life' . $i];
                //$aryAgit['hitman' . $i . '_status'] = $agitInfo['hitman_life' . $i] < $agitInfo['max_life']/2 ? '1' : '0';
                $aryAgit['hitman' . $i . '_button_type'] = $buttonType;
            }
        }

        foreach ($aryAgit as $key=>$value) {
            $xmlString = str_replace("{%" . $key . "%}", $value, $xmlString);
        }
        //$xmlString = str_replace('&amp;area=', '%26area%3D', $xmlString);
        //file_put_contents(DYNAMITE_FLASH_ROOT . "/flashxml.xml", $xmlString);
        //set process param
        $descriptorspec = array(
           0 => array("pipe", "r"),
           1 => array("pipe", "w"),
           2 => array("file", "xml2swf_error.txt", "a")
        );

        $pipes = array();
        //XML to SWF
        // run swfmill and get $process
        $process = proc_open('/usr/local/swfmill/bin/swfmill -e cp932 xml2swf stdin stdout', $descriptorspec, $pipes);

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

        $dalDynamiteUser->updateUserBasicInfo($mydynamiteInfo['uid'], array('show_mobile_flash_help' => 1));

        return $swfOutput;
    }

    /**
     * get base flash xml
     *
     * @param string $flashName
     * @return string  flash xml
     */
    private static function _getFlashXml($flashName)
    {
        /*
        $key = self::getCacheKey('getFlashXml', $flashName);

        if (!$xmlString = Bll_Cache::get($key)) {

            $swfData = file_get_contents(DYNAMITE_FLASH_ROOT . '/' . $flashName);

            // SWF to XML
            // set process param
            $descriptorspec = array(
               0 => array("pipe", "r"),
               1 => array("pipe", "w"),
               2 => array("file", "swf2xml_error.txt", "a")
            );

            $pipes = array();

            // run swfmill and get $process
            $process = proc_open('/usr/local/swfmill/bin/swfmill -e cp932 swf2xml stdin stdout', $descriptorspec, $pipes);

            if (is_resource($process)) {
                // set param $swfData
                fwrite($pipes[0], $swfData);
                fclose($pipes[0]);

                // get xmlString
                $xmlString = stream_get_contents($pipes[1]);
                fclose($pipes[1]);

                // close process
                proc_close($process);
            }
            if ($xmlString) {
                Bll_Cache::set($key, $xmlString, Bll_Cache::LIFE_TIME_ONE_MONTH);
            }
        }
        */
        $xmlName = substr($flashName, 0, strlen($flashName)-4);

        $xmlString = file_get_contents(DYNAMITE_FLASH_ROOT . '/' . $xmlName . ".xml");

        return $xmlString;
    }

    /**
     * clear feed template cache info
     *
     * @param string $flashName
     */
    public static function clearFlash($flashName)
    {
        Bll_Cache::delete(self::getCacheKey('getFlashXml', $flashName));
    }
}
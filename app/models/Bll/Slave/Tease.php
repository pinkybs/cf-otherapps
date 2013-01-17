<?php
require_once 'Bll/Abstract.php';

/**
 * Mixi App Tease logic Operation
 *
 * @package    Bll/Slave
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/07/06    xiali
 */
final class Bll_Slave_Tease extends Bll_Abstract
{

    public function addTease($info,$uid)
    {
        require_once 'Dal/Slave/Tease.php';
        $dalTease = Dal_Slave_Tease::getDefaultInstance();

        require_once 'Dal/Slave/Slave.php';
        $dalSlave = Dal_Slave_Slave::getDefaultInstance();

        require_once 'Dal/Slave/Forbidword.php';
        $dalForbid = Dal_Slave_Forbidword::getDefaultInstance();

        require_once 'Dal/Slave/CashPriceChangeLog.php';
        $dalChangeLog = Dal_Slave_CashPriceChangeLog::getDefaultInstance();

        $result = 1;
        try {
            $friendId = $info['target_uid'];
            $masterid = $info['master_uid'];
            $isExists = false;
            $rowFriend = $dalSlave->getSlaveById($friendId);
            if (empty($rowFriend)) {
                require_once 'Bll/Slave/Slave.php';
                $bllSlave = new Bll_Slave_Slave();
                $isExists = $bllSlave->newSlaveUser($friendId, $isActive = false);
                if (!$isExists) {
                    return 0;
                }
            }
            $this->_wdb->beginTransaction();
            $slaveid = $info['actor_uid'];
            $teaseid = $info['tid'];


            if ($slaveid === $friendId) {
                $this->_wdb->rollBack();
                return 0;
            }

            if (!$dalSlave->isSlaveByMasterId($slaveid, $uid)) {
            	$this->_wdb->rollBack();
                return 0;
            }


            $rowSlave = $dalSlave->getSlaveByIdLock($slaveid);
            $rowTease = $dalTease->getTeaseById($teaseid);

            $price = 0;
            $slave_Price = $dalSlave->getPriceById($slaveid);

            if ($teaseid == 38) {

            	// forbid user custom tease
            	$isForbidUser = $dalSlave->getSlaveById($uid);
            	if ($isForbidUser['is_fobid_custom_tease'] == 1) {
            		$this->_wdb->rollBack();
            		return 0;
            	}

                $price = ceil($slave_Price * 0.1);

                $isPunish = false;
                $aryForbidword = $dalForbid->listForbidword();
                foreach ($aryForbidword as $fword) {
                    $pos = strpos($info['custom_tease'], $fword['word']);
                    if ($pos !== false) {
                        $isPunish = true;
                        break;
                    }
                }

                //punish master
                if ($isPunish) {
                    //update master info
                    $aryMaster = array();

                    $master_price = $dalSlave->getPriceById($masterid);

                    $aryMaster['price'] = ceil($master_price * 0.8);
                    $dalSlave->updateSlave($aryMaster, $masterid);

                    $aryLog = array();
                    $aryLog['actor_uid'] = $masterid;
                    $aryLog['target_uid'] = $friendId;
                    $aryLog['c_floating'] = 0;
                    $aryLog['p_floating'] = '-' . ceil($master_price * 0.2);
                    $aryLog['type'] = 6;
                    $aryLog['create_time'] = time();
                    $dalChangeLog->insertCashPriceChangeLog($aryLog);
                    $result = 2;
                }
            }
            else {
                switch ($rowTease['level']) {
                    case 1 :
                        $price = ceil($slave_Price * 0.05);
                        break;
                    case 2 :
                        $price = ceil($slave_Price * 0.1);
                        break;
                    default :
                        $price = ceil($slave_Price * 0.15);
                        break;
                }
            }

            if ($result != 2) {
                $arySlave = array();
                $arySlave['price'] = $slave_Price + $price;
                $dalSlave->updateSlave($arySlave, $slaveid);

                $aryLog['actor_uid'] = $slaveid;
                $aryLog['target_uid'] = $friendId;
                $aryLog['c_floating'] = 0;
                $aryLog['p_floating'] = $price;
                $aryLog['type'] = 6;
                $dalChangeLog->insertCashPriceChangeLog($aryLog);

                $id = $dalTease->insertTease($info);
                $result = 1;
            }

            $this->_wdb->commit();

            //send feed
            if ($result != 2) {
                require_once 'Bll/User.php';
                require_once 'Bll/Slave/FeedMessage.php';
                $bllFeed = new Bll_Slave_FeedMessage();
                $userInfo = Bll_User::getPerson($slaveid);
                $userName = $userInfo->getDisplayName();

                $tarInfo = Bll_User::getPerson($friendId);
                $tarName = $tarInfo->getDisplayName();

                $actor = '<a href="/slave/profile?uid=' . $slaveid . '" >' . $userName . '</a>';
                $target = '<a href="/slave/profile?uid=' . $friendId . '" >' . $tarName . '</a>';

                if ($teaseid == 38) {
                	$pic = $info['custom_pic_small'];
					$actionName = $info['custom_tease'];

                    $aryMsgMine = array('{*target*}' => $target, '{*actionname*}' => $actionName);
                    $aryMsgTar = array('{*actor*}' => $actor, '{*actionname*}' => $actionName);
					$aryMsgFriend = array('{*actor*}' => $actor, '{*target*}' => $target, '{*actionname*}' => $actionName);

                    $bllFeed->newFeedMessage(2, 38, 'feed_tpl_actor', $slaveid, $slaveid, $aryMsgMine, 1, $pic);
                    $bllFeed->newFeedMessage(2, 38, 'feed_tpl_target', $slaveid, $friendId, $aryMsgTar, 1, $pic);
                    $bllFeed->newFeedMessage(2, 38, 'feed_tpl_friend', $slaveid, $masterid, $aryMsgFriend, 1, $pic);
                }
                else {
                    $aryMsgMine = array('{*target*}' => $target);
                    $aryMsgTar = array('{*actor*}' => $actor);
                    $aryMsgFriend = array('{*actor*}' => $actor, '{*target*}' => $target);

                    $bllFeed->newFeedMessage(2, $teaseid, 'feed_tpl_actor', $slaveid, $slaveid, $aryMsgMine, 1);
                    $bllFeed->newFeedMessage(2, $teaseid, 'feed_tpl_target', $slaveid, $friendId, $aryMsgTar, 1);
                    $bllFeed->newFeedMessage(2, $teaseid, 'feed_tpl_friend', $slaveid, $masterid, $aryMsgFriend, 1);
                }

                //send feed to friends already installed app
                require_once 'Bll/Slave/Friend.php';
                $aryIds = Bll_Slave_Friend::getFriends($slaveid);
                $aryIds2 = Bll_Slave_Friend::getFriends($friendId);
                $aryIdsTmp = array_merge($aryIds, $aryIds2);
                $aryIdsSend = array_unique($aryIdsTmp);

                foreach ($aryIdsSend as $fid) {
                    if ($fid == $friendId || $fid == $masterid) {
                        continue;
                    }
                    if ($teaseid == 38) {
                        $bllFeed->newFeedMessage(2, 38, 'feed_tpl_friend', $slaveid, $fid, $aryMsgFriend, 0, $info['custom_pic_small']);
                    }
                    else {
                        $bllFeed->newFeedMessage(2, $teaseid, 'feed_tpl_friend', $slaveid, $fid, $aryMsgFriend, 0);
                    }
                }
            }
        }
        catch (Exception $e) {
            $result = 0;
            $this->_wdb->rollBack();
            err_log($e->getMessage());
        }
        return $result;
    }

    public function upPhoto($photoField, $uid)
    {
        try {
            //if photo upload is not empty
            if (!empty($_FILES[$photoField]) && is_uploaded_file($_FILES[$photoField]['tmp_name'])) {
                //upload photo
                require_once 'Bll/PhotoUpload.php';
                $photoUpload = new Bll_PhotoUpload(array('field' => $photoField, 'section' => 2, 'id' => $uid));
                $uploadfile = $photoUpload->doUploadBySize(74, 74);
                //if do upload
                if ($uploadfile) {
                    $smallPic = Zend_Registry::get('photo') . '/' . $uploadfile['filename'];
                }
                else {
                    return null;
                }
            }
        }
        catch (Exception $e) {
            return null;
        }
        return $smallPic;
    }

    public function writeImage($uid, $imgUrl, $photoUrl)
    {
    	 try {
            require_once 'Bll/Slave/Tease.php';
            $bllTease = new Bll_Slave_Tease();

            //download image from url
            $basePath = Zend_Registry::get('photoBasePath');
            $intTime = time();
            $imgName = $bllTease->getImage($imgUrl, $basePath . '/apps/slave/tmp/' . $uid . '_' . $intTime);

            if ($imgName === false) {
            	return false;
            }

            //change image to 74*74
            require_once 'MyLib/Image/Edit.php';
            $libImgEdit = new MyLib_Image_Edit();

            $to = $basePath . '/apps/slave/0/' . $uid . '/' . $uid . '_' . $intTime . '.jpg';
            if (!is_dir($basePath . '/apps/slave/0/' . $uid . '/')) {
                mkdir($basePath . '/apps/slave/0/' . $uid . '/');
            }
            $libImgEdit->resize($imgName, $to, 74, 74);
            unlink($imgName);
        }
        catch (Exception $e) {
            return false;
        }

        $newImageUrl = $photoUrl . '/apps/slave/0/' . $uid . '/' . $uid . '_' . $intTime . '.jpg';
        return $newImageUrl;
    }

    public function getImage($url, $filename = '')
    {
        if(!$url) {
            return false;
        }

        if(!$filename) {
            $ext = strrchr(strtolower($url), '.') ;
            if($ext != '.jpg' && $ext!='.jpeg') {
                return false ;
            }
            $str = explode('/', $url) ;
            $filename = $str[count($str)-1] ;
        }
        $str = file($url);

        $count = count($str);

        if ($count > 380) {
        	return false;
        }

        for ($i = 0; $i < $count; $i++){
            $file .= $str[$i];
        }
        $listfile = $filename;
        $fp = fopen($listfile, 'w');
        flock($fp,2);
        fwrite($fp, $file);
        fclose($fp);

        return $filename;
    }
}

<?php

require_once 'Bll/Abstract.php';

/**
 * Mixi App Slave-Work logic Operation
 *
 * @package    Bll/Slave
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/07/10    zhangxin
 */
final class Bll_Slave_Work extends Bll_Abstract
{

    /**
     * init Work by uid
     *
     * @param string $uid
     * @return boolean
     */
    public function initWork($uid)
    {
        try {
            require_once 'Dal/Slave/Slave.php';
            $dalSlave = Dal_Slave_Slave::getDefaultInstance();
            require_once 'Dal/Slave/Work.php';
            $dalWork = Dal_Slave_Work::getDefaultInstance();

            $this->_wdb->beginTransaction();

            $rowSlave = $dalSlave->getSlaveById($uid);
            if (empty($rowSlave)) {
                $this->_wdb->rollBack();
                return false;
            }

            $lstWork = $dalWork->listBasicWork();
            foreach ($lstWork as $wItem) {
                $aryInfo = array();
                $aryInfo['uid'] = $uid;
                $aryInfo['category_id'] = $wItem['category_id'];
                $dalWork->insertSlaveWork($aryInfo);
            }

            $this->_wdb->commit();

            return true;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            debug_log('Bll/Slave/Work/initWork:' . $e->getMessage());
            return false;
        }
    }

	/**
	 * make slave work
	 *
	 * @param string $uid
	 * @param string $tarUid
	 * @param integer $workId
	 * @return array
	 */
	public function work($uid, $tarUid, $workId)
	{
		try {
			require_once 'Dal/Slave/Slave.php';
			$dalSlave = Dal_Slave_Slave::getDefaultInstance();
			require_once 'Dal/Slave/Work.php';
			$dalWork = Dal_Slave_Work::getDefaultInstance();
			require_once 'Dal/Slave/CashPriceChangeLog.php';
			$dalChangeLog = Dal_Slave_CashPriceChangeLog::getDefaultInstance();

			$this->_wdb->beginTransaction();

			$rowMaster= $dalSlave->getSlaveByIdLock($uid);
			$rowSlave = $dalSlave->getSlaveByIdLock($tarUid);
			//not my slave
			if (empty($rowMaster) || empty($rowSlave)) {
			    $this->_wdb->rollBack();
				return false;
			}
			if ($uid != $rowSlave['master_id']) {
				$this->_wdb->rollBack();
				return false;
			}

			$rowWork = $dalWork->getSlaveWorkByKeyLock($tarUid, $workId);
			if (empty($rowWork)) {
			    $this->_wdb->rollBack();
				return false;
			}
			$rowNbWork = $dalWork->getNbWorkByKey($workId, $rowWork['wlevel']);
		    if (empty($rowNbWork)) {
			    $this->_wdb->rollBack();
				return false;
			}
			//health not enough
			if ($rowSlave['health'] < $rowNbWork['consume_health']) {
                $this->_wdb->rollBack();
				return false;
			}

			//work
			$lstQualify = $dalWork->listNbWorkLevelUpQualify($workId);
			$dalWork->insertWorkDetail(array('uid'=>$tarUid,'category_id'=>$workId,'wlevel'=>$rowWork['wlevel'],'create_time'=>time()));
			$isLevelUp = false;
			$aryInfo = array();
            $aryInfo['wqualify'] = $rowWork['wqualify'] + 1;
            //is normal work level up
            if (0 == $rowNbWork['isspecial']) {
                foreach ($lstQualify as $qualifyData) {
                    if ($aryInfo['wqualify'] == $qualifyData['levelup_qualify'] && $qualifyData['levelup_qualify'] < 20) {
                        $aryInfo['wlevel'] = $rowWork['wlevel'] + 1;
                        $isLevelUp = true;
                        $rowNextLev = $dalWork->getNbWorkByKey($workId, $aryInfo['wlevel']);
                        break;
                    }
                }
            }
            $aryInfo['last_working_time'] = time();
			$dalWork->updateSlaveWork($aryInfo, $tarUid, $workId);

			//is special work level up
			$cntLevel5 = $dalWork->getWorkLevelFiveCountByUid($tarUid);
			if ($cntLevel5 > 0 && $isLevelUp) {
			    $rowSpeWork = $dalWork->getSlaveWorkByKey($tarUid, 9);
			    if (empty($rowSpeWork)) {
			        $dalWork->insertSlaveWork(array('uid' => $tarUid, 'category_id' => 9));
			    }

			    $aryInfoSpe = array();
			    $aryInfoSpe['wlevel'] = 1;
			    //update special work level
		        if (2 == $cntLevel5) {
                    $aryInfoSpe['wlevel'] = 2;
		        }
		        else if (4 == $cntLevel5) {
                    $aryInfoSpe['wlevel'] = 3;
		        }
			    else if (6 == $cntLevel5) {
                    $aryInfoSpe['wlevel'] = 4;
		        }
		        else if (7 == $cntLevel5) {
                    $aryInfoSpe['wlevel'] = 5;
		        }
		        $dalWork->updateSlaveWork($aryInfoSpe, $tarUid, 9);
			}

			//update slave info
			$priceUpPercent = 0;
            $arySlave = array();
            $arySlave['health'] = $rowSlave['health'] - $rowNbWork['consume_health'];
            $arySlave['cash'] = $rowSlave['cash'] + ceil($rowNbWork['salary'] * 0.1);
            if ($isLevelUp) {
                $percent = $dalWork->getMaxWorkLevelByUid($tarUid);
                if ($percent > 1) {
                    $arySlave['price'] = $rowSlave['price'] + ceil($rowSlave['price'] * $percent / 10);
                    $priceUpPercent = $percent * 10;
                }
            }
            $dalSlave->updateSlave($arySlave, $tarUid);

    		//update master info
    		$aryMaster = array();
    		$aryMaster['cash'] = $rowMaster['cash'] + ceil($rowNbWork['salary'] * 0.9);
    		$aryMaster['total_slave_price'] = $dalSlave->getSlavePriceByMaster($uid);
    		$dalSlave->updateSlave($aryMaster, $uid);

            //change log
			$aryLog = array();
			//master
			$aryLog['actor_uid'] = $uid;
			$aryLog['target_uid'] = $tarUid;
			$aryLog['c_floating'] = ceil($rowNbWork['salary'] * 0.9);
			$aryLog['p_floating'] = 0;
			$aryLog['type'] = 3;
			$aryLog['create_time'] = time();
			$dalChangeLog->insertCashPriceChangeLog($aryLog);
			//slave
			$aryLog['actor_uid'] = $tarUid;
			$aryLog['target_uid'] = $uid;
			$aryLog['c_floating'] = ceil($rowNbWork['salary'] * 0.1);
			if ($priceUpPercent) {
			    $aryLog['p_floating'] = ceil($rowSlave['price'] * $percent / 10);
			}
			$aryLog['type'] = 3;
			$aryLog['create_time'] = time();
			$dalChangeLog->insertCashPriceChangeLog($aryLog);

			$this->_wdb->commit();

			//send feed
			$staticUrl = Zend_Registry::get('static');
		    require_once 'Bll/User.php';
			require_once 'Bll/Slave/FeedMessage.php';
			$bllFeed = new Bll_Slave_FeedMessage();
			$userInfo = Bll_User::getPerson($uid);
			$userName = $userInfo->getDisplayName();
			//$userUrl = $userInfo->getProfileUrl();
			//$userPic = $userInfo->getThumbnailUrl();
			$actor = '<a href="/slave/profile?uid=' . $uid . '" >' . $userName . '</a>';

			$tarInfo = Bll_User::getPerson($tarUid);
            $tarName = $tarInfo->getDisplayName();
            //$tarUrl = $tarInfo->getProfileUrl();
            //$tarPic = $tarInfo->getThumbnailUrl();
            $target = '<a href="/slave/profile?uid=' . $tarUid . '" >' . $tarName . '</a>';

            $aryMsgMine = array('{*target*}' => $target, '{*workname*}' => $rowNbWork['wname'], '{*money*}' => '￥' . number_format($rowNbWork['salary']));
			$bllFeed->newFeedMessage(1, 11, 'feed_tpl_actor', $uid, $uid, $aryMsgMine, 1, $staticUrl . $rowNbWork['pic_big']);
			$aryMsgTar = array('{*actor*}' => $actor, '{*workname*}' => $rowNbWork['wname'], '{*money*}' => '￥' . number_format($rowNbWork['salary']));
			$bllFeed->newFeedMessage(1, 11, 'feed_tpl_target', $uid, $tarUid, $aryMsgTar, 1, $staticUrl . $rowNbWork['pic_big']);

			//send feed to friends already installed app
			require_once 'Bll/Slave/Friend.php';
			$aryIds = Bll_Slave_Friend::getFriends($uid);
			$aryIds2 = Bll_Slave_Friend::getFriends($tarUid);
			$aryIdsTmp = array_merge($aryIds, $aryIds2);
			$aryIdsSend = array_unique($aryIdsTmp);
			$aryMsgFriend = array('{*actor*}' => $actor, '{*target*}' => $target, '{*workname*}' => $rowNbWork['wname'], '{*money*}' => '￥' . number_format($rowNbWork['salary']));
			foreach ($aryIdsSend as $fid) {
				$bllFeed->newFeedMessage(1, 11, 'feed_tpl_friend', $uid, $fid, $aryMsgFriend, 0, $staticUrl . $rowNbWork['pic_big']);
			}
			//level up
		    if (!empty($rowNextLev)) {
                $bllFeed->newFeedMessage(1, 12, 'feed_tpl_actor', $tarUid, $tarUid, array('{*jobname*}' => $rowNextLev['wtitle']), 1);
                $aryIdsUp = Bll_Slave_Friend::getFriends($tarUid);
                foreach ($aryIdsUp as $fidUp) {
                    $bllFeed->newFeedMessage(1, 12, 'feed_tpl_friend', $tarUid, $fidUp, array('{*actor*}' => $target, '{*jobname*}' => $rowNextLev['wtitle']), 0);
                }
            }

            $result = array();
            $result['work_gain'] = ceil($rowNbWork['salary'] * 0.9);
            $result['next_level'] = empty($rowNextLev) ? '' : $rowNextLev['wtitle'];
            $result['price_up_percent'] = empty($priceUpPercent) ? '' : $priceUpPercent;

			return $result;
		}
		catch (Exception $e) {
			$this->_wdb->rollBack();
			debug_log('Bll/Slave/Work/work:' . $e->getMessage());
			return false;
		}
	}

	/**
     * revovery slave ' health
     *
     * @param string $uid
     * @return integer
     */
    public function recoveryHealth()
    {
        try {
            require_once 'Dal/Slave/Slave.php';
            $dalSlave = new Dal_Slave_Slave($this->_config);

            $this->_wdb->beginTransaction();
            $lstLock = $dalSlave->listSlaveRecoveryHealthLock();
            $result = $dalSlave->recoverySlaveHealth();
            $this->_wdb->commit();

            return $result ? $result->rowCount() : false;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            debug_log('Bll/Slave/Work/recoveryHealth:' . $e->getMessage());
            return false;
        }
    }

}
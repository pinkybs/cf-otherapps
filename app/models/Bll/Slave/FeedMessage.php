<?php

require_once 'Bll/Abstract.php';

/**
 * Mixi App Feed Message logic Operation
 *
 * @package    Bll/Slave
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/06/12    zhangxin
 */
final class Bll_Slave_FeedMessage extends Bll_Abstract
{

    /**
     * new Feed Message
     *
     * @param integer $type [1:system feed /2:tease feed]
     * @param integer $feedId
     * @param string $tplName [feed_tpl_friend / feed_tpl_actor / feed_tpl_target / feed_tpl_relative]
     * @param string $uid
     * @param string $tarUid
     * @param array $aryInfo
     * @param integer $isMine [0:not mine rel/1:is mine]
     * @param string $picUrl
     * @param integer $time
     * @return integer
     */
    public function newFeedMessage($type, $feedId, $tplName, $uid, $tarUid, $aryInfo, $isMine=0, $picUrl=null, $time=null)
    {
        $staticUrl = Zend_Registry::get('static');

        try {
            require_once 'Dal/Slave/FeedMessage.php';
            $dalFeed = Dal_Slave_FeedMessage::getDefaultInstance();

            // system feed
            if (1 == $type) {
                $rowTpl = $dalFeed->getNbFeedTplById($feedId);
                if (empty($rowTpl)) {
                    return false;
                }
                $template = $rowTpl[$tplName];
                if (empty($picUrl)) {
                    $picUrl = $staticUrl . $rowTpl['pic_big'];
                }
            }

            //tease feed
            else {
				$rowTpl = $dalFeed->getNbTeaseFeedTplById($feedId);
            	if (empty($rowTpl)) {
                    return false;
                }
                $template = $rowTpl[$tplName];
                if (empty($picUrl)) {
                    $picUrl = $staticUrl . $rowTpl['pic_big'];
                }
            }

            if ($aryInfo) {
                foreach ($aryInfo as $k => $v) {
                    $keys[] = $k;
                    $values[] = $v;
                }
                $template = str_replace($keys, $values, $template);
            }
            $aryFeed = array();
            $aryFeed['uid'] = $uid;
            $aryFeed['to_uid'] = $tarUid;
            $aryFeed['message'] = $template;
            $aryFeed['pic_url'] = $picUrl;
            $aryFeed['type'] = $type;
            $aryFeed['ismine'] = $isMine;
            $aryFeed['create_time'] = empty($time) ? time() : $time;
            $id = $dalFeed->insertFeedMessage($aryFeed);

            return $id;
        }
        catch (Exception $e) {
            debug_log('Bll/Slave/FeedMessage/newFeedMessage:' . $e->getMessage());
            return false;
        }
    }

    /**
     * delete Feed Message
     *
     * @param integer $id
     * @return boolean
     */
    public function delFeedMessage($id)
    {
        try {
            require_once 'Dal/Slave/FeedMessage.php';
            $dalFeed = Dal_Slave_FeedMessage::getDefaultInstance();

            return $dalFeed->updateFeedMessage(array('isdelete' => 1), $id);
        }
        catch (Exception $e) {
            debug_log('Bll/Slave/FeedMessage/delFeedMessage:' . $e->getMessage());
            return false;
        }
    }

}
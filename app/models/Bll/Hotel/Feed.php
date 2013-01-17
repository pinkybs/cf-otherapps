<?php

require_once 'Bll/Abstract.php';

/**
 * Mixi App Feed Message logic Operation
 *
 * @package    Bll/Hotel
 * @copyright  Copyright (c) 2009 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/10/28    xial
 */
final class Bll_Hotel_Feed extends Bll_Abstract
{

    /**
     * new Feed Message
     *
     * @param integer $feedId
     * @param string $uid
     * @param array $aryInfo
     * @param int $fee
     * @param integer $isMine [0:not mine rel/1:is mine]
     * @return integer
     */
    public function newFeedMessage($feedId, $uid, $tarUid, $aryInfo, $fee, $isMine=0)
    {
        $staticUrl = Zend_Registry::get('static');
        try {
            require_once 'Dal/Hotel/Feed.php';
            $dalFeed = Dal_Hotel_Feed::getDefaultInstance();

            $rowTpl = $dalFeed->getFeedTplById($feedId);
            if (empty($rowTpl)) {
                return false;
            }
            $template = $rowTpl['action_name'];
            if ($aryInfo) {
                foreach ($aryInfo as $k => $v) {
                    $keys[] = $k;
                    $values[] = $v;
                }
                $template = str_replace($keys, $values, $template);
            }
            $aryFeed = array();
            $aryFeed['action_uid'] = $uid;
            $aryFeed['target_uid'] = $tarUid;
            $aryFeed['message'] = $template;
            $aryFeed['fee'] = $fee;
            $aryFeed['ismine'] = $isMine;
            $aryFeed['create_time'] = time();
            $id = $dalFeed->insertFeed($aryFeed, 'hotel_feed_tal');

            return $id;
        }
        catch (Exception $e) {
            return false;
        }
    }
}
<?php

require_once 'Bll/Abstract.php';

/**
 * Mixi App Feed Message logic Operation
 *
 * @package    Bll/Chat
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/06/12    zhangxin
 */
final class Bll_Chat_FeedMessage extends Bll_Abstract
{

    /**
     * new Feed Message
     *
     * @param integer $templateId
     * @param integer $cid
     * @param string $uid
     * @param string $tarUid
     * @param string $tarUid
     * @param array $aryInfo
     * @return integer
     */
    public function newFeedMessage($templateId, $cid, $uid, $tarUid, $aryInfo)
    {
        try {
            require_once 'Dal/Chat/FeedMessage.php';
            $dalFeed = Dal_Chat_FeedMessage::getDefaultInstance();

            switch ($templateId) {
    			case 1 :
    				$template = "{*actor*}からチャットの招待状が届いています。";
                    $link = '/chat/confirm?cid=' . $cid;
    				break;
    			case 2 :
    				$template = "{*actor*}が、{*chat_name*}に出席表明しました。";
    				$link = '/chat/view?cid=' . $cid;
    				break;
    			case 3 :
    				$template = "{*actor*}が、{*chat_name*}の開催時刻を変更しました。";
    				$link = '/chat/view?cid=' . $cid;
    				break;
    			case 4 :
    				$template = "{*actor*}が、{*chat_name*}の開催を取り消しました。";
    				$link = '/chat/add?cancel=90006&cid=' . $cid;
    				break;
    			case 5 :
    				$template = "{*actor*}が、{*chat_name*}に欠席表明しました。";
    				$link = '/chat/view?cid=' . $cid;
    				break;
    			case 6 :
    				$template = "あと15分で、{*chat_name*}開始の時間です。";
    				$link = '/chat/view?cid=' . $cid;
    				break;
    			case 7 :
    				$template = "{*chat_name*}が始まりました！";
    				$link = '/chat/room?cid=' . $cid;
    				break;
    			default:;
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
            $aryFeed['tar_uid'] = $tarUid;
            $aryFeed['message'] = $template;
            $aryFeed['link'] = $link;
            $aryFeed['create_time'] = time();
            $aryFeed['tpl_type'] = $templateId;
            $id = $dalFeed->insertFeedMessage($aryFeed);

            return $id;
        }
        catch (Exception $e) {
            debug_log('Bll/Chat/FeedMessage/newFeedMessage:' . $e->getMessage());
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
            require_once 'Dal/Chat/FeedMessage.php';
            $dalFeed = Dal_Chat_FeedMessage::getDefaultInstance();
            $rowFeed = $dalFeed->getFeedMessageById($id);
            $result = false;
            if (!empty($rowFeed)
                    && (2 == $rowFeed['tpl_type'] || 3 == $rowFeed['tpl_type'] || 5 == $rowFeed['tpl_type'] || 6 == $rowFeed['tpl_type'])) {
                $dalFeed->updateFeedMessage(array('isdelete' => 1), $id);
            }
            return $result;
        }
        catch (Exception $e) {
            debug_log('Bll/Chat/FeedMessage/delFeedMessage:' . $e->getMessage());
            return false;
        }
    }

}
<?php

/**
 * Bll BatchWork
 * DB Auto Statistic Batch Work Logic Layer
 *
 * @package    Bll/Chat
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/06/12    zhangxin
 */
class Bll_Chat_BatchWork
{
    /**
     * db config
     * @var array
     */
    protected $_config;

    /**
     * db read adapter
     * @var Zend_Db_Abstract
     */
    protected $_rdb;

    /**
     * db write adapter
     * @var Zend_Db_Abstract
     */
    protected $_wdb;

    /**
     * construct
     *
     * @param array $config ( db config )
     */
    public function __construct($dbConfig = null)
    {
        if (is_null($dbConfig)) {
            $dbConfig = getDBConfig();
        }
        $this->_rdb = $dbConfig['readDB'];
        $this->_wdb = $dbConfig['writeDB'];
        $this->_config = $dbConfig;
    }

    /**
     * do batch send feed by Ready Start chat
     *
     * @param integer $runingDate
     * @return boolean
     */
    public function doBatchSendReadyStartChatFeed($runingDate)
    {

        try {

            require_once 'Dal/Chat/Chat.php';
            $dalChat = new Dal_Chat_Chat($this->_config);
            //get ready to start chat
            $lstChat = $dalChat->listReadyToBeginChat($runingDate);
            if (empty($lstChat) || count($lstChat) == 0) {
                //debug_log('No chat is ready to Begin');
                return false;
            }

            debug_log(' ');
            debug_log("***** Start *****" . "***** Date:" . date('Y-m-d H:i:s', $runingDate) . " ***** batch begin!");

            require_once 'Dal/Chat/Member.php';
            $dalMem = new Dal_Chat_Member($this->_config);

            require_once 'Bll/Chat/FeedMessage.php';
            $bllFeed = new Bll_Chat_FeedMessage($this->_config);

            $this->_wdb->beginTransaction();

            foreach ($lstChat as $chatData) {
                $cid = $chatData['cid'];
                $dalChat->updateChat(array('isbatchfeedsent' => 1), $cid);

                //get chat member
                $lstMem = $dalMem->listChatMember($cid);
                foreach ($lstMem as $memData) {
                    $bllFeed->newFeedMessage(6, $cid, $chatData['uid'], $memData['uid'], array('{*chat_name*}' => $chatData['title']));
                }

                debug_log("chat:$cid is ready to begin-time:" . $chatData['start_time'] . " --MemberCnt:" . count($lstMem));
            }

            $this->_wdb->commit();
            debug_log("***** Date:" . date('Y-m-d H:i:s', $runingDate) . " ***** batch end!" . "***** End *****");
            debug_log(' ');

            return true;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            debug_log('Error Happened!');
            debug_log($e);
            debug_log($e->__toString());
            return false;
        }
    }

}
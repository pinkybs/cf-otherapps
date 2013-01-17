<?php

require_once 'Dal/Chomeboard/User.php';

class Bll_Chomeboard_User
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
     * init the Vote's variables
     *
     * @param array $config ( config info )
     * @return void
     */
    public function __construct($config = null)
    {
        if (is_null($config)) {
            $config = getDBConfig();
        }
        
        $this->_config = $config;
        $this->_rdb = $config['readDB'];
        $this->_wdb = $config['writeDB'];
    }
    
    public static function getUser($uid)
    {
        $dalChomeboardUser = Dal_Chomeboard_User::getDefaultInstance();
        
        return $dalChomeboardUser->getUser($uid);
    }
    
    public static function isJoined($uid)
    {
        $user = self::getUser($uid);
        
        if ($user) {
            //check status ?
            //$user['status']
            
            return true;
        }
        else {
            return false;
        }
    }
    
    public static function join($uid)
    {
        $result = false;
        $bllChomeboardUiser = new Bll_Chomeboard_User();
        
        try {
            require_once 'Dal/Chomeboard/Chomeboard.php';
            $dalChomeboard = Dal_Chomeboard_Chomeboard::getDefaultInstance();
            
            $bllChomeboardUiser->_wdb->beginTransaction();
            
            self::updateUser($uid);
            
            $lastChomeBoard = $dalChomeboard->getLastChomeBoard($uid);
            $count = count($lastChomeBoard);
            $dalChomeboardUser = Dal_Chomeboard_User::getDefaultInstance();
            
            if (0 == $count) {
                //pick random key
                $aryDefaultPic = array(1,2,3,4,5,6,7);
                $key = array_rand($aryDefaultPic);
                $filename = "default" . ($key + 1);
                
                $info = array('uid' => $uid, 'comment_uid' => $uid, 'content' => '1/' . $filename . ".png", 'create_time' => date('Y-m-d H:i:s'), 'sort_id' => 0);
                
                //insert chome board
                $dalChomeboard->insertChomeBoard($info);
            
                //update board owner's be_commented count
                $dalChomeboardUser->updateBecommentedCount($info['uid'], 1);
                
                //update comment user's commented count
                $dalChomeboardUser->updateCommentedCount($info['comment_uid'], 1);
            } else {
            }
            
            $bllChomeboardUiser->_wdb->commit();
            
            $result = true;
        }
        catch (Exception $e) {
            $bllChomeboardUiser->_wdb->rollBack();
            return false;
        }
        
        return $result;
    }
    
    public static function remove($uid)
    {
        self::updateUser($uid);
    }
        
    public static function updateUser($uid)
    {
        $dalChomeboardUser = Dal_Chomeboard_User::getDefaultInstance();
        
        try {
            $dalChomeboardUser->updateUser($uid);
        }
        catch (Exception $e) {
            err_log($e->getMessage());
        }
    }
    
    public static function getAppFriendIds($fids)
    {
        $dalChomeboardUser = Dal_Chomeboard_User::getDefaultInstance();
        
        return $dalChomeboardUser->getAppFriendIds($fids);
    }
}
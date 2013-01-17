<?php

require_once 'Dal/Abstract.php';

/**
 * Dal advertisement
 *
 * @package    Dal/Ad
 * @copyright  Copyright (c) 2009 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/08/17    Huch
 */
class Dal_Ad_Ad extends Dal_Abstract
{
    protected static $_instance;
    
    /**
     * get Dal_Ad_Ad default
     *
     * @return Dal_Ad_Ad
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        
        return self::$_instance;
    }
    
    public function getRightAd($appId)
    {
        $sql = "SELECT * FROM app_ad WHERE app_id=:app_id AND status=1 AND type=2 ORDER BY RAND() LIMIT 1";
        return $this->_rdb->fetchRow($sql, array('app_id'=>$appId));
    }
    
    public function getTopAd($appId, $uid)
    {
        $sql = "SELECT COUNT(1) FROM app_user_ad WHERE app_id=:app_id AND uid=:uid";
        $result = $this->_rdb->fetchOne($sql, array('app_id'=>$appId, 'uid'=>$uid));
        
        if ($result == 0) {
            $sql = "SELECT * FROM app_ad WHERE app_id=:app_id AND status=1 AND type=1 ORDER BY RAND() LIMIT 4";
            $result = $this->_rdb->fetchAll($sql, array('app_id'=>$appId));
            
            if (count($result) == 0) {
                $sql = "SELECT * FROM app_ad WHERE app_id=0 AND status=1 AND type=1 ORDER BY RAND() LIMIT 4";
                
                $result = $this->_rdb->fetchAll($sql);
            }
                
            return $result;
        }
        
        return array();
    }
    
    public function closeAd($appId, $uid)
    {
        $sql = "INSERT INTO app_user_ad(app_id,uid) VALUES(:app_id, :uid) ON DUPLICATE KEY UPDATE app_id=:app_id";
        
        $this->_wdb->query($sql, array('app_id'=>$appId, 'uid'=>$uid));
    }
    
    public function getLinkUrlById($aid)
    {
        $this->addAdCount($aid);
        
        $sql = "SELECT link_url FROM app_ad WHERE id=:id";
        
        return $this->_rdb->fetchOne($sql, array('id'=>$aid));
    }
    
    public function addAdCount($aid)
    {
        $sql = "UPDATE app_ad SET `count`=`count` + 1 WHERE id=:id";
        
        $this->_wdb->query($sql, array('id'=>$aid));
    }
    
    public function checkTopAd($appId, $uid)
    {
        $result = $this->getTopAd($appId, $uid);
        return count($result) > 0;
    }
}
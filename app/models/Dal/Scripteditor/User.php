<?php

require_once 'Dal/Abstract.php';

class Dal_Scripteditor_User extends Dal_Abstract
{
    /**
     * user table name
     *
     * @var string
     */
    protected $table_user = 'scripteditor_user';
    
    protected static $_instance;
    
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        
        return self::$_instance;
    }
  
    public function getAppFriendIds($fids)
    {
        $ids = $this->_rdb->quote($fids);
        $sql = "SELECT uid FROM $this->table_user WHERE uid in ($ids)";
        
        $rows = $this->_rdb->fetchAll($sql);
        
        if ($rows) {
            $result = array();
            foreach ($rows as $row) {
                $result[] = $row['uid'];
            }
            
            return implode(',', $result);
        }
        else {
            return '';
        }
        
    }
    
    /**
     * check the user is join scripteditor
     *
     * @param integer $uid
     */
    public function isInScripteditor($uid)
    {
        $sql = "SELECT COUNT(1) FROM $this->table_user WHERE uid=:uid";

        $result = $this->_rdb->fetchOne($sql,array('uid'=>$uid));

        return $result > 0;
    }

    /**
     * insert scripteditor user
     *
     * @param array $newUser
     * @return integer
     */
    public function insertScripteditorUser($newUser)
    {
        $this->_wdb->insert($this->table_user, $newUser);
        return $this->_wdb->lastInsertId();
    }

    /**
     * update user info
     *
     * @param string $uid
     * @return void
     */
    public function updateUserInfo($uid, $info)
    {
        $where = $this->_wdb->quoteInto('uid = ?', $uid);
        $this->_wdb->update($this->table_user, $info, $where);
    }

    /**
     * select user info
     *
     * @param integer $uid
     */
    public function getUserInfo($uid)
    {
        $sql = "SELECT * FROM $this->table_user WHERE uid=:uid ";

        return $this->_rdb->fetchRow($sql,array('uid'=>$uid));
    }
    
}
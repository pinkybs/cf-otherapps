<?php

require_once 'Mdal/Abstract.php';

/**
 * Mdal quiz User
 *
 * @package    Mdal/quiz
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/09/23    xial
 */
class Mdal_Quiz_User extends Mdal_Abstract
{
    /**
     * user table name
     *
     * @var string
     */
    protected $table_user = 'quiz_user';

    protected static $_instance;

    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * insert quiz user
     *
     * @param array $user
     * @return integer
     */
    public function insertUser($user)
    {
        $this->_wdb->insert($this->table_user, $user);
        return $this->_wdb->lastInsertId();
    }

    /**
     * is joined
     *
     * @param integer $uid
     * @return array
     */
    public function getUser($uid)
    {
        $sql = "SELECT * FROM $this->table_user WHERE uid=:uid ";

        return $this->_rdb->fetchRow($sql, array('uid'=>$uid));
    }

    /**
     * update user status
     *
     * @param integer $uid
     * @param integer $status
     * @return array
     */
    public function updateStatus($uid, $status)
    {
        $sql = "UPDATE quiz_user SET status = :status WHERE uid = :uid";
        return $this->_wdb->query($sql, array('uid' => $uid, 'status' => $status));
    }
}
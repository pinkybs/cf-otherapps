<?php

require_once 'Dal/Abstract.php';

/**
 * Dal Hotel Competition
 * MixiApp hotel Competition Data Access Layer
 *
 * @package    Dal/Hotel
 * @copyright  Copyright (c) 2009 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/11/25    Zhaoxh
 */
class Dal_Hotel_Competition extends Dal_Abstract
{
    /**
     * tech table name
     *
     * @var string
     */
    protected $table_user = 'hotel_user_competition';

    protected static $_instance;

    /**
     * get Dal_Hotel_Cus default
     *
     * @return Dal_Hotel_Cus
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }


    /**
     * insert a Competition data
     *
     * @param array $info
     */
    public function insertCompetition($info)
    {
        $this->_wdb->insert($this->table_user, $info);
    }
    
	public function updateCompetition($uid,$set)
    {
    	$db = buildAdapter();
        $where = $db->quoteInto('uid = ?', $uid);
        $rows_affected = $db->update($this->table_user, $set, $where);
        return $rows_affected == 1 ;
    }
    
    
	public function isInCompetition($uid)
    {
    	$sql = "SELECT COUNT(1) FROM $this->table_user WHERE uid=:uid";
    	$re = $this->_rdb->fetchOne($sql,array('uid' => $uid));
    	return $re > 0;
    }
    /**
     *  RETURN whether can update
     *
     * @param string $uid
     * @return boolean
     */
	public function isJoined($uid)
    {
    	$sql = "SELECT COUNT(1) FROM $this->table_user WHERE uid=:uid AND joined=1";
    	$re = $this->_rdb->fetchOne($sql,array('uid' => $uid));
    	return $re > 0;
    }
    
    /**
     * get ten random users in today competition
     *
     * @return array
     */
    public function getRanTen()
    {
    	$sql = "SELECT uid,numA,numB,numC FROM $this->table_user WHERE joined=1 ORDER BY rand() LIMIT 0,10";
    	$re = $this->_rdb->fetchAll($sql);
    	$cnt = count($re);
    	if ($cnt < 10) {
    		$cnt = 10 - $cnt;
    		$sqll = "SELECT uid,numA,numB,numC FROM $this->table_user ORDER BY rand() LIMIT 0,$cnt";
 			$ree = $this->_rdb->fetchAll($sqll);
 			$re += $ree;   	
    	}
    	
    	return $re;
    }
    
    /**
     * get a random user in today competition
     *
     * @return array
     */
	public function getRanOne()
    {
    	$sql = "SELECT uid,numA,numB,numC FROM $this->table_user WHERE joined=1 AND last_result<0 ORDER BY rand() LIMIT 0,1";
    	$re = $this->_rdb->fetchRow($sql);
    	return $re;
    }
    
	public function defaultHandler()
    {
    	$sql = "UPDATE $this->table_user SET joined=0,last_result=1";
    	$re = $this->_wdb->query($sql);
    	return $re;
    }
    
    
	public function getResult($uid)
    {
    	$sql = "SELECT last_result FROM $this->table_user WHERE uid=:uid";
    	$re = $this->_rdb->fetchOne($sql,array('uid' => $uid));
    	return $re;
    }
    
    public function indexCompetition($uid) 
    {
    	
    }
    
    
}
<?php

/**
 * Board datebase's Operation
 *
 *
 * @package    Dal
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2010/01/26    hwq
 */
class Dal_AppStatistics extends Dal_Abstract
{
	protected static $_instance;

    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }
    
    /**
     * get Daily Login Count
     * @author hwq
     * @param string $app_id
     * @param string $report_date
     * @param string $isInvite 0,1,2
     * @return integer
     */
    public function getDailyAppAddCount($app_id, $report_date, $isInvite)
    {
        $sql = 'SELECT count(*) FROM app_login_log WHERE login_time >=('.$report_date.'- 3600*24)
        AND login_time <= '.$report_date .' AND app_id=' . $app_id ;
        if($isInvite < 2 ){
            $sql .=  ' AND isInvite=' .$isInvite;
        }
        
        return $this->_rdb->fetchOne($sql);
    }
    
    /**
     * get Daily Login Count
     * @author hwq
     * @param string $app_id
     * @param string $report_date
     * @param string $isInvite 0,1,2
     * @return integer
     */
    public function getKitchenPointData($report_date)
    {
        $sql = 'SELECT type, sum(amount) as sum FROM res_access_money WHERE create_time >=('.$report_date.'- 3600*24)
        AND create_time <= '.$report_date  .' GROUP BY type ORDER BY type';

        return $this->_rdb->fetchAll($sql);
    }
    
    public function getKitchenGoldDataPerType($report_date)
    {
        $sql = 'SELECT description, sum(amount) as sum FROM res_access_money WHERE type = 3 AND create_time >=('.$report_date.'- 3600*24)
        AND create_time <= '.$report_date  .' GROUP BY description ORDER BY description desc';

        return $this->_rdb->fetchAll($sql);
    }
    
    /**
     * get Daily Login Count
     * @author hwq
     * @param string $app_id
     * @param string $report_date
     * @param string $isInvite 0,1,2
     * @return integer
     */
    public function getKitchenUserData($report_date)
    {
        $sql = 'SELECT type, count(id) as sum FROM res_access_uu WHERE create_time >=('.$report_date.'- 3600*24)
        AND create_time <= '.$report_date  .' GROUP BY type ORDER BY type' ;

        return $this->_rdb->fetchAll($sql);
    }
    
    /**
     * get Daily remove Count
     * @author hwq
     * @param string $app_id
     * @param string $report_date
     * @return integer
     */
    public function getDailyAppRemoveCount($app_id, $report_date)
    {
        $sql = 'SELECT count(*) FROM app_remove_log WHERE remove_time >=('.$report_date.'- 3600*24)
        AND remove_time <= '.$report_date .' AND app_id=' . $app_id;
        
        return $this->_rdb->fetchOne($sql);
    }
    
    /**
     * get Daily remove Count
     * @author hwq
     * @param string $app_id
     * @param string $report_date
     * @return integer
     */
    public function getInviteOtherUserCount($app_id, $report_date)
    {
        $sql = 'SELECT  count(distinct actor) FROM mixi_app_invite WHERE time >=('.$report_date.'- 3600*24)
        AND time <= '.$report_date.' AND app_id=:app_id ' ;
        return $this->_rdb->fetchOne($sql,array('app_id' => $app_id));
    }
    
    /**
     * get Daily Login Count
     * @author hwq
     * @param string $table_name
     * @param string $report_date
     * @return integer
     */
    public function getDailyLoginCount($table_name,$report_date)
    {
    	$sql = 'SELECT count(*) FROM '.$table_name.' WHERE last_login_time >=('.$report_date.'- 3600*24)
    	AND last_login_time <= '.$report_date;
    	
    	return $this->_rdb->fetchOne($sql);
    }
    
    /**
     * get Daily user Count
     * @author hwq
     * @param string $table_name
     * @return integer
     */
    public function getDailyUserCount($table_name)
    {
        $sql = 'SELECT count(*) FROM '.$table_name;
        return $this->_rdb->fetchOne($sql);
    }

    /**
     * get Daily Login Invite Data
     * @author hwq
     * @param integer $app_id
     * @param string $report_date
     * @return integer
     */
    public function getDailyLoginInviteData($app_id,$report_date)
    {
        $sql = 'SELECT count(*) FROM mixi_app_invite WHERE time >= ('.$report_date.'- 3600*24)
        AND time <= '.$report_date.' AND app_id=:app_id AND process = "finished" ' ;
        
        return $this->_rdb->fetchOne($sql,array('app_id' => $app_id));
    }
    
    /**
     * get Invite Login Data
     * @author hwq
     * @param integer $app_id
     * @param string $report_date
     * @return integer
     */
    public function getInviteLoginData($app_id,$report_date)
    {
        $sql = 'SELECT count(*) FROM mixi_app_invite WHERE time >=('.$report_date.'- 3600*24)
        AND time <= '.$report_date.' AND app_id=:app_id ' ;
        return $this->_rdb->fetchOne($sql,array('app_id' => $app_id));
    }
    
    public function insertAppLogin($info)
    {
        $loginCount = $info['app_login'];
        $app_id = $info['app_id'];
        //return $this->_wdb->insert('`app_log`.`app_login`', $info);
        $sql="insert into `app_log`.`app_login` (`app_id`,`app_login`) values ($app_id,$loginCount);";
        return $this->_wdb->query($sql);
    }
    
    public function updateAppLogin($app_id,$info)
    {
        //$where = $this->_wdb->quoteInto('app_id = ?', $app_id);
        //return $this->_wdb->update('`app_log`.`app_login`', $info, $where);
        $loginCount = $info['app_login'];
        $sql="UPDATE `app_log`.`app_login` SET app_login=$loginCount WHERE app_id=$app_id";
        return $this->_wdb->query($sql);
    }
    
    public function getAppLogin($app_id)
    {
        $sql = 'SELECT * FROM `app_log`.`app_login` WHERE app_id=:app_id ' ;
        return $this->_rdb->fetchRow($sql,array('app_id' => $app_id));
    }
}
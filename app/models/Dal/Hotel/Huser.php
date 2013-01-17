<?php

require_once 'Dal/Abstract.php';

/**
 * Dal Hotel Huser
 * MixiApp hotel user Data Access Layer
 *
 * @package    Dal/Hotel
 * @copyright  Copyright (c) 2009 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/09/16    Zhaoxh
 */
class Dal_Hotel_Huser extends Dal_Abstract
{
    /**
     * user table name
     *
     * @var string
     */
    protected $table_user = 'hotel_user';

    protected static $_instance;

    /**
     * get Dal_Hotel_Huser default
     *
     * @return Dal_Hotel_Huser
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * insert hotel user
     *
     * @param array $userInfo
     * @return void
     */
    public function insertHuser($userInfo)
    {
        $this->_wdb->insert($this->table_user, $userInfo);
    }

    /**
     * insert hotel user room
     *
     * @param array $info
     * @return integer
     */
    public function insertHotelRoom($info)
    {
        return $this->_wdb->insert('hotel_user_room', $info);
    }

    /**
     * insert hotel user tech
     *
     * @param array $info
     * @return integer
     */
    public function insertHotelTech($info)
    {
        return $this->_wdb->insert('hotel_user_technology', $info);
    }

    /**
     * insert hotel user learn
     *
     * @param array $info
     * @return integer
     */
    public function insertHotelLearn($info)
    {
        return $this->_wdb->insert('hotel_user_learn', $info);
    }

    /**
     * update room
     *
     * @param string $uid
     * @param string $info
     * @return unknown
     */
    public function updateHotelRoom($uid, $info)
    {
        $where = $this->_wdb->quoteInto('uid = ?', $uid);
        return $this->_wdb->update('hotel_user_room', $info, $where);
    }

    /**
     * get One Data of hotel user table by uid
     *
     * @param string $uid
     * @param string $colName
     * @return string
     */
    public function getOneData($uid,$colName,$table='hotel_user')
    {
        $sql = "SELECT $colName FROM $table WHERE uid=:uid";

        $result = $this->_rdb->fetchOne($sql,array('uid'=>$uid));

        return $result;
    }

    /**
     * is in update process
     *
     * @param string $uid
     * @param string $type
     * @return boolean
     */
    public function inUpProcess($uid,$type)
    {
        $nowTime = time();
        $sql = "SELECT max(over_time) FROM hotel_update WHERE uid=$uid and `type`=$type";
        $overTime = $this->_rdb->fetchOne($sql);
        return $nowTime < $overTime;
    }

    /**
     * insert a update process
     *
     * @param array $info
     */
    public function insertUpProcess($info)
    {
        $this->_wdb->insert('hotel_update', $info);
    }

    /**
     * get begin time and over time
     * @param string $uid
     * @param string $type
     * @return array
     */
    public function getProcessInfo($uid,$type)
    {
        $sql = "SELECT max(id) AS id,max(begin_time) AS begin_time,max(over_time) AS over_time FROM hotel_update WHERE uid=$uid AND `type`=$type";
        $result = $this->_rdb->fetchRow($sql);

        $sqll = "SELECT name FROM hotel_update WHERE id=:id";
        $result['name'] = $this->_rdb->fetchOne($sqll,array('id' => $result['id']));
        return $result;
    }

    /**
     * update process
     *
     * @param string $id
     * @param string $over_time
     * @return pdo_obj
     */
    public function updateProcess($id,$over_time)
    {
        $sql = "UPDATE hotel_update SET over_time=:over_time WHERE id=:id";
        $result = $this->_wdb->query($sql,array('id' => $id,'over_time' => $over_time));
        return $result;
    }

    /**
     * update room level
     *
     * @param string $uid
     */
    public function updateRoom($uid, $type, $time)
    {
        //select last room_update info
        $sql = "SELECT id,`name`,currentLv FROM hotel_update WHERE uid=:uid AND `type`=:type AND operated=0 AND over_time<$time";
        $arr = $this->_rdb->fetchRow($sql,array('uid' => $uid,'type' => $type));
        $name = $arr['name'];

        //update that room
        $sqll = "UPDATE hotel_user_room SET $name=:currentLv WHERE uid=:uid ";
        $this->_wdb->query($sqll,array('uid' => $uid, 'currentLv' => $arr['currentLv'] + 1));

        return $arr;
    }

    /**
     * get Unoperated updateinfo by uid ,return info id
     *
     * @param string $uid
     * @return array
     */
    public function tryGetUnoperated($uid,$time)
    {
        $sql = "SELECT id,name,type FROM hotel_update WHERE uid=:uid AND `type`=1 AND operated=0 AND over_time<:time UNION
                (SELECT id,name,type FROM hotel_update WHERE uid=:uid AND `type`=2 AND operated=0 AND over_time<:time)";
        $result = $this->_rdb->fetchAll($sql,array('uid' => $uid,'time' => $time));
        return $result;
    }

    /**
     * update lv+1 by colname
     *
     * @param string $uid
     * @param string $name
     */
    public function updateAtOnce($uid, $name, $type)
    {
        $table = $type == 1 ? "hotel_user_room" : "hotel_user_technology";
        $sql = "UPDATE $table SET `$name` = `$name` + 1 WHERE uid=:uid ";
        $this->_wdb->query($sql,array('uid' => $uid));

    }

    /**
     * set a line of  hotel_update to operated
     *
     * @param string $id
     */
    public function setOperated($id)
    {
        $sql = "UPDATE hotel_update SET operated=1 WHERE id=:id AND operated=0";
        $this->_wdb->query($sql,array('id' => $id));
    }

    /**
     * get update_money by given arg
     *
     * @param string $table
     * @param string $currentLv
     * @return string
     */
    public function getPriceTime($table,$currentLv)
    {
        $sql = "SELECT update_money,update_time FROM $table WHERE `level`=$currentLv";
        $result = $this->_rdb->fetchRow($sql);
        return $result;
    }

	public function getLiveFee($table,$currentLv)
    {
        if ($table == 'hotel_room_type') {
    		$sql = "SELECT living_in,fee FROM $table WHERE `level`=$currentLv";
        }
        else if ($table == 'hotel_restaurant_type') {
    		$sql = "SELECT dining_rate AS living_in,dining_fee AS fee FROM $table WHERE `level`=$currentLv";
        }
        else {
        	$sql = "SELECT -4 AS living_in,-4 AS fee";
        }
        $result = $this->_rdb->fetchRow($sql);
        return $result;
    }
    
    /**
     * update Huser by uid with array $set
     *
     * @param string $uid
     * @param array $set
     * @return boolean
     */
    public function upHuser($uid,$set)
    {
        $db = buildAdapter();
        $where = $db->quoteInto('uid = ?', $uid);
        $rows_affected = $db->update($this->table_user, $set, $where);
        return $rows_affected == 1 ;
    }

    /**
     * get friend circle list
     *
     * @param string $uid
     * @param string $fids
     * @return array
     */
    public function getCircle($uid,$fids)
    {
        $sql1 = "SET @pos=0";
        $this->_rdb->query($sql1);
        $sql = "SELECT 0 AS rank,$uid AS uid UNION SELECT @pos:=@pos+1 AS rank,uid FROM hotel_user WHERE uid in ($fids)";"// AND location <> 0";
        return $this->_rdb->fetchAll($sql);
    }

    /**
     * getFullData of uid
     *
     * @param string $uid
     * @param integer $type   if (uid='self') {type =1}
     * @return array
     */
    public function getFullData ($uid,$type=0)
    {
        //basic info
        $sql = "SELECT location,experience,level,money,mixipoint,clean,join_time FROM $this->table_user WHERE uid=:uid";
        $result = $this->_rdb->fetchRow($sql,array('uid'=>$uid));

        //next level exp
        $nextExp = $this->getExpByLv($result['level'] + 1);
        $result['next_exp'] = $nextExp;
        
        //room level info
        $sqll = "SELECT room1,room2,room3,restaurant,manager,reception FROM hotel_user_room WHERE uid=:uid";
        $resultt = $this->_rdb->fetchRow($sqll,array('uid'=>$uid));
        $result += $resultt;

        //learner info
        $sqlll = "SELECT uid,create_time,`index` FROM hotel_user_learn WHERE fid=:uid";
        $resultt = $this->_rdb->fetchAll($sqlll,array('uid'=>$uid));

        require_once 'Dal/Hotel/Friend.php';
        $dalFriend = Dal_Hotel_Friend::getDefaultInstance();

        for ($i = 0; $i < min(count($resultt),3); $i++) {
            $result["learn" . strval($i+1) . "money"] = $dalFriend->earnLearner($resultt[$i]['uid'],$resultt[$i]['index']);
            $result["learn" . strval($i+1) . "uid"] = $resultt[$i]['uid'];
            $result["learn" . strval($i+1) . "index"] = $resultt[$i]['index'];
            $tempArr['uid'] = $resultt[$i]['uid'];
            Bll_User::appendPerson($tempArr, 'uid');
            $result["learn" . strval($i+1) . "displayName"] = $tempArr['displayName'];
        }
        
        
        
        
        //when uid='selfuid'
        if ($type == 1) {
            $sqllll = "SELECT update_money,living_in,fee FROM hotel_room_type WHERE level = :room1
                      UNION ALL SELECT update_money,living_in,fee FROM hotel_room_type WHERE `level` = :room1 + 1
                      UNION ALL SELECT update_money,living_in,fee FROM hotel_room_type WHERE `level` = :room2
                      UNION ALL SELECT update_money,living_in,fee FROM hotel_room_type WHERE `level` = :room3
                      UNION ALL SELECT update_money,dining_rate,dining_fee FROM hotel_restaurant_type WHERE `level` = :restaurant
                      UNION ALL SELECT update_money,dining_rate,dining_fee FROM hotel_restaurant_type WHERE `level` = :restaurant + 1
                      UNION ALL SELECT update_money,level,id FROM hotel_manager_type WHERE `level` = :manager
                      UNION ALL SELECT update_money,level,id FROM hotel_reception_type WHERE `level` = :reception
                      UNION ALL SELECT update_money,living_in,fee FROM hotel_room_type WHERE `level` = :room2 + 1
                      UNION ALL SELECT update_money,living_in,fee FROM hotel_room_type WHERE `level` = :room3 + 1";

            $resultt = $this->_rdb->fetchAll($sqllll,array('room1' => $result['room1'],
                                                          'room2' => $result['room2'],
                                                          'room3' => $result['room3'],
                                                          'restaurant' => $result['restaurant'],
                                                          'manager' => $result['manager'],
                                                          'reception' => $result['reception']));
            //add room price info
            $result['room1_price'] = $resultt[0]['update_money'];
            $result['room1_living_in'] = $resultt[0]['living_in'];
            $result['room1_fee'] = $resultt[0]['fee'];

            $result['room1_next_price'] = $resultt[1]['update_money'];
            $result['room1_next_fee'] = $resultt[1]['fee'];
            $result['room1_next_living_in'] = $resultt[1]['living_in'];

            $result['room2_price'] = $resultt[2]['update_money'];
            $result['room2_living_in'] = $resultt[2]['living_in'];
            $result['room2_fee'] = $resultt[2]['fee'];

            $result['room3_price'] = $resultt[3]['update_money'];
            $result['room3_living_in'] = $resultt[3]['living_in'];
            $result['room3_fee'] = $resultt[3]['fee'];

            $result['restaurant_price'] = $resultt[4]['update_money'];
            $result['restaurant_dining_rate'] = $resultt[4]['living_in'];
            $result['restaurant_dining_fee'] = $resultt[4]['fee'];

            $result['restaurant_next_price'] = $resultt[5]['update_money'];
            $result['restaurant_next_dining_rate'] = $resultt[5]['living_in'];
            $result['restaurant_next_dining_fee'] = $resultt[5]['fee'];

            $result['manager_price'] = $resultt[6]['update_money'];
            $result['reception_price'] = $resultt[7]['update_money'];

            $result['room2_next_price'] = $resultt[8]['update_money'];
            $result['room2_next_fee'] = $resultt[8]['fee'];
            $result['room2_next_living_in'] = $resultt[8]['living_in'];

            $result['room3_next_price'] = $resultt[9]['update_money'];
            $result['room3_next_fee'] = $resultt[9]['fee'];
            $result['room3_next_living_in'] = $resultt[9]['living_in'];

            //add process info
            $t = time();
            $processBuild = $this->getProcessInfo($uid,'1');
            if ($processBuild['over_time'] > $t) {
                //$result['buildProgress'] = intval(($t - $processBuild['begin_time']) * 100 / ($processBuild['over_time'] - $processBuild['begin_time']));
                $result['buildProgress'] = $t - $processBuild['begin_time'];
                $result['building'] = $processBuild['name'];
                $result['buildTime'] = $processBuild['over_time'] - $processBuild['begin_time'];
            }
            $processTech = $this->getProcessInfo($uid,'2');
            if ($processTech['over_time'] > $t) {
                //$result['techProgress'] = intval(($t - $processTech['begin_time']) * 100 / ($processTech['over_time'] - $processTech['begin_time']));
                $result['techProgress'] = $t - $processTech['begin_time'];
                $result['teching'] = $processTech['name'];
                $result['techTime'] = $processTech['over_time'] - $processTech['begin_time'];
            }
            
            //get item1 cnt 
            require_once 'Dal/Hotel/Item.php';
        	$dalItem = Dal_Hotel_Item::getDefaultInstance();
        	
        	$itemCnt = $dalItem->getItemList($uid,1);
            if ($itemCnt[0]['sid'] == 1 && $itemCnt[0]['number'] > 0) {
        		$result['item1cnt'] = $itemCnt[0]['number'];
            }
            else {
            	$result['item1cnt'] = 0;
            }
            
            //get cus_click cnt
            require_once 'Dal/Hotel/Cus.php';
            $dalCus = Dal_Hotel_Cus::getDefaultInstance();
            $cus_param = $dalCus->countOperatedById($uid, 0);
            $result['cus_param'] = $cus_param;
        }

        return $result;
    }
    
     

    /**
     * check the user is join hotel
     *
     * @param integer $uid
     */
    public function isInHotel($uid)
    {
        $sql = "SELECT COUNT(1) FROM $this->table_user WHERE uid=:uid";

        $result = $this->_rdb->fetchOne($sql,array('uid'=>$uid));

        return $result > 0;
    }

    /**
     * get level by exp
     *
     * @param int $exp
     * @return string
     */
    public function getUserLvByExp($exp)
    {
        $sql = "SELECT COUNT(1) FROM hotel_level_type WHERE experience<=:exp";

        $result = $this->_rdb->fetchOne($sql,array('exp'=>$exp));

        return $result;
    }
    
    
	public function getExpByLv($lv)
    {
        $sql = "SELECT experience FROM hotel_level_type WHERE `level`=:lv";

        $result = $this->_rdb->fetchOne($sql,array('lv'=>$lv));

        return $result;
    }

    /**
     * get ranking info
     *
     * @param string $colname
     * @param integer $page
     * @param string $table
     * @param integer $size
     * @param integer $pos :2 in friend,1 in mixi user
     * @param integer $uid
     * @param array $fids
     * @return array
     */
    public function rank($colname, $page, $fids = null, $type = 1,$size = 12)
    {
        $start = ($page - 1) * $size;

        $sql1 = "SET @pos = 0;";
        $this->_rdb->query($sql1);

        
        //$type => 1:friend 2:all
        if ($type == 1) {
        	$fids = $this->_rdb->quote($fids);
        	$sql = "SELECT @pos:=@pos + 1 AS rank, uid,`$colname` FROM hotel_user WHERE uid IN ($fids)
        	         ORDER BY `$colname` DESC LIMIT $start, $size;";
        }
        else if ($type == 2) {
        	$sql = "SELECT @pos:=@pos + 1 AS rank, uid,`$colname` FROM hotel_user ORDER BY `$colname` DESC LIMIT $start, $size;";
        }
        $result = $this->_rdb->fetchAll($sql);
        return $result;
    } 

	public function getUpdateInfo($uid, $table, $type, $time)
    {
        //select last room_update info
        $sql = "SELECT id,`name`,currentLv FROM hotel_update WHERE uid=:uid AND `type`=:type AND operated=0 AND over_time > $time";
        $arr = $this->_rdb->fetchRow($sql,array('uid' => $uid,'type' => $type));
         

        //update that room
        $sqll = "SELECT update_time FROM $table WHERE `level`=:currentLv";
        $result = $this->_rdb->fetchRow($sqll,array('currentLv' => $arr['currentLv']));

        return $result;
    }
    
    
//*********************************************************************************************
    public function getUserInfoById($uid)
    {
        $sql = "SELECT uid,location,experience,level,money,clean,occupancy_up FROM $this->table_user WHERE uid = :uid";
        $result = $this->_rdb->fetchRow($sql, array('uid' => $uid));

        $sqll = "SELECT experience FROM hotel_level_type WHERE level = :level";

        $next_experience = $this->_rdb->fetchOne($sqll, array('level' => ($result['level'] + 1)));

        $result['next_experience'] = $next_experience;

        return $result;
    }

    public function getRoomInfoById($uid)
    {
        $sql = "SELECT * FROM hotel_user_room WHERE uid = :uid";
        return $this->_rdb->fetchRow($sql, array('uid' => $uid));
    }

    public function listNbLoacation()
    {
        $sql = "SELECT * FROM hotel_location_type WHERE type=1 ";
        return $this->_rdb->fetchAll($sql);
    }


     /**
     * get neighber uid
     * @param integer $uid
     * @param string $nextOrPrev[prev/next/first/last]
     * @return array
     */
    public function getNeighberUid($uid, $nextOrPrev)
    {
        $aryParm = array();
        $sql = "SELECT uid FROM $this->table_user";
        if ('prev' == $nextOrPrev) {
            $sql .= " WHERE uid < :uid ORDER BY uid DESC LIMIT 0,1 ";
            $aryParm['uid'] = $uid;
        }
        else if ('next' == $nextOrPrev){
            $sql .= " WHERE uid > :uid ORDER BY uid LIMIT 0,1 ";
            $aryParm['uid'] = $uid;
        }
        else if ('first' == $nextOrPrev) {
            $sql .= " ORDER BY uid LIMIT 0,1 ";
        }
        else if ('last' == $nextOrPrev) {
            $sql .= " ORDER BY uid DESC LIMIT 0,1 ";
        }
        return $this->_rdb->fetchOne($sql, $aryParm);
    }

    /**
     * get room list
     *
     * @param integer $uid
     * @return array
     */
    public function getRoomlist($uid)
    {
        $sql = "SELECT * FROM hotel_user_room WHERE uid = :uid";
        return $this->_rdb->fetchRow($sql, array('uid' => $uid));
    }

    public function getLivingInByLv($level)
    {
    	$sql = "SELECT living_in FROM hotel_room_type WHERE level = :level";
    	return $this->_rdb->fetchOne($sql, array('level' => $level));
    }

    /**
     * get current Upgrade room or Technology
     *
     * @param integer $uid
     * @param integer $time
     * @return array
     */
    public function tryGetUpoperating($uid, $time)
    {
        $sql = "SELECT id,name,type FROM hotel_update WHERE uid=:uid AND `type`=1 AND operated=0 AND over_time>:time UNION
                (SELECT id,name,type FROM hotel_update WHERE uid=:uid AND `type`=2 AND operated=0 AND over_time>:time)";
        $result = $this->_rdb->fetchAll($sql,array('uid' => $uid,'time' => $time));
        return $result;
    }

    public function getAllAppUserCount()
    {
        $sql = "SELECT COUNT(1) FROM $this->table_user";

        $result = $this->_rdb->fetchOne($sql);

        return $result;
    }
}
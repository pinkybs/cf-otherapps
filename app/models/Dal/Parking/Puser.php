<?php

require_once 'Dal/Abstract.php';

class Dal_Parking_Puser extends Dal_Abstract
{
    /**
     * user table name
     *
     * @var string
     */
    protected $table_user = 'parking_user';
    
    protected static $_instance;
    
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        
        return self::$_instance;
    }

    /**
     * insert parking user
     *
     * @param array $park
     * @return integer
     */
    public function insertParkingUser($park, $uid)
    {
        $this->_wdb->insert($this->table_user, $park);
        
        $sql = "INSERT INTO parking_user_bomb (uid) VALUES (:uid)";
        $this->_wdb->query($sql,array('uid'=>$uid));
        
        $sql = "INSERT INTO parking_user_yanki (uid) VALUES (:uid)";
        $this->_wdb->query($sql,array('uid'=>$uid));
        
        $sql = "INSERT INTO parking_user_card (uid,sid) SELECT :uid,sid FROM parking_store";
        $this->_wdb->query($sql,array('uid'=>$uid));
    }
    
    /**
     * update parking user
     *
     * @param string $uid
     * @param array $parking
     */
    public function update($uid,$parking)
    {
        $where = $this->_wdb->quoteinto('uid = ?', $uid);
        $this->_wdb->update($this->table_user, $parking, $where);
	}
	
    /**
     * check the user is join parking
     *
     * @param integer $uid
     */
    public function isInParking($uid)
    {
        $sql = "SELECT COUNT(1) FROM parking_user WHERE uid=:uid";

        $result = $this->_rdb->fetchOne($sql,array('uid'=>$uid));

        return $result > 0;
    }
    
    /**
     * update user last login time
     *
     * @param integer $uid
     */
    public function updateLastLoginTime($uid)
    {
        $date = time();
        $sql = "UPDATE parking_user SET last_login_time=$date WHERE uid=:uid";

        $this->_wdb->query($sql,array('uid'=>$uid));
    }
    
    /**
     * update user car
     *
     * @param integer $uid
     */
    public function updateUserCar($uid)
    {
        $sql = "UPDATE $this->table_user SET car_count=(SELECT COUNT(1) FROM parking_user_car WHERE uid=:uid) WHERE uid=:uid";
        $this->_wdb->query($sql, array('uid' => $uid));

        $sql = "UPDATE $this->table_user SET car_price=(SELECT IFNULL(SUM(c.price),0) FROM parking_user_car AS u,parking_car AS c
                WHERE u.car_id=c.cid AND u.uid=:uid) WHERE uid=:uid";
        $this->_wdb->query($sql, array('uid' => $uid));
    }
    
    /**
     * update user car price
     * 
     * @param integer $price
     * @param integer $uid
     * @return void
     */
    public function updateCarPrice($price, $uid)
    {
		$sql = "UPDATE parking_user SET car_price=car_price-:price WHERE uid=:uid ";

        $this->_wdb->query($sql, array('price'=>$price, 'uid' => $uid));
    }

    /**
     * update user asset
     * @author lp
     * @param integer $price
     * @param integer $uid
     * @return void
     */
    public function updateUserAsset($price, $uid, $type=1)
    {
		if ($type==2) {
			$sql = "UPDATE parking_user SET asset=asset+:price WHERE uid=:uid ";
		}
		else {
			$sql = "UPDATE parking_user SET asset=asset-:price WHERE uid=:uid ";
		}
        $this->_wdb->query($sql, array('price'=>$price, 'uid' => $uid));
    }

    /**
     * update user last send car time
     *
     * @param integer $uid
     */
    public function updateUserSendCarTime($uid)
    {
        $time = time();
        $sql = "UPDATE parking_user SET send_car_time=$time WHERE uid=:uid";
        $this->_wdb->query($sql, array('uid' => $uid));
    }

    /**
     * update user recive car time
     *
     * @param integer $uid
     */
    public function updateUserReciveCarTime($uid)
    {
        $time = time();
        $sql = "UPDATE parking_user SET receive_car_time=$time WHERE uid=:uid";
        $this->_wdb->query($sql, array('uid' => $uid));
    }
    
    /**
     * update user last login time
     *
     * @param integer $uid
     */
    public function getLastLoginTime($uid)
    {
        $sql = "SELECT last_login_time FROM parking_user WHERE uid=:uid";

		return $this->_rdb->fetchOne($sql,array('uid'=>$uid));
    }
    

	/**
     * get user park
     * @author lp
     * @param integer $uid
     * @return array
     */
    public function getUserPark($uid)
    {
        $sql = "SELECT p.*,b.name,b.fee,b.type AS bgtype,b.cav_name AS bg_cav_name,1 AS type,k.*,
                m.location1 AS bomb1,m.location2 AS bomb2,m.location3 AS bomb3,m.location4 AS bomb4,
                m.location5 AS bomb5,m.location6 AS bomb6,m.location7 AS bomb7,m.location8 AS bomb8 
				FROM parking_user AS p,parking_background AS b,parking_user_yanki AS k,parking_user_bomb AS m 
                WHERE b.id=p.background AND k.uid=p.uid AND m.uid=p.uid AND p.uid=:uid";

        return $this->_rdb->fetchRow($sql,array('uid'=>$uid));
    }
	
    /**
     * get user neighbor park
     *
     * @param integer $id
     * @return array
     */
    public function getUserNeighborPark($id)
    {
        $sql = "SELECT  n.id AS uid,n.name AS displayName,n.free_park,n.background,b.name,b.fee,2 AS type,b.type AS bgtype,3 AS locaCount,b.cav_name AS bg_cav_name
                FROM parking_neighbor AS n,parking_background AS b WHERE n.background=b.id AND n.id=:id";

        return $this->_rdb->fetchRow($sql,array('id'=>$id));
    }

	public function getUserBombLocation($uid)
	{
		$sql = "SELECT c.parking,d.* FROM parking_user AS a,parking_background AS b,parking_rank AS c,parking_user_bomb AS d
				WHERE a.uid=d.uid AND a.background=b.id AND b.type=c.rank AND a.uid=:uid";
		
		return $this->_rdb->fetchRow($sql ,array('uid' => $uid));
	}
	
	public function getUserYankiLocation($uid)
	{
		$sql = "SELECT c.parking,a.free_park,d.* FROM parking_user AS a,parking_background AS b,parking_rank AS c,parking_user_yanki AS d
				WHERE a.uid=d.uid AND a.background=b.id AND b.type=c.rank AND a.uid=:uid";
		
		return $this->_rdb->fetchRow($sql ,array('uid' => $uid));
	}

    /**
     * get ranking count
     *
     * @param integer $uid
     * @param integer $type
     * @param string  $fids
     * @return integer
     */
    public function getRankingCount($uid, $type, $fids)
    {
        if ($type==1){
            $fids = $this->_rdb->quote($fids);
			$sql = "SELECT COUNT(1)+1 FROM parking_user WHERE uid IN ($fids)";
			return $this->_rdb->fetchOne($sql,array('uid'=>$uid));
        }
        else {
            $sql = "SELECT COUNT(1) FROM parking_user";
            return $this->_rdb->fetchOne($sql);
        }
    }

    /**
     * get ranking user
     *
     * @param integer $uid
     * @param string  $fids
     * @param integer $type1
     * @param integer $type2
     * @param integer $pageSize
     * @param string  $order
     * @param integer $isTop
     * @return array
     */
    public function getRankingUser($uid, $fids, $type1, $type2, $pageSize=16, $order='ASC', $isTop = 0)
    {
        if ($isTop) {
            $start = $isTop;
        }
        else {
            $start = 0;
        }
        
        if ($type1==1){
            $fids = $this->_rdb->quote($fids);
            if ($type2 == 1) {
                $sql = "SELECT p.uid,(p.asset+p.car_price+b.price) AS ass,p.last_login_time > (unix_timestamp(now())-300) AS online,
                        1 AS type FROM parking_user AS p,parking_background AS b
                        WHERE p.uid IN ($fids,:uid) AND p.background = b.id ORDER BY ass $order, p.id DESC LIMIT $start,$pageSize";
            }
            else {
                $sql = "SELECT p.uid,car_price AS ass,p.last_login_time > (unix_timestamp(now())-300) AS online,1 AS type 
                        FROM parking_user AS p,parking_background AS b
                        WHERE p.uid IN ($fids,:uid) AND p.background = b.id ORDER BY ass $order, p.id DESC LIMIT $start,$pageSize";
            }

            $temp = $this->_rdb->fetchAll($sql,array('uid'=>$uid));
        }
        else {
            if ($type2 == 1) {
                $sql = "SELECT p.uid,(asset+car_price+price) AS ass,p.last_login_time > (unix_timestamp(now())-300) AS online,1 AS type 
					    FROM parking_user AS p,parking_background AS b
                        WHERE b.id=p.background ORDER BY ass $order, p.id DESC LIMIT $start,$pageSize";
            }
            else {
                $sql = "SELECT uid,car_price AS ass,last_login_time > (unix_timestamp(now())-300) AS online,1 AS type 
						FROM parking_user ORDER BY ass $order, id DESC LIMIT $start,$pageSize";
            }

            $temp = $this->_rdb->fetchAll($sql);
        }            
        
        return $temp;
    }

    /**
     * get user rank number in friend
     *
     * @param integer $uid
     * @param string  $fids
     * @return integer
     */
    public function getUserRankNm($uid, $fids, $type1, $type2)
    {
        $sql1 = "SET @pos=0";
        $this->_rdb->query($sql1);
        
        if ( $type1==1 ) {
            $fids = $this->_rdb->quote($fids);
            if ($type2==1) {
                $sql = "SELECT b.rank,a.uid,b.ass FROM parking_user AS a,
                        (SELECT @pos:=@pos+1 AS rank,uid,p.asset+p.car_price+b.price AS ass 
                        FROM parking_user AS p,parking_background AS b 
                        WHERE uid IN ($fids, :uid) AND p.background=b.id ORDER BY ass ASC, p.id DESC) AS b 
                        WHERE a.uid=b.uid AND a.uid=:uid";
            }
            else {
                $sql = "SELECT b.rank,a.uid,a.car_price FROM parking_user AS a,
                        (SELECT @pos:=@pos+1 AS rank,uid,car_price FROM parking_user 
                        WHERE uid IN ($fids, :uid) ORDER BY car_price ASC, id DESC) AS b 
                        WHERE a.uid=b.uid AND a.uid=:uid";
            }
        }
        else {
            if ($type2==1) {
                $sql = "SELECT b.rank,a.uid,b.ass FROM parking_user AS a,
                        (SELECT @pos:=@pos+1 AS rank,uid,p.asset+p.car_price+b.price AS ass 
                        FROM parking_user AS p,parking_background AS b 
                        WHERE p.background=b.id ORDER BY ass ASC, p.id DESC) AS b 
                        WHERE a.uid=b.uid AND a.uid=:uid";
            }
            else {
                $sql = "select b.rank,a.uid,a.car_price FROM parking_user AS a,
                        (SELECT @pos:=@pos+1 AS rank,uid,car_price FROM parking_user ORDER BY car_price ASC, id DESC) AS b 
                        WHERE a.uid=b.uid AND a.uid=:uid";
                
                //$sql =  $this->_rdb->fetchRow($sql2);
            }
        }
        $reuslt = $this->_rdb->fetchRow($sql, array('uid' => $uid));
        
        return $reuslt['rank'];
    }

    /**
     * get user asset
     * @author lp
     */
    public function getAsset($uid){
        $sql="SELECT asset FROM parking_user WHERE uid=:uid";
        return $this->_rdb->fetchRow($sql,array('uid'=>$uid));
    }

    /**
     * get user all asset
     *
     * @param string $uid
     * @return array
     */
    public function getAllAss($uid) 
    {
    	$sql = "SELECT (p.asset+p.car_price+b.price) AS asset
                FROM parking_user AS p,parking_background AS b WHERE p.uid=:uid AND p.background = b.id";
    	
    	return $this->_rdb->fetchRow($sql,array('uid'=>$uid));
    }
    
    /**
     * check the user is join parking and active=1
     *
     * @param integer $uid
     */
    public function isInParkingUser($uid)
    {
        $sql = "SELECT COUNT(1) FROM parking_user WHERE uid=:uid";

        $result = $this->_rdb->fetchOne($sql,array('uid'=>$uid));

        return $result > 0;
    }

	/**
	 * get gadet user
	 *
	 * @param string $uid
	 * @return array
	 */
    public function getGadetUser($uid)
    {
    	$sql = "SELECT p.uid,p.free_park,r.parking AS `limit`, b.cav_name FROM
				parking_user AS p,parking_rank AS r,parking_background AS b WHERE p.background=b.id AND b.type=r.rank AND p.uid=:uid";
    	
    	return $this->_rdb->fetchRow($sql,array('uid'=>$uid));
    }
    
    /**
     * get gadet user park
     *
     * @param string $uid
     * @return array
	 */
    public function getGadetUserPark($uid)
    {
    	$sql = "SELECT c.cav_name,p.car_color,p.location FROM parking AS p,parking_car AS c 
    			WHERE p.car_id=c.cid AND p.parking_uid=:uid";
    	
    	return $this->_rdb->fetchAll($sql,array('uid'=>$uid));
    }

}
<?php

require_once 'Mdal/Abstract.php';

class Mdal_Parking_Puser extends Mdal_Abstract
{
    /**
     * get user park info
     *
     * @param string $uid
     * @return array
     */
    public function getUserParkInfo($uid)
    {
        $sql = "SELECT p.uid,p.background,b.fee,r.parking AS locaCount,p.free_park,
                k.location1 AS yankee1,k.location2 AS yankee2,k.location3 AS yankee3,k.location4 AS yankee4,
                k.location5 AS yankee5,k.location6 AS yankee6,k.location7 AS yankee7,k.location8 AS yankee8,
                m.location1 AS bomb1,m.location2 AS bomb2,m.location3 AS bomb3,m.location4 AS bomb4,
                m.location5 AS bomb5,m.location6 AS bomb6,m.location7 AS bomb7,m.location8 AS bomb8
                FROM parking_user AS p,parking_rank AS r,parking_background AS b,parking_user_yanki AS k,parking_user_bomb AS m
                WHERE b.type=r.rank AND b.id=p.background AND k.uid=p.uid AND m.uid=p.uid AND p.uid=:uid";

        return $this->_rdb->fetchRow($sql, array('uid' => $uid));
    }

    /**
     * get last evasion time
     *
     * @param string $uid
     * @return integer timestamp
     */
    public function getEvasionTime($uid)
    {
        $sql = "SELECT last_evasion_time from parking_user where uid=:uid";

        return $this->_rdb->fetchOne($sql, array('uid' => $uid));
    }

    /**
     * get car info
     *
     * @param string $uid
     * @param string $pid
     * @return array
     */
    public function getCarInfo($uid, $pid)
    {
        if ($pid < 0) {
            $sql = 'SELECT location,p.car_id,c.cav_name,p.car_color AS color FROM parking AS p,parking_car AS c
                    WHERE c.cid=p.car_id AND p.uid=:uid AND p.parking_uid=:pid';

            return $this->_rdb->fetchAll($sql, array('uid' => $uid, 'pid' => $pid));
        }
        else {
            $sql = 'SELECT location,p.car_id,c.cav_name,p.car_color AS color FROM parking AS p,parking_car AS c
                    WHERE c.cid=p.car_id AND p.parking_uid=:pid';

            return $this->_rdb->fetchAll($sql, array('pid' => $pid));
        }
    }

    /**
     * get flash car image
     *
     * @param integer $car_id
     * @param string $color
     * @return array
     */
    public function getFlashCarImage($car_id, $color)
    {
        $sql = "SELECT * FROM parking_mobile_flash WHERE car_id=:car_id AND car_color=:car_color";

        return $this->_rdb->fetchRow($sql, array('car_id' => $car_id, 'car_color' => $color));
    }

    /**
     * get parking info by location
     *
     * @param string $uid
     * @param integer $location
     * @return array
     */
    public function getParkingInfoByLocation($uid, $location)
    {
        $sql = "SELECT p.uid,p.car_id,p.car_color,c.name,c.cav_name FROM parking AS p,parking_car AS c
                WHERE p.car_id=c.cid AND p.parking_uid=:uid AND p.location=:location";

        return $this->_rdb->fetchRow($sql, array('uid' => $uid, 'location' => $location));
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

        if ( $type1 == 1 ) {
            $fids = $this->_rdb->quote($fids);

            if (empty($fids)) {
                $fids = "''";
            }

            if ($type2==1) {
                $sql = "SELECT b.rank,a.uid,b.ass FROM parking_user AS a,
                        (SELECT @pos:=@pos+1 AS rank,uid,p.asset+p.car_price+b.price AS ass
                        FROM parking_user AS p,parking_background AS b
                        WHERE uid IN ($fids, :uid) AND p.background=b.id ORDER BY ass DESC, p.id ASC) AS b
                        WHERE a.uid=b.uid AND a.uid=:uid";
            }
            else {
                $sql = "SELECT b.rank,a.uid,a.car_price FROM parking_user AS a,
                        (SELECT @pos:=@pos+1 AS rank,uid,car_price FROM parking_user
                        WHERE uid IN ($fids, :uid) ORDER BY car_price DESC, id ASC) AS b
                        WHERE a.uid=b.uid AND a.uid=:uid";
            }
        }
        else {
            if ($type2 == 1) {
                $sql = "SELECT b.rank,a.uid,b.ass FROM parking_user AS a,
                        (SELECT @pos:=@pos+1 AS rank,uid,p.asset+p.car_price+b.price AS ass
                        FROM parking_user AS p,parking_background AS b
                        WHERE p.background=b.id ORDER BY ass DESC, p.id ASC) AS b
                        WHERE a.uid=b.uid AND a.uid=:uid";
            }
            else {
                $sql = "select b.rank,a.uid,a.car_price FROM parking_user AS a,
                        (SELECT @pos:=@pos+1 AS rank,uid,car_price FROM parking_user ORDER BY car_price DESC, id ASC) AS b
                        WHERE a.uid=b.uid AND a.uid=:uid";

            }
        }

        $reuslt = $this->_rdb->fetchRow($sql, array('uid' => $uid));

        return $reuslt['rank'];
    }
}
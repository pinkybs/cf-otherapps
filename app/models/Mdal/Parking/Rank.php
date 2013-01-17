<?php

require_once 'Mdal/Abstract.php';

class Mdal_Parking_Rank extends Mdal_Abstract
{
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
    public function getRankingUser($uid, $fids, $type1, $type2, $pageSize=16, $order='DESC', $isTop = 0)
    {
        if ($isTop) {
            $start = $isTop;
        }
        else {
            $start = 0;
        }

        if ($type1 == 1) {
            $fids = $this->_rdb->quote($fids);
            if ($type2 == 1) {
                $sql = "SELECT p.uid,(p.asset+p.car_price+b.price) AS ass,p.last_login_time > (unix_timestamp(now())-300) AS online,
                        1 AS type FROM parking_user AS p,parking_background AS b
                        WHERE p.uid IN ($fids,:uid) AND p.background = b.id ORDER BY ass $order, p.id ASC LIMIT $start,$pageSize";
            }
            else {
                $sql = "SELECT p.uid,car_price AS ass,p.last_login_time > (unix_timestamp(now())-300) AS online,1 AS type
                        FROM parking_user AS p,parking_background AS b
                        WHERE p.uid IN ($fids,:uid) AND p.background = b.id ORDER BY ass $order, p.id ASC LIMIT $start,$pageSize";
            }

            $temp = $this->_rdb->fetchAll($sql, array('uid' => $uid));
        }
        else {
            if ($type2 == 1) {
                $sql = "SELECT p.uid,(asset+car_price+price) AS ass,p.last_login_time > (unix_timestamp(now())-300) AS online,1 AS type
                        FROM parking_user AS p,parking_background AS b
                        WHERE b.id=p.background ORDER BY ass $order, p.id ASC LIMIT $start,$pageSize";
            }
            else {
                $sql = "SELECT uid,car_price AS ass,last_login_time > (unix_timestamp(now())-300) AS online,1 AS type
                        FROM parking_user ORDER BY ass $order, id ASC LIMIT $start,$pageSize";
            }

            $temp = $this->_rdb->fetchAll($sql);
        }

        return $temp;
    }

}
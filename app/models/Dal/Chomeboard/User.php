<?php

require_once 'Dal/Abstract.php';

class Dal_Chomeboard_User extends Dal_Abstract
{
    /**
     * user table name
     *
     * @var string
     */
    protected $table_user = 'chome_board_user';
    
    protected static $_instance;
    
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        
        return self::$_instance;
    }

    public function getUser($uid)
    {
        $sql = "SELECT * FROM $this->table_user WHERE uid=:uid";
        
        return $this->_rdb->fetchRow($sql, array('uid' => $uid));
    }
    
    public function updateUser($uid)
    {
        $time = time();
        $sql = "INSERT INTO $this->table_user (uid, time) "
             . "VALUES (:uid, $time) ON DUPLICATE KEY UPDATE "
             . "time = $time";
        
        $params = array(
            'uid' => $uid
        );
        
        return $this->_wdb->query($sql, $params);
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
            $sql = "SELECT count(1)+1 FROM $this->table_user WHERE uid IN ($fids)";
            return $this->_rdb->fetchOne($sql,array('uid'=>$uid));
        }
        else {
            $sql = "SELECT count(1) FROM $this->table_user";
            return $this->_rdb->fetchOne($sql);
        }
    }

    /**
     * get user rank number
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
                $sql = "SELECT b.rank,a.uid,b.comment_count FROM $this->table_user AS a,
                        (SELECT @pos:=@pos+1 AS rank,uid,be_commented AS comment_count
                        FROM $this->table_user
                        WHERE uid IN ($fids, :uid) ORDER BY comment_count ASC, time DESC) AS b
                        WHERE a.uid=b.uid AND a.uid=:uid";
            }
            else {
                $sql = "SELECT b.rank,a.uid,b.comment_count FROM $this->table_user AS a,
                        (SELECT @pos:=@pos+1 AS rank,uid,commented AS comment_count
                        FROM $this->table_user
                        WHERE uid IN ($fids, :uid) ORDER BY comment_count ASC, time DESC) AS b
                        WHERE a.uid=b.uid AND a.uid=:uid";
            }
        }
        else {
            if ($type2==1) {
                $sql = "SELECT b.rank,a.uid,b.comment_count FROM $this->table_user AS a,
                        (SELECT @pos:=@pos+1 AS rank,uid,be_commented AS comment_count
                        FROM $this->table_user ORDER BY comment_count ASC, time DESC) AS b
                        WHERE a.uid=b.uid AND a.uid=:uid";
            }
            else {
                $sql = "SELECT b.rank,a.uid,b.comment_count FROM $this->table_user AS a,
                        (SELECT @pos:=@pos+1 AS rank,uid,commented AS comment_count
                        FROM $this->table_user ORDER BY comment_count ASC, time DESC) AS b
                        WHERE a.uid=b.uid AND a.uid=:uid";
            }
        }
        $reuslt = $this->_rdb->fetchRow($sql, array('uid' => $uid));
        
        return $reuslt['rank'];
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
        
        if ($type1==1) {
            $fids = $this->_rdb->quote($fids);
            if ($type2 == 1) {
                $sql = "SELECT uid,be_commented AS comment_count FROM $this->table_user
                        WHERE uid IN ($fids,:uid) ORDER BY comment_count $order, time DESC LIMIT $start,$pageSize";
            }
            else {
                $sql = "SELECT uid,commented AS comment_count FROM $this->table_user
                        WHERE uid IN ($fids,:uid) ORDER BY comment_count $order, time DESC LIMIT $start,$pageSize";
            }

            $temp = $this->_rdb->fetchAll($sql,array('uid'=>$uid));
        }
        else {
            if ($type2 == 1) {
                $sql = "SELECT uid,be_commented AS comment_count FROM $this->table_user
                        ORDER BY comment_count $order, time DESC LIMIT $start,$pageSize";
            }
            else {
                $sql = "SELECT uid,commented AS comment_count FROM $this->table_user
                        ORDER BY comment_count $order, time DESC LIMIT $start,$pageSize";
            }

            $temp = $this->_rdb->fetchAll($sql);
        }
        
        return $temp;
    }
    
    
    /**
     * update commented count
     *
     * @param integer $uid
     * @param int $num
     * @return void
     */
    public function updateCommentedCount($uid, $num)
    {
        $sql = "UPDATE $this->table_user SET commented = commented + $num WHERE uid=:uid";

        $this->_wdb->query($sql, array('uid' => $uid));
    }

    /**
     * update be commented count
     *
     * @param integer $uid
     * @param int $num
     * @return void
     */
    public function updateBecommentedCount($uid, $num)
    {
        $sql = "UPDATE $this->table_user SET be_commented = be_commented + $num WHERE uid=:uid";

        $this->_wdb->query($sql, array('uid' => $uid));
    }
    


}
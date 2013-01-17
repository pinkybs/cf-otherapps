<?php

require_once 'Dal/Abstract.php';

class Dal_Parking_Nopark extends Dal_Abstract
{
	/**
	 * user	table name
	 *
	 * @var	string
	 */
	protected $table_nopark	= 'parking_nopark';

	protected static $_instance;

	public static function getDefaultInstance()
	{
		if (self::$_instance ==	null) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * insert no park
	 *
	 * @param array	$info
	 * @return unknown
	 */
	public function	insertNoPark($info)
	{
		$this->_wdb->insert($this->table_nopark, $info);
		return $this->_wdb->lastInsertId();
	}

	/**
	 * delete no park
	 *
	 * @param integer $uid
	 * @param integer $cid
	 * @param string $color
	 */
	public function	deleteNoPark($uid, $cid, $color)
	{
		$sql = "DELETE FROM	$this->table_nopark WHERE	uid=:uid AND car_id=:cid AND car_color=:color";
		$this->_wdb->query($sql,array('uid'=>$uid, 'cid'=>$cid, 'color'=>$color));
	}

	/**
	 * update no parking info
	 * @abstract lp
	 * @param integer $uid
	 * @param integer $cidNew
	 * @param string $colorNew
	 * @param integer $cidOld
	 * @param string $colorOld
	 */
	public function	updateNoParkingInfo($uid, $cidNew, $colorNew, $cidOld, $colorOld)
	{
		$sql = "UPDATE $this->table_nopark SET car_id=:cidNew,car_color=:colorNew	WHERE uid=:uid AND car_id=:cidOld AND car_color=:colorOld";
		$this->_wdb->query($sql, array('cidNew'=>$cidNew,'colorNew'=>$colorNew,  'uid'=>$uid, 'cidOld'=>$cidOld, 'colorOld'=>$colorOld));
	}

    /**
     * update no parking uid
     * 
     * @param string $uid
     * @param integer $cid
     * @param string $color
     * @param string $uidNew
     */
    public function updateNoParkingUid($uid, $cid, $color, $uidNew)
    {
        $sql = "UPDATE $this->table_nopark SET uid=:uidNew WHERE uid=:uid AND car_id=:cid AND car_color=:color";
        $this->_wdb->query($sql, array('uid'=>$uid, 'cid'=>$cid, 'color'=>$color ,'uidNew'=>$uidNew));
    }
    
	/**
	 * get no park
	 *
	 * @param integer $uid
	 * @param integer $car_id
	 * @param string $car_color
	 * @return array
	 */
	public function	getNoPark($uid,	$car_id, $car_color)
	{
		$sql = "SELECT * FROM $this->table_nopark WHERE uid=:uid AND car_id=:car_id AND car_color=:car_color";
		return $this->_rdb->fetchRow($sql,array('uid'=>$uid, 'car_id'=>$car_id, 'car_color'=>$car_color));
	}

}
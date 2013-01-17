<?php

require_once 'Bll/Abstract.php';

/**
 * parking use item logic's Operation
 *
 * @package    Bll
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2008/03/04    Huch
 */
class Bll_Parking_Useitem extends Bll_Abstract
{
	/**
	 * use card free
	 *
	 * @param string $uid
	 * @return integer
	 */
	public function free($uid)
	{
		$result = -1;

		//check user have card
		require_once 'Dal/Parking/Item.php';
		$dalItem = new Dal_Parking_Item();
		if (!$dalItem->hasCard($uid, 1)) {
			return $result;
		}

		$this->_wdb->beginTransaction();

		try {
			//update user card info
			$dalItem->updateUserCard($uid, 1);

			//update parking_user
			require_once 'Dal/Parking/Puser.php';
			$dalPark = new Dal_Parking_Puser();

			$array = array('free_park' => 0);
			$dalPark->update($uid, $array);

            $this->_wdb->commit();
            $result = 1;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
        }

        return $result;
	}

	/**
	 * use card bomb
	 *
	 * @param string $uid
	 * @return integer
	 */
	public function bomb($uid)
	{
		$result = -1;

		//check user have card
		require_once 'Dal/Parking/Item.php';
		$dalItem = new Dal_Parking_Item();
		if (!$dalItem->hasCard($uid, 5)) {
			return $result;
		}

		//check location
		require_once 'Dal/Parking/Puser.php';
		$dalPark = new Dal_Parking_Puser();
		$parkUser = $dalPark->getUserBombLocation($uid);

		require_once 'Dal/Parking/Parking.php';
		$dalParking = new Dal_Parking_Parking();
		$parkLocation = $dalParking->getPakingLocation($uid);

		$bomb = array();

		for ($i = 1; $i <= $parkUser['parking']; $i++) {
			$temp = 0;
			foreach ($parkLocation as $item) {
				if ($item['location'] == $i) {
					$temp = 1;
					continue;
				}
			}
			if ($temp == 1) {
				continue;
			}

			if ($parkUser['location' . $i] == '0') {
				$bomb = array_merge($bomb, array(array('location' => $i)));
			}
		}

		if (count($bomb) == 0) {
			return -2;
		}

		$this->_wdb->beginTransaction();

		try {
			//update user card info
			$dalItem->updateUserCard($uid, 5);

			//update parking_user_bomb
			$rand = rand(0, count($bomb) - 1);

			$array = array('location' . $bomb[$rand]['location'] => 1);
			$dalItem->updateUserBomb($uid, $array);

            $this->_wdb->commit();
            $result = 1;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
        }

        return $result;
	}

	/**
	 * use card yanki
	 *
	 * @param string $uid
	 * @param string $fid
	 * @return integer
	 */
	public function yanki($uid, $fid)
	{
		$result = -1;

		//check user have card
		require_once 'Dal/Parking/Item.php';
		$dalItem = new Dal_Parking_Item();
		if (!$dalItem->hasCard($uid, 11)) {
			return $result;

		}

		//check is friend
		require_once 'Bll/Friend.php';
		if (!Bll_Friend::isFriend($uid, $fid)) {
			return $result;
		}

		//check location
		require_once 'Dal/Parking/Parking.php';
		$dalParking = new Dal_Parking_Parking();
		$parkLocation = $dalParking->getPakingLocation($fid);

		require_once 'Dal/Parking/Puser.php';
		$dalPark = new Dal_Parking_Puser();
		$parkUser = $dalPark->getUserYankiLocation($fid);

		$yanki = array();

		for ($i = 1; $i <= $parkUser['parking']; $i++) {
			if ($parkUser['free_park'] == $i) {
				continue;
			}

			$temp = 0;
			foreach ($parkLocation as $item) {
				if ($item['location'] == $i) {
					$temp = 1;
					continue;
				}
			}
			if ($temp == 1) {
				continue;
			}

			if (time() - $parkUser['location' . $i] > 3600*72) {
				$yanki = array_merge($yanki, array(array('location' => $i)));
			}
		}

		if (count($yanki) == 0) {
			return -2;
		}

		$this->_wdb->beginTransaction();

		try {
			//update user card info
			$dalItem->updateUserCard($uid, 11);

			//update parking_user_yanki
			$rand = rand(0, count($yanki) - 1);

			$array = array('location' . $yanki[$rand]['location'] => time());
			$dalItem->updateUserYanki($fid, $array);

			$create_time = date('Y-m-d H:i:s');
			$minifeed1 = array('uid' => $uid,
                              'template_id' => 36,
                              'actor' => $uid,
                              'target' => $fid,
                              'icon' => Zend_Registry::get('static') . "/apps/parking/img/icon/item.gif",
                              'create_time' => $create_time);
            $dalItem->insertMinifeed($minifeed1);

            $minifeed2 = array('uid' => $fid,
                              'template_id' => 35,
                              'actor' => $uid,
                              'target' => $fid,
                              'icon' => Zend_Registry::get('static') . "/apps/parking/img/icon/loss.gif",
                              'create_time' => $create_time);
            $dalItem->insertMinifeed($minifeed2);

            //insert into newsfeed
            $minifeed2['template_id'] = 36;
            $minifeed2['icon'] = Zend_Registry::get('static') . "/apps/parking/img/icon/item.gif";
            $dalItem->insertNewsfeed($minifeed1);
            $dalItem->insertNewsfeed($minifeed2);


            $this->_wdb->commit();
            $result = 1;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
        }

        return $result;
	}
	/**
     * use bribery
     * @author lp
     */
	public function bribery($uid){
		$result = -1;

        //check user have card
        require_once 'Dal/Parking/Item.php';
        $dalItem = new Dal_Parking_Item();
        if (!$dalItem->hasCard($uid, 3)) {
            return $result;
        }
        //check user last_bribery_time
        $last_bribery_time = $dalItem->getUserBriberyTime($uid);
        if( $last_bribery_time != null){
            $last_bribery_time=( time()-$last_bribery_time )/86400;

            if( $last_bribery_time <=3 ){
                return -2;
            }
        }

        $this->_wdb->beginTransaction();

        try {
            //update user card info
            $dalItem->updateUserCard($uid, 3);

            //update parking_user
            $time=time();
            $dalItem->updateUserBribery($uid, $time);

            $this->_wdb->commit();
            $result = 1;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
        }

        return $result;
	}
    /**
     * use check
     * @author lp
     */
    public function check($uid){
        $result = -1;

        //check user have card
        require_once 'Dal/Parking/Item.php';
        $dalItem = new Dal_Parking_Item();
        if (!$dalItem->hasCard($uid, 6)) {
            return $result;
        }

        //check user last_check_time
        $last_check_time = $dalItem->getUserCheckTime($uid);
        if( $last_check_time != null ){
            $last_check_time=( time()-$last_check_time )/86400;

            if( $last_check_time <=1 ){
                return -2;
            }
        }

        $this->_wdb->beginTransaction();

        try {
            //update user card info
            $dalItem->updateUserCard($uid, 6);

            //update parking_user
            $time=time();
            $dalItem->updateUserCheck($uid, $time);

            $this->_wdb->commit();
            $result = 1;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
        }

        return $result;
    }
    /**
     * use insurance
     * @author lp
     */
    public function insurance($uid){
        $result = -1;

        //check user have card
        require_once 'Dal/Parking/Item.php';
        $dalItem = new Dal_Parking_Item();
        if (!$dalItem->hasCard($uid, 7)) {
            return $result;
        }
        $getInsuranceState = $dalItem->getInsuranceState($uid);
        if($getInsuranceState['insurance_card']==1){
        	return 2;
        }
        $this->_wdb->beginTransaction();

        try {
            //update user card info
            $dalItem->updateUserCard($uid, 7);

            //update parking_user
            $dalItem->updateUserInsurance($uid);

            $this->_wdb->commit();
            $result = 1;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
        }

        return $result;
    }
    /**
     * use guard
     * @author lp
     */
    public function guard($uid){
    	$result = -1;

        //check user have card
        require_once 'Dal/Parking/Item.php';
        $dalItem = new Dal_Parking_Item();

        if (!$dalItem->hasCard($uid, 9)) {
            return $result;
        }

        require_once 'Dal/Parking/Store.php';
        $dalStore = new Dal_Parking_Store();

        $last_yanki_time = 0;
        $userYanKiItemInfo = $dalStore->getUserYanKiItemInfo($uid);

        for($i=1; $i<=8; $i++){
            $last_yanki_time = (time()-$userYanKiItemInfo['location'.$i])/(24*3600);
            if($last_yanki_time <= 3){
                    break;
                }
            }

        if( $last_yanki_time > 3 ){
            return -2;
        }

        $this->_wdb->beginTransaction();

        try {
            //update user card info
            $dalItem->updateUserCard($uid, 9);

            //update parking_user
            $dalItem->updateUserGuard($uid);

            $this->_wdb->commit();
            $result = 1;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
        }

        return $result;
    }
    /**
     * use evasion
     * @author lp
     */
    public function evasion($uid){
        $result = -1;

        //check user have card
        require_once 'Dal/Parking/Item.php';
        $dalItem = new Dal_Parking_Item();
        if (!$dalItem->hasCard($uid, 8)) {
            return $result;
        }

        //check user last_check_time
        $last_evasion_time = $dalItem->getUserEvasionTime($uid);
        if( $last_evasion_time != null ){
            $last_evasion_time=( time()-$last_evasion_time )/86400;

            if( $last_evasion_time <=2 ){
                return -2;
            }
        }

        $this->_wdb->beginTransaction();

        try {
            //update user card info
            $dalItem->updateUserCard($uid, 8);

            //update parking_user
            $time=time();
            $dalItem->updateUserEvasion($uid, $time);

            $this->_wdb->commit();
            $result = 1;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
        }

        return $result;
    }
	/**
	 * use card repair
	 *
	 * @param string $uid
	 * @param integer $cid
	 * @param integer $color
	 * @return integer
	 */
	public function repair($uid, $cid, $color)
	{
		$result = -1;

		//check user have card
		require_once 'Dal/Parking/Item.php';
		$dalItem = new Dal_Parking_Item();
		if (!$dalItem->hasCard($uid, 10)) {
			return $result;
		}

		//check car
		require_once 'Dal/Parking/Car.php';
		$dalCar = Dal_Parking_Car::getDefaultInstance();
		if(!$dalCar->isUserBreakCar($uid,$cid,$color)) {
			return $result;
		}

		$this->_wdb->beginTransaction();

		try {
			//update user card info
			$dalItem->updateUserCard($uid, 10);

			//update parking_user_car
			$dalCar->updateUserCar($uid,$cid,$color,1);

			//update parking_user car_price
			$dalCar->updateUserCarCount($uid);

			//inser into parking_nopark
			require_once 'Dal/Parking/Nopark.php';
			$dalNoPark = Dal_Parking_Nopark :: getDefaultInstance();

			$noParkingInfo = array('uid' =>$uid,
			                       'car_id' =>$cid,
			                       'car_color' =>$color,
			                       'create_time' =>time()
			                       );
			$dalNoPark->insertNoPark($noParkingInfo);

            $this->_wdb->commit();
            $result = 1;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
        }

        return $result;
	}
    /**
     * @param string $uid
     * @param integer $cid
     * @return integer
     */
    public function freshpage($uid,$cid){
        require_once 'Dal/Parking/Item.php';
        $dalItem = new Dal_Parking_Item();

        $usedCardInfo = $dalItem->getUsedCardInfo($uid,$cid);

        return $usedCardInfo;
    }
}
<?php
require_once 'Bll/Abstract.php';

class Bll_Parking_Item extends Bll_Abstract{

    /**
     * buy change park card
     * @author lp
     * @param integer $uid
     * @return integer
     */
    public function buyChangeParkCard($uid)
    {
        $result = -1;

        require_once 'Dal/Parking/Store.php';
        $dalStore = new Dal_Parking_Store();

        $cardInfo = $dalStore->getCardInfo(1);
        $userParkingInfo = $dalStore->getUserPark($uid);
        $userCardCount = $dalStore->getUserCardCoutByCid(1, $uid);

        if (empty($cardInfo)) {
            return -1;
        }

        //check user card
        if (!empty($userCardCount)) {
            return -2;
        }

        //check user park
        if ($userParkingInfo['free_park'] == 0) {
            return -3;
        }

        //check user have enough asset
        if ( $userParkingInfo['asset'] < $cardInfo['price'] ){
            return 0;
        }

        $this->_wdb->beginTransaction();

        try {
            //buy a new card
            $dalStore->updateUserCardCoutByCid(1,$uid,1);
            //update user asset
            $dalStore->updateUserAsset($cardInfo['price'], $uid);

            $this->_wdb->commit();
            $result = 1;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
        }
        return $result;
    }

    /**
     * buy card
     * @author lp
     * @param integer $cid
     * @param integer $uid
     * @return boolean
     */
    public function buyCard($cid, $uid)
    {
        $result = -1;
        try {
            require_once 'Dal/Parking/Store.php';
            $dalStore = new Dal_Parking_Store();

            $this->_wdb->beginTransaction();

            //get card info
            $cardInfo = $dalStore->getCardInfo($cid);
            if ( empty($cardInfo) ) {
                return $result;
            }

            //get user parking info
            $userParkingInfo = $dalStore->getUserPark($uid);


            //check user have enough asset
            if ( $userParkingInfo['asset'] < $cardInfo['price'] ){
                return 0;
            }

            $dalStore->updateUserCardCoutByCid($cid, $uid, '1');
            $dalStore->updateUserAsset($cardInfo['price'], $uid);

            $this->_wdb->commit();
            $result = 1;

        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return $result;
        }
        return $result;
    }

}
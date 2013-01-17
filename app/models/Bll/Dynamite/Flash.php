<?php

require_once 'Bll/Abstract.php';

/**
 * Dynamite flash logic's Operation
 *
 * @package    Bll
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2008/08/07    lp
 */
class Bll_Dynamite_Flash extends Bll_Abstract
{

    public function getGadetData($owner)
    {
        $data = array();
        $appId = 6230;

        //get user bounty, heart
        require_once 'Dal/Dynamite/User.php';
        $dalUser = Dal_Dynamite_User::getDefaultInstance();
        $userInfo = $dalUser->getUser($owner);

        require_once 'Bll/Cache/Dynamite.php';
        $hitmanInfo = Bll_Cache_Dynamite::getHitmanType();
        $userInfo['pic_id'] = $hitmanInfo[$userInfo['hitman_type']-1]['pic_id'];

        if (empty($userInfo)) {
        	return $data;
        }

        $dynamiteUrl = urlencode(MIXI_HOST . '/run_appli.pl?id=' . $appId . '&owner_id=' . $owner);

        $data[] = array('url' => $dynamiteUrl, 'bg' => $userInfo['pic_id']);

        //get user every hitman bomb power
        require_once 'Dal/Dynamite/Bomb.php';
        $dalBomb = Dal_Dynamite_Bomb::getDefaultInstance();
        $nowUserHitmanBomb = $dalBomb->getUserHitmanBomb($owner);

        $hitman1 = array();
        $hitman2 = array();
        $hitman3 = array();
        $hitman4 = array();
        foreach ($nowUserHitmanBomb as $value) {
            if ($value['bomb_hitman'] == 1) {
                $hitman1[] = $value['bomb_power'];
            }
            if ($value['bomb_hitman'] == 2) {
                $hitman2[] = $value['bomb_power'];
            }
            if ($value['bomb_hitman'] == 3) {
                $hitman3[] = $value['bomb_power'];
            }
            if ($value['bomb_hitman'] == 4) {
                $hitman4[] = $value['bomb_power'];
            }
        }

        $hitmanArray = array('1' => $hitman1, '2' => $hitman2, '3' => $hitman3, '4' => $hitman4);

        for ($i = 1; $i <= 4; $i++) {
            if ($userInfo['hitman_life' . $i] == 0) {
                $data[$i] = array('bounty' => '0',
                                  'heart' => '0',
                                  'bomb0' => null,
                                  'bomb1' => null,
                                  'bomb2' => null,
                                  'bomb3' => null);
            }
            else {
                $data[$i] = array('bounty' => round($userInfo['bonus'] * 0.1),
                                  'heart' => $userInfo['hitman_life' . $i]);

                for ($j = 0; $j < 4; $j++) {
                    $data[$i]['bomb' . $j] = $hitmanArray[$i][$j];
                }
            }
        }
        //$jsonDate = Zend_Json::encode($data);

        return $data;

    }
}
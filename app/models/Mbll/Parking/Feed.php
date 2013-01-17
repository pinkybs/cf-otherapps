<?php

require_once 'Mbll/Abstract.php';

/**
 * parking feed Operation
 *
 * @package    Mbll
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/05/19    lp
 */
class Mbll_Parking_Feed extends Mbll_Abstract
{
    /**
     * get minifeed
     *
     * @param integer $uid
     * @return array
     */
    public function getMinifeed($uid)
    {
        require_once 'Dal/Parking/Feed.php';
        $dalParkFeed = new Dal_Parking_Feed();
        $feed = $dalParkFeed->getMinifeed($uid, 1, 8);

        return $this->buildFeed($feed);
    }
    /**
     * get newsfeed
     *
     * @param integer $uid
     * @return array
     */
    public function getNewsfeed($uid)
    {
        require_once 'Bll/Friend.php';
        $friendList = Bll_Parking_Friend::getFriendIds($uid);
        $aryFriendIds = explode(',', $friendList);

        require_once 'Dal/Parking/Feed.php';
        $dalParkFeed = new Dal_Parking_Feed();
        $feed = $dalParkFeed->getNewsfeed($uid, $aryFriendIds, 1, 8);

        return $this->buildFeed($feed);
    }
    /**
     * build feed
     *
     * @param array $feed
     * @return array
     */
    public function buildFeed($feed)
    {
        $lnml_env = array('is_mobile' => false);

        require_once 'Bll/Parking/Index.php';
        $bllParking = new Bll_Parking_Index();

        require_once 'Zend/Json.php';

        for($i = 0; $i < count($feed); $i++) {

            //0->app's id,get title about table feed_template
            $feed_title_template = $bllParking->getFeedTemplateTitle(0, $feed[$i]['template_id'], $lnml_env['is_mobile']);

            $title_lnml = $this->buildTemplateLnml($feed[$i]['actor'], $feed[$i]['target'], $feed_title_template, Zend_Json::decode($feed[$i]['title']));

            if ($title_lnml) {
                $feed[$i]['title'] = $title_lnml;
            }
            else {
                $feed[$i]['title'] = '';
            }
        }

        foreach ($feed as $key => $value) {
            $strArray = explode('/', $value['icon']);
            $iconName = $strArray[count($strArray)-1];

            switch ($iconName) {
                case 'car.gif':
                    $feed[$key]['icon'] = 'F8BF';
                    break;
                case 'gift.gif':
                    $feed[$key]['icon'] = 'F8E6';
                    break;
                case 'estate.gif':
                    $feed[$key]['icon'] = 'F8C5';
                    break;
                case 'money.gif':
                    $feed[$key]['icon'] = 'F97A';
                    break;
                case 'loss.gif':
                    $feed[$key]['icon'] = 'F9A5';
                    break;
                case 'free.gif':
                    $feed[$key]['icon'] = 'F8CD';
                    break;
                case 'gas.gif':
                    $feed[$key]['icon'] = 'F8CC';
                    break;
                case 'item.gif':
                    $feed[$key]['icon'] = 'F9A3';
                    break;
                case 'police.gif':
                    $feed[$key]['icon'] = 'F8C0';
                    break;
                default:
                    break;

            }
        }
        return $feed;
    }
    /**
     * build template lnml
     *
     * @param integer $user
     * @param integer $target
     * @param string $template
     * @param array $json_array
     * @return string
     */
    public function buildTemplateLnml($user, $target, $template, $json_array)
    {
        if ($json_array == null) {
            $json_array = array();
        }

        if (!is_array($json_array)) {
            return false;
        }

        require_once 'Bll/User.php';
        $actor = Bll_User::getPerson($user);

        if (empty($actor)) {
            $actor_name = "____";
        }
        else {
            $actor_name = $actor->getDisplayName();
        }

        $actorUrl = Zend_Registry::get('host') . '/mobile/parking/start?parking_pid=' . $user;
        $actorUrl = $this->changeCommenUrlToMixiUrl($actorUrl);
        $json_array['actor'] = '<a href="' . $actorUrl . '"  >' . $actor_name . '</a>';

        if ($target) {
            if ($target < 0) {
                require_once 'Dal/Parking/Neighbor.php';
                $dalPark = new Dal_Parking_Neighbor();
                $json_array['target'] = $dalPark->getNeighborName($target);
            }
            else {
                $targ = Bll_User::getPerson($target);

                if ( empty($targ) ) {
                    $target_name = "____";
                }
                else {
                    $target_name = $targ->getDisplayName();
                }

                $targetUrl = Zend_Registry::get('host') . '/mobile/parking/start?parking_pid=' . $target;
                $targetUrl = $this->changeCommenUrlToMixiUrl($targetUrl);
                $json_array['target'] = '<a href="' . $targetUrl . '"  >' . $target_name . '</a>';
            }
        }

        $keys = array();
        $values = array();

        foreach ($json_array as $k => $v) {
            $keys[] = '{*' . $k . '*}';
            $values [] = $v;
        }

        return str_replace($keys, $values, $template);
    }
    /**
     * Smarty format url to mixi url
     *
     * Type:     modifier
     * Name:     mixiurl
     * Purpose:  Smarty format url to mixi url for mobile
     * @author   huch
     * @param    string url
     * @return   string mixi url
    */
    public function changeCommenUrlToMixiUrl($url)
    {
        $joinchar = (stripos($url,'?') === false) ? '?' : '&';
        return Zend_Registry::get('MIXI_APP_REQUEST_URL') . urlencode($url . $joinchar . 'parking_rand=' .rand());
    }
}
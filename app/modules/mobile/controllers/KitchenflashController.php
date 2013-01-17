<?php

/** @see MyLib_Zend_Controller_Action_Mobile.php */
require_once 'MyLib/Zend/Controller/Action/Mobile.php';
require_once 'Zend/Http/Client.php';

/**
 * Mobile Kitchen Flash Controller(modules/mobile/controllers/KitchenflashController.php)
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/12/31
 */
class Mobile_KitchenflashController extends MyLib_Zend_Controller_Action_Mobile
{

    /**
     * initialize object
     * override
     * @return void
     */
    public function init()
    {
        parent::init();
    }

    /**
     * deipatch
     *
     */
    function preDispatch()
    {
        $uid = $this->_user->getId();
        $this->view->ua = Zend_Registry::get('ua');
        $this->view->rand = time();
        $this->view->boardAppId = BOARD_APP_ID;
    }

    /**
     * school flash action
     *
     */
    public function changcharaAction()
    {
        $uid = $this->_user->getId();

        // get swf
        $mixiUrl = $this->_mixiMobileUrl . $this->_APP_ID . ((Zend_Registry::get('ua') == 1) ? '/?guid=ON&amp;url=' : '/?url=');
        require_once 'Mbll/Kitchen/FlashCache.php';
        $swf = Mbll_Kitchen_FlashCache::getChangeChara($uid, $mixiUrl);

        //$this->render();
        ob_end_clean();
        ob_start();
        header("Accept-Ranges: bytes");
        header("Cache-Control: no-cache, must-revalidate");
        header("Content-Type: application/x-shockwave-flash");
        header("Content-Encoding: gzip");
        echo $swf;
        exit(0);
    }

	/**
     * kitchen flash action-selectgenre
     *
     */
    public function selectgenreAction()
    {
        $uid = $this->_user->getId();

        // get swf
        $mixiUrl = $this->_mixiMobileUrl . $this->_APP_ID . ((Zend_Registry::get('ua') == 1) ? '/?guid=ON&amp;url=' : '/?url=');
        require_once 'Mbll/Kitchen/FlashCache.php';
        $swf = Mbll_Kitchen_FlashCache::getSelectGenre($uid, $mixiUrl);

        //$this->render();
        ob_end_clean();
        ob_start();
        header("Accept-Ranges: bytes");
        header("Cache-Control: no-cache, must-revalidate");
        header("Content-Type: application/x-shockwave-flash");
        header("Content-Encoding: gzip");
        echo $swf;
        exit(0);
    }

    public function setgoodsAction()
    {
        $uid = $this->_user->getId();

        $goodId = $this->getParam('good_id');

        // get swf
        $mixiUrl = $this->_mixiMobileUrl . $this->_APP_ID . ((Zend_Registry::get('ua') == 1) ? '/?guid=ON&amp;url=' : '/?url=');
        require_once 'Mbll/Kitchen/FlashCache.php';
        $swf = Mbll_Kitchen_FlashCache::getSetGoods($uid, $goodId, $mixiUrl);

        //$this->render();
        ob_end_clean();
        ob_start();
        header("Accept-Ranges: bytes");
        header("Cache-Control: no-cache, must-revalidate");
        header("Content-Type: application/x-shockwave-flash");
        header("Content-Encoding: gzip");
        echo $swf;
        exit(0);
    }

 	/**
     * school flash call back action
     *
     */
    public function flashfwdAction()
    {
        $uid = $this->_user->getId();
        $parameter = $this->getParam('CF_fwd');
        if (empty($parameter) || strlen($parameter) < 4) {
            $this->_redirect($this->_baseUrl . '/mobile/school/home');
            return;
        }
        $aryWeekDay = array(1 => 'mon', 2 => 'tue', 3 => 'wed', 4 => 'thu', 5 => 'fri', 6 => 'sat');
        $strwday = substr($parameter, 0, 3);
        $wday = array_search($strwday, $aryWeekDay);
        $part = (int)(substr($parameter, 3));
        if (empty($wday) || empty($part)) {
            $this->_redirect($this->_baseUrl . '/mobile/school/home');
            return;
        }

        require_once 'Mdal/School/Timepart.php';
        $mdalTimepart = Mdal_School_Timepart::getDefaultInstance();
        $rowNowClass = $mdalTimepart->getTimepartScheduleByPk($uid, $wday, $part);
        if (!empty($rowNowClass)) {
            $this->_redirect($this->_baseUrl . '/mobile/school/class?CF_cid=' . $rowNowClass['cid']);
        }
        else {
            $this->_redirect($this->_baseUrl . '/mobile/school/classnameadd?CF_wday=' . $wday . '&CF_part=' . $part);
        }
        return;
    }

	/**
     * magic function
     *   if call the function is undefined,then forward to not found
     *
     * @param string $methodName
     * @param array $args
     * @return void
     */
    function __call($methodName, $args)
    {
        return $this->_redirect($this->_baseUrl . '/mobile/kitchen/error');
    }
}
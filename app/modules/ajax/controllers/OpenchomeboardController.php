<?php

/** @see Zend_Json */
require_once 'Zend/Json.php';

/**
 * mixi-chomeboard Ajax Controller
 * mixi-chomeboard Ajax Controller
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create    2009/05/25   shenhw
 */
class Ajax_OpenchomeboardController extends Zend_Controller_Action
{

    /**
     * initialize basic data
     * @return void
     */
    function init()
    {
        $font = $this->getFrontController();
        $font->unregisterPlugin('Zend_Controller_Plugin_ErrorHandler');
        $font->setParam('noViewRenderer', true);
    }

    /**
     * get gadet flash data
     *
     */
    public function gadgetloadAction()
    {
        if ($this->_request->isPost()) {
            $id = $this->_request->getPost('userID');
            
            if (empty($id)) {
                echo 'gadgetData=' . Zend_Json::encode(array());
            }
            
            $temp = explode('*', $id);
            
            require_once 'Bll/Chomeboard/Flash.php';
            $bllFlash = new Bll_Chomeboard_Flash();
            $data = $bllFlash->getGadetData($temp[0]);
            
            echo 'gadgetData=' . Zend_Json::encode($data);
        }
        else {
            echo 'gadgetData=' . Zend_Json::encode(array());
        }
    }
}
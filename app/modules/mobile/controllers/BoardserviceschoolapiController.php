<?php

require_once 'Zend/Json.php';

/**
 * Mobile School Controller(modules/mobile/controllers/SchoolController.php)
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/11/09
 */
class Mobile_BoardserviceschoolapiController extends Zend_Controller_Action
{

    protected $_secretKey;

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
        $secret = Zend_Registry::get('secret');
        $this->_secretKey = $secret['validationKey'];
    }

    /**
     * validate params validation
     *
     * @param string $validString
     * @param array $aryParam
     * @return boolean
     */
    private function _validate($validString, $aryParam)
    {
        $paramString = '';
        foreach ($aryParam as $value) {
            $paramString .= $value;
        }
        return $validString == md5($paramString . $this->_secretKey);
    }

	/**
     * listAshiato action
     *
     */
    public function listashiatoAction()
    {
        $uid = $this->_request->getParam('CF_uid');
        $size = $this->_request->getParam('CF_size');
        $valid = $this->_request->getParam('CF_valid');
        if (!$this->_validate($valid, array($uid, $size))) {
            echo '';
            exit(0);
        }

        require_once 'Dal/Board/Board.php';
        $dalBoard = Dal_Board_Board::getDefaultInstance();
        $lstBoard = $dalBoard->getComments($uid, 1, $size);

        echo Zend_Json::encode($lstBoard);
        exit(0);
    }

	/**
     * getAshiato count action
     *
     */
    public function getashiatocountAction()
    {
        $uid = $this->_request->getParam('CF_uid');
        $valid = $this->_request->getParam('CF_valid');
        if (!$this->_validate($valid, array($uid))) {
            echo '';
            exit(0);
        }

        require_once 'Dal/Board/Board.php';
        $dalBoard = Dal_Board_Board::getDefaultInstance();
        $count = $dalBoard->getCommentsCount($uid);

        echo $count;
        exit(0);
    }

	/**
     * getAshiato count action
     *
     */
    public function getashiatouserAction()
    {
        $uid = $this->_request->getParam('CF_uid');
        $valid = $this->_request->getParam('CF_valid');
        if (!$this->_validate($valid, array($uid))) {
            echo '';
            exit(0);
        }

        require_once 'Dal/Board/User.php';
        $dalUser = Dal_Board_User::getDefaultInstance();
        $rowUser = $dalUser->getUser($uid);
        $rtnAry = empty($rowUser) ? '' : array($rowUser);
        echo Zend_Json::encode($rtnAry);
        exit(0);
    }

	/**
     * add ashiato action
     *
     */
    public function addashiatoAction()
    {
        $uid = $this->_request->getParam('CF_uid');
        $targetUid = $this->_request->getParam('CF_targetuid');
        $comment = $this->_request->getParam('CF_comment');
        $time = $this->_request->getParam('CF_time');
        $valid = $this->_request->getParam('CF_valid');

        if (!$this->_validate($valid, array($uid, $targetUid, $comment, $time))) {
            echo '';
            exit(0);
        }

        require_once 'Bll/Board/Board.php';
        $bllBoard = new Bll_Board_Board();
        $aryBoard = array();
        $aryBoard['uid'] = $targetUid;
        $aryBoard['comment_uid'] = $uid;
        $aryBoard['type'] = 0;
        $aryBoard['content'] = $comment;
        $aryBoard['create_time'] = date('Y-m-d H:i:s');
        $rst = $bllBoard->newBoard($aryBoard);

        echo empty($rst) ? '0' : '1';
        exit(0);
    }

}
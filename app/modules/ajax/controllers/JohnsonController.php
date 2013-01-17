<?php

/** @see Zend_Json */
require_once 'Zend/Json.php';
/** @see MyLib_Zend_Controller_Action_Ajax */
require_once 'MyLib/Zend/Controller/Action/Ajax.php';

class Ajax_JohnsonController extends MyLib_Zend_Controller_Action_Ajax
{
	/**
     * after game over, operate user infomation, used by flash
     *
     */
    public function aftergameoverAction()
    {
    	$uid = $this->_user->getId();
        $encrypt = $this->_request->getParam('encrypt');
        $honorId = $this->_request->getParam('honor_id');
        $restItemId = $this->_request->getParam('rest_item_id');
        $score = $this->_request->getParam('score');        
        
        require_once 'Bll/Johnson/Johnson.php';
        $bllJohnson = new Bll_Johnson_Johnson();

        $response = $bllJohnson->afterGameOver($uid, $encrypt, $honorId, $restItemId, $score);

        $returnResult = Zend_Json::encode($response);

        echo $returnResult;
    }

    /**
     * other type rank
     *
     */
    public function othertyperankAction()
    {
        if ($this->_request->isPost()) {
            $type = $this->_request->getPost('type');
            $uid = $this->_user->getId();

            require_once 'Bll/Johnson/Johnson.php';
            $bllJohnson = new Bll_Johnson_Johnson();

            $response = $bllJohnson->otherTypeRank($uid, $type);

            $response = Zend_Json::encode($response);

            echo $response;
        }
    }

    /**
     * rank move to up or move to down
     *
     */
    public function gonextAction()
    {
        if ($this->_request->isPost()) {
            $rankStart = $this->_request->getPost('rankStart');
            $rankEnd = $this->_request->getPost('rankEnd');
            $direction = $this->_request->getPost('direction');
            $userCnt = $this->_request->getPost('userCnt');
            $type = $this->_request->getPost('type');
            $uid = $this->_user->getId();

            require_once 'Bll/Johnson/Johnson.php';
            $bllJohnson = new Bll_Johnson_Johnson();

            $response = $bllJohnson->goNext($uid, $rankStart, $rankEnd, $direction, $type);

            $response = Zend_Json::encode($response);
            echo $response;
        }
    }
}
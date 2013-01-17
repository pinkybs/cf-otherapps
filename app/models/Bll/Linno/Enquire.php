<?php

require_once 'Bll/Abstract.php';

/**
 * logic's Operation
 *
 * @package    Bll
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/06/26    Liz
 */
class Bll_Linno_Enquire extends Bll_Abstract{

    
    /**
     * get rand not answer enquire by user id
     *
     * @param ingeter $uid
     * @param ingeter $maxNum
     * @return array
     */
    public function getRandNotAnswerEnquire($uid, $maxNum = 1)
    {
        require_once 'Dal/Linno/Enquire.php';
        $dalLinnoEnquire = new Dal_Linno_Enquire();
        //get rand not answer enquire
        $enquire = $dalLinnoEnquire->getRandNotAnswerEnquire($uid, $maxNum);
        //get enquire answer
        $enquire = $this->getEnquireAnswerInfo($enquire);
        
        if ( !$enquire['1'] ) {
            $enquire['1'] = $enquire['0'];
        }
        
        return $enquire;
    }
    

    /**
     * get not answer enquire by user id
     *
     * @param ingeter $uid
     * @param integer $pageIndex
     * @param integer $pageIndex
     * @return array
     */
    public function getNotAnswerEnquire($uid, $pageIndex, $pageSize)
    {
        require_once 'Dal/Linno/Enquire.php';
        $dalLinnoEnquire = new Dal_Linno_Enquire();
        //get user not answer enquire info
        $enquireInfo = $dalLinnoEnquire->getNotAnswerEnquire($uid, $pageIndex, $pageSize);
        
        return $enquireInfo;
    }
        

    /**
     * get enquire answer info
     *
     * @param array $enquire
     * @return array
     */
    public function getEnquireAnswerInfo($enquire)
    {
        require_once 'Dal/Linno/Enquire.php';
        $dalLinnoEnquire = new Dal_Linno_Enquire();
        
        for ( $i=0, $iCount=count($enquire); $i < $iCount; $i++ ) {
            $enquire[$i]['answer'] = $dalLinnoEnquire->getAnswerByEid($enquire[$i]['qqid']);
        }
        
        return $enquire;
    }
    

}
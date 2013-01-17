<?php

require_once 'Bll/Abstract.php';

/**
 * logic's Operation
 *
 * @package    Bll
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/05/20    Liz
 */
class Bll_Scripteditor_Toppage extends Bll_Abstract{

    /**
     * get new entry list
     * 
     * @return array
     */
    public function getNewEntry()
    {
        require_once 'Dal/Scripteditor/Entry.php';
        $dalSterEntry = new Dal_Scripteditor_Entry();
        
        //get new php entry info and count
        $phpEntry = $dalSterEntry->getNewEntry(1, 1, 30);
        $phpEntryCount = $dalSterEntry->getEntryCount(1);
    
        Bll_User::appendPeople($phpEntry, 'uid');
        /*
        //get new perl entry info and count
        $perlEntry = $dalSterEntry->getNewEntry(2, 1, 5);
        $perlEntryCount = $dalSterEntry->getEntryCount(2);
        
        //get new ruby entry info and count
        $rubyEntry = $dalSterEntry->getNewEntry(3, 1, 5);
        $rubyEntryCount = $dalSterEntry->getEntryCount(3);
        
        //get new python entry info and count
        $pythonEntry = $dalSterEntry->getNewEntry(4, 1, 5);
        $pythonEntryCount = $dalSterEntry->getEntryCount(4);
        
        $newEntry = array('phpEntry' => $phpEntry,
                          'phpCount' => $phpEntryCount,
                          'perlEntry' => $perlEntry,
                          'perlCount' => $perlEntryCount,
                          'rubyEntry' => $rubyEntry,
                          'rubyCount' => $rubyEntryCount,
                          'pythonEntry' => $pythonEntry,
                          'pythonCount' => $pythonEntryCount);
        */
        $newEntry = array('phpEntry' => $phpEntry,
                          'phpCount' => $phpEntryCount);
        
        return $newEntry;
    }

    /**
     * get user entry list
     * 
     * @param string $uid
     * @return array
     */
    public function getUserEntry($uid)
    {
        require_once 'Dal/Scripteditor/Entry.php';
        $dalSterEntry = new Dal_Scripteditor_Entry();
        
        //get user php entry info and count
        $phpEntry = $dalSterEntry->getUserEntry($uid, 1, 1, 5, 1);
        $phpEntryCount = $dalSterEntry->getUserEntryCount($uid, 1, 1);

        //get user perl entry info and count
        $perlEntry = $dalSterEntry->getUserEntry($uid, 2, 1, 5, 1);
        $perlEntryCount = $dalSterEntry->getUserEntryCount($uid, 2, 1);
        
        //get user ruby entry info and count
        $rubyEntry = $dalSterEntry->getUserEntry($uid, 3, 1, 5, 1);
        $rubyEntryCount = $dalSterEntry->getUserEntryCount($uid, 3, 1);
        
        //get user python entry info and count
        $pythonEntry = $dalSterEntry->getUserEntry($uid, 4, 1, 5, 1);
        $pythonEntryCount = $dalSterEntry->getUserEntryCount($uid, 4, 1);
        
        //get user save entry info ande count
        $saveEntryCount = $dalSterEntry->getUserEntryCount($uid, 0, 0);
        $saveEntry = $dalSterEntry->getUserEntry($uid, 0, 1, $saveEntryCount, 0);
        
        
        $userEntry = array('phpEntry' => $phpEntry,
                           'phpCount' => $phpEntryCount,
                           'perlEntry' => $perlEntry,
                           'perlCount' => $perlEntryCount,
                           'rubyEntry' => $rubyEntry,
                           'rubyCount' => $rubyEntryCount,
                           'pythonEntry' => $pythonEntry,
                           'pythonCount' => $pythonEntryCount,
                           'saveEntry' => $saveEntry,
                           'saveCount' => $saveEntryCount);
        
        return $userEntry;
    }
    
    
    
}
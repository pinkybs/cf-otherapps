<?php

/**
 * open script editor controller
 * 
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/05/20    Liz
 */
class OpenscripteditorController extends Zend_Controller_Action
{

    /**
     * get gadgets
     * 
     */
    public function showgadgetsAction()
    {
        require_once 'Dal/Scripteditor/Entry.php';
        $dalSterEntry = new Dal_Scripteditor_Entry();
        $entry = $dalSterEntry->getOneNewEntry();
        $this->view->entry = $entry;
        
        $this->view->staticUrl = Zend_Registry::get('static');
        
        $this->render();
    }
    
}

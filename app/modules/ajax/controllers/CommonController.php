<?php
/** @see Zend_Json */
require_once 'Zend/Json.php';
/** @see MyLib_Zend_Controller_Action_Ajax */
require_once 'MyLib/Zend/Controller/Action/Ajax.php';

/**
 * Common Ajax Controllers
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/06/04    Liz
 */
class Ajax_CommonController extends MyLib_Zend_Controller_Action_Ajax
{

    /**
     * create the preview thumbtail picture
     *
     */
    public function thumbpreviewAction()
    {
        $section = (int)$this->_request->getParam('section', 0);
        
        $uid = $this->_user->getId();
        require_once 'Bll/ThumbPreview.php';
        $preview = new Bll_ThumbPreview(array('field' => 'upPhoto', 'section' => $section, 'id' => $uid));
        
        $result = $preview->doPreview();
        
        echo $result ? $result : false;
    }
    
    /**
     * view the thumbtail picture
     *
     */
    public function viewthumbAction()
    {
        $key = $this->_request->getParam('post_key', '');
        
        if ($key != '') {
            require_once 'Bll/Cache/ThumbPreview.php';
            
            $image = Bll_Cache_ThumbPreview::get($key);
            
            if ($image) {
                header('Content-type: image/jpeg');
                header('Content-transfer-encoding: binary');
                header("Content-Length: " . $image['length']);
                echo $image['data'];
            }
        }
    }
    
    public function validateurlAction()
    {
        $url = $this->_request->getParam('url', '');
        
        $valid = false;
        
        if ($url != '' && $url != 'http://') {
            require_once 'MyLib/Network.php';
            $valid = MyLib_Network::validateUrl($url);            
        }
        
        echo $valid ? 'true' : 'false';
    }
}

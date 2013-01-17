<?php

/** @see MyLib_Upload_Abstract */
require_once 'MyLib/Upload/Abstract.php';

/**
 * File upload
 * 
 * @package    MyLib_Upload
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2008/07/18     Hulj
 */
class MyLib_Upload_File extends MyLib_Upload_Abstract
{     
    /**
     * construct
     *
     * @param array $options
     * @return void
     */
    public function __construct($options = array())
    { 
        parent::callSetOptions($options);
        $this->_allowType = array('doc', 'xls', 'ppt', 'pdf', 'docx', 'xlsx', 'pptx');
        $this->_maxSize = 5120;
    }

}
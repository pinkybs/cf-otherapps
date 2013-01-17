<?php

/** @see MyLib_Upload_ThumbPreviewBuffer */
require_once ('MyLib/Upload/ThumbPreviewBuffer.php');

/**
 * ThumbPreview logic's Operation
 *
 * @package    Bll
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2008/08/04    HCH
 */
final class Bll_ThumbPreview
{
    /**
     * upload object,include upload file's message
     *
     * @var array
     */
    protected $_upload;

    /**
     * upload config
     * 'field'      --  filed name
     * 'section'    --  1  :  scripteditor
     *                  
     * 'id'         --  user id
     *
     * @var array
     */
    protected $_config = array('field' => '', 'section' => 0, 'id' => 0);

    /**
     * array section
     *
     * @var array
     */
    private $_section = array(0 => 'tmp', 1 => 'scripteditor' );

    /**
     * thumb name
     *
     * @var string
     */
    private $_thumbName = null;

    /**
     * init the thumb preview's variables
     *
     * @param array $config ( config info )
     */
    public function __construct($config = array())
    {
        $this->_upload = new MyLib_Upload_ThumbPreviewBuffer();
        if (!empty($config)) {
            foreach ($config as $key => $value) {
                if (array_key_exists($key, $this->_config)) {
                    $this->_config[$key] = $value;
                }
            }
        }
    }

    /**
     * get thumb name
     *
     * @return string
     */
    protected function _getThumbName()
    {
        if ($this->_thumbName !== null) {
            return $this->_thumbName;
        }

        $id = $this->_config['id'];
        $dir0 = $id % 10000;
        $dir1 = $dir0 % 100;
        $dir2 = $dir0 - $dir1 * 100;
        if ($dir2 < 0) {
            $dir2 = 0;
        }

        if (!array_key_exists($this->_config['section'], $this->_section)) {
            $this->_config['section'] = 0;
        }

        $name = $this->_section[$this->_config['section']] . '/' . $dir2 . '/' . $dir1 . '/preview_' . $this->_config['id'] . '_' . time();

        return md5($name);
    }

    /**
     * do preview
     *
     *
     * @return boolean
     */
    public function doPreview()
    {
        $result = false;

        try {
            $field = $this->_config['field'];
            $imagefile = $_FILES[$field]['tmp_name'];
            $truename = $_FILES[$field]['name'];

            require_once 'MyLib/Image/Validate.php';
            $validate = new MyLib_Image_Validate();

            if (!$validate->isValid($imagefile, $truename)) {
                return false;
            }

            $thumbName = $this->_getThumbName();
            $image = $this->_upload->upfile($field);

            if ($image) {
                require_once 'Bll/Cache/ThumbPreview.php';
                Bll_Cache_ThumbPreview::set($thumbName, $image);

                $result = $thumbName;
            }
        }
        catch (Exception $e) {

        }
        return $result;
    }

    /**
     * do preview by size
     *
     * @param integer $width
     * @param integer $height
     * @return boolean
     */
    public function doPreviewBySize($width = 170, $height = 170)
    {
        $result = false;

        try {
            $field = $this->_config['field'];
            $imagefile = $_FILES[$field]['tmp_name'];
            $truename = $_FILES[$field]['name'];

            require_once 'MyLib/Image/Validate.php';
            $validate = new MyLib_Image_Validate();

            if (!$validate->isValid($imagefile, $truename)) {
                return false;
            }

            $thumbName = $this->_getThumbName();
            $image = $this->_upload->upfile($field, $width, $height);

            if ($image) {
                require_once 'Bll/Cache/ThumbPreview.php';
                Bll_Cache_ThumbPreview::set($thumbName, $image);

                $result = $thumbName;
            }
        }
        catch (Exception $e) {

        }
        return $result;
    }
}
<?php

/** @see MyLib_Upload_Image */
require_once ('MyLib/Upload/Image.php');

/**
 * PhotoUpload logic's Operation
 *
 * @package    Bll
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2008/07/30    HCH
 */
final class Bll_PhotoUpload
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
     * 'section'    --  1  :  member
     *                  2  :  circle
     *                  3  :  circle topic
     *                  4  :  class
     *                  5  :  class topic
     *                  6  :  diary
     * 'basefolder' --  saved base folder name
     * 'id'         --  user id/circle id/ circle topic id
     *
     * @var array
     */
    protected $_config = array('field' => '', 'section' => 0, 'basefolder' => '', 'id' => 0);

    /**
     * file path
     *
     * @var string
     */
    private $_path = '';

    /**
     * init the photo upload's variables
     *
     * @param array $config ( config info )
     * @return void
     */
    public function __construct($config = array())
    {
        $this->_upload = new MyLib_Upload_Image();
        if (!empty($config)) {
            foreach ($config as $key => $value) {
                if (array_key_exists($key, $this->_config)) {
                    $this->_config[$key] = $value;
                }
            }
        }

        if ($this->_config['basefolder'] == '') {
            $this->_config['basefolder'] = Zend_Registry::get('photoBasePath');
        }
    }

    /**
     * buile the file path and return
     *
     * @return string
     */
    protected function _getPath()
    {
        if (!empty($this->_path)) {
            return $this->_path;
        }

        $id = $this->_config['id'];
        $dir0 = $id % 10000;
        $dir1 = $dir0 % 100;
        $dir2 = $dir0 - $dir1 * 100;
        if ($dir2 < 0) {
            $dir2 = 0;
        }
        $path = 'apps/';

        switch ($this->_config['section']) {
            case 1 :
                $path .= 'scripteditor/' . $dir2 . '/' . $dir1 . '/';
                break;
           	case 2 :
                $path .= 'slave/' . $dir2 . '/' . $dir1 . '/';
                break;
            default :
                $path .= 'tmp/';
                break;
        }
        $this->_path = $path;
        return $this->_path;
    }

    /**
     * get hostname
     *
     */
    private function _getHostName()
    {
        require_once 'Bll/Cache/CollegeBasic.php';
        return Bll_Cache_CollegeBasic::getHostNameById();
    }

    /**
     * do upload
     *
     * @return boolean
     */
    public function doUpload()
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

            $path = $this->_getPath();
            $result = $this->_upload->upfile($this->_config['field'],
                      array('baseFolder' => $this->_config['basefolder'], 'path' => $path, 'id' => $this->_config['id']));
        }
        catch (Exception $e) {

        }
        return $result;
    }

    /**
     * do upload by size
     *
     * @param integer $width
     * @param integer $height
     * @param boolean $isDelSrcImg
     * @return boolean
     */
    public function doUploadBySize($width = 170, $height = 170, $isDelSrcImg = true, $isCopyImg = false)
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

            //$path = $this->_getPath();
            //if (!empty($this->_path)) {
            //    return $this->_path;
            //}

            $id = $this->_config['id'];
            $dir0 = $id % 10000;
            $dir1 = $dir0 % 100;
            $dir2 = $dir0 - $dir1 * 100;
            if ($dir2 < 0) {
                $dir2 = 0;
            }

            $path = 'apps/';
            switch ($this->_config['section']) {

                case 1 :
                    $path .= 'scripteditor/' . $dir2 . '/' . $dir1 . '/';
                    break;
                case 2 :
	                $path .= 'slave/' . $dir2 . '/' . $dir1 . '/';
	                break;
                default :
                    $path .= 'tmp/';
                    break;
            }
            $this->_path = $path;

            $result = $this->_upload->upfileBySize($this->_config['field'],
                      array('baseFolder' => $this->_config['basefolder'], 'path' => $path, 'id' => $this->_config['id'],
                            'width' => $width, 'height' => $height, 'imageName' => $imageName, 'isDelSrcImg' => $isDelSrcImg,
                            'isCopyImg' => $isCopyImg));
        }
        catch (Exception $e) {

        }
        return $result;
    }
}
<?php

/**
 * Image file upload
 *
 * @package    MyLib_Upload
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2008/07/18     Hulj
 */
class MyLib_Upload_Image
{
    /**
     * base older
     *
     * @var string
     */
    protected $_baseFolder = '';

    /**
     * relative save path
     *
     * @var string
     */
    protected $_path = '';

    /**
     * @var int
     */
    protected $_id;

    /**
     * image size - width
     *
     * @var int
     */
    protected $_width;

    /**
     * image size - height
     *
     * @var int
     */
    protected $_height;

    /**
     * image name
     *
     * @var string
     */
    protected $_imageName;

    /**
     * is delete source temp file
     *
     * @var boolean
     */
    protected $_isDelSrcImg;

    /**
     * is copy sourc image
     *
     * @var boolean
     */
    protected $_isCopyImg;

    /**
     * upload image file information
     *
     * @var array
     */
    protected $_upfile = array();

    /**
     * construct
     *
     * @param array $options
     */
    public function __construct($options = array())
    {
        if (!empty($options)) {
            $this->_setOptions($options);
        }
    }

    /**
     * upload file
     *
     * @param string $field
     * @param array $options
     * @return array|false
     */
    public function upfile($field, $options = array())
    {
        //setting options
        if (!empty($options)) {
            $this->_setOptions($options);
        }

        $this->_upfile = $_FILES[$field];

        //setting name
        $this->_createThumbnailName();

        try {
            $this->_createThumbnail();
            @unlink($this->_upfile['tmp_name']);
            return $this->_upfile;
        }
        catch (Exception $e) {

        }

        return false;
    }

    /**
     * upload file by size
     *
     * @param string $field
     * @param array $options
     * @return array|false
     */
    public function upfileBySize($field, $options = array())
    {
        //setting options
        if (!empty($options)) {
            $this->_setOptions($options);
        }

        $this->_upfile = $_FILES[$field];

        //setting name
        //$this->_createThumbnailName();
        $realpath = $this->_baseFolder . '/' . $this->_path;

        //check file path
        if (!file_exists($realpath)) {
            $this->_mkpath($realpath);
        }

        if ($realpath[strlen($realpath) - 1] != '/') {
            $realpath .= '/';
        }

        $this->_upfile['create_time'] = time();
        //$filename = $this->_id . '_' . $this->_upfile['create_time'];
        //$filename = $this->_id . '_' . 'app';
        if (empty($this->_imageName)) {
            $filename = $this->_id . '_' . $this->_upfile['create_time'];
        }
        else {
            $filename = $this->_imageName;
        }
        $filename = $filename . '.jpg';

        //overwrite if file exists
        if (file_exists($realpath . $filename)) {
            //@unlink($realpath . $filename);
        }
        $this->_upfile['filename'] = $this->_path . $filename;


        //copy image
        if ($this->_isCopyImg) {
            $imgFile = $this->_baseFolder . '/' . $this->_upfile['filename'];
            $aryName = explode('.', $this->_upfile['name']);
            $extName = 'jpg';
            if (count($aryName) > 0) {
                $extName = $aryName[count($aryName) - 1];
            }

            $moveRst = move_uploaded_file($this->_upfile['tmp_name'], str_replace('jpg', $extName, $imgFile));

            if ($moveRst) {
                $this->_upfile['filename'] = $this->_path . str_replace('jpg', $extName, $filename);
            }
            if ($this->_isDelSrcImg) {
                @unlink($this->_upfile['tmp_name']);
            }
            return $this->_upfile;
        }
        //resize image
        else {
            try {
                //$this->_createThumbnail($this->_width, $this->_height);
                require_once 'MyLib/Image/Edit.php';
                $imageEditor = new MyLib_Image_Edit();

                $imgFile = $this->_baseFolder . '/' . $this->_upfile['filename'];
                $imageEditor->resize($this->_upfile['tmp_name'], $imgFile, $this->_width, $this->_height);

                if ($this->_isDelSrcImg) {
                    @unlink($this->_upfile['tmp_name']);
                }
                return $this->_upfile;
            }
            catch (Exception $e) {

            }
        }

        return false;
    }

    /**
     * create upload image thumbnail file name
     * @return void
     */
    protected function _createThumbnailName()
    {
        $realpath = $this->_baseFolder . '/' . $this->_path;

        //check file path
        if (!file_exists($realpath)) {
            $this->_mkpath($realpath);
        }

        if ($realpath[strlen($realpath) - 1] != '/') {
            $realpath .= '/';
        }

        $this->_upfile['create_time'] = time();
        $filename = $this->_id . '_' . $this->_upfile['create_time'];
        $bigfilename = $filename . '_b.jpg';
        $smallfilename = $filename . '_s.jpg';

        //overwrite if file exists
        if (file_exists($realpath . $bigfilename)) {
            @unlink($realpath . $bigfilename);
        }
        if (file_exists($realpath . $smallfilename)) {
            @unlink($realpath . $smallfilename);
        }

        $this->_upfile['bigfilename'] = $this->_path . $bigfilename;
        $this->_upfile['smallfilename'] = $this->_path . $smallfilename;

    //debug_log($realpath);
    //debug_log($this->_upfile['bigfilename']);
    }

    /**
     * setting upload options
     *
     * @param array $options
     * @return void
     */
    protected function _setOptions($options = array())
    {
        if (is_array($options)) {
            $allowOptions = array('baseFolder', 'path', 'id', 'width', 'height', 'imageName', 'isDelSrcImg', 'isCopyImg');

            foreach ($options as $key => $value) {
                if (in_array($key, $allowOptions)) {
                    $this->_set($key, $value);
                }
            }
        }
    }

    /**
     * set function
     *
     * @param string $key
     * @param object $value
     * @return void
     */
    protected function _set($key, $value)
    {
        $name = '_' . $key;

        $this->$name = $value;
    }

    /**
     * make file path
     *
     * @param string $path
     * @param int $mode
     * @return boolean
     */
    protected function _mkPath($path, $mode = 0755)
    {
        return @mkdir($path, $mode, true);
    }

    /**
     * create thumbnail image file
     *
     * @return void
     */
    protected function _createThumbnail()
    {
        require_once 'MyLib/Image/Edit.php';
        $imageEditor = new MyLib_Image_Edit();

        $big = $this->_baseFolder . '/' . $this->_upfile['bigfilename'];
        $small = $this->_baseFolder . '/' . $this->_upfile['smallfilename'];
        $imageEditor->resize($this->_upfile['tmp_name'], $big, 180, 180);
        $imageEditor->resize($this->_upfile['tmp_name'], $small, 76, 76);
    }

}
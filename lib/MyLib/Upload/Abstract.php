<?php

/**
 * File upload abstact
 *
 * @abstract
 * @package    MyLib_Upload
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2008/07/18     Hulj
 */
abstract class MyLib_Upload_Abstract
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
     * upload allowed type
     *
     * @var array
     */
    protected $_allowType = array();

    /**
     * upload file max size
     *
     * @var int
     */
    protected $_maxSize= 1024;

    /**
     * overwrite or not if upload file name exists
     *
     * @var boolean
     */
    protected $_overwrite = false;

    /**
     * upload file information
     *
     * @var array
     */
    protected $_upfile = array();

    /**
     * upload file extension name
     *
     * @var string
     */
    protected $_ext = '';

    /**
     * error message
     *
     * @var string
     */
    protected $_errorMsg = '';

    /**
     * error mode
     * 0 : throws exception
     * 1 : output error message
     * @var int
     */
    protected $_errorMode = 0;

    /**
     * upload file
     *
     * @param string $field
     * @param array $options
     * @return int
     */
    public function upfile($field, $options = array())
    {
        //setting options
        if (!empty($options)) {
            $this->_setOptions($options);
        }

        $this->_setUpfile($field);

        //check file
        $result = $this->_check();

        if ($result != 1) {
            return $result;
        }

        //set upload file name
        $this->_setUploadFilename();

        if (@move_uploaded_file($this->_upfile['tmp_name'], $this->_baseFolder . '/' . $this->_upfile['filename'])) {
            $this->_afterUpload();
            return 1;
        }

        return -1;
    }

    /**
     * get upload file information
     *
     * @return array
     */
    public function getUpfile()
    {
        return $this->_upfile;
    }

    /**
     * after upload successed, do this
     * can override
     *
     * @return void
     */
    protected function _afterUpload()
    {

    }

    /**
     * get error message
     *
     * @return string
     */
    public function getError()
    {
        return $this->_errorMsg;
    }

    /**
     * set upload file name
     *
     * @return void
     */
    private function _setUploadFilename()
    {
        $realpath = $this->_baseFolder . '/' . $this->_path;

        if (!file_exists($realpath)) {
            $this->_mkpath($realpath);
        }

        if ($realpath[strlen($realpath)-1]!='/') {
            $realpath .= '/';
        }

        $ext = $this->_ext;

        if (empty($ext)) {
            $ext = $this->_getExtension($this->_upfile['name']);
        }
        //$filename =$this->_id . '_' . uniqid(time()).'.'.$ext;

        $this->_upfile['create_time'] = time();
        $filename = $this->_id . '_' . $this->_upfile['create_time'];
        $bigfilename = $filename . '_b.' . $ext;
        $smallfilename = $filename . '_s.' . $ext;
        $filename .= '.' . $ext;

        //if not overwrite mode
        if (!$this->_overwrite) {
            $i = 1;
            $j = 1;
            $k = 1;
            while (file_exists($realpath . $filename)) {
                $filename = $i . '_' . $filename;
                $i++;
            }

            while (file_exists($realpath . $bigfilename)) {
                $bigfilename = $j . '_' . $bigfilename;
                $j++;
            }

            while (file_exists($realpath . $smallfilename)) {
                $smallfilename = $k . '_' . $smallfilename;
                $k++;
            }
        }
        //overwrite mode
        else{
            if (file_exists($realpath . $filename)) {
                @unlink($realpath . $filename);
            }
            if (file_exists($realpath . $bigfilename)) {
                @unlink($realpath . $bigfilename);
            }
            if (file_exists($realpath . $smallfilename)) {
                @unlink($realpath . $smallfilename);
            }
        }

        $this->_upfile['filename'] = $this->_path . $filename;
        $this->_upfile['bigfilename'] = $this->_path . $bigfilename;
        $this->_upfile['smallfilename'] = $this->_path . $smallfilename;
    }

    /**
     * check file
     *
     * @return int(1 : OK, -2 : virus error, -3 : real format error, -4 : extension error, -5 : size error)
     */
    function _check()
    {
        //scan virus
        if (!$this->_scanVirus()) {
            return -2;
        }

        //check type
        if (!$this->_checkExtension()) {
            return -4;
        }

        //check file format
        if (!$this->_checkFormat()) {
            return -3;
        }

        //check size
        if (!$this->_checkSize()) {
            return -5;
        }

        return 1;
    }

    /**
     * set upload options
     *
     * @param array $options
     * @return void
     */
    private function _setOptions($options)
    {
       if (is_array($options)) {
           $allowOptions = array('baseFolder', 'path', 'id', 'allowType', 'maxSize', 'overwrite', 'errorMode');

           foreach ($options as $key => $value) {
               if (in_array($key, $allowOptions)) {
                   $this->_set($key, $value);
               }
           }
        }
    }

    /**
     * call upload options
     *
     * @param array $options
     * @return void
     */
    public function callSetOptions($options)
    {
        $this->_setOptions($options);
    }

    /**
     * check file extension
     *
     * @return boolean
     */
    protected function _checkExtension()
    {
        $this->_getExtension($this->_upfile['name']);

        if (!in_array($this->_ext, $this->_allowType)) {
            return false;
        }

        return true;
    }

    /**
     * get file extension name
     *
     * @param string $file
     * @return string
     */
    protected function _getExtension($file)
    {
        $ext = explode('.', $file);
        $ext = $ext[count($ext) - 1];
        $this->_ext = strtolower($ext);

        return $this->_ext;
    }

    /**
     * check file size
     *
     * @return boolean
     */
    function _checkSize()
    {
        if ($this->_upfile['size'] > $this->_maxSize * 1024) {
            return false;
        }

        return true;
    }

    /**
     * set upfile
     *
     * @param string $field
     * @return void
     */
    private function _setUpfile($field)
    {
        if (empty($_FILES[$field]) || $_FILES[$field]['size'] == 0) {
            $this->_error(__FUNCTION__ . '(): upload file not exists!');
        }

        $this->_upfile = $_FILES[$field];
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
    protected function _mkpath($path, $mode = 0777)
    {
        return @mkdir($path, $mode, true);
    }

    /**
     * get format size
     *
     * @param int $size
     * @return string
     */
    public function getFormatSize($size)
    {
        if ($size < 1024) {
            return $size . 'B';
        }
        else if ($size < 1024 * 1024) {
            return number_format((double)($size/1024),2) . 'KB';
        }
        else {
            return number_format((double)($size/(1024*1024)), 2) . 'MB';
        }
    }

    /**
     * create error or throws exception
     *
     * @param string $msg
     * @return void or throws exception
     */
    protected function _error($msg)
    {
        if ($this->_errorMode == 0) {
            throw new Exception($msg);
        }

        $this->_errorMsg .= 'ERROR : file ' . __FILE__ . ' function ' . $msg . "\r\n";
    }

    /**
     * scan virus
     *
     * @return boolean
     */
    protected function _scanVirus()
    {
        $path = $this->_upfile['tmp_name'];

        if (ENABLE_ANTIVIRUS) {
            $str = exec(ANTIVIRUS_DIR . '/fsav ' . $path, $output, $retval);
            $str = exec(ANTIVIRUS_DIR . '/fsav ' . $path, $output, $retval);
            return $retval != 3;
        }
        else{
            return true;
        }
    }

    /**
     * check file format
     *
     * @return boolean
     */
    protected function _checkFormat()
    {
        $path = $this->_upfile['tmp_name'];

        $file = fopen($path, "rb");
        //only need read 2 bytes
        $bin = fread($file, 2);
        fclose($file);

        $strInfo  = @unpack('c2chars', $bin);
        $typeCode = intval($strInfo['chars1'] . $strInfo['chars2']);

        /* type code
         * -48  office2003
         * 3780 pdf
         * 8075 office2007
         */

        return $typeCode == -48 || $typeCode == 3780 || $typeCode == 8075;
    }
}
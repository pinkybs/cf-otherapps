<?php

/** @see Zend_Validate_Abstract */
require_once 'Zend/Validate/Abstract.php';

/**
 * Check image format is a correct or error
 * 
 * @package    MyLib_Image
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2008/07/18     Hulj
 */
class MyLib_Image_Validate extends Zend_Validate_Abstract
{
    const NOT_EXISTS = 'notExists';                     // image not exists
    const SIZE_TOO_BIG = 'sizeTooBig';                  // image size is too big
    const EXTENSION_NOT_ALLOW  = 'extensionNotAllow';   // image extension is not allow
    const FORMAT_ERROR = 'formatError';                 // image format is error
    
    /**
     * default message(en)
     * 
     * @var array
     */
    protected $_messageTemplates = array(
        self::NOT_EXISTS  => "image not exists",
        self::SIZE_TOO_BIG  => "image size is too big",
        self::EXTENSION_NOT_ALLOW  => "image extension is not allow",
        self::FORMAT_ERROR  => "image format is error"
    );
    
    /**
     * allowed image extension
     *
     * @var array|null
     */
    protected $_allowExtension = null;
    
    /**
     * allowed image size
     *
     * @var int
     */
    protected $_allowSize = 0;
    
    /**
     * error messages to return
     *
     * @var array
     */
    protected $_messages = array();
    
    /**
     * construct
     *
     * @param array $options
     */
    public function __construct($options = array())
    {
        if (isset($options['allow_extension'])) {
            $this->_allowExtension = $options['allow_extension'];
        }
        else {
            $this->_allowExtension = array('jpg', 'jpeg', 'gif', 'bmp', 'png');
        }
        
        if (isset($options['allow_size'])) {
            $this->_allowSize = $options['allow_size'];
        }
        else {
            $this->_allowSize = 5242880;    //5M
        }
    }
    
    /**
     * check image
     *
     * @param string $imagefile
     * @param string $truename
     * @return boolean
     */
    public function isValid($imagefile, $truename = null)
    {        
        if (empty($imagefile) ||  !file_exists($imagefile)) {
            $this->_error(self::NOT_EXISTS);
            
            return false;            
        }
        
        if (!$this->_checkSize($imagefile)) {
            $this->_error(self::SIZE_TOO_BIG);
            
            return false;
        }
        
        if ($truename !== null) {
            $name = $truename;
        }
        else {
            $name = $imagefile;
        }
        
        if (!$this->_checkExtension($name)) {
            $this->_error(self::EXTENSION_NOT_ALLOW);
            
            return false;
        }
        
        if (!$this->_checkFormat($imagefile)) {
            $this->_error(self::FORMAT_ERROR);
            
            return false;
        }

        return true;    
    }
    
    /**
     * check iamge size
     *
     * @param string $imagefile
     * @return boolean
     */
    protected function _checkSize($imagefile)
    {
        $size = filesize($imagefile);
        
        return $size <= $this->_allowSize;
    }
    
    /**
     * check iamge extension
     *
     * @param string $imagefile
     * @return boolean
     */
    protected function _checkExtension($imagefile)
    {
        $parts = pathinfo($imagefile);
        $extension = strtolower($parts['extension']);
        
        return in_array($extension, $this->_allowExtension);
    }
    
    /**
     * check image format
     *
     * @param string $imagefile
     * @return boolean
     */
    protected function _checkFormat($imagefile)
    {
        list($width, $height, $type, $attr) = @getimagesize($imagefile);

        $format = false;
        switch ($type) {
            case IMAGETYPE_GIF:
                if (@imagecreatefromgif($imagefile)) $format = 'gif';
                break;
            
            case IMAGETYPE_JPEG:
                if (@imagecreatefromjpeg($imagefile)) $format = 'jpg';
                break;
                
            case IMAGETYPE_PNG:
                if (@imagecreatefrompng($imagefile)) $format = 'png';
                break;
                
            case IMAGETYPE_BMP:
                require_once('MyLib/Image/bmp.php');
                if (@imagecreatefrombmp($imagefile)) $format = 'bmp';
                break;
                
            default:
                break;
        }
    
        return $format;
    }

}
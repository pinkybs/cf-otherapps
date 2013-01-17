<?php

/**
 * Thunbnail Preview Buffer
 *
 * @package    MyLib_Upload
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2008/07/18     Hulj
 */
class MyLib_Upload_ThumbPreviewBuffer
{

    /**
     * upload image file
     *
     * @param array $field
     * @param integer $resizeWidth
     * @param integer $resizeHeight
     * @return array|false
     */
    public function upfile($field, $resizeWidth = 170, $resizeHeight = 170)
    {
        //get image file
        $this->upfile = $_FILES[$field];

        try {
            $img = $this->_createThumbnail($resizeWidth, $resizeHeight);
            @unlink($this->upfile['tmp_name']);
            return $img;
        }
        catch (Exception $e) {
        }

        return false;
    }

    /**
     * create thumbnail image
     *
     * @param integer $resizeWidth
     * @param integer $resizeHeight
     * @return array
     */
    protected function _createThumbnail($resizeWidth, $resizeHeight)
    {
        require_once 'MyLib/Image/Edit.php';
        $edit = new MyLib_Image_Edit();

        ob_start();
        $edit->resize($this->upfile['tmp_name'], null, $resizeWidth, $resizeHeight);
        $data = ob_get_contents();
        $length = ob_get_length();
        ob_end_clean();

        return array('data' => $data, 'length' => $length);
    }

}
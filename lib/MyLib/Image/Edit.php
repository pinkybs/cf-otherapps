<?php

/**
 * Image Edit
 *
 * @package    MyLib_Image
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2008/07/18     Hulj
 */
class MyLib_Image_Edit
{
    /**
     * create a new resize image
     *
     * @param string $from
     * @param string $to
     * @param int $toW
     * @param int $toH
     * @param boolean $autoSize
     * @return void
     * @throws Exception
     */
    public function resize($from, $to, $toW = 170, $toH = 170, $autoSize = true)
    {
        $data = @getimagesize($from);

        switch ($data[2]) {
            case IMAGETYPE_GIF:     //1, gif image
                $im = imagecreatefromgif($from);
                break;

            case IMAGETYPE_JPEG:    //2, jpg image
                $im = imagecreatefromjpeg($from);
                break;

            case IMAGETYPE_PNG:     //3, png image
                $im = imagecreatefrompng($from);
                break;

            case IMAGETYPE_BMP:     //6, bmp image
                require_once('MyLib/Image/bmp.php');
                $im = imagecreatefrombmp($from);
                break;
        }

        $srcW = imagesx($im);
        $srcH = imagesy($im);

        $srcWH = $srcW / $srcH;
        $borderW = $toW;
        $borderH = $toH;

        if ($srcW < $toW && $srcH < $toH) {
            $toW = $srcW;
            $toH = $srcH;
        }

        if ($autoSize) {
            $toWH = $srcWH;
            if ($srcW > $srcH) {
                $ftoW = $toW;
                $ftoH = $toW / $toWH;
            }
            else {
                $ftoW = $toH * $toWH;
                $ftoH = $toH;
            }
        }
        else {
            $toWH = $toW/$toH;
            if ($toWH <= $srcWH) {
                $ftoW = $toW;
                $ftoH = $ftoW * ($srcH / $srcW);
            }
            else {
                $ftoH = $toH;
                $ftoW = $ftoH * ($srcW / $srcH);
            }
        }

        $ftox = ($ftoW < $borderW) ? ($borderW - $ftoW) / 2 : 0;
        $ftoy = ($ftoH < $borderH) ? ($borderH - $ftoH) / 2 : 0;

        if (function_exists('imagecreatetruecolor') && $data[2] != 1) {
            @$ni = imagecreatetruecolor($borderW, $borderH);
            if ($ni) {
                $white = imagecolorallocate($ni, 255, 255, 255);
                imagefilledrectangle($ni, 0, 0, $borderW, $borderH, $white);
                imagecopyresampled($ni, $im, $ftox, $ftoy, 0, 0, $ftoW, $ftoH, $srcW, $srcH);
            }
            else {
                $ni = imagecreate($borderW, $borderH);
                $white = imagecolorallocate($ni, 255, 255, 255);
                imagefilledrectangle($ni, 0, 0, $borderW, $borderH, $white);
                imagecopyresized($ni, $im, $ftox, $ftoy, 0, 0, $ftoW, $ftoH, $srcW, $srcH);
            }
        }
        else {
            $ni = imagecreate($borderW, $borderH);
            $white = imagecolorallocate($ni, 255, 255, 255);
            imagefilledrectangle($ni, 0, 0, $borderW, $borderH, $white);
            imagecopyresized($ni, $im, $ftox, $ftoy, 0, 0, $ftoW, $ftoH, $srcW, $srcH);
        }

        if (function_exists('imagejpeg')) {
            imagejpeg($ni, $to);
        }
        else {
            throw new Exception('function imagejped not exists!');
        }

        imagedestroy($ni);
        imagedestroy($im);
    }

}
<?php

/**
 * extends php functions, global functions
 * it has no these function for bmp in php
 * 
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2008/07/18     Hulj
 */


/**
 * Create a new image from the bmp stream in the string
 * like imagecreatefromstring()
 * @param string $image
 * @return resource
 */
function imagecreatefrombmpstring($image)
{
     if (strlen($image) < 54) {
         return false;
     }
         
     $file_header = unpack('sbfType/LbfSize/sbfReserved1/sbfReserved2/LbfOffBits', substr($image, 0, 14));
     
     if ($file_header['bfType'] != 19778) {
         return false;
     }
         
     $info_header = unpack('LbiSize/lbiWidth/lbiHeight/sbiPlanes/sbiBitCountLbiCompression/LbiSizeImage/lbiXPelsPerMeter/lbiYPelsPerMeter/LbiClrUsed/LbiClrImportant', substr($image, 14, 40));    
     
     if ($info_header['biBitCountLbiCompression'] == 2) {
         return false;
     }

     $line_len = round($info_header['biWidth'] * $info_header['biBitCountLbiCompression'] / 8);
     $x = $line_len % 4;   
     if ($x > 0) {
         $line_len += 4 - $x;
     }
        
     $img = imagecreatetruecolor($info_header['biWidth'], $info_header['biHeight']);
     
     switch ($info_header['biBitCountLbiCompression']) {
         case 4:   
             $colorset = unpack('L*', substr($image, 54, 64));
             
             for ($y = 0; $y < $info_header['biHeight']; $y++) {   
                 $colors = array();
                 $y_pos = $y * $line_len + $file_header['bfOffBits'];
                 
                 for ($x = 0; $x < $info_header['biWidth']; $x++) {   
                     if ($x % 2) {
                         $colors[] = $colorset[(ord($image[$y_pos + ($x + 1) / 2]) & 0xf) + 1];
                     } else {
                         $colors[] = $colorset[((ord($image[$y_pos + $x / 2 + 1]) >> 4) & 0xf) + 1];
                     }
                 }
                 
                 imagesetstyle($img, $colors);   
                 imageline($img, 0, $info_header['biHeight'] - $y - 1, $info_header['biWidth'], $info_header['biHeight'] - $y - 1, IMG_COLOR_STYLED);   
             }
                
             break;
                
         case 8:   
             $colorset = unpack('L*', substr($image, 54, 1024));
             
             for ($y = 0; $y < $info_header['biHeight']; $y++) {
                 $colors = array();   
                 $y_pos = $y * $line_len + $file_header['bfOffBits'];
                 
                 for ($x = 0; $x < $info_header['biWidth']; $x++) {
                     $colors[] = $colorset[ord($image[$y_pos + $x]) + 1];
                 }
                  
                 imagesetstyle($img, $colors);   
                 imageline($img, 0, $info_header['biHeight'] - $y - 1, $info_header['biWidth'], $info_header['biHeight'] - $y - 1, IMG_COLOR_STYLED);   
             }
                
             break;
              
         case 16:   
             for ($y = 0; $y < $info_header['biHeight']; $y++) {
                 $colors = array();
                 $y_pos = $y*$line_len + $file_header['bfOffBits'];
                 
                 for ($x = 0; $x < $info_header['biWidth']; $x++) {
                     $i = $x * 2;   
                     $color = ord($image[$y_pos + $i]) | (ord($image[$y_pos + $i + 1]) << 8);
                     $colors[] = imagecolorallocate($img,(($color >> 10) & 0x1f) * 0xff / 0x1f, (($color >> 5) & 0x1f) * 0xff / 0x1f, ($color & 0x1f) * 0xff / 0x1f);   
                 }
                   
                 imagesetstyle($img, $colors);   
                 imageline($img, 0, $info_header['biHeight'] - $y - 1, $info_header['biWidth'], $info_header['biHeight'] - $y - 1, IMG_COLOR_STYLED);   
             }
               
             break;
               
         case 24:   
             for ($y = 0; $y < $info_header['biHeight']; $y++) {
                 $colors = array();
                 $y_pos = $y * $line_len + $file_header['bfOffBits'];
                  
                 for ($x = 0; $x < $info_header['biWidth']; $x++) {
                     $i = $x * 3;   
                     $colors[] = imagecolorallocate($img, ord($image[$y_pos + $i + 2]), ord($image[$y_pos + $i + 1]), ord($image[$y_pos + $i]));   
                 }
                    
                 imagesetstyle($img, $colors);   
                 imageline($img, 0, $info_header['biHeight'] - $y - 1, $info_header['biWidth'], $info_header['biHeight'] - $y - 1, IMG_COLOR_STYLED);   
             }
              
             break;
             
         default:   
             for ($y = 0; $y < $info_header['biHeight']; $y++) {
                 $colors = array();
                 $y_pos = $y * $line_len + $file_header['bfOffBits'];
                 
                 for ($x = 0; $x < $info_header['biWidth']; $x++) {
                     $i = $x * 4;   
                     $colors[] = imagecolorallocate($img, ord($image[$y_pos + $i + 2]), ord($image[$y_pos + $i + 1]), ord($image[$y_pos + $i]));   
                 }
                 
                 imagesetstyle($img, $colors);   
                 imageline($img, 0, $info_header['biHeight'] - $y - 1, $info_header['biWidth'], $info_header['biHeight'] - $y - 1, IMG_COLOR_STYLED);   
             }
             
             break;   
     }
     
     return $img;    
}

/**
 * Create a new image from BMP file or URL
 * like imagecreatefromgif() | imagecreatefromjpeg() | imagecreatefrompng()
 * @param string $filename
 * @return resource
 */
function imagecreatefrombmp($filename)
{
    return imagecreatefrombmpstring(@file_get_contents($filename));
}  

/**
 * Output BMP image to browser or file
 * like imagegif() | imagejpeg() | imagepng()
 * @param resource $im
 * @param string $filename
 * @return boolean
 */
function imagebmp($im, $filename = false)
{
    if (!$im) {
        return false;
    }
            
    if ($filename === false) {
        $filename = 'php://output';
    }
    
    $f = fopen ($filename, 'w');
    if (!$f) {
        return false;
    }
            
    //Image dimensions
    $biWidth = imagesx ($im);
    $biHeight = imagesy ($im);
    $biBPLine = $biWidth * 3;
    $biStride = ($biBPLine + 3) & ~3;
    $biSizeImage = $biStride * $biHeight;
    $bfOffBits = 54;
    $bfSize = $bfOffBits + $biSizeImage;
            
    //BITMAPFILEHEADER
    fwrite ($f, 'BM', 2);
    fwrite ($f, pack ('VvvV', $bfSize, 0, 0, $bfOffBits));
            
    //BITMAPINFO (BITMAPINFOHEADER)
    fwrite ($f, pack ('VVVvvVVVVVV', 40, $biWidth, $biHeight, 1, 24, 0, $biSizeImage, 0, 0, 0, 0));
            
    $numpad = $biStride - $biBPLine;
    for ($y = $biHeight - 1; $y >= 0; --$y) {
        for ($x = 0; $x < $biWidth; ++$x) {
            $col = imagecolorat ($im, $x, $y);
            fwrite ($f, pack ('V', $col), 3);
        }
        for ($i = 0; $i < $numpad; ++$i) {
            fwrite ($f, pack ('C', 0));
        }
    }
    fclose ($f);
    
    return true;
}
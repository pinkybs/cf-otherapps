<?php

require_once 'Mbll/Abstract.php';

/**
 * parking flash logic's Operation
 *
 * @package    Mbll
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/05/19    Huch
 */
class Mbll_Ship_Image extends Mbll_Abstract
{
    /**
     * get top image
     *
     * @param integer $uid
     */
    public static function getTopImage($uid, $pid, $ua)
    {
        $topImagePath = '/home/hu1/website/mixi/cf/www/static/apps/ship/img/';
        //$topImagePath = 'F:/app/mixi_app/www/static/apps/ship/img/';
        
        //get user island
        require_once 'Mdal/Ship/Island.php';
        $mdalIsland = Mdal_Ship_Island::getDefaultInstance();
        $island = $mdalIsland->getIslandByUser($pid);
        
        //get parking ship
        require_once 'Mdal/Ship/Ship.php';
        $mdalShip = Mdal_Ship_Ship::getDefaultInstance();
        $shipInfo = $mdalShip->getShipDetailInfo($uid, $pid);
        
        if (count($shipInfo) > 0) {
            $path = $ua==3 ? '-AU' : '';
            $filename = $topImagePath . 'topimage/' . $island['cav_name'] . '-' . $shipInfo[0]['cav_name'] . $path  . '.gif';
            
            if (file_exists($filename)) {
                $image = file_get_contents($filename);
                header('Content-type: image/gif');
                echo $image;
            }
            else {
                if ($ua == 3) {
                    self::makeGifAu($topImagePath, $island['cav_name'], $shipInfo[0]['cav_name']);
                }
                else {
                    self::makeGif($topImagePath, $island['cav_name'], $shipInfo[0]['cav_name']);
                }
            }
        }
        else {
            if (file_exists($topImagePath . 'topimage/' . $island['cav_name'] . '.gif')) {
                $image = file_get_contents($topImagePath . 'topimage/' . $island['cav_name'] . '-' . $shipInfo[0]['cav_name'] . '.gif');
                header('Content-type: image/gif');
                echo $image;
            }
            else {
                $bg = new Imagick($topImagePath . 'background/flash/bg' . $island['cav_name'] . '.jpg');
                $box = new Imagick($topImagePath . 'box/m1.png');
                $bg->compositeImage($box, imagick::COMPOSITE_DEFAULT, 0, 0);
                
                $bg->setImageFormat("gif");
                header('Content-type: image/gif');
                echo $bg;exit;
            }
        }
    }
    
    /**
     * make top gif
     *
     * @param string $topImagePath
     * @param string $island
     * @param string $ship
     */
    public static function makeGif($topImagePath, $islandName, $shipName)
    {
        $bg = new Imagick($topImagePath . 'background/flash/bg' . $islandName . '.jpg');
        $bg->setFormat('gif');
        $box = new Imagick($topImagePath . 'box/m1.png');
        $bg->compositeImage($box, imagick::COMPOSITE_DEFAULT, 0, 0);
        $ship = new Imagick($topImagePath . 'ship/flash/' . $shipName . '.png');
        $bg->compositeImage($ship, imagick::COMPOSITE_DEFAULT, 90, 35); 
        
        $animation = new Imagick();
        $animation->setFormat('gif');        
        $animation->addImage($bg);
        $animation->setImageDelay(50);
        
        for ($i = 2; $i < 8; $i++) {
            $box = new Imagick($topImagePath . 'box/m' . $i . '.png');
            $animation->addImage($box);
            $animation->setImageDelay(30);
        }
        
        $animation->writeImages($topImagePath . "topimage/" . $islandName . '-' . $shipName . '.gif', true);
        
        header('Content-type: image/gif');
        echo $animation->getImagesBlob();
    }
    
    public static function makeGifAu($topImagePath, $islandName, $shipName)
    {
        $bg = new Imagick($topImagePath . 'background/flash/bg' . $islandName . '.jpg');
        $bg->setFormat('gif');
        $box = new Imagick($topImagePath . 'box/m1.png');
        $bg->compositeImage($box, imagick::COMPOSITE_DEFAULT, 0, 0);
        $ship = new Imagick($topImagePath . 'ship/flash/' . $shipName . '.png');
        $bg->compositeImage($ship, imagick::COMPOSITE_DEFAULT, 90, 35);
        
        $animation = new Imagick();
        $animation->setFormat('gif');
        $animation->addImage($bg);
        $animation->setImageDelay(50);
        
        for ($i = 2; $i < 8; $i+=2) {
            $temp = $bg->clone();
            $box = new Imagick($topImagePath . 'box/m' . $i . '.png');
            $temp->compositeImage($box, imagick::COMPOSITE_DEFAULT, 0, 0);
            $animation->addImage($temp);
            $animation->setImageDelay(30);
        }
        
        $path = '-AU';
        $animation->writeImages($topImagePath . "topimage/" . $islandName . '-' . $shipName . $path . '.gif', true);
        
        header('Content-type: image/gif');
        echo $animation->getImagesBlob();
    }
}
<?php
/**
 * Disney image Operation
 *
 * @package    Mbll
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/10/21    hch
 */
class Mbll_Disney_ImageCombine
{
    public static function getLocalMap($aid, $pet=array())
    {       
        $map = new Imagick(self::getAreaImage($aid));        
        
        foreach ($pet as $item) {
            if ( $item['count'] > 0 ) {
                $map = self::combineImg($map, $item['pid']);
            }
        }        
        
        //Softbank/Disney   pnz
        if (Zend_Registry::get('ua') == 2 ) {
            header('Content-type: image/png');
            header("x-jphone-copyright: no-store, no-transfer, no-peripheral");
            header('Content-Disposition: attachment; filename="temp.pnz');
            $map->setImageFormat("png");
        }
        //au       png
        else if (Zend_Registry::get('ua') == 3 ) {
            header('Content-type: image/png'); 
            $map->commentImage('kddi_copyright=on');
            $map->setImageFormat("png");
        }
        //Docomo/other   gif
        else {
            header('Content-type: image/gif');
            $map->commentImage('copy="NO"');
            $map->setImageFormat("gif");
        }
        
        echo $map;
    }
    
    private static function combineImg($map, $pid)
    {
        $staticUrl = Zend_Registry::get('photo');
        $pet = new Imagick($staticUrl . '/img/chara/' . self::getPetImage($pid) . '.png');
        $petLocation = self::getPetMapLocation($pid);
        $map->compositeImage($pet, imagick::COMPOSITE_DEFAULT, $petLocation[0], $petLocation[1]);
        
        return $map;
    }
    
    private static function getPetMapLocation($pid)
    {
        require_once 'Mbll/Disney/Cache.php';
        $pet = Mbll_Disney_Cache::getPlace();
        
        return array($pet[$pid-1]['x'], $pet[$pid-1]['y']);
    }    
    
    private static function getPetImage($pid)
    {
        require_once 'Mbll/Disney/Cache.php';
        $pet = Mbll_Disney_Cache::getPlace();
        
        return $pet[$pid-1]['award_icon'];
    }
    
    private static function getAreaImage($aid)
    {
        $img = "";
        $staticUrl = Zend_Registry::get('photo');        
        
        switch ($aid) {
            case "1":
                $img = $staticUrl . "/img/map/D_W_Min_Map_11_kyusyu.png";
                break;
            case "2":
                $img = $staticUrl . "/img/map/D_W_Min_Map_11_shikoku.png";
                break;
            case "3":
                $img = $staticUrl . "/img/map/D_W_Min_Map_11_chugoku.png";
                break;
            case "4":
                $img = $staticUrl . "/img/map/D_W_Min_Map_11_kansai.png";
                break;
            case "5":
                $img = $staticUrl . "/img/map/D_W_Min_Map_11_toukai.png";
                break;
            case "6":
                $img = $staticUrl . "/img/map/D_W_Min_Map_11_kantou.png";
                break;
            case "7":
                $img = $staticUrl . "/img/map/D_W_Min_Map_11_hokuriku.png";
                break;
            case "8":
                $img = $staticUrl . "/img/map/D_W_Min_Map_11_touhoku.png";
                break;
            default;
                break;
        }
        
        return $img;
    }
}
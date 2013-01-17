<?php

/**
 * common logic's Operation
 *
 * @package    Bll
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2008/07/28    HCH
 */
class Bll_Emoji
{
    
    /**
     * convert emoji to the format like [i/e/s:x]
     *
     * @param string
     * @return string
     */
    public function escapeEmoji($string, $remove = false) {
        require_once 'MyLib/OpenPNE/Util/Emoji.php';
        $ua = Zend_Registry::get('ua_alpha');
        $convertedString = "";
        // convert emoji to the format like [i/e/s:x]
        if ($ua == 'i') {
            // 4 DoCoMo
            $encodingString = mb_convert_encoding($string, "SJIS-win", "UTF-8");
            $convertedString = mb_convert_encoding(emoji_escape($encodingString, $remove), 'UTF-8', 'SJIS-win');
        } else if ($ua == 'e' || $ua == 's') {
            // 4 AU and SoftBank
            $encodingString = mb_convert_encoding($string, "UCS2", "UTF-8");
            $convertedString = mb_convert_encoding(emoji_escape($encodingString, $remove), 'UTF-8', 'unicode');
        } else {
            $convertedString = $string;
        }
        
        return $convertedString;
    }
    
    /**
     * convert the string which's format like [i/e/s:x] to emoji
     *
     * @param string
     * @return string
     */
    function unescapeEmoji($string)
    {
        require_once 'MyLib/OpenPNE/Util/Emoji.php';
        
        $moji_pattern = '/\[([ies]:[0-9]{1,3})\]/';
        
        //get all emoji from the string
        $matches = array();
        preg_match_all($moji_pattern, $string, $matches);
        
        for ($i = 0; $i < count($matches[0]); $i++) {
            $hexEmoji = emoji_convert($matches[0][$i]);
            if ($hexEmoji != '〓') {
                $string = preg_replace($moji_pattern, emoji_unescape($hexEmoji), $string, 1);
            }
            else {
                $string = preg_replace($moji_pattern, $hexEmoji, $string, 1);
            }
        }
        
        return $string;
    }
    
}
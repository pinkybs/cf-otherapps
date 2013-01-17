<?php

/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

/**
 * Smarty emoji_unescape modifier plugin
 *
 * Type:     modifier<br>
 * Name:     emoji_unescape<br>
 * Purpose:  Truncate a string to a certain length if necessary,
 *           optionally splitting in the middle of a word, and
 *           appending the $etc string or inserting $etc into the middle.
 * @link http://smarty.php.net/manual/en/language.modifier.truncate.php
 *          truncate (Smarty online manual)
 * @author   Monte Ohrt <monte at ohrt dot com>
 * @param string
 * @return string
 */

function smarty_modifier_unescape_emoji($string)
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

/* vim: set expandtab: */

?>

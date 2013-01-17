<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty truncate modifier plugin
 *
 * Type:     modifier<br>
 * Name:     truncate<br>
 * Purpose:  Truncate a string to a certain length if necessary,
 *           optionally splitting in the middle of a word, and
 *           appending the $etc string or inserting $etc into the middle.
 * @link http://smarty.php.net/manual/en/language.modifier.truncate.php
 *          truncate (Smarty online manual)
 * @author   Monte Ohrt <monte at ohrt dot com>
 * @param string
 * @param integer
 * @param string
 * @return string
 */

function smarty_modifier_insertsep($string, $len = 30, $sep = '')
{
    if ($len == 0)
        return $string;
    	
    //sep == 1, insert <br>
    if ($sep=='1')
        $sep = "<br>";
        
	$a=0;
	$length = mb_strlen($string, 'utf-8');
	
	for($i=0;$i<$length;$i++){
		if (ord(mb_substr($string,$i,1,'utf-8'))>128)
			$a+=2;
		else
			$a++; 
		
		if($a>=$len)
			return mb_substr($string, 0, $i+1 , 'utf-8') . $sep . mb_substr($string, $i+1, $length, 'utf-8');
	}
	
	return $string;    
}

/* vim: set expandtab: */

?>

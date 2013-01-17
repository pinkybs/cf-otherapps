<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty format url to mixi url
 *
 * Type:     modifier
 * Name:     mixiurl
 * Purpose:  Smarty format url to mixi url for mobile
 * @author   huch
 * @param    string url
 * @return   string mixi url
 */
function smarty_modifier_mixiurl($url)
{
    $joinchar = (stripos($url,'?') === false) ? '?' : '&';
	return '?guid=ON&url=' . urlencode($url . $joinchar . 'rand=' .rand());
}

/* vim: set expandtab: */

?>

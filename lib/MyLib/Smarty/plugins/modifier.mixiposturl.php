<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty format url to mixi post url
 *
 * Type:     modifier
 * Name:     mixiurl
 * Purpose:  Smarty format url to mixi url for mobile
 * @author   huch 
 * @param    string url
 * @return   string mixi url
 */
function smarty_modifier_mixiposturl($url)
{
    $app_id = Zend_Registry::get('opensocial_app_id');
	
    $joinchar = (stripos($url,'?') === false) ? '?' : '&';
    
    return $url . $joinchar . 'app_id=' . $app_id;
}

/* vim: set expandtab: */

?>

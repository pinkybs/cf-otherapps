<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

/**
 * Smarty format url to mixi flash url
 *
 * Type:     modifier
 * Name:     mixiurl
 * Purpose:  Smarty format url to mixi flash url for mobile
 * @author   huch
 * @param    string url
 * @return   string mixi url
 */
function smarty_modifier_mobile_flashurl($url)
{
    require_once 'MyLib/Mobile/Japan/FlashLite.php';
    $flashlite = new MyLib_Mobile_Japan_FlashLite(Zend_Registry::get('ua'));

    if (!$flashlite->isValid()) {
        $url = smarty_modifier_mixiurl(Zend_Registry::get('host') . '/mobile/error/invalidflashlite');
    }

	return $url;
}

/* vim: set expandtab: */

?>

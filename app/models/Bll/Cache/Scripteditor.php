<?php

/** @see Bll_Cache */
require_once 'Bll/Cache.php';

/**
 * Scripteditor Cache
 *
 * @package    Bll/Cache
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/06/02    Liz
*/
class Bll_Cache_Scripteditor
{    
    /**
     * Bll_Scripteditor_Search object
     *
     * @static
     * @var Bll_Scripteditor_Search
     */
    private static $_bllScripteditorSearch = null;
    
    private static $_prefix = 'Bll_Cache_Scripteditor';
    
    public static function getCacheKey($salt, $params = null)
    {
        return Bll_Cache::getCacheKey(self::$_prefix, $salt, $params);
    }

    /**
     * get Class Bll_Scripteditor_Search instance object
     *
     * @static
     * @return Bll_Scripteditor_Search object
     */
    public static function getBllScripteditorSearch()
    {
        if (self::$_bllScripteditorSearch === null) {
            require_once 'Bll/Scripteditor/Search.php';
            self::$_bllScripteditorSearch = new Bll_Scripteditor_Search();
        }
        
        return self::$_bllScripteditorSearch;
    }

    /**
     * get search tag list
     *
     * @return array
     */
    public static function getTagList($number)
    {      
        $key = self::getCacheKey('getTagList', $number);
                
        if (!$result = Bll_Cache::get($key)) {
            $_bllScripteditorSearch = self::getBllScripteditorSearch();
            $result = $_bllScripteditorSearch->getTagList();

            if ($result) {
                //15 minute
                Bll_Cache::set($key, $result, 15*60);
            }
        }
        
        return $result;
    }

    /**
     * clean tag cache
     *
     * @param int $number
     * @return void
     */
    public static function cleanTagList($number)
    {
        Bll_Cache::delete(self::getCacheKey('getTagList', $number));
    }

}
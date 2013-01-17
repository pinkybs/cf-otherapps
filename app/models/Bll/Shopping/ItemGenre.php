<?php

/** @see Bll_Cache */
require_once 'Bll/Cache.php';

/**
 * Mixi App Shopping ItemGenre logic Operation
 *
 * @package    Bll/Shopping
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/08/13    zhangxin
 */
final class Bll_Shopping_ItemGenre
{

	/**
     * list items genre parent
     *
     * @return array
     */
    public static function listItemGenreParent()
    {
        //get from cache
        $cacheKey = Bll_Cache::getCacheKey('Bll_Shopping_ItemGenre', 'listItemGenreParent');
        if (!($aryInfo = Bll_Cache::get($cacheKey))) {
            require_once 'Dal/Shopping/ItemGenre.php';
            $dalGParent = Dal_Shopping_ItemGenre::getDefaultInstance();
            $aryInfo = $dalGParent->listItemGenreParent(1, 1000);

            Bll_Cache::set($cacheKey, $aryInfo, Bll_Cache::LIFE_TIME_ONE_HOUR * 10);
        }

        return $aryInfo;
    }

	/**
     * list items genre child by parent
     *
     * @param Integer $gid
     * @return array
     */
    public static function listItemGenreChildByParent($gid)
    {
        //get from cache
        $cacheKey = Bll_Cache::getCacheKey('Bll_Shopping_ItemGenre', 'listItemGenreChildByParent', $gid);
        if (!($aryInfo = Bll_Cache::get($cacheKey))) {
            require_once 'Dal/Shopping/ItemGenre.php';
            $dalGChild = Dal_Shopping_ItemGenre::getDefaultInstance();
            $aryInfo = $dalGChild->listItemGenreChildByParent($gid, 1, 1000);

            Bll_Cache::set($cacheKey, $aryInfo, Bll_Cache::LIFE_TIME_ONE_HOUR * 10);
        }

        return $aryInfo;
    }
}
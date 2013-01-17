<?php

/** @see Bll_Cache */
require_once 'Bll/Cache.php';

/**
 * Cache_ThumbPreview logic's Operation
 *
 * @package    Bll/Cache
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2008/07/23    HCH
 */
class Bll_Cache_ThumbPreview
{
    /**
     * life time  5 minutes
     *
     */
    const LIFE_TIME = 300;

    /**
     * get ThumbPreview
     *
     * @param string $key
     * @return string
     */
    public static function get($key)
    {
        return Bll_Cache::get($key);
    }

    /**
     * set ThumbPreview
     *
     * @param string $key
     * @param string $value
     */
    public static function set($key, $value)
    {
        Bll_Cache::set($key, $value, self::LIFE_TIME);
    }

    /**
     * clean ThumbPreview
     *
     * @param string $key
     */
    public static function clean($key)
    {
        Bll_Cache::delete($key);
    }
}
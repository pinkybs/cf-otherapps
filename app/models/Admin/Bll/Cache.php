<?php

/** @see Zend_Cache */
require_once 'Zend/Cache.php';

/**
 * cache admin logic's Operation
 * cache get,set,clean,delete logic
 *
 * @package    Admin/Bll
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/03/12    zhangxin
 */
class Admin_Bll_Cache
{
    /**
     * cache class name
     *
     * @var string
     */
    private static $_cacheClassName = 'Admin_Bll_Cache';

    /**
     * object cache
     *
     * @var unknown_type
     */
    private static $_cache = null;

    const LIFE_TIME_ONE_MINUTE = 60;
    const LIFE_TIME_ONE_HOUR = 3600;
    const LIFE_TIME_ONE_DAY = 86400;
    const LIFE_TIME_ONE_WEEK = 604800;
    const LIFE_TIME_ONE_MONTH = 18144000;

    /**
     * get class cache
     *
     * @return $_cache
     */
    public static function getCache()
    {
        if (self::$_cache === null) {
            self::init();
        }

        return self::$_cache;
    }

    /**
     * init cache
     *
     */
    protected static function init()
    {
        if (self::$_cache === null) {
            // set backend(eg. 'File' or 'Sqlite'...)
            $backendName = 'Memcached';

            // set frontend(eg.'Core', 'Output', 'Page'...)
            $frontendName = 'Core';

            // set frontend option
            $frontendOptions = array('automatic_serialization' => true);

            // set backend option
            if (Zend_Registry::isRegistered('MemcacheOptions')) {
                $MemcacheOptions = Zend_Registry::get('MemcacheOptions');
            }
            else {
                $MemcacheOptions = array(
                    'server' => array(
                        'host' => '127.0.0.1',
                        'port' => 11211,
                        'persistent' => true)
                );
            }

            $backendOptions = array(
                'servers' => $MemcacheOptions['server']
            );


            // create cache
            self::$_cache = Zend_Cache::factory($frontendName, $backendName, $frontendOptions, $backendOptions);
        }
    }

    /**
     * get cache value from key
     *
     * @param string $key
     * @return string
     */
    public static function get($key)
    {
        $cache = self::getCache();
        return $cache->load($key);
    }

    /**
     * set cache value by key
     *
     * @param string $key
     * @param string $value
     * @param bool $lifetime
     * @return void
     */
    public static function set($key, $value, $lifetime = false)
    {
        $cache = self::getCache();
        $cache->save($value, $key, array(), $lifetime);
    }

    /**
     * remove cache value by key
     *
     * @param string $key
     * @return void
     */
    public static function delete($key)
    {
        $cache = self::getCache();
        $cache->remove($key);
    }

    /**
     * clean all cache
     * @return void
     */
    public static function clean()
    {
        $cache = self::getCache();
        $cache->clean();
    }

    public static function getCacheKey($prefix, $salt, $params = null)
    {
        $s = $prefix . '_' . $salt . '(';

        if ($params != null) {
            if (is_array($params)) {
                $s .= implode(',', $params);
            }
            else {
                $s .= $params;
            }
        }

        $s .= ')';

        return md5($s);
    }

}
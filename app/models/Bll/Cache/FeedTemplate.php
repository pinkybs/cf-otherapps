<?php

/** @see Bll_Cache */
require_once 'Bll/Cache.php';

/**
 * Feed Template Cache
 *
 * @package    Bll/Cache
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2008/09/23    HLJ
 */
class Bll_Cache_FeedTemplate
{
    private static $_prefix = 'Bll_Cache_FeedTemplate';

    public static function getCacheKey($salt, $params = null)
    {
        return Bll_Cache::getCacheKey(self::$_prefix, $salt, $params);
    }

    /**
     * get feed template info
     *
     * @param integer $app_id application id
     * @param integer $template_id template id
     * @return array
     */
    public static function getInfo($app_id, $template_id)
    {
        $key = self::getCacheKey('getInfo', array($app_id, $template_id));

        if (!$result = Bll_Cache::get($key)) {
            require_once 'Dal/FeedTemplate.php';
            $dalFeedTemplate = Dal_FeedTemplate::getDefaultInstance();
            $result = $dalFeedTemplate->get($app_id, $template_id);

            if ($result) {
                Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_MAX);
            }
        }

        return $result;
    }

    /**
     * clear feed template cache info
     *
     * @param integer $app_id application id
     * @param integer $template_id template id
     */
    public static function clearInfo($app_id, $template_id)
    {
        Bll_Cache::delete(self::getCacheKey('getInfo', array($app_id, $template_id)));
    }
}
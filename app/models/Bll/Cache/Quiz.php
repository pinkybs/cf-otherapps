<?php

/** @see Bll_Cache */
require_once 'Bll/Cache.php';

/**
 * Board Cache
 *
 * @package    Bll/Cache
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/09/22    lp
*/
class Bll_Cache_Quiz
{

    private static $_prefix = 'Bll_Cache_Quiz';

    private static $_mdalQuiz = null;

    public static function getCacheKey($salt, $params = null)
    {
        return Bll_Cache::getCacheKey(self::$_prefix, $salt, $params);
    }

    /**
     * get all quiz answer list get Class Mdal_Quiz_Quiz instance object
     * @return Mdal_Quiz_Quiz object
     */
    public static function getMdalQuiz()
    {
        if (self::$_mdalQuiz=== null) {
           require_once 'Mdal/Quiz/Quiz.php';
            self::$_mdalQuiz = Mdal_Quiz_Quiz::getDefaultInstance();
        }

        return self::$_mdalQuiz;
    }

    /**
     * get all quiz answer list
     * @return array
     */
    public static function getlistQuiz()
    {
    	$key = self::getCacheKey('getlistQuiz');

        if (!$result = Bll_Cache::get($key)) {
            $mdalQuiz = self::getMdalQuiz();
            $result = $mdalQuiz->getlistQuiz();

            if ($result) {
                Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_ONE_WEEK);
            }
        }
        return $result;
    }

    /**
     * get quiz content
     *
     * @param integer $level
     * @param integer $type
     * @return strint
     */
    public static function getQuizContent($level, $type)
    {
        $key = self::getCacheKey('getQuizContent', $level, $type);

        if (!$result = Bll_Cache::get($key)) {

            $mdalQuiz = self::getMdalQuiz();
            $result = $mdalQuiz->getQuizContent($level, $type);

            if ($result) {
                Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_ONE_WEEK);
            }
        }
        return $result;
    }

    public static function clearGetlistQuiz()
    {
        Bll_Cache::delete(self::getCacheKey('getlistQuiz'));
    }
}
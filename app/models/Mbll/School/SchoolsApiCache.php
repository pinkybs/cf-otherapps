<?php

/** @see Bll_Cache */
require_once 'Bll/Cache.php';

/**
 * flash Cache
 *
 * @package    Mbll/School
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create
 */
class Mbll_School_SchoolsApiCache
{
    private static $_prefix = 'Mbll_School_SchoolsApiCache';

    /**
     * get cache key
     *
     * @param string $salt
     * @param mixi $params
     * @return string
     */
    private static function getCacheKey($salt, $params = null)
    {
        return Bll_Cache::getCacheKey(self::$_prefix, $salt, $params);
    }

	/**
     * get item by code from RakutenApi
     *
     * @param integer $uid
     * @param integer $uid
     * @param array $info
     * @return integer $result [0,1,2,3,4]
     */
    public static function checkSchool($uid, $appid, $info)
    {
        $result = 0;
        //get api school info
        $key = self::getCacheKey('getMySchools', $uid);
        if (!$arySchoolInfo = Bll_Cache::get($key)) {
            require_once 'Bll/Restful.php';
            $restful = Bll_Restful::getInstance($uid, $appid);
            $schools = $restful->getClassmates();
            if ($schools == null) {
                return 1;//学校情報登録なし
            }
            $arySchoolInfo = array();
            //only one data
            if ($schools instanceof osapiSchool) {
                $arySchoolInfo[0]['school_code'] = $schools->getToken();
                $arySchoolInfo[0]['school_type'] = $schools->getDivision();
            }
            //collection data
            else {
                $intIdx = 0;
                foreach ($schools->getList() as $objSchool) {
                    $arySchoolInfo[$intIdx]['school_code'] = $objSchool->getToken();
                    $arySchoolInfo[$intIdx]['school_type'] = $objSchool->getDivision();
                    $intIdx ++;
                }
            }
//info_log("reset cache $uid:" . $key, 'school_api');
            Bll_Cache::set($key, $arySchoolInfo, Bll_Cache::LIFE_TIME_ONE_MINUTE*5);
        }

        //check
        if (empty($arySchoolInfo) || count($arySchoolInfo) == 0) {
            return 1;//学校情報登録なし
        }
        if (empty($info['school_code'])) {
            if (1 == count($arySchoolInfo)) {
                require_once 'Mdal/School/User.php';
                $mdalUser = Mdal_School_User::getDefaultInstance();
                $mdalUser->updateUser(array('school_code' => $arySchoolInfo[0]['school_code'], 'school_type'=>$arySchoolInfo[0]['school_type']), $uid);
                if ($arySchoolInfo[0]['school_type'] < '04' || $arySchoolInfo[0]['school_type'] > '07') {
                    return 4;//高校生以下
                }
                return 0;
            }
            else {
                return 2;//複数の学校情報を取得
            }
        }

        if ($info['school_type'] < '04' || $info['school_type'] > '07') {
            return 4;//高校生以下
        }

        //n school
        foreach ($arySchoolInfo as $idx=>$sdata) {
            if ($sdata['school_code'] == $info['school_code']) {
                return 0;
            }
        }
        return 3;//学校情報が不一致
    }

    /**
     * clear cache info
     *
     * @param string $uid
     */
    public static function clearCache($uid)
    {
        Bll_Cache::delete(self::getCacheKey('getMySchools', $uid));
    }
}
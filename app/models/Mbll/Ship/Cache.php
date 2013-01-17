<?php

require_once 'Bll/Cache.php';

class  Mbll_Ship_Cache
{
    private static $_mdalRank = null;

    private static $_prefix = 'Mbll_Ship_Cache';
    
    public static function getCacheKey($salt, $params = null)
    {
        return Bll_Cache::getCacheKey(self::$_prefix, $salt, $params);
    }
    
    /**
     * get mdal rank
     *
     * @return Mdal_Ship_Rank
     */
    public static function getMdalRank()
    {
        if (self::$_mdalRank === null) {
            require_once 'Mdal/Ship/Rank.php';
            self::$_mdalRank = Mdal_Ship_Rank::getDefaultInstance();
        }

        return self::$_mdalRank;
    }

    public static function getAllRankingList($key)
    {
        $key = self::getCacheKey('getAllRankingList' . $key);

        if (!$result = Bll_Cache::get($key)) {

            $mdalRank = self::getMdalRank();
            $result = $mdalRank->getAssetRankAllUser(1, 10);

            if ($result) {
                Bll_Cache::set($key, $result,  Bll_Cache::LIFE_TIME_ONE_DAY );
            }
        }

        return $result;
    }
    
    /**
     * report and stick batch
     *
     * @param integer $uid
     * @param integer $fid
     */
    public static function batchReport($uid, $fid=0)
    {
        require_once 'Mbll/Ship/BatchWork.php';
        $mbllBatch = new Mbll_Ship_BatchWork();
            
        $key1 = self::getCacheKey('reporteduid' . $uid);
        if (!Bll_Cache::get($key1)) {
            $mbllBatch->report($uid);            
            Bll_Cache::set($key1, '1',  Bll_Cache::LIFE_TIME_ONE_MINUTE * 15 );
        }
        
        $key2 = self::getCacheKey('reportedfid' . $fid);
        if (!Bll_Cache::get($key2)) {            
            $mbllBatch->reportBoatShip($fid);            
            Bll_Cache::set($key2, '2',  Bll_Cache::LIFE_TIME_ONE_HOUR);
        }
    }
    
    public static function batchPolice($uid)
    {
        require_once 'Mbll/Ship/BatchWork.php';
        $mbllBatch = new Mbll_Ship_BatchWork();
            
        $key = self::getCacheKey('stickuid' . $uid);
        if (!Bll_Cache::get($key)) {            
            $mbllBatch->stick($uid);           
            Bll_Cache::set($key, '3',  Bll_Cache::LIFE_TIME_ONE_HOUR);
        }
    }
    
}
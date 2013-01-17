<?php

/**
 * Bll BatchWork
 * DB Auto Statistic Batch Work Logic Layer
 *
 * @package    Bll/Johnson
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/11/05    lp
 */
class Bll_Johnson_BatchWork
{
    /**
     * db config
     * @var array
     */
    protected $_config;

    /**
     * db read adapter
     * @var Zend_Db_Abstract
     */
    protected $_rdb;

    /**
     * db write adapter
     * @var Zend_Db_Abstract
     */
    protected $_wdb;

    /**
     * construct
     *
     * @param array $config ( db config )
     */
    public function __construct($dbConfig = null)
    {
        if (is_null($dbConfig)) {
            $dbConfig = getDBConfig();
        }
        $this->_rdb = $dbConfig['readDB'];
        $this->_wdb = $dbConfig['writeDB'];
        $this->_config = $dbConfig;
    }


    public function doBatchUpdateJohnsonRankTemTable()
    {

        try {
            require_once 'Bll/Johnson/Johnson.php';
            $bllJohnson = new Bll_Johnson_Johnson();

            $bllJohnson->refreshRankTempTable();
        }
        catch (Exception $e) {
            info_log('johnson doBatch Error Happened!' . $e->getMessage(), "lp_johnson");
        }
    }
}
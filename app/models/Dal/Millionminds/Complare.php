<?php

require_once 'Dal/Abstract.php';

/**
 * Dal Millionminds Complare
 * MixiApp Millionminds Complare Data Access Layer
 *
 * @package    Dal/Millionminds
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/07/27    Liz
 */
class Dal_Millionminds_Complare extends Dal_Abstract
{
    /**
     * complare result type table name
     *
     * @var string
     */
    protected $table_complare_result_type = 'millionmind_complare_result_type';
    
    /**
     * complare result table name
     *
     * @var string
     */
    protected $table_complare_result = 'millionmind_complare_result';
    
    protected static $_instance;
    
    /**
     * get Dal_Millionminds_Complare default
     *
     * @return Dal_Millionminds_Complare
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        
        return self::$_instance;
    }
    

    /**
     * get complare result
     *
     * @param integer $groupId1
     * @param integer $groupId2
     * @return array
     */
    public function getComplare($groupId1, $groupId2)
    {
        $sql = "SELECT t.title,t.content FROM $this->table_complare_result AS r,$this->table_complare_result_type AS t 
                WHERE r.group_id1=:groupId1 AND r.group_id2=:groupId2 AND r.type=t.id";

        return $this->_rdb->fetchRow($sql,array('groupId1'=>$groupId1, 'groupId2'=>$groupId2));
    }
}
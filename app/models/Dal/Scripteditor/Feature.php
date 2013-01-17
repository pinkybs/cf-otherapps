<?php

require_once 'Dal/Abstract.php';

class Dal_Scripteditor_Feature extends Dal_Abstract
{
    /**
     * user table name
     *
     * @var string
     */
    protected $table_feature = 'scripteditor_feature';
    
    protected static $_instance;
    
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        
        return self::$_instance;
    }
  
    /**
     * insert scripteditor feature
     *
     * @param array $feature
     * @return integer
     */
    public function insertFeature($feature)
    {
        $this->_wdb->insert($this->table_feature, $feature);
        return $this->_wdb->lastInsertId();
    }
    
    /**
     * get feature content by id
     *
     * @param integer $id
     * @return string
     */
    public function getFeature($id)
    {
        $sql = "SELECT content FROM $this->table_feature WHERE id=:id";

        return $this->_rdb->fetchOne($sql,array('id'=>$id));
    }

    /**
     * get feature list by type
     *
     * @param integer $type
     * @return array
     */
    public function getFeatureList($type)
    {
        $sql = "SELECT * FROM $this->table_feature WHERE type=:type";

        return $this->_rdb->fetchAll($sql,array('type'=>$type));
    }
}
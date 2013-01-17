<?php

require_once 'Dal/Abstract.php';

class Dal_Scripteditor_Language extends Dal_Abstract
{
    /**
     * user table name
     *
     * @var string
     */
    protected $table_language = 'scripteditor_language';
    
    protected static $_instance;
    
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        
        return self::$_instance;
    }
  
    /**
     * insert scripteditor language
     *
     * @param array $language
     * @return integer
     */
    public function insertlanguage($language)
    {
        $this->_wdb->insert($this->table_language, $language);
        return $this->_wdb->lastInsertId();
    }
    
    /**
     * get language list
     *
     * @return array
     */
    public function getLanguageList()
    {
        $sql = "SELECT * FROM $this->table_language ";

        return $this->_rdb->fetchAll($sql);
    }

    /**
     * get language info by id
     *
     * @param integer $id
     * @return array
     */
    public function getLanguage($id)
    {
        $sql = "SELECT * FROM $this->table_language WHERE id=:id ";

        return $this->_rdb->fetchRow($sql,array('id'=>$id));
    }
    
    
}
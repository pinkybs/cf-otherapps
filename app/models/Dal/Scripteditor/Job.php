<?php

require_once 'Dal/Abstract.php';

class Dal_Scripteditor_Job extends Dal_Abstract
{
    /**
     * user table name
     *
     * @var string
     */
    protected $table_job = 'scripteditor_job';
    
    protected static $_instance;
    
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        
        return self::$_instance;
    }
  
    /**
     * insert scripteditor job
     *
     * @param array $job
     * @return integer
     */
    public function insertJob($job)
    {
        $this->_wdb->insert($this->table_job, $job);
        return $this->_wdb->lastInsertId();
    }
    
    /**
     * get job content by id
     *
     * @param integer $id
     * @return string
     */
    public function getJob($id)
    {
        $sql = "SELECT content FROM $this->table_job WHERE id=:id";

        return $this->_rdb->fetchOne($sql,array('id'=>$id));
    }

    /**
     * get job list 
     *
     * @return array
     */
    public function getJobList()
    {
        $sql = "SELECT * FROM $this->table_job ";

        return $this->_rdb->fetchAll($sql);
    }
}
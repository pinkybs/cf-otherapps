<?php

require_once 'Dal/Abstract.php';

class Dal_FeedTemplate extends Dal_Abstract
{
    /**
     * user table name
     *
     * @var string
     */
    protected $table_feed_template = 'feed_template';
    
    protected static $_instance;
    
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        
        return self::$_instance;
    }
    
    public function insert($template)
    {
        $template['m_title'] = $template['title'];
        $template['m_body'] = $template['body'];
        
        $sql = "REPLACE INTO $this->table_feed_template "
             . '(app_id, template_id, title, body, m_title, m_body)' 
             . 'VALUES (:app_id, :template_id, :title, :body, :m_title, :m_body)';
             
        return $this->_wdb->query($sql, $template);
    }
    
    public function update($app_id, $template_id, $data)
    {
        if (!$data) {
            return false;
        }
        
        $isTitle = isset($data['title']);
        $isBody = isset($data['body']);
        
        if (!$isTitle && !$isBody) {
            return false;
        }
        
        $sql = "UPDATE $this->table_feed_template SET ";
        
        $params = array(
            'app_id' => $app_id,
            'template_id' => $template_id
        );
        
        if ($isTitle) {
            $sql .= 'title=:title, m_title=:m_title';
            $params['title'] = $data['title'];
            $params['m_title'] = $data['title'];
            if ($isBody) {
                $sql .= ', body=:body, m_body=:m_body';
                $params['body'] = $data['body'];
                $params['m_body'] = $data['body'];
            }
        }
        else {
            $sql .= 'body=:body, m_body=:m_body';
            $params['body'] = $data['body'];
            $params['m_body'] = $data['body'];
        }
        
        $sql .= ' WHERE app_id=:app_id AND template_id=:template_id';
        
        return $this->_wdb->query($sql, $params);
    }
    
    public function get($app_id, $template_id)
    {
        $sql = "SELECT * FROM $this->table_feed_template WHERE app_id=:app_id AND template_id=:template_id";
        $params = array(
            'app_id' => $app_id,
            'template_id' => $template_id
        );
        
        return $this->_rdb->fetchRow($sql, $params);
    }
    
    public function getByAppId($app_id)
    {
        $sql = "SELECT * FROM $this->table_feed_template WHERE app_id=:app_id ORDER BY template_id ASC";
        $params = array(
            'app_id' => $app_id
        );
        
        return $this->_rdb->fetchAll($sql, $params);
    }
    
    public function delete($app_id, $template_id)
    {
        $sql = "DELETE FROM $this->table_feed_template WHERE app_id=:app_id AND template_id=:template_id";
        $params = array(
            'app_id' => $app_id,
            'template_id' => $template_id
        );
        
        return $this->_wdb->query($sql, $params);
    
    }

}
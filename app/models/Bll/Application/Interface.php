<?php

interface Bll_Application_Interface
{
    public function redirect($url, array $options = array());
    
    public function redirect404();
        
    public function getRequest();
    
    public function getData($name);
    
    public function setData($name, $value);
    
    public function getViewerId();
    
    public function getOwnerId();
    
    public function getAppId();
    
    public static function getInstance();
}

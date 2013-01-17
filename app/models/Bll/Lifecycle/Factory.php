<?php

/**
 * @see Bll_Lifecycle_Interface
 */
require_once 'Bll/Lifecycle/Interface.php';

/**
 * application lifecycle event callback implementation factory
 *
 * @copyright  Copyright (c) 2009 Community Factory Inc. (http://communityfactory.com)
 * @create    2009/12/10    HLJ
 */

class Bll_Lifecycle_Factory
{
    /**
     * get implementation by application id
     *
     * @param int $appId
     * @return Bll_Lifecycle_Interface
     */
    public static function getImplByAppId($appId)
    {
        $appName = Bll_Application_Information::getAppName($appId);
        if ($appName) {
            return self::getImplByAppName($appName);
        }
        
        return null;
    }
    
    /**
     * get implementation by application name
     *
     * @param string $appName
     * @return Bll_Lifecycle_Interface
     */
    public static function getImplByAppName($appName)
    {
        $name = ucfirst($appName);
        $impl = null;
        $implFile = 'Bll/Lifecycle/Impl/' . $name . '.php';
        if (file_exists(MODELS_DIR . '/' . $implFile)) {
            require_once $implFile;
            $implClassName = 'Bll_Lifecycle_Impl_' . $name;
            $impl = new $implClassName(); 
        }
        
        return $impl;
    }
}

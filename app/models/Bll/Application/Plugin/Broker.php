<?php

/** Bll_Application_Plugin_Interface */
require_once 'Bll/Application/Plugin/Interface.php';

class Bll_Application_Plugin_Broker implements Bll_Application_Plugin_Interface
{

    /**
     * Array of instance of objects implements Bll_Application_Plugin_Interface
     *
     * @var array
     */
    protected $_plugins = array();


    /**
     * Register a plugin.
     *
     * @param  Bll_Application_Plugin_Interface $plugin
     * @param  int $stackIndex
     * @return Bll_Application_Plugin_Broker
     */
    public function registerPlugin(Bll_Application_Plugin_Interface $plugin, $stackIndex = null)
    {
        if (false !== array_search($plugin, $this->_plugins, true)) {
            throw new Exception('Plugin already registered');
        }

        $stackIndex = (int) $stackIndex;
        
        if ($stackIndex) {
            if (isset($this->_plugins[$stackIndex])) {
                throw new Exception('Plugin with stackIndex "' . $stackIndex . '" already registered');
            }
            $this->_plugins[$stackIndex] = $plugin;
        } else {
            $stackIndex = count($this->_plugins);
            while (isset($this->_plugins[$stackIndex])) {
                ++$stackIndex;
            }
            $this->_plugins[$stackIndex] = $plugin;
        }

        ksort($this->_plugins);

        return $this;
    }

    /**
     * Unregister a plugin.
     *
     * @param string|Bll_Application_Plugin_Interface $plugin Plugin object or class name
     * @return Bll_Application_Plugin_Broker
     */
    public function unregisterPlugin($plugin)
    {
        if ($plugin instanceof Bll_Application_Plugin_Interface) {
            // Given a plugin object, find it in the array
            $key = array_search($plugin, $this->_plugins, true);
            if (false === $key) {
                throw new Exception('Plugin never registered.');
            }
            unset($this->_plugins[$key]);
        } elseif (is_string($plugin)) {
            // Given a plugin class, find all plugins of that class and unset them
            foreach ($this->_plugins as $key => $_plugin) {
                $type = get_class($_plugin);
                if ($plugin == $type) {
                    unset($this->_plugins[$key]);
                }
            }
        }
        return $this;
    }

    /**
     * Is a plugin of a particular class registered?
     *
     * @param  string $class
     * @return bool
     */
    public function hasPlugin($class)
    {
        foreach ($this->_plugins as $plugin) {
            $type = get_class($plugin);
            if ($class == $type) {
                return true;
            }
        }

        return false;
    }

    /**
     * Retrieve a plugin or plugins by class
     *
     * @param  string $class Class name of plugin(s) desired
     * @return false|Bll_Application_Plugin_Interface|array Returns false if none found, plugin if only one found, and array of plugins if multiple plugins of same class found
     */
    public function getPlugin($class)
    {
        $found = array();
        foreach ($this->_plugins as $plugin) {
            $type = get_class($plugin);
            if ($class == $type) {
                $found[] = $plugin;
            }
        }

        switch (count($found)) {
            case 0:
                return false;
            case 1:
                return $found[0];
            default:
                return $found;
        }
    }

    /**
     * Retrieve all plugins
     *
     * @return array
     */
    public function getPlugins()
    {
        return $this->_plugins;
    }

    /**
     * Called after update person info
     *
     * @param  string $uid
     * @return void
     */
    public function postUpdatePerson($uid)
    {
        foreach ($this->_plugins as $plugin) {
            $plugin->postUpdatePerson($uid);
        }
    }
    
    /**
     * Called after update friend info
     *
     * @param  string $fid
     * @return void
     */
    public function postUpdateFriend($fid)
    {
        foreach ($this->_plugins as $plugin) {
            $plugin->postUpdateFriend($fid);
        }
    }
    
    /**
     * Called after update friendship
     *
     * @param  string $uid
     * @param  array $fids
     * @return void
     */
    public function postUpdateFriendship($uid, array $fids)
    {
        foreach ($this->_plugins as $plugin) {
            $plugin->postUpdateFriendship($uid, $fids);
        }
    }
    
    /**
     * update app friendship
     *
     * @param  string $uid
     * @param  array $fids
     * @return void
     */
    public function updateAppFriendship($uid, array $fidsHasApp)
    {
        foreach ($this->_plugins as $plugin) {
            $plugin->updateAppFriendship($uid, $fidsHasApp);
        }
    }

    /**
     * Called after application is running
     *
     * @param  Bll_Application $application
     * @return void
     */
    public function postRun(Bll_Application_Interface $application)
    {
        foreach ($this->_plugins as $plugin) {
            $plugin->postRun($application);
        }
    }
}

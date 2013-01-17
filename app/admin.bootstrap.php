<?php

error_reporting(E_ALL & ~E_NOTICE | E_STRICT);
//error_reporting(E_ALL);

//date_default_timezone_set('Asia/Shanghai');
date_default_timezone_set('Asia/Tokyo');

$starttime = getmicrotime();

// define root dir of the application
define('ROOT_DIR', dirname(dirname(__FILE__)));

require (ROOT_DIR . '/app/config/define.php');
set_include_path(LIB_DIR . PATH_SEPARATOR . MODELS_DIR . PATH_SEPARATOR . get_include_path());

// register autoload class function
require_once 'Zend/Loader.php';
Zend_Loader::registerAutoload();

Zend_Registry::set('StartTime', $starttime);

Zend_Registry::set('db.xml', CONFIG_DIR . '/db.xml');

//setup database
$dbAdapter = buildAdapter();

Zend_Db_Table::setDefaultAdapter($dbAdapter);
Zend_Registry::set('db', $dbAdapter);
$dbConfig = array('readDB' => $dbAdapter, 'writeDB' => $dbAdapter);
Zend_Registry::set('dbConfig', $dbConfig);

require_once 'Zend/Log.php';
require_once 'MyLib/Zend/Log/Writer/Stream.php';

$writer1 = new MyLib_Zend_Log_Writer_Stream(LOG_DIR . '/mixi-admin-error.log');
$logger1 = new Zend_Log($writer1);

$writer2 = new MyLib_Zend_Log_Writer_Stream(LOG_DIR . '/mixi-admin-debug.log');
$logger2 = new Zend_Log($writer2);

Zend_Registry::set('error_logger', $logger1);
Zend_Registry::set('debug_logger', $logger2);

//load configration
require_once 'Bll/Config.php';
$config = Bll_Config::get(CONFIG_DIR . '/mixiadmin-config.xml');

//init view
$smartyParams = array('left_delimiter' => '{%', 'right_delimiter' => '%}',
                      'plugins_dir' => array('plugins', LIB_DIR . '/MyLib/Smarty/plugins'));

$view = new MyLib_Zend_View_Smarty($smartyParams);
$viewRenderer = new Zend_Controller_Action_Helper_ViewRenderer($view);
Zend_Controller_Action_HelperBroker::addHelper($viewRenderer);

$isSecurity = isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on';
$protocol = $isSecurity ? 'https://' : 'http://';
$staticUrl = str_replace('http://', $protocol, $config->server->static);

//setup config data
Zend_Session::setOptions($config->session->toArray());
Zend_Registry::set('host', $config->server->host);
Zend_Registry::set('static', $config->server->static);
Zend_Registry::set('photo', $config->server->photo);
Zend_Registry::set('secret', $config->secret->toArray());
Zend_Registry::set('version', $config->version->toArray());

// setup controller
//$webConfig = Bll_Config::get(CONFIG_DIR . '/web.xml');
//Zend_Registry::set('version', $webConfig->version->toArray());

// setup controller
$controller = Zend_Controller_Front::getInstance();
$controller->addControllerDirectory(MODULES_DIR . '/admin/controllers', 'admin');
$controller->setDefaultModule('admin');
$controller->setParam('prefixDefaultModule', true);
$controller->registerPlugin(new Zend_Controller_Plugin_ErrorHandler(array('module' => 'admin', 'controller' => 'error', 'action' => 'error')));

// setting router params
/*
require_once 'Zend/Controller/Router/Route/Regex.php';
$router = $controller->getRouter();
$router->addConfig($webConfig->rewriterouter->pc, 'routes');
*/

try {
    $controller->dispatch();
}
catch (Exception $e) {
    err_log($e->getMessage());
}

function buildAdapter()
{
    require_once 'Zend/Config/Xml.php';
    require_once 'Bll/Config.php';
    $config = Bll_Config::get(Zend_Registry::get('db.xml'));
    $params = $config->database->db_basic->config->toArray();
    $params['driver_options'] = array(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true);
    $dbAdapter =  Zend_Db::factory($config->database->db_basic->adapter, $params);
    $dbAdapter->query("SET NAMES utf8");

    return $dbAdapter;
}

function err_log($msg)
{
    $logger = Zend_Registry::get('error_logger');

    $logger->log($msg, Zend_Log::ERR);
}

function debug_log($msg)
{
    if (!ENABLE_DEBUG) {
        return;
    }

    $logger = Zend_Registry::get('debug_logger');

    $logger->log($msg, Zend_Log::DEBUG);
}

function info_log($msg, $prefix = 'default')
{
    require_once 'Zend/Log.php';
    $log_name = $prefix . '_logger';
    if (!Zend_Registry::isRegistered($log_name)) {
        require_once 'Zend/Log/Writer/Stream.php';
        $writer = new Zend_Log_Writer_Stream(LOG_DIR . '/' . $log_name . '.log');
        $logger = new Zend_Log($writer);
        Zend_Registry::set($log_name, $logger);
    }
    else {
        $logger = Zend_Registry::get($log_name);
    }

    try {
        $logger->log($msg, Zend_Log::INFO);
    }
    catch (Exception $e) {

    }
}

function getmicrotime()
{
    list($usec, $sec) = explode(' ', microtime());
    return ((float) $usec + (float) $sec);
}

function getexecutetime()
{
    $starttime = Zend_Registry::get('StartTime');
    $stoptime = getmicrotime();

    return round($stoptime - $starttime, 10);
}

<?php
error_reporting(E_ALL & ~E_NOTICE | E_STRICT);
//error_reporting(E_ALL);
//error_reporting(0);

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

//load configration
require_once 'Bll/Config.php';
$config = Bll_Config::get(CONFIG_DIR . '/mixi-config.xml');

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
Zend_Registry::set('MemcacheOptions', $config->cache->memcache->servers->toArray());
Zend_Registry::set('secret', $config->secret->toArray());
Zend_Registry::set('host', $config->server->host);
Zend_Registry::set('static', $staticUrl);
Zend_Registry::set('photo', $config->server->photo);
Zend_Registry::set('photoBasePath', $config->upload->photo->basePath);


// setup controller
$webConfig = Bll_Config::get(CONFIG_DIR . '/web.xml');
Zend_Registry::set('version', $webConfig->version->toArray());

// setting controller params
$controllerFront = Zend_Controller_Front::getInstance();
$modules = $webConfig->module->toArray();

foreach ($modules as $module => $path) {
    $controllerFront->addControllerDirectory(MODULES_DIR . '/' . $path, $module);
}

// setting router params
require_once 'Zend/Controller/Router/Route/Regex.php';
$router = $controllerFront->getRouter();
$router->addConfig($webConfig->rewriterouter->pc, 'routes');

$controllerFront->registerPlugin(new MyLib_Zend_Controller_Plugin_Auth());
$controllerFront->setParam('noErrorHandler', true);
$controllerFront->throwExceptions(false);

set_time_limit(4);

function shutdown_handler()
{
  $last_error = error_get_last();
  
  if ($last_error != null) {
      $error_type = $last_error['type'];
      if ($error_type === E_ERROR || $error_type === E_CORE_ERROR || $error_type === E_COMPILE_ERROR || $error_type === E_USER_ERROR) {
        err_log('[E_ERROR] ' . $_SERVER['REQUEST_URI']);
        global_error_output();
      }
  }
}

register_shutdown_function("shutdown_handler");

function global_exception_handler($exception)
{
    err_log($exception->getMessage());
    global_error_output();
}

set_exception_handler('global_exception_handler');

try {
    $controllerFront->dispatch();
}
catch (Exception $e) {
    err_log($e->getMessage());
    global_error_output();
}

function global_error_output()
{    
    ob_end_clean();
    
    ob_start();
        
    if (Zend_Registry::isRegistered('ua')) {
        $ua = Zend_Registry::get('ua');
        $content = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd"><html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja" dir="ltr"><head>';
        //docomo
        if ($ua == 1) {
            $content .= '<meta http-equiv="Content-Type" content="application/xhtml+xml; charset=Shift_JIS" />';
        } else {
            $content .= '<meta http-equiv="Content-Type" content="application/xhtml+xml; charset=UTF-8" />';
        }
        $content .= '</head><body>ただいまアクセスが集中しています。もうしばらくたってから接続してください。</body></html>';

        header('HTTP/1.1 200 OK');
        //docomo
        if ($ua == 1) {
            header('Content-type: application/xhtml+xml; charset=Shift_JIS');
            $content = mb_convert_encoding($content, 'SJIS-win', 'UTF-8');
        } else {
            header('Content-type: application/xhtml+xml; charset=UTF-8');
        }
        
        echo $content;        
    } else {
        $front = Zend_Controller_Front::getInstance();
        $module = $front->getRequest()->getModuleName();
        if ($module == null) {
            $module = $front->getDefaultModule();
        }
        
        if($module == 'default') {
            header('HTTP/1.1 200 OK');
            header('Content-type: text/html; charset=UTF-8');
            echo '<html><body>ただいまアクセスが集中しています。もうしばらくたってから接続してください。</body></html>';
        } else if($module == 'ajax') {
            header('HTTP/1.1 503 Service Unavailable');
            echo 'ただいまアクセスが集中しています。もうしばらくたってから接続してください。';
        }
        
        //else {
            //default will response 'HTTP/1.1 500 Internal Server Error'
            //header('HTTP/1.1 500 Internal Server Error');
        //}
    }
}


function buildAdapter()
{
    require_once 'Zend/Config/Xml.php';
    require_once 'Bll/Config.php';
    $config = Bll_Config::get(Zend_Registry::get('db.xml'));
    $params = $config->database->db_basic->config->toArray();
    $params['driver_options'] = array(
        PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
        PDO::ATTR_TIMEOUT => 2
    );
    $dbAdapter =  Zend_Db::factory($config->database->db_basic->adapter, $params);
    $dbAdapter->query("SET NAMES utf8");

    return $dbAdapter;
}

function getDBConfig()
{
    if (Zend_Registry::isRegistered('dbConfig')) {
        $dbConfig = Zend_Registry::get('dbConfig');
    }
    else {
        //setup database
        $dbAdapter = buildAdapter();
        
        Zend_Db_Table::setDefaultAdapter($dbAdapter);
        Zend_Registry::set('db', $dbAdapter);
        $dbConfig = array('readDB' => $dbAdapter, 'writeDB' => $dbAdapter);
        Zend_Registry::set('dbConfig', $dbConfig);
    }
    
    return $dbConfig;
}

function err_log($msg)
{
    $log_name = 'error_logger';
    if (!Zend_Registry::isRegistered($log_name)) {
        //$writer = new MyLib_Zend_Log_Writer_Stream(LOG_DIR . '/' . $log_name . '.log');
        $writer = new Zend_Log_Writer_Stream(LOG_DIR . '/' . $log_name . '.log');
        $logger = new Zend_Log($writer);
        Zend_Registry::set($log_name, $logger);
    }
    else {
        $logger = Zend_Registry::get($log_name);
    }
    
    try {
        $logger->log($msg, Zend_Log::ERR);
    }
    catch (Exception $e) {

    }
}

function debug_log($msg)
{
    if (!(defined('ENABLE_DEBUG') && ENABLE_DEBUG)) {
        return;
    }
    
    $log_name = 'debug_logger';
    if (!Zend_Registry::isRegistered($log_name)) {
        $writer = new MyLib_Zend_Log_Writer_Stream(LOG_DIR . '/' . $log_name . '.log');
        $logger = new Zend_Log($writer);
        Zend_Registry::set($log_name, $logger);
    }
    else {
        $logger = Zend_Registry::get($log_name);
    }
    
    try {
        $logger->log($msg, Zend_Log::DEBUG);
    }
    catch (Exception $e) {

    }
}

function info_log($msg, $prefix = 'default')
{
    $log_name = $prefix . '_logger';
    if (!Zend_Registry::isRegistered($log_name)) {
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

<?php
require_once CONFIG_DIR . DIRECTORY_SEPARATOR . 'scripteditor-config.php';

class Bll_Scripteditor_Run
{
    public static function createPHPFile($script)
    {
        $d = date('Ymd');
        $dir = TEMP_PHP_DIR . DIRECTORY_SEPARATOR . $d;
        @mkdir($dir, 0777, true);
        
        require_once 'Bll/Secret.php';
        $name = Bll_Secret::getUUID() . '.php';
        $file = $dir . DIRECTORY_SEPARATOR . $name;
        
        @$fp = fopen($file, 'w');
        
        if (!$fp) {
            return null;
        }
        
        @fwrite($fp, $script);
        
        @fclose($fp);
        
        return $d . '/' . $name;
    }
    
    public static function createHTMLFile($html)
    {
        $d = date('Ymd');
        $dir = TEMP_HTML_DIR . DIRECTORY_SEPARATOR . $d;
        @mkdir($dir, 0777, true);
        
        require_once 'Bll/Secret.php';
        $name = Bll_Secret::getUUID() . '.html';
        $file = $dir . DIRECTORY_SEPARATOR . $name;
        
        @$fp = fopen($file, 'w');
        
        if (!$fp) {
            return null;
        }
        
        @fwrite($fp, $html);
        
        @fclose($fp);
        
        return $d . '/' . $name;
    }
    
    public static function php($file)
    {
        $output = '';
        
        if (!$file) {
            $output = 'HTTP 500 Internal Error.';
        }
        else {
            $cmd = BIN_PHP 
                 . ' -c ' . CONFIG_DIR . DIRECTORY_SEPARATOR . 'security-php.ini ' 
                 . ' -f ' . $file . ' -d max_execution_time=' . MAX_EXECUTION_TIME;
                 
            ob_end_clean();
            $size = 0;
            $output = '';
            
            //ob_start();
            
            //$result = passthru($cmd);
            //$output = ob_get_contents();
            //$starttime = time();
            //$nowtime = $starttime;
            $handler = @popen($cmd, 'r');
            if ($handler) {
                while ($size <= MAX_OUTPUT_SIZE) {
                    $data = @fread($handler, 8192);
                    $len = strlen($data);
                    if ($len == 0) {
                        break;
                    }
                    $output .= $data;
                    $size += $len;
                }
                
                @pclose($handler);
                
                if ($size > MAX_OUTPUT_SIZE) {
                    $output .= '......';
                }
            }
            
            //ob_end_clean();
            
            @unlink($file);
            
            $output = str_replace($file, 'your php script', $output);
        }
        
        return $output;
    }
    
    public static function phpScript($script)
    {
        $file = self::createPHPFile($script);
        
        return self::php(TEMP_PHP_DIR . DIRECTORY_SEPARATOR . $file);
    }
    
    public static function phpToHTML($file)
    {       
        return self::createHTMLFile(self::php($file));
    }
    
    
}
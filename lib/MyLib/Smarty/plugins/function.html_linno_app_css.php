<?php
function smarty_function_html_linno_app_css($params, &$smarty)
{
    $version = Zend_Registry::get('version');
    $staticUrl = Zend_Registry::get('static');
	$type = $params['type'];	
	
	if ($type == 'parking') {
	    //$output = '<link rel="stylesheet" href="'.$staticUrl.'/apps/parking/css/import.css?'.$version['css'].'" type="text/css" media="print,screen" />';
	    $output = '<link rel="stylesheet" href="'.$staticUrl.'/apps/parking/css/base.css?'.$version['css'].'" type="text/css" media="print,screen" />'
	    		. '<link rel="stylesheet" href="'.$staticUrl.'/apps/parking/css/main.css?'.$version['css'].'" type="text/css" media="print,screen" />'
	    		. '<link rel="stylesheet" href="'.$staticUrl.'/apps/parking/css/debug.css?'.$version['css'].'" type="text/css" media="print,screen" />'; 
	}
	else if ($type == 'parkinghelp') {
	    $output = '<link rel="stylesheet" href="'.$staticUrl.'/apps/parking/css/base.css?'.$version['css'].'" type="text/css" media="print,screen" />'
	    		. '<link rel="stylesheet" href="'.$staticUrl.'/apps/parking/css/main.css?'.$version['css'].'" type="text/css" media="print,screen" />'
	    		. '<link rel="stylesheet" href="'.$staticUrl.'/apps/parking/css/debug.css?'.$version['css'].'" type="text/css" media="print,screen" />'
		        . '<link rel="stylesheet" href="'.$staticUrl.'/apps/parking/css/help.css?'.$version['css'].'" type="text/css" media="print,screen" />'; 
	}
	else if ($type == 'scripteditor') {
	    $output = '<link rel="stylesheet" href="'.$staticUrl.'/apps/scripteditor/css/base.css?'.$version['css'].'" type="text/css" media="print,screen" />'
                . '<link rel="stylesheet" href="'.$staticUrl.'/apps/scripteditor/css/main.css?'.$version['css'].'" type="text/css" media="print,screen" />'
                . '<link rel="stylesheet" href="'.$staticUrl.'/apps/scripteditor/css/debug.css?'.$version['css'].'" type="text/css" media="print,screen" />'; 
	}
	else if ($type == 'board') {
	    $output = '<link rel="stylesheet" href="'.$staticUrl.'/apps/board/css/import.css?'.$version['css'].'" type="text/css" media="print,screen" />';
	}
	else if ($type == 'linno') {
	    $output = '<link rel="stylesheet" href="'.$staticUrl.'/apps/linno/css/import.css?'.$version['css'].'" type="text/css" media="print,screen" />'; 
	}
    else if ($type == 'dynamite') {
        $output = '<link rel="stylesheet" href="'.$staticUrl.'/apps/dynamite/css/import.css?'.$version['css'].'" type="text/css" media="print,screen" />'; 
    }	
	else {
        return '';
    }   
              
    return $output;
}

?>

<?php

function smarty_function_html_mobile_tracker($params, &$smarty)
{

    $aqid = $params['aqid'];//"L6FHKGWD4A6SJRBEXVQA";
	$ref = getenv('HTTP_REFERER');
	$ref = urlencode($ref);
	//$my_url = urlencode("//".getenv('SERVER_NAME').getenv('SCRIPT_NAME'));

	//delete ksid
	$visitUrl = $_SERVER["REQUEST_URI"];
	$iPos = strpos($visitUrl, '?ksid=');
    if ($iPos) {
        $visitUrl = substr($visitUrl, 0, $iPos);
    }
	$my_url = urlencode("//".getenv('SERVER_NAME') . $visitUrl);

	$rt_param = array();
	if (!empty($my_url)) $rt_param[] = "/2=$my_url";
	if (!empty($ref)) $rt_param[] = "/4=$ref";
	$tag = "<img src=\"http://t7.aqtracker.com/cgi-bin/asp/tagx/h/$aqid";

	if (!empty($rt_param)) {
	    foreach ($rt_param as $val) $tag .= $val;
	}

	$rnd = md5(getmypid() . uniqid(rand(), true));
	$tag .= "/blank.gif?guid=ON&rnd=$rnd\" />";
//info_log($tag, 'tracker');
    return $tag;
}
?>
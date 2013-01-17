<?php
$pinfo=pathinfo($_GET['fileurl']);
$ctype='image/'.$pinfo['extension'];

$Str=implode('', file($_GET['fileurl']));
$len=strlen($Str);

header("Accept-Ranges: bytes");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: public"); 
header("Content-Description: File Transfer");
header("Content-Length: ".$len);  
header("Content-Type: ".$ctype);
$header="Content-Disposition: attachment; filename=".$pinfo['basename'].";";
header($header );
   
echo $Str; 
?>
<?php

//$con=@mysql_connect('210.188.219.5:3306', 'mixiapppartner', 'mixiapppartner');
$con=@mysql_connect('192.168.1.2:3306', 'mysql', 'r$6i7kP#xb');
if($con) {
   if(!@mysql_select_db('mixi_app_partner_test')) echo('0');
} else echo('0');

getRank($_POST['ownerId']);

function getRank($uid) {
  $sql='select lastblock from towerbloxxrank where userid="'.addslashes($uid).'" ';
  if($res=@mysql_query($sql)) {
     if(mysql_num_rows($res)>0) {
       $row=@mysql_fetch_row($res);
       echo $row[0];
       return;
     }
  }
  echo '0';
}

?>
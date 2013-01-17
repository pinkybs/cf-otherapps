<?php
function createsql() {
    $sql="CREATE TABLE `towerbloxxrank` (
      `id` bigint(20) unsigned NOT NULL auto_increment,
      `domain` varchar(255) collate utf8_unicode_ci default NULL,
      `username` varchar(64) collate utf8_unicode_ci default NULL,      
      `userid` varchar(11) collate utf8_unicode_ci default NULL,
      `lastblock` int(20) default NULL,
      `lastdate` datetime default NULL,
       PRIMARY KEY  (`id`),
       KEY `index` (`domain`,`userid`,`lastblock`,`lastdate`)
     ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
     mysql_query('DROP TABLE towerbloxxrank');
     mysql_query($sql);
}

function printxmlheader() {
  echo '<?xml version="1.0" encoding="utf-8"?>';    
}

function resultxml($res, $bexit=true) {
  printxmlheader();
  echo '<RESULT>'.$res.'</RESULT>';   
  if($bexit) exit;   
}

$con=@mysql_connect('192.168.1.2:3306', 'mysql', 'r$6i7kP#xb');
if($con) {
   if(!@mysql_select_db('mixi_app_partner_test')) resultxml('FAIL');
} else resultxml('FAIL');

//createsql() ;

function RankInfoXML($idx, $data) {
    $r='<MEMBER>'.
      '<IDX>'.$idx.'</IDX>'.       
      '<USERID>'.$data['userid'].'</USERID>'.
      '<LASTBLOCK>'.$data['lastblock'].'</LASTBLOCK>'.     
      '<LASTDATE>'.$data['lastdate'].'</LASTDATE>'.    
      '</MEMBER>';
    return $r;       
}

function RankXML($data) {
    $r='<MEMBER>'.
      '<USERID>'.$data['userid'].'</USERID>'.
      '<USERNAME>'.$data['username'].'</USERNAME>'.     
      '<POS>'.$data['pos'].'</POS>'.  
      '<RANKNUM>'.$data['rank'].'</RANKNUM>'.
      '<LASTBLOCK>'.$data['lastblock'].'</LASTBLOCK>'.     
      '<LASTDATE>'.$data['lastdate'].'</LASTDATE>'.    
      '</MEMBER>';
    return $r;       
}

function UDRankXML($data, $str) {
    $r='<'.$str.'MEMBER>'.
      '<USERID>'.$data['userid'].'</USERID>'.
      '<USERNAME>'.$data['username'].'</USERNAME>'.     
      '<POS>'.$data['pos'].'</POS>'.  
      '<RANKNUM>'.$data['rank'].'</RANKNUM>'.
      '<LASTBLOCK>'.$data['lastblock'].'</LASTBLOCK>'.     
      '<LASTDATE>'.$data['lastdate'].'</LASTDATE>'.    
      '</'.$str.'MEMBER>';
    return $r;       
}

function updownuser($domain, $id, $lastblock) {
  $r='';  
  $sql='select * from towerbloxxrank where domain="'.addslashes($domain).'" and userid<>"'.addslashes($id).'" and lastblock>'.(int)($lastblock).'  group by lastblock order by lastdate asc limit 2';
  if($res=@mysql_query($sql)) { 
     if(mysql_num_rows($res)>0) {
       while($row=@mysql_fetch_array($res)) {
          $r=$r.UDRankXML($row, 'UP');
       }                                
     }
  }
  $sql='select * from towerbloxxrank where domain="'.addslashes($domain).'" and userid<>"'.addslashes($id).'"and lastblock<'.(int)($lastblock).' order by lastdate desc limit 2';
  if($res=@mysql_query($sql)) { 
     if(@mysql_num_rows($res)>0) {           
       while($row=@mysql_fetch_array($res)) {
          $r=$r.UDRankXML($row, 'DN');
       }                                
     }
   }
   return $r;       
}

function updownuseroneblock($domain, $id, $lastblock) {
  $r=''; 
  $sql='select distinct * from towerbloxxrank where domain="'.addslashes($domain).'" and userid<>"'.addslashes($id).'" and lastblock>'.(int)($lastblock).' group by lastblock order by lastdate asc limit 2';
  if($res=@mysql_query($sql)) { 
     if(@mysql_num_rows($res)>0) {
       while($row=@mysql_fetch_array($res)) {
          $r=$r.UDRankXML($row, 'UP');
       }                                
     }
  }

  $sql='select * from towerbloxxrank where domain="'.addslashes($domain).'" and userid<>"'.addslashes($id).'"and lastblock='.(int)($lastblock).' order by lastdate desc limit 2';      
  if($res=@mysql_query($sql)) { 
     if(@mysql_num_rows($res)>0) {           
       while($row=@mysql_fetch_array($res)) {
          $r=$r.UDRankXML($row, 'DN');
       }                                
     }
   }
   return $r;       
}

function setuserinfo($domain, $name, $id, $lastblock, $new=false) {
   $r=false;  
   $sql='select * from towerbloxxrank where domain="'.addslashes($domain).'" and userid="'.addslashes($id).'"';
   if($res=@mysql_query($sql)) {   
     if(@mysql_num_rows($res)>0) {
        $sql='update towerbloxxrank set lastblock='.(int)($lastblock).',username="'.addslashes($name).'", lastdate=now() where domain="'.addslashes($domain).'" and userid="'.addslashes($id).'"';
        $r=@mysql_query($sql);
     } else {
         $sql='insert into towerbloxxrank (domain, userid, username, lastblock, lastdate) values("'.addslashes($domain).'","'.addslashes($id).'","'.addslashes($name).'",'.(int)($lastblock).',now())';
         $r=@mysql_query($sql);
     }
     if($r and $new) getuserinfo($domain, $name, $id);
   } 
   if(!$r) resultxml('FAIL');
}

function getuserinfopos($domain, $name, $id) {
   $r=false;
   $sql='select *,(select count(*)+1 from towerbloxxrank as m2 where m2.domain=m1.domain and m2.lastblock >m1.lastblock) as rank from (select @pos:=@pos+1 as pos,m2.*  from (SELECT @pos:=0) r, towerbloxxrank as m2 where domain="'.addslashes($domain).'" order by lastblock desc, lastdate desc) as m1 where  userid="'.addslashes($id).'"';
   if($res=@mysql_query($sql)) {  
     if(@mysql_num_rows($res)>0) {         
       $row=@mysql_fetch_array($res);
       printxmlheader();
       echo '<RANK>'; 
       echo RankXML($row);
       echo updownuser($domain, $id, $row['lastblock']);
       echo '</RANK>';
       $r=true;
     } else { setuserinfo($domain, $name, $id, 0); $r=true; }
   }
   if(!$r) resultxml('FAIL');     
}

function getuserinfo($domain, $name, $id) {
   $r=false;
   $sql='select * from towerbloxxrank where domain="'.addslashes($domain).'" and  userid="'.addslashes($id).'"';
   if($res=@mysql_query($sql)) {  
     if(@mysql_num_rows($res)>0) {         
       $row=@mysql_fetch_array($res);
       printxmlheader();
       echo '<RANK>'; 
       echo RankXML($row);
       echo updownuser($domain, $id, $row['lastblock']);
       echo '</RANK>';
       $r=true;
     } else { setuserinfo($domain, $name, $id, 0, true); $r=true; }
   }
   if(!$r) resultxml('FAIL');     
}

function getranklist($domain, $pos) {
   printxmlheader();    
   $sql='select count(*) as total from towerbloxxrank where domain="'.addslashes($domain).'"';       
   if($res=@mysql_query($sql)) {  
       if($row=@mysql_fetch_array($res)) {
           $Total=$row['total'];
       }
   }
   echo '<RANK>';
   echo '<TOTAL>'.$Total.'</TOTAL>';    
   $fnum=(floor(($pos-1)/6)*6);      
   if($fnum<=0) $fnum=0;
   $sql='select *,(select distinct count(*)+1 from towerbloxxrank as m3 where m3.domain="'.addslashes($domain).'" and m3.lastblock >m1.lastblock ) as rank from (select @pos:=@pos+1 as pos,m2.*  from (SELECT @pos:=0) r, towerbloxxrank as m2 where domain="'.addslashes($domain).'" order by lastblock desc, lastdate desc) as m1 where m1.domain="'.addslashes($domain).'" limit '.(int)($fnum).', 6';
   if($res=@mysql_query($sql)) {  
       while($row=@mysql_fetch_array($res)) { 
          echo RankXML($row);
       }
    }
  echo '</RANK>';  
}

function updownlastblock($domain, $id, $lastblock) {   
  printxmlheader();
  echo '<RANK>'; 
  echo '<LASTBLOCK>'.$lastblock.'</LASTBLOCK>';
  echo updownuser($domain, $id, $lastblock);
  echo '</RANK>';    
} 

function updownoneblock($domain, $id, $lastblock) {   
  printxmlheader();
  echo '<RANK>'; 
  echo '<LASTBLOCK>'.$lastblock.'</LASTBLOCK>';
  echo updownuseroneblock($domain, $id, $lastblock);
  echo '</RANK>';    
} 
    
function getfriendsrank($domain, $ids) {
  printxmlheader();
  echo '<RANK>'; 
  for($i=0;$i<count($ids);$i++) {
      $sql='select * from towerbloxxrank where domain="'.addslashes($domain).'" and userid="'.addslashes($ids[$i]).'"';
      if($res=@mysql_query($sql)) {  
         if($row=@mysql_fetch_array($res)) {
         	echo RankInfoXML($i, $row);
         }
      }
  }
  echo '</RANK>';       
}     
    
switch($_POST['mode']) {
    case 'setuserinfo':  setuserinfo($_POST['domain'], $_POST['name'], $_POST['id'], $_POST['lastblock']);
      break;
    case 'getuserinfo':  getuserinfo($_POST['domain'], $_POST['name'], $_POST['id']);
      break;
    case 'getranklist':  getranklist($_POST['domain'], $_POST['pos']);
      break;    
    case 'getupdownlist':  updownlastblock($_POST['domain'], $_POST['id'], $_POST['lastblock']);
      break;         
    case 'getupdownonelist':  updownoneblock($_POST['domain'], $_POST['id'], $_POST['lastblock']);
      break; 
    case 'getfriendsrank' : getfriendsrank($_POST['domain'], $_POST['ids']);
}


function writelog($msg) 
{
	//////////////////////////////////////// 
$filename = '/home/linno/website/test/mixi/cf/www/ttm_test/Web/aa.txt';
 if (!$handle = fopen($filename, 'a')) {
}
// 将$somecontent写入到我们打开的文件中。
if (fwrite($handle, $msg) === FALSE) {
}
fclose($handle);
//////////////////////////////////////////////

}
?>
<?php

/**
 * Smarty upper modifier plugin
 *
 * @param string
 * @return string
 */
function smarty_modifier_remainingtime($time)
{
     /*$year   = floor($time / 60 / 60 / 24 / 365);
       $time  -= $year * 60 * 60 * 24 * 365;
       $month  = floor($time / 60 / 60 / 24 / 30);
       $time  -= $month * 60 * 60 * 24 * 30;
       $week   = floor($time / 60 / 60 / 24 / 7);
       $time  -= $week * 60 * 60 * 24 * 7;
       $day    = floor($time / 60 / 60 / 24);
       $time  -= $day * 60 * 60 * 24;*/
       
       $nowTime = time();
       
       $strTime = strtotime($time);
       //24 hour
       $rmTime = 24*60*60-abs($nowTime-$strTime);
       
       //get hour
       $hour   = str_pad(floor($rmTime / 60 / 60), 2, "0", STR_PAD_LEFT);
       $rmTime  -= $hour * 60 * 60;
       //get minute
       $minute = str_pad(floor($rmTime / 60), 2, "0", STR_PAD_LEFT);
       $rmTime  -= $minute * 60;
       //remaining second
       $second = str_pad($rmTime, 2, "0", STR_PAD_LEFT);
       
       $elapse = $hour . ':' . $minute . ':' . $second;
    
       return $elapse;
}

?>

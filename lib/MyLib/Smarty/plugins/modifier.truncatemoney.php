<?php

/**
 * Smarty upper modifier plugin
 *
 * @param string
 * @return string
 */
function smarty_modifier_truncatemoney($number)
{
    $length = strlen($number);
	if ( $length <= 8 ) {
		$number = round($number/10000);
		$number .= '万';
	}
	else if ( $length == 9 ) {
		$number = round($number/100000000, 2);
		$number .= '億';
	}
	else if ( $length == 10 ) {
		$number = round($number/100000000, 2);
		$number .= '億';
	}
	else if ( $length == 11 ) {
		$number = round($number/100000000, 1);
		$number .= '億';
	}
    return ($number);
}

?>

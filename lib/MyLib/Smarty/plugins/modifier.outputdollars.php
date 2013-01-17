<?php

/**
 * Smarty upper modifier plugin
 *
 * @param string
 * @return string
 */
function smarty_modifier_outputdollars($number)
{
    $output = number_format($number);

    return ($output);
}

?>

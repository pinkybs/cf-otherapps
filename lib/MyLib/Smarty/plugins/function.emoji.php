<?php

function smarty_function_emoji($params, &$smarty)
{
    $code = $params['code'];

    require_once 'MyLib/Emoji/MobileClass.php';
    $MobileClass = new MobileClass();
    $MobileClass->InputMode = 0;
    
    return $MobileClass->Convert($code);

}

?>

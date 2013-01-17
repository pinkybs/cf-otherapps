<?php

function smarty_function_disneyimg($params, &$smarty)
{
    $name = $params['name'];
    $style = $params['style'];

    $ua = Zend_Registry::get('ua');
    $static = Zend_Registry::get('static');
    
    //AU
    if ( $ua == 3 ) {
        $imgUrl = '<img copyright="yes" src="' . $static . '/apps/disney/img/img_au/' . $name .'_a.png" ' . $style .' />';
    }
    //SOFTBANK
    else if ( $ua == 2 ) {
        $imgUrl = '<img src="' . $static . '/apps/disney/img/img_softbank/' . $name . '_s.pnz" ' . $style .' />';
    }
    //docomo  and other
    else {
        $imgUrl = '<img src="' . $static . '/apps/disney/img/img_docomo/' . $name .'_d.gif" ' . $style .' />';
    }

    return $imgUrl;
}

?>

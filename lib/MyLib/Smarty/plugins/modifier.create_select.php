<?php
    function smarty_modifier_create_select($number)
    {
        $html = "";

        $html = $html . "<select style='color:#000;' name='itemselect'>";

        for ($i = 1; $i <= $number; $i++) {
            if ($i == 1) {
                $html = $html . "<option style='color:#000;' value=".$i." selected>$i".'個'."</option>";
            }
            else {
                $html = $html . "<option style='color:#000;' value=".$i.">$i".'個'."</option>";
            }
        }

        $html = $html . "</select>";

        return $html;
    }
?>
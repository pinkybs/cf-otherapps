<?php

class Bll_Application_Information
{
    /**
     * get app name by app id
     *
     * @param int $appId
     * @return string
     */
    public static function getAppName($appId)
    {
        $appList = array(
            12629   => 'school',
            12235   => 'school',
            13522   => 'ship',
            13651   => 'ship'
        );

        if (isset($appList[$appId])) {
            return $appList[$appId];
        }

        return null;
    }
}

<?php

require_once 'Dal/Nonce.php';

class Bll_Nonce
{    
    const EXPIRES_TIME = 60; // 1 miniute
    
    /**
     * check nonce is valid
     *
     * @param string $nonce
     * @return bool
     */
    public static function isValid($nonce, &$data)
    {
        $dalNonce = Dal_Nonce::getDefaultInstance();
        
        $data = $dalNonce->getNonce($nonce);
        
        $valid = false;
        
        if ($data) {
            $expires = $data['expires'];
            $now = time();
            if ($now - $expires < self::EXPIRES_TIME) {
                $valid = true;
            }
            
            //delete nonce
            try {
                $dalNonce->deleteNonce($nonce);
            }
            catch (Exception $e) {

            }
        }
        
        return $valid;
    }
    
    /**
     * create an new access nonce
     *
     * @return string
     */
    public static function createNonce($app_id, $owner_id, $viewer_id, $app_name = '')
    {
        require_once 'Bll/Secret.php';
        $nonce = Bll_Secret::getUUID();
        $expires = time() + self::EXPIRES_TIME;
        
        try {
            $dalNonce = Dal_Nonce::getDefaultInstance();
            $dalNonce->newNonce($nonce, $expires, $app_id, $owner_id, $viewer_id, $app_name);
            return $nonce;
        }
        catch (Exception $e) {
            return null;
        }
    }

}
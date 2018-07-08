<?php

namespace Vortexgin\LibraryBundle\Utils;

/**
 * Google Recaptcha v2 functions
 * 
 * @category Utils
 * @package  Vortexgin\FileBrowserBundle\Utils
 * @author   vortexgin <vortexgin@gmail.com>
 * @license  Apache 2.0
 * @link     https://github.com/vortexgin/file-browser
 */
class RecaptchaV2
{

    /**
     * Function to verify recaptcha request
     * 
     * @param string $recaptcha Recaptcha string request
     * @param string $secretKey Recaptcha secret key
     * 
     * @return boolean
     */
    static public function verify(string $recaptcha, string $secretKey)
    {
        try {
            $url = "https://www.google.com/recaptcha/api/siteverify";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt(
                $ch, CURLOPT_POSTFIELDS, 
                array(
                    "secret" => $secretKey, 
                    "response" => $recaptcha
                )
            );
            $response = curl_exec($ch);
            curl_close($ch);
            $data = json_decode($response);     
        
            return $data->success;    
        } catch(\Exception $e) {
            return false;
        }
    }
}
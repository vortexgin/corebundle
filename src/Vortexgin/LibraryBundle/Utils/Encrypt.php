<?php

namespace Vortexgin\LibraryBundle\Utils;

/**
 * Encryption utilization functions
 * 
 * @category Utils
 * @package  Vortexgin\LibraryBundle\Utils
 * @author   vortexgin <vortexgin@gmail.com>
 * @license  Apache 2.0
 * @link     https://github.com/vortexgin/corebundle
 */
class Encrypt
{

    /**
     * Function to encrypt string
     * 
     * @param string $string String to encrypt
     * @param string $secret Encryption secret
     * 
     * @return mixed
     */
    public static function encode($string, $secret, $tag='vortexgin')
    {
        $cipher = "aes-128-gcm";
        if (in_array($cipher, openssl_get_cipher_methods())) {
            $ivlen = openssl_cipher_iv_length($cipher);
            $iv = openssl_random_pseudo_bytes($ivlen);
            return openssl_encrypt($string, $cipher, $secret, 0, $iv, $tag);
        }

        return false;
    }

    /**
     * Function to decrypt string
     * 
     * @param string $string String to decrypt
     * @param string $secret Encryption secret
     * 
     * @return mixed
     */
    public static function decode($string, $secret, $tag='vortexgin')
    {
        $cipher = "aes-128-gcm";
        if (in_array($cipher, openssl_get_cipher_methods())) {
            $ivlen = openssl_cipher_iv_length($cipher);
            $iv = openssl_random_pseudo_bytes($ivlen);
            return openssl_decrypt($string, $cipher, $secret, 0, $iv, $tag);
        }

        return false;
    }
}
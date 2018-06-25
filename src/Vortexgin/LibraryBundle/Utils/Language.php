<?php

namespace Vortexgin\LibraryBundle\Utils;

/**
 * Country constanta so no need to create Country table for better performance
 * 
 * @category Utils
 * @package  Vortexgin\LibraryBundle\Utils
 * @author   vortexgin <vortexgin@gmail.com>
 * @license  Apache 2.0
 * @link     https://github.com/vortexgin/corebundle
 */
abstract class Language
{
    const INDONESIA = 'id';
    const SINGAPORE = 'sg';

    /**
     * Function to chek if language code is exists
     * 
     * @param string $code Language code
     * 
     * @return string
     */
    public static function isExist( $code )
    {
        $reflection = new \ReflectionClass(__CLASS__);
        $const      = array_search($code, $reflection->getConstants());

        return is_string($const);
    }
}

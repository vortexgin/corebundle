<?php
namespace Vortexgin\CoreBundle\Util;

/**
 * Country constanta so no need to create Country table for better performance
 */
abstract class Country
{
    const INDONESIA = 'id';
    const SINGAPORE = 'sg';

    public static function isExist( $code )
    {
        $reflection = new \ReflectionClass(__CLASS__);
        $const      = array_search($code, $reflection->getConstants());

        return is_string($const);
    }
}

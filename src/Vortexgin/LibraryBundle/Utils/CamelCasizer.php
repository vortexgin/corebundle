<?php

namespace Vortexgin\LibraryBundle\Utils;

/**
 * Camel casizer class 
 * 
 * @category Utils
 * @package  Vortexgin\LibraryBundle\Utils
 * @author   vortexgin <vortexgin@gmail.com>
 * @license  Apache 2.0
 * @link     https://github.com/vortexgin/corebundle
 */
final class CamelCasizer
{

    /**
     * Function to convert underscore into camel case
     * 
     * @param string $string String to convert
     * 
     * @return string
     */
    public static function underScoreToCamelCase($string)
    {
        return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $string))));
    }
}

<?php

namespace Vortexgin\LibraryBundle\Utils;

/**
 * Array utilization function 
 * 
 * @category Utils
 * @package  Vortexgin\LibraryBundle\Utils
 * @author   vortexgin <vortexgin@gmail.com>
 * @license  Apache 2.0
 * @link     https://github.com/vortexgin/corebundle
 */
final class ArrayUtils
{
    const RETURN_KEY_VALUE = 'RETURN_KEY_VALUE';

    const RETURN_VALUE = 'RETURN_VALUE';

    const RETURN_KEY = 'RETURN_KEY';

    /**
     * Function to find element by key like
     * 
     * @param array  $array  haystack of array
     * @param string $key    key to find
     * @param string $return return format
     * 
     * @return array
     */
    public static function findKeyLike(array $array, $key, $return = 'RETURN_KEY')
    {
        $searchKey = str_replace('%', '*',  $key);
        $search = str_replace('\*', '.*?', preg_quote($searchKey, '/'));
        $result = preg_grep('/^' . $search . '$/i', array_keys($array));

        if ($return === self::RETURN_KEY_VALUE) {
            $result = array_intersect_key($array, array_flip($result));

            return $result;
        }

        return array_values($result);
    }
}

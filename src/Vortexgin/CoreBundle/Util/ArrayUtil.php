<?php

namespace Vortexgin\CoreBundle\Util;

final class ArrayUtil
{
    const RETURN_KEY_VALUE = 'RETURN_KEY_VALUE';

    const RETURN_VALUE = 'RETURN_VALUE';

    const RETURN_KEY = 'RETURN_KEY';

    /**
     * @param array $array
     * @param string $key
     * @param string $return
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

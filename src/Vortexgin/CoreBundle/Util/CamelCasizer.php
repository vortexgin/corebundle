<?php

namespace Vortexgin\CoreBundle\Util;

final class CamelCasizer
{
    public static function underScoretToCamelCase($string)
    {
        return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $string))));
    }
}

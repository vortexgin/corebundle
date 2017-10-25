<?php

namespace Vortexgin\CoreBundle\Util;

class StringUtils
{

    /**
     * Function to create slug for SEO clean url title
     *
     * @param string $string
     * @param string $separator
     * @return string
     */
    static public function createSlug($string, $separator = '-')
    {
        $accents_regex = '~&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i';
        $special_cases = array('&' => 'and');
        $string = mb_strtolower(trim($string), 'UTF-8');
        $string = str_replace(array_keys($special_cases), array_values($special_cases), $string);
        $string = preg_replace($accents_regex, '$1', htmlentities($string, ENT_QUOTES, 'UTF-8'));
        $string = preg_replace("/[^a-z0-9]/u", "$separator", $string);
        $string = preg_replace("/[$separator]+/u", "$separator", $string);
        return $string;
    }

    /**
     * Function to generate random string
     *
     * @param integer $length
     * @param string $type
     * @return string
     */
    static public function generateRand($length, $type = 'alphanumeric')
    {
        switch ($type) {
            case 'numeric':
                $char = '0123456789';
                break;
            case 'alpha':
                $char = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;
            case 'alpha_upper':
                $char = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;
            case 'alpha_lower':
                $char = 'abcdefghijklmnopqrstuvwxyz';
                break;
            case 'alphanumeric_upper':
                $char = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;
            case 'alphanumeric_lower':
                $char = '0123456789abcdefghijklmnopqrstuvwxyz';
                break;
            case 'alphanumeric':
            default:
                $char = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;
        }
        $charactersLength = strlen($char);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $char[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public static function isJson($string)
    {
        @json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    /**
     * This program is free software. It comes without any warranty, to
     * the extent permitted by applicable law. You can redistribute it
     * and/or modify it under the terms of the Do What The Fuck You Want
     * To Public License, Version 2, as published by Sam Hocevar. See
     * http://sam.zoy.org/wtfpl/COPYING for more details.
     */

    /**
     * Tests if an input is valid PHP serialized string.
     *
     * Checks if a string is serialized using quick string manipulation
     * to throw out obviously incorrect strings. Unserialize is then run
     * on the string to perform the final verification.
     *
     * Valid serialized forms are the following:
     * <ul>
     * <li>boolean: <code>b:1;</code></li>
     * <li>integer: <code>i:1;</code></li>
     * <li>double: <code>d:0.2;</code></li>
     * <li>string: <code>s:4:"test";</code></li>
     * <li>array: <code>a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}</code></li>
     * <li>object: <code>O:8:"stdClass":0:{}</code></li>
     * <li>null: <code>N;</code></li>
     * </ul>
     *
     * @author        Chris Smith <code+php@chris.cs278.org>
     * @copyright    Copyright (c) 2009 Chris Smith (http://www.cs278.org/)
     * @license        http://sam.zoy.org/wtfpl/ WTFPL
     * @param        string $value Value to test for serialized form
     * @param        mixed $result Result of unserialize() of the $value
     * @return        boolean            True if $value is serialized data, otherwise false
     */
    public static function is_serialized($value, &$result = null)
    {
        // Bit of a give away this one
        if (!is_string($value)) {
            return false;
        }
        if (empty($value)) {
            return false;
        }

        // Serialized false, return true. unserialize() returns false on an
        // invalid string or it could return false if the string is serialized
        // false, eliminate that possibility.
        if ($value === 'b:0;') {
            $result = false;
            return true;
        }

        $length = strlen($value);
        $end = '';

        switch ($value[0]) {
            case 's':
                if ($value[$length - 2] !== '"') {
                    return false;
                }
            case 'b':
            case 'i':
            case 'd':
                // This looks odd but it is quicker than isset()ing
                $end .= ';';
            case 'a':
            case 'O':
                $end .= '}';

                if ($value[1] !== ':') {
                    return false;
                }

                switch ($value[2]) {
                    case 0:
                    case 1:
                    case 2:
                    case 3:
                    case 4:
                    case 5:
                    case 6:
                    case 7:
                    case 8:
                    case 9:
                        break;

                    default:
                        return false;
                }
            case 'N':
                $end .= ';';

                if ($value[$length - 1] !== $end[0]) {
                    return false;
                }
                break;

            default:
                return false;
        }

        if (($result = @unserialize($value)) === false) {
            $result = null;
            return false;
        }
        return true;
    }

    public function excerpt($str, $startPos = 0, $maxLength = 100)
    {
        $str = strip_tags($str);
        if (strlen($str) > $maxLength) {
            $excerpt = substr($str, $startPos, $maxLength - 3);
            $lastSpace = strrpos($excerpt, ' ');
            $excerpt = substr($excerpt, 0, $lastSpace);
            $excerpt .= '...';
        } else {
            $excerpt = $str;
        }

        return $excerpt;
    }
}

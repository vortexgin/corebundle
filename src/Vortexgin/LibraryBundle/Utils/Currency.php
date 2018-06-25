<?php

namespace Vortexgin\LibraryBundle\Utils;

/**
 * Currency utilization functions
 * 
 * @category Utils
 * @package  Vortexgin\LibraryBundle\Utils
 * @author   vortexgin <vortexgin@gmail.com>
 * @license  Apache 2.0
 * @link     https://github.com/vortexgin/corebundle
 */
class Currency
{

    /**
     * Function to round money format to Indonesian
     * 2110  =>  2000
     * 3500  =>  4000
     * 5700  =>  6000
     * 
     * @param int $number number to convert
     * 
     * @return int
     */
    static public function indonesianRound($number)
    {
        return round($number / 1000, 0, PHP_ROUND_HALF_UP) * 1000;
    }

    /**
     * Function to getList indonesian price in string
     * 
     * @param integer $number number to convert
     * 
     * @return string
     */
    static public function getIndonesianPriceInString($number) {
        $abil = array("", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas");
        if ($number < 12)
            return " " . $abil[$number];
        elseif ($number < 20)
            return self::getIndonesianPriceInString($number - 10) . "belas";
        elseif ($number < 100)
            return self::getIndonesianPriceInString($number / 10) . " puluh" . self::getIndonesianPriceInString($number % 10);
        elseif ($number < 200)
            return " seratus" . self::getIndonesianPriceInString($number - 100);
        elseif ($number < 1000)
            return self::getIndonesianPriceInString($number / 100) . " ratus" . self::getIndonesianPriceInString($number % 100);
        elseif ($number < 2000)
            return " seribu" . self::getIndonesianPriceInString($number - 1000);
        elseif ($number < 1000000)
            return self::getIndonesianPriceInString($number / 1000) . " ribu" . self::getIndonesianPriceInString($number % 1000);
        elseif ($number < 1000000000)
            return self::getIndonesianPriceInString($number / 1000000) . " juta" . self::getIndonesianPriceInString($number % 1000000);
    }

}

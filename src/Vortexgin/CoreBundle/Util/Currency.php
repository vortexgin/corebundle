<?php

namespace Vortexgin\CoreBundle\Util;

class Currency {

    /**
     * Function to round money format to Indonesian
     * 2110  =>  2000
     * 3500  =>  4000
     * 5700  =>  6000
     * @param int $number
     * @return int
     */
    static public function indonesianRound($number) {
        return round($number / 1000, 0, PHP_ROUND_HALF_UP) * 1000;
    }

    /**
     * Function to getList indonesian price in string
     * @param integer $x
     * @return string
     */
    static public function getIndonesianPriceInString($x) {
        $abil = array("", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas");
        if ($x < 12)
            return " " . $abil[$x];
        elseif ($x < 20)
            return self::getIndonesianPriceInString($x - 10) . "belas";
        elseif ($x < 100)
            return self::getIndonesianPriceInString($x / 10) . " puluh" . self::getIndonesianPriceInString($x % 10);
        elseif ($x < 200)
            return " seratus" . self::getIndonesianPriceInString($x - 100);
        elseif ($x < 1000)
            return self::getIndonesianPriceInString($x / 100) . " ratus" . self::getIndonesianPriceInString($x % 100);
        elseif ($x < 2000)
            return " seribu" . self::getIndonesianPriceInString($x - 1000);
        elseif ($x < 1000000)
            return self::getIndonesianPriceInString($x / 1000) . " ribu" . self::getIndonesianPriceInString($x % 1000);
        elseif ($x < 1000000000)
            return self::getIndonesianPriceInString($x / 1000000) . " juta" . self::getIndonesianPriceInString($x % 1000000);
    }

}

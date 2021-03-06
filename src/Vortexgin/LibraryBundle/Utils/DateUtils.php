<?php

namespace Vortexgin\LibraryBundle\Utils;

/**
 * Date utilization functions
 * 
 * @category Utils
 * @package  Vortexgin\LibraryBundle\Utils
 * @author   vortexgin <vortexgin@gmail.com>
 * @license  Apache 2.0
 * @link     https://github.com/vortexgin/corebundle
 */
class DateUtils
{

    /**
     * Function to get date period
     * 
     * @param \DateTime $period end date
     * @param string    $type   type of period. week, month, year
     * 
     * @return mixed
     */
    public static function getPeriod(\DateTime $period, $type='week')
    {
        if (!isset($period)) {
            return false;
        }
        
        $end = $period;
        $to = $end->format("Y-m-d 23:59:59");

        $start = $period;
        $start->sub(new \DateInterval('P7D'));
        $from = $start->format("Y-m-d 00:00:00");

        $interval = 'P1D';
        $dateFormat = 'Y-m-d';
        $mysqlDateFormat = '%Y-%m-%d';
        if ($type == 'month') {
            $start = $period;
            $start->sub(new \DateInterval('P1M'));
            $from = $start->format("Y-m-d 00:00:00");
        } elseif ($type == 'year') {
            $start = $period;
            $start->sub(new \DateInterval('P1Y'));
            $from = $start->format("Y-m-d 00:00:00");

            $interval = 'P1M';
            $dateFormat = 'Y-m';
            $mysqlDateFormat = '%Y-%m';
        }
        $period = new \DatePeriod($start, new \DateInterval($interval), $end);
        
        return array($period, $interval, $dateFormat, $mysqlDateFormat);
    }
    
    /**
     * Function to check string is formatted Y-m-d date
     * 
     * @param string $date String formatted date
     * 
     * @return boolean
     */
    public static function isDate($date)
    {
        list($y, $m, $d) = explode("-", $date);
        if (checkdate($m, $d, $y)) {
                return true;
        }

        return false;
    }

    /**
     * Function to get date difference on string format
     * 
     * @param string  $str_interval String of date code
     * @param string  $dt_menor     String of date
     * @param string  $dt_maior     String of date
     * @param boolean $relative     Relative return
     * 
     * @return int
     */
    public static function dateDiff( $str_interval, $dt_menor, $dt_maior, $relative=false)
    {
        if(is_string($dt_menor)) $dt_menor = date_create($dt_menor);
        if(is_string($dt_maior)) $dt_maior = date_create($dt_maior);

        $diff = date_diff($dt_menor, $dt_maior, ! $relative);

        switch( $str_interval){
        case "y": 
            $total = $diff->y + $diff->m / 12 + $diff->d / 365.25;
            break;
        case "m":
            $total= $diff->y * 12 + $diff->m + $diff->d/30 + $diff->h / 24;
            break;
        case "d":
            $total = $diff->y * 365.25 + $diff->m * 30 + $diff->d + $diff->h/24 + $diff->i / 60;
            break;
        case "h": 
            $total = ($diff->y * 365.25 + $diff->m * 30 + $diff->d) * 24 + $diff->h + $diff->i/60;
            break;
        case "i": 
            $total = (($diff->y * 365.25 + $diff->m * 30 + $diff->d) * 24 + $diff->h) * 60 + $diff->i + $diff->s/60;
            break;
        case "s": 
            $total = ((($diff->y * 365.25 + $diff->m * 30 + $diff->d) * 24 + $diff->h) * 60 + $diff->i)*60 + $diff->s;
            break;
        }
        if($diff->invert)
            return -1 * $total;
        else
            return $total;
    }
}

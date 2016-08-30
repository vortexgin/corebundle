<?php
namespace Vortexgin\CoreBundle\Util;

class DateExtended
{

  public static function getPeriod(\DateTime $period, $type='week'){
      if( !isset($period)) {
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
      if ( $type == 'month' ) {
        $start = $period;
        $start->sub(new \DateInterval('P1M'));
        $from = $start->format("Y-m-d 00:00:00");
      } else if ( $type == 'year' ) {
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
    
    public static function is_date($date){
      list($y, $m, $d) = explode("-", $date);
      if(checkdate($m, $d, $y)){
        return true;
      }

      return false;
    }
}

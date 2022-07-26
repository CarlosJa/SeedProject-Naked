<?php
class Helper {

    public static function print_array($f) {
        echo '<pre>';
        print_r($f);
        echo '</pre>';
    }

    public static function getConfig() {
        require_once (dirname(__FILE__, 2) . '/config.php');
    }
    public static function load($file) {
        include  $_SERVER['DOCUMENT_ROOT'] . ADMIN_BASE . '/controllers/' . $file . '.php';
    }

    public static function sendlog($string){
        error_log("{$string}", 3,  dirname(__FILE__, 2) . "/error_log.txt");
    }

    public static function numberFormat($str) {
        return number_format($str, 2, '.', '');
    }

    public static function countDecimals($fNumber) {
        $fNumber = floatval($fNumber);
        for ( $iDecimals = 0; $fNumber != round($fNumber, $iDecimals); $iDecimals++ );
        return $iDecimals;
    }

    public static function TimeDiff($from, $to) {
        $start_date = new DateTime('2007-09-01 04:10:58');
        $since_start = $start_date->diff(new DateTime('2012-09-11 10:25:00'));
        echo $since_start->days.' days total<br>';
        echo $since_start->y.' years<br>';
        echo $since_start->m.' months<br>';
        echo $since_start->d.' days<br>';
        echo $since_start->h.' hours<br>';
        echo $since_start->i.' minutes<br>';
        echo $since_start->s.' seconds<br>';

    }


    public static function getBetween($content,$start,$end){
        $r = explode($start, $content);
        if (isset($r[1])){
            $r = explode($end, $r[1]);
            return $r[0];
        }
        return '';
    }

    public static function calculatePercentage($oldFigure, $newFigure) {
        if (($oldFigure != 0) && ($newFigure != 0)) {
            $percentChange = (1 - $oldFigure / $newFigure) * 100;
        }
        else {
            $percentChange = null;
        }
        return $percentChange;
    }

} // End Class
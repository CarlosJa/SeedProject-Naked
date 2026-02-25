<?php


class Debug {

    public static function print_array($f, $kill=false) {
        echo '<pre>';
        print_r($f);
        echo '</pre>';


        if($kill){
            die('-- Debugging --');
        }
    }


    public static function sendlog($string){
        error_log($string, 0);
    }

} // End Class
<?php


class Debug {

    public static function print_array($f) {
        echo '<pre>';
        print_r($f);
        echo '</pre>';
    }


    public static function sendlog($string){
        error_log($string, 0);
    }

} // End Class
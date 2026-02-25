<?php


class Validate {

    public static function Email($f) {
        if($f) {
            if (!filter_var($f, FILTER_VALIDATE_EMAIL)) {
                return false;
            } else {
                return $f;
            }
        } else {
            $Error['Code']='';
            $Error['msg'] = "Email Required";
            return (json_encode($Error));
        }

    }


    public static function sendlog($string){
        error_log($string, 0);
    }

} // End Class
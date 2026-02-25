<?php

# Deprecate this

class Numbers {

    public static function ValidPhone($str) {
        if(preg_match('/^(\d{1,4}[ -]?)?(\d{6,10}|(\d{1,5}[ -]?\d{1,5}))(?![\d-])/', $str)) {
            return 1;
        } else {
            return 0;
        }
    }

    public static function onlyNumbers($c){
        return preg_replace('/\D/', '', $c);
    }

    public static function formatDate($d, $format = 'm-d-Y') {
        return date($format , strtotime($d));
    }


    public static function formatPhoneNumber($phoneNumber) {
        $phoneNumber = preg_replace('/[^0-9]/','',$phoneNumber);

        if(strlen($phoneNumber) > 10) {
            $countryCode = substr($phoneNumber, 0, strlen($phoneNumber)-10);
            $areaCode = substr($phoneNumber, -10, 3);
            $nextThree = substr($phoneNumber, -7, 3);
            $lastFour = substr($phoneNumber, -4, 4);

            $phoneNumber = '+'.$countryCode.' ('.$areaCode.') '.$nextThree.'-'.$lastFour;
        }
        else if(strlen($phoneNumber) == 10) {
            $areaCode = substr($phoneNumber, 0, 3);
            $nextThree = substr($phoneNumber, 3, 3);
            $lastFour = substr($phoneNumber, 6, 4);

            $phoneNumber = '('.$areaCode.') '.$nextThree.'-'.$lastFour;
        }
        else if(strlen($phoneNumber) == 7) {
            $nextThree = substr($phoneNumber, 0, 3);
            $lastFour = substr($phoneNumber, 3, 4);

            $phoneNumber = $nextThree.'-'.$lastFour;
        }

        return $phoneNumber;
    }

} // End Class
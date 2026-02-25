<?php

class GlobalConst {


    public static function getCountries($s=""){
       $SQLReturn = \DB::select("SELECT * FROM snpi_countries WHERE 1");
       return $SQLReturn;
    }

    public static function getStates($s=""){
        $SQLReturn = \DB::select("SELECT * FROM snpi_states WHERE 1");
        return $SQLReturn;
    }

} // End Class
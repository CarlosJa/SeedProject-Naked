<?php


class Logger {

    /*
     * Log Information to Database
     */
    public static function Entry($logData) {
        #TODO: Add Snoopi.io GeoIP Tracking

        $logData['location'] = "na";
        $logData['ipaddress'] = ($logData['ipaddress']) ? $logData['ipaddress'] : $_SERVER['REMOTE_ADDR'];
        $logData['host'] = $_SERVER['HTTP_ORIGIN'];

        Return \Db::insert('sp_activitylog', $logData);
    }

    /*
     * Log Information to a File
     */
    public static function LogtoFile($string){
        error_log($string, 0);
    }

} // End Class
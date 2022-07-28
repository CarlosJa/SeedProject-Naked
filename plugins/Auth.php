<?php

class Auth {

    public static function externalAccess(){
        $headers = apache_request_headers();

        if($headers['X-Authorization'] == AUTHORIZATION) {
            $db = new Database(DB_TYPE, DB_HOST, DB_NAME, DB_USER, DB_PASS);  // Initiate database
            $Result = $db->select("select * from webform where formkey='123456'");
            print_array($Result);
        }

    }
}
?>
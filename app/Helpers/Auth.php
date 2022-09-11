<?php

class Auth {

    public static function API() {
        if ($_SERVER['REMOTE_ADDR'] != $_SERVER['SERVER_ADDR']) {
            # If you want additional security for example. Authorization to Access the server you can use this.
            # enable SECUREAPI in config.php
            if(SECUREAPI) {
                $headers = apache_request_headers();
                if($headers['X-Authorization'] != AUTHORIZATION) die('Access Denied');
            }

            # Requires Authorization
            $ApiKey = $_REQUEST['apikey'];
            $ApiUser = \Db::getRow("select * from sp_users_api where apikey = '{$ApiKey}'");
            if(!$ApiUser) die('Unauthorized: invalid apikey');

            #TODO: ////// Security Layers Below ////////
            #TODO: Record API Requests
            #TODO: Add API Limits
            #Todo: Check for Whitelist IP Address


            $Return['code'] = '1';
            $Return['userid'] = $ApiUser['userid'];
            return $Return;
        }
    }


    public static function handleLogin() {
        $logged = $_SESSION['login'];
        if ($logged == false) {
            session_destroy();
            header('location: ./login');
            exit;
        }

        $Return = $logged;

        return $Return;
    }


    /*
     * This make no fuckn sense
     */
    public static function deniedAction($permission, $controller) {
        $controller = strtolower($controller);
        foreach($permission as $permContrller => $PerMethod) {
            if($permContrller == $controller) {
                $AllowAccess = 1;
                break;
            }
        }

        if($AllowAccess == 1) {
            return true;
        } else {
            print_array('Deny Access ' . $controller);
        }

    }

    /*
      This manages Access Level at a method level.
      If the user has a restriction in the database they will be denied access to this method.
     */

    #TODO: I'm debating whether i should have Die or just have it return a false. Having false will leave flexbilities in jquery / json call to the method.
    #TODO: Actually i could have a json error Access Denied with a Error Code with 0 hmmm.....

    public static function AccessGranted($permission, $method) {

        if (in_array($method, $permission)) {
            print_array('allow access');  // true
        } else {
            die('Access Denied');
        }
    }

}
?>
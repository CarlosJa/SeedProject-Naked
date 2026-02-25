<?php

class Auth {

    public static function API() {
        header('Access-Control-Allow-Origin: *');

        // If its coming from an Ajax Request from inside the server return true and proceed.
        if (! empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            return null;  // Return to exit function.
        }

        // Anything outside the server will go through the API Key Process.
        if ($_SERVER['REMOTE_ADDR'] != $_SERVER['SERVER_ADDR']) { # This happens if its outside the server.
                # If you want additional security for example. Authorization to Access the server you can use this.
                # enable SECUREAPI in config.php
                if (SECUREAPI) {
                    $headers = apache_request_headers();
                    if ($headers['X-Authorization'] != AUTHORIZATION) die('Access Denied');
                }

                # Requires Authorization
                $ApiKey = $_REQUEST['apikey'];
                $ApiUser = \Db::getRow("select * from sp_users_api where apikey = '{$ApiKey}'");
                if (!$ApiUser) {
                    $Return['code'] = '401';
                    $Return['msg'] = "Unauthorized: Api Invalid";
                    $Response = json_encode($Return);

                    header('Content-Type: ' . ($_GET['callback'] ? 'application/javascript' : 'application/json') . ';charset=UTF-8');
                    echo isset($_GET['callback']) ? "/**/ typeof {$_GET['callback']} === 'function' && {$_GET['callback']}($Response)" : $Response;
                    exit();
                }

                #TODO: ////// Security Layers Below ////////
                #TODO: Record API Requests
                #TODO: Add API Limits
                #Todo: Check for Whitelist IP Address

                ### Everything Passed Now proceed with the API Request.
                $Return['code'] = '1';
                $Return['userid'] = $ApiUser['userid'];
                return $Return;
        }

    }


    public static function handleLogin() {
        $logged = $_SESSION['login'];
        if ($logged == false) {
            session_destroy();
            header('location: ' . '/login');
            exit;
        }

        $Return = $logged;
        return $Return;
    }


    /*
     * Refactor this, it makes nos sense.
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
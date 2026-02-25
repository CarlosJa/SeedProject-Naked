<?php

/** Reference */
/**
*                    // Match all request URIs
[i]                  // Match an integer
[i:id]               // Match an integer as 'id'
[a:action]           // Match alphanumeric characters as 'action'
[h:key]              // Match hexadecimal characters as 'key'
[:action]            // Match anything up to the next / or end of the URI as 'action'
[create|edit:action] // Match either 'create' or 'edit' as 'action'
[*]                  // Catch all (lazy, stops at the next trailing slash)
[*:trailing]         // Catch all as 'trailing' (lazy)
[**:trailing]        // Catch all (possessive - will match the rest of the URI)
.[:format]?          // Match an optional parameter 'format' - a / or . before the block is also optional
 */

class Router {
    public static function Routing() {
        $router = new AltoRouter();
        $router->setBasePath('');

        // Rules Set Here
        $router->map('GET|POST','/ref/[a:key]',  array('c' => 'index', 'a' => 'index'));
        //$router->map('GET|POST', '/signal/[i:botid]',  array('c' => 'signal', 'a' => 'index'));
        $router->map('GET|POST', '/dashboard/games/[i:botid]',  array('c' => 'test', 'a' => 'index'));

        return $router->match();

    }
}
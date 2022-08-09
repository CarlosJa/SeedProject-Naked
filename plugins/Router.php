<?php

class Router {
    public static function Routing() {
        $router = new AltoRouter();
        $router->setBasePath('');

        // Rules Set Here
        //$router->map('GET|POST','/crypto/signal/[i:botid]',  array('c' => 'signal', 'a' => 'index'));
        //$router->map('GET|POST', '/signal/[i:botid]',  array('c' => 'signal', 'a' => 'index'));
        //$router->map('GET|POST', '/games/[i:botid]',  array('c' => 'games', 'a' => 'index'));

        return $router->match();

    }
}
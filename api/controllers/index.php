<?php

class Index extends Controller {

    function __construct() {
        parent::__construct();
         // ApiAuth::handleLogin();   # Uncomment to secure the api.
    }
    
    function index() {
        echo '..api..';
    }
	
    

}
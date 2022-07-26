<?php

class Dev extends Controller {

    function __construct() {
        parent::__construct();
       // echo $ this->view->PartialView('menu');
    }


    function index() {
        echo "Dev Page";
    }

    function test($id='', $param2='', $param3='') {
        echo 'testing Dev -' . $id ;
        echo '<br>Param 2 - ' . $param2 ;
        echo '<br>Param 3 - ' . $param3 ;
    }

}
<?php

class Index extends Controller {

    function __construct() {
        parent::__construct();
    }

    function index() {
        echo 'Dashboard working.. ';

        $this->view->title = COMPANY;
       // $this->view->render(__CLASS__ .'/'. __FUNCTION__);
        $this->view->render('index/index');

    }

    function test($param) {
        echo "Test Param: {$param}";
    }

}
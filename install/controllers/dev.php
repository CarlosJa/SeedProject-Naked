<?php

class Dev extends Controller {

    function __construct() {
        parent::__construct();
    }

    function index() {
        $this->view->title = COMPANY;
        $this->view->render('index/index');
    }

    function requirements(){
        echo "requirements";
    }
}
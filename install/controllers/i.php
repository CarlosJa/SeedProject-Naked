<?php

class I extends Controller {

    function __construct() {
        parent::__construct();
    }

    function index() {
        die();
    }

    function requirements(){
        $this->view->title = COMPANY;
        // $this->view->render(__CLASS__ .'/'. __FUNCTION__);
        $this->view->render('index/requirements');
    }

    function setup(){
        $this->view->title = COMPANY;
        // $this->view->render(__CLASS__ .'/'. __FUNCTION__);
        $this->view->render('index/setup');
    }
}
<?php

class I extends Controller {

    function __construct() {
        parent::__construct();

        $this->Styles['commenta'] = '<!-- Css files -->';
        $this->view->Styles = $this->Styles;

        $this->JavaScript[] = "/install/assets/jquery.min.js";
        $this->JavaScript[] = "/install/assets/custom.js";
        $this->view->JavaScript = $this->JavaScript;

    }

    function index() {
        die();
    }

    function requirements(){
        $this->view->render('index/requirements');
    }

    function setup(){
        $this->view->render('index/setup');
    }

    function complete(){
        $this->view->render('index/complete');
    }



}
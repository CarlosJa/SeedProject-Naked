<?php

class Index extends Controller {

    function __construct() {
        parent::__construct();
       // echo $ this->view->PartialView('menu');
    }


    function index() {

        $this->view->title = 'Home' . COMPANY;
        $this->view->Menu =  $this->view->PartialView('menu');

        $this->JavaScript[] = ASSETS . "/js/pages/crypto-dashboard.init.js";
        $this->view->JavaScript = $this->JavaScript;

        $this->Styles['commenta'] = '<!-- Bots Page -->';
        $this->view->Styles = $this->Styles;

        $this->view->render('index/index');
    }



}
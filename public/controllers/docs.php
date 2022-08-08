<?php

class Docs extends Controller {

    function __construct() {
        parent::__construct();
       // echo $ this->view->PartialView('menu');


        $this->Styles['commenta'] = '<!-- Css files -->';
        $this->view->Styles = $this->Styles;

        $this->JavaScript[] = ASSETS . "js/bootstrap.bundle.min.js";
        $this->JavaScript[] = ASSETS . "js/jquery.min.js";
        $this->JavaScript[] = ASSETS . "js/custom.js";
        $this->view->JavaScript = $this->JavaScript;
    }


    function index() {
        $this->Styles['commenta'] = '<!-- Bots Page -->';
        $this->view->Styles = $this->Styles;

        $this->view->render('docs/index');
    }



}
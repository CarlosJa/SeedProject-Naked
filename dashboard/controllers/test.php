<?php

class Test extends Controller {

    function __construct() {
        parent::__construct();
    }

    function index() {
        echo 'Test Controller working.. ';

        $this->view->title = COMPANY;
        // $this->view->render(__CLASS__ .'/'. __FUNCTION__);
        $this->view->render('test/index');

    }
}
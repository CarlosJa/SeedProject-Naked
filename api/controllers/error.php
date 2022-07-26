<?php

class _Error extends Controller {

    function __construct() {
        parent::__construct(); 
    }
    
    function index() {
		header('Content-Type: application/json');
		$ErrorMessage['Code']='404';
		$ErrorMessage['Message']='Not Found or Invalid Request';
		echo json_encode($ErrorMessage);
    }

}
<?php

class Controller {

    function __construct() {
        $this->view = new View();
        $this->view->_c = $this;
    }
    
    /**
     * 
     * @param string $name Name of the model
     * @param string $path Location of the models
     */
    public function loadModel($name, $modelPath = 'models/') {
        
        $path = $modelPath . $name.'_model.php';
        
        if (file_exists($path)) {
            require $modelPath .$name.'_model.php';
            
            $modelName = $name . '_Model';
            $this->model = new $modelName();
            $this->view->_m = $this->model;  // extends [model] access to views

        }        
    }

}
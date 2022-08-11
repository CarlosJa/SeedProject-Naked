<?php

class View {

    function __construct() {
        //echo 'this is the view';
        $this->path = VIEWS_PATH . '/views/';
    }

    public function render($name, $type='site', $noInclude = true) {
        if(empty($type)) { $type = 'site'; }

        $name = strtolower($name);

        if ($noInclude == false) {
            require $this->path . "" . $name . ".php";
        } else {
            include $this->path . "wrapper/{$type}/header.php";
            require $this->path . $name . ".php";
            include $this->path . "wrapper/{$type}/footer.php";
        }
    }


    /**
     * generates partial view. Good for straight JSON or XML responses for internal views.
     * @param $name
     * @return string
     */
    function PartialView($name, $param=false)
    {
        ob_start();
        include ($this->path . "partial/" . $name . ".php");
        $name = ob_get_clean();

        return ($name);
        ob_end_flush();
    }

    function js($data){
        print_array($data);
    }

    /*
         public function render($name) {
            require 'views/' . $name . '.php';
        }
    */

}
<?php




class _Error extends Controller {

    function __construct() {
        parent::__construct();
    }
	
	function index(){
        $this->Styles['commenta'] = '<!-- Css files -->';
        $this->view->Styles = $this->Styles;

        $this->JavaScript[] = ASSETS . "/js/pages/crypto-dashboard.init.js";
        $this->view->JavaScript = $this->JavaScript;

        $this->view->title = COMPANY;
        $this->view->render(__CLASS__ .'/'. __FUNCTION__);
	}
	
	
} // end class 
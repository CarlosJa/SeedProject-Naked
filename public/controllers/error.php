<?php




class _Error extends Controller {

    function __construct() {
        parent::__construct();
    }
	
	function index(){
		$this->view->title = COMPANY;
        $this->view->render(__CLASS__ .'/'. __FUNCTION__);
	}
	
	
} // end class 
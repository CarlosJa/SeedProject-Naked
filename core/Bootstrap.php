<?php

require_once 'ErrorHandler.php';  // Make sure to include the ErrorHandler class

class Bootstrap {

    private $_url = null;
    private $_controller = null;
    private $_controllerPath = 'controllers/'; // Always include trailing slash
    private $_modelPath = 'models/'; // Always include trailing slash
    private $_errorFile = 'error.php';
    private $_defaultFile = 'index.php';
    private $_defaultPath = 'public';


    /**
     * Starts the Bootstrap
     *
     * @return boolean
     */

    public function __construct(){
        set_error_handler([ErrorHandler::class, 'handleError']);
        set_exception_handler([ErrorHandler::class, 'handleException']);
        register_shutdown_function([ErrorHandler::class, 'handleShutdown']);

        // Turn off display_errors if you want to handle all error display through this mechanism
        ini_set('display_errors', 'Off');
    }

    public function init() {
        // Sets the protected $_url
        $this->_getUrl();



        // Load the default controller if no URL is set
        // eg: Visit http://localhost it loads Default Controller
        if (empty($this->_url[0])) {
            $this->_loadDefaultController();
            return false;
        }

        //Router
        $this->match = \Router::Routing();



        if(DEBUG == true) {
            register_shutdown_function(function () {
                $err = error_get_last();
                if (! is_null($err)) {
                    print 'Error#'.$err['message'].'<br>';
                    print 'Line#'.$err['line'].'<br>';
                    print 'File#'.$err['file'].'<br>';
                }
            });
        }


        // This check whether there is a match         
        if (empty($this->match)) {
            $this->_loadExistingController();
            $this->_callControllerMethod();
        } else {
            $this->_loadRouter();
        }

        //$this->_loadExistingController();
        //$this->_callControllerMethod();
    }

    /**
     * (Optional) Set a custom path to controllers
     * @param string $path
     */
    public function setControllerPath($path ='') {
        \Helper::print_array($path);
        $this->_controllerPath = trim($path, '') . '';
    }

    /**
     * (Optional) Set a custom path to models
     * @param string $path
     */
    public function setModelPath($path ='') {
        $this->_modelPath = trim($path, '') . '';
    }

    /**
     * (Optional) Set a custom path to the error file
     * @param string $path Use the file name of your controller, eg: error.php
     */
    public function setErrorFile($path ='') {
        $this->_errorFile = trim($path, '/');
    }

    /**
     * (Optional) Set a custom path to the error file
     * @param string $path Use the file name of your controller, eg: index.php
     */
    public function setDefaultFile($path = '') {
        $this->_defaultFile = trim($path, '/');
    }


    /**
     * (Optional) Set a custom path to the error file
     * @param string $path Use the file name of your controller, eg: index.php
     */
    public function setDefaultPath($path = '') {
        $this->_defaultPath = trim($path, '');  // Removed the trim for /
    }



    /**
     * Fetches the $_GET from 'url'
     */
    private function _getUrl() {
        $url = isset($_GET['url']) ? $_GET['url'] : null;
        $url = rtrim($url, '/');
        $url = filter_var($url, FILTER_SANITIZE_URL);
        $this->_url = explode('/', $url);
    }

    /**
     * This loads if there is no GET parameter passed
     */
    private function _loadDefaultController() {
        require $this->_defaultPath . '/' . $this->_controllerPath . $this->_defaultFile;
        $this->_controller = new Index();
        $this->_controller->index();
    }

    /**
     * Load an existing controller if there IS a GET parameter passed
     *
     * @return boolean|string
     */
    private function _loadExistingController() {
        $file = $this->_defaultPath . '/' . $this->_controllerPath . $this->_url[0] . '.php';


        if (file_exists($file)) {
            require $file;
            if($this->_url[1]) { $method = $this->_url[1]; } else { $method = "index"; }
            $this->_controller = new $this->_url[0]($method);
            $this->_controller->loadModel($this->_url[0], $this->_modelPath);
        } else {
            $this->_error();
            return false;
        }
    }

    /**
     * Loads Router if there's a rule set for specific URL combinate
     * @return boolean
     */
    private function _loadRouter() {
        /// Run the Router


        $this->ControllerName = $this->match['target']['c'];
        $this->MethodName = $this->match['target']['a'];
        $this->URIParameters = $this->match['params'];


        $file = $this->_defaultPath . '/' . $this->_controllerPath . $this->ControllerName . '.php';

        if (file_exists($file)) {
            require $file;

            $this->_controller = new $this->ControllerName($this->MethodName);
            $this->_controller->loadModel($this->ControllerName, $this->_modelPath);
        } else {
            $this->_error();
            return false;
        }

        // Load Controller
        $this->_controller->{$this->MethodName}($this->URIParameters);
    }

    /**
     * If a method is passed in the GET url paremter
     *
     *  http://localhost/controller/method/(param)/(param)/(param)
     *  url[0] = Controller
     *  url[1] = Method
     *  url[2] = Param
     *  url[3] = Param
     *  url[4] = Param
     */
    private function _callControllerMethod() {
        $length = count($this->_url);

        // Make sure the method we are calling exists
        if ($length > 1) {
            if (!method_exists($this->_controller, $this->_url[1])) {
                $this->_error();
            }
        }

        // Determine what to load
        switch ($length) {
            case 5:
                //Controller->Method(Param1, Param2, Param3)
                $this->_controller->{$this->_url[1]}($this->_url[2], $this->_url[3], $this->_url[4]);
                break;

            case 4:
                //Controller->Method(Param1, Param2)
                $this->_controller->{$this->_url[1]}($this->_url[2], $this->_url[3]);
                break;

            case 3:
                //Controller->Method(Param1, Param2)
                $this->_controller->{$this->_url[1]}($this->_url[2]);
                break;

            case 2:
                //Controller->Method(Param1, Param2)
                $this->_controller->{$this->_url[1]}();
                break;

            default:
                $this->_controller->index();
                break;
        }
    }

    /**
     * Display an error page if nothing exists
     *
     * @return boolean
     */
    private function _error() {
        require $this->_defaultPath . '/' . $this->_controllerPath . $this->_errorFile;
        $this->_controller = new _Error();
        $this->_controller->index();
        exit;
    }

}
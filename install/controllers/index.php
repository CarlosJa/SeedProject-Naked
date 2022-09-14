<?php

class Index extends Controller {

    function __construct() {
        parent::__construct();
    }

    function index() {
        $this->view->render(__CLASS__ .'/'. __FUNCTION__);
    }

    function checkDB(){
       // \Helper::print_array($_POST);

        $host = $_POST['dbloca'];
        $user = $_POST['dbuser'];
        $pass = $_POST['dbpass'];
        $db =   $_POST['dbname'];
        $charset = 'utf8';


        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_LAZY,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, $user, $pass, $options);
            echo 1;
        } catch (\PDOException $e) {
            echo 0;
        }
    }

    function installation() {
      //  \Helper::print_array($_POST);
        if(!$_POST) {
            header("Location: /install/");
            die();
        };

        $dbLoca = $_POST['dbloca'];
        $dbName = $_POST['dbname'];
        $dbUser = $_POST['dbuser'];
        $dbPass = $_POST['dbpass'];


        # Step 1: building out database structure
        $SQLFile = $_SERVER['DOCUMENT_ROOT'] . '/install/dump.sql';
        $ConfigFile = $_SERVER['DOCUMENT_ROOT'] . '/config.php';


        $HashKey = $this->getName(50);
        $HashAPIKey = $this->getName(50);



        if(!file_exists($SQLFile) ){
            die('Error: Failed to Load SQL Dump File. ');
        }

       $sql = file_get_contents($SQLFile);
       $mysqli = new mysqli($dbLoca, $dbUser, $dbPass, $dbName);

        /* check connection */
        if ($mysqli->connect_errno) {
            printf("Connect failed: %s\n", $mysqli->connect_error);
            exit();
        }

        if (!$mysqli->multi_query($sql)) {
            printf("Error message: %s\n", $mysqli->error);
        };

        /* close connection */
        $mysqli->close();

        # Step 2: Create the Config file.
     //   if(file_exists($ConfigFile)) { die ('File Currently Exist. Please Delete config.php; if you are trying to do a new install.'); }

        $myfile = fopen($ConfigFile, "w") or die("Unable to Write or Open file, Please check your permissions!");
        fwrite($myfile, '');
        fclose($myfile);


        $config_content =
            <<<SEED
<?php

define('URL', 'https://www.seedproject.com/');
define('SITE_BASE', '/');
define('ASSETS', '/public/assets/');
define('LIBS', 'core/');
define('COMPANY', 'CryptoBot - by CarlosArias.com');
define('DEBUG', false);

define('EMAILUSER', 'ADDEMAILUSER');
define('EMAILPASSWORD', 'ADDEMAILPASSWORD');
define('EMAILHOST', 'ADDEMAILHOST');

define('DB_TYPE', 'mysql');
define('DB_HOST', '{$dbLoca}');
define('DB_NAME', '{$dbName}');
define('DB_USER', '{$dbUser}');
define('DB_PASS', '{$dbPass}');

define( 'HASH_PASSWORD_KEY',    '{$HashKey}');
define( 'HASH_API_KEY',         '{$HashAPIKey}');
define( 'TIMESTAMP',            date('Y-m-d H:i:s'));
date_default_timezone_set('America/New_York');

\Db::setConnectionInfo(DB_TYPE, DB_NAME, DB_USER, DB_PASS);
SEED;

       file_put_contents($ConfigFile, $config_content);

       header("Location: /install/i/complete");

    }



    function getName($n) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()-=_+<>?;:\/';
        $randomString = '';

        for ($i = 0; $i < $n; $i++) {
            $index = rand(0, strlen($characters) - 1);
            $randomString .= $characters[$index];
        }

        return $randomString;
    }


}
<?php
$dir = strtok(parse_url($_SERVER['SCRIPT_URI'], PHP_URL_PATH), '/');

define('URL', 'https://www.seedproject.com');
define('SITE_BASE', '/' . $dir);
define('ASSETS', '/' . $dir . '/assets');
define('LIBS', 'core/');
define('PROJECT_NAME', 'SeedProject');
define('PROJECT_LOGO', '/assets/SeedProject.png');
define('DEBUG', false);
define('SECUREAPI', false);

define('EMAILUSER', 'ADDEMAILUSER');
define('EMAILPASSWORD', 'ADDEMAILPASSWORD');
define('EMAILHOST', 'ADDEMAILHOST');

define('GOOGLE_CLIENT_SECRET', 'SECRET_KEY_HERE');
define('GOOGLE_CLIENT_ID',     'CLIENT_KEY_HERE');


define( 'HASH_PASSWORD_KEY',    'G%@xZY+-5% %5:+pK#1IUzM9l}3.]PsSJ=d.^SlD{+mQYVxK4eP_fZT->nRFl@t,');
define( 'HASH_API_KEY',         ':-?7>?@[TP=oor}~<YU#EE,_dq2uwH{Zc4o0fg7*TI|rDe>BY 6UaJ!Vp$6Bw6tD');
define( 'TIMESTAMP',            date('Y-m-d H:i:s'));
date_default_timezone_set('America/New_York');

define('DB_TYPE', 'mysql');
define('DB_HOST', 'LOCALHOST');
define('DB_NAME', 'DBNAME');
define('DB_USER', 'DBUSER');
define('DB_PASS', 'DBPASSWORD');

\Db::setConnectionInfo(
    DB_TYPE,
    DB_NAME,
    DB_USER,
    DB_PASS,
    DB_HOST,
    'snoopi2024'
);

\Db::setConnectionInfo(DB_TYPE, DB_NAME, DB_USER, DB_PASS);
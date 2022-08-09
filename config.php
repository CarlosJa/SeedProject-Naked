<?php
define('URL', 'https://www.seedproject.com/');
define('SITE_BASE', '/');
define('ASSETS', '/public/assets/');
define('LIBS', 'core/');
define('COMPANY', 'CryptoBot - by CarlosArias.com');
define('DEBUG', true);

define('EMAILUSER', 'ADDEMAILUSER');
define('EMAILPASSWORD', 'ADDEMAILPASSWORD');
define('EMAILHOST', 'ADDEMAILHOST');

define('DB_TYPE', 'mysql');
define('DB_HOST', 'localhost');
define('DB_NAME', '######');
define('DB_USER', '######');
define('DB_PASS', '######');

define( 'HASH_PASSWORD_KEY',    'G%@xZY+-5% %5:+pK#1IUzM9l}3.]PsSJ=d.^SlD{+mQYVxK4eP_fZT->nRFl@t,');
define( 'HASH_API_KEY',         ':-?7>?@[TP=oor}~<YU#EE,_dq2uwH{Zc4o0fg7*TI|rDe>BY 6UaJ!Vp$6Bw6tD');
define( 'TIMESTAMP',            date('Y-m-d H:i:s'));
date_default_timezone_set('America/New_York');

\Db::setConnectionInfo(DB_TYPE, DB_NAME, DB_USER, DB_PASS);
<?php
@session_start();
ini_set('display_errors', 0);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
require $_SERVER['DOCUMENT_ROOT'] . '/config.php';

define('VIEWS_PATH', __DIR__);

$bootstrap = new Bootstrap();
$bootstrap->setDefaultPath(__DIR__);
$bootstrap->init();


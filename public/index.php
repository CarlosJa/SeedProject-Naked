<?php
@session_start();
require $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
require $_SERVER['DOCUMENT_ROOT'] . '/config.php';

define('VIEWS_PATH', __DIR__);


$bootstrap = new Bootstrap();
// $bootstrap->setControllerPath(__DIR__ . '/controllers/');
$bootstrap->init();


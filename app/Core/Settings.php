<?php namespace App\Core;


Class Settings {


    public function __construct() {

    }

    public static function Exchanges() {
        $getExDB = \Db::getResult('select * from exchanges');
        \Helper::print_array($getExDB);

    }
}

?>
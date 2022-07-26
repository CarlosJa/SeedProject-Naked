<?php

class Model {

    function __construct() {
        $this->db = new Database(DB_TYPE, DB_HOST, DB_NAME, DB_USER, DB_PASS);
        $this->db->setAttribute( PDO::ATTR_EMULATE_PREPARES, false );


        //$this->remotedb = new Database(DB_TYPE_REMOTE, DB_HOST_REMOTE, DB_NAME_REMOTE, DB_USER_REMOTE, DB_PASS_REMOTE);
        //$this->remotedb->setAttribute( PDO::ATTR_EMULATE_PREPARES, false );

    }
}
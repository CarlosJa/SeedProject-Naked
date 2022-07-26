<?php namespace App\Core;

use Model as Model;


class CRUD extends Model
{

    public function __construct()
    {
        parent::__construct();
    }

    public function Select($query) {
        $Return = $this->db->select($query);
        return $Return;
    }

    /* Generic Create */
    public function Insert($table, $data) {
        $Response = $this->db->insert($table, $data);
        return $Response;
    }

    /* Generic Update */
    public function Update($table, $data, $identifier) {
        $Return = $this->db->update($table, $data, $identifier);
        return $Return;
    }

    public function rawquery($str) {
        $Return = $this->db->query($str);
        return $Return;

    }

}
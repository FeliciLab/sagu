<?php
class MSchema
{
    public $conn; // connection identifier
    public $_miolo;     // MIOLO object

    public function __construct($conn)
    {
        $this->conn = $conn;
        $this->_miolo = MIOLO::getInstance();
    }

    // Virtual methods - to be implemented by the specific drivers
    public function getTableInfo($tablename)
    {
    }
}
?>
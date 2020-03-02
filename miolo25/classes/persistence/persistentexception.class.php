<?php

class EPersistenceException extends EMioloException
{
    public function __construct($msg)
    {
        parent::__construct();
        $this->message = $msg;
    }
}

class EPersistentManagerFactoryException extends EPersistenceException
{
    public function __construct($msg)
    {
        $msg = "Error in PersistenManagerFactory: " . $msg;
        parent::__construct($msg);
    }
}

class EPersistentManagerException extends EPersistenceException
{
    public function __construct($msg)
    {
        $msg = "Error in PersistenFactory: " . $msg;
        parent::__construct($msg);
    }
}

?>

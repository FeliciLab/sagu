<?php
class MTransaction
{
    public $conn; // connection identifier
    public $commands = array(); // commands to execute
    public $level; // a counter for the transaction level
    public $error;
    public $_miolo;     // MIOLO object
    public $batch = true; // execute commands in batch mode

    public function __construct($conn)
    {
        $this->conn = $conn;
        $this->level = 0;
        $this->_miolo = MIOLO::getInstance();
    }

    // Virtual methods - to be implemented by the specific drivers
    public function _begintransaction()
    {
    }

    public function _commit()
    {
    }

    public function _rollback()
    {
    }

    public function begin()
    {
        $this->_miolo->logSQL("Begin Transaction", false, $this->conn->db->conf);
        $this->_begintransaction();
    }

    public function commit()
    {
        $this->_commit();
        $this->_miolo->logSQL("End Transaction - Commit", false, $this->conn->db->conf);
    }

    public function rollback()
    {
        $this->_rollback();
        $this->_miolo->logSQL("End Transaction - Rollback", false, $this->conn->db->conf);
    }

    public function process()
    {
        if (!$this->batch)
        {
            $this->conn->getError() ? $this->rollback() : $this->commit();
            return;
        }
        try
        {
            $this->begin();
            $this->level++;
            $this->error = '';
            $i = 0;
            $n = count($this->commands);
            try
            {
                while ($i < $n)
                {
                    $sql = $this->commands[$i++];
                    if (is_array($sql))
                    {
                        foreach($sql as $data)
                        {
                            $data[0]->handleTypedAttribute($data[1],$data[2],$data[3]);
                        }
                    }
                    else
                    {
                        $this->conn->execute($sql);
                    }
                }
                $this->commit();
                $this->level--;
            }
            catch( Exception $e )
            {
                $this->rollback();
                $this->level--;
                $this->error = $e->getMessage();
                throw new EDatabaseTransactionException($e->getMessage());
            }
        }
        catch( Exception $e )
        {
            throw new EDatabaseTransactionException($e->getMessage());
        }

        return $ok;
    }

    public function addCommand($sql)
    {
        $this->commands[] = $sql;
    }

    public function getError()
    {
        return $this->error;
    }

    public function setBatch($mode = true)
    {
        $this->batch = $mode;
        if (!$mode) $this->begin();
    }

    public function isBatch()
    {
        return $this->batch;
    }
}
?>
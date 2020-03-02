<?php

class ODBCTransaction extends MTransaction
{
    public function _begintransaction()
    {
        odbc_autocommit($this->conn->id, FALSE);
    }

    public function _commit()
    {
        odbc_commit($this->conn->id);
    }

    public function _rollback()
    {
        odbc_rollback($this->conn->id);
    }
}
?>

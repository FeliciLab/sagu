<?php
/**
 * Brief Class Description.
 * Complete Class Description.
 */
class MSSQLTransaction extends MTransaction
{
    /**
     * Brief Description.
     * Complete Description.
     *
     * @returns (tipo) desc
     *
     */
    public function _begintransaction()
    {
        $this->conn->_execute("begin transaction");
    }

    /**
     * Brief Description.
     * Complete Description.
     *
     * @returns (tipo) desc
     *
     */
    public function _commit()
    {
        $this->conn->_execute("commit work");
    }

    /**
     * Brief Description.
     * Complete Description.
     *
     * @returns (tipo) desc
     *
     */
    public function _rollback()
    {
        $this->conn->_execute("rollback work");
    }
}
?>

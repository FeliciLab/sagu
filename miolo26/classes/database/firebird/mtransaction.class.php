<?php
/**
 * Brief Class Description.
 * Complete Class Description.
 */
class FirebirdTransaction extends MTransaction
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
        ibase_trans(IBASE_COMMITED, $this->conn->id);
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
        ibase_commit ($this->conn->id);
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
        ibase_rollback ($this->conn->id);
    }
}
?>

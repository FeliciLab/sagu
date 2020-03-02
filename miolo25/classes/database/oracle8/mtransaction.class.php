<?php
/**
 * Brief Class Description.
 * Complete Class Description.
 */
class Oracle8Transaction extends MTransaction
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
        $this->conn->executemode = OCI_DEFAULT; // AutoCommit = false
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
        OCICommit($this->conn->id);
        $this->conn->executemode = OCI_COMMIT_ON_SUCCESS;
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
        OCIRollback($this->conn->id);
        $this->conn->executemode = OCI_COMMIT_ON_SUCCESS;
    }
}
?>

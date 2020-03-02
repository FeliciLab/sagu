<?php
/**
 * Brief Class Description.
 * Complete Class Description.
 */
class SQLiteTransaction extends MTransaction
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
        sqlite_exec($this->conn->id, 'BEGIN');
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
        sqlite_exec($this->conn->id, 'COMMIT');
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
        sqlite_exec($this->conn->id, 'ROLLBACK');
    }
}
?>

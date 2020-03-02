<?php
/**
 * Brief Class Description.
 * Complete Class Description.
 */
class MSSQLIdGenerator extends MIdGenerator
{
    /**
     * Brief Description.
     * Complete Description.
     *
     * @param $sequencecommon' (tipo) desc
     * @param $tableGenerator (tipo) desc
     * @param = (tipo) desc
     * @param 'cm_sequence' (tipo) desc
     *
     * @returns (tipo) desc
     *
     */
    public function getNewId($sequence = 'admin', $tableGenerator = 'cm_sequence')
    {
        $this->value = $this->getNextValue($sequence);
        return $this->value;
    }

    /**
     * Brief Description.
     * Complete Description.
     *
     * @param $sequencecommon' (tipo) desc
     * @param $tableGenerator (tipo) desc
     * @param = (tipo) desc
     * @param 'cm_sequence' (tipo) desc
     *
     * @returns (tipo) desc
     *
     */
    public function getNextValue($sequence = 'admin', $tableGenerator = 'cm_sequence')
    {
        $value = 0;
        $sp = mssql_init("NextVal", $this->db->conn->id); // stored proc name
        mssql_bind($sp, "@sequenceName", stripslashes($sequence), SQLVARCHAR, false, false, 100);
        mssql_bind($sp, "@nextVal", &$value, SQLINT4, true, false);
        mssql_execute($sp);
        return $value;
    }
}
?>

<?php
/**
 * Brief Class Description.
 * Complete Class Description.
 */
class Oracle8Schema extends MSchema
{
    /**
     * Brief Description.
     * Complete Description.
     *
     * @returns (tipo) desc
     *
     */
    public function _gettableinfo($tablename)
    {
        $sql = new sql("column_name,data_type,data_length,data_precision,data_scale", "cols","(upper(table_name) = upper($tablename))");
        $query = @$this->conn->getQuery($sql);
        return $query->result;
    }
}
?>

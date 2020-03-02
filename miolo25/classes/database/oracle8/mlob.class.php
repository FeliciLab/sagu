<?php
class Oracle8LOB extends MLOB
{
    function handle($object, $attribute, $value, $operation)
    {
        $classMap = $object->getClassMap();
        $statement = $classMap->getSelectSqlFor($object);
        $attributeMap = $classMap->getAttributeMap($attribute);
        $columnMap = $attributeMap->getColumnMap();
        $table = $columnMap->getTableMap()->getName();
        $column = $columnMap->getName();

        if (($operation == 'insert') || ($operation == 'update'))
        {
            $statement->setForUpdate(true);
            $query = $this->conn->_createquery();
            $query->setConnection($this->conn);
            $query->setSQL($statement);
            $stmt = oci_parse($this->conn->id, $query->sql);
            $exec = oci_execute($stmt, $this->conn->executemode);
            if (!$exec)
                throw new EDatabaseQueryException($this->_error());
            $row = oci_fetch_assoc($stmt);
            $column = strtoupper($column);
            $row[$column]->truncate();
            $row[$column]->save($value);
            oci_commit($this->conn->id);
            oci_free_statement($stmt);
            $row[$column]->free();
        }
        if (($operation == 'select')) // handled directly by oci_fetch_all (query)
        {

        }
    }

    function lobload($value)
    {
        return $value;
    }

    function lobsave($value)
    {
        return ':EMPTY_BLOB()';
    }

}
?>
<?php

class ODBCIdGenerator extends MIdGenerator
{
    private $tableGenerator = "cm_sequence";

    public function getNewId($sequence = 'admin', $tableGenerator = 'cm_sequence')
    {
        $this->value = $this->getNextValue($sequence, $tableGenerator);
        return $this->value;
    }

    public function getNextValue($sequence = 'admin', $tableGenerator = 'cm_sequence')
    {
        $sql = new sql("value", $tableGenerator, "sequence='$sequence'");
        $query = $this->db->getQuery($sql);
        $value = $query->fields('value');
        $sql = new sql("value", $tableGenerator, "sequence='$sequence'");
        $value++;
        $this->db->execute($sql->update("$value"));
        return $value;
    }
}
?>

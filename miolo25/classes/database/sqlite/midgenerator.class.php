<?php
/**
 * Brief Class Description.
 * Complete Class Description.
 */
class SQLiteIdGenerator extends MIdGenerator
{
    /**
     * Attribute Description.
     */
    private $tableGenerator = "cm_sequence";

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
        $this->value = $this->getNextValue($sequence, $tableGenerator);
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
        $sql = new msql("value", $tableGenerator, "sequence='$sequence'");
        $query = $this->db->getQuery($sql);
        $value = $query->fields('value');
        $sql = new msql("value", $tableGenerator, "sequence='$sequence'");
        $value++;
        $this->db->execute($sql->update("$value"));
        return $value;
    }
}
?>

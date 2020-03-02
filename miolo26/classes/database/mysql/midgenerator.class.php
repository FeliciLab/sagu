<?php
/**
 * Brief Class Description.
 * Complete Class Description.
 */
class MysqlIdGenerator extends MIdGenerator
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
        $sql = new sql('value', 'id', "(sequence = '$sequence')");
        $query = $this->db->getQuery($sql);
        $value = $query->fields('value');
        $nextValue = $value + 1;
        $this->db->execute($sql->update($nextValue));
        return $value;
    }
}
?>

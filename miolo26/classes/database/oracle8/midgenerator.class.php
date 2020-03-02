<?php
/**
 * Brief Class Description.
 * Complete Class Description.
 */
class Oracle8IdGenerator extends MIdGenerator
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
        $sql = new MSQL($sequence . '.nextval as value', 'dual');
        $query = $this->db->getQuery($sql);
        $value = $query->fields('value');
        return $value;
    }
}
?>

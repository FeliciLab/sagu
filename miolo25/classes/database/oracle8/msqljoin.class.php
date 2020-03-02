<?
/**
 * Brief Class Description.
 * Complete Class Description.
 */
class Oracle8SqlJoin extends MSqlJoin
{
    /**
     * Brief Description.
     * Complete Description.
     *
     * @param $sql (tipo) desc
     *
     * @returns (tipo) desc
     *
     */
    public function _sqlJoin($sql)
    {
        foreach ($sql->join as $join)
        {
            $sql->setTables("$join[0], $join[1]");
            $cond = $join[2];

            if ($join[3] == 'RIGHT')
            {
                $tok = strtok($cond, "=");
                $cond = $tok . " = " . strtok("=") . "(+)";
            }
            elseif ($join[3] == 'LEFT')
            {
                $tok = strtok($cond, "=");
                $cond = $tok . "(+) = " . strtok("=");
            }

            $sql->setWhere("($cond)");
        }
    }
}
?>

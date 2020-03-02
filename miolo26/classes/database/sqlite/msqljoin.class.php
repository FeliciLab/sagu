<?
/**
 * Brief Class Description.
 * Complete Class Description.
 */
class SQLiteSqlJoin extends MSqlJoin
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
        $cond = '';

        foreach ($sql->join as $join)
        {
            if ($cond != '')
            {
                $cond = "$cond " . $join[3] . " JOIN $join[1] ON ($join[2])";
            }
            else
            {
                $cond = "$join[0] " . $join[3] . " JOIN $join[1] ON ($join[2])";
            }
        }

        $sql->setTables($cond);
    }
}
?>

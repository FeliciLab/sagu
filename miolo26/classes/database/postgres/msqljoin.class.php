<?
/**
 * Brief Class Description.
 * Complete Class Description.
 */
class PostgresSqlJoin extends MSqlJoin
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

        if(is_array($sql->join))
        {
            foreach ($sql->join as $join)
            {
                if ($cond != '')
                {
                    $cond = "($cond " . $join[3] . " JOIN $join[1] ON ($join[2]))";
                }
                else
                {
                    $cond = "($join[0] " . $join[3] . " JOIN $join[1] ON ($join[2]))";
                }
            }
        }
        else
        {
            $cond = $sql->join;
        }

        $sql->setTables($cond);
    }
}
?>

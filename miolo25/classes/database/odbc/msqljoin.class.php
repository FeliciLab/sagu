<?
class ODBCSqlJoin extends MSqlJoin
{

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
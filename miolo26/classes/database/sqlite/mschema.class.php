<?php
/**
 * Brief Class Description.
 * Complete Class Description.
 */
class SQLiteSchema extends MSchema
{
    /**
     * Brief Description.
     * Complete Description.
     *
     * @returns (tipo) desc
     *
     */
    public function getTableInfo($tablename)
    {
        $query = $this->conn->getQueryCommand("SELECT sql FROM (SELECT * FROM sqlite_master UNION ALL SELECT * FROM sqlite_temp_master) WHERE tbl_name LIKE '$tablename' AND type!='meta'");

        $exp = $query->result[0][0];
        $fields = "/([^(]*)\(([^)]*)/";
        preg_match($fields, $exp, $parts);
        $s = str_replace(')',' ',str_replace('(',' ',$parts[2]));
        $ff = explode(',',$s);
        foreach($ff as $f)
        {
           $r = sscanf($f, "%s%s%s");
           if (!is_numeric($r[2])) $r[2] = '';
           $res[] = array($r[0],$r[1],$r[2]);
        }
        return $res;
    }
}
?>
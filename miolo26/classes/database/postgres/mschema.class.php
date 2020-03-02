<?php
/**
 * Brief Class Description.
 * Complete Class Description.
 */
class PostgresSchema extends MSchema
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
        //select relfilenode from pg_class where relname = 'pacote';
        $sql = new msql("relfilenode", "pg_class","lower(relname)=?");
        $sql->setParameters( strtolower($tablename) );
        $q = $this->conn->db->query($sql->select());

        if($q[0][0])
        {
            $sql = new mSql('attname, atttypid, attinhcount', 'pg_attribute', 'attrelid=? AND attnum>0 AND atttypid > 0', 'attnum');
            $sql->setParameters($q[0][0]);
        }
        else return null;

        //select * from pg_attribute where attrelid = 964175 and attnum > 0
        $query = @$this->conn->getQuery($sql);
        return $query->result;
    }
}
?>

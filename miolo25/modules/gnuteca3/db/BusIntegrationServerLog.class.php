<?php

class BusinessGnuteca3BusIntegrationServerLog extends GBusiness
{
 
    public $integrationServerLogId;
    public $integrationServerid;
    public $materialsSynchronized;
    public $exemplariesSynchronized;
    public $date;
    
    function __construct()
    {
        $this->colsNoId = 'integrationServerLogId, 
                           libraryUnitId';
        
        $this->id = 'integrationServerLogId';
        $this->columns  = 'integrationServerLogId, ' . $this->colsNoId;
        $this->tables   = 'gtcIntegrationServerLog';
        
        parent::__construct();

    }

    public function insertIntegrationServerLog()
    {
        //Instancia objeto data, com os dados do formulário
        $data = array($this->integrationServerid,
                      $this->materialsSynchronized,
                      $this->exemplariesSynchronized,
                      $this->date);
        
        $this->clear();
        
        $this->setColumns('integrationServerid,
			   materialsSynchronized,
			   exemplariesSynchronized,
			   date');

        $this->setTables('gtcIntegrationServerLog');  

        $sql = $this->insert($data);
        

        $rs  = $this->query($sql);

        return $rs;
    }

    public function updateIntegrationServerLog()
    {
        $data = array($this->integrationServerLogId,
                      $this->integrationServerid,
                      $this->materialsSynchronized,
                      $this->exemplariesSynchronized,
                      $this->date);
        
        $this->clear();
        $this->setColumns($this->colsNoId);
        $this->setTables($this->tables);
        $this->setWhere('integrationServerLogId = ?');

        $sql = $this->update($data);
        
        $rs  = $this->execute($sql);

        return $rs;
    }

    public function deleteIntegrationServer($integrationServerLogId)
    {
        $this->clear();
        $tables  = 'gtcIntegrationServerLog';
        $where   = 'integrationServerLogId = ?';
        $data = array($integrationServerLogId);
        
        $this->setColumns($columns);
        $this->setTables($tables);
        $this->setWhere($where);
        $sql = $this->delete($data);
        
        $rs  = $this->execute($sql);
        
        return $rs;
    }

    public function getIntegrationServerLog($integrationServerLogId)
    {
        $data = array($integrationServerLogId);
        $this->clear();
        $this->setColumns('integrationServerLogId,
                           integrationServerid, 
                           materialsSynchronized,
                           exemplariesSynchronized,
                           date');
        
        $this->setTables('gtcIntegrationServerLog');
        
        $this->setWhere('integrationServerLogId = ?');
        
        $sql = $this->select($data);
        
        $rs  = $this->query($sql, true);
        
        $this->setData($rs[0]);
        
        return $this;
    }


    public function searchIntegrationServerLog($toObject = FALSE)
    {
        $this->clear();

        if ($this->integrationServerLogId)
        {
            $this->setWhere('integrationServerLogId = ?');
            $data[] = $this->integrationServerLogId;
        }
        
        if ($this->integrationServerid)
        {
            $this->setWhere('integrationServerid = ?');
            $data[] = $this->integrationServerid;
        }

        if ($this->materialsSynchronized)
        {
            $this->setWhere('materialsSynchronized = ?');
            $data[] = $this->materialsSynchronized;
        }
        
        if ($this->exemplariesSynchronized)
        {
            $this->setWhere('exemplariesSynchronized = ?');
            $data[] = $this->exemplariesSynchronized;
        }
        

        $this->setColumns('integrationServerLogId,
                           integrationServerid,
			   materialsSynchronized,
			   exemplariesSynchronized,
			   date');
        
        $this->setTables('gtcIntegrationServerLog');
        $this->setOrderBy('integrationServerLogId');
        $sql = $this->select($data);

        return $this->query($sql, ($toObject ? TRUE : FALSE));
    }


    public function listIntegrationServerLog()
    {
        $this->clear();
        $this->setColumns($this->fullColumns);
        $this->setTables($this->table);
        $sql = $this->select();
        $rs  = $this->query($sql);
        return $rs;
    }


    public function deleteIntegrationServerLog($integrationServerLogId)
    {
        
        $this->clear();
        $tables  = 'gtcIntegrationServerLog';
        $where   = 'integrationServerLogId = ?';
        $data = array($integrationServerLogId);
        
        $this->setColumns($columns);
        $this->setTables($tables);
        $this->setWhere($where);
        $sql = $this->delete($data);
        
        $rs  = $this->execute($sql);
        
        return $rs;
    }
}
?>
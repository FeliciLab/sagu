<?php

class BusinessGnuteca3BusIntegrationClientLog extends GBusiness
{
 
    public $integrationClientLogId;
    public $integrationClientid;
    public $materialsSynchronized;
    public $exemplariesSynchronized;
    public $date;
    
    function __construct()
    {
        $this->colsNoId = 'integrationClientLogId, 
                           libraryUnitId';
        
        $this->id = 'integrationClientLogId';
        $this->columns  = 'integrationClientLogId, ' . $this->colsNoId;
        $this->tables   = 'gtcIntegrationClientLog';
        
        parent::__construct();

    }

    public function insertIntegrationClientLog()
    {
        //Instancia objeto data, com os dados do formulário
        $data = array($this->integrationClientid,
                      $this->materialsSynchronized,
                      $this->exemplariesSynchronized,
                      $this->date);
        
        $this->clear();
        
        $this->setColumns('integrationClientid,
			   materialsSynchronized,
			   exemplariesSynchronized,
			   date');

        $this->setTables('gtcIntegrationClientLog');  

        $sql = $this->insert($data);
        

        $rs  = $this->query($sql);

        return $rs;
    }

    public function updateIntegrationClientLog()
    {
        $data = array($this->integrationClientLogId,
                      $this->integrationClientid,
                      $this->materialsSynchronized,
                      $this->exemplariesSynchronized,
                      $this->date);
        
        $this->clear();
        $this->setColumns($this->colsNoId);
        $this->setTables($this->tables);
        $this->setWhere('integrationClientLogId = ?');

        $sql = $this->update($data);
        
        $rs  = $this->execute($sql);

        return $rs;
    }

    public function deleteIntegrationClient($integrationClientLogId)
    {
        $this->clear();
        $tables  = 'gtcIntegrationClientLog';
        $where   = 'integrationClientLogId = ?';
        $data = array($integrationClientLogId);
        
        $this->setColumns($columns);
        $this->setTables($tables);
        $this->setWhere($where);
        $sql = $this->delete($data);
        
        $rs  = $this->execute($sql);
        
        return $rs;
    }

    public function getIntegrationClientLog($integrationClientLogId)
    {
        $data = array($integrationClientLogId);
        $this->clear();
        $this->setColumns('integrationClientLogId,
                           integrationClientid, 
                           materialsSynchronized,
                           exemplariesSynchronized,
                           date');
        
        $this->setTables('gtcIntegrationClientLog');
        
        $this->setWhere('integrationClientLogId = ?');
        
        $sql = $this->select($data);
        
        $rs  = $this->query($sql, true);
        
        $this->setData($rs[0]);
        
        return $this;
    }


    public function searchIntegrationClientLog($toObject = FALSE)
    {
        $this->clear();

        if ($this->integrationClientLogId)
        {
            $this->setWhere('integrationClientLogId = ?');
            $data[] = $this->integrationClientLogId;
        }
        
        if ($this->integrationClientid)
        {
            $this->setWhere('integrationClientid = ?');
            $data[] = $this->integrationClientid;
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

        $this->setColumns('integrationClientLogId,
                           integrationClientid,
			   materialsSynchronized,
			   exemplariesSynchronized,
			   date');
        
        $this->setTables('gtcIntegrationClientLog');
        $this->setOrderBy('integrationClientLogId');
        $sql = $this->select($data);
        
        return $this->query($sql, ($toObject ? TRUE : FALSE));
    }


    public function listIntegrationClientLog()
    {
        $this->clear();
        $this->setColumns($this->fullColumns);
        $this->setTables($this->table);
        $sql = $this->select();
        $rs  = $this->query($sql);
        return $rs;
    }


    public function deleteIntegrationClientLog($integrationClientLogId)
    {
        
        $this->clear();
        $tables  = 'gtcIntegrationClientLog';
        $where   = 'integrationClientLogId = ?';
        $data = array($integrationClientLogId);
        
        $this->setColumns($columns);
        $this->setTables($tables);
        $this->setWhere($where);
        $sql = $this->delete($data);
        
        $rs  = $this->execute($sql);
        
        return $rs;
    }
}
?>
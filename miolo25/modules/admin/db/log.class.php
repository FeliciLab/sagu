<?php

class BusinessCommonLog extends MBusiness
{
    public $idlog;
    public $idusuario;
	var $operacao;
    public $descricao;
    public $timestamp;
    public $modulo;
    public $classe;
    public $fmtTimeStamp;
    public $dataini;
    public $datafim;

    public function __construct($data=null)
    {
       parent::__construct('admin',$data);
       $this->fmtTimeStamp = $this->_db->timeStampToChar("timestamp");
       if ($data)
       {
          $this->setData($data);
       }
    }

	function setData($data)
	{
		$this->idlog = $data->idlog;
		$this->idusuario = $data->idusuario;
		$this->operacao = $data->operacao;
		$this->descricao = $data->descricao;
		$this->timestamp = $data->timestamp;
		$this->dataini = $data->dataini;
		$this->datafim = $data->datafim;
		$this->modulo = strtoupper($data->modulo);
		$this->classe = strtoupper($data->classe);
	}
 
    public function & sqlListSelection()
    {
        $sql = new sql("l.idlog, l.idusuario, l.operacao, l.descricao, $this->fmtTimeStamp as timestamp, l.modulo as modulo, l.classe as classe, u.login" ,'cm_log l, cm_usuario u', '(l.idusuario = u.idusuario)','l.timestamp DESC');
        $where = '';
        if ($this->modulo != '')
        {
           $where .= ' and (l.modulo = ?)';
           $args[] = $this->modulo;
        }
        if ($this->classe != '')
        {
           $where .= ' and (l.classe = ?)';
           $args[] = $this->classe;
        }
        if ($this->idusuario != '')
        {
           $where .= ' and (l.idusuario = ?)';
           $args[] = $this->idusuario;
        }
        if ($this->descricao != '')
        {
           $where .= " and ((upper(l.descricao) LIKE '%" . strtoupper($this->descricao) . "%')";
           $where .= " or (l.descricao) LIKE '%$this->descricao%')";
        }
        if ($this->dataini != '')
        {
           $where .= " and (l.timestamp >= " . $this->_db->charToDate("'$this->dataini'") . ")";
        }
        if ($this->datafim != '')
        {
           $where .= " and (l.timestamp <= " . $this->_db->charToDate("'$this->datafim'") . ")";
        }
        $sql->setWhere($where);
        $sql->setParameters($args);
        return $sql;
    }

    public function listByIdUsuario()
    {
        $sql = new sql("l.idlog, l.operacao, l.descricao, $this->fmtTimeStamp as timestamp, l.modulo, l.classe, u.login" ,'cm_log l, cm_usuario u', '(l.idusuario = u.idusuario) and (l.idusuario = ?)','l.timestamp DESC');
        $query = $this->query($sql, $this->idusuario);
        return $query;
    }

    public function listByModuloClasse()
    {
        $sql = new sql("l.idlog, l.idusuario, l.operacao, l.descricao, $this->fmtTimeStamp as timestamp, l.modulo, l.classe, u.login" ,'cm_log l, cm_usuario u', '(l.idusuario = u.idusuario)','l.timestamp DESC');
        $where = '';
        if ($this->modulo != '')
        {
           $where .= ' and (l.modulo = ?)';
           $args[] = $this->module;
        }
        if ($this->classe != '')
        {
           $where .= ' and (l.classe = ?)';
           $args[] = $this->classe;
        }
        $sql->setWhere($where);
        $sql->setParameters($args);
        $query = $this->query($sql);
        return $query;
    }

    public function listByDescricao()
    {
        $sql = new sql("l.idlog, l.idusuario, l.operacao, l.descricao, $this->fmtTimeStamp as timestamp, l.modulo, l.classe, u.login" ,'cm_log l, cm_usuario u', '(l.idusuario = u.idusuario)','l.timestamp DESC');
        $where = '';
        if ($this->descricao != '')
        {
           $where .= " and ((upper(l.descricao) LIKE '%" . strtoupper($this->descricao) . "%')";
           $where .= " or (l.descricao) LIKE '%$this->descricao%')";
        }
        $sql->setWhere($where);
        $query = $this->query($sql);
        return $query;
    }

    public function listSelection()
    {
        $query = $this->query($this->sqlListSelection());
        return $query;
    }

    public function insert()
    {   $MIOLO = MIOLO::getInstance();

        $this->idlog = $this->_db->getNewId('seq_cm_log');
        $timestamp = $MIOLO->getSysTime();
        $this->timestamp = $this->_db->charToTimeStamp("$timestamp");
        $sql = new sql('idlog, idusuario, operacao, descricao, timestamp, modulo, classe', 'cm_log');
        $args = array(
    		$this->idlog,
	    	$this->idusuario,
		    $this->operacao,
		    $this->descricao,
		    $this->timestamp,
		    strtoupper($this->modulo),
		    strtoupper($this->classe)
        );
        $ok = $this->execute($sql->insert($args));
        return $ok;
    }

    public function delete()
    {
        $sql = new sql('','cm_log', 'idlog = ?');
        $ok = $this->execute( $sql->delete($this->idlog) );
        return $ok;
    }

    public function listRange($range=NULL)
    {
        $sql = new sql("l.idlog, l.operacao, l.descricao, $this->fmtTimeStamp as timestamp, l.modulo, l.classe, u.login" ,'cm_log l, cm_usuario u', '(l.idusuario = u.idusuario)');
        $sql->setRange($range); 
        $query = $this->query($sql);
        return $query;
    }

	function listAll()
    {
		return $this->listRange();
    }

    public function countWhere($where='')
    {
        $sql = new sql('*','cm_log',$where);
        return $this->_db->count($sql->select());
    }

    public function log($operacao,$descricao,$idusuario,$modulo, $classe)
    {
    	$this->idusuario = $idusuario;
	    $this->operacao = $operacao;
	    $this->descricao = $descricao;
		$this->modulo = $modulo;
		$this->classe = $classe;
        return $this->insert();
    }

    public function listByClasse($classe)
    {
        $sql = "begin oracleufjf_pkg.sp_log(:classe,:cursor);end;";
        $p_classe = $classe;
        $args['classe'] = array(&$p_classe,20,null);
        $args['cursor'] = array(&$cursor,-1, OCI_B_CURSOR);
        $result = $this->_db->executeSP($sql, $args); 
        return $result;
    }

    
}

?>

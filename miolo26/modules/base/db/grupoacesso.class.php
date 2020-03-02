<?php

class BusinessBaseGrupoAcesso extends MBusiness
{
    public $idgrupo;
	var $grupo;
    public $transacoes;

    public function businessAdminGrupoAcesso($data=null)
    {
       $this->business('base',$data);
    }

	function setData($data)
	{
		$this->idgrupo = $data->idgrupo;
		$this->grupo = strtoupper($data->grupo);
        $this->transacoes = $data->transacoes;
	}

    public function getById($idgrupo)
    {
        $sql = new sql('idgrupo, grupo', 'cm_grupoacesso', 'idgrupo = ?');
        $query = $this->objQuery($sql->select($idgrupo));
        if ( $query )
        {
    		$this->idgrupo = $query->fields('idgrupo');
	    	$this->grupo = $query->fields('grupo');

            $query = $this->listAcessoByIdGrupo($idgrupo);
            if ( $query )
            {
               $this->transacoes = $query->result;
            }
        }
        return $this;
    }

    public function insert()
    {
        $MIOLO = MIOLO::getInstance();
        $this->idgrupo = $this->_db->getNewId('seq_cm_grupoacesso');
        $sql = new sql('IdGrupo, Grupo','cm_grupoacesso');
        $args = array(
    		$this->idgrupo,
		    strtoupper($this->grupo)
        );
        $cmd[] = $sql->insert($args);
        $sql->sql('idgrupo, idtrans, direito','cm_acesso');
        foreach($this->transacoes as $trans)
        {
           $cmd[] = $sql->insert(array($this->idgrupo, $trans[0], $trans[1]));
        }
        $ok = $this->executeBatch($cmd);
        if ($ok) {$this->log(OP_INS,"idgrupo = $this->idgrupo");} 
        return $ok;
    }

    public function update()
    {
        $sql = new sql('Grupo','cm_grupoacesso','idgrupo = ?');
        $args = array(
		    strtoupper($this->grupo),
    		$this->idgrupo
        );
        $cmd[] = $sql->update($args);
        $sql->sql('','cm_acesso','idgrupo=?');
        $cmd[] = $sql->delete($this->idgrupo);
        $sql->sql('idgrupo, idtrans, direito','cm_acesso');
        foreach($this->transacoes as $trans)
        {
           $cmd[] = $sql->insert(array($this->idgrupo, $trans[0], $trans[1]));
        }
        $ok = $this->executeBatch($cmd);
        if ($ok) {$this->log(OP_UPD,"idgrupo = $this->idgrupo");} 
        return $ok;
    }
    
    public function delete()
    {
        $sql = new sql('','cm_grupoacesso', 'idgrupo = ?');
        $ok = $this->execute( $sql->delete($this->idgrupo) );
        if ($ok) {$this->log(OP_DEL,"idgrupo = $this->idgrupo");} 
        return $ok;
    }

    public function listRange($range=NULL)
    {
        $sql = new sql('idgrupo, grupo', 'cm_grupoacesso', '','grupo');
        $sql->setRange($range); 
        $query = $this->query($sql);
        return $query;
    }

	function listAll()
    {
		return $this->listRange();
    }

    public function listUsuariosByIdGrupo($idgrupo)
    {
        $sql = new sql('g.idgrupo, g.grupo, u.login','','(g.grupo = ?)','u.login');
        $sql->setJoin('cm_grupoacesso g','cm_grpusuario gu','(g.idgrupo = gu.idgrupo)');
        $sql->setJoin('cm_grpusuario gu','cm_usuario u','(gu.idusuario = u.idusuario)'); 
        $query = $this->query($sql, $idgrupo);
        return $query;
    }

    public function listAcessoByIdGrupo($idgrupo)
    {
        $sql = new sql('a.idtrans, a.direito','', '(idgrupo = ?)','t.transacao');
        $sql->setJoin('cm_acesso a','cm_transacao t','(a.idtrans=t.idtrans)');
        $query = $this->query($sql,$idgrupo);
        return $query;
    }

    public function countWhere($where='')
    {
        $sql = new sql('*','cm_grupoacesso',$where);
        return $this->_db->count($sql->select());
    }
}

?>

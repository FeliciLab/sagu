<?php
class BusinessAdminTransacao extends MBusiness implements ITransaction
{
    public $idtrans;
	var $transacao;
	var $idsistema;
    public $grupos;

    public function businessAdminTransacao($data=null)
    {
       $this->business('admin',$data);
    }

	function setData($data)
	{
		$this->idtrans = $data->idtrans;
		$this->transacao = strtoupper($data->transacao);
		$this->idsistema = $data->idsistema;
        $this->grupos = $data->grupos;
	}

    public function getById($idtrans)
    {
        $sql = new sql('t.idtrans, t.transacao, t.idsistema','cm_transacao t','t.idtrans = ?');
        $query = $this->query($sql, $idtrans);
        if ( $query )
        {
            $this->setData($query->getRowObject());
            $this->setGrupos();
        }
        return $this;
    }

    public function getByName($trans)
    {
        $sql = new sql('t.idtrans, t.transacao, t.idsistema','cm_transacao t','t.transacao = ?');
        $query = $this->query($sql, $trans);
        if ( $query )
        {
            $this->setData($query->getRowObject());
            $this->setGrupos();
        }
        return $this;
    }

    public function getByGroup($group=null)
    {
        if ( $group )
        {
            $sql = new sql('distinct t.idtrans, t.transacao','','upper(g.grupo) = ?','t.transacao');
            $sql->setJoin('cm_grupoacesso g','cm_acesso a','(g.idgrupo = a.idgrupo)');
            $sql->setJoin('cm_acesso a','cm_transacao t','(a.idtrans = t.idtrans)');
            $query = $this->query($sql, $group);
        }
        else
        {
            $query = $this->listAll();
        }
        return $query;
    }
    
    public function insert()
    {
        $MIOLO = MIOLO::getInstance();

        $this->idtrans = $this->_db->getNewId('seq_cm_transacao');
        $sql = new sql('idtrans, transacao, idsistema','cm_transacao');
        $args = array($this->idtrans,
                      strtoupper($this->transacao),
                      $this->idsistema);
        $cmd[] = $sql->insert($args);
        $sql->sql('idtrans, idgrupo, direito','cm_acesso');
		
        foreach($this->grupos as $grupo)
        {
           $cmd[] = $sql->insert(array($this->idtrans, $grupo[0], $grupo[1]));
        }
        $ok = $this->execute($cmd);
        if ($ok) {$this->log(OP_INS,"idtrans = $this->idtrans; transacao = $this->transacao");} 
        return $ok;
    }

    public function update()
    {
        $sql = new sql('transacao, idsistema','cm_transacao','idtrans = ?');
        $args = array(strtoupper($this->transacao),
                      $this->idsistema,
			          $this->idtrans);
        $cmd[] = $sql->update($args);
        $sql->sql('','cm_acesso','idtrans=?');
        $cmd[] = $sql->delete($this->idtrans);
        $sql->sql('idtrans, idgrupo, direito','cm_acesso');
        foreach($this->grupos as $grupo)
        {
           $cmd[] = $sql->insert(array($this->idtrans, $grupo[0], $grupo[1]));
        }
        $ok = $this->execute($cmd);
        if ($ok) {$this->log(OP_UPD,"idtrans = $this->idtrans; transacao = $this->transacao");} 
        return $ok;
    }
    
    public function delete()
    {
        $obj = new sql('','cm_acesso','idTrans = ' . $this->idtrans);
        $sql[] = $obj->delete();
        $obj = new sql('','cm_transacao','idTrans = ' . $this->idtrans);
        $sql[] = $obj->delete();
        $ok = $this->execute( $sql );
        if ($ok) {$this->log(OP_DEL,"idtrans = $this->idtrans; transacao = $this->transacao");} 
        return $ok;
    }

    public function listRange($range=NULL)
    {
        $sql = new sql('t.idtrans, t.transacao, t.idsistema, s.sistema','cm_transacao t, cm_sistema s',
                       't.idsistema = s.idsistema','t.transacao');
        $sql->setRange($range);
        $query = $this->query($sql);
        return $query;
    }

	function listAll()
    {
		return $this->listRange();
    }

    public function listByTransaction($transaction='')
    {
        $sql = new sql('t.idtrans, t.transacao, t.idsistema, s.sistema','cm_transacao t, cm_sistema s',
                       "(t.idsistema = s.idsistema) and (t.transacao like '$transaction')",'t.transacao');
        $query = $this->query($sql);
        return $query;
    }

    public function setGrupos()
    {
        $sql = new sql('a.idgrupo, a.direito', 'cm_acesso a, cm_grupoacesso g', '(a.idgrupo=g.idgrupo) and (idtrans = ?)', 'g.grupo');
        $query = $this->query($sql,$this->idtrans);
        $this->grupos = $query->result;
    }

    public function listAcessoByIdTrans($idtrans)
    {
        $sql = new sql('a.idgrupo, a.direito', 'cm_acesso a, cm_grupoacesso g', '(a.idgrupo=g.idgrupo) and (idtrans = ?)', 'g.grupo');
        $query = $this->query($sql,$idtrans);
        return $query;
    }

    public function countWhere($where='')
    {
        $sql = new sql('*','cm_transacao',$where);
        return $this->_db->count($sql->select());
    }

    public function getUsersAllowed($action = A_ACCESS)
    {
    }

    public function getGroupsAllowed($action = A_ACCESS)
    {
    }
}
?>
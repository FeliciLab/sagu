<?php

class BusinessAdminSistema extends Business
{
    public $idsistema;
	var $sistema;

    public function businessAdminSistema($data=null)
    {
       $this->business('admin',$data);
    }

	function setData($data)
	{
		$this->idsistema = $data->idsistema;
		$this->sistema = strtoupper($data->sistema);
	}

    public function getById($idsistema)
    {
        $sql = new sql('idsistema,sistema','cm_sistema', '(idsistema = ?)');
        $query = $this->query($sql,$idsistema);
        if ( !$query->eof )
        {
            $this->setData($query->getRowData());
        }
        return $this;
    }

    public function insert()
    {
        $sql = ' insert into cm_transacao (IdTrans, Transacao, IdSistema) '.
               ' values (?,?,?)';
        $args = array($this->idtrans,
                      $this->transacao,
                      $this->idsistema);
        $ok = $this->execute($sql,$args);
        return $ok;
    }

    public function update()
    {
        $sql = ' update cm_transacao '.
			   ' set Transacao=?, IdSistema=? '.
               ' where IdTrans = ?';
        $args = array($this->transacao,
                      $this->idsistema,
			          $this->idtrans);
        $ok = $this->execute($sql,$args);
        return $ok;
    }
    
    public function delete()
    {
        $sql[] = $this->prepare(' delete from cm_acesso where idTrans=?', $this->idtrans);
        $sql[] = $this->prepare(' delete from cm_transacao where idTrans=?', $this->idtrans);
        $ok = $this->execute( $sql );
        return $ok;
    }

    public function listRange($range=NULL)
    {
        $sql = new sql('idsistema,sistema','cm_sistema', '','sistema');
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
        $sql = new sql('*','cm_setoracesso',$where);
        return $this->_db->count($sql->select());
    }

}

?>
<?php

class BusinessBaseSessao extends MBusiness implements ISession
{

    public $idsessao;
    public $name;
    public $sid;
    public $remoteaddr;
    public $idusuario;
    public $tsin;
    public $tsout;
    public $forced;

    public function __construct($data=null)
    {
       $this->getDatabase('base');
       if ($data)
       {
          $this->setData($data);
       }
    }

	function setData($data)
	{
	}

    public function sqlAllFields()
    {
        return new sql('idsessao, name, sid, remoteaddr, idusuario, tsin, tsout, forced', 'cm_sessao');
    } 
    public function getById($idsessao)
    {

        $sql = $this->sqlAllFields();
        $sql->where = 'idsetor = ?';
        $query = $this->objQuery($sql->select($idsetor));
        if ( $query )
        {
            $this->idsetor     = $query->fields('idsetor');
            $this->siglasetor  = $query->fields('siglasetor');
            $this->dataini     = $query->fields('dataini');
            $this->nomesetor   = $query->fields('nomesetor');
            $this->tiposetor   = $query->fields('tiposetor');
            $this->datafim     = $query->fields('datafim');
            $this->fone        = $query->fields('fone');
            $this->fax         = $query->fields('fax');
            $this->centrocusto = $query->fields('centrocusto');
            $this->obs         = $query->fields('obs');
            $this->localizacao = $query->fields('localizacao');
            $this->paisetor    = $query->fields('paisetor');
            $this->pairelat    = $query->fields('pairelat');
            $this->idsetorsiape= $query->fields('idsetorsiape');
        }
        return $this;
    }

    public function registerIn(&$login)
    {
        global $_SERVER;
        $MIOLO = MIOLO::getInstance();

        $session = $MIOLO->session;
        $objId = $MIOLO->getBusiness('base','objectid');
        $idsessao = $objId->getNextId('cm_sessao');
        $this->idsessao = $idsessao;
        $this->tsin = ":TO_DATE('". date('Y/m/d H:i:s') . "','YYYY/MM/DD HH24:MI:SS')";
        $this->name = $session->name;
        $this->sid = $session->id;
        $this->idusuario = $login->idkey;
        $this->tsout = null;
        $this->forced = '';
        $this->remoteaddr = $_SERVER['REMOTE_ADDR'];
        $sql = $this->sqlAllFields();
        $args = array($this->idsessao,    
                      $this->name,                
                      $this->sid,                   
                      $this->remoteaddr,                 
                      $this->idusuario,                 
                      $this->tsin,                   
                      $this->tsout,                     
                      $this->forced);
//echo $sql->insert($args);
        $ok = $this->execute($sql->insert($args));
        if ($ok) $login->idsessao = $this->idsessao;
		return $ok;     
    }

    public function registerOut($login, $forced='')
    {   
       $this->idsessao = $login->idsessao;
       if ($this->idsessao)
       {
         $this->tsout = ":TO_DATE('". date('Y/m/d H:i:s') . "','YYYY/MM/DD HH24:MI:SS')";
         $sql = new sql('tsout, forced','cm_sessao','(idsessao=?)');
         $args = array($this->tsout, $forced, $this->idsessao);
         $ok = $this->execute($sql->update($args));
         return $ok;     
       }
    }

    public function lastAccess(&$login)
    {
        $fmtTsInD = "TO_CHAR(tsin,'DD/MM/YYYY')";
        $fmtTsInH = "TO_CHAR(tsin,'HH24:MI:SS')";
        $sql = new Sql("$fmtTsInD, $fmtTsInH, remoteaddr",'cm_sessao','(idusuario=?) and (idsessao = ' .
           '(select max(idsessao) from cm_sessao where idusuario = ?))');
        $sql->setParameters(array($login->idkey, $login->idkey));    
        $query = $this->getQuery($sql);
        $login->lastaccess = $query->result[0];
    }

    public function update()
    {
        $sql = $this->sqlAllFields();
        $sql->where = 'idsetor = ?';
        $args = array($this->idsetor,
                       $this->siglasetor,                
                       $this->dataini,                   
                       $this->nomesetor,                 
                       $this->tiposetor,                 
                       $this->datafim,                   
                       $this->fone,                      
                       $this->fax,                       
                       $this->centrocusto,               
                       $this->obs,                        
                       $this->localizacao,               
                       $this->paisetor,                 
                       $this->pairelat, 
                       $this->idsetorsiape,
                       $this->idsetor);   
       $ok = $this->execute($sql->update($args));
       return $ok;
    }
    
    public function delete()
    {
        $sql= new sql('','cm_setor','idsetor = ?');
        $this->execute($sql->delete($this->idsetor));
        return $ok;
    }

    public function listRange(&$range)
    {
        $sql = $this->sqlAllFields();
	$sql->orderBy = 'siglasetor';
        $query = $this->objQueryRange($sql->select(), $range);
        return $query;
    }

	function listAll()
    {
		$range = FALSE;
		return $this->listRange($range);
    }

	function listUnidadeAcademica()
    {
        $sql = new sql('idsetor,nomesetor','cm_setor',"(tiposetor = 'UNIDADE ACAD') and (datafim is null)");
        $query = $this->objQuery($sql->select());
        return $query;
    }

	function listUnidade()
    {
        $sql = new sql('idsetor,nomesetor','cm_setor',"(tiposetor LIKE 'UNIDADE%') and (datafim is null)");
        $query = $this->objQuery($sql->select());
        return $query;
    }

	function countWhere($where)
 	{
        $sql = new sql('*','cm_setor',$where);
        return $this->db->count($sql->select());
	}

    public function listDependencias()
    {
        $sql = new sql('d.iddependencia,d.dependencia','cm_setor s, ga_dependencia d',"(d.idsetor=s.idsetor) and (d.idsetor = ?)");
        $query = $this->objQuery($sql->select($this->idsetor));
        return $query;
   }      
}

?>

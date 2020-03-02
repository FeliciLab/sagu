<?
class BusinessAdminLookup
{
    public function lookupTransacao(&$lookup)
    {
        $filter = $lookup->getFilterValue('filter');
        if (!$filter) 
           $filter = $lookup->getFilterValue();
        $lookup->addFilterField( new TextField('filter',
         $filter,'TransaÃ§Ã£o', 20));
        $columns = array(
           new DataGridColumn('transacao','Transacao','left', true,
            '60%',true),
           new DataGridColumn('idtrans','Id','right', true,
            '15%',true),
           new DataGridColumn('sistema','Sistema','left', true,
            '25%',true)
        );
        $sql = new sql('t.idtrans as key, t.transacao, t.idtrans as idtrans, '.
                       't.idsistema, s.sistema','cm_transacao t, cm_sistema s', 
                       't.idsistema = s.idsistema', 't.transacao');
        if ( $filter )
        {
            $sql->where .= " and ( upper(t.transacao)
             like upper('{$filter}%') )";
        }
        $lookup->setGrid('Admin',$sql,$columns, 'Pesquisa TransaÃ§Ãµes',15,0);
    }

    public function lookupSistemaTransacao(&$lookup)
    {   $MIOLO = MIOLO::getInstance();
        $filter = $lookup->getFilterValue('filter1');
        if (!$filter) 
           $filter = $lookup->getFilterValue();

        $fSistema = $lookup->getFilterValue('filter0');
        if (!$fSistema) 
           $fSistema = '1';

        $objSistema = $MIOLO->getBusiness('Admin','sistema');
        $objQuery = $objSistema->listAll();

        $lookup->addFilterField( new Selection('filter0', $fSistema,'Sistema', $objQuery->result));
        $lookup->addFilterField( new TextField('filter1', $filter,'TransaÃ§Ã£o', 20));
        $columns = array(
           new DataGridColumn('transacao','Transacao','left', true, '60%',true),
           new DataGridColumn('idtrans','Id','right', true, '10%',true),
           new DataGridColumn('sistema','Sistema','left', true, '20%',true),
           new DataGridColumn('idsistema','Sistema','left', true, '20%',false)
        );
        $sql = new sql("t.idtrans as \"key\", t.transacao, t.idtrans as idtrans, ".
                       't.idsistema, s.sistema','cm_transacao t, cm_sistema s', 
                       '(t.idsistema = s.idsistema)','t.transacao');
        if ( $filter )
        {
            $sql->where .= " and ( upper(t.transacao) like upper('{$filter}%') )";
        }
        $sql->where .= " and ( t.idsistema = '{$fSistema}' )";
        $lookup->setGrid('Admin',$sql,$columns,'Pesquisa TransaÃ§Ãµes',15,0);
    }

    public function lookupAgencia(&$lookup)
    {
        $filter = $lookup->getFilterValue('filter');

        if ( !$filter ){
            $filter = $lookup->getFilterValue();
            /*if (!$filter)
            {
                $filter= 'A DEFINIR';
           
            }*/   
        }
        $lookup->addFilterField( new TextField('filter',$filter,'Agencias do Banco',35) );
        $columns = array(
            new DataGridColumn('idagenc','Id','left',true,'20%',true),
            new DataGridColumn('nomeag','Nome Agencia','left',true,'40%',true),
            new DataGridColumn('nome','Nome do Banco','left',true,'40%',true)
         
                );

        $sql = new sql('a.idagenc,a.nome as nomeag,b.nome as nome,','cm_agencia a, cm_banco b','a.idbanco = b.idbanco');
    
       if ($filter)
        {
            $sql->where .= " and (upper(b.nome) like upper('%$filter%')) or (upper(a.nome) like upper('%$filter%')) ";
        }


    $lookup->setGrid('Admin',$sql,$columns,'Pesquisa por AgÃªncias ou Bancos ',15,0);
    }
    
    public function lookupCadAgencia(&$lookup)
    {
        $filterNome = $lookup->getFilterValue('filterNome');
        if(!$filterNome)
        {
            $filterNome = $lookup->getFilterValue();
        }
        $filterIdAgenc = $lookup->getFilterValue('filterIdAgenc');

        if(!$filterNome)
            if(!$filterIdAgenc)
                $filterNome = 'NAO INFORMADO';

        $lookup->addFilterField( new TextField('filterNome',$filterNome,'Nome da Agencia'));
        $lookup->addFilterField( new TextField('filterIdAgenc',$filterIdAgenc,'Numero da Agencia'));

        $columns = array(
            new DataGridColumn('key','key','right',true,'0%',false),
          new DataGridColumn('nome','Nome','left',true,'80%',true),
            new DataGridColumn('idagenc','Numero','left',true,'20%',true)
        );

        $sql = new sql("trim(t.idagenc)||'_'||t.idbanco as key,t.idagenc,t.nome,t.idbanco",'cm_agencia t');

    if ($filterNome)
        {
            $sql->where .= " upper(nome) like upper('$filterNome%')";
        }
        if ($filterIdAgenc)
        {
            if ($filterNome)
            {
                $sql->where .= " and ";
            }
            $sql->where .= "idagenc like '$filterIdAgenc'";
        }
        $lookup->setGrid('ufjf',$sql,$columns,'Pesquisa Agencia',10,0);
    }

    public function lookupBancoAgencia(&$lookup)
    {   
        $MIOLO = MIOLO::getInstance();

        $filter = $lookup->getFilterValue('filter1');
        if (!$filter) 
           $filter = $lookup->getFilterValue();

        $fBanco = $lookup->getFilterValue('filter0');
        if (!$fBanco) 
           $fBanco = '001';

        $fNomeAgencia = $lookup->getFilterValue('filter2');
        if (!$fNomeAgencia) 
           $fNomeAgencia = ''; // a testar
        
        $objBanco = $MIOLO->getBusiness('Admin','banco');
        $objQuery = $objBanco->listAll();

        $lookup->addFilterField( new Selection('filter0', $fBanco,'Banco', $objQuery->result));
        $lookup->addFilterField( new TextField('filter1', $filter,'AgÃªncia', 8));
        $lookup->addFilterField( new TextField('filter2', $fNomeAgencia,'Nome', 10));
    
        $columns = array(
            new DataGridColumn('idagenc','Numero','left',true,'20%',true),
            new DataGridColumn('idbanco','idbanco','left',true,'0%',false),
            new DataGridColumn('nome','Agencia','left',true,'80%',true),
            new DataGridColumn('nomebanco','Banco','left',true,'80%',true)
        );

        $sql = new sql("trim(t.idagenc)||'_'||trim(t.idbanco) as key,t.idagenc,t.idbanco,t.nome,b.nome as nomebanco",'cm_agencia t,cm_banco b', 't.idbanco = b.idbanco');

        if ( $filter )
        {
            $sql->where .= " and ( t.idagenc like '%$filter%' )";
        }

        $sql->where .= " and ( t.idbanco = '{$fBanco}' )";

        
        if( $fNomeAgencia )
        {
            $sql->where .= " and ( t.nome like upper('{$fNomeAgencia}%') )";
        }
        
        $lookup->setGrid('Admin',$sql,$columns,'Pesquisa AgÃªncias',15,0);
    }

   
    public function lookupMunicipio(&$lookup)
    {
        $filter = $lookup->getFilterValue('filter');
        if ( !$filter )
        {
            $filter = $lookup->getFilterValue();
            /*if (!$filter)
            {
                $filter= 'A DEFINIR';
            }*/
        }
        $lookup->addFilterField( new TextField('filter',$filter,'Municipio',20) );
        $columns = array(
            new DataGridColumn('idmunicipio','IdMunicipio','left',true,'10%',false),
            new DataGridColumn('municipio','Nome','left',true,'50%',true),
            new DataGridColumn('iduf','Estado','left','10%',true)       
            );
        $sql = new sql('m.idmunicipio,m.municipio,m.iduf,p.pais','cm_municipio m,cm_pais p','m.idpais = p.idpais');
        if ($filter)
        {
    $sql->where .= " and upper(m.municipio) LIKE upper('%$filter%') or upper(m.idmunicipio) LIKE upper ('$filter%')     ";
        }
        $lookup->setGrid('Admin',$sql,$columns,'Pesquisa Municipio',15,0);
    }

    public function lookupPais(&$lookup){
    
     $filter = $lookup->getFilterValue('filter');
        if ( !$filter )
        {
            $filter = $lookup->getFilterValue();
            /*if (!$filter)
            {
                $filter= 'A DEFINIR';
            }*/
        }
        $lookup->addFilterField( new TextField('filter',$filter,'Pais',20) );
        $columns = array(
            new DataGridColumn('idpais','IdPais','left',true,'10%',false),
            new DataGridColumn('pais','Nome','left',true,'50%',true),
            new DataGridColumn('nacionalidade','Nacionalidade','left',true,'40%',true)

            );
        $sql = new sql('idpais,pais,nacionalidade','cm_pais','','idpais');
        if ($filter)
        {
     $sql->where .= "(upper(pais) like  upper('{$filter}%')) or upper(idpais) like upper('$filter%')" ;
        }
        $lookup->setGrid('Admin',$sql,$columns,'Pesquisa Pais',15,0);
    }   
  
  
    public function lookupBanco(&$lookup)
    {   // Uso: Admin :: frmBanco

        $filter = $lookup->getFilterValue('filter0');
        if ( !$filter )
        {
            $filter = $lookup->getFilterValue();
        }
        $filterCod = $lookup->getFilterValue('filter1');
        
        $lookup->addFilterField( new TextField('filter0',$filter,'Nome',30) );
        $lookup->addFilterField( new TextField('filter1',$filterCod,'CÃ³digo',10) );

        $columns = array(
            new DataGridColumn('idbanco','Id','left',true,'10%',true),
            new DataGridColumn('nome','Nome do Banco','left',true,'90%',true)
        
        );

        $sql = new sql('idbanco,nome','cm_banco','','nome');
        if ($filter)
        {
            $sql->where .= "( upper(nome) LIKE upper('{$filter}%'))";
        }
        
        if ($filterCod)
        {
            if($filter) $sql->where .= ' AND ';
            $sql->where .= "( idbanco LIKE '%{$filterCod}%')";
        }
        $lookup->setGrid('Admin',$sql,$columns,'Pesquisa por Banco',15,0);
    }


    public function lookupUsuario(&$lookup)
    {   
        $filterLogin = $lookup->getFilterValue('filterLogin');
        if (!$filterLogin) 
           $filterLogin = $lookup->getFilterValue();
        $filterNome = $lookup->getFilterValue('filterNome');
        if (!$filterNome)
           if (!$filterLogin)
              $filterNome = 'NÃO INFORMADO';
        $clause = $lookup->getFilterValue('clause');

        $lookup->addFilterField( new TextField('filterLogin',
         $filterLogin,'Login', 20));
        $lookup->addFilterField( new Selection('clause',
        $clause,'',array('AND' => '- e -','OR'  => '- ou -' )));
        $lookup->addFilterField( new TextField('filterNome',
        $filterNome,'Nome', 40));
        $columns = array(
           new DataGridColumn('login','Login','left',
           true, '10%',true),
           new DataGridColumn('nome','Nome','left',
           true, '50%',true),
           new DataGridColumn('nick','Nick','left',
           true, '10%',true),
           new DataGridColumn('siglasetor','Setor','left'
           , true, '20%',true)
        );
        $sql = new sql("u.idusuario, u.login, u.password,
        u.idpessoa, u.idsetor,  u.nick, p.nome,
        s.siglasetor",'cm_usuario u, cm_pessoa p,
        cm_setor s', '(u.idpessoa = p.idpessoa(+))
         and (u.idsetor = s.idsetor)','u.login');
        if ( $filterLogin || $filterNome)
        {
            $sql->where .= " and (";
            if ( $filterLogin )
            {
               $sql->where .= "( upper(u.login) like
               upper('{$filterLogin}%') )";
            }
            if ( $filterNome )
            {
                if ($filterLogin)
                {
                    $sql->where .= $clause;
                }
               $sql->where .= " ( upper(p.nome)
               like upper('{$filterNome}%') )";
            }
            $sql->where .= " )";
        }
//      $lookup->setGrid($dbconf,$sql,$columns,$title,
//$pageLength,$indexColumn)
        $lookup->setGrid('Admin',$sql,$columns,
        'Pesquisa UsuÃ¡rios',10,0);
    }
    public function lookupPessoa(&$lookup)
    {   
        $filterNome = $lookup->getFilterValue('filterNome');
        if (!$filterNome) {
           $filterNome = $lookup->getFilterValue();
           if (!$filterNome) {
             $filterNome = 'A DEFINIR';
           }
        }
        $lookup->addFilterField( new TextField('filterNome',
         $filterNome,'Nome', 40));
        $columns = array(
           new DataGridColumn('idpessoa','Id','right',
           true, '10%',true),
           new DataGridColumn('nome','Nome','left', true, '90%',true)
        );
        $columns[0]->visible = false;
        $sql = new sql('p.idpessoa, p.nome','cm_pessoa p');
        if ( $filterNome )
        {
          $sql->where .= " ( upper(p.nome) like
          upper('{$filterNome}%') )";
        }
        $sql->setOrderBy('p.nome');  
        $lookup->setGrid('Admin',$sql,$columns,
        'Pesquisa Pessoas',15,0);
    }

    public function lookupPessoaCPF(&$lookup)
    {   
        $filterNome = $lookup->getFilterValue('filterNome');
        $filterCPF = $lookup->getFilterValue('filterCPF');

        if (!$filterNome) {
           $filterNome = $lookup->getFilterValue();
           if (!$filterNome) {
             $filterNome = 'A DEFINIR';
           }
        }
        if( !$filterCPF )
        {
            $filterCPF = 'A DEFINIR';
        }

        $lookup->addFilterField(new TextField('filterNome',$filterNome,'Nome',40));
        $lookup->addFilterField(new TextField('filterCPF',$filterCPF,'CPF', 40));
        
        $columns = array(
           new DataGridColumn('idpessoa','Id','right',true, '10%',true),
           new DataGridColumn('nome','Nome','left', true, '70%',true),
           new DataGridColumn('cpf','CPF','right', true, '20%',true)
        );
        $columns[0]->visible = false;
        
        $sql = new sql('p.idpessoa, p.nome, p.cpf','cm_pessoa p');
        
        if( (($filterCPF=='A DEFINIR') && ($filterNome=='A DEFINIR')) || ($filterNome!='A DEFINIR'))
        {
          $sql->where .= " ( upper(p.nome) like upper('{$filterNome}%') )";
          $sql->setOrderBy('p.nome');
        }
        if( ($filterCPF) && ($filterCPF!='A DEFINIR') )
        {
            if( ($filterNome) && ($filterNome!='A DEFINIR') )
            {
                $sql->where .= ' AND ';
            }
            $sql->where .= " (p.cpf = '{$filterCPF}')";
            $sql->setOrderBy('p.cpf');
        }
        $lookup->setGrid('Admin',$sql,$columns,'Pesquisa Pessoas',15,0);
    }

    public function lookupGrupo(&$lookup)
    {
        $filter = $lookup->getFilterValue('filter');
        if (!$filter) 
           $filter = $lookup->getFilterValue();
        $lookup->addFilterField( new TextField('filter',$filter,'Grupo', 20));
        $columns = array(
           new DataGridColumn('idgrupo','Id','right',
            true, '10%',true),
           new DataGridColumn('grupo','Grupo','left', true, '90%',true)
        );
        $sql = new sql('idgrupo, grupo', 'cm_grupoacesso'
        ,'','idgrupo');
        if ( $filter )
        {
            $sql->where .= " ( upper(grupo) like
            upper('{$filter}%') )";
        }
        $lookup->setGrid('Admin',$sql,$columns,
        'Pesquisa Grupos',15,0);
    }

    public function lookupSetor(&$lookup)
    {
        $filter = $lookup->getFilterValue('filter');
        if (!$filter) 
           $filter = $lookup->getFilterValue();
        $lookup->addFilterField( new TextField('filter',
        $filter,'Sigla Setor', 20));
        $columns = array(
           new DataGridColumn('idsetor','Id','right',
           true, '10%',true),
           new DataGridColumn('siglasetor','Sigla','left',
           true, '20%',true),
           new DataGridColumn('nomesetor','Nome','left',
           true, '55%',true),
           new DataGridColumn('datafim','Data Fim','left',
           true, '15%',true)
        );
        $sql = new sql('idsetor,siglasetor,nomesetor,dataini,datafim,tiposetor,fone,'.
'fax,centrocusto,obs,localizacao, idsetor as key', 'cm_setor','','siglasetor');
        if ( $filter )
        {
            $sql->where .= " ( upper(siglasetor) like
            upper('{$filter}%') )";
        }
        $lookup->setGrid('Admin',$sql,$columns,
        'Pesquisa Setores',15,0);
    }
        
    public function lookupTipoOrganograma(&$lookup)
    {
        $filter = $lookup->getFilterValue('filter');
        if (!$filter) 
           $filter = $lookup->getFilterValue();
        $lookup->addFilterField( new TextField('filter',
        $filter,'Sigla Tipo Organograma', 20));
        $columns = array(
           new DataGridColumn('idtipoorganograma','Id','right',
           true, '10%',true),
           new DataGridColumn('sigla','Sigla','left',
           true, '20%',true),
           new DataGridColumn('descricao','DescriÃ§Ã£o','left',
           true, '70%',true),
        );
        $sql = new sql('idtipoorganograma, sigla, descricao', 'cm_tipoorganograma','','sigla');
        if ( $filter )
        {
            $sql->where .= " ( upper(sigla) like
            upper('{$filter}%') )";
        }
        $lookup->setGrid('Admin',$sql,$columns,
        'Pesquisa Organogramas',15,0);
    }

    public function lookupInstituicao(&$lookup)
    {
      $filterNome = $lookup->getFilterValue('filterNome');
      if (!$filterNome)
        {
            $filterNome = $lookup->getFilterValue();
        }
        if (!$filterNome)
            $filterNome = 'NAO INFORMADO';

        $lookup->addFilterField( new TextField('filterNome', $filterNome, 'Nome da InstituiÃ§Ã£o',20));

        $columns = array
            (
              //new DataGridColumn('sigla','InstituiÃ§Ã£o','left',true,'100%',true)
               new DataGridColumn('idinstituicao','Id','right', true, '5%',true),
               new DataGridColumn('instituicao','Sigla','left', true, '35%',true),
               new DataGridColumn('nome','Nome','left', true, '60%',true),
             );
             $columns[0]->visible = false;
        //$sql = new  sql('t.idinstituicao,t.instituicao as sigla,t.nome,t.rua,t.numero,t.complemento,t.bairro,t.cep,t.telefone,t.email,t.fax,t.cgc,t.caixapostal','cm_instituicao t');

        $sql = new sql('idinstituicao, instituicao, nome','cm_instituicao');
    
        if ($filterNome)
        {
            $sql->where .= " upper(instituicao) like upper('$filterNome%')";
        }

        $lookup->setGrid('Admin',$sql,$columns,'Pesquisa de InstituiÃ§Ã£o',10,0);

    }
    

    public function lookupTabelaGeral(&$lookup)
    {
        $filter = $lookup->getFilterValue('filter');
        if (!$filter) 
           $filter = $lookup->getFilterValue();
        $lookup->addFilterField( new TextField('filter',
        $filter,'Tabela', 20));
        $columns = array(
           new DataGridColumn('tabela','Tabela','left',
            true, '100%',true)
        );
        $sql = new sql('tabela', 'cm_tabelageral','','tabela','tabela');
        if ( $filter )
        {
            $sql->having .= " ( upper(tabela) like
            upper('{$filter}%') )";
        }
        $lookup->setGrid('Admin',$sql,$columns,
        'Pesquisa Tabelas',15,0);
    }

    public function autoCompleteTransaction($value)
    {   $MIOLO = MIOLO::getInstance();

        $db = $MIOLO->getDatabase('tutorial');

        $sql = "select t.idtrans as key, ".
               "       t.transacao  as transacao,". 
               "       t.idtrans as idtrans,". 
               "       s.idsistema as idsistema,". 
               "       s.sistema as sistema" .
               " from cm_transacao t, cm_sistema s ".
               " where t.idsistema = s.idsistema ".
               " and t.idtrans = ?";
        
        $db->query($db->prepare($sql,$value));
        return $db->rs;
    }
  
  public function lookupPessoa2(&$lookup)
    {   
        $filterNome = $lookup->getFilterValue('filterNome');
        if (!$filterNome) {
           $filterNome = $lookup->getFilterValue();
           if (!$filterNome) {
             $filterNome = 'A DEFINIR';
           }
        }
        $lookup->addFilterField( new TextField('filterNome',
         $filterNome,'Nome', 40));
        $columns = array(
           new DataGridColumn('idpessoa','Id','right',
           true, '10%',true),
           new DataGridColumn('nome','Nome','left', true, '90%',true),
           new DataGridColumn('telefone','Telefone','left', true, '1%',false),
           new DataGridColumn('rua','Nome','Rua', true, '1%',false),
           new DataGridColumn('numero','Numero','left', true, '1%',false),
           new DataGridColumn('complemento','Complemento','left', true, '1%',false),
           new DataGridColumn('bairro','Bairro','left', true, '1%',false),
            new DataGridColumn('cep','CEP','left', true, '1%',false),
        );
        $columns[0]->visible = false;
        $sql = new sql('p.idpessoa, p.nome, p.cpf, p.telefone, p.rua, p.numero, p.complemento, p.bairro, p.cep','cm_pessoa p');
        if ( $filterNome )
        {
          $sql->where .= " ( upper(p.nome) like
          upper('{$filterNome}%') )";
        }
        $sql->setOrderBy('p.nome');  
        $lookup->setGrid('Admin',$sql,$columns,
        'Pesquisa Pessoas',15,0);
    }

}
?>

<?php

class BusinessAvinstLookup
{
    //
    // Lookup dos perfis (tabela ava_perfil)
    //    
    public function LookupPerfil(&$lookup)
    {
        $MIOLO = MIOLO::getInstance();
        $module = $MIOLO->getCurrentModule();
        
        $lookup->addFilterField( new MTextField('idPerfil', null, 'Código', 10));
        $lookup->addFilterField( new MTextField('descricao', null, 'Descrição', 45));
        $lookup->addFilterField( new MHiddenField('profilesNotEvaluable', MUtil::getAjaxActionArgs()->profilesNotEvaluable));
        
        $filter->idPerfil = $lookup->getFilterValue('idPerfil');
        $filter->descricao = $lookup->getFilterValue('descricao');
        
        if( strlen(MUtil::getAjaxActionArgs()->profilesNotEvaluable) == 0 )
        {
            $filter->avaliavel = DB_TRUE;
        }        
        
        $columns[] = new MDataGridColumn('idPerfil','Código','right', true,'5%',true);
        $columns[] = new MDataGridColumn('descricao','Descrição','left', true,'95%',true);
        
        $MIOLO->uses('types/avaPerfil.class.php',$module);
        $filter->descricao = $filter->descricao;
        $typePerfil = new avaPerfil($filter);
        $resultSql = $typePerfil->search(ADatabase::RETURN_SQL);
        $sql = new sql();
        $sql->createFrom($resultSql);
        $lookup->setGrid('avinst', $sql, $columns, 'Procurar perfis', 15, 0);
    }

    //
    // Lookup dos perfis (tabela ava_perfil)
    //    
    public function AutoCompletePerfil(&$lookup)
    {
        $MIOLO = MIOLO::getInstance();
        $module = $MIOLO->getCurrentModule();
        $filter->idPerfil = $lookup->getFilterValue();
        
        if( $filter->idPerfil )
        {
            $MIOLO->uses('types/avaPerfil.class.php',$module);
            $typePerfil = new avaPerfil($filter);
            $result = $typePerfil->search();
            if (is_array($result[0]))
            {
                $lookup->setAutoComplete($result[0]);
            }
        }        
    }
    
    //
    // Lookup da avaliação (tabela ava_avaliacao)
    //
    public function LookupAvaliacao(&$lookup)
    {
        $MIOLO = MIOLO::getInstance();
        $module = $MIOLO->getCurrentModule();

        $lookup->addFilterField( new MTextField('idAvaliacao', null, 'Código', 10));
        $lookup->addFilterField( new MTextField('nome', null, 'Nome', 45));
        
        $filter->idAvaliacao = $lookup->getFilterValue('idAvaliacao');
        $filter->nome = $lookup->getFilterValue('nome');
        
        $columns[] = new MDataGridColumn('idAvaliacao','Código','right', true,'5%',true);
        $columns[] = new MDataGridColumn('nome','Nome','left', true,'95%',true);
        
        $MIOLO->uses('types/avaAvaliacao.class.php',$module);
        $filter->nome = $filter->nome;
        $typeAvaliacao = new avaAvaliacao($filter);
        $resultSql = $typeAvaliacao->search(ADatabase::RETURN_SQL);
        $sql = new sql();
        $sql->createFrom($resultSql);
        $lookup->setGrid('avinst', $sql, $columns, 'Procurar avaliações', 15, 0);
    }
    
    //
    // Autocomplete da avaliação (tabela ava_avaliacao)
    //
    public function AutoCompleteAvaliacao(&$lookup)
    {
        $MIOLO = MIOLO::getInstance();
        $module = $MIOLO->getCurrentModule();
        $filter->idAvaliacao = $lookup->getFilterValue();
        
        if( $filter->idAvaliacao )
        {
            $MIOLO->uses('types/avaAvaliacao.class.php',$module);
            $typeAvaliacao = new avaAvaliacao($filter);
            $result = $typeAvaliacao->search();
            $lookup->setAutoComplete($result[0]);        
        }        
    }
    
    //
    // Autocomplete das questões (tabela ava_questoes)
    //    
    public function LookupQuestoes(&$lookup)
    {
        $MIOLO = MIOLO::getInstance();
        $module = $MIOLO->getCurrentModule();

        $lookup->addFilterField( new MTextField('idQuestoes', null, _M('Código', $module), 10));
        $lookup->addFilterField( new MTextField('descricao', null, _M('Enunciado', $module), 45));

        $filter->idQuestoes = $lookup->getFilterValue('idQuestoes');
        $filter->descricao = $lookup->getFilterValue('descricao');        
        
        $columns[] = new MDataGridColumn('idQuestoes','Código','right', true,'5%',true);
        $columns[] = new MDataGridColumn('descricao','Enunciado','left', true,'95%',true);
        
        $MIOLO->uses('types/avaQuestoes.class.php',$module);
        
        $filter->descricao = $filter->descricao;
        $typeQuestoes = new avaQuestoes($filter);
        $resultSql = $typeQuestoes->search(ADatabase::RETURN_SQL);
        $sql = new sql();
        $sql->createFrom($resultSql);
        $lookup->setGrid('avinst', $sql, $columns, 'Procurar questões', 15, 0);
    }

    //
    // Autocomplete das questões (tabela ava_questoes)
    //    
    public function AutoCompleteQuestoes(&$lookup)
    {
        $MIOLO = MIOLO::getInstance();
        $module = $MIOLO->getCurrentModule();
        $filter->idQuestoes = $lookup->getFilterValue();

        if( $filter->idQuestoes )
        {
            $MIOLO->uses('types/avaQuestoes.class.php',$module);
            $typeQuestoes = new avaQuestoes($filter);
            $result = $typeQuestoes->search();
            $lookup->setAutoComplete($result[0]);
        }        
    }    
    
    //
    // Lookup dos servicos (tabela ava_servico)
    //        
    public function LookupServico(&$lookup)
    {
        $MIOLO = MIOLO::getInstance();
        $module = $MIOLO->getCurrentModule();
        

        $lookup->addFilterField( new MTextField('idServico', null, 'Código', 10));
        $lookup->addFilterField( new MTextField('descricao', null, 'Descrição', 45));

        $filter->idServico = $lookup->getFilterValue('idServico');
        $filter->descricao = $lookup->getFilterValue('descricao');
        
        $columns[] = new MDataGridColumn('idServico','Código','right', true,'5%',true);
        $columns[] = new MDataGridColumn('descricao','Descrição','left', true,'95%',true);
        
        $MIOLO->uses('types/avaServico.class.php',$module);
        $filter->descricao = $filter->descricao;
        $typeServico = new avaServico($filter);
        $resultSql = $typeServico->search(ADatabase::RETURN_SQL);
        $sql = new sql();
        $sql->createFrom($resultSql);
        $lookup->setGrid('avinst', $sql, $columns, 'Procurar serviços', 15, 0);
    }

    
    //
    // Lookup dos servicos (tabela ava_servico)
    //    
    public function AutoCompleteServico(&$lookup)
    {
        $MIOLO = MIOLO::getInstance();
        $module = $MIOLO->getCurrentModule();
        $filter->idServico = $lookup->getFilterValue();
        
        if( $filter->idServico )
        {
            $MIOLO->uses('types/avaServico.class.php',$module);
            $typeServico = new avaServico($filter);
            $result = $typeServico->search();
            $lookup->setAutoComplete($result[0]);                    
        }        
    }
    
    //
    // Lookup dos formulario (tabela ava_formulario)
    //
    public function LookupFormulario(&$lookup)
    {
        $MIOLO = MIOLO::getInstance();
        $module = $MIOLO->getCurrentModule();

        $lookup->addFilterField( new MTextField('idFormulario', null, 'Código', 10));
        $lookup->addFilterField( new MTextField('nome', null, 'Nome', 45));

        $columns[] = new MDataGridColumn('idFormulario','Código','right', true,'5%',true);
        $columns[] = new MDataGridColumn('nome','Nome','left', true,'95%',true);
        
        $MIOLO->uses('types/avaFormulario.class.php',$module);
        
        $filter->idFormulario = $lookup->getFilterValue('idFormulario');
        $filter->nome = $lookup->getFilterValue('nome');
        $filter->refAvaliacao = $lookup->getFilterValue('refAvaliacao');
        $filter->nome = $filter->nome;
        
        $typeFormulario = new avaFormulario($filter);
        $resultSql = $typeFormulario->searchLookup(ADatabase::RETURN_SQL);
        $sql = new sql();
        $sql->createFrom($resultSql);
        $lookup->setGrid('avinst', $sql, $columns, 'Procurar formulários', 15, 0);
    }

    //
    // Lookup dos formularios (tabela ava_formulario)
    //    
    public function AutoCompleteFormulario(&$lookup)
    {
        $MIOLO = MIOLO::getInstance();
        $module = $MIOLO->getCurrentModule();
        $filter->idFormulario = $lookup->getFilterValue();
        
        if( $filter->idFormulario )
        {
            $MIOLO->uses('types/avaFormulario.class.php',$module);
            $typeFormulario = new avaFormulario($filter);
            $result = $typeFormulario->searchLookup();
            $lookup->setAutoComplete($result[0]);                    
        }        
    }
    
    //
    // Lookup das pessoas (Usuários do Alfa virtual)
    //        
    public function LookupPessoa(&$lookup)
    {
        $MIOLO = MIOLO::getInstance();
        $module = $MIOLO->getCurrentModule();
        
        $lookup->addFilterField( new MTextField('ref_pessoa', $filter->ref_pessoa, 'Código', 10));
        $lookup->addFilterField( new MTextField('nome_pessoa',  $filter->nome_pessoa, 'Nome', 45));
        
        $columns[] = new MDataGridColumn('ref_pessoa','Código','right', true,'5%',true);
        $columns[] = new MDataGridColumn('nome_pessoa','Nome','left', true,'70%',true);
        $columns[] = new MDataGridColumn('email','E-mail','left', true,'25%',true);
        
        $filter->ref_pessoa = $lookup->getFilterValue('ref_pessoa');
        
        if( isset(MUtil::getAjaxActionArgs()->nome_pessoa) )
        {
            $filter->nome_pessoa = $lookup->getFilterValue('nome_pessoa');
        }
        
	$MIOLO->uses('classes/sagu.class.php');
	$sagu = new saguConn();
        $resultSql = $sagu->searchPessoas($filter,ADatabase::RETURN_SQL);
        $sql = new sql();
        $sql->createFrom($resultSql);
        $lookup->setGrid('alfa_virtual', $sql, $columns, 'Procurar pessoas', 15, 0);
    }

    //
    // Lookup das pessoas (Usuários do Alfa virtual)
    //            
    public function AutoCompletePessoa(&$lookup)
    {
        $MIOLO = MIOLO::getInstance();
        $module = $MIOLO->getCurrentModule();
        $filter->ref_pessoa = $lookup->getFilterValue();
        if( $filter->ref_pessoa )
        {
            $MIOLO->uses('classes/sagu.class.php',$module);
            $alfaVirtual = new saguClass();
            $result = $alfaVirtual->searchPessoas($filter);        
            $lookup->setAutoComplete($result[0]);
        }
    }
}
?>

<?php

/**
 * JQuery Mobile Sagu basic form.
 *
 * @author Jonas Guilherme Dahmer [jonas@solis.coop.br]
 *
 * \b Maintainers: \n
 *
 * @since
 * Creation date 2012/10/23
 *
 * \b Organization: \n
 * SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b Copyright: \n
 * Copyright (c) 2012 SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b License: \n
 * Licensed under GPLv2 (for further details read the COPYING file or http://www.gnu.org/licenses/gpl.html)
 *
 */
$MIOLO->uses('forms/frmMobile.class.php', $module);
$MIOLO->uses('classes/prtCommonFormPedagogico.class.php', $module);
$MIOLO->uses('classes/prtDisciplinasPedagogico.class.php', $module);
$MIOLO->uses('classes/prtTableRaw.class.php', $module);
$MIOLO->uses('forms/frmNotasProfessor.class.php', $module);
$MIOLO->uses('classes/prtDisciplinasPedagogico.class.php', $module);
$MIOLO->uses('types/AcpOfertaComponenteCurricular.class.php', 'pedagogico');
$MIOLO->uses('types/AcpComponenteCurricularMatriz.class.php', 'pedagogico');

class frmNotasProfessorPedagogico extends frmNotasProfessor
{
    /**
     * @var string
     */
    private $isClosed;
    
    public function __construct()
    {
        //Trocar nome dinamicamente, se vier do portal deixa padrão, senão muda
        if ( MIOLO::_REQUEST('modulo') == 'academic' )
        {
            $MIOLO = MIOLO::getInstance();
            $JS = 'document.title = "Sagu";';
            $MIOLO->page->addJsCode($JS);
        }
        
        $ofertacomponentecurricularid = MIOLO::_REQUEST('ofertacomponentecurricularid');
        $matrizid = AcpOfertaComponenteCurricular::obterComponenteCurricularMatrizId($ofertacomponentecurricularid);
        $curricularid = AcpOfertaComponenteCurricular::obterComponenteCurricularId($matrizid[0]);
        $componentecurricularnome = AcpOfertaComponenteCurricular::obterComponenteCurricularNome($curricularid[0]);
        parent::__construct($componentecurricularnome[0][0]);
    }

    public function defineFields()
    {
        //Instancia classes relacionadas
        $disciplinas = new PrtDisciplinasPedagogico();
        $ofertacomponentecurricularid = MIOLO::_REQUEST('ofertacomponentecurricularid');
        $ofertacomponentecurricular = new AcpOfertaComponenteCurricular($ofertacomponentecurricularid);
        $modelodeavaliacao = AcpModeloDeAvaliacao::obterModeloDaOfertaDeComponenteCurricular($ofertacomponentecurricularid);
        
        $isClosed = $this->isClosed = ( strlen($ofertacomponentecurricular->datafechamento) > 0 );

        if( $modelodeavaliacao->tipoDeDados != AcpModeloDeAvaliacao::TIPO_PARECER )
        {
            $componentesdeavaliacao = AcpComponenteDeAvaliacao::obterComponentesDeAvaliacaoDoModelo($modelodeavaliacao->modelodeavaliacaoid);
            foreach($componentesdeavaliacao as $componente)
            {
                $options[] = array($componente->componenteDeAvaliacaoId, $componente->descricao);
            }

            // Hidden que indica qual matricula esta sendo alterada. Utilizado para identificar na funcao salvar AJAX.
            $fields[] = new MHiddenField('alterandoMatricula');

            if ( $options[0][0] )
            {
                $selection = new MSelection('componenteDeAvaliacaoId', $options[0][0], '', $options);
                $selection->setAttribute('onchange', MUtil::getAjaxAction('trocarNota'));
                
                if ( $isClosed == DB_TRUE )
                {
                    $selection->setReadOnly(true);
                }

                $bgNotas = new MDiv('', new MBaseGroup('', _M('Nota'), array($selection)));
                $bgNotas->addStyle('margin', '0 0 0 0');
                $bgNotas->addStyle('width', '100%');

                $fields[] = $bgNotas;
                $fields[] = new MSpacer();
                $fields[] = new MDiv('divAvaliacoes', $this->avaliacoes($options[0][0]));
            }
            else
            {
                $fields[] = MMessageInformation::getStaticMessage('msgInfo', _M('Não há nenhuma nota cadastrada para esta disciplina.'), MMessage::TYPE_INFORMATION);
            }
        }
        else
        {
            //Tipo Parecer
            $fields[] = new MDiv('divAvaliacoes', $this->avaliacoesParecer());
        }
        
        $fields[] = MDialog::getDefaultContainer();

	parent::addFields($fields);
    }
    
    public function trocarNota($args)
    {
        $args = $this->getAjaxData();
        
        if ( strlen($args->componenteDeAvaliacaoId) > 0 )
        {
            $this->setResponse($this->avaliacoes($args->componenteDeAvaliacaoId), 'divAvaliacoes');
        }
        else
        {
            $this->setNullResponseDiv();
        }
    }
    
    
    public function avaliacoes($componenteDeAvaliacaoId)
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        
        $disciplinas = new PrtDisciplinasPedagogico();
        
        $ofertacomponentecurricularid = MIOLO::_REQUEST('ofertacomponentecurricularid');
        $componenteDeAvaliacao = new AcpComponenteDeAvaliacao($componenteDeAvaliacaoId);
        
        $matriculas = $disciplinas->obterMatriculas($ofertacomponentecurricularid, false);
                
        if( count($matriculas) == 0 )
        {
            $fields[] = MMessageInformation::getStaticMessage('msgInfo', _M('Nenhum aluno matriculado na disciplina.'), MMessage::TYPE_INFORMATION);
        }
        
        foreach ( $matriculas as $matricula)
        {
            if ( $componenteDeAvaliacao->classeDeComponente == AcpComponenteDeAvaliacao::CLASSE_RECUPERACAO )
            {
                if ( AcpAvaliacao::deveFazerRecuperacao($matricula->matriculaid, $componenteDeAvaliacaoId) )
                {
                    $fields[] = $this->criaBaseGroupAluno($componenteDeAvaliacaoId, $matricula);
                }
            }
            else
            {
                $fields[] = $this->criaBaseGroupAluno($componenteDeAvaliacaoId, $matricula);
            }
        }

        return $fields;
    }
    
    public function avaliacoesParecer()
    {
        $disciplinas = new PrtDisciplinasPedagogico();
        
        $ofertacomponentecurricularid = MIOLO::_REQUEST('ofertacomponentecurricularid');
        $matriculas = $disciplinas->obterMatriculas($ofertacomponentecurricularid, false);
        
        if( count($matriculas) == 0 )
        {
            $fields[] = MMessageInformation::getStaticMessage('msgInfo', _M('Nenhum aluno matriculado na disciplina.'), MMessage::TYPE_INFORMATION);
        }
        
        foreach ( $matriculas  as $matricula)
        {
            $fields[] = $this->criaBaseGroupAluno(null, $matricula);
        }

        return $fields;
    }
        
    public function criaBaseGroupAluno($componenteDeAvaliacaoId, $matricula)
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        
        $nomeAluno = new MLabel($matricula->_pessoa);
        $nomeAluno->setClass('label-nome-aluno');
        $divAluno = new MDiv('', $nomeAluno);
        $divAluno->addStyle('width', '30%');
        
        $matriculaid = $matricula->matriculaid;
        $div = array();
        
        //Caso o componente for do tipo parecer
        $ofertacomponentecurricularid = MIOLO::_REQUEST('ofertacomponentecurricularid');
        $modelodeavaliacao = AcpModeloDeAvaliacao::obterModeloDaOfertaDeComponenteCurricular($ofertacomponentecurricularid);
        if( $modelodeavaliacao->tipoDeDados == AcpModeloDeAvaliacao::TIPO_PARECER )
        {
            $parecer = $this->obterCampoParecer($matricula);
            $div[] = $parecer;
        }
        else
        {
            $nota = $this->obterCampoNota($componenteDeAvaliacaoId, $matriculaid);
            $div[] = $nota;
        }
       
        if ( !MUtil::getBooleanValue(SAGU::getParameter('PORTAL', 'DESABILITA_EXIBICAO_FOTO_ALUNO')) )
        {
            //Fixme passar fileid da foto
            $divFoto = prtCommonFormPedagogico::obterFoto(null);
        }
        
        $divInfo = new MDiv('', $div);
        $divInfo->addStyle('width', '50%');
        $divInfo->addStyle('float', 'left');
        $divInfo->addStyle('margin', '15px');
        
        $bgr = new MBaseGroup('', '', array($divFoto, $divAluno, $divInfo));
        
        return $bgr;
    }
    
    /**
     * @return array
     */
    public function obterCampoNota($componenteDeAvaliacaoId, $matriculaId)
    {
        $ofertacomponentecurricularid = MIOLO::_REQUEST('ofertacomponentecurricularid') ? MIOLO::_REQUEST('ofertacomponentecurricularid') : MIOLO::_REQUEST('ofertacomponentecurricularid');
        $modelodeavaliacao = AcpModeloDeAvaliacao::obterModeloDaOfertaDeComponenteCurricular($ofertacomponentecurricularid);
        $componentedeavaliacao = new AcpComponenteDeAvaliacao($componenteDeAvaliacaoId);
        
        $avaliacao = AcpAvaliacao::obterAvaliacao($matriculaId, $componenteDeAvaliacaoId);
        
        $id = 'nota_' . $componenteDeAvaliacaoId . '_' . $matriculaId;
        
        //Tipo conceito
        if ( $modelodeavaliacao->tipoDeDados == AcpModeloDeAvaliacao::TIPO_CONCEITO )
        {
            //Tipo conceito
            $notaatual = $avaliacao->conceitodeavaliacaoid;
            $componenteconceito = AcpComponenteDeAvaliacaoConceito::obterComponenteConceitoDoComponente($componenteDeAvaliacaoId);
            $nota = new MSelection($id, $notaatual, $componentedeavaliacao->descricao, AcpConceitosDeAvaliacao::listarConceitosDoConjunto($componenteconceito->conjuntoDeConceitosId));
        }
        else //Tipo nota
        {            
            $notaatual = $avaliacao->nota;
            $nota = new MFloatField($id, $notaatual, $componentedeavaliacao->descricao);
            if( $componentedeavaliacao->permiteAlteracoes == DB_FALSE )
            {
                $nota->setReadOnly(true);
            }
        }
        
        if ( $this->isClosed == DB_TRUE )
        {
            $nota->setReadOnly(true);
        }
        
        $args = new stdClass();
        $args->matriculaId = $matriculaId;
        $args->componenteDeAvaliacaoId = $componenteDeAvaliacaoId;
        
        $nota->addAttribute('onchange', MUtil::getAjaxAction('salvar', $args));        
        $div = new MFormContainer('', array($nota));
        
        return new MHContainer('divNota_' . $componenteDeAvaliacaoId . '_' . $matriculaId, array($div, $linkHistorico));
    }
    
    public function obterCampoParecer($matricula)
    {
        $matriculaId = $matricula->matriculaid;
        
        $id = 'parecer_'.$matriculaId;
        $parecer = new MMultiLineField($id, $matricula->parecerfinal, _M('Parecer'), 30, 2, 30);
        $idSituacao = 'situacao_'.$matriculaId;
        $options = array( array( _M('Aprovado', 'basic'), AcpMatricula::SITUACAO_APROVADO ), array( _M('Reprovado', 'basic'), AcpMatricula::SITUACAO_REPROVADO ) );
        
        if ( $this->isClosed == DB_TRUE )
        {
            $parecer->setReadOnly(true);
        }
        
        if(in_array($matricula->situacao, array(AcpMatricula::SITUACAO_APROVADO, AcpMatricula::SITUACAO_REPROVADO)) )
        {
            $situacaomatricula = $matricula->situacao;
        }
        else
        {
            $situacaomatricula = AcpMatricula::SITUACAO_APROVADO;
        }
        
        $situacao = new MRadioButtonGroup($idSituacao, _M('Situação'), $options, $situacaomatricula);
       
        $args = new stdClass();
        $args->matriculaId = $matriculaId;
        $args->parecer = DB_TRUE;
        
        $parecer->addAttribute('onchange', MUtil::getAjaxAction('salvar', $args));
        $situacao->addAttribute('onchange', MUtil::getAjaxAction('salvar', $args));
        
        return new MHContainer('divNota_' . '_' . $matriculaId, array($parecer, $situacao));
    }
            
    public function salvar()
    {        
        $args = MUtil::getAjaxActionArgs();
        $disciplinas = new PrtDisciplinasPedagogico();

        $componenteDeAvaliacaoId = $args->componenteDeAvaliacaoId;        
        $matriculaid = $args->matriculaId;
        
        if ( strlen($matriculaid) == 0 )
        {
            $this->setNullResponseDiv();
            return;
        }
        
        if( $args->parecer == DB_TRUE )
        {
            $id = 'parecer_'.$matriculaid;
            $parecer = $args->$id;
            $idSituacao = 'situacao_'.$matriculaid;
            $situacao = $args->$idSituacao;
            $disciplinas->salvarParecer($matriculaid, $parecer, $situacao);
        }
        else
        {
            $this->processaNota($disciplinas, $args, $matriculaid, $componenteDeAvaliacaoId);
        }
        
        $this->setNullResponseDiv();
    }
    
    public function processaNota(PrtDisciplinasPedagogico $disciplinas, $args, $matriculaId, $componenteDeAvaliacaoId)
    {
        $MIOLO = MIOLO::getInstance();
        
        $id = 'nota_' . $componenteDeAvaliacaoId . '_' . $matriculaId;
        $nota = $args->$id;
                        
        $ofertacomponentecurricularid = MIOLO::_REQUEST('ofertacomponentecurricularid') ? MIOLO::_REQUEST('ofertacomponentecurricularid') : MIOLO::_REQUEST('ofertacomponentecurricularid');
        $modelodeavaliacao = AcpModeloDeAvaliacao::obterModeloDaOfertaDeComponenteCurricular($ofertacomponentecurricularid);
        $avaliacao = AcpAvaliacao::obterAvaliacao($matriculaId, $componenteDeAvaliacaoId);
        
        if ( $modelodeavaliacao->tipoDeDados == AcpModeloDeAvaliacao::TIPO_CONCEITO )
        {
            $conceitodeavaliacao = new AcpConceitosDeAvaliacao($avaliacao->conceitodeavaliacaoid);
            $notaAtual = $conceitodeavaliacao->resultado;
            
            // Salva nota de forma simples
            try
            {
                $disciplinas->salvarConceito($matriculaId, $componenteDeAvaliacaoId, $nota);
            }
            catch(Exception $e)
            {
                new MMessageWarning(_M('Não foi possível salvar este conceito.'));
            }
        }
        else
        {
            $notaAtual = $avaliacao->nota;
            // Verifica se a nota digitada é maior que a nota máxima permitida.
            $compoentenota = AcpComponenteDeAvaliacaoNota::obterComponenteNotaDoComponente($componenteDeAvaliacaoId);
            if ( strlen($compoentenota->valorMaximo) > 0 && strlen($nota) > 0 )
            {
                if ( $nota > $compoentenota->valorMaximo )
                {
                    $jsCode = "document.getElementById('$id').value = ''";
                    $this->manager->page->onload($jsCode);
                    new MMessageWarning(_M("A nota digitada é maior do que a nota máxima permitida ($compoentenota->valorMaximo)."));
                    return false;
                }
            }
            
            if ( strlen($compoentenota->valorMinimo) > 0 && strlen($nota) > 0 )
            {
                if ( $nota < $compoentenota->valorMinimo )
                {
                    $jsCode = "document.getElementById('$id').value = ''";
                    $this->manager->page->onload($jsCode);
                    new MMessageWarning(_M("A nota digitada é menor do que a nota mínima permitida ($compoentenota->valorMinimo)."));
                    return false;
                }
            }
            
            // Salva nota de forma simples
            try
            {
                if ( strlen($nota) <= 0 )
                {
                    if ( $MIOLO->checkAccess('FrmFrequenciasENotas', A_DELETE) || $MIOLO->checkAccess('frmNotasProfessor', A_DELETE) )
                    {
                        $disciplinas->salvarNota($matriculaId, $componenteDeAvaliacaoId, $nota, $descricao);
                    }
                    else
                    {
                        $jsCode = "document.getElementById('$id').value = '$notaAtual'";
                        $this->manager->page->onload($jsCode);
                        
                        new MMessageWarning(_M('Você não tem permissão para excluir esta nota.'));
                    }
                }
                else
                {
                    $disciplinas->salvarNota($matriculaId, $componenteDeAvaliacaoId, $nota, $descricao);
                }
            }
            catch(Exception $e)
            {
                new MMessageWarning(_M('Não foi possível salvar esta nota.'));
            }
            
            //Atualiza a nota pai
            self::processaNotaPai($args, $matriculaId, $componenteDeAvaliacaoId);
        }
        
        $this->setNullResponseDiv();
    }
    
    public static function processaNotaPai($args, $matriculaid, $componentedeavaliacaoid)
    {
        $parametros = explode("_", $args->notaselecionada);
        $matriculaid = SAGU::NVL($matriculaid, $parametros[1]);
        $componentedeavaliacaoid = SAGU::NVL($componentedeavaliacaoid, $parametros[2]);
        
        // Passadas lógicas para uma função de base de dados, para a reutilização em relatórios e scripts de correção.
        SDatabase::query("SELECT acp_processaNotaPai(?, ?)", array($matriculaid, $componentedeavaliacaoid));
        
        return '';
    }
}
?>
<?php

/**
 * @version $id$
 *
 * \b Maintainers: \n
 *
 * @since
 * Creation date 2011/11/13
 *
 * \b Organization: \n
 * SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b CopyRight: \n
 * Copyright (c) 2008 SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b License: \n
 * Licensed under GPLv2 (for further details read the COPYING file or http://www.gnu.org/licenses/gpl.html)
 *
 * \b History: \n
 * See history in CVS repository: http://www.miolo.org.br
 *
 */

$MIOLO = MIOLO::getInstance();
$MIOLO->uses('classes/awidget.class.php', 'avinst');

class relatorioGenerico extends AWidget
{
    function __construct( $parameters )
    {
        parent::__construct('relatorioGenerico'.$parameters->idAvaliacao.'_'.$parameters->idFormulario);
        $this->elementName = __CLASS__;
        $this->description = 'Relatório genérico dos formulários e questões';
        $this->version = '0.1';
        $this->parameters = $parameters;
    }

    //
    // Retorna uma div com todos os componentes
    //
    public function generate()
    { 
        $MIOLO = MIOLO::getInstance();        
        $fields = array();
	if ($this->parameters->generateReport == true)
	{
            $jasperReport = new MJasperReport('avinst');
	    try
            {
                $std = new stdClass();
                $std->idFormulario = MIOLO::_REQUEST('idFormulario');
                $std->idAvaliacao = $this->parameters->idAvaliacao;            
                
                if( ! strlen($std->idFormulario) > 0 )
                {
                    new MMessageWarning('Por favor, selecione um formulário');
                }
                else
                {
                    $this->processReportData($std); //Processa os dados para geração do relatório                

                    $parameters = array();                                
                    $parameters['str_formulario'] = MIOLO::_REQUEST('idFormulario');
                    $parameters['str_SUBREPORT_DIR'] = $MIOLO->getAbsolutePath('reports/', 'avinst');
                    $jasperReport->executeJRXML('avinst', 'Relatorio_da_autoavaliacao_institucional2012', $parameters, 'PDF');                    
                    new MMessageSuccess('Relatório gerado com sucesso');
                }
                
            }
	    catch (Exception $e)
       	    {
                new MMessageError('Não foi possível gerar o relatório, o sistema retornou os seguintes erros: '.$e->getMessage());
            }
            $this->setInner($fields);
            return parent::generate();
        }
        else
        {
            //Lista todos os formulários da avaliação
            $data = new stdClass();
            $data->refAvaliacao = $this->parameters->idAvaliacao;
            $avaFormulario = new avaFormulario($data);
            $fields[] = new MSelection('idFormulario', null, _M('Formulário'), $avaFormulario->searchLookup());
            $fields['report'] = new MButton('btnGerar', _M('Gerar relatório'));
	    $ajaxParams = 'idWidget=relatorioGenerico&generateReport=true&idAvaliacao='.urlencode($this->parameters->idAvaliacao).'&refPerfil='.urlencode($this->parameters->refPerfil).'&noPopup=true';
            $fields['report']->addAttribute('onClick', 'miolo.doAjax(\'dashboardCallMessageWidget\', \''.$ajaxParams.'\', \'__mainForm\')');
            $vct['cont'] = new MBaseGroup('vct'.$this->param->idAvaliacao.'_'.$this->params->idFormulario, _M('Relatório genérico dos formulários e questões'),$fields, 'vertical', 'css', MFormControl::FORM_MODE_SHOW_NBSP);
            $vct['cont']->addAttribute('align', 'center');
            $vct['cont']->addStyle('padding', '10px');
            $vct['cont']->addStyle('left', '10px'); 
            
            $this->setInner($vct);
            return parent::generate();
        }
    }
        
    
    /**
     * Gera as informações a serem geradas no relatório
     */
    public function processReportData($data)
    {
        //parametro ref_questao
        //parametro ref_formulario
        
        //Remove tabela com dados das opções
        $sql = "DROP TABLE IF EXISTS ava_relatorioGenerico";
        $resultSql = ADatabase::query($sql);
        
        //Cria tabela com dados das opções
        $sql = "CREATE TABLE ava_relatorioGenerico(id_questao int, tipo int, opcao varchar, descricao varchar, descritiva boolean);";
        $resultSql = ADatabase::query($sql);
        
        $dataArray = array();
        
        $formulario = new avaFormulario($data, true);
        
        //Percorre cada bloco do formulário
        foreach( $formulario->blocos as $codBloco=>$bloco )
        {
            //Percorre cada bloco de questoes
            foreach($bloco->questoes as $codBlocoQuestao=>$blocoQuestao)
            {
                //Percorre cada questao do bloco
                foreach( $blocoQuestao as $codQuestao => $questao )
                {
                    $opcoes = unserialize($questao->__get('opcoes'));
                    
                    $idQuestoes = $questao->idQuestoes;
                    $descricao = $questao->descricao;
                    $tipo = $questao->tipo;
                                        
                    //Percorre cada opção da questao
                    foreach( $opcoes as $cod=>$opcao)
                    {
                        if( is_array($opcao) )
                        {
                            //Percorre cada questao e insere as informações
                            foreach( $opcao as $codObj=>$objOpcao )
                            {
                                ADatabase::execute("INSERT INTO ava_relatorioGenerico (id_questao, tipo, opcao, descricao, descritiva) VALUES (?,?,?,?,?);", array($idQuestoes, $tipo, $objOpcao->codigo, $objOpcao->descricaoOpcao, $objOpcao->opcaoDescritiva));
                            }
                        }
                    }
                }
            }
        }
    }
}

?>


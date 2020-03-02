<?php

/**
 * Formulário para replicar registros nas tabelas ava_formulario, ava_bloco, ava_bloco_questoes.
 *
 * @author André Chagas Dias [andre@solis.coop.br]
 *
 * \b Maintainers: \n
 * André Chagas Dias [andre@solis.coop.br]
 *
 * @since
 * Creation date 29/11/2011
 *
 * \b Organization: \n
 * SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b Copyright: \n
 * Copyright (c) 2011 SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b License: \n
 * Licensed under GPLv2 (for further details read the COPYING file or http://www.gnu.org/licenses/gpl.html)
 */
class frmAtualizaTotalizadores extends AForm
{
    // verificacao para ativar o eventHandler
    public static $doEventHandler;
    
    /**
     * Construtor do formulário.
     */
    public function __construct()
    {
        parent::__construct('Atualiza totalizadores');
    }

    /**
     * Criar campos do formulário.
     */
    public function createFields()
    {
        parent::createFields();
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $fields[] = MMessage::getMessageContainer();
        $fields[] = new MLabel('<br>'); // Espaço para os campos
        $lookup = new MLookupContainer('refAvaliacao', null, 'Avaliação', 'avinst', 'Avaliacao');
        $lookup->getLookupField()->setContext($module, $module, 'Avaliacao', 'obtemGranularidadesAvaliacao', 'refAvaliacao,refAvaliacao_lookupDescription', null, true);
        $fields[] = $lookup;
        $fields[] = new MDiv('granularidadesDiv', $this->obtemGranularidadesAvaliacao(), 'field');
        $fields[] = new MLabel('<br>');
        $buttons[] = new MButton('backButton', 'Voltar', $MIOLO->getActionURL($module, 'main'));
        $buttons[] = new MButton('updateButton', 'Atualizar');
        $fields[] = new MDiv(NULL, $buttons, NULL, 'align=center');
        $this->setFields($fields);
        $validators[] = new MRequiredValidator('refAvaliacao');
        if ($this->getFormValue('refAvaliacao')>0)
        {
            $validators[] = new MRequiredValidator('idGranularidade');
        }
        $this->setValidators($validators);
        $this->setShowPostButton( FALSE );
        $this->setJsValidationEnabled( FALSE );
    }
    
    //
    //
    //
    public function obtemGranularidadesAvaliacao()
    {
        $MIOLO = MIOLO::getInstance();
        $MIOLO->uses('types/avaAvaliacao.class.php', 'avinst');
        $MIOLO->uses('classes/agranularity.class.php', 'avinst');
        $idAvaliacao = $this->getFormValue('refAvaliacao');
        if (strlen($idAvaliacao)>0)
        {
            $filter = new stdClass();
            $filter->idAvaliacao = $idAvaliacao;
            $avaAvaliacao = new avaAvaliacao($filter, true);
            $formularios = $avaAvaliacao->getFormularios();
            if (is_array($formularios))
            {
                foreach ($formularios as $formulario)
                {
                    $formulario->populate();
                    if (is_array($formulario->blocos))
                    {
                        foreach ($formulario->blocos as $bloco)
                        {
                            $granularidade = $bloco->getGranularidade();
                            if ($granularidade->tipo == AGranularity::GRANULARITY_RETURN_ARRAY_OF_OBJECTS)
                            {
                                $granularidades[$granularidade->idGranularidade] = $granularidade->descricao;
                            }
                        }
                    }
                }
            }
            if (is_array($granularidades))
            {
                $label = new MLabel('Granularidade'.':');
                $label->setClass('mCaption');
                $fields[] = new MSpan(null,$label,'label');
                $fields[] = new MSelection('idGranularidade', $this->getFormValue('idGranularidade'), 'Granularidade', $granularidades);
            }
            else
            {
                $fields[] = new MDiv('divTip', 'A avaliação selecionada não contém granularidades que possam ser utilizadas como estatísticas.');    
            }
            $this->setResponse($fields, 'granularidadesDiv');
        }
        return null;
    }
    
    //
    //
    //
    public function updateButton_click()
    {
        if( ! $this->validate() )
        {
            new MMessageWarning('Por favor, preencha os dados pedidos no formulário para continuar o processo');
            return;
        }
        $idGranularidade = $this->getFormValue('idGranularidade');
        $idAvaliacao = $this->getFormValue('refAvaliacao');
        
        if (strlen($idGranularidade)>0)
        {
            $MIOLO = MIOLO::getInstance();
            $MIOLO->uses('types/avaTotalizadores.class.php', 'avinst');
            $avaTotalizadores = new avaTotalizadores();
            $status = $avaTotalizadores->atualizaTotalizadores($idAvaliacao, $idGranularidade);
            if ($status->status == false)
            {
                new MMessageError($status->error);
            }
            else
            {
                new MMessageSuccess('Os totalizadores foram atualizados com sucesso');
            }
        }
    }
}
?>
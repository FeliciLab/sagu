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
class frmAvaReplicarFormulario extends AProcessForm
{
    /**
     * Construtor do formulário.
     */
    public function __construct()
    {
        $this->target = 'avaFormulario';
        parent::__construct('Replicar formulário');
    }

    /**
     * Criar campos do formulário.
     */
    public function createFields()
    {
        parent::createFields();
        $this->toolbar->hideButtons(MToolBar::BUTTON_SAVE);
        $module = MIOLO::getCurrentModule();
        $fields['refAvaliacaoOrigem'] = new MLookupContainer('refAvaliacaoOrigem', null, _M('Avaliação', $module), $module, 'Avaliacao');
        $fields['refAvaliacaoOrigem']->getLookupField()->setContext($module, $module, 'Avaliacao', 'loadLookups', 'refAvaliacaoOrigem,refAvaliacaoOrigem_lookupDescription', null, true);
        $fields[] = new MDiv('divLookups', $this->returnDestinationFields());
        $this->addFields($fields);

        $validators[] = new MIntegerValidator('refAvaliacaoOrigem', null, 'required');
        if (strlen($this->getFormValue('refAvaliacaoOrigem'))>0)
        {
            $validators[] = new MIntegerValidator('refFormulario', null, 'required');
            $validators[] = new MIntegerValidator('refAvaliacao', null, 'required');        
        }
        $this->setValidators($validators);
    }
    
    /**
     * Carrega os lookups da avaliação de origem e de destino.
     */
    public function loadLookups()
    {
        $module = MIOLO::getCurrentModule();
        $fields = $this->returnDestinationFields();
        $this->setAjaxFields($fields, 'divLookups');
    }
    
    /**
     * 
     */
    public function returnDestinationFields()
    {
        if (strlen($this->getFormValue('refAvaliacaoOrigem'))>0)
        {
            $fields['refFormulario'] = new MLookupContainer('refFormulario', null, 'Código do formulário de origem', $module, 'Formulario');
            $fields['refFormulario']->getLookupField()->filter = array('refAvaliacao'=>'refAvaliacaoOrigem');
            $fields[] = new MLookupContainer('refAvaliacao', null, _M('Avaliação de destino', $module), $module, 'Avaliacao');
            $fields[] = new MLabel('<br>');
            $fields[] = $this->getButtons();
        }
        return $fields;
    }

    /**
     * Replica o formulário.
     */
    public function saveButton_click()
    {
        if( ! $this->validate() )
        {
            new MMessageWarning('Verifique os dados informados');
            return;
        }
        $MIOLO = MIOLO::getInstance();
        $module = $MIOLO->getCurrentModule();
        $MIOLO->uses( "types/$this->target.class.php", $module );
        $args = MUtil::getAjaxActionArgs();
        
        try 
        {
            $type = new $this->target();
            $pk = $type->getPrimaryKeyAttribute();
            $type->__set($pk,$args->refFormulario);
            $type->populate();
            $type->__set('refAvaliacao',$args->refAvaliacao);
            $type->insert();
            $linkOpts[$pk] = $type->__get($pk);
            $linkOpts['function'] = 'search';
            $link = new MLinkButton(null,"($linkOpts[$pk])",$MIOLO->getActionUrl($module, 'main:avaFormulario', null, $linkOpts));
            new MMessageSuccess(_M(MSG_RECORD_INSERTED,avinst,$link->generate()));            
        }
        catch (Exception $e)
        {
            new MMessageError(MSG_RECORD_INSERT_ERROR . ' ' . $e);
        }       
    }
}


?>
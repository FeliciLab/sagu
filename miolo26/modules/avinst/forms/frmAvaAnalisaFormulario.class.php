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
class frmAvaAnalisaFormulario extends AForm
{
    // verificacao para ativar o eventHandler
    public static $doEventHandler;
    
    /**
     * Construtor do formulário.
     */
    public function __construct()
    {
        parent::__construct('Acessar como outro login');
        
        if ( !self::$doEventHandler )
        {
            $this->eventHandler();
            self::$doEventHandler = true;
        }
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
        $lookup = new MLookupContainer('refPessoa', null, _M('Pessoa', $module), 'avinst', 'Pessoa');
        $fields[] = $lookup;
        $fields[] = new MLabel('<br>');
        $buttons[] = new MButton('backButton', _M('Voltar', $module), $MIOLO->getActionURL($module, 'main'));
        $buttons[] = new MButton('simulateButton', _M('Simular ambiente', $module));
        $fields[] = new MDiv(NULL, $buttons, NULL, 'align=center');
        $this->setFields($fields);
        
        $validators[] = new MRequiredValidator('refPessoa',_M('Pessoa'));
        $this->setValidators($validators);
        $this->setShowPostButton( FALSE );
        $this->setJsValidationEnabled( FALSE );
        $this->page->onLoad('dojo.byId("refPessoa").focus();');        
    }
    
    public function simulateButton_click()
    {
        if( ! $this->validate() )
        {
            new MMessageWarning('Por favor, verifique se o código da pessoa está preenchido corretamente');
            return;
        }
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $refPessoa = $this->getFormValue('refPessoa');
        $args['refPessoa'] = $refPessoa;
        $this->page->redirect($MIOLO->getActionURL($module, 'main', null, $args));
    }
}


?>
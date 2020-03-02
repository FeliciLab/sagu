<?php

/**
 * Form for insert, update and delete registers on browser table
 *
 * @author Daniel Hartmann [daniel@solis.coop.br]
 *
 * \b Maintainers: \n
 * Armando Taffarel Neto [taffarel@solis.coop.br]
 * Daniel Hartmann [daniel@solis.coop.br]
 *
 * @since
 * Creation date 2011/03/14
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
class frmBrowser extends MForm
{
    /**
     * Form constructor
     */
    public function __construct()
    {
        parent::__construct(_M('Cadastro de navegador', MIOLO::getCurrentModule()));
        $this->eventHandler();
        $this->setJsValidationEnabled(FALSE);
    }

    /**
     * Create form fields
     */
    public function createFields()
    {
        $module = MIOLO::getCurrentModule();
        $fields[] = MMessage::getMessageContainer();

        $fields['toolbar'] = new MToolBar('toolbar');
        $fields['toolbar']->hideButton(array(MToolbar::BUTTON_DELETE, MToolbar::BUTTON_PRINT, MToolBar::BUTTON_RESET, MToolBar::BUTTON_EXIT));

        $readOnly = MIOLO::_REQUEST('function') == 'edit';
        $fields[] = new MTextField('identifier', NULL, _M('Identificador', $module), 10, '', NULL, $readOnly);
        $fields[] = new MTextField('description', NULL, _M('Descrição', $module), 50);

        $MIOLO = MIOLO::getInstance();
        $browser = $MIOLO->getBusiness($module, 'browser');

        $this->setFields($fields);

        $buttons[] = new MButton('backButton', _M('Voltar', $module), ':backButton_click');
        $buttons[] = new MButton('saveButton', _M('Salvar', $module));
        $this->setButtons($buttons);

        $validators[] = new MRequiredValidator('identifier');
        $validators[] = new MRequiredValidator('description');
        $this->setValidators($validators);
    }

    /**
     * Update button action
     */
    public function actionUpdate_click()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $filter = (object) array('identifier' => MIOLO::_REQUEST('item'));

        $browser = $MIOLO->getBusiness($module, 'browser');
        $data = $browser->search($filter);
        $line = $data[0];

        $this->identifier->setValue($line[0]);
        $this->description->setValue($line[1]);
    }

    /**
     * Save button action
     */
    public function saveButton_click()
    {
        if ( !$this->validate() )
        {
            new MMessageWarning(_M('Verifique os dados informados.', $module));
            return;
        }

        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $browser = $MIOLO->getBusiness($module, 'browser', $this->getData());

        switch ( MIOLO::_REQUEST('function') )
        {
            case 'insert':
                try
                {
                    if ( $browser->insert() )
                    {
                        new MMessageSuccess(_M('Registro inserido com sucesso', $module));
                    }
                }
                catch ( Exception $e )
                {
                    new MMessageError(_M('Ocorreu um erro. Verifique seus dados.', $module));
                }
                break;
            case 'edit':
                if ( $browser->update() )
                {
                    new MMessageSuccess(_M('Registro atualizado com sucesso', $module));
                }
                else
                {
                    new MMessageError(_M('Ocorreu um erro. Verifique seus dados.', $module));
                }
                break;
        }
    }

    /**
     * Back button action
     */
    public function backButton_click()
    {
        $MIOLO = MIOLO::getInstance();
        $url = $MIOLO->getActionURL(MIOLO::getCurrentModule(), MIOLO::getCurrentAction(), '', array('function' => 'search'));
        $MIOLO->page->redirect($url);
    }
}

?>
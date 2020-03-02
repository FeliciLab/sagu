<?php
/**
 * Search form for browser table
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
class frmSearchBrowser extends MForm
{
    /**
     * @var object MGrid object
     */
    public $grid;

    /**
     * Form constructor
     */
    public function __construct()
    {
        parent::__construct(_M('Busca de navegadores', MIOLO::getCurrentModule()));
        $this->eventHandler();
    }

    /**
     * Create form fields
     */
    public function createFields()
    {
        $module = MIOLO::getCurrentModule();
        $fields[] = MMessage::getMessageContainer();

        $fields['toolbar'] = new MToolBar('toolbar');
        $fields['toolbar']->disableButton(MToolbar::BUTTON_SEARCH);
        $fields['toolbar']->hideButton(array(MToolbar::BUTTON_DELETE, MToolbar::BUTTON_PRINT, MToolBar::BUTTON_RESET));

        $fields[] = new MTextField('identifier', NULL, _M('Identificador', $module), 10);
        $fields[] = new MTextField('description', NULL, _M('Descrição', $module), 50);

        $searchButtons[] = new MButton('clearButton', _M('Limpar', $module));
        $searchButtons[] = new MButton('searchButton', _M('Pesquisar', $module));
        $fields[] = new MDiv(NULL, $searchButtons, NULL, 'align=center');

        $MIOLO = MIOLO::getInstance();
        $browser = $MIOLO->getBusiness($module, 'browser');
        $this->grid = $this->manager->getUI()->getGrid($module, 'grdBrowser');
        $this->grid->setData($browser->search());
        
        $fields[] = new MDiv('divGrid', $this->grid->generate());
        $this->setFields($fields);

        $this->setButtons(array());
    }

    /**
     * Search button action
     */
    public function searchButton_click()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $data = $this->getData();

        $browser = $MIOLO->getBusiness($module, 'browser');
        $this->grid->setData($browser->search($data));
        $this->setResponse($this->grid->generate(), 'divGrid');
    }

    /**
     * Clear button action
     */
    public function clearButton_click()
    {
        $this->getField('identifier')->setValue('');
        $this->getField('description')->setValue('');
    }

    /**
     * Delete button click
     *
     * @param array $args Request arguments
     */
    public function actionDelete()
    {
        $args = MUtil::getAjaxActionArgs();

        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $browser = $MIOLO->getBusiness($module, 'browser');
        $browser->setIdentifier($args->item);

        if ( $browser->delete() )
        {
            $this->grid->setData($browser->search($data));
            $this->setResponse($this->grid->generate(), 'divGrid');
            new MMessageSuccess(_M('Registro removido com sucesso!', $module));
        }
    }
}

?>
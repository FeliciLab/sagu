<?php
/**
 *  Formulário herdado pelos formulários de pesquisa na avaliação institucional
 *
 * @author Andre Chagas Dias [andre@solis.coop.br]
 *
 * @version $id$
 *
 * \b Maintainers: \n
 *
 * @since
 * Creation date 2011/11/16
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

//$MIOLO->uses( 'classes/sfields.class.php','scotty2' );
//$MIOLO->uses( 'classes/uvalidator.class.php','adminUnivates' );

/*
 * Class ASearchForm
 *
 */
class ASearchForm extends AForm
{
    // Variável que contem um instância do objeto MGrid do formulário de busca
    public $grid;
    
    public function __construct($title)
    {
        parent::__construct( $title );        
    }
    
    public function createFields()
    {
        parent::createFields();
        $this->toolbar->hideButtons(array(MToolBar::BUTTON_SAVE,MToolBar::BUTTON_DELETE,MToolBar::BUTTON_SEARCH, MToolBar::BUTTON_PRINT));
        if( MUtil::isFirstAccessToForm() )
        {
            $this->page->onLoad('setFocus();'); // Coloca o foco sempre no primeiro campo da tela
        }
    }
    
    public function getButtons()
    {
        $module = MIOLO::getCurrentModule();
        $searchButtons[] = new MButton('searchButton', _M('Pesquisar', $module));
        return new MDiv(NULL, $searchButtons, NULL, 'align=center');                            
    }
    
    public function searchButton_click()
    {
        $targetType = new $this->target(MUtil::getAjaxActionArgs());
        $data = $targetType->search();
        if(  MUtil::getDefaultEventValue() != 'searchButton:click' )
        {
            return $data;
        }
        else
        {
            $this->grid->setData($data);
            $this->setResponse( $this->grid, 'divGrid' );
        }                            
    }
}
?>

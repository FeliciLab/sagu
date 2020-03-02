<?php
/**
 * <--- Copyright 2005-2011 de Solis - Cooperativa de Soluções Livres Ltda. e
 * Univates - Centro Universitário.
 * 
 * Este arquivo é parte do programa Gnuteca.
 * 
 * O Gnuteca é um software livre; você pode redistribuí-lo e/ou modificá-lo
 * dentro dos termos da Licença Pública Geral GNU como publicada pela Fundação
 * do Software Livre (FSF); na versão 2 da Licença.
 * 
 * Este programa é distribuído na esperança que possa ser útil, mas SEM
 * NENHUMA GARANTIA; sem uma garantia implícita de ADEQUAÇÃO a qualquer MERCADO
 * ou APLICAÇÃO EM PARTICULAR. Veja a Licença Pública Geral GNU/GPL em
 * português para maiores detalhes.
 * 
 * Você deve ter recebido uma cópia da Licença Pública Geral GNU, sob o título
 * "LICENCA.txt", junto com este programa, se não, acesse o Portal do Software
 * Público Brasileiro no endereço www.softwarepublico.gov.br ou escreva para a
 * Fundação do Software Livre (FSF) Inc., 51 Franklin St, Fifth Floor, Boston,
 * MA 02110-1301, USA --->
 * 
 *
 *
 * @author Luiz Gilberto Gregory Filho [luiz@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Luiz Gregory Filho [luiz@solis.coop.br]
 * Moises Heberle [moises@solis.coop.br]
 *
 * @since
 * Class created on 18/12/2008
 *
 **/
class FrmPreCatalogueSearch extends GForm
{

    function __construct()
    {
        $this->setBusiness('BusPreCatalogue');
        $this->setGrid('GrdPreCatalogue');
        $this->setSearchFunction('searchInPreCatalogue');
        $this->setDeleteFunction('deleteMaterial');
        $this->setPrimaryKeys('controlNumber');
        $this->setTransaction('gtcPreCatalogue');
        parent::__construct();
    }

    public function mainFields()
    {
        $list = BusinessGnuteca3BusDomain::listForSelect('MATERIAL_SEARCH_TYPE');
        
        $fields[] = new GSelection("numberType",  null,  _M("Tipo de número", $this->module), $list, false, null, null, true );
        $fields[] = new MTextField("number",      null,  _M("Número", $this->module), FIELD_ID_SIZE);
        $fields[] = new MTextField("expressionS", null,  _M("Expressão", $this->module), FIELD_DESCRIPTION_SIZE);

        $this->setFields( $fields );
    }

    
    /**
     * Método reescrito que contrói a toolbar
     */
    public function getToolBar($formContent)
    {
        $MIOLO  = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $action = MIOLO::getCurrentAction();

        $imageMaterialMoviment= $MIOLO->getUI()->getImage($module,'materialMovement-32x32.png');
        $this->_toolBar = new GToolBar('toolBar', $MIOLO->getActionURL($module, $action));

        //refaz botão new para entrar na inserção de material sempre
        $imageNew      = $MIOLO->getUI()->getImageTheme( $MIOLO->getTheme()->getId(), 'toolbar-new.png');
        $url = $this->MIOLO->getActionURL('gnuteca3','main:catalogue:material&frm__mainForm__EVENTTARGETVALUE=tbBtnNew:click&function=insert');
        $this->_toolBar->addButton(MToolBar::BUTTON_NEW, null, $url, _M('Clique para inserir um novo registro'), true, $imageNew, $imageNew);

        $imageSavePre   = $MIOLO->getUI()->getImageTheme( $MIOLO->getTheme()->getId(), 'toolbar-savePreCatalogue.png');
        $this->_toolBar->addButton("spreadSheetSavePreCatalogueButton$incrementName", null,  ":saveSpreadsheetPreCatalogue", _M("Salvar na Pré-catalogação F8", $this->module), true, $imageSavePre, $imageSavePre);

        $imageSave      = $MIOLO->getUI()->getImageTheme( $MIOLO->getTheme()->getId(), 'toolbar-save.png');
        $this->_toolBar->addButton("spreadSheetSave", null,  ":saveSpreadsheet", _M("Salvar F3", $this->module), true, $imageSave, $imageSave);

        if ( $formContent )
        {
            $this->_toolBar->setFormContent( $formContent );
        }

        //desabilitar botões
        $this->_toolBar->disableButtons(array( "spreadSheetSavePreCatalogueButton$incrementName", "spreadSheetSave") );
        //remover botões
        $this->_toolBar->removeButtons(array( MToolBar::BUTTON_SAVE, MToolBar::BUTTON_RESET, "btnFormContent"));

        return $this->_toolBar;
    }


    /**
	 * Método reescrito chamado ao apertar F2
	 *
	 */
    public function onkeydown113()
	{
        $MIOLO  = MIOLO::getInstance();
        $MIOLO->page->redirect( $MIOLO->getActionURL( 'gnuteca3' , "main:catalogue:material&function=new") );
    }


    /**
     * Método reescrito para não limpar com a tecla F7
	 *
	 */
	public function onkeydown118()
	{
        $this->setResponse('','limbo');
	}
}
?>
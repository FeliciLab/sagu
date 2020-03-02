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
 * Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @since
 * Class created on 08/12/2008
 *
 **/
$MIOLO->getClass( $module, 'controls/GMaterialDetail');
class FrmKardexControl extends GForm
{
    private $busSpreadsheet;

    function __construct()
    {
        $this->MIOLO = MIOLO::getInstance();
        $this->module = MIOLO::getCurrentModule();
        $this->setBusiness('BusKardexControl');
        $this->setGrid('GrdKardexControl');
        $this->setSearchFunction('searchKardexControl');
        $this->setDeleteFunction('deleteAllKardexByMaterialControl');
        $this->setPrimaryKeys('controlNumber');
        $this->setTransaction('gtcKardexControl');
        $this->busSpreadsheet = $this->MIOLO->getBusiness($this->module, 'BusSpreadsheet');
        
        parent::__construct();
    }


    /**
     * Create Default Fileds for Search Form
     *
     * @return void
     */
    public function mainFields()
    {
        $this->manager->uses('db/BusDomain.class.php','gnuteca3');
        $busDomain = new BusinessGnuteca3BusDomain();
        $materialSearchType = $busDomain->listDomain( 'MATERIAL_SEARCH_TYPE');
        unset( $materialSearchType[1] );

        $fields[] = new GSelection  ("numberType",     'cn',  _M("Tipo de número",    $this->module), $materialSearchType, null, null, null, TRUE  );
        $fields[] = new MTextField  ("number",         null,  _M("Número",         $this->module), FIELD_ID_SIZE);
        $fields[] = new MTextField  ("subscriberCode", null,  _M("Código do assinante",$this->module), FIELD_ID_SIZE);
        $fields[] = new MTextField  ("fiscalNote",     null,  _M("Nota fiscal",    $this->module), FIELD_ID_SIZE);
        $fields[] = new MTextField  ("titleS",         null,  _M("Título",          $this->module), FIELD_DESCRIPTION_SIZE);
        $fields[] = new MTextField  ("expressionS",    null,  _M("Expressão",     $this->module), FIELD_DESCRIPTION_SIZE);
        $validators[] = new MIntegerValidator('number');
        $baseGroup   = new MBaseGroup  ("vencimentoAssinatura", _M("Assinatura expirada ", $this->module));

        $control1[0] = new MLabel           (_M("Data de início",   $this->module) . ":");
        $control2[0] = new MLabel           (_M("Data final",     $this->module) . ":");
        $control1[1] = new MCalendarField   ("startDate",       null, null, FIELD_DATE_SIZE);
        $control2[1] = new MCalendarField   ("endDate",         null, null, FIELD_DATE_SIZE);

        $control1[0]->setWidth(115);
        $control2[0]->setWidth(115);

        $controls[] = new GContainer       ("control1", $control1);
        $controls[] = new GContainer       ("control2", $control2);

        $cont[] = new MVContainer("cont", $controls);

        $baseGroup->setControl($cont);

        $fields[] = $baseGroup;
        $this->setValidators($validators);
        $this->setFields( $fields );
    }

    public function showDetail()
    {
        $this->injectContent( new GMaterialDetail( MIOLO::_REQUEST('controlNumber') ) , false, _M( 'Detalhes' , $this->module ), '90%');
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
        $imageNew = $MIOLO->getUI()->getImageTheme( $MIOLO->getTheme()->getId(), 'toolbar-new.png');
        $url = $this->MIOLO->getActionURL('gnuteca3','main:catalogue:material&frm__mainForm__EVENTTARGETVALUE=tbBtnNew:click&function=dinamicMenu&leaderString=' . $this->getLeaderStringForKardex() );
        $this->_toolBar->addButton(MToolBar::BUTTON_NEW, null, $url, _M('Clique para inserir um novo registro'), true, $imageNew, $imageNew);

        $imageSavePre = $MIOLO->getUI()->getImageTheme( $MIOLO->getTheme()->getId(), 'toolbar-savePreCatalogue.png');
        $this->_toolBar->addButton("spreadSheetSavePreCatalogueButton$incrementName", null,  ":saveSpreadsheetPreCatalogue", _M("Salvar na Pré-catalogação F8", $this->module), true, $imageSavePre, $imageSavePre);

        $imageSave = $MIOLO->getUI()->getImageTheme( $MIOLO->getTheme()->getId(), 'toolbar-save.png');
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
        $module = MIOLO::getCurrentModule();

        $url = $MIOLO->getActionURL($module, 'main:catalogue:material&function=dinamicMenu&leaderString=' . $this->getLeaderStringForKardex());
        $MIOLO->page->redirect($url);
    }


    /**
     * Método que obtém o código leader de coleção
     */
    private function getLeaderStringForKardex()
    {
        $menus = $this->busSpreadsheet->getMenus();

        $leaderString = '';
        if ( is_array($menus) )
        {
            foreach ($menus as $i=>$menu)
            {
                if ( ( $menu->category == 'SE') && ( $menu->level == '#' ) )
                {
                    $leaderString = str_replace("#", "*", $menu->menuoption);
                }
            }
        }

        return $leaderString;
    }

    /**
	 * Método reescrito para não limpar com a tecla F7
	 *
	 */
	public function onkeydown118()
	{
        $this->setResponse('','limbo');
	}

 public function getFormMode()
    {
        if ( !$this->function || $this->function == 'search' )
        {
            return search;
        }

        return 'manage';
    }
}
?>
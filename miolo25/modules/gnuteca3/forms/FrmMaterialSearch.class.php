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
 * @author Luiz Gilberto Gregory Filho [luiz@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @since
 * Class created on 14/11/2008
 *
 **/
$MIOLO->getClass('gnuteca3', 'gIso2709Export');
class FrmMaterialSearch extends GForm
{
    function __construct()
    {
        $this->setBusiness('BusChangeMaterial');
        $this->setGrid('GrdChangeMaterial');
        $this->setSearchFunction('searchMaterial');
        $this->setDeleteFunction('deleteMaterial');
        $this->setPrimaryKeys('controlNumber');
        $this->setTransaction('gtcMaterial');
        parent::__construct();
    }

    /**
     * Create Default Fileds for Search Form
     *
     * @return void
     */
    public function mainFields()
    {
        $fields[] = new GSelection("numberType",  null, _M("Tipo de número", $this->module), BusinessGnuteca3BusDomain::listForSelect('MATERIAL_SEARCH_TYPE'), false, null, null, true);
        $fields[] = new MTextField("number",      null, _M("Número",      $this->module), FIELD_ID_SIZE);
        $fields[] = new MTextField("expressionS", null, _M("Expressão",  $this->module), FIELD_DESCRIPTION_SIZE);

        // Define foco no número de controle.
        $this->setFocus('number');
         
        $this->setFields( $fields );
    }

    public function searchFunction($args)
    {
        if ( ! is_string($args) && (! $args->number && ! $args->expressionS) )
        {
            $this->error(_M('Entre pelo menos com o campo Número ou Expressão.', $this->module));
        }
        else
        {
            parent::searchFunction($args);
        }
    }
    
    public function getGrid( $data = null, $gridData = null )
    {
        $grid = parent::getGrid($data, $gridData);
        $grid->setCount( $this->business->getCount() );
        return $grid;
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
        $this->_toolBar->removeButtons(array( MToolBar::BUTTON_SAVE, MToolBar::BUTTON_DELETE, MToolBar::BUTTON_RESET, "btnFormContent"));

        //habilita botão exportar ISO 2709
        if ( GPerms::checkAccess('gtcISO2709Export', A_ACCESS, false) )
        {
            $imageExport = GUtil::getImageTheme('iso2709-export-32x32.png');
        
            $this->_toolBar->addRelation(_M("Exportar todos registros da pesquisa",'gnuteca3'), $imageExport, "javascript:".Gutil::getAjax('exportAllRegisters'));
            $this->_toolBar->addRelation(_M('Exportar registros selecionados', 'gnuteca3'), $imageExport, 'javascript:' . GUtil::getAjax('exportSelectedRegisters'));
        }
        
        return $this->_toolBar;
    }

    /**
     * Exporta todos registros da grid
     * @param stdClass $args 
     */
    public function exportAllRegisters($args)
    {
        $data = $this->getData();
        $this->business->setData( $data );
        $data = $this->business->searchMaterial();
        
        $controlsNumbers = array();
        
        //obtém os números de controle
        if ( is_array($data) )
        {
            foreach ( $data as $i => $value )
            {
                $controlsNumbers[] = $value[0];
            }
        }
        else 
        {
            $this->information(_M('Nenhum material selecionado', 'gnuteca3'));
        }
        
        //exporta em arquivos ISO
        $this->exportISO2709($controlsNumbers);
        $this->setResponse(null, 'limbo');
    }
    
    /**
     * Exporta registros selecionados na grid
     * @param stdClass $args 
     */
    public function exportSelectedRegisters($args)
    {
        $controlNumbers = array();
        
        //obtém os números de controle
        if ( is_array( $args->selectGrdChangeMaterial ) )
        {
            foreach ( $args->selectGrdChangeMaterial as $i => $selected )
            {
                $explode = explode('=', $selected);
                $controlNumbers[] = $explode[1];
            }
        }
        
        //exporta o iso
        $this->exportISO2709($controlNumbers);
        $this->setResponse(null, 'limbo');
    }
    
    /**
     * Exporta os materiais em arquivo ISO
     * @param array $controlNumbers 
     */
    public function exportISO2709($controlNumbers)
    {
        //testa se existe números de controle para exportar
        if ( count($controlNumbers) > 0 )
        {
            $object = new gIso2709Export($controlNumbers);
            $content = $object->execute();

            $folder = 'tmp';
            $file = 'gnuteca_' . GDate::now()->getTimestampUnix() . '.iso';
            
            file_put_contents(BusinessGnuteca3BusFile::getAbsoluteServerPath(true) . '/' . $folder . '/' . $file, $content);
            BusinessGnuteca3BusFile::openDownload( $folder, $file);
        }
        else
        {
            $this->information(_M('Nenhum material para exportar', 'gnuteca3'));
        }
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
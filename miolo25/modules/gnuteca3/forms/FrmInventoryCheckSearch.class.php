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
 * Processo para verificar inventário
 *
 * @author Jader Osvino Fiegenbaum [jader@solis.coop.br]
  *
 * @version $Id$
 *
 * \b Maintainers \n
 * Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @since
 * Class created on 26/09/2011
 *
 **/

$MIOLO->getClass('gnuteca3', 'controls/GFileUploader');
class FrmInventoryCheckSearch extends GForm
{
    private $busLibraryUnit,
            $busExemplaryStatus,
            $busMaterial;

    public function __construct($data)
    {
        set_time_limit(0);
        
        $this->MIOLO    = MIOLO::getInstance();
        $this->module   = MIOLO::getCurrentModule();

        $this->busLibraryUnit = $this->MIOLO->getBusiness($this->module, 'BusLibraryUnit');
        $this->busExemplaryStatus = $this->MIOLO->getBusiness($this->module, 'BusExemplaryStatus');
        
        $this->busMaterial = $this->MIOLO->getBusiness($this->module, 'BusMaterial');

        $this->setAllFunctions('InventoryCheck');
        $this->setSearchFunction('inventoryCheck');
        $this->setGrid('GrdInventoryCheck');

        if ( GForm::primeiroAcessoAoForm() )
        {
             GFileUploader::clearData('inventoryFile');
        }

        parent::__construct(_M("Catalogação facilitada", $this->module));
    }

    public function mainFields()
    {
        $this->busLibraryUnit->filterOperator = true;
        $list = $this->busLibraryUnit->listLibraryUnit(false, true);
        $fields[] = new GSelection('libraryUnitId', null, _M("Unidade de biblioteca", $this->module), $list);
        
        $fields[] = new MMultiSelection('exemplaryStatusId', array(null), _M('Estado do exemplar',$this->module), $this->busExemplaryStatus->listExemplaryStatus(), null, _M('Para múltiplas opções, segure a tecla "Ctrl"', $this->module), 5);
        
        $fields[] = new MTextField('beginClassification',null, _M('Classificação inicial', $module),FIELD_DESCRIPTION_SIZE);
        $fields[] = new MTextField('endClassification',null, _M('Classificação final', $module),FIELD_DESCRIPTION_SIZE);

        $fields[] = new GFileUploader(_M('Inventário',$this->module), false, null, 'inventoryFile' );
        GFileUploader::setLimit(1, 'inventoryFile'); //somente uma arquivo por processo
        GFileUploader::setExtensions(array('txt'), array('php', 'class', 'js'), 'inventoryFile');
        $this->setFields($fields);

        $validators[] = new MRequiredValidator('exemplaryStatusId');
        $validators[] = new MRequiredValidator('beginClassification');
        $validators[] = new MRequiredValidator('endClassification');
        $this->setValidators($validators);
        
        $this->toolBar->disableButton( array('tbBtnNew','tbBtnSave','tbBtnSearch'));
    }
    
    /**
     * Método reescrito para tratar dados do filefield
     * 
     * @return stdClass de dados 
     */
    public function getData()
    {
        $data = parent::getData();
        
        $data->files = GFileUploader::getData('inventoryFile');
        
        $data->inventory = '';
        foreach( $data->files as $file )
        {
            $data->inventory .= file_get_contents($file->tmp_name);
        }
        
        return $data;
    }
    
    /**
     * Método reescrito para fazer a validação de dados do form
     * 
     * @param stdClass $args dados
     * @return popula a grid 
     */
    public  function searchFunction($args) 
    {
        $this->mainFields();
        
        if ( !$this->validate() )
        {
            return false;
        }
        
        parent::searchFunction($args);
    }

    public function generateGridPdf($args)
    {

        if ( !$this->validate() )
        {
            return false;
        }

        parent::searchFunction($args);

    }

    public function generateGridCsv($args)
    {

        if ( !$this->validate() )
        {
            return false;
        }

        parent::searchFunction($args);

    }

   
}
?>

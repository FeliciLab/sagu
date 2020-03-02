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
 * Supplier search form
 *
 * @author Moises Heberle [moises@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Luiz Gregory Filho [luiz@solis.coop.br]
 * Moises Heberle [moises@solis.coop.br]
 * Sandro Roberto Weisheimer [sandrow@solis.coop.br]
 * Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @since
 * Class created on 01/12/2008
 *
 **/
$MIOLO->getClass( $module, 'controls/GMaterialDetail');
class FrmSupplierSearch extends GForm
{
    public function __construct()
    {
        $this->setAllFunctions('Supplier', array('supplierId'),array('supplierId'));
        parent::__construct();
    }

    public function mainFields()
    {
    	$fields[] = new MIntegerField('supplierIdS', MIOLO::_REQUEST('supplierId'), _M('Código',$this->module), FIELD_ID_SIZE);
        $fields[] = new MTextField('nameS', null, _M('Nome', $this->module), FIELD_DESCRIPTION_SIZE);
        $fields[] = new MTextField('companyNameS', null, _M('Nome da companhia', $this->module), FIELD_DESCRIPTION_SIZE);
        $fields[] = new MTextField('cnpjS', null, _M('CNPJ', $this->module), FIELD_DESCRIPTION_SIZE);
        $fields[] = new MTextField('locationS', null, _M('Logradouro', $this->module), FIELD_DESCRIPTION_SIZE);
        $fields[] = new MTextField('cityS', null, _M('Cidade', $this->module), FIELD_DESCRIPTION_SIZE);
        $fields[] = new MTextField('neighborhoodS', null, _M('Bairro', $this->module), FIELD_DESCRIPTION_SIZE);
        $fields[] = new MTextField('contactS', null, _M('Contato', $this->module), FIELD_DESCRIPTION_SIZE);
        $fields[] = new MTextField('observationS', null, _M('Observação', $this->module), FIELD_DESCRIPTION_SIZE);
        $fields[] = new MTextField('bankDepositS', null, _M('Depósito bancário', $this->module), FIELD_DESCRIPTION_SIZE);

        // FIELD PERIOD
        $lblDate    = new MLabel(_M('Data', $this->module) . ':');
        $lblDate    ->setWidth(FIELD_LABEL_SIZE);
        $beginDateS = new MCalendarField('beginDateS', $this->beginDateS->value);
        $endDateS   = new MCalendarField('endDateS', $this->endDateS->value);
        $fields[]   = new GContainer('hctDates', array($lblDate, $beginDateS, $endDateS));

        $this->setFields($fields);
        $validators[] = new MIntegerValidator('supplierIdS');

        $this->setValidators($validators);
    }

   
    /**
     * Método que busca as coleções do fornecedor
     *
     * @param apenas dados
     * @param id do fornecedor
     */
    public function searchColectionOfSupplier( $onlyData = false, $supplierId = null)
    {
        $busMaterial = $this->MIOLO->getBusiness($this->module, 'BusMaterial');
        $supplierId = $supplierId ? $supplierId : MIOLO::_REQUEST('supplierId');

        if ( strlen($supplierId) == 0 )
        {
            return false;
        }

        $busGenericSearch = $this->MIOLO->getBusiness($this->module, 'BusGenericSearch2');
        $busSearchFormat = $this->MIOLO->getBusiness($this->module, 'BusSearchFormat');

        //pega as tags de pesquisa
        $fieldsList = $busSearchFormat->getVariablesFromSearchFormat(ADMINISTRATION_SEARCH_FORMAT_ID);

        //define as tags de pesquisa
        if ( is_array( $fieldsList ) )
        {
            foreach ( $fieldsList as $line => $info )
            {
                $tag = str_replace('$','', $info);
                $busGenericSearch->addSearchTagField($tag);
            }
        }

        //FIXME '947.a' hardcoded?
        $busGenericSearch->addMaterialWhereByTag('947.a', array($supplierId), 'AND', '=');
        $data = $busGenericSearch->getWorkSearch(9999); //limite alto para não incluir limite padrão de paginação
        $newData = array();

        if ( is_array($data) )
        {
            foreach( $data as $i=>$info )
            {
                $newData[$i][0] = $info['CONTROLNUMBER'];
                $newData[$i][1] = $busSearchFormat->formatSearchData(ADMINISTRATION_SEARCH_FORMAT_ID, $info);
                $tag = explode('.',MARC_LEADER_TAG);
            }
        }

        if ( $onlyData === true )
        {
            return $newData;
        }

        $grid = $this->MIOLO->getUi()->getGrid($this->module, 'GrdColectionOfSupplier');
        $grid->setData($newData);
        $grid->setCSV(FALSE);

        $fields = array();
        $fields[] = new MHiddenField('supplierIdGrid', $supplierId);
        $fields[] = new MDiv('divGridColectionOfSupplier', $grid);
        $this->injectContent( $fields , true, _M( 'Coleções do fornecedor' , $this->module ));
        
    }
}
?>
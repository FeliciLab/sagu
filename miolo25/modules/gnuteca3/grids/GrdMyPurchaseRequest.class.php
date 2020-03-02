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
 * @author Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @since
 * Class created on 06/06/2011
 *
 **/
class GrdMyPurchaseRequest extends GGrid
{
    public $MIOLO;
    public $module;
    public $action;
    
    private $busPurchaseRequestMaterial, $busSearchFormat;

    public function __construct($data)
    {
        $this->MIOLO  = MIOLO::getInstance();
        $this->module = MIOLO::getCurrentModule();
        $this->action = MIOLO::getCurrentAction();
        
        $this->busPurchaseRequestMaterial = $this->MIOLO->getBusiness('gnuteca3', 'BusPurchaseRequestMaterial');
        $this->busSearchFormat = $this->MIOLO->getBusiness('gnuteca3', 'BusSearchFormat');

        $columns = array(
                        new MGridColumn(_M('Código',                $this->module), MGrid::ALIGN_RIGHT, null, null, true, null, true),
                        new MGridColumn(_M('Pessoa',                $this->module), MGrid::ALIGN_LEFT,  null, null, false, null, true),
                        new MGridColumn(_M('Pessoa',                $this->module), MGrid::ALIGN_LEFT,  null, null, false, null, true),
                        new MGridColumn(_M('Centro de custo',       $this->module), MGrid::ALIGN_LEFT,  null, null, false, null, true),
                        new MGridColumn(_M('Centro de custo',       $this->module), MGrid::ALIGN_LEFT,  null, null, false, null, true),
                        new MGridColumn(_M('Dados',                 $this->module), MGrid::ALIGN_LEFT,  null, null, true, null, true),
                        new MGridColumn(_M('Quantidade',            $this->module), MGrid::ALIGN_LEFT,  null, null, false, null, true),
                        new MGridColumn(_M('Unidade de biblioteca', $this->module), MGrid::ALIGN_LEFT,  null, null, false, null, true),
                        new MGridColumn(_M('Unidade de biblioteca', $this->module), MGrid::ALIGN_LEFT,  null, null, true, null, true),
                        new MGridColumn(_M('Observação', $this->module), MGrid::ALIGN_LEFT,  null, null, true, null, true),
                        new MGridColumn(_M('Estado', $this->module), MGrid::ALIGN_LEFT,  null, null, false, null, true),
                        new MGridColumn(_M('Curso', $this->module), MGrid::ALIGN_LEFT,  null, null, false, null, true),
                        new MGridColumn(_M('Quantidade', $this->module), MGrid::ALIGN_LEFT,  null, null, false, null, true),
                        new MGridColumn(_M('Previsão', $this->module), MGrid::ALIGN_LEFT,  null, null, false, null, true),
                        new MGridColumn(_M('Entrega', $this->module), MGrid::ALIGN_LEFT,  null, null, false, null, true),
                        new MGridColumn(_M('Voucher', $this->module), MGrid::ALIGN_LEFT,  null, null, false, null, true),
                        new MGridColumn(_M('Número de controle', $this->module), MGrid::ALIGN_LEFT,  null, null, false, null, true),
                        new MGridColumn(_M('Pré-catalogação', $this->module), MGrid::ALIGN_LEFT,  null, null, false, null, true),
                        new MGridColumn(_M('Código externo', $this->module), MGrid::ALIGN_LEFT,  null, null, false, null, true),
                        new MGridColumn(_M('Estado', $this->module), MGrid::ALIGN_LEFT,  null, null, true, null, true),
        );

        parent::__construct($data, $columns, $this->MIOLO->getActionURL($this->module, $this->action), LISTING_NREGS, 0, 'gridMyLoan');

        $this->setIsScrollable();
        
        //Se preferência estiver como falso, não mostra botão CSV
        if ( (CSV_MYLIBRARY == 'f') && (MIOLO::_REQUEST('action') != 'main:materialMovement') )
        {
            $this->setCSV(false);
        }
        
        $this->setRowMethod($this, 'checkValues');
    }


    /**
     * Aplica formato de pesquisa na coluna dados
     * 
     * @param type $i
     * @param type $row
     * @param type $actions
     * @param type $columns 
     */
    public function checkValues($i, $row, $actions, $columns)
    {
        $this->busPurchaseRequestMaterial->purchaseRequestId = $columns[0]->control[$i]->value;
        $material = $this->busPurchaseRequestMaterial->searchPurchaseRequestMaterial('purchaseRequestId', true);
        
        $values = array();
        
        if ( is_array($material) )
        {
            foreach ( $material as $key => $value )
            {
                $value->subFieldId = $value->subfieldId;
                $values[$value->fieldId . '.' . $value->subfieldId][] = $value;
            }
        }

    	$tempData = $this->busSearchFormat->formatSearchData( ADMINISTRATION_SEARCH_FORMAT_ID, $values);
    	$tempDataDiv = new MDiv('materialContent'. rand(1000), $tempData);
        $columns[5]->control[$i]->setValue( $tempDataDiv->generate() );
    }
}
?>

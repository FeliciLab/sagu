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
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @since
 * Class created on 18/11/2008
 *
 **/
class GrdReserveQueue extends GGrid
{
    public $MIOLO;
    public $module;
    public $action;
    public $busExemplaryControl;
    public $busSearchFormat;
    public $busExemplaryStatus;

    public function __construct($data)
    {
        $this->MIOLO  = MIOLO::getInstance();
        $this->module = MIOLO::getCurrentModule();
        $this->action = MIOLO::getCurrentAction();
        $this->busExemplaryControl = $this->MIOLO->getBusiness($this->module, 'BusExemplaryControl');
        $this->busSearchFormat     = $this->MIOLO->getBusiness($this->module, 'BusSearchFormat');
        $this->busExemplaryStatus  = $this->MIOLO->getBusiness($this->module, 'BusExemplaryStatus');

        $columns = array(
            new MGridColumn(_M('Código da reserva', $this->module), MGrid::ALIGN_RIGHT,  null, null, true, null, true),
            new MGridColumn(_M('Número do tomo', $this->module), MGrid::ALIGN_CENTER, null, null, true, null, true),
            new MGridColumn(_M('Número do controle', $this->module),MGrid::ALIGN_CENTER, null, null, true, null, true),
            new MGridColumn(_M('Dados', $this->module), MGrid::ALIGN_LEFT,   null, null, true, null, true),
            new MGridColumn(_M('Código da pessoa', $this->module), MGrid::ALIGN_CENTER, null, null, true, null, true),
            new MGridColumn(_M('Pessoa', $this->module), MGrid::ALIGN_LEFT,   null, null, true, null, true),
            new MGridColumn(_M('Data limite', $this->module), MGrid::ALIGN_LEFT,   null, null, true, null, true, MSort::MASK_DATETIME_BR),
            new MGridColumn(_M('Estado da reserva', $this->module), MGrid::ALIGN_CENTER, null, null, true, null, true),
            new MGridColumn(_M('Estado do exemplar', $this->module), MGrid::ALIGN_CENTER, null, null, true, null, true)
        );

        parent::__construct($data, $columns, $this->MIOLO->getCurrentURL(), 0, 0, 'gridReserveQueue');

        $this->setIsScrollable();
        $this->setRowMethod($this, 'checkValues');
    }

    public function checkValues($i, $row, $actions, $columns)
    {
        //retorna título do itemNumber da reserva
    	$itemNumber = $columns[1]->control[$i]->value;
        $controlNumber = $columns[2]->control[$i]->value;;
        $columns[3]->control[$i]->setValue( $this->busSearchFormat->getFormatedString($controlNumber, ADMINISTRATION_SEARCH_FORMAT_ID) );

        //o estado do material deve ser obtido na grid, pois no processo original o estado é trocado
        //TODO isso poderia ser otimizado para obter todos os estados em um único momento
        $exemplar= $this->busExemplaryControl->getExemplaryControl($itemNumber);
        $status = $exemplar->exemplaryStatusId;
        $description = $this->busExemplaryStatus->getExemplaryStatus($status)->description;

        $columns[8]->control[$i]->setValue( $description ? $description : _M('Impossível encontrar descrição do estado @1','gnuteca3',$status)  );
        $columns[6]->control[$i]->setValue( GDate::construct($columns[6]->control[$i]->value)->getDate( GDate::MASK_DATE_USER ) );
    }
}
?>